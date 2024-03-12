<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */
class Good_Market_Woocommerce_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		self::disable_goodmarket_upload_feed_status_schedule();
	}
	private static function disable_goodmarket_upload_feed_status_schedule() {
		wp_clear_scheduled_hook( 'sync_good_market_feeds' );
	}

}
