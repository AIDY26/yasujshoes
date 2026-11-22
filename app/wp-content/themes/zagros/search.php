<?php
/** Site search for posts and pages. Product searches (post_type=product) use the shop template. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="pagehead wrap">
	<nav class="crumbs" aria-label="مسیر"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span aria-current="page">جستجو</span></nav>
	<h1 class="h1"><span class="en">SEARCH</span></h1>
	<p class="lead">نتیجه جستجو برای «<?php echo esc_html( get_search_query() ); ?>»</p>
</section>
<section class="wrap">
	<?php if ( have_posts() ) : ?>
		<div class="posts"><?php while ( have_posts() ) { the_post(); get_template_part( 'template-parts/post-card' ); } ?></div>
	<?php else : ?>
		<div class="empty">
			<h2 class="fa-title">چیزی پیدا نشد</h2>
			<p class="lead">اگر دنبال کفش هستید، در فروشگاه جستجو کنید.</p>
			<a class="btn" href="<?php echo esc_url( add_query_arg( [ 's' => get_search_query(), 'post_type' => 'product' ], home_url( '/' ) ) ); ?>">جستجو در محصولات</a>
		</div>
	<?php endif; ?>
</section>
<?php
get_footer();
