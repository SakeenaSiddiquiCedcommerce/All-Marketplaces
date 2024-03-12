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
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
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
 * @package    CedCommerce_Integration_for_Good_Market
 * @subpackage CedCommerce_Integration_for_Good_Market/includes
 */
class Good_Market_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0

	 * @var      Good_Market_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'GOOD_MARKET_INTEGRATION_VERSION' ) ) {
			$this->version = GOOD_MARKET_INTEGRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'Good_Market_Woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Good_Market_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Good_Market_Woocommerce_I18n. Defines internationalization functionality.
	 * - Good_Market_Integration_Admin. Defines all hooks for the admin area.
	 * - Good_Market_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-good_market_integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-good_market_integration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-good_market_integration-admin.php';

		$this->loader = new Good_Market_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Good_Market_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new Good_Market_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Good_Market_Integration_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_good_market_add_menus', 23 );
		$this->loader->add_action( 'wp_ajax_ced_good_market_process_api_keys', $plugin_admin, 'ced_good_market_process_api_keys' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_search_product_name', $plugin_admin, 'ced_good_market_search_product_name' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_get_product_metakeys', $plugin_admin, 'ced_good_market_get_product_metakeys' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_process_metakeys', $plugin_admin, 'ced_good_market_process_metakeys' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_get_orders_manual', $plugin_admin, 'ced_good_market_get_orders_manual' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_process_bulk_action', $plugin_admin, 'ced_good_market_process_bulk_action' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_save_cat', $plugin_admin, 'ced_good_market_save_cat' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_ship_order', $plugin_admin, 'ced_good_market_ship_order' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_update_categories', $plugin_admin, 'ced_good_market_update_categories' );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'ced_good_market_cron_schedules' );
		$this->loader->add_filter( 'ced_good_market_auto_fetch_orders', $plugin_admin, 'ced_good_market_auto_fetch_orders' );
		$this->loader->add_filter( 'ced_good_market_auto_inventory_sync', $plugin_admin, 'ced_good_market_auto_inventory_sync' );
		$this->loader->add_action( 'wp_ajax_ced_good_market_list_per_page', $plugin_admin, 'ced_good_market_list_per_page' );
		$this->loader->add_filter( 'wp_ajax_ced_gm_inventory_schedule_manager', $plugin_admin, 'ced_gm_inventory_schedule_manager' );
		$this->loader->add_filter( 'wp_ajax_nopriv_ced_gm_inventory_schedule_manager', $plugin_admin, 'ced_gm_inventory_schedule_manager' );
		$this->loader->add_filter( 'wp_ajax_sync_good_market_products', $plugin_admin, 'sync_good_market_products' );
		$this->loader->add_filter( 'wp_ajax_nopriv_sync_good_market_products', $plugin_admin, 'sync_good_market_products' );
		$this->loader->add_filter( 'wp_ajax_sync_good_market_feeds', $plugin_admin, 'sync_good_market_feeds' );
		$this->loader->add_filter( 'wp_ajax_nopriv_sync_good_market_feeds', $plugin_admin, 'sync_good_market_feeds' );
		$this->loader->add_filter( 'wp_ajax_ced_good_market_auto_inventory_sync', $plugin_admin, 'ced_good_market_auto_inventory_sync' );
		$this->loader->add_filter( 'wp_ajax_nopriv_ced_good_market_auto_inventory_sync', $plugin_admin, 'ced_good_market_auto_inventory_sync' );
		$this->loader->add_filter( 'sync_good_market_feeds', $plugin_admin, 'sync_good_market_feeds' );
		$this->loader->add_filter( 'wp_ajax_ced_good_market_auto_fetch_orders', $plugin_admin, 'ced_good_market_auto_fetch_orders' );
		$this->loader->add_filter( 'wp_ajax_nopriv_ced_good_market_auto_fetch_orders', $plugin_admin, 'ced_good_market_auto_fetch_orders' );
		$this->loader->add_action( 'updated_post_meta', $plugin_admin, 'stock_update_after_post_meta', 10, 4 );
        $this->loader->add_filter( 'woocommerce_duplicate_product_exclude_meta', $plugin_admin, 'woocommerce_duplicate_product_exclude_meta');


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
	 * @return    Good_Market_Woocommerce_Loader    Orchestrates the hooks of the plugin.
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