<?php
/**
 * Class WC_Gateway_Paymentonsite file.
 *
 * @package WooCommerce\Gateways
 */

use Automattic\Jetpack\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Payment on Site Gateway.
 *
 * Provides a Payment on Site Gateway.
 *
 * @class       WC_Gateway_Paymentonsite
 * @extends     WC_Gateway_COD
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
class WC_Gateway_Paymentonsite extends WC_Gateway_COD {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->override_form_fields();
		$this->title              = $this->get_method_title();
		$this->description        = $this->get_description();
		//$this->instructions       = $this->get_in;

		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 30, 3 );

		// Customer Emails.
		//add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), -30, 3 );
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->id                 = 'paymentonsite';
		$this->enabled            = false;
		$this->icon               = apply_filters( 'woocommerce_paymentonsite_icon', WC_HTTPS::force_https_url( TMSM_WOOCOMMERCE_PAYMENTONSITE_BASE_URL.'/public/img/paymentonsite-icon.png'  ) );
		$this->method_title       = _x('Payment On Site', 'Payment gateway', 'tmsm-woocommerce-paymentonsite-status');
		$this->method_description = __( 'Have your customers pay on site.', 'tmsm-woocommerce-paymentonsite-status' );
		$this->has_fields         = false;

	}

	/**
	 * Override Gateway Settings Form Fields.
	 */
	public function override_form_fields() {
		$this->form_fields['enabled']['label'] = __( 'Enable payment on site', 'tmsm-woocommerce-paymentonsite-status' );
		$this->form_fields['title']['default'] = __( 'Payment on site', 'tmsm-woocommerce-paymentonsite-status' );
		$this->form_fields['description']['default'] = __( 'Payment on site', 'tmsm-woocommerce-paymentonsite-status' );
		$this->form_fields['instructions']['description'] = __( 'Instructions that will be added to the thank you page and confirmation email. Use the placeholder {shop_address} for the shop address.', 'tmsm-woocommerce-paymentonsite-status' );
		$this->form_fields['instructions']['default'] = __( 'Payment on site at {shop_address}', 'tmsm-woocommerce-paymentonsite-status' );
		$this->form_fields['enable_for_methods']['options'] = $this->load_shipping_method_options();
		$this->form_fields['enable_for_virtual']['label'] = __( 'Accept Payment On Site if the order is virtual', 'tmsm-woocommerce-paymentonsite-status' );
	}

	/**
	 * Change payment complete order status to completed for paymentonsite orders.
	 *
	 * @since  3.1.0
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {

		if ( $order && 'paymentonsite' === $order->get_payment_method() ) {
			$status = 'paymentonsite';
			do_action( 'woocommerce_process_paymentonsite', $order_id );
		}
		return $status;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0  ) {
			// Mark as processing or on-hold (payment won't be taken until delivery).
			$status_to = (self::order_has_appointmentonly( $order ) ? 'appointment' : 'paymentonsite');

			$order->update_status( apply_filters( 'woocommerce_paymentonsite_process_payment_order_status', $order->has_downloadable_item() ? $status_to : $status_to, $order ), __( 'Payment to be made on site', 'tmsm-woocommerce-paymentonsite-status' ) );
		} else {
			$order->payment_complete();
		}


		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}


	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {

		if(self::cart_has_appointmentonly()){
			return true;
		}

		return parent::is_available();
	}

	/**
	 * If Cart has appointments only
	 *
	 * @return bool
	 */
	private static function cart_has_appointmentonly(){

		//return true; // for tests

		if( ! WC() && ! WC()->cart){
			return false;
		}

		$cart_items = WC()->cart->get_cart_contents();

		$appointmentonly = true;
		if ( count( $cart_items ) === 0 ) {
			$appointmentonly = false;
		}
		foreach ( $cart_items as $key => $values ) {
			if(empty($values['appointment'])){
				$appointmentonly = false;
			}
		}

		return $appointmentonly;
	}

	/**
	 * If Order has appointments only
	 *
	 * @param WC_Order|int $order
	 *
	 * @return bool
	 */
	private function order_has_appointmentonly($order){

		//return true; // for tests

		if( ! $order ) {
			return false;
		}

		$order_id = WC_Order_Factory::get_order_id( $order );

		$order = wc_get_order($order_id);

		$appointmentonly = true;

		if ( ! empty( $order ) ) {

			foreach ( $order->get_items() as $order_item_id => $order_item_data) {

				// Has appointment
				if(empty($order_item_data['_appointment'])){
					$appointmentonly = false;
				}

			}
		}
		return $appointmentonly;
	}

	/**
	 * Checks to see whether or not the admin settings are being accessed by the current request.
	 *
	 * @return bool
	 */
	private function is_accessing_settings() {

		if ( is_admin() ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['section'] ) || 'paymentonsite' !== $_REQUEST['section'] ) {
				return false;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return true;
		}

		if ( Constants::is_true( 'REST_REQUEST' ) ) {
			global $wp;
			if ( isset( $wp->query_vars['rest_route'] ) && false !== strpos( $wp->query_vars['rest_route'], '/payment_gateways' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Loads all of the shipping method options for the enable_for_methods field.
	 *
	 * @return array
	 */
	private function load_shipping_method_options() {

		// Since this is expensive, we only want to do it if we're actually on the settings page.
		if ( ! $this->is_accessing_settings() ) {
			return array();
		}

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();

		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		$zones[] = new WC_Shipping_Zone( 0 );

		$options = array();
		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

			$options[ $method->get_method_title() ] = array();

			// Translators: %1$s shipping method name.
			$options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'woocommerce' ), $method->get_method_title() );

			foreach ( $zones as $zone ) {

				$shipping_method_instances = $zone->get_shipping_methods();

				foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

					if ( $shipping_method_instance->id !== $method->id ) {
						continue;
					}

					$option_id = $shipping_method_instance->get_rate_id();

					// Translators: %1$s shipping method title, %2$s shipping method id.
					$option_instance_title = sprintf( __( '%1$s (#%2$s)', 'woocommerce' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

					// Translators: %1$s zone name, %2$s shipping method instance name.
					$option_title = sprintf( __( '%1$s &ndash; %2$s', 'woocommerce' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'woocommerce' ), $option_instance_title );

					$options[ $method->get_method_title() ][ $option_id ] = $option_title;
				}
			}
		}

		return $options;
	}

}