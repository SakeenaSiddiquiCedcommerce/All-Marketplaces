<?php
/**
 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce reverb Integration
 * @subpackage Woocommerce reverb Integration/admin/reverb
 */

if ( ! class_exists( 'Class_Ced_reverb_Manager' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on admin.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce reverb Integration
	 * @subpackage Woocommerce reverb Integration/admin/reverb
	 * author     CedCommerce <cedcommerce.com>
	 */
	class Ced_Reverb_Manager {

		/**
		 * The Instace of CED_reverb_reverb_Manager.
		 *
		 * @since    1.0.0
		 * access   private
		 * @var      $_instance   The Instance of CED_reverb_reverb_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_reverb_reverb_Manager Instance.
		 *
		 * Ensures only one instance of CED_reverb_reverb_Manager is loaded or can be loaded.
		 *
		 * author CedCommerce <plugins@cedcommerce.com>
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_reverb_reverb_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'reverb';
		public $marketplaceName = 'reverb';


		public function __construct() {
			$this->loadDependency();
			add_action( 'updated_post_meta', array( $this, 'ced_relatime_sync_inventory_to_reverb' ), 12, 4 );
		}


		public function ced_relatime_sync_inventory_to_reverb( $meta_id, $product_id, $meta_key, $meta_value ) {

			// If tha is changed by _stock only.
			if ( '_stock' == $meta_key || '_price' == $meta_key || '_regular_price' == $meta_key ) {

				$_product = wc_get_product( $product_id );
				// if ( ! wp_get_schedule( 'ced_reverb_product_update' ) || ! is_object( $_product ) ) {
				// 	return;
				// }
				// All products by product id
				// check if it has variations.
				if ( $_product->get_type() == 'variation' ) {
					$product_id = $_product->get_parent_id();
				}
				
				$my_arr = get_option('ced_reverb_product_ids_to_update_by_post_meta', array() );
				$my_arr[] = $product_id;
				$my_arr = array_unique($my_arr);
				update_option('ced_reverb_product_ids_to_update_by_post_meta', $my_arr);

				//$this->prepareProductUpdateInventory( array( $product_id ) );
			}
		}



		public function loadDependency() {

			$fileConfig = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-config.php';
			if ( file_exists( $fileConfig ) ) {
				require_once $fileConfig;
			}
			$fileProducts = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-product.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}

			require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
			$this->sendRequestObj = new Ced_Reverb_Curl_Request();

			$this->ced_reverb_configInstance = new Ced_Reverb_Config();
			$this->reverbProductsInstance    = Ced_Reverb_Product::get_instance();
		}




		public function prepareProductHtmlForUpload( $proIDs = array() ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}

			$response = $this->reverbProductsInstance->ced_reverb_prepareDataForUploading( $proIDs );
			return $response;
		}

		/**
		 * Remove selected products on REVERB.
		 * Author CedCommerce <plugins@cedcommerce.com>
		 *
		 * @since 1.0.0
		 * @param array $proIds
		 */
		public function reverbRemove( $proIDs = array(), $isWriteXML = true ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->reverbProductsInstance->ced_reverb_reverbRemove( $proIDs );
			return $response;

		}

		public function prepareProductUpdateInventory( $proIDs = array(), $UpdateOrDelete = '', $isCron = false ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}

			$response = $this->reverbProductsInstance->ced_reverb_prepareUpdateInventory( $proIDs, $UpdateOrDelete, $isCron );
			return $response;
		}

	}
}
