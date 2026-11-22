<?php
/**
 * Product page (Zagros). Variations, stock and prices come from WooCommerce; add to cart goes
 * through the Store API (src/js/wp/pdp.js reads data-pdp-json).
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$p = wc_get_product( get_the_ID() );
	if ( ! $p ) {
		continue;
	}
	$d     = lc_product_data( $p, true );
	$first = 0;
	foreach ( $d['colors'] as $i => $c ) {
		if ( ! $c['out'] ) { $first = $i; break; }
	}
	$color  = $d['colors'][ $first ] ?? null;
	$main   = $color['img'] ?? $d['image'];
	$thumbs = array_values( array_unique( array_filter( array_merge( [ $main ], $d['gallery'] ) ) ) );
	$best   = (int) $p->get_total_sales() >= (int) apply_filters( 'lc_best_seller_sales', 5 );
	$shop   = wc_get_page_permalink( 'shop' );
	$tags   = array_filter( array_merge( [ $d['cat'] ], array_slice( wc_get_product_terms( $d['id'], 'product_tag', [ 'fields' => 'names' ] ), 0, 3 ) ) );
	$photo  = $d['gallery'] ? end( $d['gallery'] ) : '';
	$free   = lc_free_shipping();
	$json   = $d;
	unset( $json['desc'] );
	?>
	<div class="pdp wrap" data-pdp data-id="<?php echo esc_attr( $d['id'] ); ?>">
		<script type="application/json" data-pdp-json><?php echo wp_json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
		<nav class="crumbs" aria-label="مسیر">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><a href="<?php echo esc_url( $shop ); ?>">فروشگاه</a>
			<?php if ( $d['cat'] ) : ?><span>/</span><a href="<?php echo esc_url( $d['catUrl'] ); ?>"><?php echo esc_html( $d['cat'] ); ?></a><?php endif; ?>
			<span>/</span><span aria-current="page" class="en"><?php echo esc_html( $d['name'] ); ?></span>
		</nav>

		<div class="pdp__grid">
			<div class="pdp__gallery" data-pdp-gallery tabindex="0" aria-label="عکس‌های <?php echo esc_attr( $d['name'] ); ?>، با کلیدهای جهت عوض می‌شوند">
				<div class="pdp__thumbs" data-pdp-thumbs>
					<?php foreach ( $thumbs as $i => $src ) : ?>
						<button type="button" class="pdp__thumb" data-thumb="<?php echo esc_url( $src ); ?>" aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="عکس <?php echo esc_attr( lc_fa( $i + 1 ) ); ?>"><img src="<?php echo esc_url( $src ); ?>" alt="" width="96" height="96" loading="lazy" /></button>
					<?php endforeach; ?>
				</div>
				<div class="pdp__main">
					<?php if ( $color ) : ?><span class="pill pdp__colortag" data-pdp-color-name><?php echo esc_html( $color['name'] ); ?></span><?php endif; ?>
					<img src="<?php echo esc_url( $main ); ?>" alt="<?php echo esc_attr( $d['name'] ); ?>" width="1200" height="1200" fetchpriority="high" data-pdp-main />
					<?php if ( count( $thumbs ) > 1 ) : ?>
						<div class="pdp__galnav">
							<button class="icon-btn" type="button" data-gal-dir="prev" aria-label="عکس قبلی"><svg><use href="#i-chev"/></svg></button>
							<span class="small" data-pdp-count>۱ / <?php echo esc_html( lc_fa( count( $thumbs ) ) ); ?></span>
							<button class="icon-btn" type="button" data-gal-dir="next" aria-label="عکس بعدی"><svg style="transform:scaleX(-1)"><use href="#i-chev"/></svg></button>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="pdp__info">
				<?php if ( $d['onSale'] ) : ?><span class="pill pill--sale">٪<?php echo esc_html( lc_fa( $d['off'] ) ); ?> تخفیف</span>
				<?php elseif ( $d['isNew'] ) : ?><span class="pill pill--new"><i></i>جدید</span>
				<?php elseif ( $best ) : ?><span class="pill pill--new"><i></i>پرفروش</span><?php endif; ?>
				<div class="pdp__titlerow">
					<h1 class="pdp__name h2"><span class="en"><?php echo esc_html( $d['name'] ); ?></span></h1>
					<p class="pdp__price">
						<?php if ( $d['onSale'] ) : ?><s class="pdp__was"><?php echo esc_html( lc_price( $d['regular'] ) ); ?></s><?php endif; ?>
						<b data-pdp-price><?php echo esc_html( lc_price( $d['price'] ) ); ?></b> <small>تومان</small>
					</p>
				</div>
				<?php if ( $tags ) : ?><div class="pdp__tags"><?php foreach ( $tags as $t ) : ?><span class="tagchip"><?php echo esc_html( $t ); ?></span><?php endforeach; ?></div><?php endif; ?>
				<?php if ( $d['desc'] ) : ?><p class="pdp__desc"><?php echo esc_html( $d['desc'] ); ?></p><?php endif; ?>

				<?php if ( $d['colors'] ) : ?>
					<div class="pdp__opt">
						<p class="quick__label">رنگ <b data-pdp-color-label><?php echo esc_html( $color['name'] ); ?></b></p>
						<div class="swatches" data-pdp-colors role="group" aria-label="رنگ">
							<?php foreach ( $d['colors'] as $i => $c ) : ?>
								<button type="button" class="swatch" data-swatch data-color="<?php echo esc_attr( $c['slug'] ); ?>" aria-pressed="<?php echo $i === $first ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $c['name'] . ( $c['out'] ? '، ناموجود' : '' ) ); ?>"><span style="--c:<?php echo esc_attr( $c['hex'] ); ?>"></span></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( $d['sizes'] ) : ?>
					<div class="pdp__opt">
						<p class="quick__label">سایز <b data-pdp-size-label></b><?php if ( lc_size_url() ) : ?><a href="<?php echo esc_url( lc_size_url() ); ?>" class="ulink pdp__sizelink">راهنمای سایز</a><?php endif; ?></p>
						<div class="sizes" data-pdp-sizes></div>
					</div>
				<?php endif; ?>
				<button type="button" class="btn btn--split btn--block pdp__add" data-pdp-add<?php echo $d['soldOut'] ? ' disabled' : ''; ?>><span data-label><?php echo $d['soldOut'] ? 'ناموجود' : ( $d['sizes'] ? 'لطفا سایز را انتخاب کنید' : 'افزودن به سبد خرید' ); ?></span><span class="btn__price"><?php echo esc_html( lc_price( $d['price'] ) ); ?></span></button>

				<ul class="pdp__promises">
					<li><i></i>ارسال همان روز برای سفارش‌های قبل از ساعت ۱۴</li>
					<?php if ( $free ) : ?><li><i></i>ارسال رایگان برای خریدهای بالای <?php echo esc_html( lc_price_words( $free ) ); ?></li><?php endif; ?>
					<li><i></i>۷ روز ضمانت تعویض سایز</li>
				</ul>

				<details class="acc" data-acc>
					<summary>ارسال و تعویض<span class="acc__icon" aria-hidden="true"></span></summary>
					<div class="acc__body"><p>سفارش‌های تهران ۱ تا ۲ روز کاری و شهرستان‌ها ۲ تا ۴ روز کاری با پست پیشتاز یا تیپاکس ارسال می‌شوند. اگر سایز مناسب نبود، تا ۷ روز در دایرکت پیام بدهید تا تعویض را هماهنگ کنیم.</p></div>
				</details>
				<details class="acc" data-acc>
					<summary>ضمانت اصالت کالا<span class="acc__icon" aria-hidden="true"></span></summary>
					<div class="acc__body"><p>همه محصولات اورجینال هستند و با جعبه و برچسب اصلی برند ارسال می‌شوند.</p></div>
				</details>
				<details class="acc" data-acc>
					<summary>نحوه نگهداری<span class="acc__icon" aria-hidden="true"></span></summary>
					<div class="acc__body"><p>با آب سرد و برس نرم تمیز کنید و در سایه خشک کنید. از ماشین لباسشویی و خشک‌کن استفاده نکنید.</p></div>
				</details>
			</div>
		</div>
	</div>

	<?php if ( $d['specs'] ) : ?>
	<section class="section" aria-labelledby="detail-title">
		<div class="wrap">
			<div class="detail<?php echo $photo ? '' : ' detail--solo'; ?>">
				<div class="detail__text">
					<h2 class="h2" id="detail-title"><span class="en">THE <span class="dim">DETAILS</span></span></h2>
					<?php if ( $p->get_weight() || $d['sizes'] ) : ?>
						<dl class="stats stats--lg">
							<?php if ( $p->get_weight() ) : ?><div><dt class="num"><?php echo esc_html( $p->get_weight() ); ?><small>g</small></dt><dd>وزن هر لنگه</dd></div><?php endif; ?>
							<?php if ( $d['sizes'] ) : ?><div><dt class="num"><?php echo esc_html( reset( $d['sizes'] )['slug'] . '–' . end( $d['sizes'] )['slug'] ); ?></dt><dd>سایزبندی اروپایی</dd></div><?php endif; ?>
							<?php if ( $d['colors'] ) : ?><div><dt class="num"><?php echo esc_html( count( $d['colors'] ) ); ?></dt><dd>رنگ</dd></div><?php endif; ?>
						</dl>
					<?php endif; ?>
					<dl class="spec"><?php foreach ( $d['specs'] as $s ) : ?><div><dt><?php echo esc_html( $s['k'] ); ?></dt><dd><?php echo esc_html( $s['v'] ); ?></dd></div><?php endforeach; ?></dl>
					<?php if ( lc_size_url() ) : ?><a href="<?php echo esc_url( lc_size_url() ); ?>" class="btn btn--ghost">راهنمای سایز</a><?php endif; ?>
				</div>
				<?php if ( $photo ) : ?>
					<figure class="detail__ph"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $d['name'] ); ?> روی پا" width="1600" height="1100" loading="lazy" /><figcaption class="pill">روی پا</figcaption></figure>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php
	$related = array_filter( array_map( 'wc_get_product', wc_get_related_products( $d['id'], 4 ) ) );
	if ( $related ) :
		?>
		<section class="section" aria-labelledby="rel-title">
			<div class="wrap">
				<header class="shead">
					<h2 class="h2" id="rel-title"><span class="en">YOU MAY <span class="dim">ALSO LIKE</span></span></h2>
					<?php if ( $d['catUrl'] ) : ?><div class="shead__side"><a href="<?php echo esc_url( $d['catUrl'] ); ?>" class="ulink">مشاهده همه</a></div><?php endif; ?>
				</header>
				<div class="grid-cards"><?php foreach ( $related as $rp ) { get_template_part( 'template-parts/product-card', null, [ 'product' => $rp ] ); } ?></div>
			</div>
		</section>
	<?php endif; ?>

	<div class="buybar" data-buybar>
		<div class="buybar__in">
			<img src="<?php echo esc_url( $d['image'] ); ?>" alt="" width="56" height="56" />
			<div class="buybar__meta"><b class="en"><?php echo esc_html( $d['name'] ); ?></b><span class="small"><span data-buybar-price><?php echo esc_html( lc_price( $d['price'] ) ); ?></span> تومان</span></div>
			<button type="button" class="btn btn--sm" data-buybar-add><span data-label>انتخاب سایز</span></button>
		</div>
	</div>
	<?php
endwhile;
get_footer();
