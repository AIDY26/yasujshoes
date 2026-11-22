<?php
/** Keep Tipax's existing WooCommerce method editable, with optional carrier collection. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_shipping_init', static function (): void {
	if ( ! class_exists( 'WC_Tipax_Method' ) || class_exists( 'LC_Tipax_Method' ) ) {
		return;
	}
	class LC_Tipax_Method extends WC_Tipax_Method {
		public function __construct( $instance_id = 0 ) {
			$this->supports[] = 'postpaid';
			parent::__construct( $instance_id );
		}

		public function calculate_shipping( $package = [] ) {
			if ( 'postpaid' === $this->payment_type ) {
				// Shipping is collected by the carrier; WooCommerce charges only the order.
				$this->add_rate_cost( 0, $package, [ 'payment_type' => 'postpaid' ] );
				return;
			}
			parent::calculate_shipping( $package );
		}
	}
}, 20 );

add_filter( 'woocommerce_shipping_methods', static function ( array $methods ): array {
	if ( isset( $methods['WC_Tipax_Method'] ) && class_exists( 'LC_Tipax_Method' ) ) {
		$methods['WC_Tipax_Method'] = LC_Tipax_Method::class;
	}
	return $methods;
}, 20 );
