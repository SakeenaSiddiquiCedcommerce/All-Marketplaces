<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */
class Good_Market_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_tables();
		self::enable_goodmarket_upload_feed_status_schedule();
	}
	private static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$prefix                    = $wpdb->prefix;
		$good_market_upload_status =
		"CREATE TABLE {$prefix}good_market_upload_status (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        feed_id VARCHAR(255) NOT NULL,
        feed_status VARCHAR(255) NOT NULL,
        feed_time VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
        );";
		dbDelta( $good_market_upload_status );
	}

	private static function enable_goodmarket_upload_feed_status_schedule() {
		wp_clear_scheduled_hook( 'sync_good_market_feeds' );
		wp_schedule_event( time(), 'ced_good_market_5min', 'sync_good_market_feeds' );
	}

}
