<?php
defined( 'ABSPATH' ) || exit;
get_header();
$lc_shoe = '';
if ( function_exists( 'wc_get_products' ) ) {
	$lc_p    = wc_get_products( [ 'status' => 'publish', 'limit' => 1, 'orderby' => 'popularity' ] );
	$lc_shoe = $lc_p ? lc_product_data( $lc_p[0] )['image'] : '';
}
?>
<section class="notfound wrap">
	<p class="notfound__num en" aria-hidden="true">404</p>
	<?php if ( $lc_shoe ) : ?><img class="notfound__shoe" src="<?php echo esc_url( $lc_shoe ); ?>" alt="" width="1200" height="800" /><?php endif; ?>
	<h1 class="fa-title">صفحه مورد نظر پیدا نشد</h1>
	<p class="lead">ممکن است آدرس اشتباه باشد یا این محصول دیگر موجود نباشد.</p>
	<div class="notfound__cta">
		<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn">بازگشت به فروشگاه</a>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--ghost">صفحه اصلی</a>
	</div>
</section>
<?php
get_footer();
