<?php
/** Theme presentation around WooCommerce's native customer account. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$classes[] = 'lc-account-page';
			if ( ! is_wc_endpoint_url() ) {
				$classes[] = 'lc-account-dashboard';
			}
		}
		return array_unique( $classes );
	}
);

// Theme customers keep one default shipping address. WooCommerce still stores
// billing details on orders where gateways require them, but it does not expose
// a separate billing-address book in My Account.
add_filter(
	'woocommerce_my_account_get_addresses',
	static function ( array $addresses, int $customer_id ): array {
		return [ 'shipping' => __( 'نشانی ارسال', 'shopkit' ) ];
	},
	20,
	2
);

add_filter(
	'woocommerce_my_account_my_address_description',
	static function ( string $description ): string {
		return __( 'نشانی ذخیره‌شده برای ارسال سفارش‌های بعدی استفاده می‌شود.', 'shopkit' );
	}
);

add_filter(
	'woocommerce_my_account_edit_address_title',
	static function ( string $title, string $load_address ): string {
		return 'shipping' === $load_address ? __( 'نشانی ارسال', 'shopkit' ) : $title;
	},
	20,
	2
);

// Give the empty shipping-address state a clear next step in Persian.
add_filter(
	'gettext',
	static function ( string $translation, string $text, string $domain ): string {
		if ( 'woocommerce' === $domain && 'You have not set up this type of address yet.' === $text && function_exists( 'is_account_page' ) && is_account_page() ) {
			return __( 'هنوز نشانی ارسالی ثبت نکرده‌اید. برای تکمیل خریدهای بعدی، نشانی خود را اضافه کنید.', 'shopkit' );
		}
		return $translation;
	},
	20,
	3
);

// Direct billing-address URLs and POSTs are blocked before WooCommerce's
// address form handler runs. The edit-address endpoint itself remains native.
add_action(
	'template_redirect',
	static function (): void {
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_wc_endpoint_url( 'edit-address' ) ) {
			return;
		}

		$requested_address = sanitize_title( (string) get_query_var( 'edit-address', '' ) );
		$address_type      = wc_edit_address_i18n( $requested_address, true );
		if ( 'billing' !== $address_type ) {
			return;
		}

		wc_add_notice( __( 'در این فروشگاه فقط نشانی ارسال نگهداری می‌شود.', 'shopkit' ), 'notice' );
		wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
		exit;
	},
	1
);

// Keep the account form aligned with the Iran-only shop even if an older
// customer record contains a different country. The hidden country value is
// required by WooCommerce's native address-save handler and province lookup.
add_filter(
	'woocommerce_my_account_edit_address_field_value',
	static function ( $value, string $key, string $load_address ) {
		if ( 'shipping_country' === $key && 'shipping' === $load_address ) {
			return WC()->countries->get_base_country() ?: 'IR';
		}
		return $value;
	},
	20,
	3
);

// WooCommerce otherwise omits shipping_phone when the store's address-field
// preference hides it. Keep this account-specific field in both rendering and
// WooCommerce's native save/required-field validation path.
add_filter(
	'woocommerce_shipping_fields',
	static function ( array $fields, string $country ): array {
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$fields['shipping_phone'] = array_merge(
			$fields['shipping_phone'] ?? [],
			[
				'label'        => __( 'تلفن', 'shopkit' ),
				'required'     => true,
				'type'         => 'tel',
				'class'        => [ 'form-row-wide' ],
				'validate'     => [ 'phone' ],
				'autocomplete' => 'tel',
				'priority'     => 70,
			]
		);
		if ( isset( $fields['shipping_city'] ) ) {
			$fields['shipping_city']['type'] = 'shipping_city';
			$fields['shipping_city']['label'] = __( 'شهر', 'shopkit' );
			$fields['shipping_city']['required'] = true;
			$fields['shipping_city']['priority'] = 40;
		}
		return $fields;
	},
	100,
	2
);

add_filter(
	'woocommerce_address_to_edit',
	'lc_customize_account_shipping_address_fields',
	20,
	2
);

/** Show only the requested, required shipping fields in the chosen order. */
function lc_customize_account_shipping_address_fields( array $address, string $load_address ): array {
	if ( 'shipping' !== $load_address || ! WC()->countries ) {
		return $address;
	}

	$country = WC()->countries->get_base_country() ?: 'IR';
	$fields  = WC()->countries->get_address_fields( $country, 'shipping_' );
	$labels  = [
		'shipping_first_name' => [ 'label' => __( 'نام', 'shopkit' ), 'priority' => 10, 'class' => [ 'form-row-first' ] ],
		'shipping_last_name'  => [ 'label' => __( 'نام خانوادگی', 'shopkit' ), 'priority' => 20, 'class' => [ 'form-row-last' ] ],
		'shipping_state'      => [ 'label' => __( 'استان', 'shopkit' ), 'priority' => 30, 'class' => [ 'form-row-first' ] ],
		'shipping_city'       => [ 'label' => __( 'شهر', 'shopkit' ), 'priority' => 40, 'class' => [ 'form-row-last' ] ],
		'shipping_address_1'  => [ 'label' => __( 'خیابان', 'shopkit' ), 'placeholder' => __( 'نام خیابان، کوچه و پلاک', 'shopkit' ), 'priority' => 50, 'class' => [ 'form-row-wide' ] ],
		'shipping_postcode'   => [ 'label' => __( 'کدپستی', 'shopkit' ), 'priority' => 60, 'class' => [ 'form-row-wide' ] ],
		'shipping_phone'      => [ 'label' => __( 'تلفن', 'shopkit' ), 'type' => 'tel', 'priority' => 70, 'class' => [ 'form-row-wide' ], 'validate' => [ 'phone' ], 'autocomplete' => 'tel' ],
	];

	$ordered = [];
	foreach ( $labels as $key => $settings ) {
		$field = $fields[ $key ] ?? $address[ $key ] ?? [];
		$field = array_merge( $field, $settings );
		$field['required'] = true;
		$field['value']    = $address[ $key ]['value'] ?? '';
		if ( 'shipping_state' === $key && function_exists( 'lc_resolve_shipping_state_id' ) ) {
			$field['value'] = lc_resolve_shipping_state_id( $field['value'] );
		}
		if ( 'shipping_city' === $key && function_exists( 'lc_resolve_shipping_city_id' ) ) {
			$field['value'] = lc_resolve_shipping_city_id( $field['value'], $address['shipping_state']['value'] ?? '' );
		}

		if ( 'shipping_phone' === $key && '' === $field['value'] ) {
			$field['value'] = get_user_meta( get_current_user_id(), 'billing_phone', true );
		}

		$ordered[ $key ] = $field;
	}

	$country_field = $fields['shipping_country'] ?? $address['shipping_country'] ?? [];
	$country_field = array_merge(
		$country_field,
		[
			'type'     => 'hidden',
			'label'    => '',
			'required' => true,
			'value'    => $country,
			'default'  => $country,
			'class'    => [ 'lc-account-hidden-country' ],
			'priority' => 999,
		]
	);
	$ordered['shipping_country'] = $country_field;

	return $ordered;
}

