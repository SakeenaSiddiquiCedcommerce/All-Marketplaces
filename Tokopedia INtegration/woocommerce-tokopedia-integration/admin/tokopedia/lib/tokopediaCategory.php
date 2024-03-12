<?php
/**
 * The admin-category related functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Class_Ced_Tokopedia_Category' ) ) {
	/**
	 * Category  class related to all the category.
	 */
	class Class_Ced_Tokopedia_Category {
		/**
		 * Instance variable to create intance of Class.
		 *
		 * @var object
		 */
		public static $_instance;

		/**
		 * Ced_Tokopedia_Config Instance.
		 *
		 * Ensures only one instance of Ced_Tokopedia_Config is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Tokopedia getting seller taxonomies.
		 *
		 * @since    1.0.0
		 */
		public function getTokopediaCategories( $shop_name ) {

			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
				$access_token = $shop_data['access_token'];
				$fsid         = $shop_data['fsid'];
			}
			$header   = array(
				'Authorization: Bearer ' . $access_token,
			);
			$curl_url = 'https://fs.tokopedia.net/inventory/v1/fs/' . $fsid . '/product/category';
			$ch       = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $curl_url );
			curl_setopt( $ch, CURLOPT_POST, false );
			curl_setopt( $ch, CURLOPT_HTTPGET, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );			
			$result     = curl_exec( $ch );
			$categories = json_decode( $result, true );
			curl_close( $ch );
			return $categories;
		}
	}
}
