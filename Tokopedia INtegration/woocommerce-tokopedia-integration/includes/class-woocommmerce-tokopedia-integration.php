<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/includes
 */
class Woocommmerce_Tokopedia_Integration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @var      Woocommmerce_Tokopedia_Integration_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOCOMMMERCE_TOKOPEDIA_INTEGRATION_VERSION' ) ) {
			$this->version = WOOCOMMMERCE_TOKOPEDIA_INTEGRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommmerce-tokopedia-integration';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommmerce_Tokopedia_Integration_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommmerce_Tokopedia_Integration_I18n. Defines internationalization functionality.
	 * - Woocommmerce_Tokopedia_Integration_Admin. Defines all hooks for the admin area.
	 * - Woocommmerce_Tokopedia_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommmerce-tokopedia-integration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommmerce-tokopedia-integration-admin.php';
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommmerce-tokopedia-integration-public.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommmerce-tokopedia-integration-loader.php';

		$this->loader = new Woocommmerce_Tokopedia_Integration_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommmerce_Tokopedia_Integration_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommmerce_Tokopedia_Integration_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {
		global $wpdb;
		$plugin_admin = new Woocommmerce_Tokopedia_Integration_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		/* ADD MENUS AND SUBMENUS */
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_tokopedia_add_menus', 23 );
		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_tokopedia_marketplaces_menus_to_array', 15 );
		$this->loader->add_filter( 'ced_marketplaces_logged_array', $plugin_admin, 'ced_tokopedia_marketplace_to_be_logged' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_change_account_status', $plugin_admin, 'ced_tokopedia_change_account_status' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_authorize_account', $plugin_admin, 'ced_tokopedia_authorize_account' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_fetch_next_level_category', $plugin_admin, 'ced_tokopedia_fetch_next_level_category' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_map_categories_to_store', $plugin_admin, 'ced_tokopedia_map_categories_to_store' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_process_bulk_action', $plugin_admin, 'ced_tokopedia_process_bulk_action' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_profiles_on_pop_up', $plugin_admin, 'ced_tokopedia_profiles_on_pop_up' );
		$this->loader->add_filter( 'wp_ajax_save_tokopedia_profile_through_popup', $plugin_admin, 'save_tokopedia_profile_through_popup' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_preview_product_detail', $plugin_admin, 'ced_tokopedia_preview_product_detail' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_get_orders', $plugin_admin, 'ced_tokopedia_get_orders' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_category_refresh', $plugin_admin, 'ced_tokopedia_category_refresh' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_search_product_name', $plugin_admin, 'ced_tokopedia_search_product_name' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_get_product_metakeys', $plugin_admin, 'ced_tokopedia_get_product_metakeys' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_process_metakeys', $plugin_admin, 'ced_tokopedia_process_metakeys' );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'my_tokopedia_cron_schedules' );
		$this->loader->add_action( 'wp_ajax_ced_tokopedia_fetch_next_level_category_add_profile', $plugin_admin, 'ced_tokopedia_fetch_next_level_category_add_profile' );
		$this->loader->add_filter( 'wp_ajax_ced_tokopedia_submit_shipment', $plugin_admin, 'ced_tokopedia_submit_shipment' );

		// global $wpdb;
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$shops     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_accounts" ), 'ARRAY_A' );
// 		$shops     = $shops[0];
		// $shop_name = trim( $shops['name'] );
		//print_r($shops);
		// die();
		$is_active = $shops['account_status'];
		if ( $shops ) {
			foreach($shops as $shop_data) {
				$shop_name = $shop_data['shop_id'];
				$this->loader->add_action( 'ced_tokopedia_update_token_hourly_' . $shop_name , $plugin_admin, 'ced_tokopedia_update_token_hourly_scheduler' );
				// $this->loader->add_action( 'ced_tokopedia_register_ip_white_list_' . $shop_name , $plugin_admin, 'ced_tokopedia_register_ip_white_list' );
				$this->loader->add_action( 'ced_tokopedia_auto_upload_category_schedule_job_' . $shop_name , $plugin_admin, 'ced_tokopedia_auto_upload_category_schedule_manager' );			
				$this->loader->add_action( 'ced_tokopedia_inventory_scheduler_job_' . $shop_name, $plugin_admin, 'ced_tokopedia_inventory_schedule_manager' );
				$this->loader->add_action( 'ced_tokopedia_order_scheduler_job_' . $shop_name, $plugin_admin, 'ced_tokopedia_order_schedule_manager' );
				$this->loader->add_action( 'ced_tokopedia_auto_upload_schedule_job_' . $shop_name, $plugin_admin, 'ced_tokopedia_auto_upload_schedule_manager' );
				$this->loader->add_action( 'ced_tokopedia_sync_existing_products_' . $shop_name, $plugin_admin, 'ced_tokopedia_sync_existing_products' );
			}
		}
		$order_status = array(
			'new_order',
			'customer_processing_order',
			'cancelled_order',
			'customer_completed_order',
			'customer_on_hold_order',
			'customer_refunded_order',
			'customer_failed_order',
		);
		foreach ( $order_status as $key => $status ) {
			$this->loader->add_filter( 'woocommerce_email_enabled_' . esc_attr( $status ), $plugin_admin, 'ced_tokopedia_email_restriction', 10, 2 );
		}

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ced_tokopedia_add_order_metabox' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ced_tokopedia_set_schedules' );
		 if( isset( $_GET['page'] ) && $_GET['page'] == "ced_tokopedia" ) {
			//$this->loader->add_action( 'admin_init', $plugin_admin, 'ced_tokopedia_sync_existing_products' );
		 }
		//$this->loader->add_action( 'ced_tokopedia_auto_upload_schedule_job_shopName', $plugin_admin, 'ced_tokopedia_auto_upload_schedule_manager' );
		// $this->loader->add_action( 'ced_tokopedia_update_token_hourly_shopName', $plugin_admin, 'ced_tokopedia_update_token_hourly_scheduler' );
		//$this->loader->add_action( 'ced_tokopedia_auto_upload_category_schedule_job_shopName', $plugin_admin, 'ced_tokopedia_auto_upload_category_schedule_manager' );
		//$this->loader->add_action( 'ced_tokopedia_inventory_scheduler_job_shopName', $plugin_admin, 'ced_tokopedia_inventory_schedule_manager' );
		//$this->loader->add_action( 'ced_tokopedia_order_scheduler_job_shopName', $plugin_admin, 'ced_tokopedia_order_schedule_manager' );
		//$this->loader->add_action( 'ced_tokopedia_auto_upload_schedule_job_shopName', $plugin_admin, 'ced_tokopedia_auto_upload_schedule_manager' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommmerce_Tokopedia_Integration_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommmerce_Tokopedia_Integration_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
