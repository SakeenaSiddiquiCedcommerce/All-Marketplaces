<?php
/**
 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce Etsy Integration
 * @subpackage Woocommerce Etsy Integration/marketplaces/etsy
 */

if ( ! class_exists( 'CED_ETSY_Dokan_Manager' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce Etsy Integration
	 * @subpackage Woocommerce Etsy Integration/marketplaces/etsy
	 */
	class CED_ETSY_Dokan_Manager {

		/**
		 * The Instace of CED_ETSY_etsy_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_ETSY_etsy_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_ETSY_etsy_Manager Instance.
		 *
		 * Ensures only one instance of CED_ETSY_etsy_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_ETSY_etsy_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'etsy';
		public $marketplaceName = 'Etsy';


		/**
		 * Constructor.
		 *
		 * Registering actions and hooks for etsy.
		 *
		 * @since 1.0.0
		 */

		public function __construct() {

			$this->loadDependency();
			add_action( 'ced_etsy_additional_configuration', array( $this, 'ced_etsy_dokan_additional_shipping_configuration' ), 10, 2 );
			add_action( 'ced_etsy_additional_configuration', array( $this, 'ced_etsy_dokan_additional_payment_configuration' ), 11, 2 );
			add_action( 'ced_etsy_additional_configuration', array( $this, 'ced_etsy_dokan_additional_shop_section_configuration' ), 12, 2 );
			add_action( 'woocommerce_thankyou', array( $this, 'ced_etsy_dokan_update_inventory_on_order_creation' ), 10, 1 );
			add_action( 'admin_init', array( $this, 'ced_etsy_dokan_schedules' ) );
			add_filter( 'woocommerce_duplicate_product_exclude_meta', array( $this, 'ced_etsy_dokan_woocommerce_duplicate_product_exclude_meta' ) );
			// add_action( 'updated_post_meta', array( $this, 'ced_relatime_sync_inventory_to_etsy' ), 12, 4 );
			add_filter( 'woocommerce_order_number', array( $this, 'ced_modify_woo_order_number' ), 20, 2 );
			add_action( 'ced_etsy_auto_submit_shipment', array( $this, 'ced_etsy_dokan_auto_submit_shipment' ) );
// 			add_action( 'admin_notices', array( $this, 'ced_etsy_admin_notices' ) );
			//add_action( 'init', array( $this, 'ced_user' ) );
			add_filter('dokan_product_row_actions',array($this,'ced_etsy_dokan_modify_row_actions') , 10 , 2 );
			add_action( 'ced_etsy_dokan_refresh_token', array( $this, 'ced_etsy_dokan_refresh_token_action' ),22, 2 );
			
		}


		/**
		 * Refresh Etsy token
		 *
		 * @param string $shop_name
		 * @return void
		 */
		public function ced_etsy_dokan_refresh_token_action( $shop_name = '', $vendor_id='' ) {
			if (empty($vendor_id)) {
				$vendor_id = get_current_user_id();
			}
			if ( ! $shop_name || get_transient( 'ced_etsy_dokan_token_' . $shop_name . $vendor_id ) ) {
				return;
			}
			$user_details  = get_option( 'ced_etsy_dokan_details', array() );
			$refresh_token = isset( $user_details[$vendor_id][$shop_name]['details']['token']['refresh_token'] ) ? $user_details[$vendor_id][$shop_name]['details']['token']['refresh_token'] : '';
			$query_args = array(
				'grant_type'    => 'refresh_token',
				'client_id'     => ced_etsy_dokan_request()->client_id,
				'refresh_token' => $refresh_token,
			);
			$parameters = $query_args;
			$action     = 'public/oauth/token';
			$response   = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, $action, 'POST', $parameters, $query_args );
			if ( isset( $response['access_token'] ) && ! empty( $response['access_token'] ) ) {
				$user_details[$vendor_id][ $shop_name ]['details']['token'] = $response;
				update_option( 'ced_etsy_dokan_details', $user_details );
				set_transient( 'ced_etsy_dokan_token_' . $shop_name . $vendor_id, $response, (int) $response['expires_in'] );
			}

		}


		public function ced_etsy_dokan_modify_row_actions( $row_action , $post) {
			$post_id = $post->ID;
			$etsy_link = get_post_meta( $post_id , '_CED_ETSY_DOKAN_URL_' . get_etsy_de_shop_name() , true );
			if(!empty($etsy_link)) {
				$row_action['view-etsy']['title'] = 'View on Etsy'; 
				$row_action['view-etsy']['url'] = 	$etsy_link;
				$row_action['view-etsy']['class'] = 'view'; 
			}
			ksort($row_action);
			return $row_action;
		}


		public function ced_etsy_admin_notices() {

			if ( isset( $_GET['page'] ) && 'ced_etsy' == $_GET['page'] ) {
				$data = file_get_contents( 'https://demo.cedcommerce.com/woocommerce/marketplace_notice.php?source=etsy' );
				if ( ! empty( $data ) ) {
					?>
					<div class="cedcommerce_important_notice_wrapper">
						<div class="cedcommerce_important_notice_container">
							<?php print_r( $data ); ?>
						</div>
					</div>
					<?php
				}

				$url = 'https://cedcommerce.com/contacts';
				echo "<div class='notice notice-success is-dismissible'><p><i><a>NOTICE</a> : Thank you for choosing <b><i>Etsy Integration for WooCommerce</i></b> . If you have any questions or need any assistance regarding the plugin feel free to contact us using the chat icon at the bottom or <a href='" . esc_url( $url ) . "' target='_blank'>here</a> .</i></p></div>";
			}
		}

		public function ced_etsy_dokan_auto_submit_shipment() {
			$etsy_orders = get_posts(
				array(
					'numberposts' => -1,
					'meta_key'    => '_etsy_dokan_umb_order_status',
					'meta_value'  => 'Fetched',
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
					'orderby'     => 'date',
					'order'       => 'DESC',
					'fields'      => 'ids',
				)
			);
			if ( ! empty( $etsy_orders ) && is_array( $etsy_orders ) ) {
				foreach ( $etsy_orders as $woo_order_id ) {
					$this->ced_etsy_auto_ship_order( $woo_order_id );
				}
			}
		}

		public function ced_etsy_auto_ship_order( $woo_order_id = 0 ) {

			$_etsy_dokan_umb_order_status = get_post_meta( $woo_order_id, '_etsy_dokan_umb_order_status', true );
			if ( empty( $_etsy_dokan_umb_order_status ) || 'Fetched' != $_etsy_dokan_umb_order_status ) {
				return;
			}

			$tracking_no      = '';
			$tracking_code    = '';
			$tracking_details = get_post_meta( $woo_order_id, '_wc_shipment_tracking_items', true );
			if ( ! empty( $tracking_details ) ) {
				$tracking_code = isset( $tracking_details[0]['custom_tracking_provider'] ) ? $tracking_details[0]['custom_tracking_provider'] : '';
				if ( empty( $tracking_code ) ) {
					$tracking_code = isset( $tracking_details[0]['tracking_provider'] ) ? $tracking_details[0]['tracking_provider'] : '';
				}
				$tracking_no = isset( $tracking_details[0]['tracking_number'] ) ? $tracking_details[0]['tracking_number'] : '';
				if ( ! empty( $tracking_no ) && ! empty( $tracking_code ) ) {
					$shop_name          = get_post_meta( $woo_order_id, 'ced_etsy_order_shop_id', true );
					$_ced_etsy_order_id = get_post_meta( $woo_order_id, '_ced_etsy_order_id', true );
					$params             = array(
						'tracking_code' => $tracking_no,
						'carrier_name'  => $tracking_code,
					);
					$vendor_id      = get_current_user_id();
					$shop_id 		= ced_etsy_dokan_get_shop_id( $shop_name, $vendor_id );
					do_action( 'ced_etsy_dokan_refresh_token', $shop_name, $vendor_id );
					$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, "application/shops/{$shop_id}/receipts/$_ced_etsy_order_id/tracking", 'POST', array(), $params, $vendor_id );
					if ( isset( $response['results'][0] ) || isset( $response['Shipping_notification_email_has_already_been_sent_for_this_receipt_'] ) ) {
						update_post_meta( $woo_order_id, '_etsy_dokan_umb_order_status', 'Shipped' );
					}
				}
			}

		}


		public function ced_modify_woo_order_number( $order_id, $order ) {
			$_ced_etsy_order_id     = get_post_meta( $order->get_id(), '_ced_etsy_order_id', true );
			$ced_etsy_order_shop_id = get_post_meta( $order->get_id(), 'ced_etsy_order_shop_id', true );

			$renderDataOnGlobalSettings = get_option( 'ced_etsy_dokan_global_settings', array() );
			$use_etsy_order_no          = isset( $renderDataOnGlobalSettings[ $ced_etsy_order_shop_id ]['use_etsy_order_no'] ) ? $renderDataOnGlobalSettings[ $ced_etsy_order_shop_id ]['use_etsy_order_no'] : '';

			if ( ! empty( $_ced_etsy_order_id ) && 'on' == $use_etsy_order_no ) {
				return $_ced_etsy_order_id;
			}

			return $order_id;

		}

		public function ced_etsy_dokan_woocommerce_duplicate_product_exclude_meta( $metakeys = array() ) {
			$de_shop_name  = get_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id() , '' );
			$metakeys[] = '_ced_etsy_listing_id_' . $de_shop_name;
			return $metakeys;
		}

		public function ced_etsy_dokan_schedules() {
			if ( isset( $_GET['de_shop_name'] ) && ! empty( $_GET['de_shop_name'] ) ) {
				$de_shop_name = sanitize_text_field( $_GET['de_shop_name'] );
				if ( ! wp_get_schedule( 'ced_etsy_sync_existing_products_job_' . $de_shop_name ) ) {
					wp_schedule_event( time(), 'ced_etsy_6min', 'ced_etsy_sync_existing_products_job_' . $de_shop_name );
				}

				$renderDataOnGlobalSettings   = get_option( 'ced_etsy_dokan_global_settings', array() );
				$update_tracking              = isset( $renderDataOnGlobalSettings[ $de_shop_name ]['update_tracking'] ) ? $renderDataOnGlobalSettings[ $de_shop_name ]['update_tracking'] : '';
				$ced_etsy_auto_upload_product = isset( $renderDataOnGlobalSettings[ $de_shop_name ]['ced_etsy_auto_upload_product'] ) ? $renderDataOnGlobalSettings[ $de_shop_name ]['ced_etsy_auto_upload_product'] : '';
				if ( ! wp_get_schedule( 'ced_etsy_auto_upload_products_' . $de_shop_name ) && 'on' == $ced_etsy_auto_upload_product ) {
					wp_schedule_event( time(), 'ced_etsy_20min', 'ced_etsy_auto_upload_products_' . $de_shop_name );
				} else {
					wp_clear_scheduled_hook( 'ced_etsy_auto_upload_products_' . $de_shop_name );
				}

				if ( ! wp_get_schedule( 'ced_etsy_auto_submit_shipment' ) && 'on' == $update_tracking ) {
					wp_schedule_event( time(), 'ced_etsy_30min', 'ced_etsy_auto_submit_shipment' );
				} else {
					wp_clear_scheduled_hook( 'ced_etsy_auto_submit_shipment' );
				}
			}
		}

		/**
		 * ******************************************************
		 * Real time Sync product form Wooocommerce to Etsy shop.
		 * ******************************************************
		 *
		 * @param $meta_id    Udpated product meta meta_id of the product.
		 * @param $product_id Updated meta value of the product id.
		 * @param $meta_key   Update products meta key.
		 * @param $mta_value  Udpated changed meta value of the post.
		 */
		public function ced_relatime_sync_inventory_to_etsy( $meta_id, $product_id, $meta_key, $meta_value ) {

			// If tha is changed by _stock only.
			if ( '_stock' == $meta_key ) {
				// Active shop name
				$de_shop_name = get_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id() , '' );

				$_product = wc_get_product( $product_id );
				if ( ! wp_get_schedule( 'ced_etsy_inventory_scheduler_job_' . $de_shop_name ) || ! is_object( $_product ) ) {
					return;
				}
				// All products by product id
				// check if it has variations.
				if ( $_product->get_type() == 'variation' ) {
					$product_id = $_product->get_parent_id();
				}
				/**
				 * *******************************************
				 *   CALLING FUNCTION TO UDPATE THE INVENTORY
				 * *******************************************
				 */
				$this->prepareProductHtmlForUpdateInventory( array( $product_id ), $de_shop_name, $is_sync );
			}
		}

		public function ced_etsy_dokan_update_inventory_on_order_creation( $order_id ) {
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
		 * Etsy Loading dependencies
		 *
		 * @since    1.0.0
		 */
		public function loadDependency() {
			if ( session_status() == PHP_SESSION_NONE ) {
				session_start();
			}

			$fileProducts = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/etsyDokanProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}

			$this->etsyProductsInstance = Class_Ced_Etsy_Dokan_Products::get_instance();
		}

		/**
		 * Function to load shipping template
		 *
		 * @since    1.0.0
		 */
		public function ced_etsy_dokan_additional_shipping_configuration( $marketPlaceName = 'etsy', $de_shop_name ) {
			echo '<div class="ced_etsy_shippingConfig">';
			require_once CED_ETSY_DOKAN_DIRPATH . 'public/pages/shipping-config.php';
			echo '</div>';
		}

		/**
		 * Function to load payment template
		 *
		 * @since    1.0.0
		 */
		public function ced_etsy_dokan_additional_payment_configuration( $marketPlaceName = 'etsy', $de_shop_name ) {
			echo '<div class="ced_etsy_paymentConfig">';
			require_once CED_ETSY_DOKAN_DIRPATH . 'public/pages/payment-config.php';
			echo '</div>';
		}
		/**
		 * Function to load shop section template
		 *
		 * @since    1.0.0
		 */

		public function ced_etsy_dokan_additional_shop_section_configuration( $marketPlaceName = 'etsy', $de_shop_name ) {
			echo '<div class="ced_etsy_shpSectionConfig">';
			require_once CED_ETSY_DOKAN_DIRPATH . 'public/pages/shop-section-config.php';
			echo '</div>';
		}

		/**
		 * Ced Etsy Fetch Categories
		 *
		 * @since    1.0.0
		 */
		public function ced_etsy_get_categories() {
			$file = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/etsyDokanCategory.php';
			if ( ! file_exists( $file ) ) {
				return;
			}
			require_once $file;
			$etsyCategoryInstance = Class_Ced_Etsy_Category::get_instance();
			$fetchedCategories    = $etsyCategoryInstance->getEtsyCategories();
			$categories           = $this->StoreCategories( $fetchedCategories );

		}

		/**
		 * Etsy Storing Categories
		 *
		 * @since    1.0.0
		 */
		public function StoreCategories( $fetchedCategories, $ajax = '' ) {
			foreach ( $fetchedCategories['results'] as $key => $value ) {
				if ( count( $value['children_ids'] ) > 0 ) {
					$arr1[] = array(
						'id'       => $value['id'],
						'name'     => $value['name'],
						'path'     => $value['path'],
						'children' => count( $value['children_ids'] ),
					);
				} else {
					$arr1[] = array(
						'id'       => $value['id'],
						'name'     => $value['name'],
						'path'     => $value['path'],
						'children' => 0,
					);
				}
				foreach ( $value['children'] as $key1 => $value1 ) {
					if ( count( $value1['children_ids'] ) > 0 ) {
						$arr2[] = array(
							'parent_id' => $value['id'],
							'id'        => $value1['id'],
							'name'      => $value1['name'],
							'path'      => $value1['path'],
							'children'  => count( $value1['children_ids'] ),
						);
					} else {
						$arr2[] = array(
							'parent_id' => $value['id'],
							'id'        => $value1['id'],
							'name'      => $value1['name'],
							'path'      => $value1['path'],
							'children'  => 0,
						);
					}
					foreach ( $value1['children'] as $key2 => $value2 ) {
						if ( count( $value2['children_ids'] ) > 0 ) {
							$arr3[] = array(
								'parent_id' => $value1['id'],
								'id'        => $value2['id'],
								'name'      => $value2['name'],
								'path'      => $value2['path'],
								'children'  => count( $value2['children_ids'] ),
							);
						} else {
							$arr3[] = array(
								'parent_id' => $value1['id'],
								'id'        => $value2['id'],
								'name'      => $value2['name'],
								'path'      => $value2['path'],
								'children'  => 0,
							);
						}
						foreach ( $value2['children'] as $key3 => $value3 ) {
							if ( count( $value3['children_ids'] ) > 0 ) {
								$arr4[] = array(
									'parent_id' => $value2['id'],
									'id'        => $value3['id'],
									'name'      => $value3['name'],
									'path'      => $value3['path'],
									'children'  => count( $value3['children_ids'] ),
								);
							} else {
								$arr4[] = array(
									'parent_id' => $value2['id'],
									'id'        => $value3['id'],
									'name'      => $value3['name'],
									'path'      => $value3['path'],
									'children'  => 0,
								);
							}
							foreach ( $value3['children'] as $key4 => $value4 ) {
								if ( count( $value4['children_ids'] ) > 0 ) {
									$arr5[] = array(
										'parent_id' => $value3['id'],
										'id'        => $value4['id'],
										'name'      => $value4['name'],
										'path'      => $value4['path'],
										'children'  => count( $value4['children_ids'] ),
									);
								} else {
									$arr5[] = array(
										'parent_id' => $value3['id'],
										'id'        => $value4['id'],
										'name'      => $value4['name'],
										'path'      => $value4['path'],
										'children'  => 0,
									);
								}
								foreach ( $value4['children'] as $key5 => $value5 ) {
									if ( count( $value5['children_ids'] ) > 0 ) {
										$arr6[] = array(
											'parent_id' => $value4['id'],
											'id'        => $value5['id'],
											'name'      => $value5['name'],
											'path'      => $value5['path'],
											'children'  => count( $value5['children_ids'] ),
										);
									} else {
										$arr6[] = array(
											'parent_id' => $value4['id'],
											'id'        => $value5['id'],
											'name'      => $value5['name'],
											'path'      => $value5['path'],
											'children'  => 0,
										);
									}
									foreach ( $value5['children'] as $key6 => $value6 ) {
										if ( is_array( $value6['children_ids'] ) && ! empty( $value6['children_ids'] ) ) {

											$arr7[] = array(
												'parent_id' => $value5['id'],
												'id'       => $value6['id'],
												'name'     => $value6['name'],
												'path'     => $value6['path'],
												'children' => count( $value6['children_ids'] ),
											);

										} else {
											$arr7[] = array(
												'parent_id' => $value5['id'],
												'id'       => $value6['id'],
												'name'     => $value6['name'],
												'path'     => $value6['path'],
												'children' => 0,
											);
										}
									}
								}
							}
						}
					}
				}
			}

			$folderName = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/json/';

			$catFirstLevelFile = $folderName . 'categoryLevel-1.json';
			file_put_contents( $catFirstLevelFile, json_encode( $arr1 ) );
			$catSecondLevelFile = $folderName . 'categoryLevel-2.json';
			file_put_contents( $catSecondLevelFile, json_encode( $arr2 ) );

			$catThirdLevelFile = $folderName . 'categoryLevel-3.json';
			file_put_contents( $catThirdLevelFile, json_encode( $arr3 ) );
			$catFourthLevelFile = $folderName . 'categoryLevel-4.json';
			file_put_contents( $catFourthLevelFile, json_encode( $arr4 ) );

			$catFifthLevelFile = $folderName . 'categoryLevel-5.json';
			file_put_contents( $catFifthLevelFile, json_encode( $arr5 ) );
			$catSixthLevelFile = $folderName . 'categoryLevel-6.json';
			file_put_contents( $catSixthLevelFile, json_encode( $arr6 ) );

			$catSeventhLevelFile = $folderName . 'categoryLevel-7.json';
			file_put_contents( $catSeventhLevelFile, json_encode( $arr7 ) );

			update_option( 'ced_etsy_categories_fetched', 'Yes' );
			if ( $ajax ) {
				return 'true';
				die;
			}
		}
		// function ced_user(){
		// 	echo "hello";
		// 	$vendor_id = get_current_user_id();
		// 	var_dump($vendor_id);
		// 	die();


		// }

		/**
		 * Etsy Create Auto Profiles
		 *
		 * @since    1.0.0
		 */
		public function ced_etsy_createAutoProfiles( $etsyMappedCategories = array(), $etsyMappedCategoriesName = array(), $etsyStoreId = '' ) {
			
			global $wpdb;
			$wooStoreCategories          = get_terms( 'product_cat' );
			$alreadyMappedCategories     = get_option( 'ced_dokan_etsy_mapped_categories_' . $etsyStoreId .'_'.get_current_user_id(), array() );
			$alreadyMappedCategoriesName = get_option( 'ced_dokan_etsy_mapped_categories_name_' . $etsyStoreId.'_'.get_current_user_id(), array() );
			$vendor_id                   = get_current_user_id();
			//var_dump(get_current_user_id());


			if ( ! empty( $etsyMappedCategories ) ) {
				foreach ( $etsyMappedCategories as $key => $value ) {
					$profileAlreadyCreated = get_term_meta( $key, 'ced_etsy_dokan_profile_created_' . $etsyStoreId.'_'.$vendor_id, true );
					$createdProfileId      = get_term_meta( $key, 'ced_etsy_dokan_profile_id_' . $etsyStoreId.'_'.$vendor_id, true );
					if ( ! empty( $profileAlreadyCreated ) && 'yes' == $createdProfileId ) {
						$newProfileNeedToBeCreated = $this->checkIfNewProfileNeedToBeCreated( $key, $value, $etsyStoreId );

						if ( ! $newProfileNeedToBeCreated ) {
							continue;
						} else {
							$this->resetMappedCategoryData( $key, $value, $etsyStoreId );
						}
					}

					$wooCategories      = array();
					$categoryAttributes = array();
					$profileName = isset( $etsyMappedCategoriesName[ $value ] ) ? $etsyMappedCategoriesName[ $value ] : 'Profile for etsy - Category Id : ' . $value;
					
					$profile_id = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `profile_name` = %s AND `de_shop_name`=%s AND `vendor_id`=%d ", $profileName, $etsyStoreId,$vendor_id ), 'ARRAY_A' );

					if ( ! isset( $profile_id[0]['id'] ) && empty( $profile_id[0]['id'] ) ) {
						$is_active       = 1;
						$marketplaceName = 'etsy';
						foreach ( $etsyMappedCategories as $key1 => $value1 ) {
							if ( $value1 == $value ) {
								$wooCategories[] = $key1;
							}
						}

						$profileData    = array();
						$profileData    = $this->prepareProfileData( $etsyStoreId, $value, $wooCategories );
						$profileDetails = array(
							'profile_name'   => $profileName,
							'profile_status' => 'active',
							'de_shop_name'      => $etsyStoreId,
							'vendor_id'      => $vendor_id,
							'profile_data'   => json_encode( $profileData ),
							'woo_categories' => json_encode( $wooCategories ),
						);
						$profileId      = $this->insertetsyProfile( $profileDetails );
					} else {
						$wooCategories      = array();
						$profileId          = $profile_id[0]['id'];
						$profile_categories = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `id` = %d ", $profileId ), 'ARRAY_A' );
						$wooCategories      = json_decode( $profile_categories[0]['woo_categories'], true );
						$wooCategories[]    = $key;
						$table_name         = $wpdb->prefix . 'ced_etsy_dokan_profiles';
						$wpdb->update(
							$table_name,
							array(
								'woo_categories' => json_encode( array_unique( $wooCategories ) ),
							),
							array( 'id' => $profileId )
						);
					}
					foreach ( $wooCategories as $key12 => $value12 ) {
						update_term_meta( $value12, 'ced_etsy_dokan_profile_created_' . $etsyStoreId.'_'.get_current_user_id(), 'yes' );
						update_term_meta( $value12, 'ced_etsy_dokan_profile_id_' . $etsyStoreId.'_'.get_current_user_id(), $profileId );
						update_term_meta( $value12, 'ced_etsy_dokan_mapped_category_' . $etsyStoreId.'_'.get_current_user_id(), $value );
					}
				}
			}
		}

		/**
		 * Etsy Insert Profiles In database
		 *
		 * @since    1.0.0
		 */
		public function insertetsyProfile( $profileDetails ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_etsy_dokan_profiles';
			$wpdb->insert( $profileTableName, $profileDetails );
			$profileId = $wpdb->insert_id;
			return $profileId;
		}

		/**
		 * Etsy Check if Profile Need to be Created
		 *
		 * @since    1.0.0
		 */
		public function checkIfNewProfileNeedToBeCreated( $wooCategoryId = '', $etsyCategoryId = '', $etsyStoreId = '' ) {

			$oldetsyCategoryMapped = get_term_meta( $wooCategoryId, 'ced_etsy_dokan_mapped_category_' . $etsyStoreId .'_'.get_current_user_id(), true );
			if ( $oldetsyCategoryMapped == $etsyCategoryId ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Etsy Update Mapped Category data
		 *
		 * @since    1.0.0
		 */
		public function resetMappedCategoryData( $wooCategoryId = '', $etsyCategoryId = '', $etsyStoreId = '' ) {

			update_term_meta( $wooCategoryId, 'ced_etsy_dokan_mapped_category_' . $etsyStoreId . '_' . get_current_user_id() , $etsyCategoryId );
			delete_term_meta( $wooCategoryId, 'ced_etsy_dokan_profile_created_' . $etsyStoreId . '_' . get_current_user_id() );
			$createdProfileId = get_term_meta( $wooCategoryId, 'ced_etsy_dokan_profile_id_' . $etsyStoreId . '_' . get_current_user_id(), true );
			delete_term_meta( $wooCategoryId, 'ced_etsy_dokan_profile_id_' . $etsyStoreId . '_' . get_current_user_id() );
			$this->removeCategoryMappingFromProfile( $createdProfileId, $wooCategoryId );
		}

		/**
		 * Etsy Remove previous mapped profile
		 *
		 * @since    1.0.0
		 */
		public function removeCategoryMappingFromProfile( $createdProfileId = '', $wooCategoryId = '' ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_etsy_dokan_profiles';
			$profile_data     = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `id`=%s ", $createdProfileId ), 'ARRAY_A' );

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
		 * Etsy Prepare Profile data
		 *
		 * @since    1.0.0
		 */
		public function prepareProfileData( $etsyStoreId, $etsyCategoryId, $wooCategories = '' ) {

			$globalSettings         = get_option( 'ced_etsy_dokan_global_settings', array() );
			$etsyShopGlobalSettings = isset( $globalSettings[get_current_user_id()][ $etsyStoreId ] ) ? $globalSettings[get_current_user_id()][ $etsyStoreId] : array();
			$shipping_templates = get_option( 'ced_etsy_dokan_details', true );
			
			$selected_shipping_template = isset( $shipping_templates[get_current_user_id()][ $etsyStoreId ]['shippingTemplateId'] ) ? $shipping_templates[get_current_user_id()][ $etsyStoreId ]['shippingTemplateId'] : null;
			$profileData            = array();
			$profileData['_umb_etsy_category']['default'] = $etsyCategoryId;
			$profileData['_umb_etsy_category']['metakey'] = null;
			$profileData['_ced_etsy_shipping_profile']['default'] = $selected_shipping_template;
			$profileData['_ced_etsy_shipping_profile']['metakey'] = null;

			foreach ( $etsyShopGlobalSettings['product_data'] as $key => $value ) {
				$profileData[ $key ]['default'] = isset( $value['default'] ) ? $value['default'] : '';
				$profileData[ $key ]['metakey'] = isset( $value['metakey'] ) ? $value['metakey'] : '';

			}
			return $profileData;
		}


		/**
		 * Etsy prepare data for uploading products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpload( $proIDs = array(), $de_shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsyProductsInstance->prepareDataForUploadingProduct( $proIDs, $de_shop_name );
			return $response;

		}

		/**
		 * Etsy prepare data for updating products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpdate( $proIDs = array(), $de_shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsyProductsInstance->prepareDokanDataForUpdating( $proIDs, $de_shop_name );
			return $response;
		}

		/**
		 * Etsy prepare data for updating inventory of products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForUpdateInventory( $proIDs = array(), $de_shop_name = '', $is_sync = false ) {

			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}

			if ( empty( $de_shop_name ) ) {
				$de_shop_name = get_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id() , '' );
			}
			$response = $this->etsyProductsInstance->prepareDokanDataForUpdatingInventory( $proIDs, $de_shop_name, $is_sync );
			return $response;
		}


		/**
		 * Etsy prepare data for deleting products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForDelete( $proIDs = array(), $de_shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsyProductsInstance->prepareDokanDataForDelete( $proIDs, $de_shop_name );
			return $response;
		}

		/**
		 * Etsy prepare data for deactivating products
		 *
		 * @since    1.0.0
		 */
		public function prepareProductHtmlForDeactivate( $proIDs = array(), $de_shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsyProductsInstance->etsy_dokan_deetsy_dokan_activate_products( $proIDs, $de_shop_name );
			return $response;
		}

		public function ced_update_images_on_etsy_dokan( $proIDs = array(), $de_shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsyProductsInstance->update_images_on_etsy_dokan( $proIDs, $de_shop_name );
			return $response;
		}

	}
}
