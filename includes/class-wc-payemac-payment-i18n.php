<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://deshonlineit.com
 * @since      1.0.0
 *
 * @package    Wc_Payemac_Payment
 * @subpackage Wc_Payemac_Payment/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Payemac_Payment
 * @subpackage Wc_Payemac_Payment/includes
 * @author     Md Nazmul <php673500@gmail.com>
 */
class Wc_Payemac_Payment_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-payemac-payment',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
