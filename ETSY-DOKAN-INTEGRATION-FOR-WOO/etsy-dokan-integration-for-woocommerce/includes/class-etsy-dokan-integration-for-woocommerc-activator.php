<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/includes
 * @author     Cedcommerce <support@cedcommerce.com>
 */
class Etsy_Dokan_Integration_For_Woocommerc_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tableName             = $wpdb->prefix . 'ced_etsy_accounts';
		$create_accounts_table =
		"CREATE TABLE $tableName (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		account_status VARCHAR(255) NOT NULL,
		shop_id BIGINT(20) DEFAULT NULL,
		-- location VARCHAR(50) NOT NULL,
		shop_data TEXT DEFAULT NULL,
		PRIMARY KEY (id)
		);";
		dbDelta( $create_accounts_table );

		$tableName = $wpdb->prefix . 'ced_etsy_dokan_profiles';

		$create_profile_table =
		"CREATE TABLE $tableName (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		profile_name VARCHAR(255) NOT NULL,
		profile_status VARCHAR(255) NOT NULL,
		de_shop_name VARCHAR(255) DEFAULT NULL,
		vendor_id VARCHAR(255) DEFAULT NULL,
		profile_data TEXT DEFAULT NULL,
		woo_categories TEXT DEFAULT NULL,
		PRIMARY KEY (id)
		);";
		dbDelta( $create_profile_table );
	}

}
