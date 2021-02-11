<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://deshonlineit.com
 * @since             1.0.0
 * @package           Wc_Payemac_Payment
 *
 * @wordpress-plugin
 * Plugin Name:       wc-payemac-payment
 * Plugin URI:        https://deshonlineit.com/wc-payemac-payment
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Md Nazmul
 * Author URI:        https://deshonlineit.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-payemac-payment
 * Domain Path:       /languages
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
define( 'WC_PAYEMAC_PAYMENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-payemac-payment-activator.php
 */
function activate_wc_payemac_payment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-payemac-payment-activator.php';
	Wc_Payemac_Payment_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-payemac-payment-deactivator.php
 */
function deactivate_wc_payemac_payment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-payemac-payment-deactivator.php';
	Wc_Payemac_Payment_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wc_payemac_payment' );
register_deactivation_hook( __FILE__, 'deactivate_wc_payemac_payment' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-payemac-payment.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_payemac_payment() {

	$plugin = new Wc_Payemac_Payment();
	$plugin->run();

}
run_wc_payemac_payment();

require_once plugin_dir_path( __FILE__ ) . 'gateways/class-wc-payemac-payment-gateway.php';
