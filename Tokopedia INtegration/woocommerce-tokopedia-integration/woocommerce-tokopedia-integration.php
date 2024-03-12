<?php
/**
 * Wordpress-plugin
 * Plugin Name:       Tokopedia Integration for WooCommerce
 * Plugin URI:        https://cedcommerce.com
 * Description:       Tokopedia Integration for WooCommerce allows merchants to list their products on Tokopedia marketplace and manage the orders from the woocommerce store.
 * Version:           1.0.0
 * Author:            CedCommerce
 * Author URI:        https://cedcommerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:      woocommmerce-tokopedia-integration
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.0
 *
 * @package  Woocommmerce_Tokopedia_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
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
define( 'WOOCOMMMERCE_TOKOPEDIA_INTEGRATION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommmerce-tokopedia-integration-activator.php
 */
function activate_woocommmerce_tokopedia_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommmerce-tokopedia-integration-activator.php';
	Woocommmerce_Tokopedia_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommmerce-tokopedia-integration-deactivator.php
 */
function deactivate_woocommmerce_tokopedia_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommmerce-tokopedia-integration-deactivator.php';
	Woocommmerce_Tokopedia_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommmerce_tokopedia_integration' );
register_deactivation_hook( __FILE__, 'deactivate_woocommmerce_tokopedia_integration' );

/* DEFINE CONSTANTS */
define( 'CED_TOKOPEDIA_LOG_DIRECTORY', wp_upload_dir()['basedir'] . '/ced_tokopedia_log_directory' );
define( 'CED_TOKOPEDIA_VERSION', '1.0.0' );
define( 'CED_TOKOPEDIA_PREFIX', 'ced_tokopedia' );
define( 'CED_TOKOPEDIA_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'CED_TOKOPEDIA_URL', plugin_dir_url( __FILE__ ) );
define( 'CED_TOKOPEDIA_ABSPATH', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );


/**
* This file includes core functions to be used globally in plugin.
 *
* @link  http://www.cedcommerce.com/
*/
require_once plugin_dir_path( __FILE__ ) . 'includes/ced-tokopedia-core-functions.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
 require plugin_dir_path( __FILE__ ) . 'includes/class-woocommmerce-tokopedia-integration.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_woocommmerce_tokopedia_integration() {

	$plugin = new Woocommmerce_Tokopedia_Integration();
	$plugin->run();

}
	run_woocommmerce_tokopedia_integration();


/* Register activation hook. */
register_activation_hook( __FILE__, 'ced_admin_notice_example_activation_hook_ced_tokopedia' );

/**
 * Runs only when the plugin is activated.
 *
 * @since 1.0.0
 */
function ced_admin_notice_example_activation_hook_ced_tokopedia() {

	/* Create transient data */
	set_transient( 'ced-tokopedia-admin-notice', true, 5 );
}

/*Admin admin notice */

add_action( 'admin_notices', 'ced_tokopedia_admin_notice_activation' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */


function ced_tokopedia_admin_notice_activation() {

	if ( get_transient( 'ced-tokopedia-admin-notice' ) ) {?>
		<div class="updated notice is-dismissible">
			<p>Welcome to WooCommerce Tokopedia Integration. Start listing, syncing, managing, & automating your WooCommerce and Tokopedia stores to boost sales.</p>
			<a href="admin.php?page=ced_tokopedia" class ="ced_configuration_plugin_main">Connect to Tokopedia</a>
		</div>
		<?php

		delete_transient( 'ced-tokopedia-admin-notice' );
	}
}

