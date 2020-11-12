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
	 * Rename order statuses in views filters
	 *
	 * @param $views array
	 *
	 * @return array
	 */
	function rename_views_filters($views){
		foreach($views as &$view){
			//$view = str_replace(_x( 'Processing', 'Order status', 'woocommerce' ), _x( 'Paid', 'Order status', 'tmsm-woocommerce-paymentonsite-status' ), $view);
			$view = str_replace(_x( 'Completed', 'Order status', 'woocommerce' ), _x( 'Paymentonsite', 'Order status', 'tmsm-woocommerce-paymentonsite-status' ), $view);
			$view = str_replace('Processed', _x( 'Processed', 'Order status', 'tmsm-woocommerce-paymentonsite-status' ), $view);
		}
		return $views;
	}

	/**
	 * Rename order preview actions
	 *
	 * @param array $actions
	 * @param  WC_Order $order Order object.
	 *
	 * @return mixed
	 */
	function admin_order_preview_actions($actions, $order){

		$status_actions = array();

		$status_actions = @$actions['status']['actions'];

		$status_actions['complete']['name'] =  _x( 'Paymentonsite', 'Order status', 'tmsm-woocommerce-paymentonsite-status' );

		if ( $order->has_status( array( 'processing', 'completed' , 'complete' ) ) ) {
			$status_actions['processed'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=processed&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
				'name'   => _x( 'Processed', 'Order status', 'tmsm-woocommerce-paymentonsite-status' ),
				'action' => 'processed',
			);
		}

		if ( $status_actions ) {
			$actions['status'] = array(
				'group'   => __( 'Change status: ', 'woocommerce' ),
				'actions' => $status_actions,
			);
		}

		return $actions;
	}

	/**
	 * Rename bulk actions
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	function rename_bulk_actions(array $actions){
		//$actions['mark_processing'] = __( 'Mark paid', 'tmsm-woocommerce-paymentonsite-status' );

		$actions['mark_completed']  = __( 'Mark paymentonsite', 'tmsm-woocommerce-paymentonsite-status' );
		$actions['mark_processed'] = __('Mark as processed', 'tmsm-woocommerce-paymentonsite-status');

		return $actions;
	}

	/**
	 * Rename order statuses: completed > paymentonsite
	 *
	 * @param $statuses
	 *
	 * @return array
	 */
	function rename_order_statuses($statuses){

		$statuses['wc-completed'] = _x( 'Paymentonsite', 'Order status', 'tmsm-woocommerce-paymentonsite-status' );
		$statuses['wc-processed'] = _x( 'Processed', 'Order status', 'tmsm-woocommerce-paymentonsite-status' );

		return $statuses;
	}

	/**
	 * Order actions for processed
	 *
	 * @param array $actions
	 * @param WC_Order $order
	 *
	 * @return mixed
	 */
	function admin_order_actions_processed($actions, $order){

		$actions['complete']['name'] = _x( 'Ship', 'Change order status', 'tmsm-woocommerce-paymentonsite-status' );

		if ( $order->has_status( array( 'processing', 'completed' ) ) ) {

			// Get Order ID (compatibility all WC versions)
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			// Set the action button
			$actions['processed'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=processed&order_id='
				                                     . $order_id ),
					'woocommerce-mark-order-status' ),
				'name'   => __( 'Mark as processed', 'tmsm-woocommerce-paymentonsite-status' ),
				'action' => "view processed", // keep "view" class for a clean button CSS
			);
		}

		return $actions;
	}

	/**
	 * Bulk action handler for processed
	 */
	function admin_action_mark_processed() {

		// if an array with order IDs is not presented, exit the function
		if( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
			return;

		foreach( $_REQUEST['post'] as $order_id ) {
			$order = new WC_Order( $order_id );
			$order_note = __('Status changed to Processed', 'tmsm-woocommerce-paymentonsite-status');
			$order->update_status( 'processed', $order_note, true );
		}

		// of course using add_query_arg() is not required, you can build your URL inline
		$location = add_query_arg( array(
			'post_type' => 'shop_order',
			'marked_processed' => 1, // marked_processed=1 is just the $_GET variable for notices
			'changed' => count( $_REQUEST['post'] ), // number of changed orders
			'ids' => join( $_REQUEST['post'], ',' ),
			'post_status' => 'all'
		), 'edit.php' );

		wp_redirect( admin_url( $location ) );



		exit;

	}

	/**
	 * Action when order goes from processing to processed
	 *
	 * @param $order_id int
	 * @param $order WC_Order
	 */
	function status_processing_to_processed($order_id, $order){
		$order->update_status( 'completed');
		$order->update_status( 'processed');
	}

	/**
	 * Action when order goes from completed to processed
	 *
	 * @param $order_id int
	 * @param $order WC_Order
	 */
	function status_completed_to_processed($order_id, $order){

	}

	/**
	 * Get list of statuses which are consider 'paid'.
	 *
	 * @param $statuses array
	 * @return array
	 */
	function woocommerce_order_is_paid_statuses($statuses){
		$statuses[] = 'processed';
		return $statuses;
	}

	/**
	 * WooCommerce reports with custom statuts processed as paid status
	 *
	 * @param $statuses array
	 *
	 * @return array
	 */
	function woocommerce_reports_order_statuses($statuses){
		if(isset($statuses) && is_array($statuses)){
			if(in_array('completed', $statuses) || in_array('processing', $statuses)){
				array_push( $statuses, 'processed');
			}
		}
		return $statuses;
	}

	/**
	 * @param $is_download_permitted bool
	 * @param $order WC_Order
	 *
	 * @return bool
	 */
	function woocommerce_order_is_download_permitted( $is_download_permitted, $order ) {
		if ( $order->has_status ( 'processed' ) ) {
			return true;
		}
		return $is_download_permitted;
	}

}
