<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/admin
 */
class Good_Market_Integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0

	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0

	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
		include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market-order.php';
		$this->ced_good_market_order_manager = Ced_Good_Market_Order::get_instance();
		include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market-product.php';
		$this->ced_good_market_product_manager = Ced_Good_Market_Product::get_instance();
		$this->send_request_order_obj          = new Ced_Good_Market_Request();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Good_Market_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Good_Market_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/good_market_integration-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Good_Market_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Good_Market_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/good_market_integration-admin.js', array( 'jquery' ), $this->version, false );
		$ajax_nonce     = wp_create_nonce( 'ced-good_market-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
		);
			wp_register_style( 'ced-select-css', plugin_dir_url( __FILE__ ) . 'js/select2.min.css', '', $this->version );
			wp_enqueue_style( 'ced-select-css' );

			wp_register_script( 'ced-select-js', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', null, $this->version, true );
			wp_enqueue_script( 'ced-select-js' );

		wp_localize_script( $this->plugin_name, 'ced_good_market_admin_obj', $localize_array );

	}

		/**
		 * Add admin menus and submenus
		 *
		 * @since    1.0.0
		 */
	public function ced_good_market_add_menus() {

		add_menu_page( __( 'Good Market', 'Good_Market_Woocommerce' ), __( 'Good Market', 'Good_Market_Woocommerce' ), 'manage_woocommerce', 'ced_good_market', array( $this, 'ced_good_marketplace_listing_page' ), '', 12 );

	}


	/**
	 * Active Marketplace List
	 *
	 * @since    1.0.0
	 */

	public function ced_good_marketplace_listing_page() {

		include GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/good_market_woocommerce-admin-display.php';

	}

	/**
	 * Save and authorize api keys.
	 *
	 * @since    1.0.0
	 */
	public function ced_good_market_process_api_keys() {
		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$post_data = array();
			$post_data['query']                 = 'mutation login($email: String!, $password: String!, $vendorId: Int!) {
				generateVendorToken(email: $email, password: $password, vendorId: $vendorId){
					token     
					hash_token
					vendor_id
				}
			}';
			$post_data['variables']['email']    = isset( $_POST['client_email'] ) ? sanitize_text_field( $_POST['client_email'] ) : '';
			$post_data['variables']['vendorId'] = isset( $_POST['vendor_id'] ) ? sanitize_text_field( (int) $_POST['vendor_id'] ) : '';
			// $post_data['variables']['password'] = 'FB6B7D12F4F6EB415D71818C484F3';// staging
			$post_data['variables']['password'] = "78B12AC3F27959C42CBE26DAD7DAD";//production
			$json_data                = json_encode( $post_data );
			$send_request_obj         = new Ced_Good_Market_Request();
			$to_save_res              = $send_request_obj->good_market_post( $json_data );
			$decode_to_save_res       = json_decode( $to_save_res, 1 );
			$error_during_fetch_token = isset( $decode_to_save_res['errors'][0]['debugMessage'] ) ? $decode_to_save_res['errors'][0]['debugMessage'] : '';

			if ( $decode_to_save_res['data']['generateVendorToken']['hash_token'] ) {
				$vendor_id                = $decode_to_save_res['data']['generateVendorToken']['vendor_id'];
				$to_save_option['data']   = $to_save_res;
				$to_save_option['vendid'] = $vendor_id;
				update_option( 'good_market_data', $to_save_option );
				update_option( 'api_details', $json_data );

				$location_post_data['query']                   = 'query getSources($vendor_id: Int!, $hash_token: String!) {
				getSources(vendor_id: $vendor_id, hash_token: $hash_token){
					success
					sources {
						source_code
						name
						description
						latitude
						longitude
						country_id
						region_id
						region
						city
						street
						postcode
						contact_name
						email
						phone
						fax
					}
				}
			}';
				$location_post_data['variables']['vendor_id']  = (int) $decode_to_save_res['data']['generateVendorToken']['vendor_id'];
				$location_post_data['variables']['hash_token'] = $decode_to_save_res['data']['generateVendorToken']['hash_token'];
				$location_json_data                            = json_encode( $location_post_data );
				$location_send_request_obj                     = new Ced_Good_Market_Request();
				$location_to_save_res                          = $location_send_request_obj->good_market_post( $location_json_data );
				$decode_location_to_save_res                   = json_decode( $location_to_save_res, 1 );
				if ( $decode_location_to_save_res['data']['getSources']['success'] ) {
					$location_data = $decode_location_to_save_res['data']['getSources']['sources'];
					update_option( 'location_data', $location_data );
					$msg_res[] = 'Success.';
				} else {
					$msg_res[] = 'Something went wrong please try again.';
					wp_send_json_error( $msg_res );

				}
				wp_send_json_success( $msg_res );
				die;

			} else {
				wp_send_json_error( $error_during_fetch_token );
				die;
			}
		}
	}

	/**
	 * Woocommerce_Good_Market_Integration_Admin ced_good_market_search_product_name.
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_search_product_name() {
		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$keyword      = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
			$product_list = '';
			if ( ! empty( $keyword ) ) {
				$arguements = array(
					'numberposts' => -1,
					'post_type'   => array( 'product', 'product_variation' ),
					's'           => $keyword,
				);
				$post_data  = get_posts( $arguements );
				if ( ! empty( $post_data ) ) {
					foreach ( $post_data as $key => $data ) {
						$product_list .= '<li class="ced_good_market_searched_product" data-post-id="' . esc_attr( $data->ID ) . '">' . esc_html( __( $data->post_title, 'good_market-woocommerce-integration' ) ) . '</li>';
					}
				} else {
					$product_list .= '<li>No products found.</li>';
				}
			} else {
				$product_list .= '<li>No products found.</li>';
			}
			echo json_encode( array( 'html' => $product_list ) );
			wp_die();
		}
	}

	/**
	 * Woocommerce_Good_Market_Integration_Admin ced_good_market_get_product_metakeys.
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_get_product_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
			include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/ced-good_market-metakeys-list.php';
		}
	}

	/**
	 *  Ced_good_market_process_metakeys.
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_process_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$metakey   = isset( $_POST['metakey'] ) ? sanitize_text_field( wp_unslash( $_POST['metakey'] ) ) : '';
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			if ( ! empty( $metakey ) ) {
				$added_meta_keys = get_option( 'ced_good_market_selected_metakeys', array() );
				if ( 'store' == $operation ) {
					$added_meta_keys[ $metakey ] = $metakey;
				} elseif ( 'remove' == $operation ) {
					unset( $added_meta_keys[ $metakey ] );
				}
				update_option( 'ced_good_market_selected_metakeys', $added_meta_keys );
				echo json_encode( array( 'status' => 200 ) );
				die();
			} else {
				echo json_encode( array( 'status' => 400 ) );
				die();
			}
		}
	}


	/**
	 * Good_Market_Woocommerce ced_good_market_get_orders_manual.
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_get_orders_manual() {

		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$response   = $this->fetch_good_market_orders();
			$data_order = json_decode( $response, 1 );
			if ( ! empty( $data_order ) ) {
				$orders  = $data_order;
				$message = 'Orders updated successfully.';
				$status  = 200;
				$this->ced_good_market_order_manager->create_local_order( $orders );
			} else {

				$message = 'No Record Found';
				$status  = 200;
			}
			echo json_encode(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);
			wp_die();
		}
	}

	/**
	 * Woocommerce_Good_Market_Integration_Admin ced_good_market_list_per_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_list_per_page() {

		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$_per_page = isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : '10';
			update_option( 'ced_good_market_list_per_page', $_per_page );
			wp_die();
		}
	}

	/**
	 * Woocommerce_Good_Market_Integration_Admin ced_good_market_auto_fetch_orders.
	 *  wp-admin/admin-ajax.php?action=ced_good_market_auto_fetch_orders
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_auto_fetch_orders() {
		$response   = $this->fetch_good_market_orders();
		$data_order = json_decode( $response, 1 );
		if ( ! empty( $data_order ) ) {
			$orders  = $data_order;
			$message = 'Orders fetched successfully.';
			$status  = 200;
			$this->ced_good_market_order_manager->create_local_order( $orders );
		}
	}

	/**
	 *  Good_Market_Woocommerce sync_good_market_feeds.
	 *  wp-admin/admin-ajax.php?action=sync_good_market_feeds
	 *
	 * @since 1.0.0
	 */
	public function sync_good_market_feeds() {
		global $wpdb;
		$prefix     = $wpdb->prefix;
		$table_name = $prefix . 'good_market_upload_status';
		$sql        = "SELECT * from $table_name WHERE `feed_status`='0'";
		// $feeds_data_response = $wpdb->get_results( $wpdb->prepare( "SELECT * from {$wpdb->prefix}good_market_upload_status  WHERE `feed_status`='0'" ), 'ARRAY_A' );
		$feeds_data_response = $wpdb->get_results( $sql, 'ARRAY_A' );
		$job_id          = $feeds_data_response[0]['feed_id'];  // first inserted data with feed id
		if ( empty( $job_id ) ) {
			return true;
		}
			$bulk_data_id['query']               = 'query pendingBulkresponse($job_id: String!) {
			    pendingBulkresponse(job_id: $job_id) {
			        job_id
			        success
			        job_status
			        error_result{product_sku
			            message
			        }
			        product_ids{product_sku
			            product_id
			        }
			    }
			}';
			$bulk_data_id['variables']['job_id'] = $job_id;
			$feed_status_response                = $this->send_request_order_obj->good_market_post( json_encode( $bulk_data_id ) );
			$feed_status_response                = json_decode( $feed_status_response, true );
			$success_job                         = isset( $feed_status_response['data']['pendingBulkresponse']['success'] ) ? $feed_status_response['data']['pendingBulkresponse']['success'] : '';
		if ( $success_job ) {
			if ( isset( $feed_status_response['data']['pendingBulkresponse']['product_ids'] ) ) {
				$array_saved_pro = ( $feed_status_response['data']['pendingBulkresponse']['product_ids'] );
				foreach ( $array_saved_pro as $key_saved_pro => $value_saved_pro ) {
					$pro_id = wc_get_product_id_by_sku( $value_saved_pro['product_sku'] );
					if ( ! $pro_id ) {
						$sku_exp = explode( '_', $value_saved_pro['product_sku'] );
						$pro_id  = $sku_exp[1];
					}
					update_post_meta( $pro_id, 'saved_good_market_product_id', $value_saved_pro['product_id'] );
				}
			}
				$updated_status = $wpdb->get_results( $wpdb->prepare( "UPDATE {$wpdb->prefix}good_market_upload_status SET `feed_status` = %d WHERE `feed_id` = %s", 1, $job_id ), 'ARRAY_A' );

		}
	}
	/**
	 *  Good_Market_Woocommerce ced_good_market_auto_inventory_sync.
	 *  wp-admin/admin-ajax.php?action=ced_good_market_auto_inventory_sync
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_auto_inventory_sync() {
		$products_to_sync = get_option( 'ced_good_market_chunk_products', array() );
		if ( empty( $products_to_sync ) ) {
			$store_products   = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => array( 'product', 'product_variation' ),
					'meta_key'     => 'saved_good_market_product_id',
					'meta_compare' => 'exists',
				)
			);
			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$products_to_sync = array_chunk( $store_products, 30 );
		}
		if ( ! empty( $products_to_sync[0] ) && is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			$get_product_detail = $this->sync_good_market_products( $products_to_sync[0] );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_good_market_chunk_products', $products_to_sync );
		}
	}

	/**
	 *  Good_Market_Woocommerce sync_good_market_products.
	 *  wp-admin/admin-ajax.php?action=sync_good_market_products
	 *
	 * @since 1.0.0
	 */
	public function sync_good_market_products( $prod_ids ) {
		foreach ( $prod_ids as $prod_id ) {
			$terms        = get_the_terms( $prod_id, 'product_type' );
			$product_type = ( ! empty( $terms ) ) ? sanitize_title( current( $terms )->name ) : 'simple';
			if ( 'simple' == $product_type ) {
				$products_data_json['query'] = 'mutation saveProduct($vendor_id: Int!,$product_data: String!, $hash_token: String) {
				saveProduct(vendor_id: $vendor_id, product_data:$product_data , hash_token:$hash_token){
					success
					message
					product_id
				}
			}';

				$gm_prod = get_post_meta( $prod_id, 'saved_good_market_product_id', 1 );
				$stock   = $this->ced_good_market_product_manager->get_stock( $prod_id );
				$parent_product_id = wp_get_post_parent_id($prod_id);
				if(!empty($parent_product_id)) {
					$manage_stock        = get_post_meta( $parent_product_id, '_manage_stock', true );
					$v_manage_stock      = get_post_meta( $prod_id, '_manage_stock', true );
					$stock_status        = get_post_meta( $parent_product_id, '_stock_status', true );
					if ( 'instock' == $stock_status && 'yes' == $manage_stock && 'no' == $v_manage_stock ) {
						$stock = get_post_meta( $parent_product_id, '_stock', true );
					}
				}
				$_price  = $this->ced_good_market_product_manager->get_price( $prod_id );

				$location_saved_data = get_option( 'location_data', true );
				$allSources          = array();
				$allSources[]        = array(
					'source_code'   => $location_saved_data[0]['source_code'],
					'name'          => $location_saved_data[0]['name'],
					'quantity'      => $stock,
					'source_status' => 1,
					'status'        => 1,
				);
				/** Product weight */
				$weight = get_post_meta( $prod_id, '_custom_package_weight', true );
				if ( empty( $weight ) ) {
					$weight = $this->ced_good_market_product_manager->fetch_meta_value_of_the_product( $prod_id, 'global_package_weight', '' );
				}
				if ( empty( $weight ) ) {
					$weight = get_post_meta( $prod_id, '_weight', 'true' );
				}
				$weight_unit = get_option( 'woocommerce_weight_unit' );
				if ( 'kg' == $weight_unit ) {
					$weight = (float)$weight * 1000;
				} elseif ( 'lbs' == $weight_unit ) {
					$weight = $weight * 453.6;
				} elseif ( 'oz' == (float)$weight_unit ) {
					$weight = (float)$weight * 28.35;
				}
				$get_type_vir = get_post_meta( '_virtual', $prod_id );
				if ( 'yes' == $get_type_vir ) {
					$weight             = 0;
					$product_has_weight = 0;
				} else {
					$product_has_weight = 1;
				}
				$pro_data['id']                                  = $gm_prod;
				$pro_data['product']                             = json_encode(
					array(
						'sku'                => get_post_meta( $prod_id, '_sku', true ),
						'sources'            => json_encode( $allSources ),
						'product_has_weight' => 1,
						'weight'             => !empty($weight) ? round( $weight ) : 0,
						'price'              => $_price,
					)
				);
				$products_data_json['variables']['product_data'] = json_encode( $pro_data );
				$get_api_related_data                            = $this->send_request_order_obj->get_api_related_data();
				if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $products_data_json ) ) {

					$products_data_json['variables']['vendor_id']  = (int) $get_api_related_data['data']['generateVendorToken']['vendor_id'];
					$products_data_json['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
				}

				$send_request_obj = new Ced_Good_Market_Request();
				$data_to_send_inv = json_encode( $products_data_json );
				$response_data_inv        = $send_request_obj->good_market_post( $data_to_send_inv );
				$response_data_decode_inv = json_decode( $response_data_inv, 1 );
			}
		}

	}

	/**
	 *  Good_Market_Woocommerce ced_gm_inventory_schedule_manager.
	 * wp-admin/admin-ajax.php?action=ced_gm_inventory_schedule_manager&per_page=50&active_page=1
	 *
	 * @since 1.0.0
	 */
	public function ced_gm_inventory_schedule_manager() {

		$post_data_inv                                = array();
		$post_data_inv['query']                       = 'query vendorProGridData($vendor_id: Int! , $status: Int! , $filter: ProductGridFilterInput!,$per_page: Int!,$active_page: Int! ,$hash_token:String) {
			vendorProGridData(vendor_id: $vendor_id, status:$status, filter:$filter, per_page : $per_page, active_page:$active_page,hash_token:$hash_token){
				count
				products {
					vproduct_id
					check_status
					product_id
					sku
				}
			}
		}';
		$active_page                                  = get_option( 'active_page' );
		$active_page                                  = ! empty( $active_page ) ? $active_page : 1;
		$per_page                                     = 10;
		$post_data_inv['variables']['filter']['type'] = 'simple';
		$post_data_inv['variables']['status']         = 3;
		$post_data_inv['variables']['active_page']    = isset( $_GET['active_page'] ) ? sanitize_text_field( $_GET['active_page'] ) : $active_page;
		$post_data_inv['variables']['per_page']       = isset( $_GET['per_page'] ) ? sanitize_text_field( $_GET['per_page'] ) : $per_page;
		$get_api_related_data                         = $this->send_request_order_obj->get_api_related_data();
		if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $post_data_inv ) ) {

			$post_data_inv['variables']['vendor_id']  = (int) $get_api_related_data['data']['generateVendorToken']['vendor_id'];
			$post_data_inv['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
		}

		$send_request_obj = new Ced_Good_Market_Request();
		$data_to_send_inv         = json_encode( $post_data_inv );
		$response_data_inv        = $send_request_obj->good_market_post( $data_to_send_inv );
		$response_data_decode_inv = json_decode( $response_data_inv, 1 );
		if ( ! empty( $response_data_decode_inv['data']['vendorProGridData']['products'] ) && is_array( $response_data_decode_inv['data']['vendorProGridData']['products'] ) ) {

			foreach ( $response_data_decode_inv['data']['vendorProGridData']['products'] as $key_res => $val_res ) {
				$pro_id = wc_get_product_id_by_sku( $val_res['sku'] );

				if ( ! $pro_id ) {
					$sku_exp = explode( '_', $val_res['sku'] );
					$pro_id  = $sku_exp[1];
				}

				if ( ! empty( $pro_id ) && $pro_id > 0 ) {

					update_post_meta( $pro_id, 'saved_good_market_product_id', $val_res['product_id'] );
				}
			}
		}
		$final_count = $response_data_decode_inv['data']['vendorProGridData']['count'];

		if ( $active_page >= ( $final_count / $per_page ) ) {
			$active_page = 0;
		}
		update_option( 'active_page', $active_page + 1 );

	}

	/**
	 *  Good_Market_Woocommerce fetch_good_market_orders.
	 *
	 * @since 1.0.0
	 */
	public function fetch_good_market_orders() {

		$post_data          = array();
		$post_data['query'] = 'query vendorOrders($vendor_id: Int!, $page_setting: ordersListPageSettingInput!, $filter: ordersListFilterInput! , $hash_token : String ) {
			vendorOrdersList(vendor_id: $vendor_id, page_setting: $page_setting, filter: $filter , hash_token:$hash_token){
				success
				count
				vendor_orders {
					increment_id
					order_id
					created_at
					billing_name
					order_total
					shop_commission_fee
					net_vendor_earn
					payment_state
					order_payment_state
				}
			}
		}';
		$post_data['variables']['filter']['order_payment_state'] = 0;
		$post_data['variables']['page_setting']['count']         = 10;
		$post_data['variables']['page_setting']['activePage']    = 1;
		$get_api_related_data                                    = $this->send_request_order_obj->get_api_related_data();
		if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $post_data ) ) {

			$post_data['variables']['vendor_id']  = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
			$post_data['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
		}

		$send_request_obj     = new Ced_Good_Market_Request();
		$data_to_send         = json_encode( $post_data );
		$response_data        = $send_request_obj->good_market_post( $data_to_send );
		$response_data_decode = json_decode( $response_data, 1 );
		// print_r($response_data);die;
		$response = array();
		if ( isset( $response_data_decode['data']['vendorOrdersList']['success'] ) && $response_data_decode['data']['vendorOrdersList']['count'] > 0 ) {

			$response = $response_data_decode['data']['vendorOrdersList']['vendor_orders'];
		}
		return json_encode( $response );
	}


	/**
	 * Good Market Integration ced_good_market_process_bulk_action.
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_process_bulk_action() {

		$err_message = '';
		$check_ajax  = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$status                  = 400;
			$operation               = isset( $_POST['operation'] ) ? sanitize_text_field( $_POST['operation'] ) : '';
			$post_data               = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$good_market_product_ids = isset( $post_data['good_market_products_ids'] ) ? $post_data['good_market_products_ids'] : array();

			if ( 'upload' == $operation ) {

				if ( ! is_array( $good_market_product_ids ) || empty( $good_market_product_ids ) ) {
					return false;
				}
				$feed_array              = array();
				$this->items_with_errors = false;

				foreach ( $good_market_product_ids as $product_id ) {
					$process_mode         = 'CREATE';
					$response_pro_up_data = $this->ced_good_market_product_manager->ced_good_market_prepare_data( $product_id, $process_mode );
					$response_pro_up      = $response_pro_up_data['data'];
					$get_api_related_data = $this->send_request_order_obj->get_api_related_data();
					if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $response_pro_up ) ) {

						$response_pro_up['variables']['vendor_id']  = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
						$response_pro_up['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
					}
					$upload_pro     = $this->send_request_order_obj->good_market_post( json_encode( $response_pro_up ) );
					$upload_data_gm = json_decode( $upload_pro, 1 );
					update_post_meta( $product_id, 'product_data_gm', $upload_pro );
					if ( $upload_data_gm['data']['saveProduct']['success'] ) {

						update_post_meta( $product_id, 'saved_good_market_product_id', $upload_data_gm['data']['saveProduct']['product_id'] );
						if ( isset( $upload_data_gm['data']['saveProduct']['child_product'] ) && is_array( $upload_data_gm['data']['saveProduct']['child_product'] ) ) {
							$array_saved_pro = $upload_data_gm['data']['saveProduct']['child_product'];
							foreach ( $array_saved_pro as $key_saved_pro => $value_saved_pro ) {
								$pro_id = wc_get_product_id_by_sku( $value_saved_pro['sku'] );

								if ( ! $pro_id ) {
									$sku_exp = explode( '_', $value_saved_pro['sku'] );
									$pro_id  = $sku_exp[1];
								}

								update_post_meta( $pro_id, 'saved_good_market_product_id', $value_saved_pro['product_id'] );
							}
						}
						$status  = 200;
						$message = $upload_data_gm['data']['saveProduct']['message'];
					} else {

						$err_message = 'Please some data is missing in product(s).';
					}
				}
			} elseif ( 'save_Bulk_Product' == $operation ) {
				if ( ! is_array( $good_market_product_ids ) || empty( $good_market_product_ids ) ) {
					return false;
				}
				$feed_array              = array();
				$this->items_with_errors = false;

				$bulk_data['query'] = 'mutation saveBulkProduct($vendor_id: Int!,$product_data: String!, $hash_token: String) {
					saveBulkProduct(vendor_id: $vendor_id, product_data:$product_data , hash_token:$hash_token){
						success
						message
						job_id
					}
				}';
				$process_mode = 'CREATE';
				$not_mapped_id = array();
				$product_data = array();
				foreach ( $good_market_product_ids as $product_id ) {
					$data = $this->ced_good_market_product_manager->ced_good_market_prepare_bulk_data( $product_id, $process_mode );

					if ( 'not_matched' == $data ) {
						$error_pro[] = $product_id;
						$err_message = 'Attribute not mapped in some products';
					} elseif ( ! empty( $data ) && $data != $product_id ) {
						$product_data[] = $data;
					} else {
						$not_mapped_id[] = $data;
					}
				}
				if ( ! empty( $product_data ) ) {
					$bulk_data['variables']['product_data'] = json_encode( $product_data );
					$get_api_related_data                   = $this->send_request_order_obj->get_api_related_data();
					if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $bulk_data ) ) {

						$bulk_data['variables']['vendor_id']  = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
						$bulk_data['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
					}
					/*print_r($bulk_data);
					die('fdg');*/
					$upload_pro = $this->send_request_order_obj->good_market_post( json_encode( $bulk_data ) );
					update_option( 'ced_good_market_prepare_bulk_data', $upload_pro );
					$upload_product_response = json_decode( $upload_pro );
					$job_id       = $upload_product_response->data->saveBulkProduct->job_id;
					$feed_type    = 'Product Upload';
					$current_time = gmdate( 'l jS \of F Y h:i:s A' );
					if ( isset( $job_id ) && ! empty( $job_id ) ) {
						$goodmarket_product_upload_status                = array();
						$goodmarket_product_upload_status['feed_id']     = $job_id;
						$goodmarket_product_upload_status['feed_status'] = '0';
						$goodmarket_product_upload_status['feed_time']   = gmdate( 'l jS \of F Y h:i:s A' );
						global $wpdb;
						$prefix                  = $wpdb->prefix;
						$goodmarket_status_table = $prefix . 'good_market_upload_status';
						$good_market_insert_id   = $this->insert_in_db( $goodmarket_product_upload_status, $goodmarket_status_table );
						if ( ! empty( $good_market_insert_id ) ) {
							echo json_encode(
								array(
									'job_id'         => $job_id,
                                    'job_detail_url' => admin_url() . 'admin.php?page=ced_good_market&section=products_feeds_details&feed_id='.$job_id.'&panel=edit',
									'not_mapped_ids' => $not_mapped_id,
									'error'          => $err_message,
								)
							);
						}
					}
					die;
				}
				else  {
						echo json_encode(
								array(
									'not_mapped_ids' => $not_mapped_id,
								)
							);
				}	die;
			} else {
				$variations           = array();
				$get_api_related_data = array();
				if ( ! is_array( $good_market_product_ids ) || empty( $good_market_product_ids ) ) {
					return false;
				}

				foreach ( $good_market_product_ids as $val_prp_delp ) {
					$_product = wc_get_product( $val_prp_delp );

					if ( is_object( $_product ) ) {
						$type = $_product->get_type();
						if ( 'variable' == $type ) {
							$variations = array_merge($variations, $_product->get_children());
						}
					}
				}

				$good_market_product_all_ids = array_merge_recursive( $good_market_product_ids, $variations );
				$response_pro_del            = $this->ced_good_market_product_manager->ced_good_market_prepare_delete_data( $good_market_product_all_ids );
				$get_api_related_data        = $this->send_request_order_obj->get_api_related_data();
				if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $response_pro_del ) ) {

					$response_pro_del['variables']['vendor_id']  = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
					$response_pro_del['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
				}
				$upload_delete  = $this->send_request_order_obj->good_market_post( json_encode( $response_pro_del ) );
				$delete_data_gm = json_decode( $upload_delete, 1 );
				if ( $delete_data_gm['data']['productMassDelete']['count'] ) {

					$status  = 200;
					$message = 'Success';
				} else {
					$status  = 400;
					$message = 'No product deleted';
				}
			}
			if ( ! empty( $err_message ) ) {
				$status  = 400;
				$message = $err_message;
			}
			echo json_encode(
				array(
					'status' => $status,
					'error'  => $message,
				)
			);
			wp_die();
		}
	}


	/**
	 * Woocommerce_Good_Market_Integration_Admin ced_good_market_save_cat.
	 *
	 * @since 2.0.0
	 */

	public function ced_good_market_save_cat() {
		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$cat        = isset( $_POST['value'] ) ? sanitize_text_field( $_POST['value'] ) : '';
			$cat_id     = isset( $_POST['catId'] ) ? sanitize_text_field( $_POST['catId'] ) : '';
			$catname    = isset( $_POST['catname'] ) ? sanitize_text_field( $_POST['catname'] ) : '';
			$mapped_cat = get_option( 'good_market_mapped_cat' );
			$mapped_cat = json_decode( $mapped_cat, 1 );
			if ( ! isset( $cat ) || empty( $cat ) ) {
				delete_term_meta( $cat_id, 'ced_good_market_category' );
				delete_term_meta( $cat_id, 'ced_good_market_category_name' );

				foreach ( $mapped_cat['profile'] as $key => $value ) {
					if ( ! empty( $mapped_cat['profile'][ $key ] ) ) {
						unset( $mapped_cat['profile'][ $key ]['woo_cat'][ $cat_id ] );
						update_option( 'good_market_mapped_cat', json_encode( $mapped_cat ), 1 );
					} else {
						unset( $mapped_cat['profile'][ $key ] );
						update_option( 'good_market_mapped_cat', json_encode( $mapped_cat ), 1 );
					}
				}
			} else {
				update_term_meta( $cat_id, 'ced_good_market_category', $cat );
				update_term_meta( $cat_id, 'ced_good_market_category_name', $catname );
				if ( empty( $mapped_cat ) ) {
					$mapped_cat['profile'][ $cat ]['woo_cat'][ $cat_id ] = $cat_id;
					$mapped_cat['profile'][ $cat ]['profile_data']       = '';
					$mapped_cat['profile'][ $cat ]['profile_name']       = $catname;
					$mapped_cat['profile'][ $cat ]['mrkt_cat_name']      = $cat;

					update_option( 'good_market_mapped_cat', json_encode( $mapped_cat ), 1 );
				} else {
					foreach ( $mapped_cat['profile'] as $key => $value ) {
						if ( in_array( $cat_id, $value['woo_cat'] ) ) {
							$temp_cat = $key;
							unset( $mapped_cat['profile'][ $temp_cat ]['woo_cat'][ $cat_id ] );
							if ( empty( $mapped_cat['profile'][ $temp_cat ]['woo_cat'] ) ) {
								unset( $mapped_cat['profile'][ $temp_cat ] );
							}
							$mapped_cat['profile'][ $cat ]['woo_cat'][ $cat_id ] = $cat_id;
							if ( ! isset( $mapped_cat['profile'][ $cat ]['profile_data'] ) ) {
								$mapped_cat['profile'][ $cat ]['profile_data']  = '';
								$mapped_cat['profile'][ $cat ]['profile_name']  = $catname;
								$mapped_cat['profile'][ $cat ]['mrkt_cat_name'] = $cat;

							}

							update_option( 'good_market_mapped_cat', json_encode( $mapped_cat ), 1 );
						} else {
							$mapped_cat['profile'][ $cat ]['woo_cat'][ $cat_id ] = $cat_id;
							if ( ! isset( $mapped_cat['profile'][ $cat ]['profile_data'] ) ) {
								$mapped_cat['profile'][ $cat ]['profile_data']  = '';
								$mapped_cat['profile'][ $cat ]['profile_name']  = $catname;
								$mapped_cat['profile'][ $cat ]['mrkt_cat_name'] = $cat;

							}
							update_option( 'good_market_mapped_cat', json_encode( $mapped_cat ), 1 );

						}
					}
				}
			}
			GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
			$good_market_Request    = new Ced_Good_Market_Request();
			$products_attri         = $good_market_Request->get_good_market_attributes( $cat );
			$products_attri_decoded = json_decode( $products_attri, 1 );
			$schema                 = json_decode( $products_attri_decoded['data']['productAllowedAttributes']['groupwise_attributes'], 1 );
			update_option( $cat . 'attribute_set_id', $schema['attribute_set_id'] );
			wp_die();

		}

	}


	public function ced_good_market_ship_order() {
		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$order_id            = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
			$order_detail        = get_post_meta( $order_id, 'order_items', true );
			$good_market_orderid = isset( $_POST['good_market_orderid'] ) ? sanitize_text_field( $_POST['good_market_orderid'] ) : '';

			if ( ! empty( $order_detail ) && is_array( $order_detail ) ) {
				$order_items = array();
				$order_ship  = array();
				foreach ( $order_detail as $key_order => $value_order ) {
					$order_ship['item_id'] = $value_order['item_id'];
					$order_ship['qty']     = $value_order['qty_ordered'];
					$order_ship_invocie[]  = $order_ship;
				}
				$order_ship_send['query'] = 'mutation createShipment($vorder_id: Int!,
				$source_code: String!,
				$items: String!,
				$tracking: String!,
				$comment_text:String,
				$comment_customer_notify:Int,
				$is_visible_on_front:Int,
				$send_email:Int,
				$vendor_id: Int!,
				$hash_token : String) {
					createShipment(vorder_id: $vorder_id,
					source_code: $source_code,
					items: $items,
					tracking: $tracking,
					comment_text:$comment_text,
					comment_customer_notify:$comment_customer_notify,
					is_visible_on_front:$is_visible_on_front,
					send_email:$send_email,
					vendor_id:$vendor_id,
					hash_token:$hash_token) {
						message
						success
					}
				}';
				$order_detail_list        = get_post_meta( $order_id, 'order_list_details', true );
				$vendor_order_id          = $order_detail_list['order_id'];
				if ( ! empty( $order_ship ) ) {
					$track_array = array();
					$order_ship_send['variables']['comment_customer_notify'] = true;
					$order_ship_send['variables']['comment_text']            = ! empty( $_POST['ced_good_market_tracking_comment'] ) ? sanitize_text_field( $_POST['ced_good_market_tracking_comment'] ) : 'check shipment';
					$order_ship_send['variables']['items']                   = json_encode( $order_ship_invocie );
					if ( isset( $_POST['ced_good_market_tracking_num'] ) && ! empty( $_POST['ced_good_market_tracking_num'] ) ) {
						$track_array['number']       = isset( $_POST['ced_good_market_tracking_num'] ) ? sanitize_text_field( $_POST['ced_good_market_tracking_num'] ) : '';
						$track_array['title']        = isset( $_POST['ced_good_market_tracking_title'] ) ? sanitize_text_field( $_POST['ced_good_market_tracking_title'] ) : '';
						$track_array['carrier_code'] = isset( $_POST['ced_good_market_tracking_carrier'] ) ? sanitize_text_field( $_POST['ced_good_market_tracking_carrier'] ) : '';
						$track_array['carrier_code'] = isset( $_POST['ced_good_market_tracking_title'] ) ? sanitize_text_field( $_POST['ced_good_market_tracking_title'] ) : '';
						$track_to_send[]             = $track_array;
						update_post_meta( $order_id, 'good_market_order_shipment_tracking', $track_to_send );

						$order_ship_send['variables']['tracking'] = json_encode( $track_to_send );

					}
					$allSources                                  = array();
					$location_saved_data                         = get_option( 'location_data', true );
					$order_ship_send['variables']['source_code'] = $location_saved_data[0]['source_code'];
					$order_ship_send['variables']['send_email']  = true;
					$order_ship_send['variables']['vorder_id']   = $vendor_order_id;
					settype( $vendor_order_id, 'integer' );
					$get_api_related_data = $this->send_request_order_obj->get_api_related_data();

					if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $order_ship_send ) ) {
						$vendor_id_order = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
						settype( $vendor_id_order, 'integer' );
						$order_ship_send['variables']['vendor_id']  = $vendor_id_order;
						$order_ship_send['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
					}

					$this->ced_good_market_genrate_invoice_order( $order_id );
					$send_order_request_obj = new Ced_Good_Market_Request();
					$order_data_to_send     = json_encode( $order_ship_send );
					$response_order_data    = $send_order_request_obj->good_market_post( $order_data_to_send );

					if ( ! empty( $response_order_data ) ) {
						$order_shipment = json_decode( $response_order_data, 1 );

						if ( true == $order_shipment['data']['createShipment']['success'] ) {
							$order = wc_get_order( $order_id );
							$order->update_status( 'completed' );
							update_post_meta( $order_id, 'good_market_order_shipment', $response_order_data );
							update_post_meta( $order_id, '_ced_good_market_order_status', 'Shipped' );
							if ( ! empty( $track_to_send ) ) {
								$track_to_send[0]['comment'] = $order_ship_send['variables']['comment_text'];
								update_post_meta( $order_id, 'good_market_order_shipment_tracking', $track_to_send );

							}
							$response_order_data_decode = json_decode( $response_order_data, 1 );

						}
					}
				}
			}
		}
	}


	public function ced_good_market_genrate_invoice_order( $order_id ) {
		$check_ajax = check_ajax_referer( 'ced-good_market-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$order_detail = get_post_meta( $order_id, 'order_items', true );
			$good_market_orderid = isset( $_POST['good_market_orderid'] ) ? sanitize_text_field( $_POST['good_market_orderid'] ) : '';
			if ( ! empty( $order_detail ) && is_array( $order_detail ) ) {
				$order_items_invoice = array();
				$order_ship_invocie  = array();
				foreach ( $order_detail as $key_order => $value_order ) {
					$order_items_invoice[ $value_order['item_id'] ] = array( 'qty' => $value_order['qty_ordered'] );
				}
				$order_ship_invocie_send['query'] = 'mutation createInvoice($vorder_id: Int!, 
				$items: String!,
				$comment_text:String,
				$comment_customer_notify:Int,
				$is_visible_on_front:Int,
				$send_email:Int,
				$do_shipment: Int,
				$vendor_id: Int!,
				$hash_token : String) {
					createInvoice(vorder_id: $vorder_id, 
					items: $items,
					comment_text:$comment_text,
					comment_customer_notify:$comment_customer_notify,
					is_visible_on_front:$is_visible_on_front,
					send_email:$send_email,
					do_shipment:$do_shipment,
					vendor_id:$vendor_id,
					hash_token:$hash_token) {
						message
						success
					}
				}';
				$order_id                         = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';

				$order_detail_list = get_post_meta( $order_id, 'order_list_details', true );
				$vendor_order_id   = isset( $order_detail_list['order_id'] ) ? sanitize_text_field( $order_detail_list['order_id'] ) : '';
				if ( ! empty( $order_items_invoice ) ) {
					$order_ship_invocie_send['variables']['comment_customer_notify'] = 1;
					$order_ship_invocie_send['variables']['comment_text']            = 'check shipment';
					$order_ship_invocie_send['variables']['items']                   = json_encode( $order_items_invoice );
					$order_ship_invocie_send['variables']['send_email']              = 1;
					$order_ship_invocie_send['variables']['vorder_id']               = $vendor_order_id;
					settype( $vendor_order_id, 'integer' );
					$get_api_related_data = $this->send_request_order_obj->get_api_related_data();

					if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $order_ship_invocie_send ) ) {
						$vendor_id_order = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
						settype( $vendor_id_order, 'integer' );
						$order_ship_invocie_send['variables']['vendor_id']  = $vendor_id_order;
						$order_ship_invocie_send['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
					}
					$send_invoice_request_obj = new Ced_Good_Market_Request();
					$response_invoice_data    = $send_invoice_request_obj->good_market_post( json_encode( $order_ship_invocie_send ) );
					update_post_meta( $order_id, 'good_market_order_shipment_invoice', $response_invoice_data );
					$response_invoice_data_decode = json_decode( $response_invoice_data, 1 );
				}
			}
		}
	}

	public function ced_good_market_update_categories() {
		$get_categories['query']           = 'query category($id: Int!) {
			category(id: $id) {
				products {
					total_count
					page_info {
						current_page
						page_size
					}
				}
				children_count
				children {
					id
					level
					name
					path
					children {
						id
						level
						name
						path
						children {
							id
							level
							name
							path
							children {
								id
								level
								name
								path
								children {
									id
									level
									name
									path
								}
							}
						}
					}
				}
			}
		}';
		$get_categories['variables']['id'] = 2;
		$send_invoice_request_obj          = new Ced_Good_Market_Request();
		$response_catogies_data            = $send_invoice_request_obj->good_market_post( json_encode( $get_categories ) );
		$res_cat_decode                    = json_decode( $response_catogies_data, 1 );
		$this->get_cats( $res_cat_decode['data']['category']['children'], array() );
		$save_cat_arr             = get_option( 'save_cat_arr' );
		$contents                 = json_encode( $save_cat_arr['data'] );
		$ced_good_market_category = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/cats.json';
		if ( file_exists( $ced_good_market_category ) ) {
			chmod( $ced_good_market_category, 0777 );
			$fp                       = fopen( $ced_good_market_category, 'wb' );
			$ced_good_market_category = file_put_contents( $ced_good_market_category, $contents );
		}
		return true;
	}

	public function get_cats( $cats = array(), $names = array() ) {

		foreach ( $cats as $key => $value ) {
			if ( ! empty( $value['children'] ) ) {
				$names[] = $value['name'];
				$name    = $this->get_cats( $value['children'], $names );
				array_pop( $names );

			} else {
				$test                                 = $names;
				$test[]                               = $value['name'];
				$save_cat_arr                         = get_option( 'save_cat_arr' );
				$save_cat_arr['data'][ $value['id'] ] = $test;

				update_option( 'save_cat_arr', $save_cat_arr );
			}
		}
		return true;
	}


	/**
	 * Good_Market_Woocommerce_Integration_Admin ced_good_market_cron_schedules.
	 *
	 * @since 1.0.0
	 * @param array $schedules Cron Schedules.
	 */
	public function ced_good_market_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['ced_good_market_5min'] ) ) {
			$schedules['ced_good_market_5min'] = array(
				'interval' => 5 * 60,
				'display'  => __( 'Once every 5 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_good_market_10min'] ) ) {
			$schedules['ced_good_market_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_good_market_15min'] ) ) {
			$schedules['ced_good_market_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_good_market_30min'] ) ) {
			$schedules['ced_good_market_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		return $schedules;
	}

	/**
	 *  Good_Market_Woocommerce sync_good_market_products.
	 *
	 * @since 1.0.0
	 */
	public function good_market_image_update( $prod_ids ) {
		foreach ( $prod_ids as $prod_id ) {

			$products_data_json['query'] = 'mutation saveProduct($vendor_id: Int!,$product_data: String!, $hash_token: String) {
				saveProduct(vendor_id: $vendor_id, product_data:$product_data , hash_token:$hash_token){
					success
					message
					product_id
				}
			}';

			$gm_prod             = get_post_meta( $prod_id, 'saved_good_market_product_id', 1 );
			$stock               = $this->ced_good_market_product_manager->get_stock( $prod_id );
			$_price              = $this->ced_good_market_product_manager->get_price( $prod_id );
			$pro_data['id']      = $gm_prod;
			$pro_data['product'] = json_encode(
				array(
					'quantity_and_stock_status' => '{"qty":"' . $stock . '"}',
					'product_has_weight'        => 1,
				)
			);
			$products_data_json['variables']['product_data'] = json_encode( $pro_data );
			$get_api_related_data = $this->send_request_order_obj->get_api_related_data();
			if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) && is_array( $products_data_json ) ) {

				$products_data_json['variables']['vendor_id']  = (int) $get_api_related_data['data']['generateVendorToken']['vendor_id'];
				$products_data_json['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
			}
			$send_request_obj = new Ced_Good_Market_Request();
			$data_to_send_inv = json_encode( $products_data_json );
			print_r( $products_data_json );
			die;
		}
		return 0;

	}
	public function insert_in_db( $details, $tableName ) {
		global $wpdb;
		$wpdb->insert( $tableName, $details );
		$id = $wpdb->insert_id;
		return $id;
	}

	public function stock_update_after_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {

		if ( '_stock' == $meta_key || '_price' == $meta_key ) {
			$products_to_sync = get_option( 'ced_good_market_chunk_products', array() );

			$_product = wc_get_product( $post_id );
			$type     = $_product->get_type();
			if ( 'variable' == $type ) {
				$array_pro_to_push = $_product->get_children();
			} else {

				$array_pro_to_push[] = $post_id;
			}
			if ( ! $products_to_sync ) {
				$pro_array_update = array( $array_pro_to_push );
			} else {

				$products_to_sync[] = $array_pro_to_push;
				$pro_array_update   = $products_to_sync;
			}
			$pro_array_update = array_reverse( $pro_array_update );
			$pro_array_update = array_map( 'unserialize', array_unique( array_map( 'serialize', $pro_array_update ) ) );
			update_option( 'ced_good_market_chunk_products', $pro_array_update );

		}

	}
    /**
	 * Exclude duplicated products from order.
	 *
	 * @param array $metakeys
	 * @return string
	 */
	public function woocommerce_duplicate_product_exclude_meta( $metakeys = array() ) {
		$metakeys[] = 'saved_good_market_product_id';
		return $metakeys;
	}


}