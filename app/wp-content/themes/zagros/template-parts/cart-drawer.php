<?php
/** Cart drawer. Items, totals and the free-shipping bar are drawn by src/js/wp/store.js from the Store API. */
defined( 'ABSPATH' ) || exit;
$lc_free = lc_free_shipping();
?>
<div class="scrim" data-scrim hidden></div>
<aside class="drawer" data-cart aria-label="سبد خرید" aria-hidden="true" tabindex="-1">
	<div class="drawer__head">
		<h2 class="drawer__title"><span class="en">BAG</span> <span>سبد خرید</span></h2>
		<button class="icon-btn" type="button" data-cart-close aria-label="بستن سبد"><svg><use href="#i-close"/></svg></button>
	</div>
	<?php if ( $lc_free ) : ?>
		<div class="drawer__ship" data-ship hidden>
			<p data-ship-text><b><?php echo esc_html( lc_price( $lc_free ) ); ?> تومان</b> تا ارسال رایگان</p>
			<span class="drawer__bar"><i data-ship-bar></i></span>
		</div>
	<?php endif; ?>
	<ul class="drawer__items" data-cart-items></ul>
	<div class="drawer__empty" data-cart-empty>
		<svg class="drawer__empty-mark" viewBox="0 0 14 12" aria-hidden="true"><path d="M7 0 14 12H0z"/></svg>
		<p>سبد خرید شما خالی است.</p>
		<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn btn--ghost btn--sm">مشاهده محصولات</a>
	</div>
	<div class="drawer__foot" data-cart-foot hidden>
		<div class="drawer__total"><span>جمع کل</span><b data-cart-total>۰ تومان</b></div>
		<a href="<?php echo esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '#' ); ?>" class="btn btn--split btn--block" data-checkout data-hard><span>ادامه فرایند خرید</span><span class="btn__price" data-cart-total-pill>۰</span></a>
		<p class="small">هزینه ارسال در مرحله بعد محاسبه می‌شود.</p>
	</div>
</aside>
