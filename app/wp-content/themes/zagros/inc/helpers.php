<?php
/** Formatting and product-data helpers shared by templates and the frontend JSON. */
defined( 'ABSPATH' ) || exit;

/** Persian digits. */
function lc_fa( $value ): string {
	return strtr( (string) $value, [ '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ] );
}

/** 7900000 → ۷،۹۰۰،۰۰۰ (Persian comma between thousands; the store currency is Toman, no decimals). */
function lc_price( $amount ): string {
	return lc_fa( number_format( (float) $amount, 0, '', '٬' ) );
}

function lc_login_url(): string {
	$page = get_page_by_path( 'login' );
	return $page ? get_permalink( $page ) : wp_login_url();
}

function lc_opt( string $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/** Image URL from a theme_mod attachment ID with a bundled fallback. */
function lc_mod_img( string $key, string $size = 'large', string $fallback = '' ): string {
	$id = (int) get_theme_mod( $key );
	$url = $id ? wp_get_attachment_image_url( $id, $size ) : '';
	return $url ?: $fallback;
}

/** Days a product counts as "new". */
function lc_new_days(): int {
	return (int) apply_filters( 'lc_new_days', 30 );
}

function lc_is_new( WC_Product $p ): bool {
	$created = $p->get_date_created();
	return $created && ( time() - $created->getTimestamp() ) < lc_new_days() * DAY_IN_SECONDS;
}

/** Hex colour stored on a pa_color term (see inc/color-terms.php). */
function lc_term_hex( WP_Term $term ): string {
	$hex = (string) get_term_meta( $term->term_id, 'lc_hex', true );
	return preg_match( '/^#[0-9a-f]{3,8}$/i', $hex ) ? $hex : '#777777';
}

/**
 * Everything a card / product page / quick-add sheet needs about a product,
 * read from WooCommerce (never duplicated).
 */
function lc_product_data( WC_Product $p, bool $full = false ): array {
	$id       = $p->get_id();
	$is_var   = $p->is_type( 'variable' );
	$image_id = $p->get_image_id();
	$gallery  = $p->get_gallery_image_ids();
	$img      = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : wc_placeholder_img_src( 'woocommerce_single' );
	$alt      = $gallery ? wp_get_attachment_image_url( $gallery[0], 'woocommerce_single' ) : $img;

	if ( $is_var ) {
		$price   = (float) $p->get_variation_price( 'min', true );
		$regular = (float) $p->get_variation_regular_price( 'min', true );
	} else {
		$price   = (float) wc_get_price_to_display( $p );
		$regular = (float) wc_get_price_to_display( $p, [ 'price' => $p->get_regular_price() ] );
	}
	$off = ( $regular > $price && $regular > 0 ) ? (int) round( ( 1 - $price / $regular ) * 100 ) : 0;

	$cats     = wc_get_product_terms( $id, 'product_cat', [ 'fields' => 'all' ] );
	$data     = [
		'id'      => $id,
		'name'    => $p->get_name(),
		'url'     => get_permalink( $id ),
		'type'    => $p->get_type(),
		'price'   => $price,
		'regular' => $regular,
		'off'     => $off,
		'onSale'  => $off > 0,
		'isNew'   => lc_is_new( $p ),
		'soldOut' => ! $p->is_in_stock(),
		'image'   => $img,
		'alt'     => $alt,
		'cat'     => $cats ? $cats[0]->name : '',
		'catSlug' => $cats ? $cats[0]->slug : '',
		'catUrl'  => $cats ? get_term_link( $cats[0] ) : '',
		'colors'  => [],
		'sizes'   => [],
		'variations' => [],
	];

	if ( $is_var ) {
		$attrs = $p->get_variation_attributes();
		foreach ( $p->get_available_variations( 'objects' ) as $v ) {
			/** @var WC_Product_Variation $v */
			$va = $v->get_attributes();
			$vimg = $v->get_image_id();
			$data['variations'][] = [
				'id'      => $v->get_id(),
				'color'   => $va['pa_color'] ?? '',
				'size'    => $va['pa_size'] ?? '',
				'price'   => (float) wc_get_price_to_display( $v ),
				'regular' => (float) wc_get_price_to_display( $v, [ 'price' => $v->get_regular_price() ] ),
				'in'      => $v->is_in_stock(),
				'img'     => $vimg && $vimg !== (int) $image_id ? wp_get_attachment_image_url( $vimg, 'woocommerce_single' ) : '',
			];
		}
		$in_stock = static fn( string $key, string $slug ) => (bool) array_filter( $data['variations'], static fn( $v ) => $v['in'] && ( '' === $v[ $key ] || $v[ $key ] === $slug ) );
		if ( isset( $attrs['pa_color'] ) ) {
			foreach ( wc_get_product_terms( $id, 'pa_color', [ 'fields' => 'all' ] ) as $t ) {
				if ( ! in_array( $t->slug, $attrs['pa_color'], true ) ) {
					continue;
				}
				$vimg = '';
				foreach ( $data['variations'] as $v ) {
					if ( $v['color'] === $t->slug && $v['img'] ) { $vimg = $v['img']; break; }
				}
				$data['colors'][] = [ 'slug' => $t->slug, 'name' => $t->name, 'hex' => lc_term_hex( $t ), 'img' => $vimg ?: $img, 'out' => ! $in_stock( 'color', $t->slug ) ];
			}
		}
		if ( isset( $attrs['pa_size'] ) ) {
			foreach ( wc_get_product_terms( $id, 'pa_size', [ 'fields' => 'all' ] ) as $t ) {
				if ( in_array( $t->slug, $attrs['pa_size'], true ) ) {
					$data['sizes'][] = [ 'slug' => $t->slug, 'name' => lc_fa( $t->name ) ];
				}
			}
		}
	}

	// colour of a single-tone product (non-variation pa_color) for the colour filter + dot
	if ( ! $data['colors'] ) {
		$tone = wc_get_product_terms( $id, 'pa_color', [ 'fields' => 'all' ] );
		$data['tone'] = $tone ? lc_term_hex( $tone[0] ) : '';
	}

	if ( $full ) {
		$data['desc']    = wp_strip_all_tags( $p->get_short_description() ?: wp_trim_words( $p->get_description(), 40 ) );
		$data['gallery'] = array_values( array_filter( array_map( static fn( $gid ) => wp_get_attachment_image_url( $gid, 'woocommerce_single' ), $gallery ) ) );
		$specs = [];
		foreach ( $p->get_attributes() as $attr ) {
			/** @var WC_Product_Attribute $attr */
			if ( $attr->get_variation() || ! $attr->get_visible() ) {
				continue;
			}
			$label = wc_attribute_label( $attr->get_name(), $p );
			$value = $attr->is_taxonomy()
				? implode( '، ', wc_get_product_terms( $id, $attr->get_name(), [ 'fields' => 'names' ] ) )
				: implode( '، ', $attr->get_options() );
			if ( $value ) {
				$specs[] = [ 'k' => $label, 'v' => lc_fa( $value ) ];
			}
		}
		if ( $p->get_sku() ) {
			$specs[] = [ 'k' => 'کد', 'v' => lc_fa( $p->get_sku() ) ];
		}
		$data['specs'] = $specs;
	}
	return $data;
}

/** Price range of the published catalog, in whole millions, for honest hero copy. */
function lc_price_range_millions(): array {
	$cached = get_transient( 'lc_price_range' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	global $wpdb;
	$row = $wpdb->get_row( "SELECT MIN(min_price) lo, MAX(max_price) hi FROM {$wpdb->wc_product_meta_lookup} l JOIN {$wpdb->posts} p ON p.ID = l.product_id WHERE p.post_status = 'publish' AND l.min_price > 0" );
	$range = [ $row && $row->lo ? (int) floor( $row->lo / 1000000 ) : 0, $row && $row->hi ? (int) ceil( $row->hi / 1000000 ) : 0 ];
	set_transient( 'lc_price_range', $range, HOUR_IN_SECONDS );
	return $range;
}
add_action( 'woocommerce_update_product', static fn() => delete_transient( 'lc_price_range' ) );

/** Shop URL keeping the current filters, with overrides. */
function lc_shop_url( array $set = [], array $drop = [], string $base = '' ): string {
	if ( ! $base ) {
		$base = is_product_taxonomy() ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' );
	}
	$keep = array_intersect_key( $_GET, array_flip( [ 'lc_color', 'lc_price', 'on_sale', 'is_new', 'in_stock', 'orderby', 's', 'post_type' ] ) );
	$args = array_merge( array_map( 'sanitize_text_field', wp_unslash( $keep ) ), $set );
	foreach ( $drop as $k ) {
		unset( $args[ $k ] );
	}
	return add_query_arg( array_filter( $args, static fn( $v ) => '' !== $v && null !== $v ), $base );
}

/** The shop's coordinates (Customizer → آدرس فروشگاه) and links that open them in each map app. */
function lc_map_links(): array {
	$lat  = (float) lc_opt( 'lc_map_lat', '35.699700' );
	$lng  = (float) lc_opt( 'lc_map_lng', '51.338000' );
	$ll   = sprintf( '%.6f,%.6f', $lat, $lng );
	$name = get_bloginfo( 'name' );
	return [
		'google'  => 'https://www.google.com/maps/search/?api=1&query=' . $ll,
		'neshan'  => 'https://neshan.org/maps/@' . $ll . ',17.0z,0.0p',
		'balad'   => sprintf( 'https://balad.ir/location?latitude=%.6f&longitude=%.6f&zoom=17', $lat, $lng ),
		'apple'   => 'https://maps.apple.com/?ll=' . $ll . '&q=' . rawurlencode( $name ),
		'geo'     => 'geo:' . $ll . '?q=' . $ll . '(' . rawurlencode( $name ) . ')',
	];
}

/** The blog ("مجله"): Settings → Reading → posts page, else the site's posts index. */
function lc_journal_url(): string {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? (string) get_permalink( $id ) : home_url( '/' );
}

/** Minutes to read a post, at about 200 Persian words a minute. */
function lc_read_minutes( ?WP_Post $post = null ): int {
	$post = get_post( $post );
	$text = trim( wp_strip_all_tags( (string) ( $post->post_content ?? '' ) ) );
	return max( 1, (int) ceil( count( preg_split( '/\s+/u', $text ) ) / 180 ) );
}
