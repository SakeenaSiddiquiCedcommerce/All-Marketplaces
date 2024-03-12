<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://woocommerce.com/vendor/cedcommerce/
 * @since      1.0.0
 *
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/includes
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
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/includes
 */
class Facebook_Marketplace_Connector_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @var      Facebook_Marketplace_Connector_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'FACEBOOK_MARKETPLACE_CONNECTOR_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = FACEBOOK_MARKETPLACE_CONNECTOR_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'facebook-marketplace-connector-for-woocommerce';

		$this->define_constants();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	public function define_constants() {
		define( 'CED_FMCW_DIRPATH', plugin_dir_path( dirname( __FILE__ ) ) );
		define( 'CED_FMCW_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		define( 'CED_FMCW_PREFIX', 'ced_fmcw_' );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Facebook_Marketplace_Connector_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Facebook_Marketplace_Connector_For_Woocommerce_I18n. Defines internationalization functionality.
	 * - Facebook_Marketplace_Connector_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Facebook_Marketplace_Connector_For_Woocommerce_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebook-marketplace-connector-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebook-marketplace-connector-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-facebook-marketplace-connector-for-woocommerce-admin.php';

		$this->loader = new Facebook_Marketplace_Connector_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Facebook_Marketplace_Connector_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new Facebook_Marketplace_Connector_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Facebook_Marketplace_Connector_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_fmcw_admin_menu' );
		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_fmcw_add_marketplace_menus_to_array', 13 );

		// $this->loader->add_action( 'admin_init', $plugin_admin, 'ced_fmcw_get_fb_account_code', 12 );
		$this->loader->add_action( 'wp_ajax_ced_fmcw_authenticate_cms_page', $plugin_admin, 'ced_fmcw_authenticate_cms_page', 12 );

		$this->loader->add_filter( 'manage_product_posts_custom_column', $plugin_admin, 'ced_fmcw_modify_content_on_product_list_page', 10, 2 );

		$this->loader->add_action( 'wp_ajax_ced_fmcw_fetch_next_level_category', $plugin_admin, 'ced_fmcw_fetch_next_level_category' );
		$this->loader->add_action( 'wp_ajax_ced_fmcw_map_categories_to_store', $plugin_admin, 'ced_fmcw_map_categories_to_store' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ced_fmcw_add_order_metabox' );

		$this->loader->add_action( 'wp_ajax_ced_fmcw_setup_completed', $plugin_admin, 'ced_fmcw_setup_completed' );
		$this->loader->add_action( 'wp_ajax_ced_facebook_get_orders', $plugin_admin, 'ced_facebook_get_orders' );
		$this->loader->add_action( 'wp_ajax_ced_fmcw_complete_dispatch_order', $plugin_admin, 'ced_fmcw_complete_dispatch_order' );
		$this->loader->add_action( 'wp_ajax_ced_facebook_cancel_order', $plugin_admin, 'ced_facebook_cancel_order' );
		$this->loader->add_action( 'wp_ajax_ced_facebook_refund_order', $plugin_admin, 'ced_facebook_refund_order' );
		$this->loader->add_action( 'wp_ajax_ced_save_catalog', $plugin_admin, 'ced_save_catalog' );
		$this->loader->add_action( 'wp_ajax_ced_fmcw_register_to_cedcommerce', $plugin_admin, 'ced_fmcw_register_to_cedcommerce' );
		$this->loader->add_action( 'woocommerce_product_data_tabs', $plugin_admin, 'ced_fmcw_add_tab_on_product_edit_page' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'ced_fmcw_add_fields_on_product_edit_page' );

		$this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'ced_fmcw_add_fields_on_variation_edit_page', 10, 3 );
		$this->loader->add_action( 'woocommerce_admin_process_variation_object', $plugin_admin, 'ced_fmcw_save_fields_on_edit_page_variation', 10, 2 );
		$this->loader->add_action( 'wp_ajax_ced_facebook_shopping_list_per_page', $plugin_admin, 'ced_facebook_shopping_list_per_page' );
		$this->loader->add_action( 'wp_ajax_ced_fmcw_manage_product_bulk_action', $plugin_admin, 'ced_fmcw_manage_product_bulk_action' );
		$this->loader->add_action( 'wp_ajax_ced_reset_facebook_entire_setup', $plugin_admin, 'ced_reset_facebook_entire_setup' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'ced_fmcw_save_product_details' );
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
	 * @return    Facebook_Marketplace_Connector_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
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
