<?php
/**
 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce Tokopedia Integration
 * @subpackage Woocommerce Tokopedia Integration/marketplaces/tokopedia
 */

if ( ! class_exists( 'CED_TOKOPEDIA_Manager' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce Tokopedia Integration
	 * @subpackage Woocommerce Tokopedia Integration/marketplaces/tokopedia
	 */
	class CED_TOKOPEDIA_Manager {

		/**
		 * The Instace of CED_TOKOPEDIA_topedia_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_TOKOPEDIA_tokopedia_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_TOKOPEDIA_tokopedia_Manager Instance.
		 *
		 * Ensures only one instance of CED_TOKOPEDIA_tokopedia_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_TOKOPEDIA_tokopedia_Manager instance.
		 */
		public static function get_instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'tokopedia';
		public $marketplaceName = 'Tokopedia';


		/**
		 * Constructor.
		 *
		 * Registering actions and hooks for tokopedia.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {


			$this->loadDependency();
			add_action( 'ced_tokopedia_additional_configuration', array( $this, 'ced_tokopedia_additional_shipping_configuration' ), 10, 2 );
			add_action( 'ced_tokopedia_additional_configuration', array( $this, 'ced_tokopedia_additional_payment_configuration' ), 11, 2 );
			add_action( 'ced_tokopedia_additional_configuration', array( $this, 'ced_tokopedia_additional_shop_section_configuration' ), 12, 2 );
			//add_action( 'woocommerce_thankyou', array( $this, 'ced_tokopedia_update_inventory_on_order_creation' ), 10, 1 );
			// add_action( 'save_post', array( $this, 'ced_tokopedia_update_inventory_on_save_post' ), 10, 1 );
		}

		public function ced_tokopedia_schedules() {
			if ( isset( $_GET['shop_name'] ) && ! empty( $_GET['shop_name'] ) ) {
				$shop_name = sanitize_text_field( $_GET['shop_name'] );
				if ( ! wp_get_schedule( 'ced_tokopedia_sync_existing_products_job_' . $shop_name ) ) {
					wp_schedule_event( time(), 'ced_tokopedia_6min', 'ced_tokopedia_sync_existing_products_job_' . $shop_name );
				}
			}
		}

		public function ced_tokopedia_update_inventory_on_save_post( $post_id ) {
			if ( empty( $post_id ) ) {
				return;
			}
			$response = $this->prepareProductHtmlForUpdateInventory( array( $post_id ), '', true );
		}

		public function ced_tokopedia_update_inventory_on_order_creation( $order_id ) {
			if ( empty( $order_id ) ) {
				return;
			}
			$product_ids   = array();
			$inventory_log = array();
			$order_obj     = wc_get_order( $order_id );
			$order_items   = $order_obj->get_items();
			if ( is_array( $order_items ) && ! empty( $order_items ) ) {
				foreach ( $order_items as $key => $value ) {
					$product_id    = $value->get_data()['product_id'];
					$product_ids[] = $product_id;
				}
			}
			if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {
				$response        = $this->prepareProductHtmlForUpdateInventory( $product_ids, '', true );
				$inventory_log[] = $response;
			}
		}



		/**
		 * Tokopedia Loading dependencies
		 *
		 * @since    1.0.0
		 */
		public function loadDependency() {
			if ( session_status() == PHP_SESSION_NONE && ! headers_sent() ) {
				session_start();
			}

			$fileProducts = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}

			$this->tokopediaProductsInstance = Class_Ced_Tokopedia_Products::get_instance();
		}

		/**
		 * Function to load shipping template
		 *
		 * @since    1.0.0
		 * @param sting  $marketPlaceName active marketplace name.
		 * @param string $shop_name active shop name .
		 */
		public function ced_tokopedia_additional_shipping_configuration( $marketPlaceName = 'tokopedia', $shop_name ) {
			echo '<div class="ced_tokopedia_shippingConfig">';
			require_once CED_TOKOPEDIA_DIRPATH . 'admin/pages/shipping-config.php';
			echo '</div>';
		}

		/**
		 * Function to load payment template
		 *
		 * @since    1.0.0
		 */
		public function ced_tokopedia_additional_payment_configuration( $marketPlaceName = 'tokopedia', $shop_name ) {
			echo '<div class="ced_tokopedia_paymentConfig">';
			require_once CED_TOKOPEDIA_DIRPATH . 'admin/pages/payment-config.php';
			echo '</div>';
		}
		/**
		 * Function to load shop section template
		 *
		 * @since    1.0.0
		 */

		public function ced_tokopedia_additional_shop_section_configuration( $marketPlaceName = 'tokopedia', $shop_name ) {
			echo '<div class="ced_tokopedia_shpSectionConfig">';
			require_once CED_TOKOPEDIA_DIRPATH . 'admin/pages/shop-section-config.php';
			echo '</div>';
		}

		/**
		 * Ced Tokopedia Fetch Categories
		 *
		 * @since    1.0.0
		 */
		public function ced_tokopedia_get_categories() {
			$file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaCategory.php';
			if ( ! file_exists( $file ) ) {
				return;
			}
			require_once $file;
			$tokopediaCategoryInstance = Class_Ced_Tokopedia_Category::get_instance();
			$fetchedCategories    = $tokopediaCategoryInstance->getTokopediaCategories();
			$categories           = $this->StoreCategories( $fetchedCategories );

		}

		/**
		 * Tokopedia Storing Categories
		 *
		 * @since    1.0.0
		 */
		public function StoreCategories( $fetchedCategories, $ajax = '' ) {

			foreach ( $fetchedCategories['data']['categories'] as $key => $value ) {
				if ( count( $value['child'] ) > 0 ) {
					$arr1[] = array(
						'id'    => $value['id'],
						'name'  => $value['name'],
						'child' => count( $value['child'] ),
					);
				} else {
					$arr1[] = array(
						'id'    => $value['id'],
						'name'  => $value['name'],
						'child' => 0,
					);
				}
				foreach ( $value['child'] as $key1 => $value1 ) {
					if ( count( $value1['child'] ) > 0 ) {
						$arr2[] = array(
							'parent_id' => $value['id'],
							'id'        => $value1['id'],
							'name'      => $value1['name'],
							'child'     => count( $value1['child'] ),
						);
					} else {
						$arr2[] = array(
							'parent_id' => $value['id'],
							'id'        => $value1['id'],
							'name'      => $value1['name'],
							'child'     => 0,
						);
					}
					foreach ( $value1['child'] as $key2 => $value2 ) {

							$arr3[] = array(
								'parent_id' => $value1['id'],
								'id'        => $value2['id'],
								'name'      => $value2['name'],
								'child'     => 0,
							);

					}
				}
			}

			$folderName         = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/json/';
			$catFirstLevelFile  = $folderName . 'categoryLevel-1.json';
			$catSecondLevelFile = $folderName . 'categoryLevel-2.json';
			$catThirdLevelFile  = $folderName . 'categoryLevel-3.json';
			
			if ( !empty( $arr1 ) ) {
				file_put_contents( $catFirstLevelFile, json_encode( $arr1 ) );
			}
			if ( !empty( $arr2 ) ) {
				file_put_contents( $catSecondLevelFile, json_encode( $arr2 ) );
			}
			if ( !empty( $arr3 ) ) {
				file_put_contents( $catThirdLevelFile, json_encode( $arr3 ) );
			}
	
			update_option( 'ced_tokoped_categories_fetched', 'Yes' );

			if ( $ajax ) {
				return 'true';
				die;
			}
		}

		/**
		 * Tokopedia Create Auto Profiles
		 *
		 * @since    1.0.0
		 */
		public function ced_tokopedia_createAutoProfiles( $tokopediaMappedCategories = array(), $tokopediaMappedCategoriesName = array(), $tokopediaStoreId = '' ) {
			global $wpdb;

			$wooStoreCategories          = get_terms( 'product_cat' );
			$alreadyMappedCategories     = get_option( 'ced_woo_tokopedia_mapped_categories_' . $tokopediaStoreId, array() );
			$alreadyMappedCategoriesName = get_option( 'ced_woo_tokopedia_mapped_categories_name_' . $tokopediaStoreId, array() );

			if ( ! empty( $tokopediaMappedCategories ) ) {
				foreach ( $tokopediaMappedCategories as $key => $value ) {
					$profileAlreadyCreated = get_term_meta( $key, 'ced_tokopedia_profile_created_' . $tokopediaStoreId, true );
					$createdProfileId      = get_term_meta( $key, 'ced_tokopedia_profile_id_' . $tokopediaStoreId, true );
					if ( ! empty( $profileAlreadyCreated ) && 'yes' == $createdProfileId ) {

						$newProfileNeedToBeCreated = $this->checkIfNewProfileNeedToBeCreated( $key, $value, $tokopediaStoreId );

						if ( ! $newProfileNeedToBeCreated ) {
							continue;
						} else {
							$this->resetMappedCategoryData( $key, $value, $tokopediaStoreId );
						}
					}

					$wooCategories      = array();
					$categoryAttributes = array();

					$profileName     = isset( $tokopediaMappedCategoriesName[ $value ] ) ? $tokopediaMappedCategoriesName[ $value ] : 'Profile for tokopedia - Category Id : ' . $value;

					$profile_id = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `profile_name` = %s", $profileName ), 'ARRAY_A' );

					if( ! isset( $profile_id[0]['id'] ) && empty( $profile_id[0]['id'] ) ) {
						$is_active       = 1;
						$marketplaceName = 'tokopedia';

						foreach ( $tokopediaMappedCategories as $key1 => $value1 ) {
							if ( $value1 == $value ) {
								$wooCategories[] = $key1;
							}
						}

						$profileData    = array();
						$profileData    = $this->prepareProfileData( $tokopediaStoreId, $value, $wooCategories );
						$profileDetails = array(
							'profile_name'   => $profileName,
							'profile_status' => 'active',
							'shop_name'      => $tokopediaStoreId,
							'profile_data'   => json_encode( $profileData ),
							'woo_categories' => json_encode( $wooCategories ),
						);
						$profileId      = $this->inserttokopediaProfile( $profileDetails );
					}else {
						$wooCategories     = array();
						$profileId         = $profile_id[0]['id'];
						$profile_categories = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `id` = %d ", $profileId ), 'ARRAY_A' );
						$wooCategories     = json_decode( $profile_categories[0]['woo_categories'], true );
						$wooCategories[]   = $key;
						$table_name         = $wpdb->prefix . 'ced_tokopedia_profiles';
						$wpdb->update(
							$table_name,
							array(
								'woo_categories' => json_encode( array_unique($wooCategories) ),
							),
							array( 'id' => $profileId )
						);
					}
					foreach ( $wooCategories as $key12 => $value12 ) {
						update_term_meta( $value12, 'ced_tokopedia_profile_created_' . $tokopediaStoreId, 'yes' );
						update_term_meta( $value12, 'ced_tokopedia_profile_id_' . $tokopediaStoreId, $profileId );
						update_term_meta( $value12, 'ced_tokopedia_mapped_category_' . $tokopediaStoreId, $value );
					}
				}
			}
		}

		/**
		 * Tokopedia Insert Profiles In database
		 *
		 * @since    1.0.0
		 */
		public function inserttokopediaProfile( $profileDetails ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_tokopedia_profiles';
			$wpdb->insert( $profileTableName, $profileDetails );
			$profileId = $wpdb->insert_id;
			return $profileId;
		}

		/**
		 * Tokopedia Check if Profile Need to be Created
		 *
		 * @since    1.0.0
		 */
		public function checkIfNewProfileNeedToBeCreated( $wooCategoryId = '', $tokopediaCategoryId = '', $tokopediaStoreId = '' ) {

			$oldtokopediaCategoryMapped = get_term_meta( $wooCategoryId, 'ced_tokopedia_mapped_category_' . $tokopediaStoreId, true );
			if ( $oldtokopediaCategoryMapped == $tokopediaCategoryId ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Tokopedia Update Mapped Category data
		 *
		 * @since    1.0.0
		 */
		public function resetMappedCategoryData( $wooCategoryId = '', $tokopediaCategoryId = '', $tokopediaStoreId = '' ) {

			update_term_meta( $wooCategoryId, 'ced_tokopedia_mapped_category_' . $tokopediaStoreId, $tokopediaCategoryId );
			delete_term_meta( $wooCategoryId, 'ced_tokopedia_profile_created_' . $tokopediaStoreId );
			$createdProfileId = get_term_meta( $wooCategoryId, 'ced_tokopedia_profile_id_' . $tokopediaStoreId, true );
			delete_term_meta( $wooCategoryId, 'ced_tokopedia_profile_id_' . $tokopediaStoreId );
			$this->removeCategoryMappingFromProfile( $createdProfileId, $wooCategoryId );
		}

		/**
		 * Tokopedia Remove previous mapped profile
		 *
		 * @since    1.0.0
		 */
		public function removeCategoryMappingFromProfile( $createdProfileId = '', $wooCategoryId = '' ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_tokopedia_profiles';
			$profile_data     = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `id`=%s ", $createdProfileId ), 'ARRAY_A' );

			if ( is_array( $profile_data ) ) {

				$profile_data  = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$wooCategories = isset( $profile_data['woo_categories'] ) ? json_decode( $profile_data['woo_categories'], true ) : array();
				if ( is_array( $wooCategories ) && ! empty( $wooCategories ) ) {
					$categories = array();
					foreach ( $wooCategories as $key => $value ) {
						if ( $value != $wooCategoryId ) {

							$categories[] = $value;
						}
					}
					$categories = json_encode( $categories );
					$wpdb->update( $profileTableName, array( 'woo_categories' => $categories ), array( 'id' => $createdProfileId ) );
				}
			}
		}

		/**
		 * Tokopedia Prepare Profile data
		 *
		 * @since    1.0.0
		 */
		public function prepareProfileData( $tokopediaStoreId, $tokopediaCategoryId, $wooCategories = '' ) {

			$globalSettings                                    = get_option( 'ced_tokopedia_global_settings', array() );
			$tokopediaShopGlobalSettings                       = isset( $globalSettings[ $tokopediaStoreId ] ) ? $globalSettings[ $tokopediaStoreId ] : array();
			$profileData                                       = array();
			$profileData['_umb_tokopedia_category']['default'] = $tokopediaCategoryId;
			$profileData['_umb_tokopedia_category']['metakey'] = null;
			$profileData['_ced_tokopedia_manufacturer']['default'] = null;
			$profileData['_ced_tokopedia_manufacturer']['metakey'] = null;
			$profileData['_ced_tokopedia_markup']['default']       = null;
			$profileData['_ced_tokopedia_markup']['metakey']       = null;
			$who_made = isset( $tokopediaShopGlobalSettings['ced_tokopedia_who_made'] ) ? $tokopediaShopGlobalSettings['ced_tokopedia_who_made'] : '';
			$profileData['_ced_tokopedia_who_made']['default']     = $who_made;
			$profileData['_ced_tokopedia_who_made']['metakey']     = null;
			$profileData['_ced_tokopedia_shop_section']['default'] = null;
			$profileData['_ced_tokopedia_shop_section']['metakey'] = null;
			$profileData['_ced_tokopedia_tags']['default']         = null;
			$profileData['_ced_tokopedia_tags']['metakey']         = null;
			$profileData['_ced_tokopedia_materials']['default']    = null;
			$profileData['_ced_tokopedia_materials']['metakey']    = null;
			$profileData['_ced_tokopedia_recipient']['default']    = null;
			$profileData['_ced_tokopedia_recipient']['metakey']    = null;
			$profileData['_ced_tokopedia_occasion']['default']     = null;
			$profileData['_ced_tokopedia_occasion']['metakey']     = null;
			$profileData['_ced_tokopedia_is_supply']['default']    = isset( $tokopediaShopGlobalSettings['ced_tokopedia_product_supply'] ) ? $tokopediaShopGlobalSettings['ced_tokopedia_product_supply'] : '';
			$profileData['_ced_tokopedia_is_supply']['metakey']    = null;
			$profileData['_ced_tokopedia_when_made']['default']    = isset( $tokopediaShopGlobalSettings['ced_tokopedia_manufacturing_year'] ) ? $tokopediaShopGlobalSettings['ced_tokopedia_manufacturing_year'] : '';
			$profileData['_ced_tokopedia_when_made']['metakey']    = null;
			$profileData['_ced_tokopedia_condition']['metakey']    = null;
			return $profileData;
		}


		/**
		 * Tokopedia prepare data for uploading products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpload( $proIDs = array(), $shop_name ) {
			
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->tokopediaProductsInstance->prepareDataForUploading( $proIDs, $shop_name );
			return $response;

		}

		/**
		 * Tokopedia prepare data for updating products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpdate( $proIDs = array(), $shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->tokopediaProductsInstance->prepareDataForUpdating( $proIDs, $shop_name );
			return $response;
		}

		/**
		 * Tokopedia prepare data for updating inventory of products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpdateInventory( $proIDs = array(), $shop_name = '' ) {
			return;
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			if ( empty( $shop_name ) ) {
				$shop_name = get_option( 'ced_tokopedia_active_shop', '' );
			}
			if ( empty( $shop_name ) ) {
				$shop_name = get_option( 'ced_tokopedia_shop_name', '' );
			}
			$response = $this->tokopediaProductsInstance->prepareDataForUpdatingInventory( $proIDs, $shop_name );
			return $response;
		}

		/**
		 * Tokopedia prepare data for updating price of products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpdatePrice( $proIDs = array(), $shop_name = '' ) {

			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->tokopediaProductsInstance->prepareDataForUpdatingPrice( $proIDs, $shop_name );
			return $response;
		}
		/**
		 * Tokopedia prepare data for updating stock
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpdateStock( $proIDs = array(), $shop_name ) {

			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->tokopediaProductsInstance->prepareDataForUpdatingStock( $proIDs, $shop_name );
			return $response;
		}
		/**
		 * Tokopedia prepare data for deleting products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForDelete( $proIDs = array(), $shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->tokopediaProductsInstance->prepareDataForDelete( $proIDs, $shop_name );
			return $response;
		}

		/**
		 * Tokopedia prepare data for deactivating products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForDeactivate( $proIDs = array(), $shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->tokopediaProductsInstance->deactivate_products( $proIDs, $shop_name );
			return $response;
		}

	}
}
