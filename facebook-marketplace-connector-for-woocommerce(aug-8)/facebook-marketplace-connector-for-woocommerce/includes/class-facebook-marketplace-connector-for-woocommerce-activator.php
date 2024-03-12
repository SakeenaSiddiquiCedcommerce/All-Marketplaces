<?php
/**
 * Fired during plugin activation
 *
 * @link       https://woocommerce.com/vendor/cedcommerce/
 * @since      1.0.0
 *
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/includes
 */
class Facebook_Marketplace_Connector_For_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$create_profile_table =
			"CREATE TABLE {$prefix}ced_fb_profiles (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			profile_name VARCHAR(255) NOT NULL,
			profile_status VARCHAR(255) NOT NULL,
			profile_data TEXT DEFAULT NULL,
			woo_categories TEXT DEFAULT NULL,
			PRIMARY KEY (id)
		);";

		$ced_fb_feeds_status =
		"CREATE TABLE {$prefix}ced_fb_feeds_status (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        feed_id VARCHAR(255) NOT NULL,
        feed_status VARCHAR(255) NOT NULL,
        feed_type VARCHAR(255) NOT NULL
        feed_time VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
        );";
		dbDelta( $ced_fb_feeds_status );
		dbDelta( $create_profile_table );

	}

}
