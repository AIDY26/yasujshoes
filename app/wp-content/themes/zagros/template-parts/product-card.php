<?php
/**
 * Product card (Zagros). $args['product'] = WC_Product.
 * Data comes from lc_product_data(); the JSON in data-lc feeds quick add and the wishlist.
 */
defined( 'ABSPATH' ) || exit;
$p = $args['product'] ?? null;
if ( ! $p instanceof WC_Product ) {
	return;
}
$d     = lc_product_data( $p );
$quick = [ 'id' => $d['id'], 'name' => $d['name'], 'type' => $d['type'], 'price' => $d['price'], 'cat' => $d['cat'], 'colors' => $d['colors'], 'sizes' => $d['sizes'], 'variations' => $d['variations'], 'image' => $d['image'], 'url' => $d['url'] ];
$best  = (int) $p->get_total_sales() >= (int) apply_filters( 'lc_best_seller_sales', 5 );
$img   = $d['colors'][0]['img'] ?? $d['image'];
?>
<article class="pcard<?php echo $d['soldOut'] ? ' is-out' : ''; ?>" data-product data-id="<?php echo esc_attr( $d['id'] ); ?>" data-lc="<?php echo esc_attr( wp_json_encode( $quick, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?>">
	<a href="<?php echo esc_url( $d['url'] ); ?>" class="pcard__media" aria-label="<?php echo esc_attr( $d['name'] . '، ' . $d['cat'] ); ?>" data-product-link>
		<span class="pcard__panel">
			<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $d['name'] ); ?>" width="660" height="660" loading="lazy" decoding="async" />
			<?php if ( $d['onSale'] ) : ?><span class="pill pill--sale pcard__badge">٪<?php echo esc_html( lc_fa( $d['off'] ) ); ?> تخفیف</span>
			<?php elseif ( $d['soldOut'] ) : ?><span class="pill pcard__badge">ناموجود</span>
			<?php elseif ( $d['isNew'] ) : ?><span class="pill pill--new pcard__badge"><i></i>جدید</span>
			<?php elseif ( $best ) : ?><span class="pill pcard__badge"><i></i>پرفروش</span><?php endif; ?>
			<span class="pcard__price<?php echo $d['onSale'] ? ' is-sale' : ''; ?>">
				<?php if ( $d['onSale'] ) : ?><s><?php echo esc_html( lc_price( $d['regular'] ) ); ?></s><?php endif; ?>
				<b><?php echo esc_html( lc_price( $d['price'] ) ); ?></b><small>تومان</small>
			</span>
		</span>
	</a>
	<button class="pcard__wish" type="button" data-wish="<?php echo esc_attr( $d['id'] ); ?>" aria-label="افزودن <?php echo esc_attr( $d['name'] ); ?> به علاقه‌مندی‌ها" aria-pressed="false"><svg class="icon"><use href="#i-heart"/></svg></button>
	<div class="pcard__foot">
		<div class="pcard__meta">
			<a href="<?php echo esc_url( $d['url'] ); ?>" class="pcard__name en" data-product-link><?php echo esc_html( $d['name'] ); ?></a>
			<span class="pcard__cat"><?php echo esc_html( $d['cat'] ); ?></span>
		</div>
		<button class="btn btn--sm pcard__shop" type="button" data-quick-add<?php echo $d['soldOut'] ? ' disabled' : ''; ?>><svg class="icon"><use href="#i-bag"/></svg><?php echo $d['soldOut'] ? 'ناموجود' : 'خرید'; ?></button>
	</div>
</article>
