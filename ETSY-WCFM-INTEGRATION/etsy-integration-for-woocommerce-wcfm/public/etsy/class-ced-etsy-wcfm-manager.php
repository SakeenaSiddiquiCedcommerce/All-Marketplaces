<?php
/**
 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce Etsy Integration
 * @subpackage Woocommerce Etsy Integration/marketplaces/etsy
 */

if ( ! class_exists( 'Ced_Etsy_Wcfm_Manager' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce Etsy Integration
	 * @subpackage Woocommerce Etsy Integration/marketplaces/etsy
	 */
	class Ced_Etsy_Wcfm_Manager {

		/**
		 * The Instace of CED_ETSY_etsy_wcfm_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_ETSY_etsy_wcfm_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_ETSY_etsy_wcfm_Manager Instance.
		 *
		 * Ensures only one instance of CED_ETSY_etsy_wcfm_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_ETSY_etsy_wcfm_Manager instance.
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
			ced_etsy_wcfm_include_file(CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/class-ced-etsy-wcfm-products.php');
			$this->etsy_products_instance = Class_Ced_Wcfm_Etsy_Products::get_instance();
			add_action('updated_post_meta',array($this,'ced_etsy_wcfm_update_product_on_etsy'),10,4);
			add_action( 'ced_etsy_wcfm_refresh_token', array( $this, 'ced_etsy_wcfm_refresh_token_action' ),20,4 );
		}

		/**
		 * Refresh Etsy token
		 *
		 * @param string $shop_name
		 * @return void
		 */
		public function ced_etsy_wcfm_refresh_token_action( $shop_name = '',$vendor_id ='' ) {
			if ( null == $vendor_id || '' == $vendor_id || empty( $vendor_id) ) {
				$vendor_id = ced_etsy_wcfm_get_vendor_id();
			}
			if ( ! $shop_name || !$vendor_id || get_transient( 'ced_etsy_wcfm_token_' .$vendor_id. $shop_name ) ) {
				return;
			}
			$user_details      = get_option( 'ced_etsy_wcfm_accounts' , array() );
			if( empty($user_details) ) {
				$user_details = array();
			} else {
				$user_details = json_decode($user_details,true);	
			}

			if (!empty($user_details)) {
				$user_details = isset( $user_details[$vendor_id] ) ? $user_details[$vendor_id] : array();
			}

			if ( ! isset( $user_details[ $shop_name ]['details']['token']['refresh_token'] ) ) {
				$legacy_token = isset( $user_details[ $shop_name ]['access_token']['oauth_token'] ) ? $user_details[ $shop_name ]['access_token']['oauth_token'] : '';
				$query_args   = array(
					'grant_type'   => 'token_exchange',
					'client_id'    => Ced_Etsy_WCFM_API_Request( $shop_name, $vendor_id )->client_id,
					'legacy_token' => $legacy_token,
				);
			} else {
				$refresh_token = isset( $user_details[ $shop_name ]['details']['token']['refresh_token'] ) ? $user_details[ $shop_name ]['details']['token']['refresh_token'] : '';
				$query_args    = array(
					'grant_type'    => 'refresh_token',
					'client_id'     => Ced_Etsy_WCFM_API_Request( $shop_name, $vendor_id )->client_id,
					'refresh_token' => $refresh_token,
				);

			}

			$parameters = $query_args;
			$action     = 'public/oauth/token';
			$response   = Ced_Etsy_WCFM_API_Request( $shop_name, $vendor_id )->post( $action, $parameters, $shop_name, $query_args );
			if ( isset( $response['access_token'] ) && ! empty( $response['access_token'] ) ) {
				$user_details[$shop_name]['details']['token'] = $response;
				if ($vendor_id) {					
					$update_to_vendor[$vendor_id] = $user_details;
					update_option( 'ced_etsy_wcfm_accounts', json_encode( $update_to_vendor ) );
					set_transient( 'ced_etsy_wcfm_token_' .$vendor_id . $shop_name, $response, (int) $response['expires_in'] );
				}
			}

		}

		public function ced_etsy_wcfm_update_product_on_etsy( $meta_id,$product_id,$metakey,$metavalue ) {

			$post_author_id = get_post_field( 'post_author', $product_id );
			if( "_stock" == $metakey ) {
				$activeShops = get_option( 'ced_etsy_active_shops' . $post_author_id , array() );
				if(!empty($activeShops)) {
					foreach ($activeShops as $key => $shop_name) {
						$is_uploaded = get_post_meta( $product_id , '_ced_etsy_wcfm_listing_id_' . $shop_name , true );
						if( $is_uploaded ) {
							$response = $this->prepare_product_for_update_inventory( array( $product_id ), $shop_name, true );
						}
					}
				}
			}
		}


		public function ced_etsy_wcfm_create_auto_profiles( $etsy_wcfm_mapped_categories = array(), $etsy_wcfm_mapped_categories_name = array(), $etsy_wcfm_store_id = '' ) {
			global $wpdb;
			$woo_store_categories           = get_terms( 'product_cat' );
			$already_mapped_categories      = get_option( 'ced_woo_etsy_wcfm_mapped_categories' . $etsy_wcfm_store_id, array() );
			

			$already_mapped_categories_name = get_option( 'ced_woo_etsy_wcfm_mapped_categories_name' . $etsy_wcfm_store_id, array() );
			
			
			if ( ! empty( $etsy_wcfm_mapped_categories ) ) {

				foreach ( $etsy_wcfm_mapped_categories as $key => $value ) {
					$profile_id = '';
					$profile_already_created = get_term_meta( $key, 'ced_etsy_wcfm_profile_created_' . $etsy_wcfm_store_id, true );
					$created_profile_id      = get_term_meta( $key, 'ced_etsy_wcfm_profile_id_' . $etsy_wcfm_store_id, true );
					
					if ( 'yes' == $profile_already_created && ! empty( $created_profile_id ) ) {
						$new_profile_need_to_be_created = $this->check_if_new_profile_need_to_be_created( $key, $value, $etsy_wcfm_store_id );

						if ( ! $new_profile_need_to_be_created ) {
							continue;
						} else {
							$this->reset_mapped_category_data( $key, $value, $etsy_wcfm_store_id );
						}
					}

					$woo_categories      = array();
					$category_attributes = array();
					$profile_name = isset( $etsy_wcfm_mapped_categories_name[ $value ] ) ? $etsy_wcfm_mapped_categories_name[ $value ] : 'Profile for Etsy - Category Id : ' . $value;

					$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details'.$etsy_wcfm_store_id, array() );
					$traverse_this              = true;
					if(isset($ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'])) {
						foreach ( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'] as $id => $data ) {
							if ( $profile_name == $data['profile_name'] && $traverse_this ) {
								$profile_id    = $id;
								$traverse_this = false;
							}
						}
					}

					if ( empty( $profile_id ) ) {
						$is_active = 1;

						$marketplace_name = 'Etsy';

						foreach ( $etsy_wcfm_mapped_categories as $key1 => $value1 ) {
							if ( $value1 == $value ) {
								$woo_categories[] = $key1;
							}
						}

						$profile_data = array();
						$profile_data = $this->ced_etsy_wcfm_prepare_profile_data( $etsy_wcfm_store_id, $value );
						$profile_details = array(
							'profile_name'   => $profile_name,
							'profile_status' => 'active',
							'profile_data'   => json_encode( $profile_data ),
							'shop_id'        => $etsy_wcfm_store_id,
							'woo_categories' => json_encode( $woo_categories ),
						);
						$profile_id      = $this->insert_etsy_wcfm_profile( $profile_details , $etsy_wcfm_store_id);
						$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details'.$etsy_wcfm_store_id, array() );
						$ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ]['profile_id'] = $profile_id;
						update_option( 'ced_etsy_wcfm_profile_details'. $etsy_wcfm_store_id, $ced_etsy_wcfm_profile_details );
					} else {
						$woo_categories     = array();
						$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details'.$etsy_wcfm_store_id, array() );
						$woo_categories             = json_decode( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ]['woo_categories'], true );
						$woo_categories[]           = $key;
						$ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ]['woo_categories'] = json_encode( array_unique( $woo_categories ) );
						update_option( 'ced_etsy_wcfm_profile_details' . $etsy_wcfm_store_id, $ced_etsy_wcfm_profile_details );
					}
					foreach ( $woo_categories as $key12 => $value12 ) {
						update_term_meta( $value12, 'ced_etsy_wcfm_profile_created_' . $etsy_wcfm_store_id, 'yes' );
						update_term_meta( $value12, 'ced_etsy_wcfm_profile_id_' . $etsy_wcfm_store_id, $profile_id );
						update_term_meta( $value12, 'ced_etsy_wcfm_mapped_category_' . $etsy_wcfm_store_id, $value );
					}
				}
			}
		}

		public function check_if_new_profile_need_to_be_created( $woo_category_id = '', $etsy_wcfm_category_id = '', $etsy_wcfm_store_id = '' ) {
			$old_etsy_wcfm_category_mapped = get_term_meta( $woo_category_id, 'ced_etsy_wcfm_mapped_category_' . $etsy_wcfm_store_id, true );
			if ( $old_etsy_wcfm_category_mapped == $etsy_wcfm_category_id ) {
				return false;
			} else {
				return true;
			}
		}

		public function reset_mapped_category_data( $woo_category_id = '', $etsy_wcfm_category_id = '', $etsy_wcfm_store_id = '' ) {
			
			update_term_meta( $woo_category_id, 'ced_etsy_wcfm_mapped_category_' . $etsy_wcfm_store_id, $etsy_wcfm_category_id );
			delete_term_meta( $woo_category_id, 'ced_etsy_wcfm_profile_created_' . $etsy_wcfm_store_id );
			$created_profile_id = get_term_meta( $woo_category_id, 'ced_etsy_wcfm_profile_id_' . $etsy_wcfm_store_id, true );
			
			delete_term_meta( $woo_category_id, 'ced_etsy_wcfm_profile_id_' . $etsy_wcfm_store_id );
			$this->remove_category_mapping_from_profile( $created_profile_id, $woo_category_id , $etsy_wcfm_store_id);
		}

		public function remove_category_mapping_from_profile( $created_profile_id = '', $woo_category_id = '' , $etsy_wcfm_store_id) {

			$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details' . $etsy_wcfm_store_id, array() );
			$woo_categories             = isset( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $created_profile_id ]['woo_categories'] ) ? json_decode($ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $created_profile_id ]['woo_categories'] ,true) : array();

			if ( is_array( $woo_categories ) && ! empty( $woo_categories ) ) {
				$categories = array();
				foreach ( $woo_categories as $key => $value ) {
					if ( $value != $woo_category_id ) {
						$categories[] = $value;
					}
				}
				$categories = json_encode( $categories );
				$ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $created_profile_id ]['woo_categories'] = $categories;
				update_option( 'ced_etsy_wcfm_profile_details' . $etsy_wcfm_store_id , $ced_etsy_wcfm_profile_details );
			}
		}

		public function ced_etsy_wcfm_prepare_profile_data( $etsy_wcfm_store_id, $etsy_wcfm_category_id ) {
			$profile_data                                    = array();
			$ced_etsy_wcfm_global_settings   = get_option( 'ced_etsy_wcfm_global_settings', '' );
			if( empty($ced_etsy_wcfm_global_settings) ) {
				$ced_etsy_wcfm_global_settings = array();
			} else {
				$ced_etsy_wcfm_global_settings = json_decode($ced_etsy_wcfm_global_settings,true);	
			}
			$data                 = isset($ced_etsy_wcfm_global_settings[ ced_etsy_wcfm_get_vendor_id() ][ $etsy_wcfm_store_id ]) ? $ced_etsy_wcfm_global_settings[ ced_etsy_wcfm_get_vendor_id() ][ $etsy_wcfm_store_id ] : array();

			$fields_file = CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-product-fields.php';
			ced_etsy_wcfm_include_file($fields_file);
			$product_field_instance = Ced_Etsy_Wcfm_Product_Fields::get_instance();
			$product_fields = $product_field_instance->get_etsy_wcfm_custom_products_fields();
			foreach ($product_fields as $fields_data ) {
				$id = $fields_data['fields']['id'];
				$profile_data[$id]['default'] = isset($data[$id]['default']) ? $data[$id]['default'] : '';
				$profile_data[$id]['metakey'] = isset($data[$id]['metakey']) ? $data[$id]['metakey'] : '';
			}
			$profile_data['_umb_etsy_wcfm_category']['default'] = (int)$etsy_wcfm_category_id;
			$profile_data['_umb_etsy_wcfm_category']['metakey'] = null;
			return $profile_data;
		}

		public function insert_etsy_wcfm_profile( $profile_details , $etsy_wcfm_store_id) {
			$ced_etsy_wcfm_profile_details                                 = get_option( 'ced_etsy_wcfm_profile_details' . $etsy_wcfm_store_id, array() );

			$count                       = get_option( 'ced_etsy_wcfm_profile_counts' . $etsy_wcfm_store_id, '' );
			if ( empty( $count ) ) {
				$count = 0;
			}
			$count ++;

			$ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $count ] = $profile_details;
			update_option( 'ced_etsy_wcfm_profile_details' . $etsy_wcfm_store_id, $ced_etsy_wcfm_profile_details );
			update_option( 'ced_etsy_wcfm_profile_counts' . $etsy_wcfm_store_id, $count );
			return $count;
		}

		/**
		 * Etsy prepare data for uploading products
		 *
		 * @since    1.0.0
		 */
		public function prepare_product_for_upload( $proIDs = array(), $shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsy_products_instance->ced_etsy_upload_product_to_etsy( $proIDs, $shop_name );
			return $response;

		}

		/**
		 * Etsy prepare data for updating products
		 *
		 * @since    1.0.0
		 */
		public function prepare_product_for_update( $proIDs = array(), $shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsy_products_instance->ced_etsy_update_product_on_etsy( $proIDs, $shop_name );
			return $response;
		}

		/**
		 * Etsy prepare data for updating inventory of products
		 *
		 * @since    1.0.0
		 */
		public function prepare_product_for_update_inventory( $proIDs = array(), $shop_name = '', $is_sync = false ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			if ( empty( $shop_name ) ) {
				$shop_name = get_option( 'ced_etsy_active_shop', '' );
			}
			if ( empty( $shop_name ) ) {
				$shop_name = get_option( 'ced_etsy_shop_name', '' );
			}
			$response = $this->etsy_products_instance->ced_etsy_update_inventory_to_etsy( $proIDs, $shop_name, $is_sync );
			return $response;
		}

		/**
		 * Etsy prepare data for deleting products
		 *
		 * @since    1.0.0
		 */
		public function prepare_product_for_delete( $proIDs = array(), $shop_name ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->etsy_products_instance->ced_etsy_delete_product( $proIDs, $shop_name );
			return $response;
		}
		
	}
}