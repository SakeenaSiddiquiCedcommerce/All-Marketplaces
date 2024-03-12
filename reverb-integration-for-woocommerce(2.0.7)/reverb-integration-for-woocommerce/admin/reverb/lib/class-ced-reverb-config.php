<?php
/**
 * Gettting mandatory data
 *
 * @package  Woocommerce_reverb_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

	/**
	 * Ced_reverb_Config
	 *
	 * @since 1.0.0
	 */
class Ced_Reverb_Config {

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
	 * Ced_reverb_Config Instance.
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
	 * Ced_reverb_Config constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$config_details = get_option( 'ced_reverb_configuration_details', array() );
		if ( isset( $config_details['environment'] ) && 'sandbox' == $config_details['environment'] ) {
			$this->end_point_url = 'https://sandbox.reverb.com/api/';
		} else {
			$this->end_point_url = 'https://api.reverb.com/api/';
		}
		$this->environment = '';
		$this->client_id   = isset( $config_details['client_id'] ) ? $config_details['client_id'] : '';
	}
}

