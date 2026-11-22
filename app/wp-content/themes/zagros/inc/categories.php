<?php
/**
 * Categories index (/categories/): every WooCommerce product category with its image,
 * product count, description, sub-categories and newest pieces. Nothing to maintain by hand:
 * add, rename, reorder or give an image to a category in «محصولات ← دسته‌ها» and the page follows.
 */
defined( 'ABSPATH' ) || exit;

const LC_CATS_TPL = 'page-templates/template-categories.php';

function lc_categories_page_id(): int {
	$id = (int) get_option( 'lc_categories_page' );
	if ( $id && 'page' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) {
		return $id;
	}
	$pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => LC_CATS_TPL, 'number' => 1 ] );
	return $pages ? (int) $pages[0]->ID : 0;
}

/** The categories page, or the shop when it has not been created yet. */
function lc_categories_url(): string {
	$id = lc_categories_page_id();
	if ( $id ) {
		return (string) get_permalink( $id );
	}
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/** Creates the page once (it stays editable like any page). */
function lc_categories_ensure_page(): void {
	if ( lc_categories_page_id() ) {
		update_option( 'lc_categories_page', lc_categories_page_id(), false );
		return;
	}
	$id = (int) wp_insert_post( [
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'دسته‌ها',
		'post_name'   => 'categories',
	] );
	if ( $id ) {
		update_post_meta( $id, '_wp_page_template', LC_CATS_TPL );
		update_option( 'lc_categories_page', $id, false );
	}
}
add_action( 'admin_init', static function (): void {
	if ( ! get_option( 'lc_categories_page' ) && current_user_can( 'publish_pages' ) ) {
		lc_categories_ensure_page();
	}
} );

/** Products in a category including its sub-categories (WooCommerce keeps this count). */
function lc_category_count( WP_Term $t ): int {
	$n = get_term_meta( $t->term_id, 'product_count_product_cat', true );
	return '' === $n ? (int) $t->count : (int) $n;
}

/** Everything the index shows, straight from WooCommerce. */
function lc_category_index(): array {
	$out = [];
	foreach ( lc_categories() as $c ) {
		$term = get_term_by( 'slug', $c['slug'], 'product_cat' );
		if ( ! $term ) {
			continue;
		}
		$children = get_terms( [ 'taxonomy' => 'product_cat', 'parent' => $term->term_id, 'hide_empty' => true, 'orderby' => 'menu_order' ] );
		$products = wc_get_products( [ 'category' => [ $term->slug ], 'limit' => 4, 'status' => 'publish', 'visibility' => 'catalog', 'orderby' => 'date', 'order' => 'DESC' ] );
		$out[] = [
			'name'     => $term->name,
			'url'      => $c['url'],
			'img'      => $c['img'],
			'count'    => lc_category_count( $term ),
			'desc'     => wp_strip_all_tags( term_description( $term ) ),
			'children' => is_wp_error( $children ) ? [] : array_map( static fn( WP_Term $k ) => [ 'name' => $k->name, 'url' => get_term_link( $k ), 'count' => lc_category_count( $k ) ], $children ),
			'products' => array_map( static fn( WC_Product $p ) => [
				'name' => $p->get_name(),
				'url'  => get_permalink( $p->get_id() ),
				'img'  => $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' ),
			], $products ),
		];
	}
	return $out;
}

// Loaded on every page: the router swaps only <main>, so a soft visit would miss a page-only stylesheet.
add_action( 'wp_enqueue_scripts', static function (): void {
	$file = '/assets/categories/categories.css';
	// Zagros: categories page styles live in the bundle
	unset( $file );
}, 30 );
