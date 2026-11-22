<?php
/**
 * WordPress config for the yasujshoes demo on Wasmer Edge (made by J:\wasmer\tools\build-repo.ps1).
 * No secrets here: Wasmer passes the database details as environment variables (DB_HOST,
 * DB_PORT, DB_NAME, DB_USERNAME, DB_PASSWORD), and the keys and salts are derived from them,
 * so they are unique to this app and stay the same between deploys.
 * For a local test, set the same variables before starting php -S (see README.md).
 */
function ab_env( string $name, string $default = '' ): string {
	foreach ( [ getenv( $name ), $_ENV[ $name ] ?? false, $_SERVER[ $name ] ?? false ] as $v ) {
		if ( false !== $v && '' !== $v && null !== $v ) {
			return (string) $v;
		}
	}
	return $default;
}

define( 'DB_NAME', ab_env( 'DB_NAME', 'wordpress' ) );
define( 'DB_USER', ab_env( 'DB_USERNAME', ab_env( 'DB_USER', 'root' ) ) );
define( 'DB_PASSWORD', ab_env( 'DB_PASSWORD' ) );
define( 'DB_HOST', ab_env( 'DB_HOST', '127.0.0.1' ) . ( ab_env( 'DB_PORT' ) ? ':' . ab_env( 'DB_PORT' ) : '' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
// Wasmer's MySQL needs TLS; its certificate comes from a private CA, so the chain is not checked.
// DB_SSL=0 turns TLS off (only for a local test against a server without it).
if ( '0' !== ab_env( 'DB_SSL', '1' ) ) {
	define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT );
}

$ab_seed = ab_env( 'WP_SALT', DB_PASSWORD . '|' . DB_NAME . '|' . DB_HOST );
foreach ( [ 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ] as $ab_k ) {
	define( $ab_k, hash( 'sha256', $ab_k . '|' . $ab_seed ) );
}
$table_prefix = 'wp_';

// Wasmer ends HTTPS at its edge and forwards plain HTTP, so trust its forwarded protocol.
if ( 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' ) ) {
	$_SERVER['HTTPS'] = 'on';
}
// The site follows the address it is opened with (the wasmer.app address, a custom domain
// later, or localhost for a test), so the database never needs an address rewrite.
if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
	$ab_scheme = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) ? 'https' : 'http';
	define( 'WP_HOME', $ab_scheme . '://' . $_SERVER['HTTP_HOST'] );
	define( 'WP_SITEURL', WP_HOME );
}

// A demo served from a read-only package:
define( 'DISABLE_WP_CRON', true );         // no loopback cron calls
define( 'WP_HTTP_BLOCK_EXTERNAL', true );  // no calls out (update checks, licence pings): faster pages
define( 'DISALLOW_FILE_MODS', true );      // no plugin or theme installs; they would not survive a deploy
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'WC_LOG_HANDLER', 'WC_Log_Handler_DB' ); // WooCommerce logs go to the database, not to files
define( 'WP_TEMP_DIR', sys_get_temp_dir() );
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
// First visit on a new app: load the demo store into the empty database.
require_once __DIR__ . '/wasmer-db-import.php';
require_once ABSPATH . 'wp-settings.php';
