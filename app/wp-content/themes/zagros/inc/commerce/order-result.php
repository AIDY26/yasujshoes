<?php
/** Payment receipt and verified order result for the native checkout widget. */
defined( 'ABSPATH' ) || exit;

// Zarinpal's sandbox still requires a nonempty merchant_id in its API payload.
// This public test UUID was verified against the sandbox API; never persist it
// as a live merchant credential or replace an owner-configured merchant code.
add_filter( 'option_woocommerce_WC_ZPal_settings', static function ( $settings ) {
	if ( is_array( $settings ) && 'yes' === ( $settings['sandbox'] ?? 'no' ) && empty( $settings['merchantcode'] ) && ! is_admin() ) {
		$settings['merchantcode'] = '00000000-0000-0000-0000-000000000000';
	}
	return $settings;
} );

/** A returned receipt hook means the gateway did not redirect successfully. */
function lc_render_payment_receipt_result( int $order_id ): void {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->needs_payment() ) {
		return;
	}
	// The gateway queues errors after WooCommerce has printed the earlier
	// notices, so print them here instead of leaving an unexplained summary.
	wc_print_notices();
	echo '<section class="lc-payment-retry"><h2>پرداخت انجام نشده است</h2><p>سفارش شما در انتظار پرداخت است. لطفاً دوباره تلاش کنید.</p><a class="button" href="' . esc_url( $order->get_checkout_payment_url( true ) ) . '">تلاش دوباره برای پرداخت</a></section>';
}
add_action( 'woocommerce_receipt_WC_ZPal', 'lc_render_payment_receipt_result', 20 );

/** Result text follows persisted WooCommerce payment status, never URL flags. */
function lc_order_result_message( $order ): string {
	if ( ! $order instanceof WC_Order ) {
		return 'برای مشاهده نتیجه و جزئیات سفارش، از لینک سفارش خود استفاده کنید.';
	}
	if ( $order->is_paid() ) {
		return 'پرداخت شما با موفقیت انجام شد. از خرید شما متشکریم! جزئیات سفارش در ادامه آمده است.';
	}
	if ( $order->has_status( [ 'failed', 'cancelled' ] ) ) {
		return 'پرداخت سفارش تکمیل نشده است.';
	}
	if ( $order->has_status( 'refunded' ) ) {
		return 'مبلغ سفارش بازپرداخت شده است.';
	}
	return 'سفارش شما ثبت شده است؛ پرداخت هنوز تأیید نشده است.';
}
add_filter( 'woocommerce_thankyou_order_received_text', static function ( $text, $order ) {
	return esc_html( lc_order_result_message( $order ) );
}, 20, 2 );

/** Only derive the hero's payment state from an accessible order and its key. */
function lc_checkout_result_order() {
	if ( ! is_order_received_page() ) { return false; }
	$order = wc_get_order( absint( get_query_var( 'order-received' ) ) );
	$key = isset( $_GET['key'] ) && is_string( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
	if ( ! $order || ! hash_equals( $order->get_order_key(), $key ) ) { return false; }
	if ( $order->get_customer_id() && $order->get_customer_id() !== get_current_user_id() ) { return false; }
	return $order;
}
add_shortcode( 'lc_order_result_title', static function (): string {
	$order = lc_checkout_result_order();
	return $order && $order->is_paid() ? 'ممنون از خرید شما!' : 'نتیجه سفارش شما';
} );

// Keep WooCommerce's own access checks, while
// arranging the native order-details hooks beside a compact payment summary.
add_filter( 'woocommerce_locate_template', static function ( $template, $name ) {
	if ( 'checkout/thankyou.php' === $name && is_order_received_page() ) {
		return LC_DIR . '/woocommerce/checkout/thankyou.php';
	}
	return $template;
}, 20, 2 );

add_filter( 'woocommerce_order_item_name', static function ( $name, $item ) {
	if ( ! is_order_received_page() || ! $item instanceof WC_Order_Item_Product ) { return $name; }
	$product = $item->get_product();
	$image = $product ? $product->get_image( 'woocommerce_thumbnail', [ 'class' => 'lc-result-product-image' ] ) : '';
	return '<span class="lc-result-product">' . $image . '<span>' . $name . '</span></span>';
}, 20, 2 );

add_filter( 'gettext', static function ( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain || ! is_order_received_page() ) { return $translation; }
	return [ 'Order details' => 'محصولات سفارش شما', 'Billing address' => 'اطلاعات گیرنده', 'Shipping address' => 'اطلاعات ارسال' ][ $text ] ?? $translation;
}, 30, 3 );
