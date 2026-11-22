<?php
/**
 * Zagros: the data the Zagros templates need on top of the kit core.
 * Everything here reads WooCommerce or the admin settings (lc_home); nothing is hard-wired to
 * product ids, so the owner's own catalogue drives the pages.
 */
defined( 'ABSPATH' ) || exit;

/** Free shipping threshold in Toman (0 = no free-shipping bar). Change with the lc_free_shipping filter. */
function lc_free_shipping(): int {
	return (int) apply_filters( 'lc_free_shipping', 3000000 );
}

/** 3000000 → «۳ میلیون تومان»; other amounts as a plain price. */
function lc_price_words( int $amount ): string {
	if ( $amount >= 1000000 && 0 === $amount % 100000 ) {
		$m = $amount / 1000000;
		return lc_fa( rtrim( rtrim( number_format( $m, 1, '٫', '' ), '0' ), '٫' ) ) . ' میلیون تومان';
	}
	return lc_price( $amount ) . ' تومان';
}

/** English label for a category (term meta lc_en, else a known slug, else the slug). */
function lc_category_en( string $slug ): string {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	$en = $t ? (string) get_term_meta( $t->term_id, 'lc_en', true ) : '';
	if ( $en ) {
		return $en;
	}
	$known = [ 'everyday' => 'Everyday', 'running' => 'Running', 'court' => 'Sneakers', 'sneakers' => 'Sneakers', 'trail' => 'Trail', 'slipon' => 'Slip-on', 'kids' => 'Kids' ];
	return $known[ $slug ] ?? ucfirst( str_replace( '-', ' ', rawurldecode( $slug ) ) );
}

/** Short line under a category (its description in WooCommerce). */
function lc_category_blurb( string $slug ): string {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	return $t ? wp_strip_all_tags( (string) $t->description ) : '';
}

/** Lifestyle photo for a category card (term meta lc_photo = attachment id), else its thumbnail. */
function lc_category_photo( string $slug, string $fallback ): string {
	$t  = get_term_by( 'slug', $slug, 'product_cat' );
	$id = $t ? (int) get_term_meta( $t->term_id, 'lc_photo', true ) : 0;
	$url = $id ? wp_get_attachment_image_url( $id, 'large' ) : '';
	return $url ?: $fallback;
}

/** URL of the first published page using a page template (size guide, help). */
function lc_page_by_template( string $template ): string {
	static $cache = [];
	if ( ! isset( $cache[ $template ] ) ) {
		$pages              = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => $template, 'number' => 1 ] );
		$cache[ $template ] = $pages ? (string) get_permalink( $pages[0] ) : '';
	}
	return $cache[ $template ];
}
function lc_size_url(): string {
	return lc_page_by_template( 'page-templates/template-size.php' );
}
function lc_help_url(): string {
	return lc_page_by_template( 'page-templates/template-help.php' );
}

/** «@handle» from the Instagram address in the admin. */
function lc_instagram_handle(): string {
	$url  = (string) lc_home()['general']['instagram'];
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	return $path ? '@' . explode( '/', $path )[0] : '@instagram';
}

/** The four promises in the footer band. */
function lc_promises(): array {
	$free = lc_free_shipping();
	return (array) apply_filters( 'lc_promises', [
		[ 'title' => $free ? 'ارسال رایگان' : 'ارسال به سراسر کشور', 'note' => $free ? 'برای خریدهای بالای ' . lc_price_words( $free ) : 'با پست پیشتاز و تیپاکس' ],
		[ 'title' => '۷ روز ضمانت تعویض', 'note' => 'اگر سایز مناسب نبود، عوض می‌کنیم' ],
		[ 'title' => 'ضمانت اصالت کالا', 'note' => 'همه محصولات اورجینال و با جعبه اصلی' ],
		[ 'title' => 'مشاوره خرید', 'note' => 'برای انتخاب سایز در دایرکت پیام بدهید' ],
	] );
}

/** Words in the moving band on the home page (en = set in the Latin face). */
function lc_band_words(): array {
	$free = lc_free_shipping();
	return (array) apply_filters( 'lc_band_words', [
		[ 'text' => $free ? 'ارسال رایگان بالای ' . preg_replace( '/ تومان$/u', '', lc_price_words( $free ) ) : 'ارسال به سراسر کشور', 'en' => false ],
		[ 'text' => 'Original only', 'en' => true ],
		[ 'text' => '۷ روز ضمانت تعویض', 'en' => false ],
		[ 'text' => lc_size_range() ? 'Sizes ' . str_replace( '–', ' to ', lc_size_range() ) : 'Sneakers & Runners', 'en' => true ],
	] );
}

