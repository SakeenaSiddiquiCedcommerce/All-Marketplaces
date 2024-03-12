<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/public
 * @author     Cedcommerce <support@cedcommerce.com>
 */
class Etsy_Dokan_Integration_For_Woocommerc_Public {

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
		ob_start();		
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->loadDependency();
		$this->keyString    = 'ghvcvauxf2taqidkdx2sw4g4';
		$this->sharedString = '27u2kvhfmo';
		add_action( 'manage_edit-shop_order_columns', array( $this, 'ced_etsy_dokan_add_table_columns' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'ced_etsy_dokan_manage_table_columns' ), 10, 2 );
		add_action( 'wp_ajax_ced_etsy_dokan_auto_upload_categories', array( $this, 'ced_etsy_dokan_auto_upload_categories' ) , 11 , 3 );

	}

	public function loadDependency() {
		require_once CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/class-dokan-etsy.php';
		$this->CED_ETSY_Dokan_Manager = CED_ETSY_Dokan_Manager::get_instance();
	}

	public function ced_etsy_dokan_auto_upload_categories() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		$check_ajax = true;
		if ( $check_ajax ) {
			$sanitized_array        = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$woo_categories         = isset( $sanitized_array['categories'] ) ? json_decode( $sanitized_array['categories'], true ) : array();
			$de_shop_name              = isset( $sanitized_array['de_shop_name'] ) ? $sanitized_array['de_shop_name'] : '';
			$operation              = isset( $sanitized_array['operation'] ) ? sanitize_text_field( $sanitized_array['operation'] ) : 'save';
			$auto_upload_categories = get_option( 'ced_etsy_dokan_auto_upload_categories_' . $de_shop_name, array() );
			if ( 'save' == $operation ) {
				$auto_upload_categories = array_merge( $auto_upload_categories, $woo_categories );
				$message                = 'Category added in auto upload queue';
			} elseif ( 'remove' == $operation ) {
				$auto_upload_categories = array_diff( $auto_upload_categories, $woo_categories );
				$auto_upload_categories = array_values( $auto_upload_categories );
				$message                = 'Category removed from auto upload queue';
			}
			$auto_upload_categories = array_unique( $auto_upload_categories );
			update_option( 'ced_etsy_dokan_auto_upload_categories_' . $de_shop_name, $auto_upload_categories );
			echo json_encode(
				array(
					'status'  => 200,
					'message' => $message,
				)
			);
			wp_die();
		}
	}

	public function ced_etsy_dokan_add_table_columns( $columns ) {
		$modified_columns = array();
		foreach ( $columns as $key => $value ) {
			$modified_columns[ $key ] = $value;
			if ( 'order_number' == $key ) {
				$modified_columns['order_from_dokan_vendor'] = '<span title="Order source">Vendor Order source</span>';
			}
		}
		return $modified_columns;
	}


	public function ced_etsy_dokan_manage_table_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'order_from_dokan_vendor':
				$_ced_etsy_order_id = get_post_meta( $post_id, '_ced_etsy_dokan_order_id', true );
				if ( ! empty( $_ced_etsy_order_id ) ) {
					$etsy_icon = CED_ETSY_DOKAN_URL . 'public/images/etsy.png';
					echo '<p><img src="' . esc_url( $etsy_icon ) . '" height="35" width="60"></p>';
				}
		}
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
		 * defined in Etsy_Dokan_Integration_For_Woocommerc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Etsy_Dokan_Integration_For_Woocommerc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'ced-boot-css', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '2.0.0', 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/etsy-dokan-integration-for-woocommerc-public.css', array(), $this->version, 'all' );

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
		 * defined in Etsy_Dokan_Integration_For_Woocommerc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Etsy_Dokan_Integration_For_Woocommerc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// woocommerce style //
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_menu_styles' );
		wp_enqueue_style( 'woocommerce_admin_styles' );

		$params = array(
			/* translators: (%s): wc_get_price_decimal_separator */
				'i18n_mon_decimal_error'       => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
			'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
			'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),

			'mon_decimal_point'                => wc_get_price_decimal_separator(),
			'strings'                          => array(
				'import_products' => __( 'Import', 'woocommerce' ),
				'export_products' => __( 'Export', 'woocommerce' ),
			),
			'urls'                             => array(
				'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
				'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
			),
		);
		// woocommerce script //

		$suffix = '';
		wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
		wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );

		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
		wp_enqueue_script( 'woocommerce_admin' );

		$de_shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/etsy-dokan-integration-for-woocommerc-public.js', array( 'jquery' ), $this->version, false );
		$ajax_nonce = wp_create_nonce( 'ced-etsy-dokan-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
			'de_shop_name'=> $de_shop_name,
		);
		wp_localize_script( $this->plugin_name, 'ced_etsy_dokan_admin_obj', $localize_array );
	}

	/**
	 *********************************
	 * Add vender menus and submenus
	 *********************************
	 *
	 * @since    1.0.0
	 */

	public function ced_etsy_add_etsy_menu( $menus ) {

		$vendor_id = check_if_etsy_vendor();
		if ( ! $vendor_id ) {
			return $menus;
		}

		$ced_logo            = CED_ETSY_DOKAN_URL . 'public/images/logo1.png';
		$menus['ced_etsy'] = array(
			'title' => __( 'ETSY', 'woocommerce-etsy-integration' ),
			'icon'  => '<img class="fa fa-user class_ced_new" src=' . esc_url( $ced_logo ) . ' ></img>',
			'url'   => dokan_get_navigation_url( 'ced_etsy' ),
			'pos'   => 51,
		);
		return $menus;
	}


	public function ced_etsy_load_document( $query_etsy_vars ) {
		$query_etsy_vars['ced_etsy'] = 'ced_etsy';
		return $query_etsy_vars;
	}

	public function ced_etsy_landing_page( $query_etsy_vars ) {
		
		/*
		 * Before check here need to save permalink button.
		 *
		 */

		if ( isset( $query_etsy_vars['ced_etsy'] ) ) {
			require_once dirname( __FILE__ ) . '/partials/ced_etsy.php';
		}
	}

	public function ced_etsy_authorise_account() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		$check_ajax = true;
		if ( $check_ajax ) {
			$api_url = $this->CED_ETSY_Dokan_Manager->prepare_store_authorisation_url();
			echo json_encode(
				array(
					'status' => '200',
					'apiUrl' => $api_url,
				)
			);
			die;
		}
	}

	/**
	 * Woocommerce_Etsy_Integration_Admin ced_etsy_dokan_search_product_name.
	 *
	 * @since 1.0.0
	 */
	public function ced_etsy_dokan_search_product_name() {

		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		$check_ajax = true;
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
						$product_list .= '<li class="ced_etsy_dokan_searched_product" data-post-id="' . esc_attr( $data->ID ) . '">' . esc_html( __( $data->post_title, 'etsy-woocommerce-integration' ) ) . '</li>';
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
		 * Woocommerce_Etsy_Integration_Admin ced_etsy_get_product_metakeys.
		 *
		 * @since 1.0.0
		 */
	public function ced_etsy_get_product_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
			include_once CED_ETSY_DOKAN_DIRPATH . 'public/partials/ced-etsy-metakeys-list.php';
		}
	}

	/**
	 * Woocommerce_Etsy_Integration_Admin ced_etsy_process_metakeys.
	 *
	 * @since 1.0.0
	 */
	public function ced_etsy_process_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$metakey   = isset( $_POST['metakey'] ) ? sanitize_text_field( wp_unslash( $_POST['metakey'] ) ) : '';
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			if ( ! empty( $metakey ) ) {
				$added_meta_keys = get_option( 'ced_etsy_selected_metakeys', array() );
				if ( 'store' == $operation ) {
					$added_meta_keys[ $metakey ] = $metakey;
				} elseif ( 'remove' == $operation ) {
					unset( $added_meta_keys[ $metakey ] );
				}
				update_option( 'ced_etsy_selected_metakeys', $added_meta_keys );
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
			require CED_ETSY_DOKAN_DIRPATH . 'public/partials/marketplaces.php';
		}
	}

	public function ced_etsy_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'Etsy',
			'slug'            => 'woocommerce-etsy-integration',
			'menu_link'       => 'ced_etsy',
			'instance'        => $this,
			'function'        => 'ced_etsy_accounts_page',
			'card_image_link' => CED_ETSY_DOKAN_URL . 'public/images/etsy.png',
		);
		return $menus;
	}

	/**
	 * Ced Etsy Accounts Page
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_accounts_page() {

		$fileAccounts = CED_ETSY_DOKAN_DIRPATH . 'public/partials/ced-etsy-accounts.php';
		if ( file_exists( $fileAccounts ) ) {
			require_once $fileAccounts;
		}
	}


	/**
	 * Etsy Changing Account status
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_dokan_change_account_status() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$de_shop_name = isset( $_POST['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['de_shop_name'] ) ) : '';
			$status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active';
			$shops     = get_option( 'ced_etsy_dokan_details', array() );
			$shops[ $de_shop_name ]['details']['ced_shop_account_status'] = $status;
			update_option( 'ced_etsy_dokan_details', $shops );
			echo json_encode( array( 'status' => '200' ) );
			die;
		}
	}


	/**
	 * Woocommerce_Etsy_Integration_Admin ced_etsy_dokan_add_order_metabox.
	 *
	 * @since 1.0.0
	 */
	public function ced_etsy_dokan_add_order_metabox() {
		global $post;
		$order_from_dokan_vendor = get_post_meta( $post->ID, '_umb_dokan_etsy_marketplace', true );
		if ( 'etsy' == strtolower( $order_from_dokan_vendor ) ) {
			add_meta_box(
				'ced_etsy_dokan_manage_orders_metabox',
				__( 'Manage Vendor Marketplace Orders', 'woocommerce-etsy-integration' ) . wc_help_tip( __( 'Please send shipping confirmation.', 'woocommerce-etsy-integration' ) ),
				array( $this, 'ced_etsy_dokan_render_orders_metabox' ),
				'shop_order',
				'advanced',
				'high'
			);
		}
	}

	/**
	 * Woocommerce_Etsy_Integration_Admin ced_etsy_dokan_submit_shipment.
	 *
	 * @since 1.0.0
	 */
	public function ced_etsy_dokan_submit_shipment() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_etsy_tracking_code = isset( $_POST['ced_etsy_tracking_code'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_tracking_code'] ) ) : '';
			$ced_etsy_carrier_name  = isset( $_POST['ced_etsy_carrier_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_carrier_name'] ) ) : '';
			$order_id               = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			// $shop_name              = get_option( $order_id, 'ced_etsy_dokan_order_shop_name', true );
			// if ( empty( $shop_name ) ) {
			$shop_name = get_post_meta( $order_id, 'ced_etsy_dokan_order_shop_name', true );
			// }
			$_ced_etsy_order_id = get_post_meta( $order_id, '_ced_etsy_dokan_order_id', true );
			$saved_etsy_details = get_option( 'ced_etsy_dokan_details', array() );
			$shopDetails        = $saved_etsy_details[ $shop_name ];
			$vendor_id          = get_post_meta( $order_id, 'ced_etsy_order_vendor', true );
			$shop_id 		    = ced_etsy_dokan_get_shop_id( $shop_name, $vendor_id );
			$parameters         = array(
				'tracking_code' => $ced_etsy_tracking_code,
				'carrier_name'  => $ced_etsy_carrier_name,
			);
			/** Refresh token
			 *
			 * @since 2.0.0
			 */
			do_action( 'ced_etsy_dokan_refresh_token', $shop_name, $vendor_id );
			$action         = 'application/shops/' . $shop_id . '/receipts/' . $_ced_etsy_order_id . '/tracking';
			$e_shpng_tmplts = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, $action, 'PUT', array(), $parameters, $vendor_id );
			if ( isset( $response['receipt_id'] ) || isset( $response['Shipping_notification_email_has_already_been_sent_for_this_receipt_'] ) ) {
				update_post_meta( $order_id, '_etsy_dokan_umb_order_status', 'Shipped' );
				$_order = wc_get_order( $order_id );
				$_order->update_status( 'wc-completed' );
				echo json_encode(
					array(
						'status'  => 200,
						'message' => 'Shipment submitted successfully.',
					)
				);
				wp_die();
			} elseif ( is_array( $response ) ) {
				foreach ( $response as $error => $value ) {
					$message = isset( $error ) ? ucwords( str_replace( '_', ' ', $error ) ) : '';
					echo json_encode(
						array(
							'status'  => 400,
							'message' => $message,
						)
					);
					wp_die();
				}
			} else {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => 'Shipment not submitted.',
					)
				);
				wp_die();
			}
		}
	}


	/**
	 * Woocommerce_Etsy_Integration_Admin ced_etsy_dokan_render_orders_metabox.
	 *
	 * @since 1.0.0
	 */
	public function ced_etsy_dokan_render_orders_metabox() {
		global $post;
		$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';
		if ( ! is_null( $order_id ) ) {
			$order          = wc_get_order( $order_id );
			$e_order_status = $order->get_status();
			$template_path = CED_ETSY_DOKAN_DIRPATH . 'public/partials/order-template.php';
			if ( file_exists( $template_path ) ) {
				include_once $template_path;
			}
		}
	}

	/**
	 * Woocommerce_Etsy_Integration_Admin ced_etsy_email_restriction.
	 *
	 * @since 1.0.0
	 */
	public function ced_etsy_email_restriction( $enable = '', $order = array() ) {
		if ( ! is_object( $order ) ) {
			return $enable;
		}
		$order_id   = $order->get_id();
		$order_from_dokan_vendor = get_post_meta( $order_id, '_etsy_dokan_umb_order_status', true );
		if ( 'etsy' == strtolower( $order_from_dokan_vendor ) ) {
			$enable = false;
		}
		return $enable;
	}

	/**
	 * Marketplace
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_marketplace_to_be_logged( $marketplaces = array() ) {

		$marketplaces[] = array(
			'name'             => 'Etsy',
			'marketplace_slug' => 'etsy',
		);
		return $marketplaces;
	}

	/**
	 * Etsy Cron Schedules
	 *
	 * @since    1.0.0
	 */
	public function my_etsy_cron_schedules( $schedules ) {
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


	/**
	 * Etsy Fetch Next Level Category
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_dokan_fetch_next_level_category() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$store_category_id      = isset( $_POST['store_id'] ) ? sanitize_text_field( wp_unslash( $_POST['store_id'] ) ) : '';
			$etsy_category_name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$etsy_category_id       = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			$level                  = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';
			$next_level             = intval( $level ) + 1;
			$etsyCategoryList       = file_get_contents( CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/json/categoryLevel-' . $next_level . '.json' );
			$etsyCategoryList       = json_decode( $etsyCategoryList, true );
			$select_html            = '';
			$nextLevelCategoryArray = array();
			if ( ! empty( $etsyCategoryList ) ) {
				foreach ( $etsyCategoryList as $key => $value ) {
					if ( isset( $value['parent_id'] ) && $value['parent_id'] == $etsy_category_id ) {
						$nextLevelCategoryArray[] = $value;
					}
				}
			}
			if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {

				$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_etsy_level' . $next_level . '_category ced_etsy_dokan_select_category select_boxes_cat_map" name="ced_etsy_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '">';
				$select_html .= '<option value=""> --' . __( 'Select', 'woocommerce-etsy-integration' ) . '-- </option>';
				foreach ( $nextLevelCategoryArray as $key => $value ) {
					if ( ! empty( $value['name'] ) ) {
						$select_html .= '<option value="' . $value['id'] . ',' . $value['name'] . '">' . $value['name'] . '</option>';
					}
				}
				$select_html .= '</select></td>';
				echo json_encode( $select_html );
				wp_die();
			}else{
				wp_die();
			}
		}
	}

	/*
	*
	*Function for Fetching child categories for custom profile
	*
	*
	*/

	public function ced_etsy_dokan_fetch_next_level_category_add_profile() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$tableName              = $wpdb->prefix . 'ced_etsy_accounts';
			$etsy_store_id          = isset( $_POST['etsy_store_id'] ) ? sanitize_text_field( wp_unslash( $_POST['etsy_store_id'] ) ) : '';
			$etsy_category_name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$etsy_category_id       = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			$level                  = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';
			$next_level             = intval( $level ) + 1;
			$etsyCategoryList       = file_get_contents( CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/json/categoryLevel-' . $next_level . '.json' );
			$etsyCategoryList       = json_decode( $etsyCategoryList, true );
			$select_html            = '';
			$nextLevelCategoryArray = array();
			if ( ! empty( $etsyCategoryList ) ) {
				foreach ( $etsyCategoryList as $key => $value ) {
					if ( isset( $value['parent_id'] ) && $value['parent_id'] == $etsy_category_id ) {
						$nextLevelCategoryArray[] = $value;
					}
				}
			}
			if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {
				$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_etsy_level' . $next_level . '_category ced_etsy_dokan_select_category_on_add_profile  select_boxes_cat_map" name="ced_etsy_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-etsyStoreId="' . $etsy_store_id . '">';
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


	/**
	 * Etsy Mapping Categories to WooStore
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_dokan_map_categories_to_store() {

		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array             = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$etsy_category_array         = isset( $sanitized_array['etsy_category_array'] ) ? $sanitized_array['etsy_category_array'] : '';
			$store_category_array        = isset( $sanitized_array['store_category_array'] ) ? $sanitized_array['store_category_array'] : '';
			$etsy_category_name          = isset( $sanitized_array['etsy_category_name'] ) ? $sanitized_array['etsy_category_name'] : '';
			$etsy_store_id               = isset( $_POST['storeName'] ) ? sanitize_text_field( wp_unslash( $_POST['storeName'] ) ) : '';
			$etsy_saved_category         = get_option( 'ced_etsy_saved_category', array() );
			$alreadyMappedCategories     = array();
			$alreadyMappedCategoriesName = array();
			$etsyMappedCategories        = array_combine( $store_category_array, $etsy_category_array );
			$etsyMappedCategories        = array_filter( $etsyMappedCategories );
			$alreadyMappedCategories     = get_option( 'ced_dokan_etsy_mapped_categories_' . $etsy_store_id . '_' . get_current_user_id(), array() );
			if ( is_array( $etsyMappedCategories ) && ! empty( $etsyMappedCategories ) ) {
				foreach ( $etsyMappedCategories as $key => $value ) {
					$alreadyMappedCategories[ $etsy_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_dokan_etsy_mapped_categories_' . $etsy_store_id . '_' . get_current_user_id(), $alreadyMappedCategories );
			$etsyMappedCategoriesName    = array_combine( $etsy_category_array, $etsy_category_name );
			$etsyMappedCategoriesName    = array_filter( $etsyMappedCategoriesName );
			$alreadyMappedCategoriesName = get_option( 'ced_dokan_etsy_mapped_categories_name_' . $etsy_store_id . '_' . get_current_user_id(), array() );
			if ( is_array( $etsyMappedCategoriesName ) && ! empty( $etsyMappedCategoriesName ) ) {
				foreach ( $etsyMappedCategoriesName as $key => $value ) {
					$alreadyMappedCategoriesName[ $etsy_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_dokan_etsy_mapped_categories_name_' . $etsy_store_id . '_' . get_current_user_id(), $alreadyMappedCategoriesName );
			$this->CED_ETSY_Dokan_Manager->ced_etsy_createAutoProfiles( $etsyMappedCategories, $etsyMappedCategoriesName, $etsy_store_id );
			wp_die();
		}
	}

	/**
	 * Etsy Inventory Scheduler
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_inventory_schedule_manager() {

		$hook    = current_action();
		$shop_id = str_replace( 'ced_etsy_inventory_scheduler_job_', '', $hook );
		$shop_id = trim( $shop_id );

		if ( empty( $shop_id ) ) {
			$shop_id = get_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id() , '' );
		}

		$products_to_sync = get_option( 'ced_etsy_chunk_products', array() );
		if ( empty( $products_to_sync ) ) {
			$store_products   = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'meta_query'  => array(
						array(
							'key'     => '_ced_etsy_listing_id_' . $shop_id,
							'compare' => 'EXISTS',
						),
					),
				)
			);
			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$products_to_sync = array_chunk( $store_products, 10 );

		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			$fileProducts = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/etsyDokanProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}

			$etsyProductInstance = Class_Ced_Etsy_Dokan_Products::get_instance();
			$getDokanOrders          = $etsyProductInstance->prepareDokanDataForUpdatingInventory( $products_to_sync[0], $shop_id, true );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_etsy_chunk_products', $products_to_sync );
		}
	}


	public function ced_etsy_auto_upload_products() {
		$de_shop_name     = str_replace( 'ced_etsy_auto_upload_products_', '', current_action() );
		$de_shop_name     = trim( $de_shop_name );
		$product_chunk = get_option( 'ced_etsy_product_upload_chunk_' . $de_shop_name, array() );
		if ( empty( $product_chunk ) ) {
			$woo_categories = get_option( 'ced_etsy_dokan_auto_upload_categories_' . $de_shop_name, array() );
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
									'key'     => '_ced_etsy_listing_id_' . $de_shop_name,
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
				$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpload( $product_id, $de_shop_name );
			}
			unset( $product_chunk[0] );
			$product_chunk = array_values( $product_chunk );
			update_option( 'ced_etsy_product_upload_chunk_' . $de_shop_name, $product_chunk );
		}
	}


	/**
	 * Etsy Sync existing products scheduler
	 *
	 * @since    1.0.5
	 */
	public function ced_etsy_sync_existing_products() {

		$hook               = current_action();
		$de_shop_name       = str_replace( 'ced_etsy_sync_existing_products_job_', '', $hook );
		$de_shop_name       = trim( $de_shop_name );
		$offset             = get_option( 'ced_etsy_get_offset', '' );
		if ( empty( $offset ) ) {
			$offset = 0;
		}
		$params   = array(
			'offset' => $offset,
			'limit'  => 25,
		);

		$vendor_id      = get_current_user_id();
		$shop_id 		= ced_etsy_dokan_get_shop_id( $de_shop_name, $vendor_id );
		/** Refresh token
		 *
		 * @since 2.0.0
		 */
		do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name, $vendor_id );
		$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name, "application/shops/{$shop_id}/listings", 'GET', array(), array(), $vendor_id );
		if ( isset( $response ) ) {
			foreach ( $response['listing_id'] as $key => $value ) {
				$sku = isset( $value['sku'][0] ) ? $value['sku'][0] : '';
				if ( ! empty( $sku ) ) {
					$product_id = wc_get_product_id_by_sku( $sku );
					if ( $product_id ) {
						$_product = wc_get_product( $product_id );
						if ( 'variation' == $_product->get_type() ) {
							update_post_meta( $_product->get_parent_id(), '_CED_ETSY_DOKAN_URL_' . $de_shop_name, $value['url'] );
							update_post_meta( $_product->get_parent_id(), '_ced_etsy_listing_id_' . $de_shop_name, $value['listing_id'] );
						} else {
							update_post_meta( $product_id, '_CED_ETSY_DOKAN_URL_' . $de_shop_name, $value['url'] );
							update_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, $value['listing_id'] );
						}
					}
				}
			}
			if ( isset( $response['pagination']['next_offset'] ) && ! empty( $response['pagination']['next_offset'] ) ) {
				$next_offset = $response['pagination']['next_offset'];
			} else {
				$next_offset = 0;
			}
			update_option( 'ced_etsy_get_offset', $next_offset );
		} else {
			update_option( 'ced_etsy_get_offset', 0 );
		}
	}

	/**
	 * Etsy Order Scheduler
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_order_schedule_manager() {
		$hook    = current_action();
		$shop_id = str_replace( 'ced_etsy_order_scheduler_job_', '', $hook );
		$shop_id = trim( $shop_id );
		if ( empty( $shop_id ) ) {
			$shop_id = get_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id() , '' );
		}
		$fileOrders = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/etsyDokanOrders.php';
		if ( file_exists( $fileOrders ) ) {
			require_once $fileOrders;
		}
		$etsyOrdersInstance = Class_Ced_Etsy_Orders::get_instance();
		$vendor_id          = get_current_user_id();
		$getDokanOrders     = $etsyOrdersInstance->getDokanOrders( $shop_id, $vendor_id );
		// if ( ! empty( $getDokanOrders ) ) {
		// 	$createOrder = $etsyOrdersInstance->createDokanLocalOrder( $getDokanOrders, $shop_id );
		// }
	}

	/**
	 * Etsy Fetch Orders
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_dokan_get_orders() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id        = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$isShopInActive = ced_etsy_dokan_inactive_shops( $shop_id );
			if ( $isShopInActive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'woocommerce-etsy-integration'
						),
					)
				);
				die;
			}

			$fileOrders = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/etsyDokanOrders.php';
			if ( file_exists( $fileOrders ) ) {
				require_once $fileOrders;
			}
			$vendor_id          = get_current_user_id();
			$etsyOrdersInstance = Class_Ced_Etsy_Orders::get_instance();
			$getDokanOrders     = $etsyOrdersInstance->getDokanOrders( $shop_id, $vendor_id );
			// if ( ! empty( $getDokanOrders ) ) {
			// 	$createOrder = $etsyOrdersInstance->createDokanLocalOrder( $getDokanOrders, $shop_id );
			// }
		}
	}

	/**
	 * Etsy Profiles List on popup
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_dokan_profiles_on_pop_up() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$store_id  = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$vendor_id = get_current_user_id();
			$prodId    = isset( $_POST['prodId'] ) ? sanitize_text_field( wp_unslash( $_POST['prodId'] ) ) : '';
			global $wpdb;
			$profiles = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `de_shop_name` = %s AND `vendor_id` = %s", $store_id, $vendor_id ), 'ARRAY_A' );
			?>
			<div class="ced_etsy_profile_popup_content">
				<div id="profile_pop_up_head_main">
					<h2><?php esc_html_e( 'CHOOSE PROFILE FOR THIS PRODUCT', 'woocommerce-etsy-integration' ); ?></h2>
					<div class="ced_etsy_profile_popup_close">X</div>
				</div>
				<div id="profile_pop_up_head"><h3><?php esc_html_e( 'Available Profiles', 'woocommerce-etsy-integration' ); ?></h3></div>
				<div class="ced_etsy_profile_dropdown">
					<select name="ced_etsy_dokan_profile_selected_on_popup" class="ced_etsy_dokan_profile_selected_on_popup">
						<option class="profile_options" value=""><?php esc_html_e( '---Select Profile---', 'woocommerce-etsy-integration' ); ?></option>
						<?php
						foreach ( $profiles as $key => $value ) {
							echo '<option  class="profile_options" value="' . esc_html( $value['id'] ) . '">' . esc_html( $value['profile_name'] ) . '</option>';
						}
						?>
					</select>
				</div>	
				<div id="save_profile_through_popup_container">
					<button data-prodId="<?php echo esc_html( $prodId ); ?>" class="ced_etsy_custom_button" id="save_etsy_dokan_profile_through_popup"  data-shopid="<?php echo esc_html( $store_id ); ?>"><?php esc_html_e( 'Assign Profile', 'woocommerce-etsy-integration' ); ?></button>
				</div>
			</div>
			<?php
			wp_die();
		}
	}

	/**
	 * Etsy Refreshing Categories
	 *
	 * @since    1.0.0
	 */
	public function ced_etsy_category_refresh() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$de_shop_name      = isset( $_POST['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['de_shop_name'] ) ) : '';
			$isShopInActive = ced_etsy_dokan_inactive_shops( $de_shop_name );
			if ( $isShopInActive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'woocommerce-etsy-integration'
						),
					)
				);
				die;
			}
			$file = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/etsyDokanCategory.php';
			if ( ! file_exists( $file ) ) {
				return;
			}
			require_once $file;
			$etsyCategoryInstance = Class_Ced_Etsy_Category::get_instance();
			$fetchedCategories    = $etsyCategoryInstance->getEtsyCategories( $de_shop_name );
			if ( $fetchedCategories ) {
				$categories = $this->CED_ETSY_Dokan_Manager->StoreCategories( $fetchedCategories, true );
				echo json_encode( array( 'status' => 200 ) );
				wp_die();
			} else {
				echo json_encode( array( 'status' => 400 ) );
				wp_die();
			}
		}
	}

	/**
	 * Etsy Save profile On Product level
	 *
	 * @since    1.0.0
	 */
	public function save_etsy_dokan_profile_through_popup() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shopid     = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$prodId     = isset( $_POST['prodId'] ) ? sanitize_text_field( wp_unslash( $_POST['prodId'] ) ) : '';
			$profile_id = isset( $_POST['profile_id'] ) ? sanitize_text_field( wp_unslash( $_POST['profile_id'] ) ) : '';
			if ( '' == $profile_id ) {
				echo 'null';
				wp_die();
			}

			update_post_meta( $prodId, 'ced_etsy_dokan_profile_assigned' . $shopid, $profile_id );
		}
	}

	/**
	 * ******************************************************************
	 * Function to Delete for mapped profiles in the profile-view page
	 * ******************************************************************
	 *
	 *  @since version 1.0.8.
	 */
	public function ced_esty_delete_mapped_profiles() {

		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$profile_id = isset( $_POST['profile_id'] ) ? sanitize_text_field( $_POST['profile_id'] ) : '';
			$de_shop_name  = isset( $_POST['de_shop_name'] ) ? sanitize_text_field( $_POST['de_shop_name'] ) : '';
			$vendor_id  = get_current_user_id();
			$tableName  = $wpdb->prefix . 'ced_etsy_dokan_profiles';
			$result     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `de_shop_name`= %d AND `vendor_id` = %s ", $de_shop_name, $vendor_id ), 'ARRAY_A' );
			foreach ( $result as $key => $value ) {
				if ( $value['id'] === $profile_id ) {
					$wpdb->query(
						$wpdb->prepare(
							" DELETE FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE 
							`id` = %s AND de_shop_name = %s AND `vendor_id` = %s",
							$value['id'],
							$de_shop_name,
							$vendor_id
						)
					);
					echo json_encode(
						array(
							'status'  => 200,
							'message' => __(
								'Profile Deleted Successfully !',
								'woocommerce-etsy-integration'
							),
						)
					);
				}
			}
			die;
		}
	}

	/**
	 * *********************************************************************
	 * Include product settings template in the Etsy->Settings Tab.
	 * *********************************************************************
	 */
	public function ced_etsy_dokan_render_product_settings_in_setting_tab() {

		$Settings_file = CED_ETSY_DOKAN_DIRPATH . 'public/pages/setting-pages/ced-etsy-product-upload-settings.php';
		if ( file_exists( $Settings_file ) ) {
			require_once $Settings_file;
		}
	}


	/**
	 * ****************************************************************
	 * Include Order  settings template in the Etsy->Settings Tab.
	 * ****************************************************************
	 */
	public function ced_etsy_dokan_render_order_settings_in_setting_tab() {

		$Settings_file = CED_ETSY_DOKAN_DIRPATH . 'public/pages/setting-pages/ced-etsy-order-settings.php';
		if ( file_exists( $Settings_file ) ) {
			require_once $Settings_file;
		}
	}

	/**
	 * ***************************************************************
	 * Include product Scheduler  template in the Etsy->Settings Tab.
	 * ***************************************************************
	 */
	public function ced_etsy_dokan_render_shedulers_settings_in_setting_tab() {

		$Settings_file = CED_ETSY_DOKAN_DIRPATH . 'public/pages/setting-pages/ced-etsy-scheduler-settings.php';
		if ( file_exists( $Settings_file ) ) {
			require_once $Settings_file;
		}
	}

	/**
	 * **************************************************************
	 * Include Shipping Profiles template in the Etsy->Settings Tab.
	 * **************************************************************
	 */
	public function ced_etsy_dokan_render_shipping_profiles_in_setting_tab() {

		$Shipping_profile_file = CED_ETSY_DOKAN_DIRPATH . 'public/pages/setting-pages/ced-etsy-shipping-profiles.php';
		if ( file_exists( $Shipping_profile_file ) ) {
			require_once $Shipping_profile_file;
		}
	}
	/**
	 * *************************************************************
	 * Include Payment Methods  template in the Etsy->Settings Tab.
	 * *************************************************************
	 */
	public function ced_etsy_render_product_configuration_in_setting_tab() {

		$product_config_page = CED_ETSY_DOKAN_DIRPATH . 'public/pages/setting-pages/ced-etsy-product-configuration.php';
		if ( file_exists( $product_config_page ) ) {
			require_once $product_config_page;
		}
	}

	/**
	 * **********************************************************
	 * Include Shop section template in the Etsy->Settings Tab.
	 * **********************************************************
	 */
	public function ced_etsy_render_meta_key_settings_in_setting_tab() {

		$Settings_file = require_once CED_ETSY_DOKAN_DIRPATH . 'public/pages/setting-pages/ced-etsy-metakeys-template.php';
		if ( file_exists( $Settings_file ) ) {
			require_once $Settings_file;
		}
	}

	/**
	 * **********************************************************
	 * Woocommerce_Etsy_Integration_Admin ced_Etsy_save_meta_data
	 * **********************************************************
	 *
	 * @since 1.0.0
	 */
	
	public function ced_etsy_save_meta_data( $post_id = '' ) {

		if ( empty( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) && false ) {
			$unused = true;
		}
		if ( isset( $_POST['ced_etsy_data'] ) ) {
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			if ( ! empty( $sanitized_array ) ) {
				foreach ( $sanitized_array['ced_etsy_data'] as $id => $value ) {
					foreach ( $value as $meta_key => $meta_val ) {
						update_post_meta( $id, $meta_key, $meta_val );
					}
				}
			}
		}
	}

	/**
	 * **************************************************************
	 * Woocommerce_Etsy_Integration_Admin ced_Etsy_save_meta_data
	 * **************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_add_etsy_operations( $operations ) {
		
		$vendor_id = check_if_etsy_vendor();		
		if ( ! $vendor_id ) {
			return $operations;
		}
		
		$operations['upload_on_etsy']           = 'Upload to etsy';
		$operations['update_on_etsy']           = 'Update to etsy';
		$operations['update_image_on_etsy']     = 'Update Image to etsy';
		$operations['update_inventory_on_etsy'] = 'Update Inventory to etsy';
		$operations['remove_from_etsy']         = 'Remove from etsy';
		return $operations;
	}

	/**
	 * **************************************************************
	 * Woocommerce_Etsy_Integration_Admin ced_Etsy_save_meta_data
	 * **************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_add_filters() {
		// require_once CED_ETSY_DOKAN_DIRPATH . 'public/partials/ced-etsy-filter-template.php';
	}

	/**
	 * **************************************************************
	 * Woocommerce_Etsy_Integration_Admin ced_Etsy_save_meta_data
	 * **************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_get_filtered_data() {
		if ( isset( $_POST['product_status_filter'] ) && $_POST['product_status_filter'] == 'ok' ) {
			if ( isset( $_POST['product_status'] ) && $_POST['product_status'] != '-1' ) {
				$meta_query    = array();
				$productStatus = isset( $_POST['product_status'] ) ? $_POST['product_status'] : '';
				if ( $productStatus == 'uploaded_on_etsy' ) {
					$meta_query[] = array(
						'key'     => '_ced_etsy_listing_id_' . get_etsy_de_shop_name(),
						'compare' => 'EXISTS',
					);
				} elseif ( $productStatus == 'notuploaded_on_etsy' ) {
					$meta_query = array(
						array(
							'key'     => '_ced_etsy_listing_id_' . get_etsy_de_shop_name(),
							'compare' => 'NOT EXISTS',
						),
					);
				}
				if ( ! empty( $meta_query ) ) {
					$arguements['meta_query'] = $meta_query;
				}
			}
		}
		return $arguements;
	}

	/**
	 * *************************************************************************
	 *  Showing bulk action and preparing message for show on the Product Page
	 * *************************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_perform_bulk_actions() {

		if ( ! isset( $_POST['status'] ) || ! isset( $_POST['bulk_products'] ) ) {
			return;
		}
		$status   = $_POST['status'];
		$products = $_POST['bulk_products'];
		$de_shop_name  = get_etsy_de_shop_name();
		$notifications = array() ;
		if ( $status === '-1' ) {
			return;
		}
		foreach ( $products as $product_id ) {
			$already_uploaded = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, true );
			// $already_uploaded = false;
			if ( $status === 'upload_on_etsy' ) {
				if ( $already_uploaded ) {
					$response['msg']   = 'Product ' . $product_id . ' Already Uploaded';
					$response['class'] = 'response_error';
				} else {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpload( $product_id, $de_shop_name );
					if ( isset( $response['listing_id'] ) ) {
						$response['msg'] = $response['title'] . 'Uploaded Successfully';
						$response['class'] = 'response_success';
					}else{
						$response['msg']   = isset( $response['error'] ) ? $response['error'] : 'Product was not uploaded successfully';
						$response['class'] = 'response_error';
					}
				}
			} elseif ( $status === 'update_on_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpdate( $product_id, $de_shop_name );
					if ( isset( $response['status'] ) && 200 == $response['status'] ) {
						$response['msg'] = $response['message'];
						$response['class'] = 'response_success';
					}else{
						$response['msg']   = isset( $response['message'] ) ? $response['message'] : 'Product was not updated successfully';
						$response['class'] = 'response_error';
					}
				} else {
					$response['msg']   = 'Product ' . $response['error'] . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			} elseif ( $status === 'update_inventory_on_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpdateInventory( $product_id, $de_shop_name );
					if ( isset( $response['status'] ) && 200 == $response['status'] ) {
						$response['msg'] = $response['message'];
						$response['class'] = 'response_success';
					}else{
						$response['msg']   = isset( $response['message'] ) ? $response['message'] : 'Product Image was not updated successfully!';
						$response['class'] = 'response_error';
					}
				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			} elseif ( $status === 'update_image_on_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->ced_update_images_on_etsy_dokan( $product_id, $de_shop_name );
					if ( isset( $response['status'] ) && 200 == $response['status'] ) {
						$response['msg'] = $response['message'];
						$response['class'] = 'response_success';
					}else{
						$response['msg']   = isset( $response['message'] ) ? $response['message'] : 'Product Image was not updated successfully!';
						$response['class'] = 'response_error';
					}
				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			} elseif ( $status === 'remove_from_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForDelete( $product_id, $de_shop_name );
					if ( isset( $response['results'] ) ) {
						$response['msg'] = $response['title'] . ' Deleted Successfully';
						$response['class'] = 'response_success';
					}else{
						$response['msg']   = isset( $response['message'] ) ? $response['message'] : 'Product was not deleted successfully!';
						$response['class'] = 'response_error';
					}
				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			}
			$notifications[ get_current_user_id() ][ $product_id ] = $response;
		}

		update_option( 'ced_etsy_notifications', $notifications );
		do_action( 'ced_dokan_products_args', $_POST, 'etsy' );
		wp_redirect( dokan_get_navigation_url( 'products' ) );
		exit;
	}

	/**
	 * ***********************************************************************
	 * Showing bulk action and preparing message for show on the Product Page
	 * ***********************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_perform_bulk_actions_custom( $POST = array(), $marketplace ) {
		
		if ( $marketplace == 'etsy' ) {
			return;
		}
		if ( ! isset( $POST['status'] ) || ! isset( $POST['bulk_products'] ) ) {
			return;
		}

		$status   = $POST['status'];
		$products = $POST['bulk_products'];
		$de_shop_name  = get_etsy_de_shop_name();

		if ( $status === '-1' ) {
			return;
		}

		foreach ( $products as $product_id ) {
			$already_uploaded = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, true );
			if ( $status === 'upload_on_etsy' ) {
				if ( $already_uploaded ) {
					$response['msg']   = 'Product ' . $product_id . ' Already Uploaded';
					$response['class'] = 'response_error';
				} else {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpload( $product_id, $de_shop_name );
					if ( isset( $response['listing_id'] ) ) {
						$response['msg'] = $response['title'] . ' Uploaded Successfully';
						$response['class'] = 'response_success';
					}
				}
			} elseif ( $status === 'update_on_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpdate( $product_id, $de_shop_name );
					if ( isset( $response['listing_id'] ) ) {
						$response['msg'] = $response['title'] . ' Updated Successfully';
						$response['class'] = 'response_success';
					}

				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			} elseif ( $status === 'update_inventory_on_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForUpdateInventory( $product_id, $de_shop_name );
					if ( isset($response['results'] ) ) {
						$response['msg'] = $response['title'] . ' Inventory Updated Successfully';
						$response['class'] = 'response_success';
					}
				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			} elseif ( $status === 'update_image_on_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->ced_update_images_on_etsy_dokan( $product_id, $de_shop_name );
					if ( isset( $response['listing_id'] ) ) {
						$response['msg'] = $response['title'] . ' Image Updated Successfully';
						$response['class'] = 'response_success';
					}
				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			} elseif ( $status === 'remove_from_etsy' ) {
				if ( $already_uploaded ) {
					$response = $this->CED_ETSY_Dokan_Manager->prepareProductHtmlForDelete( $product_id, $de_shop_name );
					if ( isset( $response['results'] ) ) {
						$response['msg'] = $response['title'] . ' Deleted Successfully';
						$response['class'] = 'response_success';
					}
				} else {
					$response['msg']   = 'Product ' . $product_id . ' Not Found On etsy';
					$response['class'] = 'response_error';
				}
			}
			$notifications[get_current_user_id()][ $product_id ] = $response;
		}
		update_option( 'ced_etsy_notifications', $notifications );
	}

	/**
	 * **************************************************************
	 * Prepared Notification showing here on Product page of vendor
	 * **************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_show_notifications() {

		$vendor_id = check_if_etsy_vendor();
		if ( ! $vendor_id ) {
			return;
		}

		$notifications = get_option( 'ced_etsy_notifications', array() );
		update_option( 'ced_etsy_notifications', array() );
		$notifications = isset( $notifications[ get_current_user_id() ] ) ? $notifications[ get_current_user_id() ] : array();
		if ( ! empty( $notifications ) ) {
			echo '<div>Recent Notifications</div>';
			echo "<div class='ced_etsy_notification_wrapper'>";
			foreach ( $notifications as $product_id => $response ) {
				$message = isset( $response['msg'] ) ? $response['msg'] : '';
				$class   = $response['class'];
				echo '<div class=' . $class . ' >' . $product_id . ' - ' . $message . '</div>';
			}
			echo '</div>';
		}
	}

	/**
	 * **************************************************************
	 * Woocommerce_Etsy_Integration_Admin ced_Etsy_save_meta_data
	 * **************************************************************
	 *
	 * @since 1.0.0
	 */

	public function ced_etsy_dokan_fetch_orders_button() {

		$vendor_id = check_if_etsy_vendor();
		if ( ! $vendor_id ) {
			return;
		}

		if ( ! isset( $_GET['order_id'] ) ) {
			?>
			<div class="" align="right">
				<button name="ced_etsy_fetch_etsy_orders" class="ced_etsy_custom_button" id="ced_etsy_dokan_fetch_orders" data-id=<?php echo get_etsy_de_shop_name(); ?>>Fetch etsy Orders</button>
			</div>
			<?php
		}
	}


	public function ced_etsy_dokan_delete_account() {
		$check_ajax = check_ajax_referer( 'ced-etsy-dokan-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id        = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$etsy_dokan_account_list = get_option( 'ced_etsy_dokan_details' ,'' );
			if ( isset( $etsy_dokan_account_list[get_current_user_id()][$shop_id] ) ) {
				unset($etsy_dokan_account_list[get_current_user_id()][$shop_id]);
				update_option( 'ced_etsy_dokan_details', $etsy_dokan_account_list );
			}
				wp_die();
		}
	}
}