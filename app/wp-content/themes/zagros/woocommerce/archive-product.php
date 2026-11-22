<?php
/**
 * Shop, category archives and product search (Zagros).
 * Filters are server-side GET parameters (inc/shop-query.php). The router swaps only the
 * [data-region] parts on the same archive, so every state is a real, shareable URL.
 */
defined( 'ABSPATH' ) || exit;
get_header();

global $wp_query;
$shop    = wc_get_page_permalink( 'shop' );
$lc_term = is_product_taxonomy() ? get_queried_object() : null;
$base    = $lc_term ? get_term_link( $lc_term ) : $shop;
$f       = lc_filter_state();
$found   = (int) $wp_query->found_posts;
$active  = lc_active_filter_count();
$orderby = $f['orderby'] ?: 'menu_order';
$en      = $lc_term ? lc_category_en( $lc_term->slug ) : ( $f['s'] ? 'SEARCH' : 'ALL' );
$sorts   = [ 'menu_order' => 'پیشنهادی', 'date' => 'جدیدترین', 'popularity' => 'پرفروش‌ترین', 'price' => 'ارزان‌ترین', 'price-desc' => 'گران‌ترین', 'discount' => 'بیشترین تخفیف' ];
$keep    = static function ( array $except = [] ) use ( $f ): void {
	$map = [ 'lc_color' => $f['color'], 'lc_price' => $f['price'], 'on_sale' => $f['sale'] ? 1 : '', 'is_new' => $f['new'] ? 1 : '', 'in_stock' => $f['stock'] ? 1 : '', 'orderby' => $f['orderby'], 's' => $f['s'] ];
	if ( $f['s'] ) {
		$map['post_type'] = 'product';
	}
	foreach ( $map as $k => $v ) {
		if ( '' !== $v && ! in_array( $k, $except, true ) ) {
			printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $k ), esc_attr( (string) $v ) );
		}
	}
};
?>
<div class="shop">
	<section class="pagehead wrap">
		<nav class="crumbs" aria-label="مسیر">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span>
			<?php if ( $lc_term || $f['s'] ) : ?><a href="<?php echo esc_url( $shop ); ?>">فروشگاه</a><span>/</span><?php endif; ?>
			<span aria-current="page" data-region="crumb"><?php echo esc_html( $lc_term ? $lc_term->name : ( $f['s'] ? 'جستجو' : 'فروشگاه' ) ); ?></span>
		</nav>
		<h1 class="h1"><span class="en"><?php echo esc_html( $en ); ?> <span class="dim">SHOES</span></span></h1>
		<p class="lead"><?php echo esc_html( $lc_term && $lc_term->description ? wp_strip_all_tags( $lc_term->description ) : 'کتونی، کفش رانینگ، کوهنوردی و اسلیپ‌آن؛ همه اورجینال و با ۷ روز ضمانت تعویض سایز.' ); ?></p>
	</section>

	<div class="shopbar wrap" data-shopbar>
		<nav class="shopbar__tabs" aria-label="دسته‌بندی‌ها">
			<a href="<?php echo esc_url( lc_shop_url( [], [ 's', 'post_type' ], $shop ) ); ?>" class="tab<?php echo ! $lc_term ? ' is-on' : ''; ?>"<?php echo ! $lc_term ? ' aria-current="page"' : ''; ?>>همه</a>
			<?php foreach ( lc_categories() as $c ) : $on = $lc_term && $lc_term->slug === $c['slug']; ?>
				<a href="<?php echo esc_url( lc_shop_url( [], [ 's', 'post_type' ], $c['url'] ) ); ?>" class="tab<?php echo $on ? ' is-on' : ''; ?>"<?php echo $on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $c['name'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="shopbar__tools">
			<form class="shopbar__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-soft-form>
				<svg class="icon"><use href="#i-search"/></svg>
				<label class="sr-only" for="shop-q">جستجو در فروشگاه</label>
				<input id="shop-q" type="search" name="s" placeholder="جستجو" value="<?php echo esc_attr( $f['s'] ); ?>" />
				<input type="hidden" name="post_type" value="product" />
			</form>
			<button class="btn btn--ghost btn--sm tool" type="button" data-sheet-open="filters"><svg class="icon"><use href="#i-filter"/></svg>فیلتر<span class="tool__badge" data-region="badge"<?php echo $active ? '' : ' hidden'; ?>><?php echo esc_html( lc_fa( $active ) ); ?></span></button>
			<div class="sort" data-region="sort">
				<button class="btn btn--ghost btn--sm tool" type="button" data-sort aria-haspopup="true" aria-expanded="false"><span class="small">مرتب‌سازی</span> <b><?php echo esc_html( $sorts[ $orderby ] ?? 'پیشنهادی' ); ?></b><svg class="icon"><use href="#i-chev-down"/></svg></button>
				<ul class="sort__menu" data-sort-menu hidden>
					<?php foreach ( $sorts as $k => $label ) : ?>
						<li><a href="<?php echo esc_url( lc_shop_url( [ 'orderby' => 'menu_order' === $k ? '' : $k ], 'menu_order' === $k ? [ 'orderby' ] : [], $base ) ); ?>" aria-checked="<?php echo $orderby === $k ? 'true' : 'false'; ?>" role="menuitemradio"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>

	<section class="wrap shop__results" aria-label="نتیجه">
		<p class="shop__meta small" data-region="meta" aria-live="polite"><b><?php echo esc_html( lc_fa( $found ) ); ?></b> کالا<?php echo $f['s'] ? ' برای «' . esc_html( $f['s'] ) . '»' : ''; ?></p>
		<div data-region="results">
			<?php if ( woocommerce_product_loop() && have_posts() ) : ?>
				<div class="grid-cards">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/product-card', null, [ 'product' => wc_get_product( get_the_ID() ) ] );
					}
					?>
				</div>
				<?php
				$links = paginate_links( [ 'total' => $wp_query->max_num_pages, 'current' => max( 1, get_query_var( 'paged' ) ), 'type' => 'array', 'prev_text' => 'قبلی', 'next_text' => 'بعدی', 'mid_size' => 1 ] );
				if ( $links ) :
					?>
					<nav class="pagination" aria-label="صفحه‌ها"><?php echo wp_kses_post( lc_fa( implode( '', $links ) ) ); ?></nav>
				<?php endif; ?>
			<?php else : ?>
				<div class="empty">
					<svg class="empty__mark" viewBox="0 0 14 12" aria-hidden="true"><path d="M7 0 14 12H0z"/></svg>
					<h2 class="fa-title">کالایی با این مشخصات پیدا نشد</h2>
					<p class="lead">فیلترها را تغییر دهید یا عبارت دیگری جستجو کنید.</p>
					<a href="<?php echo esc_url( $base ); ?>" class="btn btn--ghost">حذف فیلترها</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>

