<?php
/**
 * Gettting category related data
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Class_Ced_Good_Market_Category' ) ) {

	/**
	 * Class_Ced_Good_Market_Category .
	 *
	 * @since 1.0.0
	 */
	class Class_Ced_Good_Market_Category {

		/**
		 * The instance variable of this class.
		 *
		 * @since    1.0.0
		 * @var      object    $_instance    The instance variable of this class.
		 */
		public static $_instance;

		/**
		 * Class_Ced_Good_Market_Category Instance.
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Class_Ced_Good_Market_Category construct.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->load_dependency();
		}


		/**
		 * Class_Ced_Good_Market_Category loading dependency.
		 *
		 * @since 1.0.0
		 */
		public function load_dependency() {
			require_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
			$this->good_market_send_http_request_instance = new Ced_Good_Market_Request();
		}

		/**
		 * Function for getting category specific attributes
		 *
		 * @since 1.0.0
		 * @param int $shop_id Shopee Shop Id.
		 * @param int $good_market_category_id Shopee Category Id.
		 */
		public function ced_good_market_get_category_attributes( $shop_id = '', $good_market_category_id = '' ) {

			if ( empty( $good_market_category_id ) ) {
				return;
			}
			// print_r($good_market_category_id);
			$action                    = 'product/get_attributes';
			$parameters                = array();
			$parameters['category_id'] = (int) $good_market_category_id;
			do_action( 'ced_good_market_refresh_token', $shop_id );
			$category_attributes = $this->good_market_send_http_request_instance->send_http_request( $action, $parameters, $shop_id, '', true );
			return $category_attributes;
		}

		/**
		 * Function for getting category specific Brand List
		 *
		 * @since 1.0.0
		 * @param int $shop_id Shopee Shop Id.
		 * @param int $good_market_category_id Shopee Category Id.
		 */
		public function ced_good_market_get_category_brand_list( $shop_id = '', $good_market_category_id = '' ) {

			if ( empty( $good_market_category_id ) ) {
				return;
			}
			$action                    = 'product/get_brand_list';
			$parameters                = array();
			$parameters['offset']      = 0;
			$parameters['page_size']   = 100;
			$parameters['category_id'] = (int) $good_market_category_id;
			$parameters['status']      = 1;
			do_action( 'ced_good_market_refresh_token', $shop_id );
			$category_attributes = $this->good_market_send_http_request_instance->send_http_request( $action, $parameters, $shop_id, '', true );
			return $category_attributes;
		}

		/**
		 * Function for getting good_market categories
		 *
		 * @since 1.0.0
		 * @param int $shop_id Shopee Shop Id.
		 */
		public function get_good_market_category( $shop_id = '' ) {
			if ( empty( $shop_id ) ) {
				return;
			}
			do_action( 'ced_good_market_refresh_token', $shop_id );
			$action     = 'product/get_category';
			$categories = $this->good_market_send_http_request_instance->send_http_request( $action, array(), $shop_id, '', true );
			return $categories;
		}

		/**
		 * Function for getting updated good_market categories
		 *
		 * @since 1.0.0
		 * @param int $shop_id Shopee Shop Id.
		 */
		public function get_refreshed_good_market_category( $shop_id = '' ) {
			if ( empty( $shop_id ) ) {
				return;
			}
			do_action( 'ced_good_market_refresh_token', $shop_id );
			$action     = 'product/get_category';
			$categories = $this->good_market_send_http_request_instance->send_http_request( $action, array(), $shop_id, '', true );
			return $categories;
		}
	}
}
