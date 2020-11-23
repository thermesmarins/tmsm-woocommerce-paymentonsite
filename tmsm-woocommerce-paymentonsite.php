<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/nicomollet
 * @since             1.0.0
 * @package           Tmsm_Woocommerce_Paymentonsite_Status
 *
 * @wordpress-plugin
 * Plugin Name:       TMSM WooCommerce Payment On Site Status
 * Plugin URI:        https://github.com/thermesmarins/tmsm-woocommerce-paymentonsite-status
 * Description:       Adds a "Payment On Site" status to WooCommerce order statuses
 * Version:           1.0.0
 * Author:            Nicolas Mollet
 * Author URI:        https://github.com/nicomollet
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       tmsm-woocommerce-paymentonsite-status
 * Domain Path:       /languages
 * Github Plugin URI: https://github.com/thermesmarins/tmsm-woocommerce-paymentonsite-status
 * Github Branch:     master
 * Requires PHP:      7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TMSM_WOOCOMMERCE_PAYMENTONSITE_STATUS_VERSION', '1.0.0' );

defined( 'TMSM_WOOCOMMERCE_PAYMENTONSITE_BASE_PATH' ) || define( 'TMSM_WOOCOMMERCE_PAYMENTONSITE_BASE_PATH', plugin_dir_path( __FILE__ ) );
defined( 'TMSM_WOOCOMMERCE_PAYMENTONSITE_BASE_URL' ) || define( 'TMSM_WOOCOMMERCE_PAYMENTONSITE_BASE_URL', plugin_dir_url( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tmsm-woocommerce-paymentonsite-status-activator.php
 */
function activate_tmsm_woocommerce_paymentonsite_status() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-woocommerce-paymentonsite-status-activator.php';
	Tmsm_Woocommerce_Paymentonsite_Status_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tmsm-woocommerce-paymentonsite-status-deactivator.php
 */
function deactivate_tmsm_woocommerce_paymentonsite_status() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-woocommerce-paymentonsite-status-deactivator.php';
	Tmsm_Woocommerce_Paymentonsite_Status_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tmsm_woocommerce_paymentonsite_status' );
register_deactivation_hook( __FILE__, 'deactivate_tmsm_woocommerce_paymentonsite_status' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-woocommerce-paymentonsite.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tmsm_woocommerce_paymentonsite_status() {

	$plugin = new Tmsm_Woocommerce_Paymentonsite_Status();
	$plugin->run();

}
run_tmsm_woocommerce_paymentonsite_status();
