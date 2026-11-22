<?php
/**
 * «مدیریت سایت» admin menu: home page settings (announcement bar, hero slider, wide cards,
 * sale, Instagram section, general) stored in one option, `lc_home`.
 * Stories and popups are post types listed under the same menu (inc/stories.php, inc/popups.php).
 *
 * The first values come from the old Customizer settings, so nothing changes until the owner saves.
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ data */

/** Gallery items from old single-image theme mods. */
function lc_home_items( array $ids ): array {
	return array_values( array_map( static fn( $id ) => [ 'img' => (int) $id, 'product' => 0 ], array_filter( array_map( 'absint', $ids ) ) ) );
}

function lc_home_defaults(): array {
	// raw values: get_theme_mod() would run this file's own theme_mod filter and loop back here
	$mods = (array) get_theme_mods();
	$mod  = static fn( string $k, $d = '' ) => $mods[ $k ] ?? $d;
	$cat = static function ( string $url ): int {
		if ( preg_match( '#/product-category/([^/]+)#', $url, $m ) ) {
			$t = get_term_by( 'slug', rawurldecode( $m[1] ), 'product_cat' );
			return $t ? (int) $t->term_id : 0;
		}
		return 0;
	};
	return [
		'announce' => [
			'show' => 1,
			'text' => (string) $mod( 'lc_announce_text', 'ارسال رایگان برای خریدهای بالای ۳ میلیون تومان' ),
			'en'   => 'Free shipping over 3M',
			'url'  => (string) $mod( 'lc_announce_url', '' ),
		],
		'general'  => [
			'instagram'     => (string) $mod( 'lc_instagram_url', 'https://instagram.com/' ),
			'story_seconds' => 5,
			'intro'         => 1,
			'enamad'        => '',
			'enamad_slot'   => 1,
		],
		'hero'     => [
			'line1'      => (string) $mod( 'lc_hero_line1', 'کتونی و کفش رانینگ اورجینال' ),
			'line2'      => '',
			'lead'       => (string) $mod( 'lc_hero_lead', '' ),
			'btn_text'   => 'دیدن محصولات',
			'btn_url'    => '',
			'insta_text' => 'اینستاگرام',
			'interval'   => 6,
			'right'      => lc_home_items( [ $mod( 'lc_hero_img_a' ) ] ),
			'left'       => lc_home_items( [ $mod( 'lc_hero_img_b' ) ] ),
		],
		'cards'    => array_map( static fn( int $n ) => [
			'title' => (string) $mod( "lc_banner{$n}_title", '' ),
			'sub'   => (string) $mod( "lc_banner{$n}_sub", '' ),
			'img'   => (int) $mod( "lc_banner{$n}_img", 0 ),
			'cat'   => $cat( (string) $mod( "lc_banner{$n}_url", '' ) ),
		], [ 1, 2 ] ),
		'sale'     => [
			'show'     => 1,
			'title'    => (string) $mod( 'lc_sale_title', 'حراج' ),
			'sub'      => '',
			'end'      => (string) $mod( 'lc_sale_end', '' ),
			'img'      => 0,
			'products' => [],
		],
		'spot'     => [ 'product' => 0, 'notes' => [], 'stats' => [] ],
		'insta'    => [
			'title'    => 'محصولات تازه اول در اینستاگرام می‌آیند',
			'text'     => 'عکس‌های تن‌خور، موجودی سایزها و تخفیف‌های کوتاه را آن‌جا می‌گذاریم.',
			'btn_text' => 'دنبال کردن در اینستاگرام',
			'btn_url'  => '',
			'items'    => lc_home_items( array_map( static fn( $n ) => $mod( "lc_insta_img{$n}" ), range( 1, 6 ) ) ),
		],
	];
}

/** Saved settings over the defaults (section by section, so a saved list replaces the default list). */
function lc_home(): array {
	static $data = null;
	if ( null === $data ) {
		$saved = (array) get_option( 'lc_home', [] );
		$data  = [];
		foreach ( lc_home_defaults() as $section => $defaults ) {
			$data[ $section ] = isset( $saved[ $section ] ) && is_array( $saved[ $section ] )
				? ( array_is_list( $defaults ) ? $saved[ $section ] : $saved[ $section ] + $defaults )
				: $defaults;
		}
	}
	return $data;
}

