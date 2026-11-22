<?php
/** Journal post (Zagros): reading bar, cover, body, and the products the post talks about (_lc_products). */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$cat = get_the_category();
	$ids = array_filter( array_map( 'absint', (array) get_post_meta( get_the_ID(), '_lc_products', true ) ) );
	$products = $ids && function_exists( 'wc_get_products' )
		? wc_get_products( [ 'include' => $ids, 'status' => 'publish', 'limit' => 4, 'orderby' => 'post__in' ] )
		: ( function_exists( 'wc_get_products' ) ? wc_get_products( [ 'status' => 'publish', 'limit' => 4, 'orderby' => 'popularity' ] ) : [] );
	?>
	<span class="readbar" data-readbar aria-hidden="true"></span>
	<article class="post" data-post>
		<header class="post__head wrap">
			<nav class="crumbs" aria-label="مسیر"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><a href="<?php echo esc_url( lc_journal_url() ); ?>">مجله</a><?php if ( $cat ) : ?><span>/</span><a href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>"><?php echo esc_html( $cat[0]->name ); ?></a><?php endif; ?></nav>
			<p class="kicker"><?php if ( $cat ) : ?><span class="en"><?php echo esc_html( lc_topic_en( $cat[0] ) ); ?></span><span>·</span><?php endif; ?><span><?php echo esc_html( lc_fa( get_the_date( 'j F Y' ) ) ); ?></span><span>·</span><span><?php echo esc_html( lc_fa( lc_read_minutes() ) ); ?> دقیقه مطالعه</span></p>
			<h1 class="fa-title post__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</header>
		<?php if ( has_post_thumbnail() ) : ?><figure class="post__cover wrap"><?php the_post_thumbnail( 'full', [ 'fetchpriority' => 'high', 'alt' => '' ] ); ?></figure><?php endif; ?>
		<div class="post__body wrap">
			<aside class="post__aside">
				<p class="small">نویسنده: <?php echo esc_html( ( get_the_author() && get_the_author() !== get_the_author_meta( 'user_login' ) ? get_the_author() : 'تیم ' . preg_replace( '/^کفش\s+/u', '', lc_brand() ) ) ); ?></p>
				<button class="btn btn--ghost btn--sm" type="button" data-copy-link><svg class="icon"><use href="#i-share"/></svg>کپی لینک</button>
			</aside>
			<div class="prose"><?php the_content(); ?></div>
		</div>
	</article>
	<?php if ( $products ) : ?>
		<section class="section" aria-labelledby="post-prod">
			<div class="wrap">
				<header class="shead"><h2 class="h2" id="post-prod"><span class="en">IN THIS <span class="dim">STORY</span></span></h2></header>
				<div class="grid-cards"><?php foreach ( $products as $p ) { get_template_part( 'template-parts/product-card', null, [ 'product' => $p ] ); } ?></div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;
get_footer();
