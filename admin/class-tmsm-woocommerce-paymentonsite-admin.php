<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Woocommerce_Paymentonsite_Status
 * @subpackage Tmsm_Woocommerce_Paymentonsite_Status/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tmsm_Woocommerce_Paymentonsite_Status
 * @subpackage Tmsm_Woocommerce_Paymentonsite_Status/admin
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Woocommerce_Paymentonsite_Status_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-woocommerce-paymentonsite-status-admin.css', array('woocommerce_admin_styles'), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-woocommerce-paymentonsite-status-admin.js', array( 'jquery' ), $this->version, true );

	}


	/**
	 * @param array $emails
	 *
	 * @return array
	 */
	public function register_emails( $emails ) {
		require_once 'emails/class-tmsm-woocommerce-paymentonsite-status-customer-email.php';
		require_once 'emails/class-tmsm-woocommerce-paymentonsite-status-admin-email.php';

		$emails['Tmsm_Woocommerce_Paymentonsite_Status_Customer_Email'] = new Tmsm_Woocommerce_Paymentonsite_Status_Customer_Email();
		$emails['Tmsm_Woocommerce_Paymentonsite_Status_Admin_Email'] = new Tmsm_Woocommerce_Paymentonsite_Status_Admin_Email();

		return $emails;
	}

	/**
	 * Add order statuses: paymentonsite
	 *
	 * @param $statuses
	 *
	 * @return array
	 */
	function rename_order_statuses($statuses){

		$statuses['wc-paymentonsite'] = _x( 'Payment On Site', 'Order status', 'tmsm-woocommerce-paymentonsite-status' );

		return $statuses;
	}

	/**
	 * Load Gateway Class
	 */
	function load_gateway() {
		/**
		 * The class responsible for defining the payment gateway
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-woocommerce-paymentonsite-gateway.php';

	}

	/**
	 * Add Paymentonsite Gateway to the Gateways List
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	function add_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Paymentonsite';
		return $methods;
	}

	/**
	 * WooCommerce: Add Web Hook Order Paid
	 *
	 * @param array $topic_hooks Existing topic hooks.
	 *
	 * @return array
	 */
	function webhook_topic_hooks_order_paymentonsite( $topic_hooks ) {
		$new_hooks = array(
			'order.paymentonsite' => array(
				'woocommerce_process_paymentonsite',
			),
		);
		return array_merge( $topic_hooks, $new_hooks );
	}

	/**
	 * WooCommerce: Add Web Hook Topic Paid
	 *
	 * @param array $topic_events Existing topic hooks.
	 *
	 * @return array
	 */
	function valid_webhook_events_paymentonsite( $topic_events ) {
		$new_events = array(
			'paymentonsite',
		);
		return array_merge( $topic_events, $new_events );
	}

	/**
	 * WooCommerce: Add Web Hook Order Paid i18n
	 *
	 * @param array $topics Array of topics with the i18n proper name.
	 *
	 * @return array
	 */
	function webhook_topics_order_paymentonsite( $topics ) {
		$new_topics = array(
			'order.paymentonsite' => __( 'Order Payment On Site', 'tmsm-woocommerce-paymentonsite-status' ),
		);
		return array_merge( $topics, $new_topics );
	}

}
