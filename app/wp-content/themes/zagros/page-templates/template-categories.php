<?php
/**
 * Template Name: دسته‌بندی‌ها
 * Every top category with its photo and count (WooCommerce). Created once at /categories/.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$cats  = function_exists( 'lc_category_index' ) ? lc_category_index() : [];
$total = array_sum( wp_list_pluck( $cats, 'count' ) );
$shop  = wc_get_page_permalink( 'shop' );
?>
<section class="pagehead wrap">
	<nav class="crumbs" aria-label="مسیر"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><a href="<?php echo esc_url( $shop ); ?>">فروشگاه</a><span>/</span><span aria-current="page">دسته‌بندی‌ها</span></nav>
	<h1 class="h1"><span class="en">SHOP BY <span class="dim">TYPE</span></span></h1>
	<p class="lead"><b><?php echo esc_html( lc_fa( count( $cats ) ) ); ?></b> دسته‌بندی و <b><?php echo esc_html( lc_fa( $total ) ); ?></b> کالا</p>
</section>
<section class="wrap">
	<?php if ( $cats ) : ?>
		<div class="cats">
			<?php foreach ( $cats as $c ) : $slug = basename( untrailingslashit( wp_parse_url( $c['url'], PHP_URL_PATH ) ) ); ?>
				<a href="<?php echo esc_url( $c['url'] ); ?>" class="catcard">
					<span class="catcard__media"><img src="<?php echo esc_url( lc_category_photo( rawurldecode( $slug ), $c['img'] ) ); ?>" alt="" width="800" height="800" loading="lazy" /></span>
					<span class="catcard__foot">
						<span><b><?php echo esc_html( $c['name'] ); ?></b> <span class="en"><?php echo esc_html( lc_category_en( rawurldecode( $slug ) ) ); ?></span></span>
						<span class="catcard__count"><?php echo esc_html( lc_fa( $c['count'] ) ); ?> کالا</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="empty"><h2 class="fa-title">هنوز دسته‌ای ساخته نشده</h2><a href="<?php echo esc_url( $shop ); ?>" class="btn btn--ghost">مشاهده همه کفش‌ها</a></div>
	<?php endif; ?>
</section>
<?php
get_footer();
