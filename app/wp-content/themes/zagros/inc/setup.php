<?php
/** Theme supports, menus, image sizes, document basics. */
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', static function (): void {
	load_theme_textdomain( 'shopkit', LC_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'responsive-embeds' );
	// Customizer → Site Identity → Logo (see inc/brand.php)
	add_theme_support( 'custom-logo', [ 'height' => 240, 'width' => 240, 'flex-height' => true, 'flex-width' => true ] );
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width' => 660,
		'single_image_width'    => 900,
		'product_grid'          => [ 'default_columns' => 4, 'default_rows' => 4 ],
	] );
	register_nav_menus( [
		'primary' => 'منوی بالا',
		'footer'  => 'منوی پایین صفحه',
	] );
	add_image_size( 'lc-story', 160, 160, true );
	add_image_size( 'lc-banner', 1400, 760, true );
} );

/** Header nav fallback when no menu is assigned yet. */
function lc_default_nav(): array {
	$items = [];
	// Zagros: all shoes, the first three categories, the journal
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$items[] = [ 'همه کفش‌ها', wc_get_page_permalink( 'shop' ) ];
		foreach ( array_slice( function_exists( 'lc_categories' ) ? lc_categories() : [], 0, 3 ) as $c ) {
			$items[] = [ $c['name'], $c['url'] ];
		}
	}
	$items[] = [ 'مجله', lc_journal_url() ];
	return $items;
}

function lc_nav( string $location, string $class ): void {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( [ 'theme_location' => $location, 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'walker' => new LC_Flat_Walker( $class ) ] );
		return;
	}
	foreach ( lc_default_nav() as [ $label, $url ] ) {
		printf( '<a href="%s" class="%s">%s</a>', esc_url( $url ), esc_attr( $class ), esc_html( $label ) );
	}
}

/** Prints plain <a> links (no <ul>/<li>) so menus fit the header/footer rows. */
class LC_Flat_Walker extends Walker_Nav_Menu {
	public function __construct( private string $link_class = 'ulink' ) {}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$current = in_array( 'current-menu-item', (array) $item->classes, true ) ? ' aria-current="page"' : '';
		$output .= sprintf( '<a href="%s" class="%s"%s>%s</a>', esc_url( $item->url ), esc_attr( $this->link_class ), $current, esc_html( $item->title ) );
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ): void {}
}

add_filter( 'body_class', static function ( array $classes ): array {
	$classes[] = 'lc';
	return $classes;
} );

/** Template name the router uses to decide between region swap and full swap. */
function lc_template_key(): string {
	if ( is_front_page() ) return 'home';
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) ) return 'shop';
	if ( function_exists( 'is_product' ) && is_product() ) return 'product';
	if ( is_home() || is_category() || is_tag() || ( is_archive() && 'post' === get_post_type() ) ) return 'journal';
	if ( is_singular( 'post' ) ) return 'post';
	return 'page';
}

add_action( 'wp_head', static function (): void {
	printf( '<meta name="theme-color" content="#f7f7f5" />' . "\n" );
	// Site Icon (Customizer → Site Identity) wins; WordPress prints that one itself.
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/svg+xml" />' . "\n", esc_url( LC_URI . '/assets/img/mark.svg' ) );
	}
	?>
	<script>
	(function () {
		var t = 'light';
		try { t = localStorage.getItem('zg-theme') || 'light'; } catch (e) {}
		document.documentElement.setAttribute('data-theme', t);
	})();
	</script>
	<?php if ( lc_intro_on() ) : ?>
	<script>
	/* the home page opens on the size-dial loader (template-parts/intro.php, src/js/modules/intro.js) */
	document.documentElement.classList.add('has-intro');
	</script>
	<?php
	endif;
}, 1 );

/** The loading intro: home page only, when «صفحه ورود» is on (پیشخوان ← تنظیمات کلی). */
function lc_intro_on(): bool {
	return is_front_page() && function_exists( 'lc_home' ) && ! empty( lc_home()['general']['intro'] );
}
add_action( 'wp_body_open', static function (): void {
	if ( lc_intro_on() ) {
		get_template_part( 'template-parts/intro' );
	}
}, 1 );
