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
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/includes
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
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/includes
 * @author     cedcommerce <support@cedcommerce.com>
 */
class Cedcommerce_Vidaxl_Dropshipping {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cedcommerce_Vidaxl_Dropshipping_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'CEDCOMMERCE_VIDAXL_DROPSHIPPING_VERSION' ) ) {
			$this->version = CEDCOMMERCE_VIDAXL_DROPSHIPPING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cedcommerce-vidaxl-dropshipping';
		$this->define_constants_for_vidaxl_dropshipping();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}
	public function define_constants_for_vidaxl_dropshipping() {
		define( 'CED_VIDAXL_DROPSHIPPING_DIRPATH', plugin_dir_path( dirname( __FILE__ ) ) );
		define( 'CED_VIDAXL_DROPSHIPPING_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		define( 'CED_VIDAXL_DROPSHIPPING_PREFIX', 'ced_vidaxl_' );
	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cedcommerce_Vidaxl_Dropshipping_Loader. Orchestrates the hooks of the plugin.
	 * - Cedcommerce_Vidaxl_Dropshipping_i18n. Defines internationalization functionality.
	 * - Cedcommerce_Vidaxl_Dropshipping_Admin. Defines all hooks for the admin area.
	 * - Cedcommerce_Vidaxl_Dropshipping_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cedcommerce-vidaxl-dropshipping-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cedcommerce-vidaxl-dropshipping-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cedcommerce-vidaxl-dropshipping-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cedcommerce-vidaxl-dropshipping-public.php';

		$this->loader = new Cedcommerce_Vidaxl_Dropshipping_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cedcommerce_Vidaxl_Dropshipping_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cedcommerce_Vidaxl_Dropshipping_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Cedcommerce_Vidaxl_Dropshipping_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_vidaxl_admin_menu' );
		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_vidaxl_add_marketplace_menus_to_array', 13 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'wp_ajax_ced_vidaxl_download_csv', $plugin_admin, 'ced_vidaxl_download_csv' );
		$this->loader->add_action( 'wp_ajax_ced_vidaxl_process_csv', $plugin_admin, 'ced_vidaxl_process_csv' );
		$this->loader->add_action( 'wp_ajax_ced_vidaxl_fetch_categories', $plugin_admin, 'ced_vidaxl_fetch_categories' );
		$this->loader->add_action( 'wp_ajax_ced_vidaxl_check_temp_db_table_status', $plugin_admin, 'ced_vidaxl_check_temp_db_table_status' );
		$this->loader->add_action( 'wp_ajax_ced_vidaxl_start_import_process', $plugin_admin, 'ced_vidaxl_start_import_process' );
		$this->loader->add_action( 'wp_ajax_ced_vidaxl_send_data', $plugin_admin, 'ced_vidaxl_send_data' );
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_admin, 'ced_vidaxl_order_data_on_processing' );



		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'my_vidaXl_cron_schedules' );
		$this->loader->add_action( 'ced_fmcw_vidaxl_productimage_update_scheduler_job', $plugin_admin, 'ced_vidaxl_product_image_creation' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Cedcommerce_Vidaxl_Dropshipping_Public( $this->get_plugin_name(), $this->get_version() );

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
	 * @return    Cedcommerce_Vidaxl_Dropshipping_Loader    Orchestrates the hooks of the plugin.
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
