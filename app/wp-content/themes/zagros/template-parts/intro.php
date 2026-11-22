<?php
/**
 * The opening screen of the home page (every full load, when «صفحه ورود» is on in
 * پیشخوان ← تنظیمات کلی). It is also the loader: src/js/modules/intro.js holds the page until the
 * hero's first shoe, the fonts and the images of the first screen are loaded and decoded.
 *
 * The idea is the hero's own size dial seen face on: while things load it makes one full turn,
 * size by size under the red marker, and stops on the size the hero's first shoe shows; then the
 * screen lifts like a curtain whose lower edge is a Zagros ridge (one red peak: the logo's mark)
 * and the hero's dial swings to that same size underneath.
 * Styles: src/styles/intro.css.
 */
defined( 'ABSPATH' ) || exit;

$lc_sizes  = range( 36, 47 );
$lc_slides = function_exists( 'lc_hero_slides' ) ? lc_hero_slides() : [];
$lc_target = (int) ( $lc_slides[0]['size'] ?? 42 );
if ( ! in_array( $lc_target, $lc_sizes, true ) ) {
	$lc_target = 42;
}
// the dial starts one size past the target and turns a full round back to it (intro.js does the
// turning); the first position is set here so the first paint is already right
$lc_ti    = array_search( $lc_target, $lc_sizes, true );
$lc_first = $lc_sizes[ ( $lc_ti + 1 ) % 12 ];
$lc_rot   = -90 - ( $lc_ti + 1 ) * 30;
?>
<div class="zintro" data-intro data-target="<?php echo esc_attr( (string) $lc_target ); ?>" aria-hidden="true">
	<div class="zintro__curtain">
		<div class="zintro__in">
			<div class="zintro__dial">
				<svg class="zintro__face" viewBox="0 0 1000 1000" style="transform: rotate(<?php echo (int) $lc_rot; ?>deg)">
					<circle class="zintro__ring" cx="500" cy="500" r="470" />
					<circle class="zintro__ring zintro__ring--in" cx="500" cy="500" r="352" />
					<g class="zintro__ticks" data-intro-ticks>
						<?php
						// 12 sizes, 4 ticks each; size i sits at i × 30° (0° = 3 o'clock); the group turns so the current size is at 12 o'clock
						for ( $i = 0; $i < 48; $i++ ) {
							$a     = $i * 7.5;
							$rad   = deg2rad( $a );
							$major = 0 === $i % 4;
							$r1    = $major ? 428 : 446;
							printf(
								'<line class="zintro__tick%s" x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" />',
								$major ? ' zintro__tick--major' : '',
								500 + cos( $rad ) * $r1,
								500 + sin( $rad ) * $r1,
								500 + cos( $rad ) * 462,
								500 + sin( $rad ) * 462
							);
							if ( $major ) {
								$s = $lc_sizes[ $i / 4 ];
								$x = 500 + cos( $rad ) * 394;
								$y = 500 + sin( $rad ) * 394;
								printf( '<text class="zintro__label%5$s" data-size="%1$d" x="%2$.1f" y="%3$.1f" transform="rotate(%4$.1f %2$.1f %3$.1f)">%1$d</text>', (int) $s, $x, $y, $a + 90, $s === $lc_first ? ' is-on' : '' );
							}
						}
						?>
					</g>
				</svg>
				<span class="zintro__marker"><span class="en">EU <b data-intro-size><?php echo esc_html( (string) $lc_first ); ?></b></span></span>
				<div class="zintro__brand">
					<?php get_template_part( 'template-parts/logo' ); ?>
					<p class="zintro__fa"><?php echo esc_html( lc_brand() ); ?></p>
				</div>
			</div>
		</div>
		<?php // cropped, never stretched (xMidYMin slice): the summit and its red cap keep their shape at every width ?>
		<svg class="zintro__ridge" viewBox="0 0 2400 120" preserveAspectRatio="xMidYMin slice">
			<path class="zintro__ridge-fill" d="M0 0H2400V60L2330 82 2262 56 2190 90 2120 62 2046 96 1980 66 1904 100 1840 58 1776 84 1706 48 1650 70 1590 40 1530 68 1470 34 1404 62 1338 30 1270 56 1218 31 1200 0 1182 31 1128 56 1060 26 996 60 930 36 866 70 802 44 740 78 680 50 610 92 546 60 480 88 410 56 340 94 270 64 200 90 130 58 60 84 0 66Z" />
			<path class="zintro__peak" d="M1200 0 1211.6 20 1188.4 20Z" />
		</svg>
	</div>
</div>
