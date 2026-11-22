<?php
/** Phone tab bar and the persistent sheets: categories, menu, wishlist, quick add. */
defined( 'ABSPATH' ) || exit;
$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$cats = function_exists( 'lc_categories' ) ? lc_categories() : [];
$tpl  = lc_template_key();
$n    = function_exists( 'lc_cart_count' ) ? lc_cart_count() : 0;
?>
<nav class="tabbar" aria-label="ناوبری پایین">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tabbar__item<?php echo 'home' === $tpl ? ' is-on' : ''; ?>" data-tab="home"><svg class="icon"><use href="#i-home"/></svg><span>خانه</span></a>
	<button type="button" class="tabbar__item" data-sheet-open="cats" data-tab="cats"><svg class="icon"><use href="#i-grid"/></svg><span>دسته‌بندی</span></button>
	<a href="<?php echo esc_url( $shop ); ?>" class="tabbar__item tabbar__item--shop<?php echo 'shop' === $tpl ? ' is-on' : ''; ?>" data-tab="shop"><span class="tabbar__peak"><svg viewBox="0 0 14 12" aria-hidden="true"><path d="M7 0 14 12H0z"/></svg></span><span>فروشگاه</span></a>
	<button type="button" class="tabbar__item" data-sheet-open="wish" data-tab="wish"><svg class="icon"><use href="#i-heart"/></svg><span>علاقه‌مندی</span></button>
	<button type="button" class="tabbar__item" data-cart-open data-tab="cart"><svg class="icon"><use href="#i-bag"/></svg><span>سبد</span><span class="count-badge<?php echo $n ? '' : ' is-zero'; ?>" data-cart-count><?php echo esc_html( lc_fa( $n ) ); ?></span></button>
</nav>

<div class="sheet" data-sheet="cats" role="dialog" aria-modal="true" aria-label="دسته‌بندی‌ها" hidden>
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head"><h2 class="sheet__title"><span class="en">SHOP BY</span> دسته‌بندی</h2><button class="icon-btn" type="button" data-sheet-close aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
	<ul class="catlist">
		<li><a href="<?php echo esc_url( $shop ); ?>"><span>همه کفش‌ها</span><span class="en">All</span></a></li>
		<?php foreach ( $cats as $c ) : ?>
			<li><a href="<?php echo esc_url( $c['url'] ); ?>"><img src="<?php echo esc_url( $c['img'] ); ?>" alt="" width="56" height="56" loading="lazy" /><span><?php echo esc_html( $c['name'] ); ?></span><span class="en"><?php echo esc_html( lc_category_en( $c['slug'] ) ); ?></span></a></li>
		<?php endforeach; ?>
	</ul>
</div>

<div class="sheet" data-sheet="menu" role="dialog" aria-modal="true" aria-label="منو" hidden>
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head"><h2 class="sheet__title"><span class="en">MENU</span></h2><button class="icon-btn" type="button" data-sheet-close aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
	<nav class="menulist" aria-label="منوی موبایل">
		<?php foreach ( lc_menu_items() as $item ) : ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?><span class="en"><?php echo esc_html( $item['en'] ); ?></span></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( is_user_logged_in() && function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : lc_login_url() ); ?>" data-hard><?php echo is_user_logged_in() ? 'حساب کاربری' : 'ورود | ثبت‌نام'; ?><span class="en">Account</span></a>
	</nav>
	<button class="btn btn--ghost btn--block" type="button" data-theme-toggle>تغییر حالت روشن و تاریک</button>
</div>

<div class="sheet" data-sheet="wish" role="dialog" aria-modal="true" aria-label="علاقه‌مندی‌ها" hidden>
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head"><h2 class="sheet__title"><span class="en">SAVED</span> علاقه‌مندی‌ها</h2><button class="icon-btn" type="button" data-sheet-close aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
	<ul class="wishlist" data-wish-list></ul>
	<p class="sheet__empty" data-wish-empty>لیست علاقه‌مندی شما خالی است. روی قلب هر محصول بزنید تا اینجا ذخیره شود.</p>
</div>

<div class="sheet sheet--quick" data-sheet="quick" role="dialog" aria-modal="true" aria-labelledby="quick-title" hidden>
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head"><h2 class="sheet__title" id="quick-title" data-quick-title>انتخاب سایز</h2><button class="icon-btn" type="button" data-sheet-close aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
	<div class="quick" data-quick-body></div>
</div>

<div class="toast" data-toast role="status" aria-live="polite"></div>
