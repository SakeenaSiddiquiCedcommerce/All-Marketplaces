<?php
/**
 * Gettting Config data
 *
 * @package  Facebook_Marketplace_Connector_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class_Ced_Fmcw_Config
 *
 * @since 1.0.0
 */
class Class_Ced_Fmcw_Config {

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
	 * The sAppId variable.
	 *
	 * @since    1.0.0
	 * @var      string    $sAppId   The sub user variable.
	 */
	public $sAppId;

	/**
	 * The public key variable
	 *
	 * @since    1.0.0
	 * @var      string    $public_key    The public key variable.
	 */
	public $public_key;

	/**
	 * The shop id variable
	 *
	 * @since    1.0.0
	 * @var      string    $shop_id    The shop id variable.
	 */
	public $shop_id;

	/**
	 * The Refresh Token variable
	 *
	 * @since    1.0.0
	 * @var      string    $refresh_token    The Refresh Token variable.
	 */
	public $refresh_token;

	/**
	 * The Access Token variable
	 *
	 * @since    1.0.0
	 * @var      string    $access_token    The Access Token variable.
	 */
	public $access_token;

	/**
	 * Class_Ced_Fmcw_Config Instance.
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
	 * Class_Ced_Fmcw_Config constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$registration_data = get_option( 'ced_fmcw_registered_with_cedcommerce', true );
		$this->sAppId      = isset( $registration_data['reg_app_id'] ) ? $registration_data['reg_app_id'] : '';

		$this->public_key = isset( $registration_data['reg_public_key'] ) ? base64_decode( $registration_data['reg_public_key'] ) : '';

		$this->refresh_token = $refresh_token = get_option( 'ced_fmcw_refresh_token' );
		// $this->refresh_token = isset( $registration_data['reg_refresh_token'] ) ? $registration_data['reg_refresh_token'] : '';

		$shop_id = get_option( 'ced_fmcw_fb_shop_id', '' );
		if ( ! empty( $shop_id ) ) {
			$this->shop_id = $shop_id;
		}
		// $this->end_point_url = 'https://fbdev.cifapps.com/remote/public/';
		$this->end_point_url = 'https://metaapi-backend.cifapps.com/';
	}
}