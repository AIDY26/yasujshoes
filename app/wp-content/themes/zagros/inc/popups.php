<?php
/**
 * Popup manager (مدیریت سایت ← پاپ‌آپ‌ها). Each popup: image, label, title, text, button,
 * optional discount code, who sees it (everyone / first visit / returning), how often, the delay,
 * a start/end date for occasions and where it shows. The first matching popup (by «ترتیب») wins.
 * Front: assets/popup.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
	register_post_type( 'lc_popup', [
		'labels'       => [
			'name'          => 'پاپ‌آپ‌ها',
			'singular_name' => 'پاپ‌آپ',
			'menu_name'     => 'پاپ‌آپ‌ها',
			'all_items'     => 'پاپ‌آپ‌ها',
			'add_new'       => 'افزودن پاپ‌آپ',
			'add_new_item'  => 'افزودن پاپ‌آپ تازه',
			'edit_item'     => 'ویرایش پاپ‌آپ',
			'not_found'     => 'هنوز پاپ‌آپی ساخته نشده.',
		],
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'lc-site',
		'supports'     => [ 'title', 'page-attributes' ],
		'show_in_rest' => false,
	] );
} );

function lc_popup_defaults(): array {
	return [
		'img'      => 0,
		'eyebrow'  => '',
		'title'    => '',
		'text'     => '',
		'btn_text' => '',
		'btn_url'  => '',
		'code'     => '',
		'audience' => 'all',
		'freq'     => 'days',
		'days'     => 3,
		'delay'    => 5,
		'where'    => 'all',
		'start'    => '',
		'end'      => '',
	];
}

function lc_popup_meta( int $id ): array {
	return (array) get_post_meta( $id, '_lc_popup', true ) + lc_popup_defaults();
}

add_action( 'add_meta_boxes_lc_popup', static function (): void {
	add_meta_box( 'lc_popup_box', 'محتوا و قوانین نمایش', static function ( WP_Post $post ): void {
		$m = lc_popup_meta( $post->ID );
		wp_nonce_field( 'lc_popup_save', 'lc_popup_nonce' );
		$select = static function ( string $name, string $value, string $label, array $opts ): void {
			echo '<div class="lcx-field"><label><span class="lcx-label">' . esc_html( $label ) . '</span><select name="' . esc_attr( $name ) . '">';
			foreach ( $opts as $k => $v ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $value, $k, false ), esc_html( $v ) );
			}
			echo '</select></label></div>';
		};
		echo '<div class="lcx-meta"><p class="lcx-help" style="margin-bottom:14px">عنوان بالای این صفحه فقط برای شناختن پاپ‌آپ در پیشخوان است. اگر چند پاپ‌آپ هم‌زمان فعال باشند، اولین پاپ‌آپ مطابق (بر اساس «ترتیب») نمایش داده می‌شود.</p>';
		echo '<div class="lcx-grid"><div>';
		lc_field_text( 'lc_popup[eyebrow]', $m['eyebrow'], 'برچسب کوچک بالای تیتر', 'همه‌ی بخش‌ها اختیاری‌اند: می‌شود فقط عکس، فقط متن، یا هر ترکیبی گذاشت.' );
		lc_field_text( 'lc_popup[title]', $m['title'], 'تیتر', 'مثلا «۱۰٪ تخفیف خوش‌آمد»' );
		lc_field_text( 'lc_popup[text]', $m['text'], 'متن', '', 'textarea' );
		lc_field_text( 'lc_popup[code]', $m['code'], 'کد تخفیف (اختیاری)', 'باید در «ووکامرس ← کوپن‌ها» ساخته شده باشد. با یک دکمه کپی می‌شود.', 'text', [ 'dir' => 'ltr' ] );
		echo '<div class="lcx-grid">';
		lc_field_text( 'lc_popup[btn_text]', $m['btn_text'], 'متن دکمه', 'خالی = بدون دکمه' );
		lc_field_text( 'lc_popup[btn_url]', $m['btn_url'], 'لینک دکمه', '', 'url', [ 'dir' => 'ltr' ] );
		echo '</div></div><div>';
		lc_field_image( 'lc_popup[img]', (int) $m['img'], 'عکس', 'عمودی یا مربع. خالی = پاپ‌آپ بدون عکس.' );
		$select( 'lc_popup[audience]', $m['audience'], 'به چه کسی نشان داده شود', [ 'all' => 'همه‌ی بازدیدکننده‌ها', 'new' => 'فقط بازدید اول', 'returning' => 'فقط کسانی که قبلا آمده‌اند' ] );
		$select( 'lc_popup[freq]', $m['freq'], 'هر چند وقت یک بار', [ 'once' => 'فقط یک بار برای هر نفر', 'session' => 'یک بار در هر بازدید', 'days' => 'هر چند روز یک بار' ] );
		lc_field_text( 'lc_popup[days]', $m['days'], 'چند روز (برای «هر چند روز»)', '', 'number', [ 'min' => 1, 'max' => 365 ] );
		lc_field_text( 'lc_popup[delay]', $m['delay'], 'بعد از چند ثانیه باز شود', '', 'number', [ 'min' => 0, 'max' => 120, 'step' => 0.5 ] );
		$select( 'lc_popup[where]', $m['where'], 'کجا نشان داده شود', [ 'all' => 'همه‌ی صفحه‌ها (جز سبد و پرداخت)', 'home' => 'فقط صفحه‌ی اصلی' ] );
		echo '<div class="lcx-grid">';
		lc_field_text( 'lc_popup[start]', lc_dt_input( $m['start'] ), 'شروع (اختیاری)', 'به وقت تهران', 'datetime-local', [ 'dir' => 'ltr' ] );
		lc_field_text( 'lc_popup[end]', lc_dt_input( $m['end'] ), 'پایان (اختیاری)', 'برای مناسبت‌ها', 'datetime-local', [ 'dir' => 'ltr' ] );
		echo '</div></div></div></div>';
	}, 'lc_popup', 'normal', 'high' );
} );

add_action( 'save_post_lc_popup', static function ( int $post_id ): void {
	if ( ! isset( $_POST['lc_popup_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lc_popup_nonce'] ), 'lc_popup_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in  = wp_unslash( (array) ( $_POST['lc_popup'] ?? [] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised below
	$dt  = static function ( $v ): string {
		$v = trim( (string) $v );
		return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $v ) ? str_replace( 'T', ' ', substr( $v, 0, 16 ) ) : '';
	};
	$pick = static fn( $v, array $ok, string $d ) => in_array( $v, $ok, true ) ? $v : $d;
	update_post_meta( $post_id, '_lc_popup', [
		'img'      => absint( $in['img'] ?? 0 ),
		'eyebrow'  => sanitize_text_field( (string) ( $in['eyebrow'] ?? '' ) ),
		'title'    => sanitize_text_field( (string) ( $in['title'] ?? '' ) ),
		'text'     => sanitize_textarea_field( (string) ( $in['text'] ?? '' ) ),
		'btn_text' => sanitize_text_field( (string) ( $in['btn_text'] ?? '' ) ),
		'btn_url'  => esc_url_raw( (string) ( $in['btn_url'] ?? '' ) ),
		'code'     => sanitize_text_field( (string) ( $in['code'] ?? '' ) ),
		'audience' => $pick( $in['audience'] ?? '', [ 'all', 'new', 'returning' ], 'all' ),
		'freq'     => $pick( $in['freq'] ?? '', [ 'once', 'session', 'days' ], 'days' ),
		'days'     => max( 1, min( 365, absint( $in['days'] ?? 3 ) ) ),
		'delay'    => is_numeric( $in['delay'] ?? '' ) ? max( 0, min( 120, (float) $in['delay'] ) ) : 5,
		'where'    => $pick( $in['where'] ?? '', [ 'all', 'home' ], 'all' ),
		'start'    => $dt( $in['start'] ?? '' ),
		'end'      => $dt( $in['end'] ?? '' ),
	] );
} );

// List screen: status at a glance.
add_filter( 'manage_lc_popup_posts_columns', static fn( array $c ) => [ 'cb' => $c['cb'] ?? '', 'title' => 'نام', 'lc_rule' => 'برای چه کسی', 'lc_when' => 'زمان', 'lc_state' => 'وضعیت' ] );
add_action( 'manage_lc_popup_posts_custom_column', static function ( string $col, int $id ): void {
	$m = lc_popup_meta( $id );
	if ( 'lc_rule' === $col ) {
		$aud  = [ 'all' => 'همه', 'new' => 'بازدید اول', 'returning' => 'بازدیدکننده‌های قبلی' ][ $m['audience'] ];
		$freq = [ 'once' => 'یک بار', 'session' => 'هر بازدید', 'days' => 'هر ' . lc_fa( $m['days'] ) . ' روز' ][ $m['freq'] ];
		echo esc_html( $aud . '، ' . $freq . '، بعد از ' . lc_fa( $m['delay'] ) . ' ثانیه' );
	} elseif ( 'lc_when' === $col ) {
		echo esc_html( ( $m['start'] ? 'از ' . $m['start'] : '' ) . ( $m['start'] && $m['end'] ? ' ' : '' ) . ( $m['end'] ? 'تا ' . $m['end'] : '' ) ?: 'همیشه' );
	} elseif ( 'lc_state' === $col ) {
		$on = 'publish' === get_post_status( $id ) && lc_popup_in_window( $m );
		printf( '<span style="color:%s;font-weight:600">%s</span>', $on ? '#1a7f37' : '#8c8f94', $on ? 'فعال' : 'غیرفعال' );
	}
}, 10, 2 );

function lc_popup_ts( string $v ): int {
	if ( ! $v ) {
		return 0;
	}
	try {
		return ( new DateTimeImmutable( $v, new DateTimeZone( 'Asia/Tehran' ) ) )->getTimestamp();
	} catch ( Exception ) {
		return 0;
	}
}

function lc_popup_in_window( array $m ): bool {
	$now = time();
	$s   = lc_popup_ts( $m['start'] );
	$e   = lc_popup_ts( $m['end'] );
	return ( ! $s || $now >= $s ) && ( ! $e || $now < $e );
}

/** Popups that may show right now, in order. Visitor rules are checked in the browser. */
function lc_active_popups(): array {
	$out = [];
	foreach ( get_posts( [ 'post_type' => 'lc_popup', 'post_status' => 'publish', 'posts_per_page' => 10, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ], 'no_found_rows' => true ] ) as $post ) {
		$m = lc_popup_meta( $post->ID );
		// any mix of parts is fine (text only, image only, with or without code/button), just not nothing
		if ( ! lc_popup_in_window( $m ) || ( ! $m['img'] && ! $m['title'] && ! $m['text'] && ! $m['eyebrow'] && ! $m['code'] && ! $m['btn_text'] ) ) {
			continue;
		}
		$out[] = [
			'id'       => $post->ID . '-' . get_post_modified_time( 'U', true, $post ),
			'img'      => $m['img'] ? (string) wp_get_attachment_image_url( (int) $m['img'], 'large' ) : '',
			'eyebrow'  => $m['eyebrow'],
			'title'    => $m['title'],
			'text'     => $m['text'],
			'btn'      => $m['btn_text'],
			'url'      => $m['btn_url'],
			'code'     => $m['code'],
			'audience' => $m['audience'],
			'freq'     => $m['freq'],
			'days'     => (int) $m['days'],
			'delay'    => (float) $m['delay'],
			'where'    => $m['where'],
		];
	}
	return $out;
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( is_admin() || ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() ) ) ) {
		return;
	}
	$popups = lc_active_popups();
	if ( ! $popups ) {
		return;
	}
	$d = '/assets/popup/';
	wp_enqueue_style( 'lc-popup', LC_URI . $d . 'popup.css', [], (string) filemtime( LC_DIR . $d . 'popup.css' ) );
	wp_enqueue_script( 'lc-popup', LC_URI . $d . 'popup.js', [], (string) filemtime( LC_DIR . $d . 'popup.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	wp_add_inline_script( 'lc-popup', 'window.LC_POPUPS=' . wp_json_encode( $popups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . ';', 'before' );
}, 30 );
