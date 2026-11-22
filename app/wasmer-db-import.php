<?php
/**
 * Loads the demo store into the app's database the first time the site is opened.
 * Wasmer creates an empty MySQL database for the app; when it has no `<prefix>options` table
 * yet, this reads the .sql file in the repo's db/ folder statement by statement and runs it.
 * A MySQL named lock stops two first visits from importing at the same time.
 * Once the tables exist it does nothing (one quick SHOW TABLES per request).
 * To start the demo over, empty the database in Wasmer's DB Explorer and open the site again.
 * Generic: made by J:\wasmer\tools\build-repo.ps1, the same file for every site.
 */
defined( 'ABSPATH' ) || exit;

// only on a web request: wp-cli (used to build the export) must never trigger an import
if ( 'cli' === PHP_SAPI ) {
	return;
}

// wp-config.php requires this file right after setting $table_prefix, in its own scope
// (which is not always the global scope), so take the prefix from here.
( static function ( string $prefix ): void {
	$files = glob( dirname( __DIR__ ) . '/db/*.sql' );
	if ( ! $files ) {
		return;
	}
	$file = $files[0];
	$like = str_replace( '_', '\\_', $prefix ) . 'options';
	mysqli_report( MYSQLI_REPORT_OFF );
	$host = DB_HOST;
	$port = null;
	if ( preg_match( '/^(.+):(\d+)$/', $host, $m ) ) {
		$host = $m[1];
		$port = (int) $m[2];
	}
	$db = mysqli_init();
	if ( ! $db ) {
		return;
	}
	$flags = defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0;
	if ( ! @$db->real_connect( $host, DB_USER, DB_PASSWORD, DB_NAME, $port, null, $flags ) ) {
		return; // WordPress shows its own database error
	}
	$db->set_charset( 'utf8mb4' );
	$has = $db->query( "SHOW TABLES LIKE '" . $db->real_escape_string( $like ) . "'" );
	if ( $has && $has->num_rows > 0 ) {
		$db->close();
		return;
	}
	$lock = $db->query( "SELECT GET_LOCK('ab_demo_import', 300)" );
	if ( ! $lock || '1' !== (string) $lock->fetch_row()[0] ) {
		$db->close();
		return;
	}
	// another visit may have finished the import while this one waited for the lock
	$has = $db->query( "SHOW TABLES LIKE '" . $db->real_escape_string( $like ) . "'" );
	if ( ! $has || 0 === $has->num_rows ) {
		@set_time_limit( 300 );
		ignore_user_abort( true ); // finish even if the first visitor gives up waiting
		$fh  = fopen( $file, 'rb' );
		$sql = '';
		$ok  = (bool) $fh;
		while ( $ok && false !== ( $line = fgets( $fh ) ) ) {
			$trim = rtrim( $line );
			if ( '' === $sql && ( '' === $trim || 0 === strpos( $trim, '--' ) ) ) {
				continue;
			}
			$sql .= $line;
			if ( ';' === substr( $trim, -1 ) ) {
				if ( ! $db->query( $sql ) ) {
					error_log( 'demo import failed: ' . $db->error . ' in: ' . substr( $sql, 0, 200 ) );
					$ok = false;
				}
				$sql = '';
			}
		}
		if ( $fh ) {
			fclose( $fh );
		}
		if ( ! $ok ) {
			// leave the database empty so the next visit tries again
			$res = $db->query( 'SHOW TABLES' );
			while ( $res && ( $row = $res->fetch_row() ) ) {
				$db->query( 'DROP TABLE IF EXISTS `' . $row[0] . '`' );
			}
		}
	}
	$db->query( "SELECT RELEASE_LOCK('ab_demo_import')" );
	$db->close();
} )( (string) ( $table_prefix ?? 'wp_' ) );
