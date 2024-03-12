<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Reverb_Integartion_For_Woocommerce
 * @subpackage Reverb_Integartion_For_Woocommerce/includes
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
 * @package    Reverb_Integartion_For_Woocommerce
 * @subpackage Reverb_Integartion_For_Woocommerce/includes
 * author     CedCommerce <plugins@cedcommerce.com>
 */
class Reverb_Integartion_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * access   protected
	 * @var      Reverb_Integartion_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * access   protected
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
		if ( defined( 'REVERB_INTEGARTION_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = REVERB_INTEGARTION_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'reverb-integartion-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Reverb_Integartion_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Reverb_Integartion_For_Woocommerce_I18n. Defines internationalization functionality.
	 * - Reverb_Integartion_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Reverb_Integartion_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-reverb-integartion-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-reverb-integartion-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-reverb-integartion-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */

		$this->loader = new Reverb_Integartion_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Reverb_Integartion_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Reverb_Integartion_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Reverb_Integartion_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_reverb_add_menus', 23 );

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ced_reverb_add_order_metabox', 24 );
		$this->loader->add_action( 'save_post', $plugin_admin, 'ced_reverb_save_metadata', 24 );

		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_reverb_add_marketplace_menus_to_array', 13 );
		$this->loader->add_action( 'wp_ajax_ced_reverb_process_api_keys', $plugin_admin, 'ced_reverb_process_api_keys' );

		$this->loader->add_action( 'ced_reverb_render_order_settings', $plugin_admin, 'ced_reverb_render_order_settings' );
		$this->loader->add_action( 'ced_reverb_render_product_settings', $plugin_admin, 'ced_reverb_render_product_settings' );
		$this->loader->add_action( 'ced_reverb_render_shedulers_settings', $plugin_admin, 'ced_reverb_render_shedulers_settings' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_map_order_status', $plugin_admin, 'ced_reverb_map_order_status' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_search_product_name', $plugin_admin, 'ced_reverb_search_product_name' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_get_product_metakeys', $plugin_admin, 'ced_reverb_get_product_metakeys' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_process_metakeys', $plugin_admin, 'ced_reverb_process_metakeys' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_fetch_next_level_category', $plugin_admin, 'ced_reverb_fetch_next_level_category' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_map_categories_to_store', $plugin_admin, 'ced_reverb_map_categories_to_store' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_update_categories', $plugin_admin, 'ced_reverb_update_categories' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_process_bulk_action', $plugin_admin, 'ced_reverb_process_bulk_action' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_get_orders_manual', $plugin_admin, 'ced_reverb_get_orders_manual' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_import_product_by_wp_list', $plugin_admin, 'ced_reverb_import_product_by_wp_list' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_ship_order', $plugin_admin, 'ced_reverb_ship_order' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_auto_upload_categories', $plugin_admin, 'ced_reverb_auto_upload_categories' );
		$this->loader->add_action( 'wp_ajax_ced_reverb_copy_global', $plugin_admin, 'ced_reverb_copy_global' );

		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'ced_reverb_custom_product_tabs' );
		$this->loader->add_filter( 'woocommerce_product_data_panels', $plugin_admin, 'ced_reverb_inventory_options_product_tab_content' );
		$this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'ced_reverb_render_product_fields', 10, 3 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'ced_reverb_save_product_fields', 10, 2 );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'ced_reverb_save_product_fields', 10, 2 );

		// scheduler setting
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'ced_reverb_cron_schedules' );
		$this->loader->add_filter( 'admin_init', $plugin_admin, 'ced_reverb_set_schedules' );
		$this->loader->add_action( 'ced_reverb_auto_fetch_orders', $plugin_admin, 'ced_reverb_auto_fetch_orders' );

		$this->loader->add_action( 'ced_reverb_product_update', $plugin_admin, 'ced_reverb_product_update' );
		$this->loader->add_action( 'ced_reverb_sync_existing_products', $plugin_admin, 'ced_reverb_sync_existing_products' );

		$this->loader->add_action( 'ced_reverb_auto_upload_product', $plugin_admin, 'ced_reverb_auto_upload_product' );

		$this->loader->add_action( 'ced_reverb_auto_update_tracking', $plugin_admin, 'ced_reverb_auto_update_tracking' );

		$this->loader->add_filter( 'woocommerce_order_number', $plugin_admin, 'ced_reverb_filter_woocommerce_order_number', 10, 2 );
		$this->loader->add_filter( 'woocommerce_duplicate_product_exclude_meta', $plugin_admin, 'ced_woocommerce_duplicate_product_exclude_meta', 10, 2 );

		//=================================================================

		$this->loader->add_action( 'delete_transient_ced_utk_123', $plugin_admin, 'ced_delete_transient_func',10,1 );

		//=================================================================
		$this->loader->add_action( 'ced_reverb_product_update_on_updated_post_meta', $plugin_admin, 'ced_reverb_product_update_on_updated_post_meta_func');


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
			$this->loader->add_filter( 'woocommerce_email_enabled_' . esc_attr( $status ), $plugin_admin, 'ced_reverb_email_restriction', 10, 2 );
		}

		$this->loader->add_action('wp_ajax_ced_reverb_test_prepared_data', $plugin_admin, 'ced_reverb_test_prepared_data_func');
		$this->loader->add_action('wp_ajax_ced_reverb_test_prepared_data_update_inventory', $plugin_admin, 'ced_reverb_test_prepared_data_update_inventory_func');
		$this->loader->add_action('woocommerce_order_status_processing', $plugin_admin, 'reverb_get_product_id_from_orders_created',10,1);


		

	}

	public function ced_delete_transient_func($name){

		//$value = get_transient($name);

		update_option('luck_test', $name);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Reverb_Integartion_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

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
	 * @return    Reverb_Integartion_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
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
