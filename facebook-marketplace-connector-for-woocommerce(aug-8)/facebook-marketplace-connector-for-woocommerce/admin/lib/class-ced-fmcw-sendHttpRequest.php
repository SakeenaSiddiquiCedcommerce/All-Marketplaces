<?php
/**
 * Curl requests
 *
 * @package  Facebook_Marketplace_Connector_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class_Ced_Fmcw_Send_Http_Request
 *
 * @since 1.0.0
 */
class Class_Ced_Fmcw_Send_Http_Request {

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
	 * Class_Ced_Shopee_Send_Http_Request construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_depenedency();
		$this->end_point_url = $this->ced_fmcw_config_instance->end_point_url;
		$this->sAppId        = $this->ced_fmcw_config_instance->sAppId;
		$this->public_key    = $this->ced_fmcw_config_instance->public_key;
		$this->refresh_token = $this->ced_fmcw_config_instance->refresh_token;
		$this->shop_id       = $this->ced_fmcw_config_instance->shop_id;
		// $this->shop_id       = 963;
	}

	/**
	 * The Get API Call Request Function
	 *
	 * @since    1.0.0
	 */
	public function get_request( $action = '', $parameters = array() ) {

		$connection = curl_init();

		$header = $this->prepare_request_header();
		// print_r( $header );
		$data = get_option( 'ced_fmcw_stord_whole_store_data' );
		// print_r( $data );
		$this->end_point_url = $this->end_point_url . $action;
		$this->prepare_endpoint_url( $parameters );
		// print_r( $this->end_point_url );
		// echo '----';
		// print_r( $header );
		curl_setopt( $connection, CURLOPT_URL, $this->end_point_url );

		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );

		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );

		$curl_error = curl_error( $connection );

		curl_close( $connection );

		$response = $this->parse_json_response( $response );
		return $response;
	}

	public function get_shop_details( $social_fb_action, $refresh_token, $shop_id ) {
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				// CURLOPT_URL => 'https://fbdev.cifapps.com/remote/public/webapi/rest/v1/shop?shop_id='.$shop_id,
				CURLOPT_URL            => 'https://metaapi-backend.cifapps.com/webapi/rest/v1/shop?shop_id=' . $shop_id,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'Authorization: Bearer ' . $refresh_token,
				),
			)
		);
		$response = curl_exec( $curl );
		curl_close( $curl );
		$response = $this->parse_json_response( $response );
		return $response;
	}

	public function get_refresh_token( $social_fb_action, $registration_data_token ) {
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->end_point_url . $social_fb_action,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'Authorization: Bearer ' . $registration_data_token,
				),
			)
		);
		$response = curl_exec( $curl );
		curl_close( $curl );
		$response = $this->parse_json_response( $response );
		return $response;
	}

	/**
	 * The POST API Call Request Function
	 *
	 * @since    1.0.0
	 */
	public function post_request( $action = '', $post_parameters = array() ) {

		$connection = curl_init();

		$header = $this->prepare_request_header();

		$this->end_point_url = $this->end_point_url . $action;
		// echo "<pre>";
		// echo $this->end_point_url;

		$post_parameters['sAppId']  = $this->sAppId;
		$post_parameters['shop_id'] = $this->shop_id;

		// print_r( $post_parameters );
		// print_r( $header );
		curl_setopt( $connection, CURLOPT_URL, $this->end_point_url );

		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );

		curl_setopt( $connection, CURLOPT_POST, 1 );

		if ( ! empty( $post_parameters ) ) {
			curl_setopt( $connection, CURLOPT_POSTFIELDS, json_encode( $post_parameters ) );
		}

		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );

		$curl_error = curl_error( $connection );

		curl_close( $connection );

		$response = $this->parse_json_response( $response );
		return $response;
	}

	public function upload_product( $postData = array(), $action = '' ) {

		$connection = curl_init();

		$header = $this->prepare_request_header();

		$this->end_point_url = $this->end_point_url . $action;
		$this->prepare_endpoint_url();
		// print_r($this->end_point_url);
		curl_setopt( $connection, CURLOPT_URL, $this->end_point_url );

		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );

		// curl_setopt( $connection, CURLOPT_POST, 1 );
		curl_setopt( $connection, CURLOPT_CUSTOMREQUEST, 'PUT' );

		if ( ! empty( $postData ) ) {
			curl_setopt( $connection, CURLOPT_POSTFIELDS, json_encode( $postData ) );
		}
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );

		$curl_error = curl_error( $connection );

		curl_close( $connection );

		$response = $this->parse_json_response( $response );

		// echo '<pre>';
		// print_r($response);
		// die;
		return $response;
	}

	public function delete_product( $postData = array(), $action = '' ) {

		$connection = curl_init();

		$header = $this->prepare_request_header();

		$this->end_point_url = $this->end_point_url . $action;
		$this->prepare_endpoint_url( $postData );
		// print_r($this->end_point_url);
		// die;
		curl_setopt( $connection, CURLOPT_URL, $this->end_point_url );

		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );

		curl_setopt( $connection, CURLOPT_CUSTOMREQUEST, 'DELETE' );

		if ( ! empty( $postData ) ) {
			curl_setopt( $connection, CURLOPT_POSTFIELDS, json_encode( $postData ) );
		}

		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );

		$curl_error = curl_error( $connection );

		curl_close( $connection );
		// var_dump( $curl_error );
		// var_dump( $response );
		$response = $this->parse_json_response( $response );
		// var_dump( $response );
		// die;
		return $response;
	}

	public function update_product( $postData = array(), $action = '' ) {

		$connection = curl_init();

		$header = $this->prepare_request_header();

		$this->end_point_url = $this->end_point_url . $action;
		$this->prepare_endpoint_url();

		curl_setopt( $connection, CURLOPT_URL, $this->end_point_url );

		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );

		curl_setopt( $connection, CURLOPT_CUSTOMREQUEST, 'PUT' );

		if ( ! empty( $postData ) ) {
			curl_setopt( $connection, CURLOPT_POSTFIELDS, json_encode( $postData ) );
		}

		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );

		$curl_error = curl_error( $connection );

		curl_close( $connection );

		$response = $this->parse_json_response( $response );
		return $response;
	}

	/**
	 * Prepare endpoint url function
	 *
	 * @since    1.0.0
	 */
	public function prepare_endpoint_url( $parameters = array() ) {

		$args = array(
			// 'sAppId'  => $this->sAppId,
			'shop_id' => $this->shop_id,
		);
		if ( ! empty( $parameters ) ) {
			$args = array_merge( $args, $parameters );
		}
		$this->end_point_url = add_query_arg( $args, $this->end_point_url );
	}

	/**
	 * Prepare Header function
	 *
	 * @since    1.0.0
	 */
	public function prepare_request_header() {

		$token  = $this->getTokenFromRefresh();
		$header = array(
			"Authorization: Bearer $token",
			'Content-type: application/json',
		);
		return $header;
	}

	public function getTokenFromRefresh() {
		$refresh_token = get_option( 'ced_fmcw_refresh_token' );
		return $refresh_token;
	}

	public function parse_json_response( $response ) {

		if ( ! empty( $response ) ) {

			return json_decode( $response, true );
		}
	}

	/**
	 * Function load_depenedency
	 *
	 * @since 1.0.0
	 */
	public function load_depenedency() {

		$http_request_file = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-config.php';
		if ( file_exists( $http_request_file ) ) {
			include_once $http_request_file;
			$this->ced_fmcw_config_instance = Class_Ced_Fmcw_Config::get_instance();
		}
	}
}