/** Mobile menu: shop, the first categories, size guide, journal, help. */
function lc_menu_items(): array {
	$items = [];
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$items[] = [ 'label' => 'همه کفش‌ها', 'en' => 'Shop', 'url' => wc_get_page_permalink( 'shop' ) ];
		foreach ( array_slice( lc_categories(), 0, 4 ) as $c ) {
			$items[] = [ 'label' => $c['name'], 'en' => lc_category_en( $c['slug'] ), 'url' => $c['url'] ];
		}
	}
	if ( lc_size_url() ) {
		$items[] = [ 'label' => 'راهنمای سایز', 'en' => 'Size guide', 'url' => lc_size_url() ];
	}
	$items[] = [ 'label' => 'مجله', 'en' => 'Journal', 'url' => lc_journal_url() ];
	if ( lc_help_url() ) {
		$items[] = [ 'label' => 'سوالات متداول', 'en' => 'Help', 'url' => lc_help_url() ];
	}
	return $items;
}

/**
 * Hero turntable slides from «صفحه اصلی ← هیرو و اسلایدر ← عکس‌های راست»: each row is a cut-out
 * photo and the product it shows. Without rows, the newest products stand in.
 * Shape (read by src/js/modules/hero.js): { key, word, lead, size, product: { name, url, price, colors: [{ slug, name, hex, img }] } }
 */
function lc_hero_slides(): array {
	$rows = (array) lc_home()['hero']['right'];
	if ( ! $rows ) {
		foreach ( wc_get_products( [ 'status' => 'publish', 'limit' => 5, 'orderby' => 'date', 'order' => 'DESC' ] ) as $p ) {
			$rows[] = [ 'img' => 0, 'product' => $p->get_id() ];
		}
	}
	$out = [];
	foreach ( $rows as $row ) {
		$p = ! empty( $row['product'] ) ? wc_get_product( (int) $row['product'] ) : null;
		if ( ! $p || 'publish' !== $p->get_status() ) {
			continue;
		}
		$d      = lc_product_data( $p );
		$photo  = ! empty( $row['img'] ) ? (string) wp_get_attachment_image_url( (int) $row['img'], 'large' ) : '';
		$colors = array_map( static fn( $c ) => [ 'slug' => $c['slug'], 'name' => $c['name'], 'hex' => $c['hex'], 'img' => $c['img'] ], $d['colors'] );
		if ( ! $colors ) {
			$colors = [ [ 'slug' => '', 'name' => '', 'hex' => $d['tone'] ?: '#d9d9d6', 'img' => $d['image'] ] ];
		}
		if ( $photo ) {
			$colors[0]['img'] = $photo;
		}
		// the size the dial shows: the middle one of the sizes in stock (EU 36 to 47 on the dial)
		$sizes = array_values( array_unique( array_filter( array_map( static fn( $v ) => $v['in'] ? (int) $v['size'] : 0, $d['variations'] ) ) ) );
		sort( $sizes );
		$size = (int) $p->get_meta( '_zg_hero_size' ) ?: ( $sizes ? $sizes[ (int) floor( ( count( $sizes ) - 1 ) / 2 ) ] : 42 );
		$size = min( 47, max( 36, $size ?: 42 ) );
		$word = trim( (string) $p->get_meta( '_zg_hero_word' ) ) ?: $d['name'];
		$lead = trim( (string) $p->get_meta( '_zg_hero_lead' ) ) ?: wp_trim_words( wp_strip_all_tags( $p->get_short_description() ?: $p->get_description() ), 16, '…' );
		$out[] = [
			'key'     => (string) $d['id'],
			'word'    => function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $word ) : strtoupper( $word ),
			'lead'    => $lead,
			'size'    => $size,
			'product' => [ 'name' => $d['name'], 'url' => $d['url'], 'price' => $d['price'], 'colors' => $colors ],
		];
	}
	return $out;
}

/**
 * «Pick of the month»: the best-selling product (ties: newest). Notes come from its visible
 * attributes (name → title, value → text); stats from its weight, size range and colour count.
 */
