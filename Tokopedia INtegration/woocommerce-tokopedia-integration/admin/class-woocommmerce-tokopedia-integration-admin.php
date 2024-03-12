<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/admin
 */
class Woocommmerce_Tokopedia_Integration_Admin {

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
	 * @param string $plugin_name       The name of this plugin.
	 * @param string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->loadDependency();
		add_action( 'manage_edit-shop_order_columns', array( $this, 'ced_tokopedia_add_table_columns' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'ced_tokopedia_manage_table_columns' ), 10, 2 );
	}
	public function ced_tokopedia_set_schedules() {
		if( isset($_GET['shop_name']) && !empty($_GET['shop_name']) ) {
			if( ! wp_get_schedule('ced_tokopedia_sync_existing_products_' . $_GET['shop_name']) ) {
				wp_schedule_event( time(), 'ced_tokopedia_10min' ,'ced_tokopedia_sync_existing_products_' . $_GET['shop_name'] );
			}
		}
	}
	public function ced_tokopedia_sync_existing_products() {
		$shop_name = str_replace("ced_tokopedia_sync_existing_products_", "", current_action());
		//$shop_name = 11426500;
		if( ! empty($shop_name)) {
			$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
			$shop_id      = $shop_data['shop_id'];
			$access_token = $shop_data['access_token'];
			$fsid         = $shop_data['fsid'];
			$requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
			include_once $requrest_tokopedia_file;
			$obj_request = new tokopediaRequest();
			$page = get_option( 'ced_tokopedia_sync_offset_' . $shop_name , '' );
			if( empty((int)$page) ) {
				$page = 1;
			}
			$url = 'https://fs.tokopedia.net/v2/products/fs/'.$fsid.'/'.$page.'/50';
			$response = $obj_request->sendCurlGetMethod( $url , $shop_name );
			if( isset( $response['data'][0] ) ) {
				foreach( $response['data'] as $product_info ) {
					$sku = $product_info['sku'];
					if( ! empty( $sku ) ) {
						$pro_id = wc_get_product_id_by_sku( $sku );
						//var_dump($sku);
						//var_dump($pro_id);echo'==';
						if( $pro_id ) {
							update_post_meta( $pro_id , '_ced_tokopedia_upload_id_'. $shop_name , $product_info['product_id'] );
						}
					}
				}
				update_option('ced_tokopedia_sync_offset_'. $shop_name , ( $page+1 ) );
			} else {
				update_option('ced_tokopedia_sync_offset_'. $shop_name , 1 );
			}
			
		}
	}


	public function ced_tokopedia_submit_shipment() {
// 		die('kk');
		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if( $check_ajax ) {
			$ced_tokopedia_tracking_code = isset( $_POST['ced_tokopedia_tracking_code'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_tracking_code'] ) ) : '';
			$order_id               = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			
			//$order_status = get_post_meta( $order_id , 'ced_tokopedia_order_state', true );
			//print_r( $order_status );
			
			$requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';

			if ( file_exists( $requrest_tokopedia_file ) && 400 == get_post_meta( $order_id , 'ced_tokopedia_order_state', true )) {
				$shop_name = get_post_meta( $order_id, 'ced_tokopedia_order_shop_id', true );
				require_once $requrest_tokopedia_file;
				if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
					$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
					$shop_id      = $shop_data['shop_id'];
					$access_token = $shop_data['access_token'];
					$fsid         = $shop_data['fsid'];
				}
				
				$obj_request        = new tokopediaRequest();
				$toko_order_id      = get_post_meta( $order_id, '_ced_tokopedia_order_id', true );

				// $action             = 'https://fs.tokopedia.net/inventory/v1/fs/'.$fsid.'/pick-up';
				// $params             = array();
				// $params['order_id'] = (int)$toko_order_id;
				// $params['shop_id']  = (int)$shop_id;
				// $body_params        = json_encode( $params , true );
				// $response           = $obj_request->sendshipmentCurlPostMethod( $action , $body_params , $shop_name );

				if( $toko_order_id ) {
					$action = 'https://fs.tokopedia.net/v1/order/'.$toko_order_id.'/fs/'.$fsid.'/status';
					$params = array();
					$params['order_status'] = 500;
					$params['shipping_ref_num'] = $ced_tokopedia_tracking_code;
						$body_params = json_encode($params);
						$response = $obj_request->sendshipmentCurlPostMethod( $action , $body_params , $shop_name );
					if(isset($response['data']) && strtolower($response['data']) == "success") {
						update_post_meta( $order_id,'_tokopedia_umb_order_status','Shipped' );
						update_post_meta( $order_id,'_tokopedia_umb_order_srn', $ced_tokopedia_tracking_code );
						$_order = wc_get_order( $order_id );
						$_order->update_status( 'wc-completed' );
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Shipment submitted successfully.',
							)
						);
						wp_die();
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => isset( $response['header']['error_code']) ? $response['header']['messages'] .' - '. $response['header']['reason'] : 'Shipment not submitted.',
							)
						);
						wp_die();
					}
				}else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => isset($response['header']['error_code']) ? $response['header']['messages'] .' - '. $response['header']['reason'] : 'Shipment not submitted.',
						)
					);
					wp_die();
				}
			}	else {
				echo json_encode(
						array(
							'status'  => 400,
							'message' => 'Order status needs to be Acknowledge for confirming shipping.',
						)
					);
					wp_die();
			}		
		}
	}
	/**
	 * Load dependency function
	 *
	 * @return void
	 */
	public function loadDependency() {

		$tokopediaRequest =  CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';		
		if (file_exists( $tokopediaRequest ) ) {
			require_once $tokopediaRequest;
			$this->CED_TOKOPEDIA_API_REQUEST = new tokopediaRequest;
		}

		$toko_manager =  CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/class-tokopedia.php';
		if ( file_exists( $toko_manager ) ) {
			require_once $toko_manager;
			$this->CED_TOKOPEDIA_Manager = CED_TOKOPEDIA_Manager::get_instance();
		}
	}

	public function ced_tokopedia_marketplaces_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'Tokopedia',
			'slug'            => 'woocommerce-tokopedia-integration',
			'menu_link'       => 'ced_tokopedia',
			'instance'        => $this,
			'function'        => 'ced_tokopedia_accounts_page',
			'card_image_link' => CED_TOKOPEDIA_URL . 'admin/images/tokopedia.jpg',

		);
		return $menus;
	}

	public function ced_tokopedia_accounts_page() {

		$file_Accounts = CED_TOKOPEDIA_DIRPATH . 'admin/partials/ced-tokopedia-accounts.php';

		if ( file_exists( $file_Accounts ) ) {
			require_once $file_Accounts;
		}
	}

	public function ced_tokopedia_add_table_columns( $columns ) {
		$modified_columns = array();
		foreach ( $columns as $key => $value ) {
			$modified_columns[ $key ] = $value;
			if ( 'order_number' == $key ) {
				$modified_columns['order_from'] = '<span title="Order source">Order source</span>';
			}
		}
		return $modified_columns;
	}


	public function ced_tokopedia_manage_table_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'order_from':
			$_ced_tokopedia_order_id = get_post_meta( $post_id, '_ced_tokopedia_order_id', true );
			if ( ! empty( $_ced_tokopedia_order_id ) ) {
				$tokopedia_icon = CED_TOKOPEDIA_URL . 'admin/images/tokopedia.jpg';
				echo '<p><img src="' . esc_url( $tokopedia_icon ) . '" height="35" width="60"></p>';
			}
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		if ( isset( $_GET['page'] ) && ( 'ced_tokopedia' == $_GET['page'] ) ) {
			 wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommmerce-tokopedia-integration-admin.css', array(), $this->version, 'all' );
			 wp_enqueue_style( 'ced-boot-css', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '1.0.0', 'all' );
		}


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
		 * defined in Woocommmerce_Tokopedia_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommmerce_Tokopedia_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( isset( $_GET['page'] ) && ( 'ced_tokopedia' == $_GET['page'] ) ) {
			$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommmerce-tokopedia-integration-admin.js', array( 'jquery' ), $this->version, false );
			$ajax_nonce     = wp_create_nonce( 'ced-tokopedia-ajax-seurity-string' );
			$localize_array = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => $ajax_nonce,
				'shop_name'  => $shop_name,
			);
			wp_localize_script( $this->plugin_name, 'ced_tokopedia_admin_obj', $localize_array );
		}

	}

	/**
	 * Add admin menus and submenus
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_add_menus() {

		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'CedCommerce', 'woocommerce-tokopedia-integration' ), __( 'CedCommerce', 'woocommerce-tokopedia-integration' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), plugins_url( 'woocommerce-tokopedia-integration/admin/images/logo1.png' ), 12 );

			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );

			if ( is_array( $menus ) && ! empty( $menus ) ) {

				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );

				}
			}
		}
	}

	/**
	 * Woocommerce_Tokopedia_Integration_Admin ced_tokopedia_search_product_name.
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_search_product_name() {
		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );

		if ( $check_ajax ) {
			$keyword      = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
			$product_list = '';
			if ( ! empty( $keyword ) ) {
				$arguements = array(
					'numberposts' => -1,
					'post_type'   => array( 'product', 'product_variation' ), // post type product with variation.
					's'           => $keyword,
				);
				$post_data  = get_posts( $arguements );
				if ( ! empty( $post_data ) ) {
					foreach ( $post_data as $key => $data ) {
						$product_list .= '<li class="ced_tokopedia_searched_product" data-post-id="' . esc_attr( $data->ID ) . '">' . esc_html( __( $data->post_title, 'tokopedia-woocommerce-integration' ) ) . '</li>';
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
	 * Woocommerce_Tokopedia_Integration_Admin ced_tokopedia_get_product_metakeys.
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_get_product_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
			include_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/ced-tokopedia-metakeys-list.php';
		}
	}

	/**
	 * Woocommerce_Tokopedia_Integration_Admin ced_tokopedia_process_metakeys.
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_process_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$metakey   = isset( $_POST['metakey'] ) ? sanitize_text_field( wp_unslash( $_POST['metakey'] ) ) : '';
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			if ( ! empty( $metakey ) ) {
				$added_meta_keys = get_option( 'ced_tokopedia_selected_metakeys', array() );
				if ( 'store' == $operation ) {
					$added_meta_keys[ $metakey ] = $metakey;
				} elseif ( 'remove' == $operation ) {
					unset( $added_meta_keys[ $metakey ] );
				}
				update_option( 'ced_tokopedia_selected_metakeys', $added_meta_keys );
				echo json_encode( array( 'status' => 200 ) );
				die();
			} else {
				echo json_encode( array( 'status' => 400 ) );
				die();
			}
		}
	}

	/**
	 * Active Marketplace List
	 *
	 * @since    1.0.0
	 */

	public function ced_marketplace_listing_page() {
		$activeMarketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $activeMarketplaces ) && ! empty( $activeMarketplaces ) ) {
			require CED_TOKOPEDIA_DIRPATH . 'admin/partials/marketplaces.php';
		}
	}

	/**
	 * Tokopedia Changing Account status
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_change_account_status() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_name = isset( $_POST['shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shop_name'] ) ) : '';
			$status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'Active';
			global $wpdb;
			$table = $wpdb->prefix . 'ced_tokopedia_accounts';
			$wpdb->update(
				$table,
				array(
					'account_status' => $status,   // string
				),
				array( 'name' => $shop_name ),
				array(
					'%s',   // value1
				),
				array( '%d' )
			);
			die;
		}

	}

	/**
	 * Woocommerce_Tokopedia_Integration_Admin ced_tokopedia_add_order_metabox.
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_add_order_metabox() {
		global $post;
		$product    = wc_get_product( $post->ID );
		$order_from = get_post_meta( $post->ID, '_umb_tokopedia_marketplace', true );
		if ( 'tokopedia' == strtolower( $order_from ) ) {
			add_meta_box(
				'ced_tokopedia_manage_orders_metabox',
				__( 'Manage Marketplace Orders', 'woocommerce-tokopedia-integration' ) . wc_help_tip( __( 'Please send shipping confirmation.', 'woocommerce-tokopedia-integration' ) ),
				array( $this, 'ced_tokopedia_render_orders_metabox' ),
				'shop_order',
				'advanced',
				'high'
			);
		}
	}


	/**
	 * Woocommerce_Tokopedia_Integration_Admin ced_tokopedia_render_orders_metabox.
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_render_orders_metabox() {
		global $post;
		$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';
		if ( ! is_null( $order_id ) ) {
			$order         = wc_get_order( $order_id );
			$template_path = CED_TOKOPEDIA_DIRPATH . 'admin/partials/order-template.php';
			if ( file_exists( $template_path ) ) {
				include_once $template_path;
			}
		}
	}

	/**
	 * Woocommerce_Tokopedia_Integration_Admin ced_tokopedia_email_restriction.
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_email_restriction( $enable = '', $order = array() ) {
		if ( ! is_object( $order ) ) {
			return $enable;
		}
		$order_id   = $order->get_id();
		$order_from = get_post_meta( $order_id, '_umb_tokopedia_marketplace', true );
		if ( 'Tokopedia' == strtolower( $order_from ) ) {
			$enable = false;
		}
		return $enable;
	}

	/**
	 * Marketplace
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_marketplace_to_be_logged( $marketplaces = array() ) {

		$marketplaces[] = array(
			'name'             => 'Tokopedia',
			'marketplace_slug' => 'tokopedia',
		);
		return $marketplaces;
	}

	/**
	 * Tokopedia Cron Schedules
	 *
	 * @since    1.0.0
	 */
	public function my_tokopedia_cron_schedules( $schedules ) {

		if ( ! isset( $schedules['ced_tokopedia_6min'] ) ) {
			$schedules['ced_tokopedia_6min'] = array(
				'interval' => 6 * 60,
				'display'  => __( 'Once every 6 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_tokopedia_10min'] ) ) {
			$schedules['ced_tokopedia_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_tokopedia_15min'] ) ) {
			$schedules['ced_tokopedia_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_tokopedia_30min'] ) ) {
			$schedules['ced_tokopedia_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_tokopedia_1hour'] ) ) {

			$schedules['ced_tokopedia_1hour'] = array(

				'interval' => 60 * 60,
				'display'  => __( 'Once every 1 hour' ),
			);
		}
		return $schedules;
	}


	/**
	 * Tokopedia Fetch Next Level Category
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_fetch_next_level_category() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			global $wpdb;
			$store_category_id       = isset( $_POST['store_id'] ) ? sanitize_text_field( wp_unslash( $_POST['store_id'] ) ) : '';
			$tokopedia_category_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$tokopedia_category_id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			$level                   = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';
			$next_level              = intval( $level ) + 1;

			$tokopediaCategoryList = file_get_contents( CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/json/categoryLevel-' . $next_level . '.json' );

			$tokopediaCategoryList = json_decode( $tokopediaCategoryList, true );

			$select_html            = '';
			$nextLevelCategoryArray = array();
			if ( ! empty( $tokopediaCategoryList ) ) {
				foreach ( $tokopediaCategoryList as $key => $value ) {
					if ( isset( $value['parent_id'] ) && $value['parent_id'] == $tokopedia_category_id ) {
						$nextLevelCategoryArray[] = $value;
					}
				}
			}

			if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {
				$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_tokopedia_level' . $next_level . '_category ced_tokopedia_select_category select_boxes_cat_map" name="ced_tokopedia_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '">';
				$select_html .= '<option value=""> --' . __( 'Select', 'woocommerce-tokopedia-integration' ) . '-- </option>';
				foreach ( $nextLevelCategoryArray as $key => $value ) {
					if ( ! empty( $value['name'] ) ) {
						$select_html .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
					}
				}
				$select_html .= '</select></td>';
				echo json_encode( $select_html );
				die;
			}
		}
	}

	/*
	 * Function for Fetching child categories for custom profile
	 */
	public function ced_tokopedia_fetch_next_level_category_add_profile() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );

		if ( $check_ajax ) {

			global $wpdb;

			$tableName = $wpdb->prefix . 'ced_tokopedia_accounts';

			$tokopedia_store_id = isset( $_POST['tokopedia_store_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tokopedia_store_id'] ) ) : '';

			$tokopedia_category_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

			$tokopedia_category_id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

			$level = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';

			$next_level = intval( $level ) + 1;

			$tokopediaCategoryList = file_get_contents( CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/json/categoryLevel-' . $next_level . '.json' );

			$tokopediaCategoryList  = json_decode( $tokopediaCategoryList, true );
			$select_html            = '';
			$nextLevelCategoryArray = array();

			if ( ! empty( $tokopediaCategoryList ) ) {

				foreach ( $tokopediaCategoryList as $key => $value ) {

					if ( intval( $tokopedia_category_id ) == isset( $value['parent_id'] ) && $value['parent_id'] ) {
						$nextLevelCategoryArray[] = $value;
					}
				}
			}

			if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {

				$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_tokopedia_level' . $next_level . '_category ced_tokopedia_select_category_on_add_profile  select_boxes_cat_map" name="ced_tokopedia_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-tokopediaStoreId="' . $tokopedia_store_id . '">';
				$select_html .= '<option value=""> --' . __( 'Select', 'woocommerce-tokopedia-integration' ) . '-- </option>';
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


	/**
	 * Tokopedia Mapping Categories to WooStore
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_map_categories_to_store() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$sanitized_array          = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$tokopedia_category_array = isset( $sanitized_array['tokopedia_category_array'] ) ? $sanitized_array['tokopedia_category_array'] : '';
			$store_category_array     = isset( $sanitized_array['store_category_array'] ) ? $sanitized_array['store_category_array'] : '';
			$tokopedia_category_name  = isset( $sanitized_array['tokopedia_category_name'] ) ? $sanitized_array['tokopedia_category_name'] : '';
			$tokopedia_store_id       = isset( $_POST['storeName'] ) ? sanitize_text_field( wp_unslash( $_POST['storeName'] ) ) : '';

			$tokopedia_saved_category    = get_option( 'ced_tokopedia_saved_category', array() );
			$alreadyMappedCategories     = array();
			$alreadyMappedCategoriesName = array();

			$tokopediaMappedCategories = array_combine( $store_category_array, $tokopedia_category_array );
			$tokopediaMappedCategories = array_filter( $tokopediaMappedCategories );
			$alreadyMappedCategories   = get_option( 'ced_woo_tokopedia_mapped_categories', array() );

			if ( is_array( $tokopediaMappedCategories ) && ! empty( $tokopediaMappedCategories ) ) {

				foreach ( $tokopediaMappedCategories as $key => $value ) {
					$alreadyMappedCategories[ $tokopedia_store_id ][ $key ] = $value;

				}
			}
			update_option( 'ced_woo_tokopedia_mapped_categories', $alreadyMappedCategories );
			$tokopediaMappedCategoriesName = array_combine( $tokopedia_category_array, $tokopedia_category_name );
			$tokopediaMappedCategoriesName = array_filter( $tokopediaMappedCategoriesName );
			$alreadyMappedCategoriesName   = get_option( 'ced_woo_tokopedia_mapped_categories_name', array() );

			if ( is_array( $tokopediaMappedCategoriesName ) && ! empty( $tokopediaMappedCategoriesName ) ) {
				foreach ( $tokopediaMappedCategoriesName as $key => $value ) {

					$alreadyMappedCategoriesName[ $tokopedia_store_id ][ $key ] = $value;
				}
			}

			update_option( 'ced_woo_tokopedia_mapped_categories_name', $alreadyMappedCategoriesName );
			$this->CED_TOKOPEDIA_Manager->ced_tokopedia_createAutoProfiles( $tokopediaMappedCategories, $tokopediaMappedCategoriesName, $tokopedia_store_id );
			wp_die();
		}
	}


	/**
	 * Tokopedia Preview Product
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_preview_product_detail() {
		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			require_once CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaProducts.php';
			$product_id = isset( $_POST['prodId'] ) ? sanitize_text_field( wp_unslash( $_POST['prodId'] ) ) : '';
			$shopid     = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			if ( isset( $product_id ) && ! empty( $product_id ) ) {
				$tokopediaProductsInstance = Class_Ced_Tokopedia_Products::get_instance();
				$previewData               = $tokopediaProductsInstance->getFormattedData( $product_id, $shopid, true );
				$previewData               = $previewData['data'];
				$image_gallery_id          = array();
				$image_gallery_view        = array();
				$product                   = wc_get_product( $product_id );
				$product_type              = $product->get_type();
				$image                     = $product->get_data();
				$image_id                  = $image['image_id'];
				$image_gallery_id          = $image['gallery_image_ids'];
				$image_view                = wp_get_attachment_image_url( $image_id );
				?>
				<div class="ced_tokopedia_preview_product_popup_content">
					<div class="ced_tokopedia_preview_product_popup_header">
						<h5><?php esc_html_e( 'Tokopedia Product Details', 'woocommerce-tokopedia-integration' ); ?></h5>
						<span class="ced_tokopedia_preview_product_popup_close">X</span>
					</div>
					<div class="ced_tokopedia_preview_product_popup_body">
						<div class="preview_content preview-image-col">
							<div id="preview-image">
								<?php
								echo '<image height="100%" width="100%" src="' . esc_html( $image_view ) . '">';
								?>
							</div>
							<div class="ced_tokopedia_thumbnail">
								<ul>
									<?php
									if ( isset( $image_gallery_id ) && ! empty( $image_gallery_id ) ) {
										foreach ( $image_gallery_id as $value ) {
											$image_gallery_view = wp_get_attachment_image_url( $value );
											echo '<li>
											<img src="' . esc_html( $image_gallery_view ) . '">
											</li>';
										}
									}
									?>
								</ul>
							</div>	
						</div>
						<div class="preview_content preview_content-col">
							<div id="preview_Right_details_content">
								<h4>
									<?php
									echo esc_html( $shopid );
									?>
								</h4>
								<h3 class="">
									<?php
									$title  = isset( $previewData['title'] ) ? $previewData['title'] : '';
									$title .= '<br>';
									echo esc_html( $title );
									?>
								</h3>
								<p>
									<?php
									$sku = get_post_meta( $product_id, '_sku', true );
									echo esc_html( $sku );
									?>
								</p>
								<div id="Price_detail_tokopedia">
									<?php
									$price = '₹' . isset( $previewData['price'] ) ? $previewData['price'] : '';
									echo esc_html( $previewData['price'] );
									?>
								</div>
								<div class="ced_tokopedia_preview_detail">
									<ul>
										<li>
											<?php
											if ( 'variable' == $product_type ) {
												$variations = $tokopediaProductsInstance->getVaritionDataForPreview( $product_id );
												if ( isset( $variations['tier_variation'] ) ) {
													foreach ( $variations['tier_variation'] as $key => $value ) {
														echo '<div class="ced_tokopedia_variations_wrapper">
														<div>' . esc_html( $value['name'] );
														foreach ( $value['options'] as $key1 => $value1 ) {
															echo '<span class="ced_tokopedia_product_variation">' . esc_html( $value1 ) . '</span>';
														}
														echo '</span></div>';
														echo '</div>';
													}
												}
											}
											?>
										</li>
										<li>
											<div class="ced_tokopedia_preview_detail_name">Quantity</div>
											<div class="ced_tokopedia_preview_detail_desc">
												<div class="ced_tokopedia_qnty_wrapper">
													<span class="ed_tokopedia_qnty_point">-</span>
													<span class="ed_tokopedia_qnty_number">1</span>
													<span class="ed_tokopedia_qnty_point">+</span>
												</div>
												<div class="ced_tokopedia_product_piece"><?php echo isset( $previewData['quantity'] ) ? esc_html( $previewData['quantity'] ) : 0; ?> piece available</div>
											</div>
										</li>
									</ul>
									<input type="button" class="ced_tokopedia_preview_btn ced_tokopedia_add_to_cart"><?php esc_html_e( 'Add to basket', 'woocommerce-tokopedia-integration' ); ?>
									
								</div>
							</div>

						</div>
					</div>
					<div class="ced_tokopedia_product_wrapper">
						<h3 class="ced_tokopedia_product_title"><?php esc_html_e( 'Overview', 'woocommerce-tokopedia-integration' ); ?></h3>
						<ul class="ced_tokopedia_product_listing">
							<li>
								<div class="ced_tokopedia_product_value"><?php esc_html_e( 'Material', 'woocommerce-tokopedia-integration' ); ?></div>
								<div class="ced_tokopedia_product_desc"><?php echo isset( $previewData['materials'][0] ) ? esc_html( $previewData['materials'][0] ) : ''; ?></div>
							</li>
							
						</ul>
						<h6 class="ced_tokopedia_product_title"><?php esc_html_e( 'Product Description', 'woocommerce-tokopedia-integration' ); ?></h6>
						<p class="ced_tokopedia_product_para"><?php echo esc_html( $previewData['description'] ); ?></p>
					</div>
				</div>
				<?php
			}
			wp_die();

		}
	}

	/**
	 * Tokopedia update Token Hourly.
	 */
	public function ced_tokopedia_update_token_hourly_scheduler( $shop_name = '' ) {

		// $requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
		// if ( file_exists( $requrest_tokopedia_file ) ) {
		// 	require_once $requrest_tokopedia_file;
		// 	$obj_request = new tokopediaRequest();
		// }
		
		global $wpdb;
		
		if ( empty( $shop_name ) ) {
			$shop_name    = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		}

		if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
			$shop_data     = ced_topedia_get_account_details_by_shop_name( $shop_name );
			$client_id     = $shop_data['client_id'];
			$client_secret = $shop_data['client_secret'];
		} else{
			return;
		}
		
		$access_token = $this->CED_TOKOPEDIA_API_REQUEST->sendCurlGetMethodForAcesssToken( 'ced_tokopedia_get_access_token', $client_id, $client_secret );		
		if (!empty( $access_token ) ) {
			$access_token = json_decode( $access_token , true );
			$token        = isset( $access_token['access_token'] ) ? $access_token['access_token'] :'';
		}
		if ( empty( $token ) || $token == '' || $token == null ) {
			return;
		}
		$table = $wpdb->prefix . 'ced_tokopedia_accounts';
		$wpdb->update( $table, array( 'access_token' => $token ), array( 'name' => $shop_name ), array( '%s' ) );
	}


	/**
	 * Tokopedia update Token Hourly.
	 */
	public function ced_tokopedia_register_ip_white_list( $shop_name = '' ) {

		// $requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
		
		// if ( file_exists( $requrest_tokopedia_file ) ) {
		// 	require_once $requrest_tokopedia_file;
		// 	$obj_request = new tokopediaRequest();
		// }
		
		if ( empty( $shop_name ) ) {
			$shop_name    = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		}

		$white_list_ip = array( "insert" => array( ced_tokopedia_get_current_ip() ) );
		$body_params   = json_encode( $white_list_ip , true );
		$Ip_whitelist  = $this->CED_TOKOPEDIA_API_REQUEST->sendCurlPostMethod( 'register_ip_whitelist' , $body_params , $shop_name );
	}


	/**
	 * Tokopedia Inventory Scheduler
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_inventory_schedule_manager() {

		$hook             = current_action();
		$shop_id          = str_replace( 'ced_tokopedia_inventory_scheduler_job_', '', $hook );
		$shop_id          = trim( $shop_id );
		$products_to_sync = get_option( 'ced_tokopedia_chunk_products', array() );

		if ( empty( $products_to_sync ) ) {
			$store_products   = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => 'product',
					'meta_key'     => '_ced_tokopedia_upload_id_' . $shop_id,
					'meta_compare' => 'EXISTS',
				)
			);
			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$products_to_sync = array_chunk( $store_products, 10 );

		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			
			$get_product_price_detail = $this->CED_TOKOPEDIA_Manager->prepareProductHtmlForUpdatePrice( $products_to_sync[0], $shop_id );
			$get_product_stock_detail = $this->CED_TOKOPEDIA_Manager->prepareProductHtmlForUpdateStock( $products_to_sync[0], $shop_id );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_tokopedia_chunk_products', $products_to_sync );
		}
	}
	/**
	 * Tokopedia Sync auto upload scheduler
	 */
	public function ced_tokopedia_auto_upload_schedule_manager() {

		$hook             = current_action();
		$shop_id          = str_replace( 'ced_tokopedia_auto_upload_schedule_job_', '', $hook );
		$shop_id          = trim( $shop_id );
		$products_to_sync = get_option( 'ced_tokopedia_aut_upload_chunk_products', array() );

		if ( empty( $products_to_sync ) ) {
			$store_products = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'fields'      => 'ids',
					'meta_query'  => array(
						array(
							'key'     => '_ced_tokopedia_upload_id_' . $shop_id,
							'compare' => 'NOT EXISTS',
						),
					),
					'tax_query'   => array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $term_id,
							'operator' => 'IN',
						),
					),
				)
			);
			$products_to_sync   = array_chunk( $store_products, 10 );
		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {

			$fileProducts = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}
			$tokopediaProductInstance = Class_Ced_Tokopedia_Products::get_instance();
			$getProducts              = $tokopediaProductInstance->prepareDataForUploading( $products_to_sync[0], $shop_id, true );
			unset( $products_to_sync[0] );
			update_option( 'check_update_option', 'yes' );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_tokopedia_aut_upload_chunk_products', $products_to_sync );
		}

	}
	public function ced_tokopedia_auto_upload_category_schedule_manager() {

		$hook                      = current_action();
		$shop_id                   = str_replace( 'ced_tokopedia_auto_upload_category_schedule_job_', '', $hook );
		$shop_id                   = trim( $shop_id );
		$category_products_to_sync = get_option( 'ced_tokopedia_category_auto__upload_chunk_products' );
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_tokopedia_profiles WHERE `shop_id`= %d ", $shop_id ), 'ARRAY_A' );

		if ( empty( $category_products_to_sync ) ) {
			foreach ( $result as $key => $terms ) {
				$term_id[] = $terms['woo_categories'];
			}
			$store_products = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'fields'      => 'ids',
					'tax_query'   => array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'id',
							'terms'    => $term_id,
							'operator' => 'IN',
						),
					),
				)
			);
		}
		$category_products_to_sync = $store_products;
		if ( is_array( $category_products_to_sync ) && ! empty( $category_products_to_sync ) ) {

			$fileProducts = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}

			$tokopediaProductInstance = Class_Ced_Tokopedia_Products::get_instance();
			$getProducts              = $tokopediaProductInstance->prepareDataForUploading( $category_products_to_sync, $shop_id, true );
			unset( $category_products_to_sync );
			$category_products_to_sync = array_values( $category_products_to_sync );
			update_option( 'ced_tokopedia_cat_auto__upload_chunk_products', $category_products_to_sync );
		}

	}
	/**
	 * Tokopedia Order Scheduler
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_order_schedule_manager() {

		$hook       = current_action();
		$shop_id    = str_replace( 'ced_tokopedia_order_scheduler_job_', '', $hook );
		$shop_id    = trim( $shop_id );
		$fileOrders = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaOrders.php';
		if ( file_exists( $fileOrders ) ) {
			require_once $fileOrders;
		}
		$tokopediaOrdersInstance = Class_Ced_Tokopedia_Orders::get_instance();
		$getOrders               = $tokopediaOrdersInstance->getOrders( $shop_id );
		if ( ! empty( $getOrders ) ) {
			$createOrder = $tokopediaOrdersInstance->createLocalOrder( $getOrders, $shop_id );
		}
	}

	/**
	 * Tokopedia Fetch Orders
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_get_orders() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );

		if ( $check_ajax ) {
			$shop_id        = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			
			$isShopInActive = ced_tokopedia_inactive_shops( $shop_id );
			if ( $isShopInActive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'woocommerce-tokopedia-integration'
						),
					)
				);
				die;
			}

			$fileOrders = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaOrders.php';
			if ( file_exists( $fileOrders ) ) {
				require_once $fileOrders;
			}
			$tokopediaOrdersInstance = Class_Ced_Tokopedia_Orders::get_instance();
			$getOrders               = $tokopediaOrdersInstance->getOrders( $shop_id );
			if ( ! empty( $getOrders ) ) {
				$createOrder = $tokopediaOrdersInstance->createLocalOrder( $getOrders, $shop_id );
			}
		}
	}

	/**
	 * Tokopedia Profiles List on popup
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_profiles_on_pop_up() {
		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$store_id = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$prodId   = isset( $_POST['prodId'] ) ? sanitize_text_field( wp_unslash( $_POST['prodId'] ) ) : '';
			global $wpdb;
			$profiles = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `shop_id` = %d", $store_id ), 'ARRAY_A' );
			?>
			<div class="ced_tokopedia_profile_popup_content">
				<div id="profile_pop_up_head_main">
					<h2><?php esc_html_e( 'CHOOSE PROFILE FOR THIS PRODUCT', 'woocommerce-tokopedia-integration' ); ?></h2>
					<div class="ced_tokopedia_profile_popup_close">X</div>
				</div>
				<div id="profile_pop_up_head"><h3><?php esc_html_e( 'Available Profiles', 'woocommerce-tokopedia-integration' ); ?></h3></div>
				<div class="ced_tokopedia_profile_dropdown">
					<select name="ced_tokopedia_profile_selected_on_popup" class="ced_tokopedia_profile_selected_on_popup">
						<option class="profile_options" value=""><?php esc_html_e( '---Select Profile---', 'woocommerce-tokopedia-integration' ); ?></option>
						<?php
						foreach ( $profiles as $key => $value ) {
							echo '<option  class="profile_options" value="' . esc_html( $value['id'] ) . '">' . esc_html( $value['profile_name'] ) . '</option>';
						}
						?>
					</select>
				</div>	
				<div id="save_profile_through_popup_container">
					<button data-prodId="<?php echo esc_html( $prodId ); ?>" class="ced_tokopedia_custom_button" id="save_tokopedia_profile_through_popup"  data-shopid="<?php echo esc_html( $store_id ); ?>"><?php esc_html_e( 'Assign Profile', 'woocommerce-tokopedia-integration' ); ?></button>
				</div>
			</div>


			<?php
			wp_die();
		}
	}

	/**
	 * Tokopedia Refreshing Categories
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_category_refresh() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_name      = isset( $_POST['shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shop_name'] ) ) : '';
			$isShopInActive = ced_tokopedia_inactive_shops( $shop_name );
			if ( $isShopInActive ) {
				die( 'you are inside the if' );
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'woocommerce-tokopedia-integration'
						),
					)
				);
				die;
			}
			$file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/tokopediaCategory.php';
			if ( ! file_exists( $file ) ) {
				return;
			}
			require_once $file;
			$tokopediaCategoryInstance = Class_Ced_Tokopedia_Category::get_instance();
			$fetchedCategories    = $tokopediaCategoryInstance->getTokopediaCategories( $shop_name );
			if ( $fetchedCategories ) {
				$categories = $this->CED_TOKOPEDIA_Manager->StoreCategories( $fetchedCategories, true );
				echo json_encode( array( 'status' => 200 ) );
				wp_die();
			} else {
				echo json_encode( array( 'status' => 400 ) );
				wp_die();
			}
		}
	}

	/**
	 * Tokopedia Save profile On Product level
	 *
	 * @since    1.0.0
	 */
	public function save_tokopedia_profile_through_popup() {
		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shopid     = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$prodId     = isset( $_POST['prodId'] ) ? sanitize_text_field( wp_unslash( $_POST['prodId'] ) ) : '';
			$profile_id = isset( $_POST['profile_id'] ) ? sanitize_text_field( wp_unslash( $_POST['profile_id'] ) ) : '';
			if ( '' == $profile_id ) {
				echo 'null';
				wp_die();
			}

			update_post_meta( $prodId, 'ced_tokopedia_profile_assigned' . $shopid, $profile_id );
		}
	}

	/**
	 * Tokopedia Bulk Operations
	 *
	 * @since    1.0.0
	 */
	public function ced_tokopedia_process_bulk_action() {

		$check_ajax = check_ajax_referer( 'ced-tokopedia-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$CED_TOKOPEDIA_Manager = $this->CED_TOKOPEDIA_Manager;
			$shop_name             = isset( $_POST['shopname'] ) ? sanitize_text_field( wp_unslash( $_POST['shopname'] ) ) : '';

			$operation      = isset( $_POST['operation_to_be_performed'] ) ? sanitize_text_field( wp_unslash( $_POST['operation_to_be_performed'] ) ) : '';
			$prodIDs     = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			$isShopInActive = ced_tokopedia_inactive_shops( $shop_name );

			if ( $isShopInActive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'woocommerce-tokopedia-integration'
						),
					)
				);
				die;
			}
			if ( 'upload_product' == $operation ) {
				$already_uploaded = get_post_meta( $prodIDs, '_ced_tokopedia_upload_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . get_the_title( $prodIDs ) . ' Already Uploaded',
								'woocommerce-tokopedia-integration'
							),
						)
					);
					die;
				} else {
					$get_product_detail = $CED_TOKOPEDIA_Manager->prepareProductHtmlForUpload( $prodIDs, $shop_name );
					if ( isset( $get_product_detail['data']['success_rows_data'][0]['product_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => get_the_title( $prodIDs ) . ' Uploaded Successfully',
								'prodid'  => $prodIDs,
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['msg'],
								'prodid'  => $prodIDs,
							)
						);
						die;
					}
				}
			} elseif ( 'update_product' == $operation ) {
				
				$already_uploaded = get_post_meta( $prodIDs, '_ced_tokopedia_upload_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_TOKOPEDIA_Manager->prepareProductHtmlForUpdate( $prodIDs, $shop_name );
					if ( isset( $get_product_detail['data']['success_rows_data'][0]['product_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => get_the_title( $prodIDs ) . ' Updated Successfully',
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['msg'],
							)
						);
						die;
					}
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $prodIDs . ' Not Found On Tokopedia',
								'woocommerce-tokopedia-integration'
							),
						)
					);
					die;
				}
			} elseif ( 'remove_product' == $operation ) {

				$already_uploaded = get_post_meta( $prodIDs, '_ced_tokopedia_upload_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_TOKOPEDIA_Manager->prepareProductHtmlForDelete( $prodIDs, $shop_name );
					if ( isset( $get_product_detail['data']['succeed_rows'] ) && empty( $get_product_detail['data']['failed_rows'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Product ' . get_the_title( $prodIDs ) . ' Deleted Successfully',
								'prodid'  => $prodIDs,
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['msg'],
							)
						);
						die;
					}
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Product ' . $prodIDs . ' Not Found On Tokopedia',
								'woocommerce-tokopedia-integration'
							),
						)
					);
					die;
				}
			} elseif ( 'update_price' == $operation ) {
				$already_uploaded = get_post_meta( $prodIDs, '_ced_tokopedia_upload_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_TOKOPEDIA_Manager->prepareProductHtmlForUpdatePrice( $prodIDs, $shop_name );
					if (  isset( $get_product_detail['data']['succeed_rows'] ) >= 1 && empty( $get_product_detail['data']['failed_rows'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => __(
									'Price Updated Successfully',
									'woocommerce-tokopedia-integration'
								),
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => __(
									'Price Not Updated',
									'woocommerce-tokopedia-integration'
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
								'Product ' . $prodIDs . ' Not Found On Tokopedia',
								'woocommerce-tokopedia-integration'
							),
						)
					);
					die;
				}
			} elseif ( 'update_stock' == $operation ) {
				$already_uploaded = get_post_meta( $prodIDs, '_ced_tokopedia_upload_id_' . $shop_name, true );
				if ( $already_uploaded ) {
					$get_product_detail = $CED_TOKOPEDIA_Manager->prepareProductHtmlForUpdateStock( $prodIDs, $shop_name );
					if ( isset( $get_product_detail['data']['succeed_rows'] ) >= 1 && empty( $get_product_detail['data']['failed_rows'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => __(
									'Stock Updated Successfully',
									'woocommerce-tokopedia-integration'
								),
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => __(
									'Stock Not Updated',
									'woocommerce-tokopedia-integration'
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
								'Product ' . $prodIDs . ' Not Found On Tokopedia',
								'woocommerce-tokopedia-integration'
							),
						)
					);
					die;
				}
			}
		}
	}

	public function ced_tokopedia_reOrderMenu( $menu_ord ) {

		if ( ! $menu_ord ) {
			return true;
		}

		$temp         = array();
		$traverseThis = true;
		
		for ( $i = 0; $i < 2; $i++ ) {
			foreach ( $menu_ord as $key => $value ) {
				if ( $traverseThis ) {
					if ( 'cedcommerce-integrations' == $value ) {
						unset( $menu_ord[ $key ] );
					}
				} else {
					if ( 'plugins.php' == $value ) {
						$temp[] = 'cedcommerce-integrations';
						$temp[] = 'plugins.php';
					} else {
						$temp[] = $value;
					}
				}
			}
			$traverseThis = false;

		}
		return $temp;

	}


	

}
