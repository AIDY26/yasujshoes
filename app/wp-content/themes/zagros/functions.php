<?php
/**
 * Zagros theme, built on the Shop Kit (Lucid Studio).
 * Presentation lives in templates + the Vite bundle (assets/dist, from src/);
 * store behaviour lives in inc/commerce (Iran-ready WooCommerce: Digits, Persian checkout, Tipax, Zarinpal);
 * Zagros-only data helpers in inc/zagros.php. No lookbook and no footer shader in this project.
 */
defined( 'ABSPATH' ) || exit;

define( 'LC_DIR', get_template_directory() );
define( 'LC_URI', get_template_directory_uri() );

require LC_DIR . '/inc/helpers.php';
require LC_DIR . '/inc/brand.php';
require LC_DIR . '/inc/setup.php';
require LC_DIR . '/inc/assets.php';
require LC_DIR . '/inc/customizer.php';
require LC_DIR . '/inc/site-admin.php';
require LC_DIR . '/inc/stories.php';
require LC_DIR . '/inc/popups.php';
require LC_DIR . '/inc/color-terms.php';
require LC_DIR . '/inc/fly-cart.php';
require LC_DIR . '/inc/categories.php';
require LC_DIR . '/inc/zagros.php';

if ( class_exists( 'WooCommerce' ) ) {
	require LC_DIR . '/inc/woocommerce.php';
	require LC_DIR . '/inc/shop-query.php';
	require LC_DIR . '/inc/commerce/checkout.php';
	require LC_DIR . '/inc/commerce/account.php';
	require LC_DIR . '/inc/commerce/order-result.php';
	require LC_DIR . '/inc/commerce/shipping.php';
}
require LC_DIR . '/inc/commerce/digits.php';
require LC_DIR . '/inc/commerce/login-security.php';
