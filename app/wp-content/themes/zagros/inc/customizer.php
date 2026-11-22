<?php
/**
 * Customizer: login photo and shop address. Everything else on the home page lives in
 * the «مدیریت سایت» admin menu (inc/site-admin.php).
 * Products, prices, stock and categories stay in WooCommerce; only editorial
 * copy and images live here.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', static function ( WP_Customize_Manager $c ): void {
	$c->add_panel( 'lc_brand_panel', [ 'title' => lc_brand(), 'priority' => 20, 'description' => 'نوار اعلان، هیرو و اسلایدر، کارت‌ها، حراج، بخش اینستاگرام، استوری‌ها و پاپ‌آپ‌ها حالا در پیشخوان ← «مدیریت سایت» هستند.' ] );

	$text = static function ( string $section, string $id, string $label, string $default = '', string $type = 'text' ) use ( $c ): void {
		$c->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'textarea' === $type ? 'sanitize_textarea_field' : ( 'url' === $type ? 'esc_url_raw' : 'sanitize_text_field' ), 'transport' => 'refresh' ] );
		$c->add_control( $id, [ 'section' => $section, 'label' => $label, 'type' => $type ] );
	};
	$image = static function ( string $section, string $id, string $label ) use ( $c ): void {
		$c->add_setting( $id, [ 'sanitize_callback' => 'absint' ] );
		$c->add_control( new WP_Customize_Media_Control( $c, $id, [ 'section' => $section, 'label' => $label, 'mime_type' => 'image' ] ) );
	};

	$c->add_section( 'lc_brand', [ 'title' => 'برند', 'panel' => 'lc_brand_panel', 'description' => 'نام فارسی همان «عنوان سایت» در تنظیمات ← عمومی است. لوگو و آیکن سایت در «هویت سایت».' ] );
	$text( 'lc_brand', 'lc_brand_latin', 'نام لاتین (واژه‌ی بزرگ هیرو، هدر، فوتر و ورود)', 'STORE' );

	$c->add_section( 'lc_login', [ 'title' => 'صفحه‌ی ورود', 'panel' => 'lc_brand_panel' ] );
	$image( 'lc_login', 'lc_login_img', 'عکس کنار فرم ورود (عمودی؛ خالی = عکس چپ هیرو)' );

	$c->add_section( 'lc_visit', [ 'title' => 'آدرس فروشگاه', 'panel' => 'lc_brand_panel', 'description' => 'دکمه‌های گوگل مپ، نشان و بلد از این مختصات ساخته می‌شوند. تصویر نقشه با tools/map/build-map.mjs ساخته شده؛ اگر فروشگاه جابه‌جا شد، آن را هم دوباره بسازید.' ] );
	$text( 'lc_visit', 'lc_city', 'شهر', '' );
	$text( 'lc_visit', 'lc_street', 'خیابان و نشانی', '' );
	$text( 'lc_visit', 'lc_landmark', 'نشانه‌ی کوتاه روی نقشه (مثلا «روبه‌روی پارک ملت»)', '' );
	$text( 'lc_visit', 'lc_map_lat', 'عرض جغرافیایی (Latitude)', '35.699700' );
	$text( 'lc_visit', 'lc_map_lng', 'طول جغرافیایی (Longitude)', '51.338000' );
} );

/** End of the store-wide sale as a timestamp (Tehran time), or 0. */
function lc_sale_end_ts(): int {
	$raw = trim( (string) ( function_exists( 'lc_home' ) ? lc_home()['sale']['end'] : get_theme_mod( 'lc_sale_end', '' ) ) );
	if ( ! $raw ) {
		return 0;
	}
	try {
		$d = new DateTimeImmutable( $raw, new DateTimeZone( 'Asia/Tehran' ) );
		return $d->getTimestamp();
	} catch ( Exception ) {
		return 0;
	}
}