function lc_home_url( string $url, string $fallback = '' ): string {
	return $url ? $url : ( $fallback ? $fallback : home_url( '/' ) );
}

/** Instagram address: the admin setting wins over the old Customizer one everywhere it is read. */
add_filter( 'theme_mod_lc_instagram_url', static fn( $v ) => lc_home()['general']['instagram'] ?: $v );

/* ------------------------------------------------------------------ menu */

add_action( 'admin_menu', static function (): void {
	add_menu_page( lc_brand(), lc_brand(), 'manage_options', 'lc-site', 'lc_home_screen', 'dashicons-store', 3 );
	add_submenu_page( 'lc-site', 'صفحه‌ی اصلی', 'صفحه‌ی اصلی', 'manage_options', 'lc-site', 'lc_home_screen' );
}, 9 );

function lc_admin_assets(): void {
	wp_enqueue_media();
	wp_enqueue_script( 'wc-enhanced-select' );
	wp_enqueue_style( 'woocommerce_admin_styles' );
	wp_enqueue_script( 'lc-admin', LC_URI . '/assets/admin/admin.js', [ 'jquery', 'jquery-ui-sortable' ], (string) filemtime( LC_DIR . '/assets/admin/admin.js' ), true );
	wp_enqueue_style( 'lc-admin', LC_URI . '/assets/admin/admin.css', [], (string) filemtime( LC_DIR . '/assets/admin/admin.css' ) );
}
add_action( 'admin_enqueue_scripts', static function ( string $hook ): void {
	$screen = get_current_screen();
	if ( 'toplevel_page_lc-site' === $hook || ( $screen && in_array( $screen->post_type, [ 'lc_story', 'lc_popup' ], true ) && 'post' === $screen->base ) ) {
		lc_admin_assets();
	}
}, 20 );

/* ---------------------------------------------------------------- fields */

function lc_field_text( string $name, $value, string $label, string $help = '', string $type = 'text', array $attr = [] ): void {
	$extra = '';
	foreach ( $attr as $k => $v ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( (string) $v ) );
	}
	printf(
		'<div class="lcx-field"><label><span class="lcx-label">%s</span>%s</label>%s</div>',
		esc_html( $label ),
		'textarea' === $type
			? sprintf( '<textarea name="%s"%s%s>%s</textarea>', esc_attr( $name ), isset( $attr['rows'] ) ? '' : ' rows="3"', $extra, esc_textarea( (string) $value ) )
			: sprintf( '<input type="%s" name="%s" value="%s"%s />', esc_attr( $type ), esc_attr( $name ), esc_attr( (string) $value ), $extra ),
		$help ? '<p class="lcx-help">' . esc_html( $help ) . '</p>' : ''
	);
}

function lc_field_toggle( string $name, $value, string $label ): void {
	printf(
		'<div class="lcx-field lcx-field--toggle"><input type="hidden" name="%1$s" value="0" /><label class="lcx-toggle"><input type="checkbox" name="%1$s" value="1"%2$s /><span></span>%3$s</label></div>',
		esc_attr( $name ),
		checked( (int) $value, 1, false ),
		esc_html( $label )
	);
}

function lc_field_image( string $name, int $id, string $label, string $help = '' ): void {
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	printf(
		'<div class="lcx-field"><span class="lcx-label">%s</span><div class="lcx-image%s"><input type="hidden" name="%s" value="%d" /><img src="%s" alt="" /><button type="button" class="button lcx-image__pick">انتخاب عکس</button><button type="button" class="button-link lcx-image__clear">برداشتن</button></div>%s</div>',
		esc_html( $label ),
		$src ? ' has-img' : '',
		esc_attr( $name ),
		$id,
		esc_url( (string) $src ),
		$help ? '<p class="lcx-help">' . esc_html( $help ) . '</p>' : ''
	);
}

