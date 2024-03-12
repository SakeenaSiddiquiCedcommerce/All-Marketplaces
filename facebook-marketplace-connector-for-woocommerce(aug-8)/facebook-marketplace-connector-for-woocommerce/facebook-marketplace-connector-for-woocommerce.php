<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://woocommerce.com/vendor/cedcommerce/
 * @since             1.0.0
 * @package           Facebook_Marketplace_Connector_For-Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Facebook Marketplace For Woocommerce
 * Plugin URI:        https://woocommerce.com/vendor/cedcommerce/
 * Description:       Instagram Shopping for WooCommerce connects the woocommerce store with the Instagram marketplace by synchronizing the inventory, price, and other product details for the product creation.
 * Version:           1.0.3
 * Author:            CedCommerce
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       facebook-marketplace-connector-for-woocommerce
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
define( 'FACEBOOK_MARKETPLACE_CONNECTOR_FOR_WOOCOMMERCE_VERSION', '1.0.4' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-facebook-marketplace-connector-for-woocommerce-activator.php
 */
function activate_facebook_marketplace_connector_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-facebook-marketplace-connector-for-woocommerce-activator.php';
	Facebook_Marketplace_Connector_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-facebook-marketplace-connector-for-woocommerce-deactivator.php
 */
function deactivate_facebook_marketplace_connector_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-facebook-marketplace-connector-for-woocommerce-deactivator.php';
	Facebook_Marketplace_Connector_For_Woocommerce_Deactivator::deactivate();

}


register_deactivation_hook( __FILE__, 'deactivate_facebook_marketplace_connector_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-facebook-marketplace-connector-for-woocommerce.php';
require plugin_dir_path( __FILE__ ) . 'admin/lib/JWT/vendor/autoload.php';

/**
 * The core function file that is been used for common functions throughout the plugin.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/ced-facebook-marketplace-connector-for-woocommerce-core-functions.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_facebook_marketplace_connector_for_woocommerce() {

	$plugin = new Facebook_Marketplace_Connector_For_Woocommerce();
	$plugin->run();

}

/**
 * This code runs when WooCommerce is not activated,
 *
 * @since 1.0.0
 */
function deactivate_facebook_marketplace_woo_missing() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action( 'admin_notices', 'ced_fmcw_basic_plugin_activation_failure_admin_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}


/**
 * Ced_facebook_admin_notice_activation.
 *
 * @since 1.0.0
 */
function ced_facebook_admin_notice_activation() {
	if ( get_transient( 'ced-facebook-actiavte-plugin-admin-notice' ) ) {?>
		<div class="updated notice is-dismissible">
			<p>Welcome to Facebook Marketplace Connector For Woocommerce. Start listing, syncing, managing, & automating your WooCommerce store.</p>
			<p>To get started , proceed with <a href="admin.php?page=ced_fb" class ="ced_configuration_plugin_main">connecting</a> your Facebook Marketplace Connector account.</p>
		</div>
		<?php
		delete_transient( 'ced-facebook-actiavte-plugin-admin-notice' );
	}
}

if ( ced_facebook_marketplace_check_woocommerce_active() ) {
	register_activation_hook( __FILE__, 'activate_facebook_marketplace_connector_for_woocommerce' );
	run_facebook_marketplace_connector_for_woocommerce();
	add_action( 'admin_notices', 'ced_facebook_admin_notice_activation' );
} else {
	add_action( 'admin_init', 'deactivate_facebook_marketplace_woo_missing' );
}

function ced_facebook_marketplace_check_woocommerce_active() {
	/**Get active plugin list
	 *
	 *@since 1.0.0
	 */
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return true;
	}
	return false;
}



/**
 * This function is used to display failure message if WooCommerce is deactivated.
 *
 * @name ced_fmcw_basic_plugin_activation_failure_admin_notice()
 *
 * @link http://cedcommerce.com/
 */
function ced_fmcw_basic_plugin_activation_failure_admin_notice() {
	// translators: %s: search term !!
	echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( esc_html( __( ' Facebook Marketplace Connector For Woocommerce requires WooCommerce to be installed and active. You can download %s from here.', 'GoodMarket-woocommerce-integration' ) ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}
?>