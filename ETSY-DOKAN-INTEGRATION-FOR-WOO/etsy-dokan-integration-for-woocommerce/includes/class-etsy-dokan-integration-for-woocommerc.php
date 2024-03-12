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
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/includes
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
 * @package    Etsy_Dokan_Integration_For_Woocommerc
 * @subpackage Etsy_Dokan_Integration_For_Woocommerc/includes
 * @author     Cedcommerce <support@cedcommerce.com>
 */
class Etsy_Dokan_Integration_For_Woocommerc {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Etsy_Dokan_Integration_For_Woocommerc_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		if ( defined( 'ETSY_DOKAN_INTEGRATION_FOR_WOOCOMMERC_VERSION' ) ) {
			$this->version = ETSY_DOKAN_INTEGRATION_FOR_WOOCOMMERC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		
		$this->plugin_name = 'etsy-dokan-integration-for-woocommerc';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Etsy_Dokan_Integration_For_Woocommerc_Loader. Orchestrates the hooks of the plugin.
	 * - Etsy_Dokan_Integration_For_Woocommerc_i18n. Defines internationalization functionality.
	 * - Etsy_Dokan_Integration_For_Woocommerc_Admin. Defines all hooks for the admin area.
	 * - Etsy_Dokan_Integration_For_Woocommerc_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-etsy-dokan-integration-for-woocommerc-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-etsy-dokan-integration-for-woocommerc-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-etsy-dokan-integration-for-woocommerc-public.php';

		$this->loader = new Etsy_Dokan_Integration_For_Woocommerc_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Etsy_Dokan_Integration_For_Woocommerc_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Etsy_Dokan_Integration_For_Woocommerc_i18n();

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

		$plugin_public = new Etsy_Dokan_Integration_For_Woocommerc_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/*-------------------------------------------------------------------------------------------------------------------*/
		/**
		 *****************************
		 * REQUIRED DOKEN HOOKS HERE
		 *****************************
		 */

		// Creating menu in the vendor section
		$this->loader->add_filter( 'dokan_get_dashboard_nav', $plugin_public, 'ced_etsy_add_etsy_menu', 10 );
		// Changing prepared URLS
		$this->loader->add_filter( 'dokan_query_var_filter', $plugin_public, 'ced_etsy_load_document', 10 );
		// Load our custom plugin template
		$this->loader->add_action( 'dokan_load_custom_template', $plugin_public, 'ced_etsy_landing_page', 10 );
		// Setting Product bulk actions 
		$this->loader->add_filter( 'dokan_bulk_product_statuses', $plugin_public, 'ced_etsy_add_etsy_operations', 10 );
		// Product listing arguments
		$this->loader->add_filter( 'dokan_product_listing_arg', $plugin_public, 'ced_etsy_get_filtered_data', 10 );
		// Redirecting Template
		$this->loader->add_action( 'template_redirect', $plugin_public, 'ced_etsy_perform_bulk_actions' );
		// Adding filters
		$this->loader->add_action( 'dokan_product_listing_filter_before_search_form', $plugin_public, 'ced_etsy_add_filters', 10 );
		// Showing notifications on the product page
		$this->loader->add_action( 'dokan_before_listing_product', $plugin_public, 'ced_etsy_show_notifications', 10 );
		// Fetching orders from the Etsy on click on the order buttons 
		$this->loader->add_action( 'dokan_order_inside_content', $plugin_public, 'ced_etsy_dokan_fetch_orders_button', 10 );
		//
		$this->loader->add_action( 'ced_dokan_products_args', $plugin_public, 'ced_etsy_perform_bulk_actions_custom', 10, 2 );
		/*-------------------------------------------------------------------------------------------------------------------*/

		$this->loader->add_filter( 'wp_ajax_ced_etsy_authorize_account', $plugin_public, 'ced_etsy_authorize_account' );

		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_fetch_next_level_category', $plugin_public, 'ced_etsy_dokan_fetch_next_level_category' );
		$this->loader->add_filter( 'wp_ajax_nopriv_ced_etsy_dokan_fetch_next_level_category', $plugin_public, 'ced_etsy_dokan_fetch_next_level_category' );
		
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_map_categories_to_store', $plugin_public, 'ced_etsy_dokan_map_categories_to_store' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_process_bulk_action', $plugin_public, 'ced_etsy_process_bulk_action' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_import_products_bulk_action', $plugin_public, 'ced_etsy_dokan_import_products_bulk_action' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_profiles_on_pop_up', $plugin_public, 'ced_etsy_dokan_profiles_on_pop_up' );
		$this->loader->add_filter( 'wp_ajax_save_etsy_dokan_profile_through_popup', $plugin_public, 'save_etsy_dokan_profile_through_popup' );
		
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_get_orders', $plugin_public, 'ced_etsy_dokan_get_orders' );
		$this->loader->add_filter( 'wp_ajax_nopriv_ced_etsy_dokan_get_orders', $plugin_public, 'ced_etsy_dokan_get_orders' );

