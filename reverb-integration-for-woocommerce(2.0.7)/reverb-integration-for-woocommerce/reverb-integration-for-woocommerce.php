<?php
/**
 * Wordpress-plugin
 * Plugin Name:       Reverb Integration for WooCommerce
 * Plugin URI:        https://woocommerce.com/products/reverb-integration-for-woocommerce/
 * Description:       Reverb Integration for WooCommerce allows merchants to list their products on Reverb marketplace and manage the orders from the woocommerce store.
 * Version:           2.0.7
 * Author:            CedCommerce
 * Author URI:        https://woocommerce.com/vendor/cedcommerce/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:      reverb-integartion-for-woocommerce
 * Domain Path:       /languages
 *
 * Woo: 7274710:19abaae9947c531e7a81c2cea3078682
 * WC requires at least: 3.0
 * WC tested up to: 4.7.1
 *
 * @package  Woocommmerce_Reverb_Integration
 * @version  1.0.0
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
define( 'REVERB_INTEGARTION_FOR_WOOCOMMERCE_VERSION', '2.0.7' );
define( 'CED_REVERB_LOG_DIRECTORY', wp_upload_dir()['basedir'] . '/ced_mall_log_directory' );
define( 'CED_REVERB_VERSION', '2.0.7' );
define( 'CED_REVERB_PREFIX', 'ced_mall' );
define( 'CED_REVERB_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'CED_REVERB_URL', plugin_dir_url( __FILE__ ) );
define( 'CED_REVERB_ABSPATH', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );
define( 'CED_REVERB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-reverb-integartion-for-woocommerce-activator.php
 */
function activate_reverb_integartion_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-reverb-integartion-for-woocommerce-activator.php';
	Reverb_Integartion_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-reverb-integartion-for-woocommerce-deactivator.php
 */
function deactivate_reverb_integartion_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-reverb-integartion-for-woocommerce-deactivator.php';
	Reverb_Integartion_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_reverb_integartion_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_reverb_integartion_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-reverb-integartion-for-woocommerce.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ced-reverb-core-functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_reverb_integartion_for_woocommerce() {

	$plugin = new Reverb_Integartion_For_Woocommerce();
	$plugin->run();

}

/**
 * Ced_admin_notice_example_activation_hook_ced_mall.
 *
 * @since 1.0.0
 */
function ced_admin_notice_example_activation_hook_ced_reverb() {
	set_transient( 'ced-reverb-admin-notice', true, 5 );
}


/**
 * This code runs when WooCommerce is not activated,
 *
 * @since 1.0.0
 */
function deactivate_ced_reverb_woo_missing() {
	deactivate_plugins( CED_REVERB_PLUGIN_BASENAME );
	add_action( 'admin_notices', 'ced_reverb_woo_missing_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}
/**
 * Ced_mall_admin_notice_activation.
 *
 * @since 1.0.0
 */
function ced_reverb_admin_notice_activation() {
	if ( get_transient( 'ced-reverb-admin-notice' ) ) {?>
		<div class="updated notice is-dismissible">
			<p>Welcome to Reverb Integration For WooCommerce. Start listing, syncing, managing, & automating your WooCommerce and Reverb store to boost sales.</p>
			<a href="admin.php?page=ced_reverb" class ="ced_configuration_plugin_main">Connect to Reverb</a>
		</div>
		<?php
		delete_transient( 'ced-reverb-admin-notice' );
	}
}
/**
 * Callback function for sending notice if woocommerce is not activated.
 *
 * @since 1.0.0
 */
function ced_reverb_woo_missing_notice() {
	// translators: %s: search term !!
	echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( esc_html( __( 'Reverb Integration For Woocommerce requires WooCommerce to be installed and active. You can download %s from here.', 'reverb-woocommerce-integration' ) ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}
/**
 * Check WooCommerce is Installed and Active.
 *
 * @since 1.0.0
 */
if ( ced_reverb_check_woocommerce_active() ) {
	run_reverb_integartion_for_woocommerce();
	register_activation_hook( __FILE__, 'ced_admin_notice_example_activation_hook_ced_reverb' );
	add_action( 'admin_notices', 'ced_reverb_admin_notice_activation' );
} else {
	add_action( 'admin_init', 'deactivate_ced_reverb_woo_missing' );
}


function ced_reverb_check_woocommerce_active() {
	/**Get active plugin list
	 *
	 *@since 1.0.0
	 */
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return true;
	}
	return false;
}