<?php
/** Journal card (Zagros): photo, topic and date, title, two lines. Use inside the loop. */
defined( 'ABSPATH' ) || exit;
$cat = get_the_category();
?>
<a href="<?php the_permalink(); ?>" <?php post_class( 'postcard' ); ?>>
	<span class="postcard__media">
		<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'large', [ 'loading' => 'lazy', 'alt' => '' ] ); else : ?>
			<img src="<?php echo esc_url( lc_mark_url() ); ?>" alt="" />
		<?php endif; ?>
	</span>
	<span class="kicker"><?php if ( $cat ) : ?><span class="en"><?php echo esc_html( lc_topic_en( $cat[0] ) ); ?></span><span>·</span><?php endif; ?><span><?php echo esc_html( lc_fa( get_the_date( 'j F Y' ) ) ); ?></span></span>
	<h3 class="postcard__title"><?php the_title(); ?></h3>
	<p class="small"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
</a>
