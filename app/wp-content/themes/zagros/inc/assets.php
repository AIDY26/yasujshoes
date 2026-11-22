<?php
/**
 * Loads the Vite bundle.
 * - theme/.vite-hot exists (npm run wp:dev): modules come from the dev server (HMR).
 * - otherwise: hashed files from assets/dist/manifest.json.
 */
defined( 'ABSPATH' ) || exit;

const LC_ENTRY = 'src/js/wp.js';

function lc_vite_hot(): ?string {
	$file = LC_DIR . '/.vite-hot';
	if ( ! is_readable( $file ) ) {
		return null;
	}
	$url = trim( (string) file_get_contents( $file ) );
	return $url ?: null;
}

function lc_vite_manifest(): array {
	static $manifest = null;
	if ( null === $manifest ) {
		$path     = LC_DIR . '/assets/dist/manifest.json';
		$manifest = is_readable( $path ) ? (array) json_decode( (string) file_get_contents( $path ), true ) : [];
	}
	return $manifest;
}

/** Cache-busting version for a built file: its modified time, so edits reach browsers right away. */
function lc_dist_ver( string $file ): string {
	$path = LC_DIR . '/assets/dist/' . $file;
	return is_readable( $path ) ? (string) filemtime( $path ) : '1';
}

add_action( 'wp_enqueue_scripts', static function (): void {
	$hot = lc_vite_hot();
	if ( $hot ) {
		wp_enqueue_script( 'lc-vite-client', $hot . '/@vite/client', [], null, false );
		wp_enqueue_script( 'lc-app', $hot . '/' . LC_ENTRY, [], null, true );
	} else {
		$entry = lc_vite_manifest()[ LC_ENTRY ] ?? null;
		if ( ! $entry ) {
			return;
		}
		$base = LC_URI . '/assets/dist/';
		foreach ( $entry['css'] ?? [] as $i => $css ) {
			wp_enqueue_style( 'lc-app-' . $i, $base . $css, [], lc_dist_ver( $css ) );
		}
		wp_enqueue_script( 'lc-app', $base . $entry['file'], [], lc_dist_ver( $entry['file'] ), true );
	}
	wp_localize_script( 'lc-app', 'LC_WP', lc_frontend_config() );
}, 20 );

// Vite emits ES modules.
add_filter( 'script_loader_tag', static function ( string $tag, string $handle ): string {
	if ( in_array( $handle, [ 'lc-app', 'lc-vite-client' ], true ) ) {
		$tag = str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}, 10, 2 );

/** Data the frontend needs: Store API, pages and URL rules for soft navigation. */
function lc_frontend_config(): array {
	$wc = function_exists( 'wc_get_page_permalink' );
	return [
		'home'       => home_url( '/' ),
		'storeApi'   => esc_url_raw( rest_url( 'wc/store/v1' ) ),
		'storeNonce' => wp_create_nonce( 'wc_store_api' ),
		'shopUrl'    => $wc ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'cartUrl'    => $wc ? wc_get_cart_url() : '',
		'checkoutUrl'=> $wc ? wc_get_checkout_url() : '',
		'accountUrl' => $wc ? wc_get_page_permalink( 'myaccount' ) : '',
		// pages that always load normally (payment, OTP and account scripts need a real page load)
		'hardPaths'  => array_values( array_filter( [
			$wc ? wp_make_link_relative( wc_get_cart_url() ) : '',
			$wc ? wp_make_link_relative( wc_get_checkout_url() ) : '',
			$wc ? wp_make_link_relative( wc_get_page_permalink( 'myaccount' ) ) : '',
			wp_make_link_relative( lc_login_url() ),
			'/wp-admin', '/wp-login.php', '/wp-json',
		] ) ),
		'freeShipping' => function_exists( 'lc_free_shipping' ) ? lc_free_shipping() : 0,
		'sizeUrl'    => function_exists( 'lc_size_url' ) ? lc_size_url() : '',
		'i18n'       => [
			'added'    => 'به سبد اضافه شد',
			'removed'  => 'از سبد برداشته شد',
			'error'    => 'مشکلی پیش آمد. دوباره تلاش کن.',
			'loading'  => 'در حال بارگذاری',
			'toman'    => 'تومان',
		],
	];
}

// Keep the front lean: block styles only where blocks render.
add_action( 'wp_enqueue_scripts', static function (): void {
	if ( ! is_singular() || ! has_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
	wp_dequeue_style( 'wc-blocks-style' );
}, 100 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Back/forward restores the scroll position the soft-navigation router would otherwise lose.
add_action( 'wp_enqueue_scripts', static function (): void {
	$file = '/assets/router/scroll-memory.js';
	wp_enqueue_script( 'lc-scroll-memory', LC_URI . $file, [], (string) filemtime( LC_DIR . $file ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
}, 21 );

// Shared UI fixes (pill alignment) and the shop sort menu.
add_action( 'wp_enqueue_scripts', static function (): void {
	// Zagros: the bundle styles every control; the kit's Lucifer-era ui.css and sort.js are not loaded
}, 22 );

// Home hero slider (loaded everywhere: the home page can arrive by soft navigation).
add_action( 'wp_enqueue_scripts', static function (): void {
	// Zagros: the turntable hero lives in the bundle (src/js/modules/hero.js); the kit's photo-stack slider is not loaded
}, 23 );
