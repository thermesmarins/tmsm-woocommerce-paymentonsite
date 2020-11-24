<?php
/**
 * Class WC_Gateway_Paymentonsite file.
 *
 * @package WooCommerce\Gateways
 */


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
		//$this->form_fields['enable_for_methods']['options'] = $options;
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
}