<?php
/**
 * Password sign-in, hardened.
 *
 * Phone + password now works alongside phone + SMS code (inc/commerce/digits.php). Digits checks
 * passwords through wp_authenticate(), so this file adds two things on top of Digits' own lockout:
 *
 * 1. Throttling: 5 wrong passwords for one account from one network address in 15 minutes locks
 *    that pair for 15 minutes; 20 wrong passwords from one address in an hour locks the address
 *    for an hour. Keyed per address so nobody can lock the owner out from elsewhere.
 * 2. Strong passwords wherever one is set (sign-up, reset by SMS code, My Account, admin profile):
 *    10+ characters, letters and digits, not built from the phone number or username, not a common one.
 */
defined( 'ABSPATH' ) || exit;

const LC_PASS_MIN       = 10;
const LC_PAIR_LIMIT     = 5;
const LC_PAIR_WINDOW    = 15 * MINUTE_IN_SECONDS;
const LC_ADDRESS_LIMIT  = 20;
const LC_ADDRESS_WINDOW = HOUR_IN_SECONDS;

/* ------------------------------------------------------------- throttling */

function lc_login_ip(): string {
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- validated below
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

function lc_login_keys( string $login ): array {
	$ip = lc_login_ip();
	return [
		'pair'    => 'lc_lf_p_' . md5( strtolower( trim( $login ) ) . '|' . $ip ),
		'address' => 'lc_lf_a_' . md5( $ip ),
	];
}

/** Seconds until this login/address may try again (0 = allowed). */
function lc_login_wait( string $login ): int {
	$keys = lc_login_keys( $login );
	$wait = 0;
	foreach ( [ 'pair' => LC_PAIR_LIMIT, 'address' => LC_ADDRESS_LIMIT ] as $kind => $limit ) {
		$rec = get_transient( $keys[ $kind ] );
		if ( is_array( $rec ) && $rec['n'] >= $limit ) {
			$wait = max( $wait, (int) $rec['until'] - time() );
		}
	}
	return max( 0, $wait );
}

// Refuse early (before the password is even checked) while locked.
add_filter( 'authenticate', static function ( $user, $username, $password ) {
	if ( '' === (string) $username || '' === (string) $password ) {
		return $user;
	}
	$wait = lc_login_wait( (string) $username );
	if ( $wait > 0 ) {
		return new WP_Error(
			'lc_too_many_attempts',
			sprintf( 'تعداد تلاش‌های ناموفق زیاد بود. %s دیگر دوباره امتحان کنید یا با کد پیامکی وارد شوید.', lc_fa( (string) max( 1, (int) ceil( $wait / 60 ) ) ) . ' دقیقه' )
		);
	}
	return $user;
}, 25, 3 );

add_action( 'wp_login_failed', static function ( $username ): void {
	$keys = lc_login_keys( (string) $username );
	foreach ( [ 'pair' => LC_PAIR_WINDOW, 'address' => LC_ADDRESS_WINDOW ] as $kind => $window ) {
		$rec = get_transient( $keys[ $kind ] );
		$rec = is_array( $rec ) ? $rec : [ 'n' => 0, 'until' => time() + $window ];
		$rec['n']++;
		if ( $rec['n'] === ( 'pair' === $kind ? LC_PAIR_LIMIT : LC_ADDRESS_LIMIT ) ) {
			$rec['until'] = time() + $window; // the lock runs a full window from the attempt that tripped it
		}
		set_transient( $keys[ $kind ], $rec, max( 60, (int) $rec['until'] - time() ) );
	}
} );

// A successful sign-in clears that account's counter from this address.
add_action( 'wp_login', static function ( $login ): void {
	delete_transient( lc_login_keys( (string) $login )['pair'] );
} );

/* --------------------------------------------------------- strong passwords */

/** Problems with a new password, in Persian (empty = fine). */
function lc_password_problems( string $pass, ?WP_User $user = null, string $phone = '' ): array {
	$out = [];
	if ( mb_strlen( $pass ) < LC_PASS_MIN ) {
		$out[] = sprintf( 'رمز عبور باید حداقل %s نویسه باشد.', lc_fa( LC_PASS_MIN ) );
	}
	if ( ! preg_match( '/\p{L}/u', $pass ) || ! preg_match( '/[0-9۰-۹]/u', $pass ) ) {
		$out[] = 'رمز عبور باید هم حرف و هم عدد داشته باشد.';
	}
	$lower  = mb_strtolower( $pass );
	$digits = preg_replace( '/\D/', '', strtr( $pass, [ '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9' ] ) );
	$phone  = preg_replace( '/\D/', '', $phone ?: ( $user ? (string) get_user_meta( $user->ID, 'digits_phone_no', true ) : '' ) );
	if ( $phone && strlen( $phone ) >= 7 && $digits && ( str_contains( $digits, substr( $phone, -7 ) ) ) ) {
		$out[] = 'رمز عبور نباید شماره‌ی موبایل شما باشد.';
	}
	if ( $user && strlen( $user->user_login ) >= 4 && str_contains( $lower, mb_strtolower( $user->user_login ) ) ) {
		$out[] = 'رمز عبور نباید نام کاربری شما را داشته باشد.';
	}
	$common = [ 'password', 'qwerty', '123456', '1234567890', '0987654321', 'iloveyou', 'admin', 'abc123', '111111', '000000' ];
	foreach ( $common as $w ) {
		if ( str_contains( $lower, $w ) && mb_strlen( $pass ) < mb_strlen( $w ) + 6 ) {
			$out[] = 'این رمز عبور خیلی رایج است؛ رمز دیگری انتخاب کنید.';
			break;
		}
	}
	if ( preg_match( '/^(.)\1+$/u', $pass ) ) {
		$out[] = 'رمز عبور نباید از تکرار یک نویسه ساخته شود.';
	}
	return array_values( array_unique( $out ) );
}

function lc_password_from_request( array $names ): string {
	foreach ( $names as $n ) {
		if ( isset( $_POST[ $n ] ) && '' !== $_POST[ $n ] ) { // phpcs:ignore WordPress.Security.NonceVerification -- each caller's own form checks its nonce
			return (string) wp_unslash( $_POST[ $n ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- passwords are not sanitised
		}
	}
	return '';
}

// Digits sign-up (the password is optional; only checked when one is given).
add_filter( 'digits_registration_errors', static function ( $errors, $login = '', $email = '' ) {
	$pass = lc_password_from_request( [ 'digits_reg_password', 'password' ] );
	if ( '' !== $pass && $errors instanceof WP_Error ) {
		$phone = (string) ( $_POST['digits_reg_mobile'] ?? $_POST['mobile/email'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		foreach ( lc_password_problems( $pass, null, $phone ) as $i => $msg ) {
			$errors->add( 'lc_weak_password_' . $i, $msg );
		}
	}
	return $errors;
}, 10, 3 );

// Reset with an SMS code (Digits) and WordPress's own reset form.
add_action( 'validate_password_reset', static function ( $errors, $user ) {
	$pass = lc_password_from_request( [ 'password', 'pass1' ] );
	if ( '' !== $pass && $user instanceof WP_User ) {
		foreach ( lc_password_problems( $pass, $user ) as $i => $msg ) {
			$errors->add( 'lc_weak_password_' . $i, $msg );
		}
	}
}, 10, 2 );

// WooCommerce My Account → account details.
add_action( 'woocommerce_save_account_details_errors', static function ( $errors, $user ) {
	$pass = lc_password_from_request( [ 'password_1' ] );
	if ( '' !== $pass ) {
		$wp_user = get_user_by( 'id', is_object( $user ) && isset( $user->ID ) ? (int) $user->ID : get_current_user_id() );
		foreach ( lc_password_problems( $pass, $wp_user ?: null ) as $i => $msg ) {
			$errors->add( 'lc_weak_password_' . $i, $msg );
		}
	}
}, 10, 2 );

// Admin → Users → profile.
add_action( 'user_profile_update_errors', static function ( $errors, $update, $user ) {
	$pass = lc_password_from_request( [ 'pass1' ] );
	if ( '' !== $pass ) {
		$wp_user = ! empty( $user->ID ) ? get_user_by( 'id', (int) $user->ID ) : null;
		foreach ( lc_password_problems( $pass, $wp_user ?: null ) as $i => $msg ) {
			$errors->add( 'lc_weak_password_' . $i, $msg );
		}
	}
}, 10, 3 );
