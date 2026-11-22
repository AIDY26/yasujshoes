<?php
defined( 'ABSPATH' ) || exit;
$lc_general = lc_home()['general'];
$lc_shop    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$lc_help    = lc_help_url();
?>
</main>
<footer class="footer">
	<div class="footer__promises wrap">
		<?php foreach ( lc_promises() as $pr ) : ?>
			<a href="<?php echo esc_url( $lc_help ?: $lc_shop ); ?>" class="promise">
				<b><i aria-hidden="true"></i><?php echo esc_html( $pr['title'] ); ?></b>
				<span><?php echo esc_html( $pr['note'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<div class="footer__main wrap">
		<div class="footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer__logo" aria-label="<?php echo esc_attr( lc_brand() ); ?>"><?php get_template_part( 'template-parts/logo' ); ?></a>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ?: 'فروشگاه اینترنتی کتونی، کفش رانینگ و کفش اسپرت اورجینال؛ با ارسال به سراسر کشور.' ); ?></p>
			<?php if ( $lc_general['instagram'] ) : ?>
				<a class="footer__ig" href="<?php echo esc_url( $lc_general['instagram'] ); ?>" target="_blank" rel="noopener"><svg class="icon"><use href="#i-insta"/></svg><span class="en"><?php echo esc_html( lc_instagram_handle() ); ?></span></a>
			<?php endif; ?>
			<p class="footer__note">پاسخگویی در دایرکت اینستاگرام، هر روز ۱۰ صبح تا ۱۰ شب</p>
		</div>
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer__col" aria-label="پیوندها"><p class="footer__label en">Links</p><?php lc_nav( 'footer', 'ulink-f' ); ?></nav>
		<?php else : ?>
			<nav class="footer__col" aria-label="فروشگاه">
				<p class="footer__label en">Shop</p>
				<a href="<?php echo esc_url( $lc_shop ); ?>" class="ulink-f">همه کفش‌ها</a>
				<?php foreach ( array_slice( function_exists( 'lc_categories' ) ? lc_categories() : [], 0, 4 ) as $c ) : ?>
					<a href="<?php echo esc_url( $c['url'] ); ?>" class="ulink-f"><?php echo esc_html( $c['name'] ); ?></a>
				<?php endforeach; ?>
			</nav>
			<nav class="footer__col" aria-label="راهنما">
				<p class="footer__label en">Help</p>
				<?php if ( lc_size_url() ) : ?><a href="<?php echo esc_url( lc_size_url() ); ?>" class="ulink-f">راهنمای سایز</a><?php endif; ?>
				<?php if ( $lc_help ) : ?>
					<a href="<?php echo esc_url( $lc_help . '#shipping' ); ?>" class="ulink-f">شرایط ارسال</a>
					<a href="<?php echo esc_url( $lc_help . '#swap' ); ?>" class="ulink-f">تعویض و مرجوعی</a>
				<?php endif; ?>
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="ulink-f" data-hard>پیگیری سفارش</a><?php endif; ?>
			</nav>
			<nav class="footer__col" aria-label="<?php echo esc_attr( lc_brand() ); ?>">
				<p class="footer__label en"><?php echo esc_html( ucfirst( strtolower( lc_brand_latin() ) ) ); ?></p>
				<a href="<?php echo esc_url( lc_journal_url() ); ?>" class="ulink-f">مجله</a>
				<?php if ( $lc_help ) : ?><a href="<?php echo esc_url( $lc_help ); ?>" class="ulink-f">سوالات متداول</a><?php endif; ?>
				<?php if ( function_exists( 'lc_categories_url' ) ) : ?><a href="<?php echo esc_url( lc_categories_url() ); ?>" class="ulink-f">دسته‌بندی‌ها</a><?php endif; ?>
				<a href="<?php echo esc_url( is_user_logged_in() && function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : lc_login_url() ); ?>" class="ulink-f">حساب کاربری</a>
			</nav>
		<?php endif; ?>
		<form class="footer__news" action="#" data-news>
			<h3 class="footer__news-title"><span class="en">BE FIRST</span> <span>از تخفیف‌ها جا نمونی</span></h3>
			<p>با ثبت شماره موبایل، از مدل‌های جدید و تخفیف‌ها باخبر شوید.</p>
			<div class="footer__field">
				<label class="sr-only" for="news-phone">شماره موبایل</label>
				<input id="news-phone" type="tel" inputmode="numeric" placeholder="۰۹۱۲ ۰۰۰ ۰۰۰۰" dir="ltr" required pattern="[0-9۰-۹ ]{10,14}" />
				<button class="btn btn--light btn--sm" type="submit">ثبت</button>
			</div>
			<p class="footer__ok" role="status" hidden>شماره شما ثبت شد.</p>
		</form>
	</div>
	<div class="footer__bottom wrap">
		<p>© <span class="num"><?php echo esc_html( gmdate( 'Y' ) ); ?></span> <?php echo esc_html( lc_brand() ); ?>. <span class="en">Sneakers &amp; Runners, Iran.</span></p>
		<div class="footer__trust">
			<span class="trust-tag">زرین‌پال</span>
			<span class="trust-tag">شتاب</span>
			<?php if ( ! empty( $lc_general['enamad'] ) ) : ?>
				<span class="trust-tag trust-tag--enamad"><?php echo lc_clean_enamad( $lc_general['enamad'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- whitelisted link + image ?></span>
			<?php elseif ( ! empty( $lc_general['enamad_slot'] ) ) : ?>
				<span class="trust-tag trust-tag--slot" title="کد نماد را در «پیشخوان ← <?php echo esc_attr( lc_brand() ); ?> ← تنظیمات کلی» بچسبانید">اینماد</span>
			<?php endif; ?>
		</div>
	</div>
	<p class="footer__mega en" aria-hidden="true"><?php $p = explode( ' ', lc_brand_latin(), 2 ); ?><span class="logo__first"><?php echo esc_html( $p[0] ); ?></span><?php echo isset( $p[1] ) ? ' ' . esc_html( $p[1] ) : ''; ?></p>
</footer>
<?php
get_template_part( 'template-parts/cart-drawer' );
get_template_part( 'template-parts/bottom-nav' );
wp_footer();
?>
</body>
</html>
