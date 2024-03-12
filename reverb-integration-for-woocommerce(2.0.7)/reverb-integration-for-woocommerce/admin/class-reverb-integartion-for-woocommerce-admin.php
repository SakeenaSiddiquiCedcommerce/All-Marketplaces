<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Reverb_Integartion_For_Woocommerce
 * @subpackage Reverb_Integartion_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Reverb_Integartion_For_Woocommerce
 * @subpackage Reverb_Integartion_For_Woocommerce/admin
 * author     CedCommerce <plugins@cedcommerce.com>
 */
class Reverb_Integartion_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
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
		$this->load_dependency();
		add_action( 'manage_edit-shop_order_columns', array( $this, 'ced_reverb_add_table_columns' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'ced_reverb_manage_table_columns' ), 999, 2 );

	}

	public function ced_woocommerce_duplicate_product_exclude_meta($exclude_meta, $existing_meta_keys){
	    
	    $exclude_meta[] = 'ced_reverb_listing_id';
	    $exclude_meta[] = 'ced_reverb_listing_url';
	    $exclude_meta[] = 'ced_reverb_listing_status';
		return $exclude_meta;
	    
	    
	}

	public function ced_reverb_add_table_columns( $columns ) {
		$modified_columns = array();
		foreach ( $columns as $key => $value ) {
			$modified_columns[ $key ] = $value;
			if ( 'order_number' == $key ) {
				$modified_columns['order_from'] = '<span title="Order source">Order source</span>';
			}
		}
		return $modified_columns;
	}


	public function ced_reverb_manage_table_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'order_from':
				$_ced_reverb_order_id = get_post_meta( $post_id, '_ced_reverb_order_id', true );
				if ( ! empty( $_ced_reverb_order_id ) ) {
					$reverb_icon = CED_REVERB_URL . 'admin/images/reverb.png';
					echo '<p><img src="' . esc_url( $reverb_icon ) . '" height="25" width="40"></p>';
				}
		}
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reverb_Integartion_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reverb_Integartion_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'ced-boot-css', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '2.0.0', 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/reverb-integartion-for-woocommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reverb_Integartion_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reverb_Integartion_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/reverb-integartion-for-woocommerce-admin.js', array( 'jquery' ), $this->version, false );

		$ajaxnonce = wp_create_nonce( 'ced-reverb-ajax-seurity-string' );

		$localize_array = array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'ajaxnonce' => $ajaxnonce,
		);
		wp_localize_script( $this->plugin_name, 'ced_reverb_admin_obj', $localize_array );

	}

	/**
	 * Load the dependencies.
	 *
	 * @since    1.0.0
	 */
	public function load_dependency() {
		$ced_reverb_curl_file    = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		$ced_reverb_order_file   = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-order.php';
		$ced_reverb_manager_file = CED_REVERB_DIRPATH . 'admin/reverb/class-ced-reverb-manager.php';

		reverb_include_file( $ced_reverb_curl_file );
		reverb_include_file( $ced_reverb_order_file );
		reverb_include_file( $ced_reverb_manager_file );

		$this->ced_reverb_curl_instance = Ced_Reverb_Curl_Request::get_instance();
		$this->ced_reverb_order_manager = Ced_Reverb_Order::get_instance();
		$this->ced_reverb_manager       = Ced_Reverb_Manager::get_instance();

	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_add_menus.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_add_menus() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'Marketplaces', 'reverb-woocommerce-integration' ), __( 'Marketplaces', 'reverb-woocommerce-integration' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), 'dashicons-store', 12 );
			/**
 			* Filter hook for filtering cedcommerce submenus.
 			* @since 1.0.0
 			*/
			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );
				}
			}
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_add_marketplace_menus_to_array.
	 *
	 * @since 1.0.0
	 * @param array $menus Marketplace menus.
	 */
	public function ced_reverb_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'Reverb',
			'slug'            => 'reverb-woocommerce-integration',
			'menu_link'       => 'ced_reverb',
			'instance'        => $this,
			'function'        => 'ced_reverb_configuration_page',
			'card_image_link' => CED_REVERB_URL . 'admin/images/reverb.png',
		);
		return $menus;
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_license_panel.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_license_panel() {
		$file_license = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-license.php';
		if ( file_exists( $file_license ) ) {
			include_once $file_license;
		}
	}


	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_configuration_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_configuration_page() {
		$file_accounts = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-configuration.php';
		$status        = ced_reverb_check_license();
		if ( isset( $_GET['section'] ) && $status ) {
			include_once CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-main.php';
		} elseif ( file_exists( $file_accounts ) && $status ) {
			include_once $file_accounts;
		} else {
			/**
 			* Action hook for getting licence pannel.
 			* @since 1.0.0
 			*/
			do_action( 'ced_reverb_license_panel' );
		}
	}


	/**
	 * Reverb_Integration_For_Woocommerce ced_marketplace_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_marketplace_listing_page() {
		/**
 		* Filter hook for filtering cedcommerce plugin menus
 		* @since 1.0.0
 		*/
		$activeMarketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $activeMarketplaces ) && ! empty( $activeMarketplaces ) ) {
			require CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-marketplaces.php';
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_render_order_settings.
	 *
	 * @since    1.0.0
	 */
	public function ced_reverb_render_order_settings() {
		$file = CED_REVERB_DIRPATH . 'admin/pages/ced-reverb-order-settings.php';
		reverb_include_file( $file );
	}

	public function ced_reverb_render_shedulers_settings() {
		$file = CED_REVERB_DIRPATH . 'admin/pages/ced-reverb-shedulers-settings.php';
		reverb_include_file( $file );
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_render_product_settings.
	 *
	 * @since    1.0.0
	 */
	public function ced_reverb_render_product_settings() {
		$file = CED_REVERB_DIRPATH . 'admin/pages/ced-reverb-product-settings.php';
		reverb_include_file( $file );
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_process_api_keys.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_process_api_keys() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajaxnonce' );

		if ( $check_ajax ) {
			$status                           = 400;
			$message                          = 'Some error occured';
			$client_id                        = isset( $_POST['client_id'] ) ? sanitize_text_field( $_POST['client_id'] ) : '';
			$operation                        = isset( $_POST['operation'] ) ? sanitize_text_field( $_POST['operation'] ) : '';
			$environment                      = isset( $_POST['environment'] ) ? sanitize_text_field( $_POST['environment'] ) : '';
			$ced_reverb_configuration_details = array(
				'client_id'   => $client_id,
				'environment' => 'api',
			);
			if ( 'validate' == $operation ) {
				update_option( 'ced_reverb_configuration_details', $ced_reverb_configuration_details );
				$response = $this->ced_reverb_curl_instance->getValidatedCredentials( $client_id );

				if ( 'true' == $response ) {
					echo json_encode(
						array(
							'status'  => '200',
							'message' => __(
								'Authorized successfully',
								'reverb-woocommerce-integration'
							),
						)
					);
					die;
				} else {
					echo json_encode(
						array(
							'status'   => '201',
							'response' => 'Unable to Auhtorize',
						)
					);
					die;
				}
			}
			wp_die();
		}
	}


	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_map_order_status.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_map_order_status() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$woo_order_status      = isset( $_POST['woo_order_status'] ) ? sanitize_text_field( $_POST['woo_order_status'] ) : '';
			$reverb_order_status   = isset( $_POST['reverb_order_status'] ) ? sanitize_text_field( $_POST['reverb_order_status'] ) : '';
			$mapped_order_statuses = get_option( 'ced_reverb_mapped_order_statuses', array() );
			if ( ! empty( $woo_order_status ) ) {
				$mapped_order_statuses[ $reverb_order_status ] = $woo_order_status;
				echo 'Order status mapped successfully';
			} elseif ( isset( $mapped_order_statuses[ $reverb_order_status ] ) ) {
				unset( $mapped_order_statuses[ $reverb_order_status ] );
				echo 'Order status unmapped successfully';
			}
			update_option( 'ced_reverb_mapped_order_statuses', $mapped_order_statuses );
			wp_die();
		}
	}

	/**
	 * Woocommerce_reverb_Integration_Admin ced_reverb_search_product_name.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_search_product_name() {

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
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
						$product_list .= '<li class="ced_reverb_searched_product" data-post-id="' . esc_attr( $data->ID ) . '">' . esc_html( __( $data->post_title, 'reverb-woocommerce-integration' ) ) . '</li>';
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
		 * Woocommerce_reverb_Integration_Admin ced_reverb_get_product_metakeys.
		 *
		 * @since 1.0.0
		 */
	public function ced_reverb_get_product_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
			include_once CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-metakeys-list.php';
		}
	}

	/**
	 * Woocommerce_reverb_Integration_Admin ced_reverb_process_metakeys.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_process_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$metakey   = isset( $_POST['metakey'] ) ? sanitize_text_field( wp_unslash( $_POST['metakey'] ) ) : '';
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			if ( ! empty( $metakey ) ) {
				$added_meta_keys = get_option( 'ced_reverb_selected_metakeys', array() );
				if ( 'store' == $operation ) {
					$added_meta_keys[ $metakey ] = $metakey;
				} elseif ( 'remove' == $operation ) {
					unset( $added_meta_keys[ $metakey ] );
				}
				update_option( 'ced_reverb_selected_metakeys', $added_meta_keys );
				echo json_encode( array( 'status' => 200 ) );
				die();
			} else {
				echo json_encode( array( 'status' => 400 ) );
				die();
			}
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_cron_schedules.
	 *
	 * @since 1.0.0
	 * @param array $schedules Cron Schedules.
	 */
	public function ced_reverb_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['ced_reverb_5min'] ) ) {
			$schedules['ced_reverb_5min'] = array(
				'interval' => 5 * 60,
				'display'  => __( 'Once every 5 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_reverb_10min'] ) ) {
			$schedules['ced_reverb_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_reverb_15min'] ) ) {
			$schedules['ced_reverb_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_reverb_30min'] ) ) {
			$schedules['ced_reverb_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		return $schedules;
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_set_schedules.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_set_schedules() {
		$auto_fetch_orders = get_option( 'ced_reverb_auto_fetch_orders', '' );
		if ( 'on' == $auto_fetch_orders && ! wp_get_schedule( 'ced_reverb_auto_fetch_orders' ) ) {
			wp_schedule_event( time(), 'ced_reverb_10min', 'ced_reverb_auto_fetch_orders' );
		} elseif ( '' == $auto_fetch_orders ) {
			wp_clear_scheduled_hook( 'ced_reverb_auto_fetch_orders' );
		}

		$auto_update_inventory = get_option( 'ced_reverb_auto_update_inventory', '' );
		if ( 'on' == $auto_update_inventory && ! wp_get_schedule( 'ced_reverb_product_update' ) ) {
			wp_schedule_event( time(), 'ced_reverb_15min', 'ced_reverb_product_update' );
		} elseif ( '' == $auto_update_inventory ) {
			wp_clear_scheduled_hook( 'ced_reverb_product_update' );
		}

		if('on' == $auto_update_inventory && !wp_get_schedule('ced_reverb_product_update_on_updated_post_meta') ){

			wp_schedule_event(time(), 'ced_reverb_5min', 'ced_reverb_product_update_on_updated_post_meta' );

		}elseif ('' == $auto_update_inventory) {
			
			wp_clear_scheduled_hook('ced_reverb_product_update_on_updated_post_meta');

		}

		$auto_update_tracking = get_option( 'ced_reverb_auto_update_tracking', '' );
		if ( 'on' == $auto_update_tracking && ! wp_get_schedule( 'ced_reverb_auto_update_tracking' ) ) {
			wp_schedule_event( time(), 'ced_reverb_15min', 'ced_reverb_auto_update_tracking' );
		} elseif ( wp_get_schedule( 'ced_reverb_auto_update_tracking' ) ) {
			wp_clear_scheduled_hook( 'ced_reverb_auto_update_tracking' );
		}

		$ced_reverb_auto_upload_product = get_option( 'ced_reverb_auto_upload_product', '' );
		if ( 'on' == $ced_reverb_auto_upload_product && ! wp_get_schedule( 'ced_reverb_auto_upload_product' ) ) {
			wp_schedule_event( time(), 'ced_reverb_15min', 'ced_reverb_auto_upload_product' );
		} elseif ( wp_get_schedule( 'ced_reverb_auto_upload_product' ) ) {
			wp_clear_scheduled_hook( 'ced_reverb_auto_upload_product' );
		}

		if ( ! wp_get_schedule( 'ced_reverb_sync_existing_products' ) ) {
			wp_schedule_event( time(), 'ced_reverb_5min', 'ced_reverb_sync_existing_products' );
		}
	}




		/**
		 * Reverb_Integration_For_Woocommerce ced_reverb_get_orders_manual.
		 *
		 * @since 1.0.0
		 */
	public function ced_reverb_get_orders_manual() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$orders  = $this->fetch_reverb_orders();

			$status  = 400;
			$message = 'some error occurred.';

			if ( isset( $orders['total'] ) && $orders['total'] && isset( $orders['orders'] ) ) {
				$orders      = $orders['orders'];
				$createOrder = $this->ced_reverb_order_manager->createLocalOrder( $orders );
				$message     = 'Orders fetched successfully.';
				$status      = 200;
			} else {
				$message = 'No new orders to fetch.';
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
	 * Reverb_Integration_For_Woocommerce ced_reverb_get_orders_manual.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_auto_fetch_orders() {
		$orders = $this->fetch_reverb_orders();
		if ( isset( $orders['total'] ) && $orders['total'] && isset( $orders['orders'] ) ) {
			$orders      = $orders['orders'];
			$createOrder = $this->ced_reverb_order_manager->createLocalOrder( $orders );
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_product_shedulers_settings.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_product_update() {
		$config_details = get_option( 'ced_reverb_configuration_details', array() );
		$account_type   = '';
		$data           = get_option( 'ced_rvrb_upload_product_ids', true );
		if ( empty( $data ) ) {
			$metaKey = 'ced_reverb_listing_status' . $account_type;
			$args    = array(
				'post_type'      => array( 'product', 'product_variation' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => $metaKey,
						'compare' => 'EXISTS',
					),
				),
				'fields'         => 'ids',
			);
			$data    = get_posts( $args );
			$data    = array_chunk( $data, 25 );
		}

		$this->ced_reverb_manager->prepareProductUpdateInventory( $data[0] );
		unset( $data[0] );
		$data = array_values( $data );
		update_option( 'ced_rvrb_upload_product_ids', $data );
	}

	public function ced_reverb_auto_upload_categories() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$woo_categories  = isset( $_POST['categories'] ) ? array_map( 'sanitize_text_field', $_POST['categories'] ) : array();

			$operation              = isset( $sanitized_array['operation'] ) ? sanitize_text_field( $sanitized_array['operation'] ) : 'save';
			$auto_upload_categories = get_option( 'ced_reverb_auto_upload_categories', array() );
			if ( 'save' == $operation ) {
				$auto_upload_categories = array_merge( $auto_upload_categories, $woo_categories );
				$message                = 'Category added in auto upload queue';
			} elseif ( 'remove' == $operation ) {
				$auto_upload_categories = array_diff( $auto_upload_categories, $woo_categories );
				$auto_upload_categories = array_values( $auto_upload_categories );
				$message                = 'Category removed from auto upload queue';
			}
			$auto_upload_categories = array_unique( $auto_upload_categories );

			update_option( 'ced_reverb_auto_upload_categories', $auto_upload_categories );
			echo json_encode(
				array(
					'status'  => 200,
					'message' => $message,
				)
			);
			wp_die();
		}
	}

	public function ced_reverb_auto_update_tracking() {
		$reverb_orders = get_posts(
			array(
				'numberposts' => -1,
				'meta_key'    => '_reverb_umb_order_status',
				'meta_value'  => 'Fetched',
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
				'orderby'     => 'date',
				'order'       => 'DESC',
				'fields'      => 'ids',
			)
		);
		if ( ! empty( $reverb_orders ) && is_array( $reverb_orders ) ) {
			foreach ( $reverb_orders as $woo_order_id ) {
				$this->ced_reverb_auto_ship_order( $woo_order_id );
			}
		}
	}

	public function ced_reverb_auto_ship_order( $woo_order_id ) {
		$_reverb_umb_order_status = get_post_meta( $woo_order_id, '_reverb_umb_order_status', true );
		if ( empty( $_reverb_umb_order_status ) || 'Fetched' != $_reverb_umb_order_status ) {
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
				$shipping_data                      = array();
				$shipping_data['provider']          = $tracking_code;
				$shipping_data['tracking_number']   = $tracking_no;
				$shipping_data['send_notification'] = 1;
				$shipping_data                      = json_encode( $shipping_data, true );

				$purchaseOrderId = get_post_meta( $woo_order_id, '_ced_reverb_order_id', true );

				$shipRequest = $this->ced_reverb_curl_instance->ced_reverb_request( 'my/orders/selling/' . $purchaseOrderId . '/ship', $shipping_data, '', 'POST' );
				update_post_meta( $woo_order_id, '_reverb_umb_order_status', 'Shipped' );
				update_post_meta(
					$woo_order_id,
					'ced_reverb_order_details',
					array(
						'trackingNo' => $tracking_no,
						'provider'   => $tracking_code,
					)
				);
			}
		}
	}

	public function ced_reverb_auto_upload_product() {
		$config_details = get_option( 'ced_reverb_configuration_details', array() );
		$account_type   = '';
		$product_chunk  = get_option( 'ced_reverb_product_upload_chunk', array() );
		if ( empty( $product_chunk ) ) {
			$woo_categories = get_option( 'ced_reverb_auto_upload_categories', array() );
			$woo_categories = array_unique( $woo_categories );

			if ( ! empty( $woo_categories ) ) {
				$products = array();
				foreach ( $woo_categories as $term_id ) {
					$store_products = get_posts(
						array(
							'numberposts' => -1,
							'post_type'   => 'product',
							'fields'      => 'ids',
							'tax_query'   => array(
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'term_id',
									'terms'    => $term_id,
									'operator' => 'IN',
								),
							),
							'meta_query'  => array(
								array(
									'key'     => 'ced_reverb_listing_status' . $account_type,
									'compare' => 'NOT EXISTS',
								),

							),
						)
					);
					$products = array_unique( array_merge( $products, $store_products ) );
				}
				$products      = array_reverse( $products );
				$product_chunk = array_chunk( $products, 20 );
			}
		}

		if ( isset( $product_chunk[0] ) && is_array( $product_chunk[0] ) && ! empty( $product_chunk[0] ) ) {
			foreach ( $product_chunk[0] as $product_id ) {

				$response = $this->ced_reverb_manager->prepareProductHtmlForUpload( $product_id );
			}
			unset( $product_chunk[0] );
			$product_chunk = array_values( $product_chunk );
			update_option( 'ced_reverb_product_upload_chunk', $product_chunk );
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce fetch_reverb_orders.
	 *
	 * @since 1.0.0
	 */
	public function fetch_reverb_orders( $page = 1 ) {
		$ced_fetch_order_by_reverb_status = get_option( 'ced_fetch_order_by_reverb_status', '' );

		if ( ! empty( $ced_fetch_order_by_reverb_status ) ) {
			$action = 'my/orders/selling/' . $ced_fetch_order_by_reverb_status;
		} else {
			$action = 'my/orders/selling/all';
		}

		// echo $action;
		// die('pppp');

		$response = $this->ced_reverb_curl_instance->ced_reverb_get_request( $action );
		return $response;
	}


	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_sync_existing_products.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_sync_existing_products() {
		// ini_set( 'memory_limit', '-1' );
		$config_details = get_option( 'ced_reverb_configuration_details', array() );
		$account_type   = '';

		$offset = get_option( 'ced_reverb_sync_existing_offset', '' );
		if ( empty( $offset ) ) {
			$offset = 0;
		}
		$file = CED_REVERB_DIRPATH . 'reverb/lib/class-ced-reverb-curl-request.php';
		reverb_include_file( $file );
		$reverbRequest = new Ced_Reverb_Curl_Request();

		$per_page = 50;
		$loopMore = true;

		$resultJsonArray = $reverbRequest->ced_reverb_get_request( 'my/listings?per_page=' . $per_page . '&offset=' . $offset . '&state=all' );
		//$response        = json_decode( $resultJsonArray, true );
		//$response        = json_decode( json_encode( $response ), true );
		$response = $resultJsonArray;

		$offset = $offset + $per_page;
		if ( empty( $response['listings']['0'] ) ) {
			$loopMore = false;
			$offset   = 0;
		}
		update_option( 'ced_reverb_sync_existing_offset', $offset );

		$product_ids = array();
		if ( isset( $response['listings'] ) && is_array( $response['listings'] ) && ! empty( $response['listings'] ) ) {
			$productsOnReverb = $response['listings'];
			foreach ( $productsOnReverb as $key => $product ) {

				if ( isset( $product['state']['description'] ) ) {
					if ( isset( $product['sku'] ) ) {

						$product_id = wc_get_product_id_by_sku( $product['sku'] );

						if ( 0 != $product_id ) {

							$product_ids[] = $product_id;
							update_post_meta( $product_id, 'ced_reverb_listing_id' . $account_type, $product['id'] );
							update_post_meta( $product_id, 'ced_reverb_listing_url' . $account_type, $product['_links']['web']['href'] );
							update_post_meta( $product_id, 'ced_reverb_listing_status' . $account_type, 'PUBLISHED' );
							update_post_meta( $product_id, 'ced_reverb_listing_status' . $account_type, 'PUBLISHED' );
						}
					}
				}
			}
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce get_order_ids_by_status.
	 *
	 * @since 1.0.0
	 */
	public function get_order_ids_by_status( $status = 'Fetched' ) {
		$reverb_orders = get_posts(
			array(
				'numberposts' => -1,
				'meta_key'    => '_reverb_umb_order_status',
				'meta_value'  => $status,
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
				'orderby'     => 'date',
				'order'       => 'DESC',
				'fields'      => 'ids',
			)
		);
		return $reverb_orders;
	}

	/**
	 * Woocommerce_reverb_Integration_Admin ced_reverb_email_restriction.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_email_restriction( $enable = '', $order = array() ) {
		if ( ! is_object( $order ) ) {
			return $enable;
		}
		$order_id   = $order->get_id();
		$order_from = get_post_meta( $order_id, 'ced_reverb_order_marketplace', true );
		if ( 'reverb' == strtolower( $order_from ) ) {
			$enable = false;
		}
		return $enable;
	}



	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_submit_shipment.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_submit_shipment() {

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_reverb_tracking_code = isset( $_POST['ced_reverb_tracking_code'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_reverb_tracking_code'] ) ) : '';
			$order_id                 = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			$_ced_reverb_order_id     = get_post_meta( $order_id, '_ced_reverb_order_id', true );
			$ced_reverb_tracking_url  = 'https://sledenje.posta.si/Default.aspx?tn=' . $ced_reverb_tracking_code . '&guid=90DD826A-6016&lang=si';

			$parameters = array(
				'confirmed'       => true,
				'status'          => 'shipped',
				'tracking_number' => $ced_reverb_tracking_code,
				'tracking_url'    => $ced_reverb_tracking_url,
			);

			$response = $this->ced_reverb_order_manager->update_order_status( $_ced_reverb_order_id, $parameters );
			if ( isset( $response['result']['code'] ) && 400 == $response['result']['code'] ) {
				$message = isset( $response['result']['message'] ) ? $response['result']['message'] : 'Shipment not submitted.';
				$status  = 400;

			} elseif ( isset( $response['result']['code'] ) && 200 == $response['result']['code'] ) {
				$_order = wc_get_order( $order_id );
				$_order->update_status( 'wc-completed' );
				$message = 'Shipment submitted successfully.';
				$status  = 200;

			} else {
				$message = 'Shipment not submitted.';
				$status  = 400;
			}

			echo json_encode(
				array(
					'status'  => $status,
					'message' => isset( $message ) ? $message : 'Shipment not submitted.',
				)
			);
			wp_die();
		}
	}

	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_process_bulk_action.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_process_bulk_action() {

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_reverb_manager = $this->ced_reverb_manager;

			$operation   = isset( $_POST['operation_to_be_performed'] ) ? sanitize_text_field( $_POST['operation_to_be_performed'] ) : '';
			$product_ids = isset( $_POST['id'] ) ? array_map( 'sanitize_text_field', $_POST['id'] ) : '';
			if ( is_array( $product_ids ) ) {

				if ( 'upload_product' == $operation ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_reverb_manager->prepareProductHtmlForUpload( $prodIDs );
					$notice_array       = json_decode( $get_product_detail, true );

					if ( is_array( $notice_array ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => $notice_array['message'],
								'classes' => $notice_array['classes'],
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Unexpected error encountered, please try again!',
							)
						);
						die;
					}
				} elseif ( 'update_inventory' == $operation ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_reverb_manager->prepareProductUpdateInventory( $prodIDs, 'updateInventory' );
					$notice_array       = json_decode( $get_product_detail, true );
					if ( is_array( $notice_array ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => $notice_array['message'],
								'classes' => $notice_array['classes'],
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Unexpected error encountered, please try again!',
							)
						);
						die;
					}
				} elseif ( 'remove' == $operation ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_reverb_manager->reverbRemove( $prodIDs, 'DELETE' );
					$notice_array       = json_decode( $get_product_detail, true );
					if ( is_array( $notice_array ) && ! empty( $notice_array ) ) {
						if ( isset( $notice_array['message'] ) && '' != $notice_array['message'] && null != $notice_array['message'] && '' != $notice_array['classes'] && null != $notice_array['classes'] ) {
							echo json_encode(
								array(
									'status'  => 200,
									'message' => $notice_array['message'],
									'classes' => $notice_array['classes'],
								)
							);
							die;
						} else {
							echo json_encode(
								array(
									'status'  => 400,
									'message' => 'Unexpected error encountered, please try again!',
								)
							);
							die;
						}
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Unexpected error encountered, please try again!',
							)
						);
						die;
					}
				}
			}
		}
	}


	public function ced_reverb_fetch_next_level_category() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$store_category_id      = isset( $_POST['store_id'] ) ? sanitize_text_field( $_POST['store_id'] ) : '';
			$reverb_category_name   = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$reverb_category_id     = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
			$level                  = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '';
			$next_level             = intval( $level ) + 1;
			$folderName             = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
			$categoryFirstLevelFile = $folderName . 'categories.json';
			$reverbCategoryList     = file_get_contents( $categoryFirstLevelFile );
			$reverbCategoryList     = json_decode( $reverbCategoryList, true );
			$reverbCategoryList     = isset( $reverbCategoryList['categories'] ) ? $reverbCategoryList['categories'] : array();
			$select_html            = '';
			$nextLevelCategoryArray = array();

			if ( isset( $reverbCategoryList ) && is_array( $reverbCategoryList ) && ! empty( $reverbCategoryList ) ) {
				foreach ( $reverbCategoryList as $key => $categories ) {
					if ( $reverb_category_id == $categories['uuid'] ) {
						if ( isset( $categories['subcategories'] ) && is_array( $categories['subcategories'] ) && ! empty( $categories['subcategories'] ) ) {
							$options  = '';
							$options  = '<td data-catlevel="2" class="next_leveltd">';
							$options .= '<select class="ced_reverb_level2_category ced_reverb_select_category"><option>--select--</option>';
							foreach ( $categories['subcategories'] as $key => $category ) {
								$options .= '<option value="' . $category['uuid'] . '" data-catname="' . $category['full_name'] . '">' . $category['name'] . '</option>';
							}
							$options .= '</select></td>';
							print_r( $options );
						}
					}
				}
			}
			wp_die();
		}
	}

	public function ced_reverb_fetch_next_level_category_add_profile() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$store_category_id      = isset( $_POST['store_id'] ) ? sanitize_text_field( $_POST['store_id'] ) : '';
			$reverb_category_name   = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$reverb_category_id     = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
			$level                  = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '';
			$next_level             = intval( $level ) + 1;
			$folderName             = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
			$categoryFirstLevelFile = $folderName . 'categories.json';
			$reverbCategoryList     = file_get_contents( $categoryFirstLevelFile );
			$reverbCategoryList     = json_decode( $reverbCategoryList, true );
			$reverbCategoryList     = isset( $reverbCategoryList['categories'] ) ? $reverbCategoryList['categories'] : array();
			$select_html            = '';
			$nextLevelCategoryArray = array();

			if ( isset( $reverbCategoryList ) && is_array( $reverbCategoryList ) && ! empty( $reverbCategoryList ) ) {
				foreach ( $reverbCategoryList as $key => $categories ) {
					if ( $reverb_category_id == $categories['uuid'] ) {
						if ( isset( $categories['subcategories'] ) && is_array( $categories['subcategories'] ) && ! empty( $categories['subcategories'] ) ) {
							$options  = '';
							$options  = '<td data-catlevel="2" class="next_leveltd">';
							$options .= '<select class="ced_reverb_level2_category ced_reverb_select_category"><option>--select--</option>';
							foreach ( $categories['subcategories'] as $key => $category ) {
								$options .= '<option value="' . $category['uuid'] . '" data-catname="' . $category['full_name'] . '">' . $category['name'] . '</option>';
							}
							$options .= '</select></td>';
							print_r( $options );
						}
					}
				}
			}
			wp_die();
		}
	}


	public function ced_reverb_add_order_metabox() {
		global $post;
		$product    = wc_get_product( $post->ID );
		$order_from = get_post_meta( $post->ID, '_ced_reverb_order' . rifw_environment(), true );
		if ( $order_from ) {
			add_meta_box(
				'ced_reverb_manage_orders_metabox',
				__( 'Manage Reverb Orders', 'woocommerce-reverb-integration' ) . wc_help_tip( __( 'Please save tracking information of order.', 'woocommerce-reverb-integration' ) ),
				array( $this, 'reverb_render_orders_metabox' ),
				'shop_order',
				'advanced',
				'high'
			);
		}

		add_meta_box(
			'ced_reverb_description_metabox',
			__( 'Reverb Description', 'reverb-woocommerce-integration' ),
			array( $this, 'ced_reverb_render_metabox' ),
			'product',
			'advanced',
			'high'
		);
	}

	public function ced_reverb_render_metabox() {
		global $post;
		$product_id       = $post->ID;
		$long_description = get_post_meta( $product_id, '_ced_reverb_description', true );
		?>
		<table>
			<tbody>
				<tr>
					<td>
						<?php
						$key          = 'ced_reverb_data[' . $product_id . '][_ced_reverb_title]';
						$custom_title = get_post_meta( $product_id, '_ced_reverb_title', true );
						woocommerce_wp_text_input(
							array(
								'id'                => $key,
								'label'             => __( 'Reverb Title', 'woocommerce' ),
								'description'       => '',
								'type'              => 'text',
								'value'             => $custom_title,
								'custom_attributes' => array(
									'min' => '1',
								),
							)
						);

						woocommerce_wp_text_input(
							array(
								'id'          => 'ced_reverb_metabox_nonce',
								'label'       => '',
								'description' => '',
								'type'        => 'hidden',
								'value'       => esc_attr( wp_create_nonce( 'ced_reverb_metabox_nonce' ) ),
							)
						);

						$content   = $long_description;
						$editor_id = '_ced_reverb_description';
						$settings  = array( 'textarea_rows' => 10 );
						echo '<label>Reverb Description</label>';
						wp_editor( $content, $editor_id, $settings );
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}


	public function reverb_render_orders_metabox() {
		global $post;
		$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';
		if ( ! is_null( $order_id ) ) {
			$order         = wc_get_order( $order_id );
			$template_path = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-order-template.php';
			if ( file_exists( $template_path ) ) {
				include_once $template_path;
			}
		}
	}

	public function ced_reverb_save_metadata( $post_id = '' ) {
		if ( ! $post_id ) {
			return;
		}

		if ( $post_id ) {
			$ced_reverb_metabox_nonce = isset( $_POST['ced_reverb_metabox_nonce'] ) ? sanitize_text_field( $_POST['ced_reverb_metabox_nonce'] ) : '';
			if ( wp_verify_nonce( $ced_reverb_metabox_nonce, 'ced_reverb_metabox_nonce' ) ) {
				if ( isset( $_POST['ced_reverb_data'] ) ) {
					$ced_reverb_data = array_map( 'sanitize_text_field', $_POST['ced_reverb_data'] );
					foreach ( $ced_reverb_data as $key => $value ) {
						foreach ( $value as $meta_key => $meta_val ) {
							update_post_meta( $key, $meta_key, $meta_val );
						}
					}
				}
				if ( isset( $_POST['_ced_reverb_description'] ) ) {
					update_post_meta( $post_id, '_ced_reverb_description', sanitize_text_field( $_POST['_ced_reverb_description'] ) );
				}
			}

			if ( isset( $_POST['ced_reverb_carrier_code'] ) ) {
				update_post_meta( $post_id, 'ced_reverb_carrier_code', sanitize_text_field( $_POST['ced_reverb_carrier_code'] ) );
			}

			if ( isset( $_POST['ced_reverb_carrier_name'] ) ) {
				update_post_meta( $post_id, 'ced_reverb_carrier_name', sanitize_text_field( $_POST['ced_reverb_carrier_name'] ) );
			}

			if ( isset( $_POST['ced_reverb_carrier_url'] ) ) {
				update_post_meta( $post_id, 'ced_reverb_carrier_url', sanitize_text_field( $_POST['ced_reverb_carrier_url'] ) );
			}

			if ( isset( $_POST['ced_reverb_tracking_number'] ) ) {
				update_post_meta( $post_id, 'ced_reverb_tracking_number', sanitize_text_field( $_POST['ced_reverb_tracking_number'] ) );
			}

			$config_details = get_option( 'ced_reverb_configuration_details', array() );
			$account_type   = '';
			$is_uploaded    = get_post_meta( $post_id, 'ced_reverb_listing_id' . $account_type, true );
			if ( $is_uploaded ) {
				$is_transient_set = get_transient( 'ced_reverb_update_offers' );
				if ( ! $is_transient_set ) {
					$this->ced_reverb_manager->prepareProductUpdateInventory( array( $post_id ), 'UPDATE', true );
					set_transient( 'ced_reverb_update_offers', true, 60 );
				}
			}
		}
	}



	public function ced_reverb_custom_product_tabs( $tab ) {
		$tab['ced_reverb_custom_inventory'] = array(
			'label'  => __( 'Reverb Data', 'woocommerce' ),
			'target' => 'reverb_inventory_options',
			'class'  => array( 'show_if_simple' ),
		);
		return $tab;
	}

	public function ced_reverb_inventory_options_product_tab_content() {
		global $post;

		// Note the 'id' attribute needs to match the 'target' parameter set above
		?>
		<div id='reverb_inventory_options' class='panel woocommerce_options_panel'><div class='options_group'>
			<?php
			echo "<div class='ced_reverb_simple_product_level_wrap'>";
			echo "<div class=''>";
			echo "<h2 class='reverb-cool'>Reverb Product Data";
			echo '</h2>';
			echo '</div>';
			echo "<div class='ced_reverb_simple_product_content'>";
			$this->ced_reverb_render_fields( $post->ID );
			echo '</div>';
			echo '</div>';
			?>
		</div></div>
		<?php
	}

	public function ced_reverb_render_fields( $post_id ) {

		$file = CED_REVERB_DIRPATH . 'admin/partials/class-ced-reverb-product-fields.php';
		reverb_include_file( $file );
		$productFieldInstance = new Ced_Reverb_Product_Fields();

		$fields = $productFieldInstance->ced_reverb_get_custom_products_fields();

		foreach ( $fields as $key => $value ) {
			$attribute_options = array();
			$key               = '_ced_reverb_' . $value['fields']['id'];

			if ( 'title' == $value['fields']['id'] || 'description' == $value['fields']['id'] || 'markup_type' == $value['fields']['id'] || 'markup_price' == $value['fields']['id'] ) {
				continue;
			}

			$selected_value = get_post_meta( $post_id, $key, true );
			$id             = 'ced_reverb_data[' . $post_id . '][' . $key . ']';
			if ( '_select' == $value['type'] ) {

				$attribute_options[] = '--select--';
				foreach ( $value['fields']['options'] as $key => $list_value ) {
					$attribute_options[ $key ] = $list_value;
				}

				woocommerce_wp_select(
					array(
						'id'          => $id,
						'label'       => ucwords( strtolower( $value['fields']['label'] ) ),
						'options'     => $attribute_options,
						'value'       => $selected_value,
						'desc_tip'    => 'true',
						'description' => $value['fields']['description'],
					)
				);
			} elseif ( '_text_input' == $value['type'] ) {
				woocommerce_wp_text_input(
					array(
						'id'          => $id,
						'label'       => ucwords( strtolower( $value['fields']['label'] ) ),
						'desc_tip'    => 'true',
						'description' => $value['fields']['description'],
						'type'        => 'text',
						'value'       => $selected_value,
					)
				);
			}
		}
	}

	public function ced_reverb_render_product_fields( $loop, $variation_data, $variation ) {
		if ( ! empty( $variation_data ) ) {
			?>
			<div id='reverb_inventory_options_variable' class='panel woocommerce_options_panel'><div class='options_group'>
				<?php
				echo "<div class='ced_reverb_variation_product_level_wrap'>";
				echo "<div class='ced_reverb_parent_element'>";
				echo "<h2 class='reverb-cool'> Reverb Product Data";
				echo "<span class='dashicons dashicons-arrow-down-alt2 ced_reverb_instruction_icon'></span>";
				echo '</h2>';
				echo '</div>';
				echo "<div class='ced_reverb_variation_product_content ced_reverb_child_element'>";
				$this->ced_reverb_render_fields( $variation->ID );
				echo '</div>';
				echo '</div>';
				?>
			</div></div>
			<?php
		}
	}

	public function ced_reverb_save_product_fields( $post_id = '', $i = '' ) {
		if ( empty( $post_id ) ) {
			return;
		}
		$ced_reverb_metabox_nonce = isset( $_POST['ced_reverb_metabox_nonce'] ) ? sanitize_text_field( $_POST['ced_reverb_metabox_nonce'] ) : '';
		if ( wp_verify_nonce( $ced_reverb_metabox_nonce, 'ced_reverb_metabox_nonce' ) ) {
			if ( isset( $_POST['ced_reverb_data'] ) ) {
				$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
				if ( ! empty( $sanitized_array ) ) {
					foreach ( $sanitized_array['ced_reverb_data'] as $id => $value ) {
						foreach ( $value as $meta_key => $meta_val ) {
							update_post_meta( $id, $meta_key, $meta_val );
						}
					}
				}
			}
		}
	}


	/*
	*
	*Function for Storing mapped categories
	*
	*
	*/
	public function ced_reverb_map_categories_to_store() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$reverb_category_array = isset( $_POST['reverb_category_array'] ) ? array_map( 'sanitize_text_field', $_POST['reverb_category_array'] ) : '';
			$store_category_array  = isset( $_POST['store_category_array'] ) ? array_map( 'sanitize_text_field', $_POST['store_category_array'] ) : '';
			$reverb_category_name  = isset( $_POST['reverb_category_name'] ) ? array_map( 'sanitize_text_field', $_POST['reverb_category_name'] ) : '';

			if ( ! empty( $reverb_category_array ) && ! empty( $store_category_array ) && ! empty( $reverb_category_name ) ) {
				$this->ced_reverb_insert_profile( $reverb_category_array, $store_category_array, $reverb_category_name );
			}

			wp_die();
		}
	}

	public function ced_reverb_insert_profile( $reverb_category_array, $store_category_array, $reverb_category_name ) {
		if ( is_array( $reverb_category_array ) && is_array( $store_category_array ) && is_array( $reverb_category_name ) ) {
			foreach ( $reverb_category_array as $key1 => $value1 ) {
				foreach ( $store_category_array as $key2 => $value2 ) {
					foreach ( $reverb_category_name as $key3 => $value3 ) {
						if ( $key3 == $key2 && $key2 == $key1 && $key3 == $key1 ) {
							$woo_cat_id      = $store_category_array[ $key1 ];
							$reverb_cat_id   = $reverb_category_array[ $key1 ];
							$reverb_cat_name = $reverb_category_name[ $key1 ];
							update_term_meta( $woo_cat_id, 'ced_reverb_mapped_category', $reverb_cat_id );
							$need_new_profile = $this->ced_reverb_check_if_new_profile_needed( $woo_cat_id, $reverb_cat_name, $reverb_cat_id );
							if ( $need_new_profile ) {
								$this->ced_create_profile_list( $woo_cat_id, $reverb_cat_id, $reverb_cat_name );

							}
						}
					}
				}
			}
		}
	}

	public function ced_create_profile_list( $woo_cat_id = '', $reverb_cat_id = '', $reverb_cat_name = '' ) {
		$profile_data                    = array();
		$reverb_profiles                 = get_option( 'ced_reverb_profiles_list', array() );
		$index                           = count( $reverb_profiles ) + 1;
		$check                           = false;
		$profile_data['reverb_cat_id']   = $reverb_cat_id;
		$profile_data['reverb_cat_name'] = $reverb_cat_name;
		if ( ! empty( $reverb_profiles ) && is_array( $reverb_profiles ) ) {
			foreach ( $reverb_profiles as $key => $profileData ) {
				if ( isset( $profileData['reverb_cat_id'] ) && $reverb_cat_id == $profileData['reverb_cat_id'] ) {
					$reverb_profiles[ $key ]['woo_categories'][] = $woo_cat_id;
					$check                                       = true;
					break;
				}
			}
		}
		if ( false == $check ) {
			$profile_data['woo_categories'] = array( $woo_cat_id );
			$reverb_profiles[]      = $profile_data;
			$this->prepareProfileData( $reverb_cat_id, $woo_cat_id );
		}
		update_option( 'ced_reverb_profiles_list', $reverb_profiles );
	}

	public function ced_reverb_check_if_new_profile_needed( $woo_cat_id = '', $reverb_cat_name = '', $reverb_cat_id = '' ) {

		$reverb_profiles = get_option( 'ced_reverb_profiles_list', array() );

		if ( empty( $reverb_profiles ) ) {
			$reverb_profiles = array();
		}
		$check_reverb = false;
		$reverb_key   = '';
		$check        = false;

		if ( ! empty( $reverb_profiles ) && is_array( $reverb_profiles ) ) {
			foreach ( $reverb_profiles as $key => $profile_data ) {
				foreach ( $profile_data['woo_categories'] as $woo_key => $woo_categories ) {
					if ( isset( $profile_data['reverb_cat_id'] ) && $reverb_cat_id == $profile_data['reverb_cat_id'] ) {
						if ( $woo_cat_id != $woo_categories ) {
							$reverb_key   = $key;
							$check_reverb = true;

						} else {
							$check = true;
							break;
						}
					} else {
						if ( isset( $woo_categories ) && $woo_cat_id == $woo_categories ) {
							unset( $profile_data['woo_categories'][ $woo_key ] );
							$reverb_profiles[ $key ] = $profile_data;
							update_option( 'ced_reverb_profiles_list', $reverb_profiles );
							return true;
						}
					}
				}
			}
			if ( true == $check ) {
				update_option( 'ced_reverb_profiles_list', $reverb_profiles );
				return false;
			}
			if ( true == $check_reverb ) {
				$reverb_profiles[ $reverb_key ]['woo_categories'][] = $woo_cat_id;
				update_option( 'ced_reverb_profiles_list', $reverb_profiles );
				return false;
			}
		}
		return true;
	}

		/*
		*
		*Preparing profile data for saving
		*
		*
		*/
	public function prepareProfileData( $reverbCategoryId = '', $wooCategories = '' ) {

		$profileData                = array();
		$renderDataOnGlobalSettings = get_option( 'ced_reverb_global_settings', false );
		$renderDataOnGlobalSettings = json_decode( $renderDataOnGlobalSettings, true );
		$renderDataOnGlobalSettings['_umb_reverb_category']['default'] = $reverbCategoryId;
		$renderDataOnGlobalSettings['_umb_reverb_category']['metakey'] = null;
		$updateinfo                       = json_encode( $renderDataOnGlobalSettings );
		$check                            = false;
		$ced_reverb_category_specificData = get_option( 'ced_reverb_profile_data', array() );
		if ( empty( $ced_reverb_category_specificData ) ) {
			$ced_reverb_category_specificData[ $reverbCategoryId ] = $updateinfo;
		} else {
			if ( is_array( $ced_reverb_category_specificData ) ) {

				foreach ( $ced_reverb_category_specificData as $key => $value ) {
					if ( $key == $reverbCategoryId ) {
						$ced_reverb_category_specificData[ $key ] = $updateinfo;
						$check                                    = true;
						break;
					}
				}
			}
			if ( false == $check ) {
				$ced_reverb_category_specificData[ $reverbCategoryId ] = $updateinfo;
			}
		}
		update_option( 'ced_reverb_profile_data', $ced_reverb_category_specificData );
	}


	/**
	 * Reverb_Integration_For_Woocommerce ced_reverb_update_categories.
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_update_categories() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$action   = 'categories';
			$status   = 400;
			$response = $this->ced_reverb_curl_instance->ced_reverb_get_request( $action );

			if ( isset( $response['categories'] ) && isset( $response['categories'] ) && ! empty( $response['categories'] ) ) {
				$category_file = CED_REVERB_DIRPATH . 'admin/reverb/lib/categories.json';
				@file_put_contents( $category_file, json_encode( $response ) );
				$status  = 200;
				$message = 'Categories updated successfully .';
			} elseif ( isset( $response['categories'] ) && 400 == $response['categories'] ) {
				$message = isset( $response['categories'] ) ? $response['categories'] : $message;
			} else {
				$message = 'Unable to update categories.';
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
		 * Hangle ajax requer of bulk action of imoprt poroduct on the product imoprt tab
		 *
		 * @since    1.0.0
		 */
	public function ced_reverb_import_product_by_wp_list() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$listing_ids    = isset( $_POST['listing_ids'] ) ? array_map( 'sanitize_text_field', $_POST['listing_ids'] ) : '';
			$operation_name = isset( $_POST['operation_to_be_performed'] ) ? sanitize_text_field( $_POST['operation_to_be_performed'] ) : '';
			$config_details = get_option( 'ced_reverb_configuration_details', array() );
			$account_type   = '';

			foreach ( $listing_ids as $key => $listing_id ) {

				$if_product_exist = get_posts(
					array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
						'meta_query'     => array(
							array(
								'key'     => 'ced_reverb_listing_id' . $account_type,
								'value'   => $listing_id,
								'compare' => '=',
							),
						),
						'fields'         => 'ids',
					)
				);
				if ( ! empty( $if_product_exist ) ) {
					continue;
				} else {
					$this->ced_reverb_import_products( $listing_id );
				}
				break;
			}
		}
		wp_die();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function ced_reverb_import_products( $listings_id = '' ) {

		if ( ! empty( $listings_id ) ) {

			$listings_details   = array();
			$image_details      = array();
			$resultArray        = array();
			$resultJsonArray    = $this->ced_reverb_curl_instance->ced_reverb_get_request( 'listings/' . $listings_id );
			$response           = $resultJsonArray;
			$listings_details[] = $response;
			$product            = $response;
			$image_details      = $response['photos'];
			$this->create_simple_product( $product, $image_details );
		}
		return;
	}

	/**
	 * Creat Simple product with import product
	 *
	 * @since    1.0.0
	 */
	public function create_simple_product( $product = array(), $image_details = array() ) {

		$product_id     = wp_insert_post(
			array(
				'post_title'   => $product['title'],
				'post_status'  => 'publish',
				'post_type'    => 'product',
				'post_content' => $product['description'],
			)
		);
		$config_details = get_option( 'ced_reverb_configuration_details', array() );
		$account_type   = '';

		$this->insert_product_category( $product_id, $product );

		$_weight   = isset( $product['weight'] ) ? $product['weight'] : 0;
		$_length   = isset( $product['length'] ) ? $product['length'] : 0;
		$_width    = isset( $product['width'] ) ? $product['width'] : 0;
		$_height   = isset( $product['height'] ) ? $product['height'] : 0;
		$condition = isset( $product['condition']['uuid'] ) ? $product['condition'] : '';

		update_post_meta( $product_id, '_ced_reverb_prod_condition', $condition );
		update_post_meta( $product_id, '_weight', $_weight );
		update_post_meta( $product_id, '_length', $_length );
		update_post_meta( $product_id, '_width', $_width );
		update_post_meta( $product_id, '_height', $_height );

		$productData['attributes']['condition'][] = $product['condition']['display_name'];
		$productData['attributes']['make'][]      = $product['make'];
		$productData['attributes']['model'][]     = $product['model'];
		$productData['attributes']['shop_name'][] = $product['shop_name'];
		$count                                    = 0;
		foreach ( $productData['attributes'] as $key => $value ) {
			$values                         = array_unique( $value );
			$values                         = array_values( $values );
			$data['attribute_names'][]      = $key;
			$data['attribute_position'][]   = $count;
			$data['attribute_values'][]     = implode( '|', $values );
			$data['attribute_visibility'][] = 1;
			$data['attribute_variation'][]  = 0;
			++$count;
		}

		if ( isset( $data['attribute_names'], $data['attribute_values'] ) ) {
			$attribute_names         = $data['attribute_names'];
			$attribute_values        = $data['attribute_values'];
			$attribute_visibility    = isset( $data['attribute_visibility'] ) ? $data['attribute_visibility'] : array();
			$attribute_variation     = isset( $data['attribute_variation'] ) ? $data['attribute_variation'] : array();
			$attribute_position      = $data['attribute_position'];
			$attribute_names_max_key = max( array_keys( $attribute_names ) );

			for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
				if ( empty( $attribute_names[ $i ] ) || ! isset( $attribute_values[ $i ] ) ) {
					continue;
				}
				$attribute_id   = 0;
				$attribute_name = wc_clean( $attribute_names[ $i ] );
				if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
					$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
				}
				$options = isset( $attribute_values[ $i ] ) ? $attribute_values[ $i ] : '';
				if ( is_array( $options ) ) {
					$options = wp_parse_id_list( $options );
				} else {
					$options = wc_get_text_attributes( $options );
				}

				if ( empty( $options ) ) {
					continue;
				}
				$attribute = new WC_Product_Attribute();
				$attribute->set_id( $attribute_id );
				$attribute->set_name( $attribute_name );
				$attribute->set_options( $options );
				$attribute->set_position( $attribute_position[ $i ] );
				$attribute->set_visible( isset( $attribute_visibility[ $i ] ) );
				$attribute->set_variation( isset( $attribute_variation[ $i ] ) );
				$attributes[] = $attribute;
			}
			$product_type = 'simple';
			$classname    = WC_Product_Factory::get_product_classname( $product_id, $product_type );
			$_product     = new $classname( $product_id );
			$_product->set_attributes( $attributes );
			$_product->save();
		}

		wp_set_object_terms( $product_id, 'simple', 'product_type' );
		update_post_meta( $product_id, '_visibility', 'visible' );
		update_post_meta( $product_id, 'ced_reverb_listing_id' . $account_type, $product['id'] );
		update_post_meta( $product_id, 'ced_reverb_listing_url' . $account_type, $product['_links']['web']['href'] );
		update_post_meta( $product_id, 'ced_reverb_product_data', $product );
		update_post_meta( $product_id, 'ced_reverb_product_inventory', $product['inventory'] );

		if ( isset( $product['sku'] ) ) {
			update_post_meta( $product_id, '_sku', $product['sku'] );
		} else {
			update_post_meta( $product_id, '_sku', $product['id'] );
		}
		update_post_meta( $product_id, '_stock_status', 'instock' );
		update_post_meta( $product_id, '_currency', $product['listing_currency'] );

		if ( isset( $product['inventory'] ) ) {
			update_post_meta( $product_id, '_stock_status', 'instock' );
			update_post_meta( $product_id, '_manage_stock', 'yes' );
			update_post_meta( $product_id, '_stock', $product['inventory'] );
		} else {
			update_post_meta( $product_id, '_stock_status', 'outofstock' );
		}
		$reverb_price = get_option( 'ced_reverb_product_prices_api', array() );
		$price        = isset( $reverb_price[ $product['id'] ] ) ? $reverb_price[ $product['id'] ] : $product['price']['amount'] * 1.223803772;
		update_post_meta( $product_id, '_regular_price', $price );
		update_post_meta( $product_id, '_price', $price );

		if ( isset( $image_details ) ) {
			$this->create_product_images( $product_id, $image_details );
		}
		return;
	}


	/**
	 * To insert product images in the woocommerce with product
	 *
	 * @since    1.0.0
	 */

	public function create_product_images( $product_id, $images = array() ) {

		foreach ( $images as $key1 => $value1 ) {
			$image_url        = $value1['_links']['full']['href'];
			$image_name       = explode( '/', $image_url );
			$image_name       = $image_name[ count( $image_name ) - 1 ];
			$upload_dir       = wp_upload_dir();
			$image_url        = str_replace( 'https', 'http', $image_url );
			$image_data       = file_get_contents( $image_url );
			$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
			$filename         = basename( $unique_file_name );
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			file_put_contents( $file, $image_data );
			ob_clean();
			$wp_filetype = wp_check_filetype( $filename, null );
			$attachment  = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => sanitize_file_name( $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);
			$attach_id   = wp_insert_attachment( $attachment, $file, $product_id );
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			if ( 0 == $key1 ) {
				set_post_thumbnail( $product_id, $attach_id );
			} else {
				$image_ids[] = $attach_id;
			}
		}

		if ( ! empty( $image_ids ) ) {
			update_post_meta( $product_id, '_product_image_gallery', implode( ',', $image_ids ) );

		}
	}

	/**
	 * To insert product category in the woocommerce with product
	 *
	 * @since    1.0.0
	 */
	public function insert_product_category( $product_id, $listing_details ) {

		if ( isset( $listing_details['categories'] ) ) {
			$categoryPath = $listing_details['categories'];
		}
		$categoryPath = explode( '/', $categoryPath[0]['full_name'] );

		$parent_id = '';
		foreach ( $categoryPath as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$term = wp_insert_term(
				$value,
				'product_cat',
				array(
					'description' => $value,
					'parent'      => $parent_id,
				)
			);
			if ( isset( $term->error_data['term_exists'] ) ) {
				$term_id = $term->error_data['term_exists'];
			} elseif ( isset( $term['term_id'] ) ) {
				$term_id = $term['term_id'];
			}
			$term_ids[]  = $term_id;
			$category_id = ! empty( $categoryPath[0]['uuid'] ) ? $categoryPath[0]['uuid'] : '';
			$parent_id   = ! empty( $term_id ) ? $term_id : '';
		}
		wp_set_object_terms( $product_id, $term_ids, 'product_cat' );

	}

	public function ced_reverb_ship_order() {
		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$order_id           = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : false;
			$trackNumber        = isset( $_POST['trackNumber'] ) ? sanitize_text_field( $_POST['trackNumber'] ) : false;
			$shipping_providers = isset( $_POST['shipping_providers'] ) ? sanitize_text_field( $_POST['shipping_providers'] ) : false;
			$purchaseOrderId    = get_post_meta( $order_id, '_ced_reverb_order_id', true );
			if ( $order_id && $trackNumber ) {
				$shipping_data                      = array();
				$shipping_data['provider']          = $shipping_providers;
				$shipping_data['tracking_number']   = $trackNumber;
				$shipping_data['send_notification'] = 1;
				$shipping_data                      = json_encode( $shipping_data, true );

				$shipRequest = $this->ced_reverb_curl_instance->ced_reverb_request( 'my/orders/selling/' . $purchaseOrderId . '/ship', $shipping_data, '', 'POST' );
				update_post_meta( $order_id, '_reverb_umb_order_status', 'Shipped' );
				update_post_meta(
					$order_id,
					'ced_reverb_order_details',
					array(
						'trackingNo' => $trackNumber,
						'provider'   => $shipping_providers,
					)
				);
				echo json_encode(
					array(
						'trackingNo' => $trackNumber,
						'provider'   => $shipping_providers,
						'ordeid'     => $order_id,
						'status'     => '200',
					)
				);
				die;
			} else {
				echo json_encode( array( 'status' => 'Please fill in all the details' ) );
				die;
			}
		}
	}

	public function ced_reverb_filter_woocommerce_order_number( $order_id, $order ) {
		$use_reverb_order_no  = get_option( 'ced_reverb_set_reverbOrderNumber', '' );
		$_ced_reverb_order_id = get_post_meta( $order->get_id(), '_ced_reverb_order_id', true );
		if ( ! empty( $_ced_reverb_order_id ) && 'on' == $use_reverb_order_no ) {
			return $_ced_reverb_order_id;
		}
		return $order_id;
	}

	public function ced_reverb_copy_global(){

		$profileID = isset( $_POST['profileid'] ) ? $_POST['profileid'] : '';
		$reverb_profiles = get_option( 'ced_reverb_profiles_list', array() );
		$categoryid      = '';

		if ( ! empty( $reverb_profiles[ $profileID ] ) && is_array( $reverb_profiles ) ) {
			$category_id = $reverb_profiles[ $profileID ]['reverb_cat_id'];
		}

		$category_specificData = get_option( 'ced_reverb_profile_data', array() );

		$category_data = $category_specificData[$category_id];
		
		$data = json_decode($category_data, true);
		
		$reverb_category = $data['_umb_reverb_category'];

		$ced_reverb_global_settings = get_option( 'ced_reverb_global_settings', array() );

		$ced_reverb_global_settings = json_decode($ced_reverb_global_settings, true);


		$new_cat_specific = array();
		$new_cat_specific['_umb_reverb_category'] = $reverb_category;

		$new_cat_specific_data = array_merge($new_cat_specific, $ced_reverb_global_settings);

		$new_cat_specific_data = json_encode($new_cat_specific_data, true);

		$category_specificData[$category_id] = $new_cat_specific_data;

		update_option('ced_reverb_profile_data', $category_specificData);

		echo "done";
		die;

		




		//$category_data = $category_specificData[$category_id]);
		
		//$data = json_decode($category_data, true);
		// echo '<pre>';
		// print_r($category_data);
		// die;
		
		

	}

	public function ced_reverb_test_prepared_data_func(){

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );

		if(!empty($check_ajax)){


			$productID = isset($_POST['productID']) ? $_POST['productID'] : "";

			$prod_data = wc_get_product( $productID );

			$type = $prod_data->get_type();

			require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-product.php';

			$product_ob = new Ced_Reverb_Product();

			if('variable' == $type){

				

				$variations = $prod_data->get_available_variations();
				if(empty($variations)) {
					$variations = 'Profile Not Created for this Product';
					print_r($value);
					die();
				}

				$final_data_prepared = array();

				foreach ( $variations as $variation ) {

					$attributes   = $variation['attributes'];
					$variation_id = $variation['variation_id'];

					$final_data_prepared[] = $product_ob->getFormattedData( $variation_id, $attributes );


					
				}

				
				echo '<pre>';
				foreach ($final_data_prepared as $key => $value) {
						
					//$dates = $dates.$value.'<hr>';

					print_r($value);
					echo '<hr>';
				}

				
				die;
			}else{

				// require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-product.php';

				// $product_ob = new Ced_Reverb_Product();

				$prepared_data = $product_ob->getFormattedData($productID);
				if(empty($prepared_data)) {
					$prepared_data = 'Profile Not Created for this Product';
				}
				
				echo '<pre>';
				print_r($prepared_data);
				die;

			}

			echo "Something went wrong";

			die;


		}

	}

	public function ced_reverb_test_prepared_data_update_inventory_func(){

		$check_ajax = check_ajax_referer( 'ced-reverb-ajax-seurity-string', 'ajax_nonce' );

		if(!empty($check_ajax)){


			$productID = isset($_POST['productID']) ? $_POST['productID'] : "";

			$prod_data = wc_get_product( $productID );

			$type = $prod_data->get_type();

			require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-product.php';

			$product_ob = new Ced_Reverb_Product();

			if('variable' == $type){

				

				$variations = $prod_data->get_available_variations();

				$final_data_prepared = array();

				foreach ( $variations as $variation ) {

					$attributes   = $variation['attributes'];
					$variation_id = $variation['variation_id'];

					$final_data_prepared[] = $product_ob->ced_reverb_getFormattedDataForInventory( $variation_id, $attributes );


					
				}

				
				echo '<pre>';
				foreach ($final_data_prepared as $key => $value) {
						
					//$dates = $dates.$value.'<hr>';

					print_r($value);
					echo '<hr>';
				}

				
				die;
			}else{

				// require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-product.php';

				// $product_ob = new Ced_Reverb_Product();

				$prepared_data = $product_ob->ced_reverb_getFormattedDataForInventory($productID);
				
				echo '<pre>';
				print_r($prepared_data);
				die;

			}

			echo "Something went wrong";

			die;


		}

	}

	public function ced_reverb_product_update_on_updated_post_meta_func(){

		$data = get_option('ced_reverb_product_ids_to_update_by_post_meta', array() );

		if(!empty($data)){

			$slice = array_slice($data, 0, 5);
			foreach ($slice as $key => $value) {
				
				//$notice = $ced_upload_instance->update_inventory_price_temporary( $value, $seller_id );
				$this->ced_reverb_manager->prepareProductUpdateInventory( array( $value ) );

			}


			$slice1 = array_slice($data, 5);
			update_option('ced_reverb_product_ids_to_update_by_post_meta', $slice1);


		}



	}

	public function reverb_get_product_id_from_orders_created($order_id){
	    
	        $order = new WC_Order( $order_id );
            $items = $order->get_items();
				

				$art = get_option('ced_reverb_product_ids_to_update_by_post_meta', array());

				foreach ( $items as $item ) {
	                $product_name = $item['name'];
	                $product_id = $item['product_id'];
	                $art[] = $product_id;
	                //$product_variation_id = $item['variation_id'];
            	}

            	$art = array_unique($art);

            	update_option('ced_reverb_product_ids_to_update_by_post_meta', $art);
	    
	}

}
