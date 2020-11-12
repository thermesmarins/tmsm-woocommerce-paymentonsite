<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Woocommerce_Paymentonsite_Status
 * @subpackage Tmsm_Woocommerce_Paymentonsite_Status/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tmsm_Woocommerce_Paymentonsite_Status
 * @subpackage Tmsm_Woocommerce_Paymentonsite_Status/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Woocommerce_Paymentonsite_Status {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tmsm_Woocommerce_Paymentonsite_Status_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TMSM_WOOCOMMERCE_PAYMENTONSITE_STATUS_VERSION' ) ) {
			$this->version = TMSM_WOOCOMMERCE_PAYMENTONSITE_STATUS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tmsm-woocommerce-paymentonsite-status';

		$this->load_dependencies();
		$this->set_locale();
		$this->register_paymentonsitestatus();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tmsm_Woocommerce_Paymentonsite_Status_Loader. Orchestrates the hooks of the plugin.
	 * - Tmsm_Woocommerce_Paymentonsite_Status_i18n. Defines internationalization functionality.
	 * - Tmsm_Woocommerce_Paymentonsite_Status_Admin. Defines all hooks for the admin area.
	 * - Tmsm_Woocommerce_Paymentonsite_Status_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-woocommerce-paymentonsite-status-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-woocommerce-paymentonsite-status-i18n.php';

		/**
		 * The class responsible for defining processed status
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-woocommerce-paymentonsite-status-poststatus.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tmsm-woocommerce-paymentonsite-status-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tmsm-woocommerce-paymentonsite-status-public.php';

		$this->loader = new Tmsm_Woocommerce_Paymentonsite_Status_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tmsm_Woocommerce_Paymentonsite_Status_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tmsm_Woocommerce_Paymentonsite_Status_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tmsm_Woocommerce_Paymentonsite_Status_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_filter( 'woocommerce_admin_order_actions', $plugin_admin, 'admin_order_actions_processed', 20, 2 );

		$this->loader->add_filter( 'wc_order_statuses', $plugin_admin, 'rename_order_statuses', 999, 1 );

		//$this->loader->add_filter( 'bulk_actions-edit-shop_order', $plugin_admin, 'rename_bulk_actions', 50, 1 );
		//$this->loader->add_filter( 'views_edit-shop_order', $plugin_admin, 'rename_views_filters', 50, 1 );
		//$this->loader->add_filter( 'woocommerce_admin_order_preview_actions', $plugin_admin, 'admin_order_preview_actions', 50, 2 );

		$this->loader->add_filter( 'admin_action_mark_processed', $plugin_admin, 'admin_action_mark_processed', 10 );

		//$this->loader->add_action( 'woocommerce_order_status_processing_to_processed', $plugin_admin, 'status_processing_to_processed', 10, 2 );
		//$this->loader->add_action( 'woocommerce_order_status_completed_to_processed', $plugin_admin, 'status_completed_to_processed', 10, 2 );
		//$this->loader->add_action( 'woocommerce_order_is_paid_statuses', $plugin_admin, 'woocommerce_order_is_paid_statuses', 10, 1 );
		//$this->loader->add_action( 'woocommerce_reports_order_statuses', $plugin_admin, 'woocommerce_reports_order_statuses', 10, 1 );

		//$this->loader->add_filter( 'woocommerce_order_is_download_permitted', $plugin_admin, 'woocommerce_order_is_download_permitted', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tmsm_Woocommerce_Paymentonsite_Status_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Define the processed status for WooCommerce
	 *
	 * Uses the Tmsm_Woocommerce_Paymentonsite_Status_Poststatus class
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_paymentonsitestatus() {

		$plugin_posttypes = new Tmsm_Woocommerce_Paymentonsite_Status_Poststatus();

		$this->loader->add_filter( 'init', $plugin_posttypes, 'register_post_status_paymentonsite' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tmsm_Woocommerce_Paymentonsite_Status_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
