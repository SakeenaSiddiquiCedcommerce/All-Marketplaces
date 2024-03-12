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
 * @package           Cedcommerce_Vidaxl_Dropshipping
 *
 * @wordpress-plugin
 * Plugin Name:       Cedcommerce-VidaXL Dropshipping for WooCommerce
 * Plugin URI:        https://cedcommerce.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            cedcommerce
 * Author URI:        https://cedcommerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cedcommerce-vidaxl-dropshipping
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
define( 'CEDCOMMERCE_VIDAXL_DROPSHIPPING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cedcommerce-vidaxl-dropshipping-activator.php
 */
function activate_cedcommerce_vidaxl_dropshipping() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cedcommerce-vidaxl-dropshipping-activator.php';
	Cedcommerce_Vidaxl_Dropshipping_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cedcommerce-vidaxl-dropshipping-deactivator.php
 */
function deactivate_cedcommerce_vidaxl_dropshipping() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cedcommerce-vidaxl-dropshipping-deactivator.php';
	Cedcommerce_Vidaxl_Dropshipping_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cedcommerce_vidaxl_dropshipping' );
register_deactivation_hook( __FILE__, 'deactivate_cedcommerce_vidaxl_dropshipping' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cedcommerce-vidaxl-dropshipping.php';

/**
 * Check WooCommerce is Installed and Active.
 *
 * since VidaXL Dropshipping is extension for WooCommerce it's necessary,
 * to check that WooCommerce is installed and activated or not,
 * if yes allow extension to execute functionalities and if not
 * let deactivate the extension and show the notice to admin.
 * 
 * @author CedCommerce
 */
if(ced_vidaxl_dropshipping_check_woocommerce_active()){
	run_cedcommerce_vidaxl_dropshipping();
}else{
	add_action( 'admin_init', 'deactivate_ced_dropshipping_vidaxl_woo_missing' );
}

/**
 * Check WooCommmerce active or not.
 *
 * @since 1.0.0
 * @return bool true|false
 */
function ced_vidaxl_dropshipping_check_woocommerce_active(){
	if ( function_exists('is_multisite') && is_multisite() ){

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){

			return true;
		}
		return false;
	}else{
			
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ){

			return true;
		}
		return false;
	}
}
/**
 * This code runs when WooCommerce is not activated,
 * deativates the extension and displays the notice to admin.
 *
 * @since 1.0.0
 */
function deactivate_ced_dropshipping_vidaxl_woo_missing() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action('admin_notices', 'ced_vidaxl_dropshipping_woo_missing_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}
/**
 * callback function for sending notice if woocommerce is not activated.
 *
 * @since 1.0.0
 * @return string
 */
function ced_vidaxl_dropshipping_woo_missing_notice(){

	echo '<div class="error"><p>' . sprintf(__('VidaXL Dropshipping plugin requires WooCommerce to be installed and active. You can download %s here.', 'cedcommerce-vidaxl-dropshipping'), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>') . '</p></div>';
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cedcommerce_vidaxl_dropshipping() {

	$plugin = new Cedcommerce_Vidaxl_Dropshipping();
	$plugin->run();

}