function lc_spot_product(): ?array {
	$cfg = (array) ( lc_home()['spot'] ?? [] );
	$p   = ! empty( $cfg['product'] ) ? wc_get_product( (int) $cfg['product'] ) : null;
	if ( ! $p || 'publish' !== $p->get_status() ) {
		$p = lc_best_sellers( 1 )[0] ?? null;
	}
	if ( ! $p ) {
		return null;
	}
	$d     = lc_product_data( $p, true );
	$notes = array_values( array_filter( (array) ( $cfg['notes'] ?? [] ), static fn( $n ) => ! empty( $n['k'] ) ) );
	if ( ! $notes ) {
		$notes = array_slice( array_values( array_filter( $d['specs'], static fn( $s ) => 'کد' !== $s['k'] ) ), 0, 3 );
	}
	$stats = array_values( array_filter( (array) ( $cfg['stats'] ?? [] ), static fn( $s ) => '' !== (string) ( $s['v'] ?? '' ) ) );
	if ( $stats ) {
		return [ 'data' => $d, 'notes' => $notes, 'stats' => $stats ];
	}
	if ( $p->get_weight() ) {
		$stats[] = [ 'v' => $p->get_weight(), 'u' => 'g', 'k' => 'وزن هر لنگه' ];
	}
	if ( $d['sizes'] ) {
		$first   = reset( $d['sizes'] )['slug'];
		$last    = end( $d['sizes'] )['slug'];
		$stats[] = [ 'v' => $first . '–' . $last, 'u' => '', 'k' => 'سایزبندی اروپایی' ];
	}
	if ( $d['colors'] ) {
		$stats[] = [ 'v' => (string) count( $d['colors'] ), 'u' => '', 'k' => 'رنگ' ];
	}
	return [ 'data' => $d, 'notes' => $notes, 'stats' => $stats ];
}

/**
 * Best sellers by WooCommerce's sales count (ties: newest).
 * wc_get_products() ignores orderby=popularity and silently falls back to date, so sort on the meta.
 */
function lc_best_sellers( int $limit = 8 ): array {
	return wc_get_products( [ 'status' => 'publish', 'limit' => $limit, 'visibility' => 'catalog', 'meta_key' => 'total_sales', 'orderby' => [ 'meta_value_num' => 'DESC', 'date' => 'DESC' ] ] );
}

/** «38–45»: the smallest and largest numeric size in the store (pa_size terms in use). */
function lc_size_range(): string {
	$n = array_filter( array_map( 'intval', (array) get_terms( [ 'taxonomy' => 'pa_size', 'hide_empty' => true, 'fields' => 'names' ] ) ) );
	return $n ? min( $n ) . '–' . max( $n ) : '';
}

/* Product screen: the words the home slider prints for this product (General tab). */
add_action( 'woocommerce_product_options_general_product_data', static function (): void {
	echo '<div class="options_group"><p class="form-field"><strong>اسلایدر صفحه اصلی</strong> فقط وقتی این محصول در «اسلایدر مدل‌ها» باشد استفاده می‌شود.</p>';
	woocommerce_wp_text_input( [ 'id' => '_zg_hero_word', 'label' => 'کلمه بزرگ پشت کفش', 'description' => 'انگلیسی و کوتاه، مثلا DAILY. خالی = نام محصول.', 'desc_tip' => true ] );
	woocommerce_wp_textarea_input( [ 'id' => '_zg_hero_lead', 'label' => 'جمله زیر اسلاید', 'description' => 'یک جمله. خالی = توضیح کوتاه محصول.', 'desc_tip' => true, 'rows' => 2 ] );
	woocommerce_wp_text_input( [ 'id' => '_zg_hero_size', 'label' => 'سایز روی سکو', 'type' => 'number', 'custom_attributes' => [ 'min' => 36, 'max' => 47 ], 'description' => 'خالی = سایز وسط موجودی.', 'desc_tip' => true ] );
	echo '</div>';
} );
add_action( 'woocommerce_admin_process_product_object', static function ( WC_Product $p ): void {
	// phpcs:disable WordPress.Security.NonceVerification -- WooCommerce verifies the product form nonce before this hook
	$p->update_meta_data( '_zg_hero_word', sanitize_text_field( wp_unslash( (string) ( $_POST['_zg_hero_word'] ?? '' ) ) ) );
	$p->update_meta_data( '_zg_hero_lead', sanitize_textarea_field( wp_unslash( (string) ( $_POST['_zg_hero_lead'] ?? '' ) ) ) );
	$p->update_meta_data( '_zg_hero_size', absint( $_POST['_zg_hero_size'] ?? 0 ) ?: '' );
	// phpcs:enable
} );

/** English label for a journal topic (term meta lc_en, else the slug). */
function lc_topic_en( WP_Term $t ): string {
	$en = (string) get_term_meta( $t->term_id, 'lc_en', true );
	return $en ?: ucfirst( str_replace( '-', ' ', rawurldecode( $t->slug ) ) );
}
