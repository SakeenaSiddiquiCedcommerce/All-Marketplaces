<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://woocommerce.com/vendor/cedcommerce/
 * @since      1.0.0
 *
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/includes
 */
class Facebook_Marketplace_Connector_For_Woocommerce_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'facebook-marketplace-connector-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
