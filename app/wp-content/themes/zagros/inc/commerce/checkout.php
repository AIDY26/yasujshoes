<?php
/** Checkout field and language integration for an Iran-only store. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Resolve legacy province IDs and saved names against the active location provider. */
function lc_resolve_shipping_state_id( $state_value ): string {
	$state_value = is_scalar( $state_value ) ? trim( (string) $state_value ) : '';
	if ( '' === $state_value || ! function_exists( 'PWS' ) ) {
		return $state_value;
	}
	$states = PWS()->states();
	if ( isset( $states[ $state_value ] ) ) {
		return $state_value;
	}
	$legacy = ctype_digit( $state_value ) ? get_term( (int) $state_value, 'state_city' ) : null;
	$name = $legacy && ! is_wp_error( $legacy ) ? $legacy->name : $state_value;
	$id = array_search( $name, $states, true );
	return false === $id ? '' : (string) $id;
}

/** Resolve a saved Persian city name to the active PWS or Tapin city ID. */
function lc_resolve_shipping_city_id( $city_value, $state_id ): string {
	$city_value = is_scalar( $city_value ) ? trim( (string) $city_value ) : '';
	$state_id   = absint( lc_resolve_shipping_state_id( $state_id ) );
	if ( '' === $city_value || ! $state_id || ! function_exists( 'PWS' ) ) {
		return $city_value;
	}

	$cities = PWS()->cities( $state_id );
	if ( ! is_array( $cities ) || empty( $cities ) ) {
		return $city_value;
	}

	$city_id = absint( $city_value );
	if ( $city_id && isset( $cities[ $city_id ] ) ) {
		return (string) $city_id;
	}
	$legacy = $city_id ? get_term( $city_id, 'state_city' ) : null;
	if ( $legacy && ! is_wp_error( $legacy ) ) {
		$city_value = $legacy->name;
	}

	$normalize = static function ( string $value ): string {
		$value = str_replace( [ 'ي', 'ك', "\u{200c}" ], [ 'ی', 'ک', ' ' ], trim( $value ) );
		return preg_replace( '/\s+/u', ' ', $value ) ?? $value;
	};
	$wanted = $normalize( $city_value );
	foreach ( $cities as $id => $name ) {
		if ( $wanted === $normalize( (string) $name ) ) {
			return (string) absint( $id );
		}
	}

	// Do not present an invalid option in PWS's required city selector.
	return '';
}

