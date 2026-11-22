<?php
/** WooCommerce wiring: the theme draws the shop itself, WooCommerce owns the data. */
defined( 'ABSPATH' ) || exit;

// No WooCommerce frontend CSS; the theme styles every surface it shows.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Classic templates call these wrappers; the theme's own markup replaces them.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter( 'loop_shop_per_page', static fn() => 32 );

// Hide WooCommerce's coming-soon screen from the login page.
add_filter( 'woocommerce_coming_soon_exclude', static function ( $excluded ) {
	return $excluded || is_page( 'login' );
} );

// The cart drawer reads the real cart over the Store API; keep the header
// counts correct on full page loads too.
function lc_cart_count(): int {
	return WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/** Products currently on sale (published, visible). */
function lc_sale_products( int $limit = 10 ): array {
	$ids = array_slice( wc_get_product_ids_on_sale(), 0, 60 );
	if ( ! $ids ) {
		return [];
	}
	return wc_get_products( [ 'include' => $ids, 'status' => 'publish', 'limit' => $limit, 'visibility' => 'catalog', 'orderby' => 'date', 'order' => 'DESC' ] );
}

/** Product categories that have products, with an image (term thumbnail or first product). */
function lc_categories(): array {
	$terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'exclude' => [ (int) get_option( 'default_product_cat' ) ], 'orderby' => 'menu_order' ] );
	if ( is_wp_error( $terms ) ) {
		return [];
	}
	return array_map( static function ( WP_Term $t ): array {
		$thumb = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
		$img = $thumb ? wp_get_attachment_image_url( $thumb, 'woocommerce_single' ) : '';
		if ( ! $img ) {
			$p = wc_get_products( [ 'category' => [ $t->slug ], 'limit' => 1, 'status' => 'publish' ] );
			$img = $p && $p[0]->get_image_id() ? wp_get_attachment_image_url( $p[0]->get_image_id(), 'woocommerce_single' ) : wc_placeholder_img_src();
		}
		return [ 'slug' => $t->slug, 'name' => $t->name, 'url' => get_term_link( $t ), 'img' => $img, 'count' => $t->count ];
	}, $terms );
}

/** Mark the classic cart/checkout/account pages so the theme can style them. */
add_filter( 'body_class', static function ( array $classes ): array {
	if ( is_cart() ) $classes[] = 'lc-cart-page';
	if ( is_checkout() ) $classes[] = 'lc-checkout-page';
	if ( is_account_page() ) $classes[] = 'lc-account-page';
	return $classes;
} );

// Persian, Toman-only price HTML for the classic cart/checkout too.
add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

// "۷،۹۰۰،۰۰۰ تومان": amount, a non-breaking space, then the currency.
add_filter( 'woocommerce_price_format', static fn() => '%2$s&nbsp;%1$s', 20 );
// Same Persian thousands separator as the theme's own prices.
add_filter( 'wc_get_price_thousand_separator', static fn() => '٬', 20 );
// Persian digits in WooCommerce's own price HTML (checkout review, order emails), like the theme's prices.
add_filter( 'formatted_woocommerce_price', static fn( $price ) => lc_fa( (string) $price ), 20 );
// The checkout privacy line ships in English when the translation is missing.
add_filter( 'woocommerce_get_privacy_policy_text', static function ( $text, $type ) {
	return 'checkout' === $type ? 'اطلاعات شما فقط برای ارسال سفارش و پیگیری آن استفاده می‌شود؛ جزئیات در [privacy_policy].' : $text;
}, 10, 2 );
