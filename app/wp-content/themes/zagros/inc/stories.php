<?php
/**
 * Stories: the circles above the home hero. Each story (مدیریت سایت ← استوری‌ها) has a title,
 * a cover (featured image, or its first photo) and any number of photos; a photo can link to a
 * WooCommerce product, which shows a «مشاهده‌ی محصول» button on it. Viewer: assets/stories.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
	register_post_type( 'lc_story', [
		'labels'       => [
			'name'                  => 'استوری‌ها',
			'singular_name'         => 'استوری',
			'menu_name'             => 'استوری‌ها',
			'all_items'             => 'استوری‌ها',
			'add_new'               => 'افزودن استوری',
			'add_new_item'          => 'افزودن استوری تازه',
			'edit_item'             => 'ویرایش استوری',
			'not_found'             => 'هنوز استوری‌ای ساخته نشده.',
			'featured_image'        => 'عکس دایره (کاور)',
			'set_featured_image'    => 'انتخاب عکس دایره',
			'remove_featured_image' => 'برداشتن عکس دایره',
		],
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'lc-site',
		'supports'     => [ 'title', 'thumbnail', 'page-attributes' ],
		'show_in_rest' => false,
	] );
} );

add_action( 'add_meta_boxes_lc_story', static function (): void {
	add_meta_box( 'lc_story_slides', 'عکس‌های استوری', static function ( WP_Post $post ): void {
		wp_nonce_field( 'lc_story_save', 'lc_story_nonce' );
		echo '<div class="lcx-meta">';
		lc_field_gallery( 'lc_story_slides', (array) get_post_meta( $post->ID, '_lc_story_slides', true ), 'عکس‌ها به ترتیب نمایش', 'عکس عمودی (۹ به ۱۶) بهترین است. برای هر عکس می‌توانی محصولی انتخاب کنی تا دکمه‌ی «مشاهده‌ی محصول» روی آن بیاید. عنوان استوری زیر دایره نوشته می‌شود؛ ترتیب دایره‌ها از «ویژگی‌های برگه ← ترتیب».' );
		echo '</div>';
	}, 'lc_story', 'normal', 'high' );
} );

add_action( 'save_post_lc_story', static function ( int $post_id ): void {
	if ( ! isset( $_POST['lc_story_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lc_story_nonce'] ), 'lc_story_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_lc_story_slides', lc_clean_gallery( wp_unslash( (array) ( $_POST['lc_story_slides'] ?? [] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
} );

// List screen: cover + photo count, sorted by order.
add_filter( 'manage_lc_story_posts_columns', static fn( array $c ) => [ 'cb' => $c['cb'] ?? '', 'lc_cover' => 'دایره', 'title' => 'عنوان', 'lc_slides' => 'عکس‌ها', 'lc_order' => 'ترتیب', 'date' => $c['date'] ?? 'تاریخ' ] );
add_action( 'manage_lc_story_posts_custom_column', static function ( string $col, int $id ): void {
	if ( 'lc_cover' === $col ) {
		$src = lc_story_cover( $id );
		if ( $src ) {
			printf( '<img src="%s" alt="" style="width:48px;height:48px;border-radius:50%%;object-fit:cover;box-shadow:0 0 0 2px #fff,0 0 0 4px #b1122c" />', esc_url( $src ) );
		}
	} elseif ( 'lc_slides' === $col ) {
		$slides = (array) get_post_meta( $id, '_lc_story_slides', true );
		$linked = count( array_filter( $slides, static fn( $s ) => ! empty( $s['product'] ) ) );
		echo esc_html( lc_fa( count( $slides ) ) . ' عکس' . ( $linked ? '، ' . lc_fa( $linked ) . ' با محصول' : '' ) );
	} elseif ( 'lc_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $id ) );
	}
}, 10, 2 );
add_action( 'pre_get_posts', static function ( WP_Query $q ): void {
	if ( is_admin() && $q->is_main_query() && 'lc_story' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', [ 'menu_order' => 'ASC', 'date' => 'DESC' ] );
	}
} );

function lc_story_cover( int $id ): string {
	$thumb = get_post_thumbnail_id( $id );
	if ( ! $thumb ) {
		$slides = (array) get_post_meta( $id, '_lc_story_slides', true );
		$thumb  = (int) ( $slides[0]['img'] ?? 0 );
	}
	return $thumb ? (string) wp_get_attachment_image_url( $thumb, 'lc-story' ) : '';
}

/** Published stories with their photos, ready for the viewer. */
function lc_stories(): array {
	$out = [];
	foreach ( get_posts( [ 'post_type' => 'lc_story', 'post_status' => 'publish', 'posts_per_page' => 30, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ], 'no_found_rows' => true ] ) as $post ) {
		$slides = [];
		foreach ( (array) get_post_meta( $post->ID, '_lc_story_slides', true ) as $s ) {
			$src = wp_get_attachment_image_src( (int) ( $s['img'] ?? 0 ), 'large' );
			if ( ! $src ) {
				continue;
			}
			$slide = [ 'src' => $src[0], 'w' => (int) $src[1], 'h' => (int) $src[2], 'bg' => (string) wp_get_attachment_image_url( (int) $s['img'], 'thumbnail' ) ];
			$p     = ! empty( $s['product'] ) && function_exists( 'wc_get_product' ) ? wc_get_product( (int) $s['product'] ) : null;
			if ( $p && 'publish' === $p->get_status() ) {
				$d              = lc_product_data( $p );
				$slide['product'] = [ 'name' => $d['name'], 'url' => $d['url'], 'price' => lc_price( $d['price'] ) . ' تومان' ];
			}
			$slides[] = $slide;
		}
		if ( ! $slides ) {
			continue;
		}
		$out[] = [
			'key'    => $post->ID . '-' . get_post_modified_time( 'U', true, $post ),
			'title'  => get_the_title( $post ),
			'cover'  => lc_story_cover( $post->ID ),
			'slides' => $slides,
		];
	}
	return $out;
}

// Loaded everywhere: the router swaps only <main>, and the home page can arrive by soft navigation.
add_action( 'wp_enqueue_scripts', static function (): void {
	$d = '/assets/stories/';
	wp_enqueue_style( 'lc-stories', LC_URI . $d . 'stories.css', [], (string) filemtime( LC_DIR . $d . 'stories.css' ) );
	wp_enqueue_script( 'lc-stories', LC_URI . $d . 'stories.js', [], (string) filemtime( LC_DIR . $d . 'stories.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
}, 30 );