// Customers maintain one shipping address. Checkout shows that address form,
// while the billing address needed by WooCommerce and payment gateways is
// copied from the shipping values before validation and order creation.
add_filter( 'woocommerce_checkout_fields', static function ( array $fields ): array {
	$fields['billing']  = $fields['billing'] ?? [];
	$fields['shipping'] = $fields['shipping'] ?? [];

	$billing_email = $fields['billing']['billing_email'] ?? [
		'label'        => __( 'ایمیل', 'shopkit' ),
		'required'     => false,
		'type'         => 'email',
		'class'        => [ 'form-row-wide' ],
		'validate'     => [ 'email' ],
		'autocomplete' => 'email',
	];

	// Remove every plugin-added billing field too; the order's billing address
	// is filled from the shipping fields below, without a second address form.
	$fields['billing'] = [];

	$needs_shipping_address = WC()->cart && WC()->cart->needs_shipping_address();
	if ( $needs_shipping_address ) {
		$visible_fields = [
			'shipping_first_name' => [ 'label' => __( 'نام', 'shopkit' ), 'placeholder' => '', 'priority' => 10, 'class' => [ 'form-row-first' ] ],
			'shipping_last_name'  => [ 'label' => __( 'نام خانوادگی', 'shopkit' ), 'placeholder' => '', 'priority' => 20, 'class' => [ 'form-row-last' ] ],
			'shipping_state'      => [ 'label' => __( 'استان', 'shopkit' ), 'priority' => 30, 'class' => [ 'form-row-first' ] ],
			'shipping_city'       => [ 'label' => __( 'شهر', 'shopkit' ), 'priority' => 40, 'type' => 'shipping_city', 'class' => [ 'form-row-last' ] ],
			'shipping_address_1'  => [ 'label' => __( 'خیابان', 'shopkit' ), 'placeholder' => __( 'نام خیابان، کوچه و پلاک', 'shopkit' ), 'priority' => 50, 'class' => [ 'form-row-wide' ] ],
			'shipping_postcode'   => [ 'label' => __( 'کدپستی', 'shopkit' ), 'priority' => 60, 'class' => [ 'form-row-wide' ] ],
			'shipping_phone'      => [ 'label' => __( 'تلفن', 'shopkit' ), 'priority' => 70, 'type' => 'tel', 'class' => [ 'form-row-wide' ], 'validate' => [ 'phone' ], 'autocomplete' => 'tel' ],
		];
		foreach ( $visible_fields as $key => $settings ) {
			$field = array_merge( $fields['shipping'][ $key ] ?? [], $settings );
			$field['required'] = true;
			$fields['shipping'][ $key ] = $field;
		}

		$country_key = 'shipping_country';
		$fields['shipping'][ $country_key ] = array_merge(
			$fields['shipping'][ $country_key ] ?? [],
			[ 'type' => 'hidden', 'default' => 'IR', 'required' => true, 'priority' => 999 ]
		);
		unset( $fields['shipping']['shipping_company'], $fields['shipping']['shipping_address_2'], $fields['shipping']['shipping_district'] );

		$billing_email['label'] = __( 'ایمیل برای پیگیری سفارش', 'shopkit' );
		$billing_email['required'] = false;
		$billing_email['priority'] = 80;
		$billing_email['class'] = [ 'form-row-wide' ];
		$fields['shipping']['billing_email'] = $billing_email;
	} else {
		$billing_email['label'] = __( 'ایمیل برای پیگیری سفارش', 'shopkit' );
		$billing_email['required'] = false;
		$fields['billing']['billing_email'] = $billing_email;
	}

	return $fields;
}, 1000 );

add_filter(
	'woocommerce_ship_to_different_address_checked',
	static function ( $checked ) {
		return WC()->cart && WC()->cart->needs_shipping_address() ? 1 : $checked;
	},
	100
);

// Reapply Theme labels at the final form-render step as checkout extensions
// sometimes rebuild field arguments after woocommerce_checkout_fields.
add_filter( 'woocommerce_form_field_args', static function ( array $args, string $key, $value ): array {
	$labels = [
		'shipping_first_name' => 'نام',
		'shipping_last_name'  => 'نام خانوادگی',
		'shipping_state'      => 'استان',
		'shipping_city'       => 'شهر',
		'shipping_address_1'  => 'خیابان',
		'shipping_postcode'   => 'کدپستی',
		'shipping_phone'      => 'تلفن',
		'billing_email'       => 'ایمیل برای پیگیری سفارش',
	];
	if ( isset( $labels[ $key ] ) && ( is_checkout() || ( function_exists( 'is_account_page' ) && is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) ) ) {
		$args['label'] = __( $labels[ $key ], 'shopkit' );
	}
	if ( is_checkout() && in_array( $key, [ 'billing_first_name', 'billing_last_name', 'shipping_first_name', 'shipping_last_name' ], true ) ) {
		// Some extensions supply English placeholders after checkout fields are built.
		$args['placeholder'] = '';
	}
	if ( is_checkout() && 'billing_email' === $key ) {
		$args['required'] = false;
	}
	if ( 'shipping_country' === $key && ( is_checkout() || ( function_exists( 'is_account_page' ) && is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) ) ) {
		$args['type'] = 'hidden';
		$args['label'] = '';
		$args['required'] = true;
		$args['default'] = 'IR';
	}
	return $args;
}, 1000, 3 );

