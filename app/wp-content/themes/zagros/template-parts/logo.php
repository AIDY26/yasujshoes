<?php
/** Wordmark: the Customizer logo when one is set, else the Latin name in upper case with the peak mark. The first word is orange. */
defined( 'ABSPATH' ) || exit;
if ( get_theme_mod( 'custom_logo' ) ) {
	echo lc_logo_mark( 'logo__img' ); // phpcs:ignore WordPress.Security.EscapeOutput -- attachment markup
	return;
}
$lc_parts = explode( ' ', strtoupper( lc_brand_latin() ), 2 );
?>
<span class="logo" dir="ltr"><span class="logo__word"><span class="logo__first"><?php echo esc_html( $lc_parts[0] ); ?></span><?php echo isset( $lc_parts[1] ) ? ' ' . esc_html( $lc_parts[1] ) : ''; ?></span><svg class="logo__peak" viewBox="0 0 14 12" aria-hidden="true"><path d="M7 0 14 12H0z"/></svg></span>
