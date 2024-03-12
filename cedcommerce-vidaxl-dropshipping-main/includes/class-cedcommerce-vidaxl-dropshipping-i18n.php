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
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/includes
 * @author     cedcommerce <support@cedcommerce.com>
 */
class Cedcommerce_Vidaxl_Dropshipping_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cedcommerce-vidaxl-dropshipping',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
