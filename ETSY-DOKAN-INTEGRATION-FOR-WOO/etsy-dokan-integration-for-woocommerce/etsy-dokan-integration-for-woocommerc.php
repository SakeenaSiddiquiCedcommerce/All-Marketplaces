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
 * @since             2.0.0
 * @package           Etsy_Dokan_Integration_For_Woocommerc
 *
 * @wordpress-plugin
 * Plugin Name:       Etsy Dokan Integration For WooCommerce
 * Plugin URI:        https://cedcommerce.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           2.0.0
 * Author:            Cedcommerce
 * Author URI:        https://cedcommerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       etsy-dokan-integration-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 2.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ETSY_DOKAN_INTEGRATION_FOR_WOOCOMMERC_VERSION', '2.0.0' );

/* DEFINE CONSTANTS */
define( 'CED_ETSY_DOKAN_LOG_DIRECTORY', wp_upload_dir()['basedir'] . '/ced_etsy_log_directory' );
define( 'CED_ETSY_DOKAN_VERSION', '2.0.0' );
define( 'CED_ETSY_DOKAN_PREFIX', 'ced_etsy' );
define( 'CED_ETSY_DOKAN_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'CED_ETSY_DOKAN_URL', plugin_dir_url( __FILE__ ) );
define( 'CED_ETSY_DOKAN_ABSPATH', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-etsy-dokan-integration-for-woocommerc-activator.php
 */
function activate_etsy_dokan_integration_for_woocommerc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-etsy-dokan-integration-for-woocommerc-activator.php';
	Etsy_Dokan_Integration_For_Woocommerc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-etsy-dokan-integration-for-woocommerc-deactivator.php
 */
function deactivate_etsy_dokan_integration_for_woocommerc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-etsy-dokan-integration-for-woocommerc-deactivator.php';
	Etsy_Dokan_Integration_For_Woocommerc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_etsy_dokan_integration_for_woocommerc' );
register_deactivation_hook( __FILE__, 'deactivate_etsy_dokan_integration_for_woocommerc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-etsy-dokan-integration-for-woocommerc.php';
require plugin_dir_path( __FILE__ ) . 'includes/ced-etsy-dokan-core-functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_etsy_dokan_integration_for_woocommerc() {

	$plugin = new Etsy_Dokan_Integration_For_Woocommerc();
	$plugin->run();

}
run_etsy_dokan_integration_for_woocommerc();


/* Register activation hook. */
register_activation_hook( __FILE__, 'ced_dokan_admin_notice_example_activation_hook_ced_etsy' );

/**
 * Runs only when the plugin is activated.
 *
 * @since 2.0.0
 */
function ced_dokan_admin_notice_example_activation_hook_ced_etsy() {

	/* Create transient data */
	set_transient( 'ced-etsy-admin-notice', true, 5 );
}

/*Admin admin notice */

add_action( 'admin_notices', 'ced_etsy_dokan_admin_notice_activation' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */


function ced_etsy_dokan_admin_notice_activation() {

	/* Check transient, if available display notice */
	if ( get_transient( 'ced-etsy-admin-notice' ) ) {?>
		<div class="updated notice is-dismissible">
			<p>Welcome to WooCommerce Etsy Integration. Start listing, syncing, managing, & automating your WooCommerce and Etsy stores to boost sales.</p>
			<a href="admin.php?page=ced_etsy" class ="ced_configuration_plugin_main">Connect to Etsy</a>
		</div>
		<?php
		/* Delete transient, only display this notice once. */
		delete_transient( 'ced-etsy-admin-notice' );
	}
}