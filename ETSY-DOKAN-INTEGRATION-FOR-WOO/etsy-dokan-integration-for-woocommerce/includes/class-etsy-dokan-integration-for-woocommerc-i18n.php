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
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/includes
 * @author     Cedcommerce <support@cedcommerce.com>
 */
class Etsy_Dokan_Integration_For_Woocommerc_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'etsy-dokan-integration-for-woocommerc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
