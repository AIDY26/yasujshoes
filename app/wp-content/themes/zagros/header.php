<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'template-parts/icons' ); ?>
<a class="skip" href="#main">رفتن به محتوا</a>
<?php
$lc_bar = lc_home()['announce'];
if ( $lc_bar['show'] && $lc_bar['text'] ) :
	$lc_bar_url = $lc_bar['url'] ? $lc_bar['url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) );
	?>
	<a class="announce" href="<?php echo esc_url( $lc_bar_url ); ?>"><span><?php echo esc_html( $lc_bar['text'] ); ?></span><?php if ( ! empty( $lc_bar['en'] ) ) : ?><span class="announce__en en"><?php echo esc_html( $lc_bar['en'] ); ?></span><?php endif; ?></a>
<?php endif; ?>
<header class="header" data-header>
	<div class="header__in wrap">
		<nav class="header__nav" aria-label="منوی اصلی"><?php lc_nav( 'primary', 'header__link' ); ?></nav>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header__logo" aria-label="<?php echo esc_attr( lc_brand() ); ?>، صفحه اصلی"><?php get_template_part( 'template-parts/logo' ); ?></a>
		<div class="header__actions">
			<?php if ( lc_size_url() ) : ?><a href="<?php echo esc_url( lc_size_url() ); ?>" class="header__link header__link--quiet desk-only">راهنمای سایز</a><?php endif; ?>
			<button class="icon-btn desk-only" type="button" data-theme-toggle aria-label="حالت روشن یا تاریک">
				<svg class="i-moon"><use href="#i-moon"/></svg><svg class="i-sun"><use href="#i-sun"/></svg>
			</button>
			<button class="icon-btn" type="button" aria-label="جستجو" aria-expanded="false" aria-controls="search" data-search-open><svg><use href="#i-search"/></svg></button>
			<a class="icon-btn desk-only" href="<?php echo esc_url( is_user_logged_in() && function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : lc_login_url() ); ?>" aria-label="<?php echo is_user_logged_in() ? 'حساب کاربری' : 'ورود و ثبت‌نام'; ?>"><svg><use href="#i-user"/></svg></a>
			<button class="icon-btn desk-only" type="button" aria-label="علاقه‌مندی‌ها" data-sheet-open="wish"><svg><use href="#i-heart"/></svg><span class="count-badge is-zero" data-wish-count>۰</span></button>
			<?php $lc_n = function_exists( 'lc_cart_count' ) ? lc_cart_count() : 0; ?>
			<button class="icon-btn" type="button" aria-label="سبد خرید" data-cart-open><svg><use href="#i-bag"/></svg><span class="count-badge<?php echo $lc_n ? '' : ' is-zero'; ?>" data-cart-count><?php echo esc_html( lc_fa( $lc_n ) ); ?></span></button>
			<button class="icon-btn mob-only" type="button" aria-label="منو" aria-expanded="false" data-sheet-open="menu"><svg><use href="#i-menu"/></svg></button>
		</div>
	</div>
	<form class="search" id="search" data-search data-soft-form action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search" hidden>
		<div class="search__in wrap">
			<svg class="icon"><use href="#i-search"/></svg>
			<input type="search" name="s" placeholder="جستجوی مدل یا برند…" aria-label="جستجو در محصولات" autocomplete="off" value="<?php echo esc_attr( get_search_query() ); ?>" />
			<input type="hidden" name="post_type" value="product" />
			<button class="btn btn--sm" type="submit">جستجو</button>
			<button class="icon-btn" type="button" data-search-close aria-label="بستن جستجو"><svg><use href="#i-close"/></svg></button>
		</div>
	</form>
</header>
<main id="main" data-template="<?php echo esc_attr( lc_template_key() ); ?>" data-view>