// A saved customer address or a forged hidden value must not switch checkout
// away from Iran and break the PWS state, city, and shipping methods.
add_filter( 'woocommerce_checkout_get_value', static function ( $value, $key ) {
	if ( in_array( $key, [ 'billing_country', 'shipping_country' ], true ) ) {
		return 'IR';
	}
	if ( 'billing_email' === $key && is_user_logged_in() && ( '' === $value || null === $value ) ) {
		$user = wp_get_current_user();
		return $user->user_email;
	}
	if ( is_user_logged_in() && in_array( $key, [ 'shipping_first_name', 'shipping_last_name', 'shipping_state', 'shipping_city', 'shipping_address_1', 'shipping_postcode', 'shipping_phone' ], true ) ) {
		$stored_value = get_user_meta( get_current_user_id(), $key, true );
		if ( '' !== $stored_value && null !== $stored_value ) {
			if ( 'shipping_state' === $key ) {
				return lc_resolve_shipping_state_id( $stored_value );
			}
			return 'shipping_city' === $key
				? lc_resolve_shipping_city_id( $stored_value, get_user_meta( get_current_user_id(), 'shipping_state', true ) )
				: $stored_value;
		}
	}
	return $value;
}, 100, 2 );
/** Populate the contact number before extensions validate the raw request. */
function lc_prepare_checkout_contact_phone(): void {
	if ( ! WC()->cart || ! WC()->cart->needs_shipping_address() ) {
		return;
	}
	// The SMS extension reads billing_phone during checkout_process, before
	// WooCommerce builds posted_data. The shipping-only form submits no billing
	// input, so copying it only in the later filter leaves that validator empty.
	$phone = $_POST['shipping_phone'] ?? '';
	$_POST['billing_phone'] = is_scalar( $phone ) ? $phone : '';
}
add_action( 'woocommerce_checkout_process', 'lc_prepare_checkout_contact_phone', 1 );

add_filter( 'woocommerce_checkout_posted_data', static function ( $data ) {
	$data['billing_country'] = 'IR';
	$data['shipping_country'] = 'IR';
	if ( WC()->cart && WC()->cart->needs_shipping_address() ) {
		// The checkout keeps WooCommerce's native checked flag but the UI does
		// not offer a second address mode. Recover submitted shipping values if
		// a theme or browser omits that checkbox from the request.
		foreach ( [ 'shipping_first_name', 'shipping_last_name', 'shipping_state', 'shipping_city', 'shipping_address_1', 'shipping_postcode', 'shipping_phone', 'billing_email' ] as $key ) {
			if ( ( ! isset( $data[ $key ] ) || '' === $data[ $key ] ) && isset( $_POST[ $key ] ) ) {
				$data[ $key ] = wc_clean( wp_unslash( $_POST[ $key ] ) );
			}
		}
		$data['ship_to_different_address'] = true;
		foreach (
			[
				'billing_first_name' => 'shipping_first_name',
				'billing_last_name'  => 'shipping_last_name',
				'billing_state'      => 'shipping_state',
				'billing_city'       => 'shipping_city',
				'billing_address_1'  => 'shipping_address_1',
				'billing_postcode'   => 'shipping_postcode',
				'billing_phone'      => 'shipping_phone',
			] as $billing_key => $shipping_key
		) {
			$data[ $billing_key ] = $data[ $shipping_key ] ?? '';
		}
		$data['billing_company'] = '';
		$data['billing_address_2'] = '';
		$data['shipping_company'] = '';
		$data['shipping_address_2'] = '';
	}
	return $data;
}, 100 );

add_action( 'woocommerce_checkout_create_order', static function ( WC_Order $order, array $data ): void {
	if ( empty( $data['ship_to_different_address'] ) ) {
		return;
	}
	$order->set_billing_first_name( $order->get_shipping_first_name() );
	$order->set_billing_last_name( $order->get_shipping_last_name() );
	$order->set_billing_country( 'IR' );
	$order->set_billing_state( $order->get_shipping_state() );
	$order->set_billing_city( $order->get_shipping_city() );
	$order->set_billing_address_1( $order->get_shipping_address_1() );
	$order->set_billing_address_2( '' );
	$order->set_billing_postcode( $order->get_shipping_postcode() );
	$order->set_billing_phone( $order->get_shipping_phone() );
}, 100, 2 );

add_action( 'template_redirect', static function (): void {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}
	if ( is_wc_endpoint_url( 'payment-methods' ) || is_wc_endpoint_url( 'add-payment-method' ) ) {
		wc_add_notice( __( 'پرداخت سفارش‌ها از طریق زرین‌پال انجام می‌شود؛ نیازی به ذخیرهٔ شیوهٔ پرداخت نیست.', 'shopkit' ), 'notice' );
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}
}, 2 );

