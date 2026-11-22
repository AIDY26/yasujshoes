<?php
/** Fallback for anything without a dedicated template (posts, archives, search). */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="lc-page">
	<div class="wrap">
		<h1 class="page__title"><?php echo esc_html( is_search() ? 'جستجو: ' . get_search_query() : ( is_archive() ? wp_strip_all_tags( get_the_archive_title() ) : get_the_title() ) ); ?></h1>
		<div class="page__content entry">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'entry__item' ); ?>>
						<?php if ( ! is_singular() ) : ?><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?>
						<?php else : the_content(); endif; ?>
					</article>
				<?php endwhile; ?>
			<?php else : ?>
				<p>چیزی پیدا نشد.</p>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
