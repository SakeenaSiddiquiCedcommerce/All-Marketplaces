<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Etsy_Integration_For_Woocommerce_Wcfm
 * @subpackage Etsy_Integration_For_Woocommerce_Wcfm/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Etsy_Integration_For_Woocommerce_Wcfm
 * @subpackage Etsy_Integration_For_Woocommerce_Wcfm/public
 * @author     CedCommerce <plugins@cedcommerce.com>
 */

class Etsy_Integration_For_Woocommerce_Wcfm_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		session_start();
		ob_start();
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependency();
		ini_set('display_errors', 0);
	}

	function load_dependency() {
		ced_etsy_wcfm_include_file(CED_ETSY_WCFM_DIRPATH . 'public/etsy/class-ced-etsy-wcfm-manager.php');
		$this->ced_etsy_wcfm_manager = Ced_Etsy_Wcfm_Manager::get_instance();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Etsy_Integration_For_Woocommerce_Wcfm_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Etsy_Integration_For_Woocommerce_Wcfm_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/etsy-integration-for-woocommerce-wcfm-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Etsy_Integration_For_Woocommerce_Wcfm_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Etsy_Integration_For_Woocommerce_Wcfm_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/etsy-integration-for-woocommerce-wcfm-public.js', array( 'jquery' ), $this->version, false );
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$ajax_nonce     = wp_create_nonce( 'ced-etsy-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
			'shop_name'  => $shop_name,
		);
		wp_localize_script( $this->plugin_name, 'ced_etsy_wcfm_admin_obj', $localize_array );

	}

	/**
	 * Add the Etsy menu in the vendor dashboard.
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_wcfm_add_main_menu( $wcfm_menus = array() ) {

		if(!wcfm_is_vendor()) {
			return $wcfm_menus;
		}
		
		$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
		if ( in_array( 'etsy', $enabled_marketplaces ) ) {
			$wcfm_menus['ced-etsy'] = array(
				'label'=>'ETSY',
				'url'=> esc_url( get_wcfm_url() .'ced-etsy' ),
				'icon'=> 'shopping-cart',
				'priority'=>2
			);
			return $wcfm_menus;
		} else {
			return $wcfm_menus;
		}
	}

	/**
	 * Add the Etsy menu in the vendor dashboard.
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_wcfm_load_page( $end_point = array() ) {
		switch( $end_point ) {
			case 'ced-etsy':
			require_once CED_ETSY_WCFM_DIRPATH . 'public/partials/ced-etsy-wcfm-main.php';
			break;
		}
	}


	/**
	 * Add the Etsy menu in the vendor dashboard.
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_wcfm_add_query_vars( $query_vars = array() ) {
		$query_vars['ced-etsy'] = 'ced-etsy';
		return $query_vars;
	}

	public function ced_etsy_wcfm_fetch_next_level_category() {
		$check_ajax = check_ajax_referer( 'ced-etsy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$store_category_id      = isset( $_POST['store_id'] ) ? sanitize_text_field( wp_unslash( $_POST['store_id'] ) ) : '';
			$etsy_wcfm_category_name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$etsy_wcfm_category_id       = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			$level                  = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';
			$next_level             = intval( $level ) + 1;
			$etsyCategoryList       = file_get_contents( CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/json/categoryLevel-' . $next_level . '.json' );
			$etsyCategoryList       = json_decode( $etsyCategoryList, true );
			$select_html            = '';
			$nextLevelCategoryArray = array();
			if ( ! empty( $etsyCategoryList ) ) {
				foreach ( $etsyCategoryList as $key => $value ) {
					if ( isset( $value['parent_id'] ) && $value['parent_id'] == $etsy_wcfm_category_id ) {
						$nextLevelCategoryArray[] = $value;
					}
				}
			}
			if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {

				$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_etsy_wcfm_level' . $next_level . '_category ced_etsy_wcfm_select_category select_boxes_cat_map" name="ced_etsy_wcfm_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '">';
				$select_html .= '<option value=""> --' . __( 'Select', 'woocommerce-etsy-integration' ) . '-- </option>';
				foreach ( $nextLevelCategoryArray as $key => $value ) {
					if ( ! empty( $value['name'] ) ) {
						$select_html .= '<option value="' . $value['id'] . ',' . $value['name'] . '">' . $value['name'] . '</option>';
					}
				}
				$select_html .= '</select></td>';
				echo json_encode( $select_html );
				die;
			}
		}
	}

	public function ced_etsy_wcfm_map_categories_to_store() {
		$check_ajax = check_ajax_referer( 'ced-etsy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array             = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$etsy_wcfm_category_array         = isset( $sanitized_array['etsy_wcfm_category_array'] ) ? $sanitized_array['etsy_wcfm_category_array'] : '';
			$store_category_array        = isset( $sanitized_array['store_category_array'] ) ? $sanitized_array['store_category_array'] : '';
			$etsy_wcfm_category_name          = isset( $sanitized_array['etsy_wcfm_category_name'] ) ? $sanitized_array['etsy_wcfm_category_name'] : '';
			$etsy_wcfm_store_id               = isset( $_POST['storeName'] ) ? sanitize_text_field( wp_unslash( $_POST['storeName'] ) ) : '';
			$etsy_wcfm_saved_category         = get_option( 'ced_etsy_wcfm_saved_category', array() );
			$alreadyMappedCategories     = array();
			$alreadyMappedCategoriesName = array();
			$etsyMappedCategories        = array_combine( $store_category_array, $etsy_wcfm_category_array );
			$etsyMappedCategories        = array_filter( $etsyMappedCategories );
			$alreadyMappedCategories     = get_option( 'ced_woo_etsy_wcfm_mapped_categories' . $etsy_wcfm_store_id, array() );
			if ( is_array( $etsyMappedCategories ) && ! empty( $etsyMappedCategories ) ) {
				foreach ( $etsyMappedCategories as $key => $value ) {
					$alreadyMappedCategories[ $etsy_wcfm_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_woo_etsy_wcfm_mapped_categories' . $etsy_wcfm_store_id, $alreadyMappedCategories );
			$etsyMappedCategoriesName    = array_combine( $etsy_wcfm_category_array, $etsy_wcfm_category_name );
			$etsyMappedCategoriesName    = array_filter( $etsyMappedCategoriesName );
			$alreadyMappedCategoriesName = get_option( 'ced_woo_etsy_wcfm_mapped_categories_name' . $etsy_wcfm_store_id, array() );
			if ( is_array( $etsyMappedCategoriesName ) && ! empty( $etsyMappedCategoriesName ) ) {
				foreach ( $etsyMappedCategoriesName as $key => $value ) {
					$alreadyMappedCategoriesName[ $etsy_wcfm_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_woo_etsy_wcfm_mapped_categories_name' . $etsy_wcfm_store_id, $alreadyMappedCategoriesName );
			$this->ced_etsy_wcfm_manager->ced_etsy_wcfm_create_auto_profiles( $etsyMappedCategories, $etsyMappedCategoriesName, $etsy_wcfm_store_id );
			wp_die();
		}
	}

	public function ced_etsy_wcfm_delete_account() {
		
		$check_ajax = check_ajax_referer( 'ced-etsy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id        = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$etsy_wcfm_account_list = get_option( 'ced_etsy_wcfm_accounts' ,'' );
			if( empty($etsy_wcfm_account_list) ) {
				$etsy_wcfm_account_list = array();
			} else {
				$etsy_wcfm_account_list = json_decode($etsy_wcfm_account_list,true);	
			}
			unset($etsy_wcfm_account_list[ced_etsy_wcfm_get_vendor_id()][$shop_id]);
			update_option( 'ced_etsy_wcfm_accounts', json_encode($etsy_wcfm_account_list) );
			wp_die();

		}
	}

	public function ced_etsy_wcfm_get_orders() {
		$check_ajax = check_ajax_referer( 'ced-etsy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id        = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';

			$fileOrders = CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/class-ced-etsy-wcfm-orders.php';
			ced_etsy_wcfm_include_file($fileOrders);

			$etsyOrdersInstance = Class_Ced_Etsy_Wcfm_Orders::get_instance();
			$getOrders          = $etsyOrdersInstance->getOrders( $shop_id );
		}
	}

	public function ced_etsy_wcfm_process_bulk_action() {
		$check_ajax = check_ajax_referer( 'ced-etsy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$CED_ETSY_Manager = $this->ced_etsy_wcfm_manager;
			$shop_name        = isset( $_POST['shopname'] ) ? sanitize_text_field( wp_unslash( $_POST['shopname'] ) ) : '';
			$operation        = isset( $_POST['operation_to_be_performed'] ) ? sanitize_text_field( wp_unslash( $_POST['operation_to_be_performed'] ) ) : '';
			$product_id       = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			if ( 'upload_product' == $operation ) {
				$already_uploaded = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $product_id . ' Already Uploaded',
								'woocommerce-etsy-integration'
							),
						)
					);
					die;
				} else {
					$get_product_detail = $CED_ETSY_Manager->prepare_product_for_upload( $product_id, $shop_name );
					if ( isset( $get_product_detail['results'][0]['listing_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => $get_product_detail['results'][0]['title'] . ' Uploaded Successfully',
								'prodid'  => $product_id,
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
								'prodid'  => $product_id,
							)
						);
						die;
					}
				}
			} elseif ( 'update_product' == $operation ) {
				$already_uploaded = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_ETSY_Manager->prepare_product_for_update( $product_id, $shop_name );
					if ( isset( $get_product_detail['results'][0]['listing_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => $get_product_detail['results'][0]['title'] . ' Updated Successfully',
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
							)
						);
						die;
					}
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $product_id . ' Not Found On Etsy',
								'woocommerce-etsy-integration'
							),
						)
					);
					die;
				}
			} elseif ( 'remove_product' == $operation ) {
				$already_uploaded = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_ETSY_Manager->prepare_product_for_delete( $product_id, $shop_name );
					if ( isset( $get_product_detail['results'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Product ' . $product_id . ' Deleted Successfully',
								'prodid'  => $product_id,
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
							)
						);
						die;
					}
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $product_id . ' Not Found On Etsy',
								'woocommerce-etsy-integration'
							),
						)
					);
					die;
				}
			} elseif ( 'update_inventory' == $operation ) {
				$already_uploaded = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$results = $CED_ETSY_Manager->prepare_product_for_update_inventory( $product_id, $shop_name );
					if ( 200 == $results['status'] ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => __(
									$results['message'],
									'woocommerce-etsy-integration'
								),
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => __(
									$results['message'],
									'woocommerce-etsy-integration'
								),
							)
						);
						die;
					}
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $product_id . ' Not Found On Etsy',
								'woocommerce-etsy-integration'
							),
						)
					);
					die;
				}
			} elseif ( 'deactivate_product' == $operation ) {
				$already_uploaded = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_ETSY_Manager->prepareProductHtmlForDeactivate( $product_id, $shop_name );
					if ( isset( $get_product_detail ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => __(
									'Product Deactivated Successfully',
									'woocommerce-etsy-integration'
								),
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => __(
									'Product Not Deactivated',
									'woocommerce-etsy-integration'
								),
							)
						);
						die;
					}
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $product_id . ' Not Found On Etsy',
								'woocommerce-etsy-integration'
							),
						)
					);
					die;
				}
			}
			elseif ( 'mark_not_uploaded' == $operation ) {
				delete_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name );
				
				echo json_encode(
					array(
						'status'  => 200,
						'message' => __(
							'Product marked as not uploaded',
							'woocommerce-etsy-integration'
						),
					)
				);
				die;
				
			}
		}
	}


	/**
	 ****************************************
	 * Etsy Import Products Bulk Operations.
	 ****************************************
	 *
	 * @since    1.1.2
	 */

	public function ced_etsy_import_product_bulk_action() {

		$check_ajax = check_ajax_referer( 'ced-etsy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			
			$import_product_file = CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/class-ced-etsy-wcfm-import-products.php';
			if ( file_exists( $import_product_file )) {
				require_once $import_product_file;
			} else {
				return;
			}

			$instance_import_product = Class_Ced_Etsy_Import_Product::get_instance();
			$operation   			 = isset( $_POST['operation_to_be_performed'] ) ? $_POST['operation_to_be_performed'] : '';
			$listing_ids 			 = isset( $_POST['listing_id'] ) ? $_POST['listing_id'] : '';
			$shop_name   			 = isset( $_POST['shop_name'] ) ? $_POST['shop_name'] : '';
			if ( !empty( $listing_ids ) ) {
				foreach( $listing_ids as $key => $listing_id ) {
					$if_product_exists = get_posts( 
						array(
							'numberposts'   =>-1,
							'post_type'     => 'product',
							'meta_query'    => array(
								array(
									'key'    => '_ced_etsy_listing_id_' . $shop_name,
									'value'  => $listing_id,
									'compare'=> '='
								)
							),
							'fields'=>'ids'
						) 
					);
					if( !empty( $if_product_exists ) ) {
						continue;
					} else {
						$response = $instance_import_product->ced_etsy_import_products( $listing_id , $shop_name );
						echo json_encode(
							array(
								'status'  => 200,
								'message' => __(
									'Product Imported Successfully !'
								),
							)
						);
						die;
					}
				}
			} else {
				return;
			}	
			wp_die();
		}
	}


	/**
	 * This function is used to create crons
	**/


	public function my_etsy_cron_schedules($schedules)
	{
		if ( ! isset( $schedules['ced_etsy_1min'] ) ) {
			$schedules['ced_etsy_1min'] = array(
				'interval' => 1 * 60,
				'display'  => __( 'Once every 1 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_etsy_6min'] ) ) {
			$schedules['ced_etsy_6min'] = array(
				'interval' => 6 * 60,
				'display'  => __( 'Once every 6 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_etsy_10min'] ) ) {
			$schedules['ced_etsy_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_etsy_15min'] ) ) {
			$schedules['ced_etsy_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_etsy_30min'] ) ) {
			$schedules['ced_etsy_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_etsy_20min'] ) ) {
			$schedules['ced_etsy_20min'] = array(
				'interval' => 20 * 60,
				'display'  => __( 'Once every 20 minutes' ),
			);
		}
		return $schedules;
	}


	// public function my_add_intervals($schedules)
	// {

	// 	$schedules['oneminute'] = array(
	// 		'interval' => 60,
	// 		'display' => __('Once Minute')
	// 	);
	// 	return $schedules;
	// }


	// public function ced_svd_run_cron()
	// {
		// die('hi');
		// update_option('abcdffgghjj','abcdef');
		// $shop_namess=isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		// 		// print_r($shop_namess);

		// $vendor_idss=ced_etsy_wcfm_get_vendor_id();
	// }


	public function action_to_get_orders_from_etsy_callback($vendor_id,$shop_name)
	{

		$order_product_file = CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/json/woo-etsy-test-order.json';
		
		$file_contents=file_get_contents($order_product_file);
		$array_form=json_decode($file_contents,true);
		if( isset($array_form) && !empty($array_form) && is_array($array_form) ) {
			foreach( $array_form['results'] as $orderData ) {
				if( isset($orderData['transactions']) && !empty($orderData['transactions']) && is_array($orderData['transactions']) ) {
					foreach($orderData['transactions'] as $productData) {
						if( $productData['product_id'] ) {
							$product_id = $productData['product_id'];

							// if( isset($productData['product_status']) && !empty($productData['product_status'])  ) {
							// 	$product_status = $productData['product_status'];
							// 	// Update status code 
							// }
							
							if( isset($productData['stock']) && !empty($productData['stock']) ) {
								$stock_quantity = $productData['stock'];

								$data  = wc_update_product_stock($product_id, $stock_quantity);
								 // print_r($data);
                                  // die();
							}

						}
					}
				}
			}
		}
	}
	

	public function schedule_events_for_time_interval()
	{

		if ( ! wp_get_schedule('fetch_orders_frometsy') ) {

			wp_schedule_event(time(), 'ced_etsy_30min', 'fetch_orders_frometsy');
		}
		


	}

	public function ced_etsy_wcfm_fetch_orders_from_etsy( $vendor_id = '', $shop_name = '' ) {
		if (empty($vendor_id) || empty( $shop_name ) ) {
			return false;
		}
		$fileOrders = CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/class-ced-etsy-wcfm-orders.php';
		ced_etsy_wcfm_include_file($fileOrders);
		$etsyOrdersInstance = Class_Ced_Etsy_Wcfm_Orders::get_instance();
		$getOrders          = $etsyOrdersInstance->getOrders( $shop_name );
		if( isset($getOrders) && !empty($getOrders) && is_array($getOrders) ) {
			foreach($getOrders['results'] as $content_to_update){
				if( isset($content_to_update['transactions']) && !empty($content_to_update['transactions']) && is_array($content_to_update['transactions'])){
					foreach($content_to_update['transactions'] as $productData){
						if(isset($productData['product_id']))
						{
							$product_id = $productData['product_id'];
							if( isset($productData['stock']) && !empty($productData['stock']) ) {
								$stock_quantity = $productData['stock'];

								$data  = wc_update_product_stock($product_id, $stock_quantity);

							}
						}

					}
				}
			}
		}

	}

	public function ced_etsy_wcfm_update_inventory_to_etsy_from_wcfm( $vendor_id = '', $shop_name = '' ) {
		
		if (empty($vendor_id) || empty( $shop_name ) ) {
			return false;
		}
		$ced_wcfm_pro_to_update = get_option( 'ced_etsy_wcfm_update_inventory_chunk_products_' . $shop_name . '_' . $vendor_id, array() );
		if ( empty( $ced_wcfm_pro_to_update ) ) {
			$store_products   = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'post_author' => $vendor_id,
					'meta_query'  => array(
						array(
							'key'     => '_ced_etsy_listing_id_' . $shop_name,
							'compare' => 'EXISTS',
						),
					),
				)
			);
			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$ced_wcfm_pro_to_update = array_chunk( $store_products, 20 );
		}
		if ( is_array( $ced_wcfm_pro_to_update[0] ) && ! empty( $ced_wcfm_pro_to_update[0] ) ) {
			foreach ( $ced_wcfm_pro_to_update[0] as $product_id ) {
				$this->ced_etsy_wcfm_manager->prepare_product_for_update_inventory( $product_id, $shop_name, true );
			}
			unset( $ced_wcfm_pro_to_update[0] );
			$ced_wcfm_pro_to_update = array_values( $ced_wcfm_pro_to_update );
			update_option( 'ced_etsy_wcfm_update_inventory_chunk_products_' . $shop_name . '_' . $vendor_id, $ced_wcfm_pro_to_update );
		}

	}

}