// WooCommerce stores PWS city select values as term IDs. Keep the customer
// profile's visible address as a city name, resolving it back to the PWS ID
// only when the account or checkout form is rendered.
add_action(
	'woocommerce_customer_save_address',
	static function ( int $customer_id, string $address_type, array $address = [], $customer = null ): void {
		if ( 'shipping' !== $address_type || empty( $address['shipping_city'] ) || ! function_exists( 'PWS' ) ) {
			return;
		}
		$city_id = absint( $address['shipping_city'] );
		if ( ! $city_id ) {
			return;
		}
		$city_name = PWS()->get_city( $city_id );
		if ( ! $city_name ) {
			return;
		}

		$customer = $customer instanceof WC_Customer ? $customer : new WC_Customer( $customer_id );
		$customer->set_shipping_city( $city_name );
		$customer->save();
	},
	20,
	4
);

// Keep WooCommerce's saved customer city human-readable after checkout saves
// the PWS city term ID into the shipping customer object.
add_action(
	'woocommerce_checkout_update_customer',
	static function ( WC_Customer $customer, array $data ): void {
		if ( empty( $data['shipping_city'] ) || ! function_exists( 'PWS' ) ) {
			return;
		}
		$city_id = absint( $data['shipping_city'] );
		if ( ! $city_id ) {
			return;
		}
		$city_name = PWS()->get_city( $city_id );
		if ( $city_name ) {
			$customer->set_shipping_city( $city_name );
		}
	},
	20,
	2
);

add_action( 'woocommerce_before_account_navigation', 'lc_render_account_welcome', 5 );

