<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */
class Good_Market_Woocommerce_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'Good_Market_Woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
