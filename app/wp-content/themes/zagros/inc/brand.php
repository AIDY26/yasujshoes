<?php
/**
 * Brand identity. Everything a new store changes about its name and logo comes from here:
 *   - Settings → General → Site Title         → lc_brand()       (admin menu, page titles, copy)
 *   - Customizer → Site Identity → Logo        → lc_logo_mark()   (header, footer, login, post byline)
 *   - Customizer → Site Identity → Site Icon   → favicon
 *   - Customizer → برند → نام لاتین            → lc_brand_latin() (big Latin wordmark: header, hero, footer, login, lookbook)
 * Without a logo the theme falls back to assets/img/mark.svg; replace that file per project.
 */
defined( 'ABSPATH' ) || exit;

/** Store name for admin labels and visible copy. */
function lc_brand(): string {
	$name = (string) apply_filters( 'lc_brand', get_bloginfo( 'name' ) );
	return '' !== trim( $name ) ? $name : 'فروشگاه';
}

/** Latin wordmark (dir="ltr" spots in the design). */
function lc_brand_latin(): string {
	return (string) apply_filters( 'lc_brand_latin', lc_opt( 'lc_brand_latin', 'STORE' ) );
}

/** URL of the logo image: the Customizer logo when set, else the bundled mark. */
function lc_mark_url(): string {
	$id  = (int) get_theme_mod( 'custom_logo' );
	$url = $id ? (string) wp_get_attachment_image_url( $id, 'medium' ) : '';
	return $url ?: LC_URI . '/assets/img/mark.svg';
}

/** Logo markup for inline spots: the Customizer logo as <img>, else the bundled SVG inline (styleable with currentColor). */
function lc_logo_mark( string $class = '' ): string {
	$id = (int) get_theme_mod( 'custom_logo' );
	if ( $id ) {
		return (string) wp_get_attachment_image( $id, 'medium', false, [ 'class' => trim( 'lc-logo ' . $class ), 'alt' => '', 'loading' => 'eager', 'decoding' => 'async' ] );
	}
	static $svg = null;
	if ( null === $svg ) {
		$file = LC_DIR . '/assets/img/mark.svg';
		$svg  = is_readable( $file ) ? (string) file_get_contents( $file ) : '';
	}
	return $svg;
}
