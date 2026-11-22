<?php
/** Theme layout for WooCommerce thank-you template 8.1.0. Order access is checked by WooCommerce. */
defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order lc-order-result">
<?php if ( $order instanceof WC_Order ) :
	do_action( 'woocommerce_before_thankyou', $order->get_id() );
	$paid = $order->is_paid();
	$state = $paid ? 'paid' : ( $order->has_status( [ 'failed', 'cancelled' ] ) ? 'failed' : 'pending' );
	$status_text = $paid ? 'پرداخت موفق' : ( 'failed' === $state ? 'پرداخت تکمیل نشد' : 'در انتظار تأیید پرداخت' );
	if ( $order->has_status( 'refunded' ) ) { $status_text = 'بازپرداخت شده'; $state = 'refunded'; }
?>
	<div class="lc-result-grid">
		<div class="lc-result-details">
			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		</div>
		<aside class="lc-result-summary" aria-label="خلاصه سفارش">
			<div class="lc-result-summary-heading"><h2>خلاصه سفارش</h2><span class="lc-result-status lc-result-status--<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $status_text ); ?></span></div>
			<dl class="lc-result-meta">
				<div><dt>شماره سفارش</dt><dd><?php echo esc_html( lc_fa( $order->get_order_number() ) ); ?></dd></div>
				<?php if ( $order->get_date_created() ) : ?><div><dt>تاریخ سفارش</dt><dd><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></dd></div><?php endif; ?>
				<?php if ( $paid && $order->get_transaction_id() ) : ?><div><dt>کد پیگیری پرداخت</dt><dd class="lc-result-reference"><?php echo esc_html( $order->get_transaction_id() ); ?></dd></div><?php endif; ?>
				<div><dt>روش پرداخت</dt><dd><?php echo esc_html( $order->get_payment_method_title() ); ?></dd></div>
			</dl>
			<dl class="lc-result-totals">
				<?php foreach ( $order->get_order_item_totals() as $key => $total ) :
					if ( 'payment_method' === $key ) { continue; }
				?>
				<div class="<?php echo 'order_total' === $key ? 'lc-result-grand-total' : ''; ?>"><dt><?php echo esc_html( rtrim( wp_strip_all_tags( $total['label'] ), ': ' ) ); ?></dt><dd><?php echo wp_kses_post( $total['value'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<?php if ( $order->needs_payment() ) : ?>
				<p class="lc-result-unpaid-message"><?php echo esc_html( lc_order_result_message( $order ) ); ?></p>
				<a class="lc-result-action lc-result-action--pay" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">تلاش دوباره برای پرداخت</a>
			<?php endif; ?>
			<a class="lc-result-action" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">پیگیری وضعیت سفارش <span aria-hidden="true">←</span></a>
		</aside>
	</div>
	<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
<?php else : ?>
	<p class="lc-result-unavailable"><?php echo esc_html( lc_order_result_message( false ) ); ?></p>
<?php endif; ?>
</div>
