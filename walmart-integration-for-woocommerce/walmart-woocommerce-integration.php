<?php
/**
 * Wordpress-plugin
 * Plugin Name:       Walmart Integration for WooCommerce
 * Plugin URI:        https://woocommerce.com/products/walmart-integration-for-woocommerce/
 * Description:       Walmart Integration for WooCommerce allows merchants to list their products on Walmart marketplace and manage the orders from the WooCommerce store.
 * Version:           2.1.5
 * Author:            CedCommerce
 * Author URI:        https://woocommerce.com/vendor/cedcommerce/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       walmart-woocommerce-integration
 * Domain Path:       /languages
 *
 * Woo: 5423057:b9ae22238e0bc7cbc5c32fbff78cd950
 * WC requires at least: 4.0
 * WC tested up to: 7.5.1
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  2.1.4
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
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
define( 'WALMART_WOOCOMMERCE_INTEGRATION_VERSION', '2.1.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-walmart-woocommerce-integration-activator.php
 */
function activate_walmart_woocommerce_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-walmart-woocommerce-integration-activator.php';
	Walmart_Woocommerce_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-walmart-woocommerce-integration-deactivator.php
 */
function deactivate_walmart_woocommerce_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-walmart-woocommerce-integration-deactivator.php';
	Walmart_Woocommerce_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_walmart_woocommerce_integration' );
register_deactivation_hook( __FILE__, 'deactivate_walmart_woocommerce_integration' );

define( 'CED_WALMART_LOG_DIRECTORY', wp_upload_dir()['basedir'] . '/ced_walmart_log_directory' );
define( 'CED_WALMART_VERSION', '1.0.0' );
define( 'CED_WALMART_PREFIX', 'ced_walmart' );
define( 'CED_WALMART_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'CED_WALMART_URL', plugin_dir_url( __FILE__ ) );
define( 'CED_WALMART_ABSPATH', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );
define( 'CED_WALMART_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-walmart-woocommerce-integration.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ced-walmart-core-functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_walmart_woocommerce_integration() {

	$plugin = new Walmart_Woocommerce_Integration();
	$plugin->run();

}

/**
 * Ced_admin_notice_example_activation_hook_ced_walmart.
 *
 * @since 1.0.0
 */
function ced_admin_notice_example_activation_hook_ced_walmart() {
	set_transient( 'ced-walmart-admin-notice', true, 5 );
}

/**
 * Ced_walmart_admin_notice_activation.
 *
 * @since 1.0.0
 */
function ced_walmart_admin_notice_activation() {
	if ( get_transient( 'ced-walmart-admin-notice' ) ) {?>
		<div class="updated notice is-dismissible">
			<p>Welcome to Walmart Integration For WooCommerce. Start listing, syncing, managing, & automating your WooCommerce and Walmart store to boost sales.</p>

		  <p> To get started , proceed with  <a href="admin.php?page=ced_walmart" class ="ced_configuration_plugin_main">connecting</a> your Walmart marketplace account. </p>

	   </div>
		<?php
		delete_transient( 'ced-walmart-admin-notice' );
	}
}

/**
 * Check WooCommerce is Installed and Active.
 *
 * @since 1.0.0
 */
if ( ced_walmart_check_woocommerce_active() ) {
	run_walmart_woocommerce_integration();
	register_activation_hook( __FILE__, 'ced_admin_notice_example_activation_hook_ced_walmart' );
	add_action( 'admin_notices', 'ced_walmart_admin_notice_activation' );
} else {
	add_action( 'admin_init', 'deactivate_ced_walmart_woo_missing' );
}

