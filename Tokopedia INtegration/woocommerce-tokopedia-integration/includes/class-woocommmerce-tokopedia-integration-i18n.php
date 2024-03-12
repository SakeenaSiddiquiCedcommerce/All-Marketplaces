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
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/includes
 */
class Woocommmerce_Tokopedia_Integration_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( ' woocommmerce-tokopedia-integration ', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );

	}



}