/** Sortable list of images, each with an optional linked WooCommerce product. */
function lc_field_gallery( string $name, array $items, string $label, string $help = '', bool $products = true ): void {
	echo '<div class="lcx-field"><span class="lcx-label">' . esc_html( $label ) . '</span>';
	if ( $help ) {
		echo '<p class="lcx-help">' . esc_html( $help ) . '</p>';
	}
	printf( '<div class="lcx-gallery" data-name="%s" data-products="%d"><ul class="lcx-gallery__list">', esc_attr( $name ), $products ? 1 : 0 );
	foreach ( $items as $it ) {
		$img = (int) ( $it['img'] ?? 0 );
		if ( ! $img ) {
			continue;
		}
		lc_gallery_row( $name, $img, (int) ( $it['product'] ?? 0 ), $products );
	}
	echo '</ul><button type="button" class="button lcx-gallery__add">+ افزودن عکس</button></div></div>';
}

function lc_gallery_row( string $name, int $img, int $product, bool $products ): void {
	$p = $product && function_exists( 'wc_get_product' ) ? wc_get_product( $product ) : null;
	echo '<li class="lcx-row"><span class="lcx-row__handle" title="جابه‌جا کردن" aria-hidden="true">⋮⋮</span>';
	printf( '<img src="%s" alt="" /><input type="hidden" name="%s[img][]" value="%d" />', esc_url( (string) wp_get_attachment_image_url( $img, 'thumbnail' ) ), esc_attr( $name ), $img );
	if ( $products ) {
		printf(
			'<select class="wc-product-search lcx-row__product" name="%s[product][]" data-placeholder="لینک به محصول (اختیاری)" data-allow_clear="true" data-action="woocommerce_json_search_products" data-exclude_type="variation"><option value=""></option>%s</select>',
			esc_attr( $name ),
			$p ? sprintf( '<option value="%d" selected="selected">%s</option>', $product, esc_html( wp_strip_all_tags( $p->get_formatted_name() ) ) ) : ''
		);
	}
	echo '<button type="button" class="lcx-row__remove" aria-label="برداشتن">×</button></li>';
}

function lc_field_products( string $name, array $ids, string $label, string $help = '' ): void {
	echo '<div class="lcx-field"><span class="lcx-label">' . esc_html( $label ) . '</span>';
	printf( '<select class="wc-product-search" multiple="multiple" style="width:100%%" name="%s[]" data-placeholder="نام محصول را بنویس…" data-action="woocommerce_json_search_products" data-exclude_type="variation">', esc_attr( $name ) );
	foreach ( $ids as $id ) {
		$p = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $id ) : null;
		if ( $p ) {
			printf( '<option value="%d" selected="selected">%s</option>', (int) $id, esc_html( wp_strip_all_tags( $p->get_formatted_name() ) ) );
		}
	}
	echo '</select>' . ( $help ? '<p class="lcx-help">' . esc_html( $help ) . '</p>' : '' ) . '</div>';
}

function lc_field_category( string $name, int $value, string $label ): void {
	$terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ] );
	echo '<div class="lcx-field"><label><span class="lcx-label">' . esc_html( $label ) . '</span><select name="' . esc_attr( $name ) . '"><option value="0">فروشگاه (همه‌ی محصولات)</option>';
	foreach ( is_wp_error( $terms ) ? [] : $terms as $t ) {
		printf( '<option value="%d"%s>%s</option>', (int) $t->term_id, selected( $value, $t->term_id, false ), esc_html( ( $t->parent ? '— ' : '' ) . $t->name ) );
	}
	echo '</select></label></div>';
}

/** 'Y-m-d H:i' (Tehran) ⇄ the value a datetime-local input wants. */
function lc_dt_input( string $v ): string {
	return $v ? str_replace( ' ', 'T', substr( $v, 0, 16 ) ) : '';
}

/* --------------------------------------------------------------- screen */