<div class="sheet" data-sheet="filters" role="dialog" aria-modal="true" aria-label="فیلترها" hidden>
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head"><h2 class="sheet__title"><span class="en">FILTER</span> فیلترها</h2><button class="icon-btn" type="button" data-sheet-close aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
	<form class="filters" method="get" action="<?php echo esc_url( $base ); ?>" data-soft-form data-merge data-auto>
		<?php $keep( [ 'lc_color', 'lc_price', 'on_sale', 'is_new', 'in_stock' ] ); ?>
		<?php $colors = lc_filter_colors(); if ( $colors ) : ?>
			<fieldset>
				<legend>رنگ</legend>
				<div class="chips">
					<label class="chk"><input type="radio" name="lc_color" value=""<?php checked( '', $f['color'] ); ?> /><span>همه رنگ‌ها</span></label>
					<?php foreach ( $colors as $c ) : ?>
						<label class="chk"><input type="radio" name="lc_color" value="<?php echo esc_attr( $c['slug'] ); ?>"<?php checked( $c['slug'], $f['color'] ); ?> /><span><i class="chk__dot" style="--c:<?php echo esc_attr( $c['hex'] ); ?>"></i><?php echo esc_html( $c['name'] ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>
		<fieldset>
			<legend>محدوده قیمت</legend>
			<div class="chips">
				<?php foreach ( [ '' => 'همه', '0-4000000' => 'تا ۴ میلیون تومان', '4000000-5000000' => '۴ تا ۵ میلیون', '5000000-999999999' => 'بیشتر از ۵ میلیون' ] as $k => $label ) : ?>
					<label class="chk"><input type="radio" name="lc_price" value="<?php echo esc_attr( $k ); ?>"<?php checked( $k, $f['price'] ); ?> /><span><?php echo esc_html( $label ); ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<fieldset>
			<legend>نمایش کالاها</legend>
			<label class="switch"><input type="checkbox" name="on_sale" value="1"<?php checked( $f['sale'] ); ?> /><span class="switch__ui"></span>فقط کالاهای دارای تخفیف</label>
			<label class="switch"><input type="checkbox" name="is_new" value="1"<?php checked( $f['new'] ); ?> /><span class="switch__ui"></span>فقط محصولات جدید</label>
			<label class="switch"><input type="checkbox" name="in_stock" value="1"<?php checked( $f['stock'] ); ?> /><span class="switch__ui"></span>فقط کالاهای موجود</label>
		</fieldset>
		<div class="filters__foot">
			<a href="<?php echo esc_url( $base ); ?>" class="btn btn--ghost" data-sheet-close>حذف فیلترها</a>
			<button class="btn" type="submit" data-sheet-close>نمایش <span data-region="filtercount"><?php echo esc_html( lc_fa( $found ) ); ?></span> کالا</button>
		</div>
	</form>
</div>
<?php
get_footer();
