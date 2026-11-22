<?php
/**
 * Template Name: ورود (Digits)
 * Sign in / sign up with an Iranian mobile number (Digits). Digits renders every step inside
 * [data-digits]; this page frames it. Rules (+98 only, SMS code or password, name + mobile to
 * register) live in inc/commerce/digits.php.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$image = lc_mod_img( 'lc_login_img', 'large', '' );
if ( ! $image && function_exists( 'lc_home_photos' ) ) {
	$ph    = lc_home_photos( (array) lc_home()['insta']['items'], '', 'large' );
	$image = $ph[0]['src'] ?? '';
}
?>
<section class="login wrap" data-login>
	<div class="login__art" aria-hidden="true">
		<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="" width="1200" height="1500" fetchpriority="high" /><?php endif; ?>
		<p class="login__word en"><?php echo esc_html( lc_brand_latin() ); ?></p>
	</div>
	<div class="login__panel">
		<span class="login__mark"><?php get_template_part( 'template-parts/logo' ); ?></span>
		<h1 class="fa-title login__title">ورود به حساب کاربری</h1>
		<p class="login__lead">شماره موبایل خود را وارد کنید. کد تایید برایتان پیامک می‌شود.</p>
		<div class="login__form" data-digits>
			<?php
			while ( have_posts() ) {
				the_post();
				$content = trim( get_the_content() );
				echo do_shortcode( false !== strpos( $content, '[df-form' ) ? $content : '[df-form]' ); // phpcs:ignore
			}
			?>
		</div>
		<ul class="login__perks">
			<li><svg class="icon"><use href="#i-truck"/></svg>ارسال به سراسر کشور</li>
			<li><svg class="icon"><use href="#i-swap"/></svg>۷ روز ضمانت تعویض</li>
			<li><svg class="icon"><use href="#i-tag"/></svg>ضمانت اصالت کالا</li>
		</ul>
	</div>
</section>
<?php
get_footer();