function lc_home_screen(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$h    = lc_home();
	$tabs = [ 'announce' => 'نوار اعلان', 'hero' => 'اسلایدر مدل‌ها', 'sale' => 'حراج', 'spot' => 'کفش ماه', 'insta' => 'عکس‌ها و اینستاگرام', 'general' => 'تنظیمات کلی' ];
	?>
	<div class="wrap lcx">
		<h1>صفحه‌ی اصلی</h1>
		<p class="lcx-intro">تازه‌های فروشگاه و دسته‌ها خودکار از ووکامرس می‌آیند. استوری‌ها و پاپ‌آپ‌ها از منوی کناری همین بخش مدیریت می‌شوند. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">دیدن صفحه‌ی اصلی</a></p>
		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>ذخیره شد.</p></div>
		<?php endif; ?>
		<nav class="lcx-tabs" role="tablist">
			<?php foreach ( $tabs as $k => $label ) : ?>
				<a href="#<?php echo esc_attr( $k ); ?>" class="lcx-tab" role="tab" data-tab="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lcx-form">
			<input type="hidden" name="action" value="lc_save_home" />
			<input type="hidden" name="lc_tab" value="" class="lcx-current-tab" />
			<?php wp_nonce_field( 'lc_save_home' ); ?>

			<section class="lcx-panel" data-panel="announce">
				<h2>نوار اعلان بالای صفحه</h2>
				<?php
				lc_field_toggle( 'lc[announce][show]', $h['announce']['show'], 'نوار نشان داده شود' );
				lc_field_text( 'lc[announce][text]', $h['announce']['text'], 'متن' );
				lc_field_text( 'lc[announce][en]', $h['announce']['en'] ?? '', 'متن انگلیسی کنارش', 'کوتاه، مثلا Free shipping over 3M. خالی بماند، نمایش داده نمی‌شود.', 'text', [ 'dir' => 'ltr' ] );
				lc_field_text( 'lc[announce][url]', $h['announce']['url'], 'لینک', 'خالی بماند، به حراجی‌های فروشگاه می‌رود.', 'url', [ 'dir' => 'ltr' ] );
				?>
			</section>

			<section class="lcx-panel" data-panel="hero">
				<h2>اسلایدر مدل‌ها (بالای صفحه اصلی)</h2>
				<p class="lcx-intro">هر ردیف یک مدل است: عکس کفش بدون پس‌زمینه (PNG یا WebP شفاف، افقی) و محصولی که نشان می‌دهد. اسم محصول با حروف درشت پشت کفش می‌آید، رنگ‌ها و قیمت از ووکامرس خوانده می‌شوند و سایز وسطِ موجودی روی سکو نشان داده می‌شود. خالی بماند، جدیدترین محصولات می‌آیند.</p>
				<?php
				lc_field_gallery( 'lc[hero][right]', $h['hero']['right'], 'مدل‌ها', 'با کشیدن مرتب کن. اولین ردیف اول نمایش داده می‌شود.' );
				lc_field_text( 'lc[hero][interval]', $h['hero']['interval'], 'جابه‌جایی خودکار هر چند ثانیه', 'وقتی نشانگر روی کفش است یا صفحه دیده نمی‌شود، اسلایدر می‌ایستد.', 'number', [ 'min' => 2, 'max' => 30, 'step' => 0.5 ] );
				lc_field_text( 'lc[hero][line1]', $h['hero']['line1'], 'جمله کوتاه گوشه هیرو', 'مثلا «کتونی و کفش رانینگ اورجینال».' );
				?>
			</section>

			
			<section class="lcx-panel" data-panel="sale">
				<h2>بخش حراج</h2>
				<?php
				lc_field_toggle( 'lc[sale][show]', $h['sale']['show'], 'بخش حراج نشان داده شود' );
				lc_field_text( 'lc[sale][title]', $h['sale']['title'], 'عنوان' );
				lc_field_text( 'lc[sale][sub]', $h['sale']['sub'], 'زیرعنوان', 'خالی بماند، بیشترین درصد تخفیف خودکار نوشته می‌شود («تا ۲۵٪ تخفیف»).' );
				lc_field_text( 'lc[sale][end]', lc_dt_input( $h['sale']['end'] ), 'پایان حراج (شمارش معکوس)', 'به وقت تهران. خالی یا گذشته باشد، شمارش معکوس پنهان می‌شود.', 'datetime-local', [ 'dir' => 'ltr' ] );
				lc_field_image( 'lc[sale][img]', (int) ( $h['sale']['img'] ?? 0 ), 'عکس کفش روی کارت', 'کفش بدون پس‌زمینه (PNG یا WebP شفاف، افقی). خالی بماند، عکس اولین محصول حراج می‌آید.' );
				lc_field_products( 'lc[sale][products]', (array) $h['sale']['products'], 'محصولات حراج', 'به همین ترتیب نمایش داده می‌شوند. خالی بماند، محصولات تخفیف‌دار ووکامرس خودکار می‌آیند.' );
				?>
			</section>

			<section class="lcx-panel" data-panel="spot">
				<h2>کفش ماه (PICK OF THE MONTH)</h2>
				<p class="lcx-intro">یک محصول با سه دلیل کوتاه و سه عدد. محصول خالی بماند، پرفروش‌ترین محصول می‌آید؛ دلیل‌ها خالی بمانند، ویژگی‌های نمایشی محصول؛ عددها خالی بمانند، وزن و بازه‌ی سایز و تعداد رنگ.</p>
				<?php
				lc_field_products( 'lc[spot][product]', array_filter( [ (int) $h['spot']['product'] ] ), 'محصول', 'فقط یک محصول. اگر چند تا انتخاب شود، اولی استفاده می‌شود.' );
				for ( $i = 0; $i < 3; $i++ ) {
					$n = $h['spot']['notes'][ $i ] ?? [ 'k' => '', 'v' => '' ];
					lc_field_text( "lc[spot][notes][$i][k]", $n['k'], 'دلیل ' . lc_fa( $i + 1 ) . ': تیتر' );
					lc_field_text( "lc[spot][notes][$i][v]", $n['v'], 'دلیل ' . lc_fa( $i + 1 ) . ': توضیح', '', 'textarea' );
				}
				for ( $i = 0; $i < 3; $i++ ) {
					$s = $h['spot']['stats'][ $i ] ?? [ 'v' => '', 'u' => '', 'k' => '' ];
					echo '<div class="lcx-grid">';
					lc_field_text( "lc[spot][stats][$i][v]", $s['v'], 'عدد ' . lc_fa( $i + 1 ), 'مثلا 240', 'text', [ 'dir' => 'ltr' ] );
					lc_field_text( "lc[spot][stats][$i][u]", $s['u'], 'واحد', 'مثلا g یا mm', 'text', [ 'dir' => 'ltr' ] );
					lc_field_text( "lc[spot][stats][$i][k]", $s['k'], 'عنوان', 'مثلا وزن هر لنگه' );
					echo '</div>';
				}
				?>
			</section>

			<section class="lcx-panel" data-panel="insta">
				<h2>عکس‌های صفحه اصلی و بخش اینستاگرام</h2>
				<?php
								lc_field_text( 'lc[insta][text]', $h['insta']['text'], 'متن بخش اینستاگرام', '', 'textarea' );
				?>
				<div class="lcx-grid">
					<?php
					lc_field_text( 'lc[insta][btn_text]', $h['insta']['btn_text'], 'متن دکمه', 'خالی = بدون دکمه' );
										?>
				</div>
				<?php lc_field_gallery( 'lc[insta][items]', $h['insta']['items'], 'شش عکس', 'عکس‌های روی پا و لایف‌استایل. سه تای اول داخل جمله بزرگ صفحه اصلی، چهار تای اول در موزاییک کنار کارت حراج، و پنجم و ششم در بخش اینستاگرام می‌آیند. عکس اول کنار فرم ورود هم استفاده می‌شود.' ); ?>
			</section>

			<section class="lcx-panel" data-panel="general">
				<h2>تنظیمات کلی</h2>
				<?php
				lc_field_text( 'lc[general][instagram]', $h['general']['instagram'], 'آدرس صفحه‌ی اینستاگرام', 'در هیرو، بخش اینستاگرام و پایین صفحه استفاده می‌شود.', 'url', [ 'dir' => 'ltr' ] );
				lc_field_text( 'lc[general][story_seconds]', $h['general']['story_seconds'], 'مدت نمایش هر استوری (ثانیه)', '', 'number', [ 'min' => 2, 'max' => 20, 'step' => 0.5 ] );
				lc_field_toggle( 'lc[general][intro]', $h['general']['intro'], 'صفحه ورود: صفحه اصلی تا آماده شدن عکس‌های اول با دایره سایز باز شود' );
				?>
				<h2>نماد اعتماد الکترونیکی (اینماد)</h2>
				<?php
				lc_field_text( 'lc[general][enamad]', $h['general']['enamad'], 'کد نماد', 'کدی را که سایت اینماد بعد از تأیید می‌دهد (با <a> و <img>) کامل این‌جا بچسبان. در پایین صفحه، روی گوشی بین لینک‌ها و آدرس و روی دسکتاپ بالای آدرس نمایش داده می‌شود.', 'textarea', [ 'dir' => 'ltr', 'rows' => 4 ] );
				lc_field_toggle( 'lc[general][enamad_slot]', $h['general']['enamad_slot'], 'تا وقتی کد وارد نشده، جای خالی نماد نشان داده شود' );
				?>
			</section>

			<p class="lcx-actions"><?php submit_button( 'ذخیره‌ی تغییرات', 'primary large', 'submit', false ); ?></p>
		</form>
	</div>
	<?php
}

