<?php
/**
 * Digits (OTP login/registration) (Iran-only, SMS or password).
 * Digits keeps its own authentication, OTP, registration and recovery; the
 * theme only sends its entry points to the theme's /login/ page and fixes copy.
 */
defined( 'ABSPATH' ) || exit;

// Every Digits login/register entry point opens the theme's login page.
add_filter( 'digits_page_url', static function ( $url, $current_url, $form_type ) {
	if ( ! in_array( $form_type, [ 'login_register', 'login' ], true ) ) {
		return $url;
	}
	$page = get_page_by_path( 'login', OBJECT, 'page' );
	if ( ! $page || 'publish' !== $page->post_status ) {
		return $url;
	}
	$target = get_permalink( $page );
	if ( isset( $_GET['redirect_to'] ) ) {
		$target = add_query_arg( 'redirect_to', rawurlencode( sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) ), $target );
	}
	return $target;
}, 10, 3 );

// Digits builds the password/OTP tab captions from these exact strings.
add_filter( 'gettext', static function ( $translation, $text, $domain ) {
	if ( 'digits' !== $domain ) {
		return $translation;
	}
	if ( '<strong>Oops!</strong> The password you entered is not correct.' === $text ) {
		return 'رمز عبوری که وارد کردید اشتباه است.';
	}
	// Digits' own Persian file says «آیا هنوز عضو نشده؟»
	if ( 'Not a member yet?' === $text ) {
		return 'آیا هنوز عضو نشده‌اید؟';
	}
	if ( 'Already a member?' === $text ) {
		return 'قبلاً عضو شده‌اید؟';
	}
	// sign-in method switch (phone + password or phone + SMS code)
	$methods = [
		'Password'                         => 'رمز عبور',
		'OTP'                              => 'کد پیامکی',
		'SMS'                              => 'پیامک',
		'First Name'                       => 'نام و نام خانوادگی', // saved as one string (Persian first names can be two words)
		'<span>Send Passcode on</span> %s' => '<span>ارسال کد ورود با</span> %s',
	];
	if ( isset( $methods[ $text ] ) ) {
		return $methods[ $text ];
	}
	if ( 'Use' === $text ) {
		return '';
	}
	if ( 'Continue' === $text && is_page( 'login' ) ) {
		return 'ادامه';
	}
	return $translation;
}, 10, 3 );

/** Pages where Digits forms or checkout OTP verification are used. */
function lc_needs_digits(): bool {
	return is_page( 'login' ) || ( function_exists( 'is_checkout' ) && ( is_checkout() || is_account_page() ) );
}

// Digits loads its scripts, a password-strength library (zxcvbn), an unpinned
// CDN phone library and Google Roboto on every page. Keep them only where
// Digits runs; Roboto never (the form uses the theme's fonts, and Google Fonts
// is unreliable from Iran).
$lc_trim_digits = static function (): void {
	wp_dequeue_style( 'google-Roboto' );
	if ( is_admin() || lc_needs_digits() ) {
		return;
	}
	foreach ( [ 'digits-login-style', 'digits-style' ] as $h ) {
		wp_dequeue_style( $h );
	}
	foreach ( [ 'digits-login-script', 'digits-main-script', 'digits-frontend-script', 'libphonenumber-mobile', 'scrollTo', 'zxcvbn-async', 'password-strength-meter' ] as $h ) {
		wp_dequeue_script( $h );
	}
};
// Digits enqueues at several points, so trim at each print stage too.
add_action( 'wp_enqueue_scripts', $lc_trim_digits, 999 );
add_action( 'wp_print_styles', $lc_trim_digits, 0 );
add_action( 'wp_print_scripts', $lc_trim_digits, 0 );
add_action( 'wp_print_footer_scripts', $lc_trim_digits, 0 );
add_action( 'wp_footer', $lc_trim_digits, 0 );

add_filter( 'wp_resource_hints', static function ( array $urls ): array {
	return array_values( array_filter( $urls, static fn( $u ) => false === strpos( is_array( $u ) ? ( $u['href'] ?? '' ) : (string) $u, 'fonts.googleapis' ) ) );
} );

// Signed-in customers who open /login/ go to their account.
add_action( 'template_redirect', static function (): void {
	if ( is_page( 'login' ) && is_user_logged_in() && ! isset( $_GET['redirect_to'] ) && function_exists( 'wc_get_page_permalink' ) ) {
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}
} );

// Guests reaching My Account see the Digits login page instead of WooCommerce's form.
add_action( 'template_redirect', static function (): void {
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() && ! is_wc_endpoint_url( 'lost-password' ) && get_page_by_path( 'login' ) ) {
		wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( wc_get_page_permalink( 'myaccount' ) ), lc_login_url() ) );
		exit;
	}
}, 5 );

/*
 * Iranian mobile numbers only. Sign in with the phone + an SMS code, or the phone + a password
 * (throttled and strength-checked in inc/commerce/login-security.php).
 * Kept in code (not only in Digits settings) so the rules travel with the
 * theme to the live host. Digits field values: 0 off, 1 optional, 2 required.
 */
add_filter( 'pre_option_whitelistcountrycodes', static fn() => [ 'Iran' ] );
add_filter( 'pre_option_dig_default_ccode', static fn() => 'Iran' );
add_filter( 'pre_option_dig_login_default_tab', static fn() => 'phone' );

add_filter( 'digits_login_fields', static function ( $fields ) {
	$fields = is_array( $fields ) ? $fields : [];
	return array_merge( $fields, [
		'dig_login_email'        => 0,
		'dig_login_username'     => 0,
		'dig_login_mobilenumber' => 1,
		'dig_login_otp'          => 1,
		'dig_login_password'     => 1,
	] );
}, 20 );

add_filter( 'pre_option_dig_reg_fields', static function () {
	return [
		'dig_reg_name'         => 2,
		'dig_reg_uname'        => 0,
		'dig_reg_email'        => 0,
		'dig_reg_mobilenumber' => 2,
		'dig_reg_password'     => 1, // optional: anyone can still sign up with just the SMS code
	];
} );

// Digits pulls its phone-number library from unpkg.com at "@latest": unpinned,
// slow and often unreachable from Iran. Serve a pinned copy from the theme
// (assets/vendor, copied from the libphonenumber-js npm package, v1.11.20).
add_filter( 'script_loader_src', static function ( $src, $handle ) {
	if ( 'libphonenumber-mobile' === $handle && file_exists( LC_DIR . '/assets/vendor/libphonenumber-max.js' ) ) {
		return LC_URI . '/assets/vendor/libphonenumber-max.js?ver=1.11.20';
	}
	return $src;
}, 10, 2 );

// Lets the theme's CSS tell pages where Digits runs from the rest.
add_filter( 'body_class', static function ( array $classes ): array {
	if ( lc_needs_digits() ) {
		$classes[] = 'lc-digits';
	}
	return $classes;
} );
