<?php
/** Fly-to-cart animation, shared by every page (the bundle and the lookbook call window.LC_FLY). */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', static function (): void {
	$file = '/assets/fly-cart/fly-cart.js';
	wp_enqueue_script( 'lc-fly-cart', LC_URI . $file, [], (string) filemtime( LC_DIR . $file ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
}, 30 );
