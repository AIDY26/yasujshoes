<?php
/** Products → Attributes → رنگ: a colour picker per term, used for swatches and the colour filter. */
defined( 'ABSPATH' ) || exit;

$lc_color_field = static function ( $term = null ): void {
	$hex = $term ? (string) get_term_meta( $term->term_id, 'lc_hex', true ) : '#111111';
	wp_nonce_field( 'lc_hex', 'lc_hex_nonce' );
	if ( $term ) {
		echo '<tr class="form-field"><th scope="row"><label for="lc_hex">رنگ نمونه</label></th><td>';
	} else {
		echo '<div class="form-field"><label for="lc_hex">رنگ نمونه</label>';
	}
	printf( '<input type="color" id="lc_hex" name="lc_hex" value="%s" />', esc_attr( $hex ?: '#111111' ) );
	echo $term ? '<p class="description">رنگ دایره‌ی انتخاب رنگ در فروشگاه.</p></td></tr>' : '<p>رنگ دایره‌ی انتخاب رنگ در فروشگاه.</p></div>';
};
add_action( 'pa_color_add_form_fields', static fn() => $lc_color_field() );
add_action( 'pa_color_edit_form_fields', static fn( $term ) => $lc_color_field( $term ) );

$lc_color_save = static function ( int $term_id ): void {
	if ( ! isset( $_POST['lc_hex'], $_POST['lc_hex_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lc_hex_nonce'] ), 'lc_hex' ) ) {
		return;
	}
	$hex = sanitize_hex_color( wp_unslash( $_POST['lc_hex'] ) );
	if ( $hex ) {
		update_term_meta( $term_id, 'lc_hex', $hex );
	}
};
add_action( 'created_pa_color', $lc_color_save );
add_action( 'edited_pa_color', $lc_color_save );

add_filter( 'manage_edit-pa_color_columns', static function ( array $cols ): array {
	return [ 'lc_hex' => '' ] + $cols;
} );
add_filter( 'manage_pa_color_custom_column', static function ( $out, $col, $term_id ) {
	if ( 'lc_hex' !== $col ) {
		return $out;
	}
	$hex = get_term_meta( $term_id, 'lc_hex', true ) ?: '#777';
	return sprintf( '<span style="display:inline-block;width:20px;height:20px;border-radius:50%%;background:%s;box-shadow:inset 0 0 0 1px #0003"></span>', esc_attr( $hex ) );
}, 10, 3 );
