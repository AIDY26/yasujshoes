<?php
/** Journal index and topic archives (Zagros). The first post on page one gets the wide card. */
defined( 'ABSPATH' ) || exit;
get_header();

global $wp_query;
$topic  = is_category() ? get_queried_object() : null;
$paged  = max( 1, (int) get_query_var( 'paged' ) );
$topics = [];
foreach ( get_posts( [ 'numberposts' => 60, 'fields' => 'ids' ] ) as $lc_pid ) {
	foreach ( get_the_category( $lc_pid ) as $lc_t ) {
		$topics[ $lc_t->term_id ] = $lc_t;
	}
}
?>
<section class="pagehead wrap">
	<nav class="crumbs" aria-label="مسیر"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><?php if ( $topic ) : ?><a href="<?php echo esc_url( lc_journal_url() ); ?>">مجله</a><span>/</span><span aria-current="page"><?php echo esc_html( $topic->name ); ?></span><?php else : ?><span aria-current="page">مجله</span><?php endif; ?></nav>
	<h1 class="h1"><span class="en">THE <span class="dim"><?php echo esc_html( $topic ? mb_strtoupper( lc_topic_en( $topic ) ) : 'JOURNAL' ); ?></span></span></h1>
	<p class="lead"><?php echo esc_html( $topic && $topic->description ? wp_strip_all_tags( $topic->description ) : 'راهنمای خرید، نکته‌های نگهداری کفش و معرفی مدل‌های جدید.' ); ?></p>
</section>
<?php if ( count( $topics ) > 1 ) : ?>
	<nav class="wrap helptabs" aria-label="موضوع‌ها">
		<div class="helptabs__in">
			<a href="<?php echo esc_url( lc_journal_url() ); ?>" class="chip<?php echo $topic ? '' : ' is-on'; ?>">همه</a>
			<?php foreach ( $topics as $t ) : ?><a href="<?php echo esc_url( get_category_link( $t ) ); ?>" class="chip<?php echo $topic && $topic->term_id === $t->term_id ? ' is-on' : ''; ?>"><?php echo esc_html( $t->name ); ?></a><?php endforeach; ?>
		</div>
	</nav>
<?php endif; ?>
<section class="wrap">
	<?php if ( have_posts() ) : ?>
		<?php
		if ( 1 === $paged && ! $topic ) :
			the_post();
			$cat = get_the_category();
			?>
			<a href="<?php the_permalink(); ?>" class="feature">
				<span class="feature__media"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', [ 'fetchpriority' => 'high', 'alt' => '' ] ); } ?></span>
				<span class="feature__body">
					<span class="kicker"><?php if ( $cat ) : ?><span class="en"><?php echo esc_html( lc_topic_en( $cat[0] ) ); ?></span><span>·</span><?php endif; ?><span><?php echo esc_html( lc_fa( lc_read_minutes() ) ); ?> دقیقه مطالعه</span></span>
					<h2 class="fa-title feature__title"><?php the_title(); ?></h2>
					<p class="lead"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
					<span class="btn btn--ghost">ادامه مطلب</span>
				</span>
			</a>
		<?php endif; ?>
		<div class="posts">
			<?php while ( have_posts() ) { the_post(); get_template_part( 'template-parts/post-card' ); } ?>
		</div>
		<?php
		$links = paginate_links( [ 'total' => $wp_query->max_num_pages, 'current' => $paged, 'type' => 'array', 'prev_text' => 'قبلی', 'next_text' => 'بعدی' ] );
		if ( $links ) :
			?>
			<nav class="pagination" aria-label="صفحه‌ها"><?php echo wp_kses_post( lc_fa( implode( '', $links ) ) ); ?></nav>
		<?php endif; ?>
	<?php else : ?>
		<div class="empty"><h2 class="fa-title">هنوز مطلبی منتشر نشده</h2></div>
	<?php endif; ?>
</section>
<?php
get_footer();
