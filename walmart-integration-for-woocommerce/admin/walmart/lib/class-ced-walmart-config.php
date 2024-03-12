<?php
/**
 * Gettting mandatory data
 *
 * @package  Woocommerce_Walmart_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

	/**
	 * Ced_Walmart_Config
	 *
	 * @since 1.0.0
	 */
class Ced_Walmart_Config {

	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */
	public static $_instance;

	/**
	 * The endpoint variable.
	 *
	 * @since    1.0.0
	 * @var      string    $end_point_url    The endpoint variable.
	 */
	public $end_point_url;
	/**
	 * The client_id variable.
	 *
	 * @since    1.0.0
	 * @var      string    $client_id   The client_id variable.
	 */
	public $client_id;
	/**
	 * The environment variable.
	 *
	 * @since    1.0.0
	 * @var      string    $environment   The environment variable.
	 */
	public $environment;

	/**
	 * The client_secret variable
	 *
	 * @since    1.0.0
	 * @var      string    $client_secret    The client_secret variable.
	 */
	public $client_secret;

	/**
	 * The channel_id variable
	 *
	 * @since    1.0.0
	 * @var      string    $channel_id    The channel_id variable.
	 */
	public $channel_id;
	/**
	 * The access_token variable
	 *
	 * @since    1.0.0
	 * @var      string    $access_token    The access_token variable.
	 */
	public $access_token;



	/**
	 * Ced_Walmart_Config Instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Ced_Walmart_Config constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$config_details = get_option( 'ced_walmart_configuration_details', array() );
		if ( isset( $config_details['environment'] ) && 'sandbox' == $config_details['environment'] ) {
			$this->end_point_url = 'https://sandbox.walmartapis.com/v3/';
		} else {
			$this->end_point_url = 'https://marketplace.walmartapis.com/v3/';
		}
		$this->environment   = isset( $config_details['environment'] ) ? $config_details['environment'] : '';
		$this->client_id     = isset( $config_details['client_id'] ) ? $config_details['client_id'] : '';
		$this->client_secret = isset( $config_details['client_secret'] ) ? $config_details['client_secret'] : '';
		$this->access_token  = get_option( 'ced_walmart_token', '' );
		/** Get latest channel id for walmart
		 *
		 * @since 1.0.0
		 */
		$this->channel_id = apply_filters( 'ced_walmart_channel_id', '7b2c8dab-c79c-4cee-97fb-0ac399e17ade' );
	}
}