/* ----------------------------------------------------------------- save */

/** eNamad gives a link + image snippet; keep only that (no scripts), with the attributes it relies on. */
function lc_clean_enamad( string $html ): string {
	$img = [ 'src' => true, 'alt' => true, 'style' => true, 'referrerpolicy' => true, 'id' => true, 'code' => true, 'width' => true, 'height' => true, 'class' => true ];
	$html = (string) preg_replace( '#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $html );
	return trim( wp_kses( $html, [
		'a'   => [ 'href' => true, 'target' => true, 'rel' => true, 'referrerpolicy' => true, 'id' => true ],
		'img' => $img,
	] ) );
}

function lc_clean_gallery( $raw ): array {
	$imgs  = array_map( 'absint', (array) ( $raw['img'] ?? [] ) );
	$prods = array_map( 'absint', (array) ( $raw['product'] ?? [] ) );
	$out   = [];
	foreach ( $imgs as $i => $img ) {
		if ( $img ) {
			$out[] = [ 'img' => $img, 'product' => $prods[ $i ] ?? 0 ];
		}
	}
	return $out;
}

add_action( 'admin_post_lc_save_home', static function (): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'دسترسی ندارید.' );
	}
	check_admin_referer( 'lc_save_home' );
	$in  = wp_unslash( (array) ( $_POST['lc'] ?? [] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised field by field below
	$txt = static fn( $v ) => sanitize_text_field( (string) $v );
	$num = static fn( $v, $min, $max, $d ) => is_numeric( $v ) ? max( $min, min( $max, (float) $v ) ) : $d;
	$end = trim( (string) ( $in['sale']['end'] ?? '' ) );

	$clean = [
		'announce' => [ 'show' => (int) ! empty( $in['announce']['show'] ), 'text' => $txt( $in['announce']['text'] ?? '' ), 'en' => $txt( $in['announce']['en'] ?? '' ), 'url' => esc_url_raw( (string) ( $in['announce']['url'] ?? '' ) ) ],
		'general'  => [
			'instagram'     => esc_url_raw( (string) ( $in['general']['instagram'] ?? '' ) ),
			'story_seconds' => $num( $in['general']['story_seconds'] ?? '', 2, 20, 5 ),
			'intro'         => (int) ! empty( $in['general']['intro'] ),
			'enamad'        => lc_clean_enamad( (string) ( $in['general']['enamad'] ?? '' ) ),
			'enamad_slot'   => (int) ! empty( $in['general']['enamad_slot'] ),
		],
		'hero'     => [
			'line1'      => $txt( $in['hero']['line1'] ?? '' ),
			'line2'      => $txt( $in['hero']['line2'] ?? '' ),
			'lead'       => sanitize_textarea_field( (string) ( $in['hero']['lead'] ?? '' ) ),
			'btn_text'   => $txt( $in['hero']['btn_text'] ?? '' ),
			'btn_url'    => esc_url_raw( (string) ( $in['hero']['btn_url'] ?? '' ) ),
			'insta_text' => $txt( $in['hero']['insta_text'] ?? '' ),
			'interval'   => $num( $in['hero']['interval'] ?? '', 2, 30, 5 ),
			'right'      => lc_clean_gallery( $in['hero']['right'] ?? [] ),
			'left'       => lc_clean_gallery( $in['hero']['left'] ?? [] ),
		],
		'cards'    => array_map( static fn( $c ) => [
			'title' => sanitize_text_field( (string) ( $c['title'] ?? '' ) ),
			'sub'   => sanitize_text_field( (string) ( $c['sub'] ?? '' ) ),
			'img'   => absint( $c['img'] ?? 0 ),
			'cat'   => absint( $c['cat'] ?? 0 ),
		], array_values( array_slice( (array) ( $in['cards'] ?? [] ), 0, 2 ) ) ),
		'sale'     => [
			'show'     => (int) ! empty( $in['sale']['show'] ),
			'title'    => $txt( $in['sale']['title'] ?? '' ),
			'sub'      => $txt( $in['sale']['sub'] ?? '' ),
			'end'      => preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $end ) ? str_replace( 'T', ' ', substr( $end, 0, 16 ) ) : '',
			'img'      => absint( $in['sale']['img'] ?? 0 ),
			'products' => array_values( array_filter( array_map( 'absint', (array) ( $in['sale']['products'] ?? [] ) ) ) ),
		],
		'spot'     => [
			'product' => absint( ( (array) ( $in['spot']['product'] ?? [] ) )[0] ?? 0 ),
			'notes'   => array_values( array_map( static fn( $n ) => [ 'k' => sanitize_text_field( (string) ( $n['k'] ?? '' ) ), 'v' => sanitize_textarea_field( (string) ( $n['v'] ?? '' ) ) ], array_slice( (array) ( $in['spot']['notes'] ?? [] ), 0, 3 ) ) ),
			'stats'   => array_values( array_map( static fn( $s ) => [ 'v' => sanitize_text_field( (string) ( $s['v'] ?? '' ) ), 'u' => sanitize_text_field( (string) ( $s['u'] ?? '' ) ), 'k' => sanitize_text_field( (string) ( $s['k'] ?? '' ) ) ], array_slice( (array) ( $in['spot']['stats'] ?? [] ), 0, 3 ) ) ),
		],
		'insta'    => [
			'title'    => $txt( $in['insta']['title'] ?? '' ),
			'text'     => sanitize_textarea_field( (string) ( $in['insta']['text'] ?? '' ) ),
			'btn_text' => $txt( $in['insta']['btn_text'] ?? '' ),
			'btn_url'  => esc_url_raw( (string) ( $in['insta']['btn_url'] ?? '' ) ),
			'items'    => lc_clean_gallery( $in['insta']['items'] ?? [] ),
		],
	];
	update_option( 'lc_home', $clean, false );
	$tab = sanitize_key( (string) ( $_POST['lc_tab'] ?? '' ) );
	wp_safe_redirect( admin_url( 'admin.php?page=lc-site&updated=1' ) . ( $tab ? '#' . $tab : '' ) );
	exit;
} );

