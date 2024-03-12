<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Class_Ced_Etsy_Category' ) ) {

	class Class_Ced_Etsy_Category {


		public static $_instance;

		/**
		 * Ced_Etsy_Config Instance.
		 *
		 * Ensures only one instance of Ced_Etsy_Config is loaded or can be loaded.
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
		 * Etsy getting seller taxonomies
		 *
		 * @since    1.0.0
		 */
		public function getEtsyCategories( $de_shop_name = '' ) {
			$vendor_id      = get_current_user_id();
			do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name, $vendor_id );
			$categories = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name, "application/seller-taxonomy/nodes", 'GET', array(), array(), $vendor_id );
			return $categories;
		}
	}
}