		$this->loader->add_filter( 'wp_ajax_ced_etsy_category_refresh', $plugin_public, 'ced_etsy_category_refresh' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_search_product_name', $plugin_public, 'ced_etsy_dokan_search_product_name' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_get_product_metakeys', $plugin_public, 'ced_etsy_get_product_metakeys' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_process_metakeys', $plugin_public, 'ced_etsy_process_metakeys' );
		$this->loader->add_filter( 'cron_schedules', $plugin_public, 'my_etsy_cron_schedules' );
		$this->loader->add_action( 'wp_ajax_ced_etsy_dokan_fetch_next_level_category_add_profile', $plugin_public, 'ced_etsy_dokan_fetch_next_level_category_add_profile' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_change_account_status', $plugin_public, 'ced_etsy_dokan_change_account_status' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_submit_shipment', $plugin_public, 'ced_etsy_dokan_submit_shipment' );
		$this->loader->add_filter( 'wp_ajax_ced_esty_delete_mapped_profiles', $plugin_public, 'ced_esty_delete_mapped_profiles' );
		$this->loader->add_filter( 'wp_ajax_ced_etsy_dokan_delete_account', $plugin_public, 'ced_etsy_dokan_delete_account' );

		/**
		 **************************************
		 * INCLUDE TEMPLATE FOR SETTINGS  TAB
		 **************************************
		 */
		$this->loader->add_action( 'ced_etsy_dokan_render_meta_keys_settings', $plugin_public, 'ced_etsy_render_meta_key_settings_in_setting_tab' );
		$this->loader->add_action( 'ced_etsy_dokan_render_product_settings', $plugin_public, 'ced_etsy_dokan_render_product_settings_in_setting_tab' );
		$this->loader->add_action( 'ced_etsy_dokan_render_shipping_profiles', $plugin_public, 'ced_etsy_dokan_render_shipping_profiles_in_setting_tab' );
		$this->loader->add_action( 'ced_etsy_dokan_render_order_settings', $plugin_public, 'ced_etsy_dokan_render_order_settings_in_setting_tab' );
		$this->loader->add_action( 'ced_etsy_dokan_render_shedulers_settings', $plugin_public, 'ced_etsy_dokan_render_shedulers_settings_in_setting_tab' );

		/**
		 *********************************************
		 * ADD CUSTOM FIELDS ON THE PRODUC EDIT PAGE
		 *********************************************
		 */

		// AT THE VARIATION LEVEL CUSTOMIZATION
		// $this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_public, 'ced_etsy_render_product_fields', 10, 3 );
		// RENDER CUSTOM FIELD ON THE SIMPLE PRODUCT LEVEL
		// $this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_public, 'ced_etsy_product_data_tabs' );
		// RENDER PRODUCT  CUSTOM FIELDS ON THE VARIATION LEVEL
		// $this->loader->add_action( 'woocommerce_process_product_meta', $plugin_public, 'ced_etsy_save_product_fields_variation', 10, 2 );
		// ON SAVE VARIATION BUTTON IT WILL RUN
		// $this->loader->add_action( 'woocommerce_save_product_variation', $plugin_public, 'ced_etsy_save_product_fields_variation', 12, 2 );
		// SHOW THE SAVE BUTTON OF THE VARIATION
		// $this->loader->add_filter( 'woocommerce_product_data_panels', $plugin_public, 'ced_etsy_product_data_panels' );
		// ON SAVE OF THE TOTAL PRODUCT
		// $this->loader->add_action( 'save_post', $plugin_public, 'ced_etsy_save_meta_data' );

		$shops = get_option( 'ced_etsy_dokan_details', array() );
		if ( ! empty( $shops ) ) {
			foreach ( $shops as $key => $value ) {
				if ( isset( $value['details']['ced_shop_account_status'] ) && 'Active' == $value['details']['ced_shop_account_status'] ) {
					$key = trim( $key );
					$this->loader->add_action( 'ced_etsy_inventory_scheduler_job_' . $key, $plugin_public, 'ced_etsy_inventory_schedule_manager' );
					$this->loader->add_action( 'ced_etsy_auto_import_schedule_job_' . $key, $plugin_public, 'ced_etsy_auto_import_schedule_manager' );
					$this->loader->add_action( 'ced_etsy_order_scheduler_job_' . $key, $plugin_public, 'ced_etsy_order_schedule_manager' );
					$this->loader->add_action( 'ced_etsy_sync_existing_products_job_' . $key, $plugin_public, 'ced_etsy_sync_existing_products' );
					$this->loader->add_action( 'ced_etsy_auto_upload_products_' . $key, $plugin_public, 'ced_etsy_auto_upload_products' );
				}
			}
		}

		$this->loader->add_action( 'ced_etsy_order_scheduler_job_', $plugin_public, 'ced_etsy_order_schedule_manager' );
		$this->loader->add_action( 'ced_etsy_inventory_scheduler_job_', $plugin_public, 'ced_etsy_inventory_schedule_manager' );

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
			$this->loader->add_filter( 'woocommerce_email_enabled_' . esc_attr( $status ), $plugin_public, 'ced_etsy_email_restriction', 10, 2 );
		}
		$this->loader->add_action( 'add_meta_boxes', $plugin_public, 'ced_etsy_dokan_add_order_metabox', 77 );

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
	 * @return    Etsy_Dokan_Integration_For_Woocommerc_Loader    Orchestrates the hooks of the plugin.
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
