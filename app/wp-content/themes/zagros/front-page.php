<?php
/**
 * Home (Zagros). Products and categories from WooCommerce; slides, photos, sale and copy from
 * «پیشخوان ← <نام سایت>» (inc/site-admin.php); stories and popups from their own menus.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$home    = lc_home();
$shop    = wc_get_page_permalink( 'shop' );
$slides  = lc_hero_slides();
$cats    = lc_categories();
$photos  = lc_home_photos( (array) $home['insta']['items'], $shop, 'large' );
$ph      = static fn( int $i ) => $photos[ $i % max( 1, count( $photos ) ) ]['src'] ?? wc_placeholder_img_src( 'large' );
$latest  = wc_get_products( [ 'status' => 'publish', 'limit' => 8, 'orderby' => 'date', 'order' => 'DESC', 'visibility' => 'catalog' ] );
$best    = lc_best_sellers( 8 );
$sale    = $home['sale'];
$sale_p  = $sale['show'] ? lc_home_sale_products( 12 ) : [];
$spot    = lc_spot_product();
$stories = lc_stories();
$insta   = $home['general']['instagram'];
$first   = $slides[0] ?? null;
?>

<?php if ( $slides ) : ?>
<section class="hero" data-hero data-hero-slider data-interval="<?php echo esc_attr( (string) $home['hero']['interval'] ); ?>" aria-roledescription="carousel" aria-label="مدل‌های شاخص">
	<div class="hero__top wrap" data-enter>
		<p class="hero__meta"><span class="en"><?php echo esc_html( lc_brand_latin() ); ?> / SNEAKERS &amp; RUNNERS</span><span><?php echo esc_html( $home['hero']['line1'] ?: 'کتونی و کفش رانینگ اورجینال' ); ?></span></p>
		<div class="hero__tabs" role="tablist" aria-label="انتخاب مدل" data-hs-tabs>
			<?php foreach ( $slides as $i => $s ) : ?>
				<button type="button" role="tab" class="hero__tab en<?php echo 0 === $i ? ' is-on' : ''; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-hs-tab="<?php echo (int) $i; ?>"><?php echo esc_html( $s['product']['name'] ); ?></button>
			<?php endforeach; ?>
		</div>
		<div class="hero__ctrl">
			<button class="icon-btn" type="button" data-hs-dir="prev" aria-label="مدل قبلی"><svg><use href="#i-chev"/></svg></button>
			<p class="hero__count num" aria-live="polite"><b data-hs-count>1</b> / <?php echo count( $slides ); ?></p>
			<button class="icon-btn" type="button" data-hs-dir="next" aria-label="مدل بعدی"><svg style="transform:scaleX(-1)"><use href="#i-chev"/></svg></button>
		</div>
	</div>

	<div class="hero__stage">
		<div class="hero__words" aria-hidden="true">
			<?php foreach ( $slides as $i => $s ) : ?><p class="hero__word en<?php echo 0 === $i ? ' is-on' : ''; ?>" data-hs-word="<?php echo (int) $i; ?>"><span><?php echo esc_html( $s['word'] ); ?></span></p><?php endforeach; ?>
		</div>
		<div class="turntable" aria-hidden="true" data-turntable>
			<div class="turntable__disc" data-turntable-disc>
				<svg viewBox="0 0 1000 1000" class="turntable__ticks"><circle cx="500" cy="500" r="470" class="tt-ring"/><circle cx="500" cy="500" r="352" class="tt-ring tt-ring--in"/><g data-tt-ticks></g></svg>
			</div>
			<span class="turntable__marker"><span class="en" data-tt-size><?php echo esc_html( $first['size'] ); ?></span></span>
		</div>
		<div class="hero__shoes">
			<?php foreach ( $slides as $i => $s ) : $c0 = $s['product']['colors'][0]; ?>
				<a href="<?php echo esc_url( $s['product']['url'] ); ?>" class="hero__shoe<?php echo 0 === $i ? ' is-on' : ''; ?>" data-hs-shoe="<?php echo (int) $i; ?>" tabindex="-1" aria-hidden="<?php echo 0 === $i ? 'false' : 'true'; ?>">
					<img src="<?php echo esc_url( $c0['img'] ); ?>" alt="<?php echo esc_attr( trim( $s['product']['name'] . '، ' . $c0['name'], '، ' ) ); ?>" width="1024" height="683" <?php echo 0 === $i ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async" data-hs-img />
				</a>
			<?php endforeach; ?>
			<span class="hero__shadow"></span>
		</div>
	</div>

	<div class="hero__bottom wrap" data-enter>
		<div class="hero__copy">
			<?php foreach ( $slides as $i => $s ) : ?>
				<p class="hero__lead<?php echo 0 === $i ? ' is-on' : ''; ?>" data-hs-lead="<?php echo (int) $i; ?>"><a href="<?php echo esc_url( $s['product']['url'] ); ?>" class="ulink en"><?php echo esc_html( $s['product']['name'] ); ?></a> <?php echo esc_html( $s['lead'] ); ?></p>
			<?php endforeach; ?>
		</div>
		<div class="hero__colors">
			<div class="swatches" data-hs-swatches role="group" aria-label="رنگ‌ها"></div>
			<div class="hero__colorname"><span data-hs-colorname></span><a href="<?php echo esc_url( $first['product']['url'] ); ?>" class="ulink small" data-hs-details>مشخصات محصول</a></div>
		</div>
		<div class="hero__cta">
			<?php if ( lc_size_url() ) : ?><a href="<?php echo esc_url( lc_size_url() ); ?>" class="btn btn--ghost">راهنمای سایز</a><?php endif; ?>
			<a href="<?php echo esc_url( $first['product']['url'] ); ?>" class="btn btn--split" data-hs-buy><span>خرید <span class="en" data-hs-name><?php echo esc_html( $first['product']['name'] ); ?></span></span><span class="btn__price" data-hs-price><?php echo esc_html( lc_price( $first['product']['price'] ) ); ?></span></a>
		</div>
	</div>
	<script type="application/json" id="zg-hero"><?php echo wp_json_encode( $slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
</section>
<?php endif; ?>

<?php if ( $stories ) : ?>
<section class="stories wrap" aria-label="استوری‌های اینستاگرام">
	<?php if ( $insta ) : ?><a class="stories__ig" href="<?php echo esc_url( $insta ); ?>" target="_blank" rel="noopener"><svg class="icon"><use href="#i-insta"/></svg><span class="en"><?php echo esc_html( lc_instagram_handle() ); ?></span></a><?php endif; ?>
	<div class="stories__row">
		<?php foreach ( $stories as $i => $st ) : ?>
			<button type="button" class="story" data-story="<?php echo (int) $i; ?>" data-key="<?php echo esc_attr( $st['key'] ); ?>" aria-label="دیدن استوری <?php echo esc_attr( $st['title'] ); ?>">
				<span class="story__ring"><img src="<?php echo esc_url( $st['cover'] ); ?>" alt="" width="72" height="72" loading="lazy" /></span>
				<span class="story__label"><?php echo esc_html( $st['title'] ); ?></span>
			</button>
		<?php endforeach; ?>
	</div>
	<script type="application/json" id="lc-stories-data" data-seconds="<?php echo esc_attr( (string) $home['general']['story_seconds'] ); ?>"><?php echo wp_json_encode( $stories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
</section>
<?php endif; ?>

<section class="statement section" aria-label="درباره <?php echo esc_attr( lc_brand() ); ?>">
	<p class="statement__text wrap" data-highlight>
		<span>از</span> <span>پیاده‌روی</span> <span>تو</span> <span>شهر</span> <span class="ph"><img src="<?php echo esc_url( $ph( 0 ) ); ?>" alt="" width="160" height="80" loading="lazy" /></span> <span>تا</span> <span>دویدن</span> <span>صبح</span> <span class="ph"><img src="<?php echo esc_url( $ph( 1 ) ); ?>" alt="" width="160" height="80" loading="lazy" /></span> <span>و</span> <span>کوه</span> <span>آخر</span> <span>هفته؛</span> <span class="ph"><img src="<?php echo esc_url( $ph( 2 ) ); ?>" alt="" width="160" height="80" loading="lazy" /></span> <span>کفش</span> <span>مناسبش</span> <span>رو</span> <span>اینجا</span> <span>پیدا</span> <span>کن.</span>
	</p>
</section>

<?php if ( $latest ) : ?>
<section class="section" aria-labelledby="new-title">
	<div class="wrap">
		<header class="shead shead--center">
			<h2 class="h2" id="new-title"><span class="en"><span class="dim">NEW</span> ARRIVALS</span></h2>
			<p class="lead">جدیدترین مدل‌های فصل؛ موجود و آماده ارسال.</p>
		</header>
		<div class="rail" data-rail>
			<div class="rail__track" data-rail-track>
				<?php foreach ( $latest as $p ) { get_template_part( 'template-parts/product-card', null, [ 'product' => $p ] ); } ?>
			</div>
			<div class="rail__nav">
				<button class="icon-btn" type="button" data-dir="prev" aria-label="قبلی"><svg><use href="#i-chev"/></svg></button>
				<button class="icon-btn icon-btn--solid" type="button" data-dir="next" aria-label="بعدی"><svg><use href="#i-chev"/></svg></button>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( count( $cats ) >= 3 ) : ?>
<section class="section" aria-labelledby="explore-title">
	<div class="wrap">
		<header class="shead">
			<h2 class="h2" id="explore-title"><span class="en">SHOP BY<br /><span class="dim">THE DAY</span></span></h2>
			<p class="lead">کفش مناسبت رو بر اساس نوع استفاده انتخاب کن.</p>
		</header>
		<div class="explore">
			<?php foreach ( array_slice( $cats, 0, 3 ) as $i => $c ) : ?>
				<a href="<?php echo esc_url( $c['url'] ); ?>" class="explore__card<?php echo 0 === $i ? ' explore__card--wide' : ''; ?>">
					<img src="<?php echo esc_url( lc_category_photo( $c['slug'], $c['img'] ) ); ?>" alt="" width="1200" height="1500" loading="lazy" decoding="async" />
					<span class="explore__tag">
						<span><b><?php echo esc_html( $c['name'] ); ?></b> <span class="en"><?php echo esc_html( lc_category_en( $c['slug'] ) ); ?></span><small><?php echo esc_html( lc_category_blurb( $c['slug'] ) ); ?></small></span>
						<span class="explore__go"><svg class="icon"><use href="#i-arrow-out"/></svg></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
		<div class="center-cta"><a href="<?php echo esc_url( $shop ); ?>" class="btn">مشاهده همه کفش‌ها</a></div>
	</div>
</section>
<?php endif; ?>

<?php if ( count( $photos ) >= 4 ) : ?>
<section class="section" aria-labelledby="mosaic-title">
	<div class="wrap">
		<header class="shead shead--center">
			<h2 class="h2" id="mosaic-title"><span class="en">WORN ALL DAY,<br /><span class="dim">EVERY DAY</span></span></h2>
			<p class="lead">چند ایده برای ست کردن کتونی با لباس‌های روزمره.</p>
		</header>
		<div class="mosaic">
			<div class="mosaic__col">
				<?php foreach ( [ 0, 1 ] as $k ) : $m = $photos[ $k ]; ?>
					<figure class="mosaic__ph<?php echo 1 === $k ? ' mosaic__ph--tall' : ''; ?>"><img src="<?php echo esc_url( $m['src'] ); ?>" alt="<?php echo esc_attr( $m['label'] ); ?>" width="<?php echo (int) $m['w']; ?>" height="<?php echo (int) $m['h']; ?>" loading="lazy" /></figure>
				<?php endforeach; ?>
			</div>
			<?php if ( $sale_p ) : $sp = lc_product_data( $sale_p[0] ); $max_off = max( array_map( static fn( $x ) => lc_product_data( $x )['off'], $sale_p ) ); $end = lc_sale_end_ts(); ?>
				<a href="<?php echo esc_url( add_query_arg( 'on_sale', 1, $shop ) ); ?>" class="salecard"<?php echo $end > time() ? ' data-countdown="' . esc_attr( gmdate( 'c', $end ) ) . '"' : ''; ?>>
					<h3 class="salecard__title"><span class="en">LAST CALL</span><span class="salecard__fa"><?php echo esc_html( $sale['title'] ?: 'حراج' ); ?></span></h3>
					<div class="salecard__stage">
						<svg class="salecard__ring" viewBox="0 0 400 400" aria-hidden="true"><g data-ticks="64"></g></svg>
						<img src="<?php echo esc_url( ( ! empty( $sale['img'] ) ? wp_get_attachment_image_url( (int) $sale['img'], 'large' ) : '' ) ?: ( $sp['colors'][0]['img'] ?? $sp['image'] ) ); ?>" alt="<?php echo esc_attr( $sp['name'] ); ?>" width="1200" height="800" loading="lazy" />
					</div>
					<?php if ( $end > time() ) : ?>
						<div class="cd" aria-label="زمان باقی‌مانده تا پایان حراج">
							<span><b data-cd="d">۰۰</b><small>روز</small></span>
							<span><b data-cd="h">۰۰</b><small>ساعت</small></span>
							<span><b data-cd="m">۰۰</b><small>دقیقه</small></span>
							<span><b data-cd="s">۰۰</b><small>ثانیه</small></span>
						</div>
					<?php endif; ?>
					<p class="salecard__text"><?php echo esc_html( $sale['sub'] ?: 'تا ٪' . lc_fa( $max_off ) . ' تخفیف روی کفش‌های رانینگ و روزمره؛ تا تمام شدن موجودی.' ); ?></p>
					<span class="btn btn--light">مشاهده حراجی‌ها</span>
				</a>
			<?php endif; ?>
			<div class="mosaic__col">
				<?php foreach ( [ 2, 3 ] as $k ) : $m = $photos[ $k ]; ?>
					<figure class="mosaic__ph<?php echo 2 === $k ? ' mosaic__ph--tall' : ''; ?>"><img src="<?php echo esc_url( $m['src'] ); ?>" alt="<?php echo esc_attr( $m['label'] ); ?>" width="<?php echo (int) $m['w']; ?>" height="<?php echo (int) $m['h']; ?>" loading="lazy" /></figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $spot ) : $sd = $spot['data']; $c0 = $sd['colors'][0] ?? null; ?>
<section class="section" aria-labelledby="spot-title">
	<div class="wrap">
		<header class="shead">
			<h2 class="h2" id="spot-title"><span class="en">PICK OF<br /><span class="dim">THE MONTH</span></span></h2>
			<p class="lead">پرفروش‌ترین مدل این ماه. سه دلیلی که مشتری‌ها دوستش دارند:</p>
		</header>
		<div class="spot" data-spot>
			<ol class="spot__notes">
				<?php foreach ( $spot['notes'] as $i => $n ) : ?>
					<li><span class="num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><div><h3><?php echo esc_html( $n['k'] ); ?></h3><p><?php echo esc_html( $n['v'] ); ?></p></div></li>
				<?php endforeach; ?>
			</ol>
			<div class="spot__stage">
				<span class="pill spot__pill en"><?php echo esc_html( $sd['name'] ); ?></span>
				<img src="<?php echo esc_url( $c0['img'] ?? $sd['image'] ); ?>" alt="<?php echo esc_attr( $sd['name'] ); ?>" width="1024" height="683" loading="lazy" data-spot-img />
				<span class="spot__shadow"></span>
			</div>
			<div class="spot__side">
				<?php if ( $spot['stats'] ) : ?>
					<dl class="stats"><?php foreach ( $spot['stats'] as $s ) : ?><div><dt class="num"><?php echo esc_html( $s['v'] ); ?><small><?php echo esc_html( $s['u'] ); ?></small></dt><dd><?php echo esc_html( $s['k'] ); ?></dd></div><?php endforeach; ?></dl>
				<?php endif; ?>
				<?php if ( count( $sd['colors'] ) > 1 ) : ?>
					<div class="spot__colors">
						<p class="quick__label">رنگ <b data-spot-colorname><?php echo esc_html( $c0['name'] ); ?></b></p>
						<div class="spot__thumbs" role="group" aria-label="رنگ‌های <?php echo esc_attr( $sd['name'] ); ?>">
							<?php foreach ( array_slice( $sd['colors'], 0, 4 ) as $i => $c ) : ?>
								<button type="button" class="spot__thumb" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-spot-color="<?php echo esc_url( $c['img'] ); ?>" data-name="<?php echo esc_attr( $c['name'] ); ?>" aria-label="<?php echo esc_attr( $c['name'] ); ?>"><img src="<?php echo esc_url( $c['img'] ); ?>" alt="" width="120" height="80" loading="lazy" /></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				<p class="small"><?php echo esc_html( lc_free_shipping() ? 'ارسال رایگان برای خرید بالای ' . lc_price_words( lc_free_shipping() ) . ' و ۷ روز ضمانت تعویض سایز.' : '۷ روز ضمانت تعویض سایز.' ); ?></p>
				<a href="<?php echo esc_url( $sd['url'] ); ?>" class="btn btn--split btn--block"><span>خرید <span class="en"><?php echo esc_html( $sd['name'] ); ?></span></span><span class="btn__price"><?php echo esc_html( lc_price( $sd['price'] ) ); ?></span></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $best ) : ?>
<section class="section" aria-labelledby="best-title">
	<div class="wrap">
		<header class="shead">
			<h2 class="h2" id="best-title"><span class="en">BEST <span class="dim">SELLERS</span></span></h2>
			<div class="shead__side">
				<p class="lead">محبوب‌ترین کفش‌های فروشگاه از نگاه خریدارها.</p>
				<a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', $shop ) ); ?>" class="ulink">مشاهده همه</a>
			</div>
		</header>
		<div class="grid-cards">
			<?php foreach ( $best as $p ) { get_template_part( 'template-parts/product-card', null, [ 'product' => $p ] ); } ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
$band_src  = array_filter( array_map( static fn( $s ) => wc_get_product( (int) $s['key'] ), $slides ) ) ?: $latest;
$band_imgs = array_values( array_filter( array_map( static fn( $p ) => lc_product_data( $p )['image'], array_slice( array_values( $band_src ), 0, 4 ) ) ) );
?>
<section class="section wrap" aria-label="خدمات فروشگاه">
	<div class="band">
		<?php foreach ( [ 0, 1 ] as $row ) : ?>
			<div class="band__row<?php echo $row ? ' band__row--rev' : ''; ?>" aria-hidden="<?php echo $row ? 'true' : 'false'; ?>">
				<?php foreach ( [ 0, 1 ] as $copy ) : ?>
					<div class="band__set"<?php echo $copy ? ' aria-hidden="true"' : ''; ?>>
						<?php foreach ( lc_band_words() as $k => $w ) : ?>
							<span class="band__word<?php echo $w['en'] ? ' en' : ''; ?>"><?php echo esc_html( $w['text'] ); ?></span><?php if ( isset( $band_imgs[ $k ] ) ) : ?><img class="band__shoe" src="<?php echo esc_url( $band_imgs[ $k ] ); ?>" alt="" width="92" height="92" loading="lazy" /><?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php if ( $insta ) : ?>
<section class="section" aria-labelledby="ig-title">
	<div class="wrap">
		<header class="shead">
			<h2 class="h2" id="ig-title"><span class="en">FOUND US ON<br /><span class="dim">INSTAGRAM?</span></span></h2>
			<p class="lead"><?php echo esc_html( $home['insta']['text'] ?: 'سوالی درباره سایز، رنگ یا موجودی دارید؟ در دایرکت اینستاگرام بپرسید؛ هر روز جواب می‌دهیم.' ); ?></p>
		</header>
		<div class="ig">
			<figure class="ig__ph ig__ph--big"><img src="<?php echo esc_url( $ph( 4 ) ); ?>" alt="" width="1200" height="1200" loading="lazy" /><figcaption class="pill">پست هفته</figcaption></figure>
			<figure class="ig__ph"><img src="<?php echo esc_url( $ph( 5 ) ); ?>" alt="" width="1200" height="1200" loading="lazy" /></figure>
			<div class="ig__card">
				<ul class="ig__list">
					<li><svg class="icon"><use href="#i-ruler"/></svg><div><b>مشاوره انتخاب سایز</b><span>عکس پایتان را کنار خط‌کش بفرستید تا سایز مناسب هر مدل را بگوییم.</span></div></li>
					<li><svg class="icon"><use href="#i-tag"/></svg><div><b>استعلام موجودی</b><span>قبل از سفارش، موجودی رنگ و سایز مورد نظرتان را بپرسید.</span></div></li>
					<li><svg class="icon"><use href="#i-truck"/></svg><div><b>پیگیری سفارش</b><span>شماره سفارش را بفرستید تا کد رهگیری را برایتان ارسال کنیم.</span></div></li>
				</ul>
				<a href="<?php echo esc_url( $insta ); ?>" class="btn btn--split btn--block" target="_blank" rel="noopener"><span><?php echo esc_html( $home['insta']['btn_text'] ?: 'دنبال کردن در اینستاگرام' ); ?></span><span class="btn__price en"><?php echo esc_html( lc_instagram_handle() ); ?></span></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>
<?php
get_footer();
