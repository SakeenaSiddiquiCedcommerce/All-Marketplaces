<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Etsy_Integration_For_Woocommerce_Wcfm
 * @subpackage Etsy_Integration_For_Woocommerce_Wcfm/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Etsy_Integration_For_Woocommerce_Wcfm
 * @subpackage Etsy_Integration_For_Woocommerce_Wcfm/includes
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class Etsy_Integration_For_Woocommerce_Wcfm_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'etsy-integration-for-woocommerce-wcfm',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
