<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/includes
 * @author     cedcommerce <support@cedcommerce.com>
 */
class Cedcommerce_Vidaxl_Dropshipping_Activator {

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
		$table_name = 'wp_ced_vidaxl_temp_product_data';

		$create_table_vidaxl_temp_product_data =
			"CREATE TABLE $table_name (
			 `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`sku` varchar(50) NOT NULL,
			`title` varchar(250) NOT NULL,
			`category` text NOT NULL,
			`b2b_price` decimal(10,2) NOT NULL,
			`stock` varchar(50) NOT NULL,
			`description` text NOT NULL,
			`properties` text NOT NULL,
			`weight` varchar(50) NOT NULL,
			`image1` varchar(250) NOT NULL,
			`image2` varchar(250) NOT NULL,
			`image3` varchar(250) NOT NULL,
			`image4` varchar(250) NOT NULL,
			`image5` varchar(250) NOT NULL,
			`image6` varchar(250) NOT NULL,
			`image7` varchar(250) NOT NULL,
			`image8` varchar(250) NOT NULL,
			`image9` varchar(250) NOT NULL,
			`image10` varchar(250) NOT NULL,
			`image11` varchar(250) NOT NULL,
			`image12` varchar(250) NOT NULL,
			`ean` varchar(100) NOT NULL,
			`html_description` text NOT NULL,
			`category_id` varchar(100) NOT NULL,
			`webshop_price` decimal(10,2) NOT NULL,
			`date_time` datetime NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (id)
		);";
		dbDelta( $create_table_vidaxl_temp_product_data );
	}

}
