<?php
/**
 * Server-side shop filters. Every filter is a plain GET parameter, so each
 * state has a real URL (shareable, works without JavaScript); the router only
 * swaps the results region instead of reloading the page.
 *
 *   product-category/<slug>/   category (WooCommerce archive)
 *   lc_color=<pa_color slug>   colour (any variation or the product's own colour)
 *   lc_price=<min>-<max>       toman
 *   on_sale=1  is_new=1  in_stock=1
 *   orderby=date|price|price-desc|discount
 *   s=<text>&post_type=product search
 */
defined( 'ABSPATH' ) || exit;

function lc_is_catalog_query( WP_Query $q ): bool {
	return $q->is_main_query() && ! is_admin() && (
		$q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product' ) )
		|| ( $q->is_search() && 'product' === $q->get( 'post_type' ) )
	);
}

function lc_filter_state(): array {
	$g = static fn( string $k ): string => isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : '';
	return [
		'color'   => sanitize_title( $g( 'lc_color' ) ),
		'price'   => preg_match( '/^\d+-\d+$/', $g( 'lc_price' ) ) ? $g( 'lc_price' ) : '',
		'sale'    => '1' === $g( 'on_sale' ),
		'new'     => '1' === $g( 'is_new' ),
		'stock'   => '1' === $g( 'in_stock' ),
		'orderby' => $g( 'orderby' ),
		's'       => $g( 's' ),
	];
}

add_action( 'pre_get_posts', static function ( WP_Query $q ): void {
	if ( ! lc_is_catalog_query( $q ) ) {
		return;
	}
	$f = lc_filter_state();
	$tax = (array) $q->get( 'tax_query' );
	$meta = (array) $q->get( 'meta_query' );
	$in = null;

	if ( $f['color'] ) {
		$tax[] = [ 'taxonomy' => 'pa_color', 'field' => 'slug', 'terms' => [ $f['color'] ] ];
	}
	if ( $f['price'] ) {
		[ $lo, $hi ] = array_map( 'intval', explode( '-', $f['price'] ) );
		global $wpdb;
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$wpdb->wc_product_meta_lookup} WHERE max_price >= %d AND min_price <= %d", $lo, $hi ) );
		$in = array_map( 'intval', $ids ?: [ 0 ] );
	}
	if ( $f['sale'] ) {
		$sale = wc_get_product_ids_on_sale();
		$in = null === $in ? ( $sale ?: [ 0 ] ) : ( array_values( array_intersect( $in, $sale ) ) ?: [ 0 ] );
	}
	if ( $f['new'] ) {
		$q->set( 'date_query', [ [ 'after' => lc_new_days() . ' days ago' ] ] );
	}
	if ( $f['stock'] ) {
		$meta[] = [ 'key' => '_stock_status', 'value' => 'outofstock', 'compare' => '!=' ];
	}
	if ( null !== $in ) {
		$q->set( 'post__in', $in );
	}
	if ( 'discount' === $f['orderby'] ) {
		// biggest saving first, then everything else by date
		$sale = wc_get_products( [ 'include' => wc_get_product_ids_on_sale() ?: [ 0 ], 'limit' => -1, 'status' => 'publish' ] );
		usort( $sale, static fn( $a, $b ) => lc_product_data( $b )['off'] <=> lc_product_data( $a )['off'] );
		$order = array_map( static fn( $p ) => $p->get_id(), $sale );
		$q->set( 'post__in', null === $in ? ( $order ?: [ 0 ] ) : ( array_values( array_intersect( $order, $in ) ) ?: [ 0 ] ) );
		$q->set( 'orderby', 'post__in' );
	}
	$q->set( 'tax_query', $tax );
	$q->set( 'meta_query', $meta );
}, 20 );

// 'discount' is ours; keep WooCommerce from rewriting it.
add_filter( 'woocommerce_get_catalog_ordering_args', static function ( array $args, $orderby ) {
	if ( 'discount' === $orderby ) {
		return [ 'orderby' => 'post__in', 'order' => 'ASC', 'meta_key' => '' ];
	}
	return $args;
}, 10, 2 );

/** Colours that exist in the catalog, for the filter sheet. */
function lc_filter_colors(): array {
	$terms = get_terms( [ 'taxonomy' => 'pa_color', 'hide_empty' => true ] );
	return is_wp_error( $terms ) ? [] : array_map( static fn( $t ) => [ 'slug' => $t->slug, 'name' => $t->name, 'hex' => lc_term_hex( $t ) ], $terms );
}

function lc_active_filter_count(): int {
	$f = lc_filter_state();
	return (int) ( (bool) $f['color'] ) + (int) ( (bool) $f['price'] ) + (int) $f['sale'] + (int) $f['new'] + (int) $f['stock'];
}