/** Render a branded welcome panel using the authenticated customer's name. */
function lc_render_account_welcome(): void {
	$user = wp_get_current_user();
	if ( ! $user->exists() ) {
		return;
	}

	$name = $user->first_name ? $user->first_name : $user->display_name;
	$name = trim( $name );
	$initial = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1, 'UTF-8' ) : substr( $name, 0, 1 );
	?>
	<section class="lc-account-welcome" aria-labelledby="lc-account-welcome-title">
		<div class="lc-account-welcome__copy">
			<h1 id="lc-account-welcome-title"><?php printf( esc_html__( 'سلام، %s!', 'shopkit' ), esc_html( $name ) ); ?></h1>
			<p class="lc-account-welcome__description"><?php esc_html_e( 'سفارش‌ها، نشانی‌ها و اطلاعات حسابتان را از اینجا به‌راحتی مدیریت کنید.', 'shopkit' ); ?></p>
			<a class="lc-account-welcome__shop-link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<span><?php esc_html_e( 'رفتن به فروشگاه', 'shopkit' ); ?></span>
				<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 12H5m0 0 6 6m-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>
		<div class="lc-account-welcome__avatar" aria-hidden="true"><span><?php echo esc_html( $initial ); ?></span></div>
	</section>
	<?php
}

add_action( 'woocommerce_account_dashboard', 'lc_render_account_shortcuts', 20 );

// Remove WooCommerce's separate billing-address editor from the account area.
add_filter( 'woocommerce_account_menu_items', static function ( array $items ): array {
	unset( $items['payment-methods'] );
	return $items;
}, 100 );

/** Add dashboard shortcuts that link back to WooCommerce account endpoints. */
function lc_render_account_shortcuts(): void {
	$user = wp_get_current_user();
	if ( ! $user->exists() ) {
		return;
	}

	$order_count = wc_get_customer_order_count( $user->ID );
	$shortcuts = [
		[
			'class'       => 'orders',
			'title'       => __( 'سفارش‌های من', 'shopkit' ),
			'description' => __( 'وضعیت و جزئیات خریدهایتان را ببینید.', 'shopkit' ),
			'url'         => wc_get_account_endpoint_url( 'orders' ),
			'metric'      => lc_fa( $order_count ),
			'icon'        => '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3.75h8l4 4v12.5H7a2 2 0 0 1-2-2v-12.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M15 3.75v4h4M9 12h6m-6 3.5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
		],
		[
			'class'       => 'addresses',
			'title'       => __( 'نشانی‌های من', 'shopkit' ),
			'description' => __( 'نشانی ارسال سفارش‌هایتان را مدیریت کنید.', 'shopkit' ),
			'url'         => wc_get_account_endpoint_url( 'edit-address' ),
			'metric'      => '',
			'icon'        => '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.25" stroke="currentColor" stroke-width="1.7"/></svg>',
		],
		[
			'class'       => 'details',
			'title'       => __( 'اطلاعات حساب', 'shopkit' ),
			'description' => __( 'نام و اطلاعات ورودتان را به‌روز کنید.', 'shopkit' ),
			'url'         => wc_get_account_endpoint_url( 'edit-account' ),
			'metric'      => '',
			'icon'        => '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M5 20c.6-3.2 3.4-5.25 7-5.25s6.4 2.05 7 5.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
		],
	];
	?>
	<section class="lc-account-shortcuts" aria-labelledby="lc-account-shortcuts-title">
		<div class="lc-account-shortcuts__heading">
			<h2 id="lc-account-shortcuts-title"><?php esc_html_e( 'میان‌برهای حساب شما', 'shopkit' ); ?></h2>
		</div>
		<div class="lc-account-shortcuts__grid">
			<?php foreach ( $shortcuts as $shortcut ) : ?>
				<a class="lc-account-shortcut lc-account-shortcut--<?php echo esc_attr( $shortcut['class'] ); ?>" href="<?php echo esc_url( $shortcut['url'] ); ?>">
					<span class="lc-account-shortcut__icon"><?php echo $shortcut['icon']; // Static, project-owned SVG markup. ?></span>
					<span class="lc-account-shortcut__copy">
						<span class="lc-account-shortcut__title"><?php echo esc_html( $shortcut['title'] ); ?></span>
						<span class="lc-account-shortcut__description"><?php echo esc_html( $shortcut['description'] ); ?></span>
					</span>
					<?php if ( '' !== $shortcut['metric'] ) : ?>
						<span class="lc-account-shortcut__metric" aria-label="<?php echo esc_attr( sprintf( __( '%s سفارش', 'shopkit' ), $shortcut['metric'] ) ); ?>"><?php echo esc_html( $shortcut['metric'] ); ?></span>
					<?php endif; ?>
					<svg class="lc-account-shortcut__arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 12H5m0 0 6 6m-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

// The store sells no downloads; drop the empty tab.
add_filter( 'woocommerce_account_menu_items', static function ( array $items ): array {
	unset( $items['downloads'] );
	return $items;
} );
