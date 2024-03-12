<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cedcommerce.com
 * @since             1.0.0
 * @package           CedCommerce_Integration_for_Good_Market
 *
 * @wordpress-plugin
 * Plugin Name:       CedCommerce Integration for Good Market
 * Plugin URI:        https://cedcommerce.com
 * Description:       CedCommerce Integration for Good Market allows merchants to list their products on Good Market marketplace and manage the orders from the WooCommerce store.
 * Version:           1.0.5
 * Author:            CedCommerce
 * Author URI:        https://cedcommerce.com/woocommerce-extensions
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ced-good-market-integration
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
define( 'GOOD_MARKET_INTEGRATION_VERSION', '1.0.5' );
define( 'GOOD_MARKET_INTEGRATION_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'GOOD_MARKET_INTEGRATION_URL', plugin_dir_url( __FILE__ ) );
define( 'GOOD_MARKET_INTEGRATION_BASENAME', plugin_basename( __FILE__ ) );

require GOOD_MARKET_INTEGRATION_DIRPATH . '/includes/ced-good-market-core.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-good_market_integration-activator.php
 */
function activate_Good_Market_Woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-good_market_integration-activator.php';
	Good_Market_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-good_market_integration-deactivator.php
 */
function deactivate_Good_Market_Woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-good_market_integration-deactivator.php';
	Good_Market_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Good_Market_Woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_Good_Market_Woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-good_market_integration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Good_Market_Woocommerce() {

	$plugin = new Good_Market_Woocommerce();
	$plugin->run();

}
/**
 * This code runs when WooCommerce is not activated,
 *
 * @since 1.0.0
 */
function deactivate_ced_good_market_woo_missing() {
	deactivate_plugins( GOOD_MARKET_INTEGRATION_BASENAME );
	add_action( 'admin_notices', 'ced_good_market_woo_missing_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}
/**
 * Callback function for sending notice if woocommerce is not activated.
 *
 * @since 1.0.0
 */
function ced_good_market_woo_missing_notice() {
	// translators: %s: search term !!
	echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( esc_html( __( 'Good Market Integration For Woocommerce requires WooCommerce to be installed and active. You can download %s from here.', 'GoodMarket-woocommerce-integration' ) ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}

/**
 * Ced_admin_notice_example_activation_hook_ced_GoodMarket.
 *
 * @since 1.0.0
 */
function ced_admin_notice_example_activation_hook_ced_GoodMarket() {
	set_transient( 'ced-good-makrket-admin-notice', true, 5 );
}

/**
 * Ced_good_market_admin_notice_activation.
 *
 * @since 1.0.0
 */
function ced_good_market_admin_notice_activation() {
	if ( get_transient( 'ced-good-makrket-admin-notice' ) ) {?>
		<div class="updated notice is-dismissible">
			<p>Welcome to Good Market Integration For WooCommerce. Start listing, syncing, managing, & automating your WooCommerce and Good Market store to boost sales.</p>
		</div>
		<?php
		delete_transient( 'ced-good-makrket-admin-notice' );
	}
}

/**
 * Check WooCommerce is Installed and Active.
 *
 * @since 1.0.0
 */
if ( ced_good_market_check_woocommerce_active() ) {
	run_Good_Market_Woocommerce();
	register_activation_hook( __FILE__, 'ced_admin_notice_example_activation_hook_ced_GoodMarket' );
	add_action( 'admin_notices', 'ced_good_market_admin_notice_activation' );
} else {
	add_action( 'admin_init', 'deactivate_ced_good_market_woo_missing' );
}

function ced_good_market_check_woocommerce_active() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return true;
	}
	return false;
}