/* ------------------------------------------------------------ front helpers */

/** Gallery items → what the templates print: image, link (product or fallback) and label. */
function lc_home_photos( array $items, string $fallback_url, string $size = 'large' ): array {
	$out = [];
	foreach ( $items as $it ) {
		$id  = (int) ( $it['img'] ?? 0 );
		$src = $id ? wp_get_attachment_image_src( $id, $size ) : null;
		if ( ! $src ) {
			continue;
		}
		$p     = ! empty( $it['product'] ) && function_exists( 'wc_get_product' ) ? wc_get_product( (int) $it['product'] ) : null;
		$p     = $p && 'publish' === $p->get_status() ? $p : null;
		$out[] = [
			'id'      => $id,
			'src'     => $src[0],
			'w'       => (int) $src[1],
			'h'       => (int) $src[2],
			'srcset'  => (string) wp_get_attachment_image_srcset( $id, $size ),
			'url'     => $p ? (string) get_permalink( $p->get_id() ) : $fallback_url,
			'label'   => $p ? $p->get_name() : '',
			'product' => (bool) $p,
		];
	}
	return $out;
}

/** Hero slider photos: the admin lists, or recent product photos while they are empty. */
function lc_hero_stacks(): array {
	$h    = lc_home()['hero'];
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$a    = lc_home_photos( (array) $h['right'], $shop );
	$b    = lc_home_photos( (array) $h['left'], $shop );
	if ( ! $a || ! $b ) {
		$fill = [];
		foreach ( function_exists( 'wc_get_products' ) ? wc_get_products( [ 'status' => 'publish', 'limit' => 6, 'orderby' => 'date', 'order' => 'DESC' ] ) : [] as $p ) {
			if ( $p->get_image_id() ) {
				$fill[] = [ 'img' => $p->get_image_id(), 'product' => $p->get_id() ];
			}
		}
		$a = $a ?: lc_home_photos( array_slice( $fill, 0, 3 ), $shop );
		$b = $b ?: lc_home_photos( array_slice( $fill, 3, 3 ), $shop );
	}
	// phones: one stack, alternating right/left so both lists take turns
	$phone = [];
	for ( $i = 0, $n = max( count( $a ), count( $b ) ); $i < $n; $i++ ) {
		if ( isset( $a[ $i ] ) ) {
			$phone[] = $a[ $i ];
		}
		if ( isset( $b[ $i ] ) ) {
			$phone[] = $b[ $i ];
		}
	}
	return [ 'a' => $a, 'b' => $b, 'phone' => $phone, 'interval' => (float) $h['interval'] ];
}

