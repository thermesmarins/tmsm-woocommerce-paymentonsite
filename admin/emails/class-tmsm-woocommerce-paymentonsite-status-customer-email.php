<?php
/**
 * Class Tmsm_Woocommerce_Paymentonsite_Status_Customer_Email file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tmsm_Woocommerce_Paymentonsite_Status_Customer_Email', false ) ) :

	/**
	 * Customer On-hold Order Email.
	 *
	 * An email sent to the customer when a new order is on-hold for.
	 *
	 * @class       Tmsm_Woocommerce_Paymentonsite_Status_Customer_Email
	 * @version     1.0.0
	 * @package     WooCommerce/Classes/Emails
	 * @extends     WC_Email
	 */
	class Tmsm_Woocommerce_Paymentonsite_Status_Customer_Email extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_paymentonsite';
			$this->customer_email = true;
			$this->enabled        = false;
			$this->title          = __( 'Order with payment on site', 'tmsm-woocommerce-paymentonsite-status' );
			$this->description
			                      = __( 'This is an order notification sent to customers containing order details after an order has payment on site.',
				'tmsm-woocommerce-paymentonsite-status' );
			$this->template_html  = 'emails/customer-paymentonsite.php';
			$this->template_plain = 'emails/plain/customer-paymentonsite.php';
			$this->template_base  = TMSM_WOOCOMMERCE_PAYMENTONSITE_STATUS . 'templates/';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Triggers for this email.
			add_action( 'woocommerce_order_status_pending_to_paymentonsite_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_failed_to_paymentonsite_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_cancelled_to_paymentonsite_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Your {site_title} order has been received!', 'tmsm-woocommerce-paymentonsite-status' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Thank you for your order', 'tmsm-woocommerce-paymentonsite-status' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order    Order object.
		 */
		public function trigger( $order_id, $order = false ) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'shop_address'       => self::get_formatted_base_address(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'shop_address'       => self::get_formatted_base_address(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				)
			);
		}

		/**
		 * Get formatted store address.
		 *
		 * @return string
		 */
		public function get_formatted_base_address() {
			$address = array(
				'company' => get_bloginfo( 'name' ),
				'address_1' => WC()->countries->get_base_address(),
				'address_2' => WC()->countries->get_base_address_2(),
				'city'      => WC()->countries->get_base_city(),
				'state'     => WC()->countries->get_base_state(),
				'postcode'  => WC()->countries->get_base_postcode(),
				'country'   => WC()->countries->get_base_country(),
			);

			return WC()->countries->get_formatted_address( $address );
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_additional_content() {
			return __( 'We look forward to fulfilling your order soon.', 'tmsm-woocommerce-paymentonsite-status' );
		}
	}

endif;