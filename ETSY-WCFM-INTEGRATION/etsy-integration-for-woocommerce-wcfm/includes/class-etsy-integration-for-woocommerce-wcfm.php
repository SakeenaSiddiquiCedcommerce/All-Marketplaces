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
 * @package    Etsy_Integration_For_Woocommerce_Wcfm
 * @subpackage Etsy_Integration_For_Woocommerce_Wcfm/includes
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
 * @package    Etsy_Integration_For_Woocommerce_Wcfm
 * @subpackage Etsy_Integration_For_Woocommerce_Wcfm/includes
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class Etsy_Integration_For_Woocommerce_Wcfm {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Etsy_Integration_For_Woocommerce_Wcfm_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
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
		if ( defined( 'ETSY_INTEGRATION_FOR_WOOCOMMERCE_WCFM_VERSION' ) ) {
			$this->version = ETSY_INTEGRATION_FOR_WOOCOMMERCE_WCFM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'etsy-integration-for-woocommerce-wcfm';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Etsy_Integration_For_Woocommerce_Wcfm_Loader. Orchestrates the hooks of the plugin.
	 * - Etsy_Integration_For_Woocommerce_Wcfm_i18n. Defines internationalization functionality.
	 * - Etsy_Integration_For_Woocommerce_Wcfm_Admin. Defines all hooks for the admin area.
	 * - Etsy_Integration_For_Woocommerce_Wcfm_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-etsy-integration-for-woocommerce-wcfm-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-etsy-integration-for-woocommerce-wcfm-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-etsy-integration-for-woocommerce-wcfm-public.php';

		$this->loader = new Etsy_Integration_For_Woocommerce_Wcfm_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Etsy_Integration_For_Woocommerce_Wcfm_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Etsy_Integration_For_Woocommerce_Wcfm_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Etsy_Integration_For_Woocommerce_Wcfm_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//add menu in vendor dashboard
		$this->loader->add_action( 'wcfm_formeted_menus' , $plugin_public , 'ced_etsy_wcfm_add_main_menu' , 23 );

		$this->loader->add_action( 'wcfm_load_views', $plugin_public, 'ced_etsy_wcfm_load_page' , 23 );
		$this->loader->add_filter( 'wcfm_query_vars', $plugin_public, 'ced_etsy_wcfm_add_query_vars' , 23 );
		// $this->loader->add_action('init', $plugin_public,'do_output_buffer');
		
		$this->loader->add_action( 'wp_ajax_ced_etsy_wcfm_fetch_next_level_category', $plugin_public, 'ced_etsy_wcfm_fetch_next_level_category' );
		$this->loader->add_action( 'wp_ajax_ced_etsy_wcfm_map_categories_to_store', $plugin_public, 'ced_etsy_wcfm_map_categories_to_store' );

		$this->loader->add_action( 'wp_ajax_ced_etsy_wcfm_process_bulk_action', $plugin_public, 'ced_etsy_wcfm_process_bulk_action' );
		$this->loader->add_action( 'wp_ajax_ced_etsy_wcfm_get_orders', $plugin_public, 'ced_etsy_wcfm_get_orders' );
		$this->loader->add_action( 'wp_ajax_ced_etsy_wcfm_delete_account', $plugin_public, 'ced_etsy_wcfm_delete_account' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_import_product_bulk_action', $plugin_public, 'ced_etsy_import_product_bulk_action' );
		// $this->loader->add_filter( 'cron_schedules',$plugin_public, 'my_add_intervals');
		$this->loader->add_filter( 'action_to_get_orders_from_etsy',$plugin_public, 'action_to_get_orders_from_etsy_callback',999,2);
		$this->loader->add_filter( 'cron_schedules', $plugin_public, 'my_etsy_cron_schedules' );
		$this->loader->add_action( 'init',$plugin_public, 'schedule_events_for_time_interval' );
		$this->loader->add_action('fetch_orders_frometsy',$plugin_public,'fetch_orders_frometsy_scheduler');

		$etsy_wcfm_account_list = get_option( 'ced_etsy_wcfm_accounts' ,array() );
		if( !empty($etsy_wcfm_account_list) ) {
			$etsy_wcfm_account_list = json_decode($etsy_wcfm_account_list,true);	
		}
		foreach ( $etsy_wcfm_account_list as $vendor_id => $shop_name ) {
			$this->loader->add_action( 'ced_etsy_wcfm_update_inventory_cron_job_' . $shop_name . '_' . $vendor_id, $plugin_public,'ced_etsy_wcfm_update_inventory_to_etsy_from_wcfm', 77, 3 );
			$this->loader->add_action( 'ced_etsy_wcfm_fetch_order_cron_job_' . $shop_name . '_' . $vendor_id, $plugin_public,'ced_etsy_wcfm_fetch_orders_from_etsy', 77, 3 );
		}

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
	 * @return    Etsy_Integration_For_Woocommerce_Wcfm_Loader    Orchestrates the hooks of the plugin.
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