/** One hero stack (data-dir: 1 = queue fans out to the right, -1 = to the left, 0 = centred deck on phones). */
function lc_hero_stack( array $photos, string $class, int $dir, int $eager ): void {
	if ( ! $photos ) {
		return;
	}
	printf( '<div class="look hs %s" data-hs data-dir="%d">', esc_attr( $class ), $dir );
	foreach ( $photos as $i => $ph ) {
		printf(
			'<a class="hs__item" href="%s"%s><img src="%s" srcset="%s" sizes="(max-width: 900px) 80vw, 30vw" alt="%s" width="%d" height="%d"%s /></a>',
			esc_url( $ph['url'] ),
			$ph['label'] ? ' aria-label="' . esc_attr( $ph['label'] ) . '"' : ' aria-label="دیدن محصولات"',
			esc_url( $ph['src'] ),
			esc_attr( $ph['srcset'] ),
			esc_attr( $ph['label'] ),
			$ph['w'],
			$ph['h'],
			$i < $eager ? ' fetchpriority="high"' : ' loading="lazy"'
		);
	}
	echo '<span class="hs__bar" aria-hidden="true"><i></i></span></div>';
}

/** Sale band products: the admin's list in its order, or WooCommerce's on-sale products. */
function lc_home_sale_products( int $limit = 12 ): array {
	$ids = (array) lc_home()['sale']['products'];
	if ( ! $ids ) {
		return function_exists( 'lc_sale_products' ) ? lc_sale_products( $limit ) : [];
	}
	$products = wc_get_products( [ 'include' => $ids, 'status' => 'publish', 'limit' => $limit, 'orderby' => 'post__in' ] );
	return array_values( array_filter( $products, static fn( $p ) => $p->is_visible() ) );
}
