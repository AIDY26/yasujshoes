<?php
/** Pages, including WooCommerce's classic cart, checkout and account shortcodes (Zagros head). */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$is_store = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
	$title    = get_the_title();
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		$title = function_exists( 'lc_checkout_result_order' ) && ( $o = lc_checkout_result_order() ) && $o->is_paid() ? 'ممنون از خرید شما!' : 'نتیجه سفارش';
	}
	$en = [ 'cart' => 'BAG', 'checkout' => 'CHECKOUT', 'account' => 'ACCOUNT' ];
	$key = function_exists( 'is_cart' ) ? ( is_cart() ? 'cart' : ( is_checkout() ? 'checkout' : ( is_account_page() ? 'account' : '' ) ) ) : '';
	?>
	<section class="lc-page<?php echo $is_store ? ' lc-page--store' : ''; ?>">
		<div class="wrap">
			<?php if ( function_exists( 'is_checkout' ) && ( is_cart() || is_checkout() ) ) : ?>
				<ol class="steps-bar" aria-label="مراحل خرید">
					<li class="<?php echo is_cart() ? 'is-current' : 'is-done'; ?>"><a href="<?php echo esc_url( wc_get_cart_url() ); ?>">سبد خرید</a></li>
					<li class="<?php echo is_checkout() && ! is_order_received_page() ? 'is-current' : ( is_order_received_page() ? 'is-done' : '' ); ?>">ارسال و پرداخت</li>
					<li class="<?php echo is_order_received_page() ? 'is-current' : ''; ?>">نتیجه سفارش</li>
				</ol>
			<?php endif; ?>
			<?php if ( ! ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() && ! is_wc_endpoint_url() ) ) : ?>
				<header class="pagehead pagehead--store">
					<h1 class="h2"><?php if ( $key ) : ?><span class="en"><?php echo esc_html( $en[ $key ] ); ?></span> <?php endif; ?><span class="fa-title page__fa"><?php echo esc_html( $title ); ?></span></h1>
				</header>
			<?php endif; ?>
			<div class="page__content entry"><?php the_content(); ?></div>
		</div>
	</section>
	<?php
endwhile;
get_footer();
