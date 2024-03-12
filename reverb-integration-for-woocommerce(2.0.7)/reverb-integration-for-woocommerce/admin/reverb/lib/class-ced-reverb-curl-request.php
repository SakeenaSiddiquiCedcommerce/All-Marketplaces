<?php
/**
 * Curl requests
 *
 * @package  reverb_Integration_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Ced_reverb_Curl_Request
 *
 * @since 1.0.0
 */
class Ced_Reverb_Curl_Request {
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
	 * Ced_reverb_Curl_Request Instance.
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
	 * Ced_reverb_Curl_Request construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_depenedency();
		$this->end_point_url = $this->ced_reverb_config_instance->end_point_url;
		$this->client_id     = $this->ced_reverb_config_instance->client_id;
		$this->environment   = $this->ced_reverb_config_instance->environment;
	}


	/**
	 * * Function to get all reverb categories details
	 */
	public function getValidatedCredentials( $authorization_key = '' ) {

		$saved_reverb_details = get_option( 'ced_reverb_configuration_details', array() );

		$account_type = isset( $saved_reverb_details['environment'] ) ? $saved_reverb_details['environment'] : '';

		$curl = curl_init();

		curl_setopt_array(
			$curl,
			array(

				CURLOPT_URL            => 'https://' . $account_type . '.reverb.com/api/shop',
				CURLOPT_FOLLOWLOCATION => true,

				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,

				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'Authorization:Bearer ' . $authorization_key,
					'Accept: application/json',
					'Accept-Version: 3.0',

				),
			)
		);
		$server_respose = curl_exec( $curl );
		$server_respose = json_decode( $server_respose, true );

		

		$err = curl_error( $curl );

		curl_close( $curl );

		if ( 'You must log in to access this endpoint' == $server_respose ) {
			return 'false';
		} else {
			return 'true';
		}
	}

	/**
	 * Ced_reverb_Curl_Request ced_reverb_get_request
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_get_request( $action = '', $query_args = array() ) {

		
		if ( empty( $action ) ) {
			return;
		}

		$header = $this->get_headers();

		$api_url = $this->end_point_url . $action;

		$connection = curl_init();
		curl_setopt( $connection, CURLOPT_URL, $api_url );
		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $connection, CURLOPT_HEADER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );

		$response = curl_exec( $connection );

		$error    = curl_error( $connection );
		curl_close( $connection );

		if ( $error ) {
			echo json_encode(
				array(
					'status'  => 400,
					'message' => 'Curl error : ' . $error,
				)
			);
			die;
		}

		$response = $this->parse_response( $response );
		return $response;
	}

	/**
	 * Ced_reverb_Curl_Request ced_reverb_request
	 *
	 * @since 1.0.0
	 */
	public function ced_reverb_request( $action = '', $parameters = array(), $query_args = array(), $request_type = 'POST' ) {

		if ( empty( $action ) ) {
			return;
		}

		$header  = $this->get_headers();
		$api_url = $this->end_point_url . $action;

		$connection = curl_init();
		curl_setopt( $connection, CURLOPT_URL, trim( $api_url ) );
		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $connection, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $connection, CURLOPT_CUSTOMREQUEST, $request_type );
		curl_setopt( $connection, CURLOPT_POSTFIELDS, $parameters );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );

		$response = curl_exec( $connection );
		$error    = curl_error( $connection );

		curl_close( $connection );
		if ( $error ) {
			echo json_encode(
				array(
					'status'  => 400,
					'message' => 'Curl error : ' . $error,
				)
			);
			die;
		}

		$response = $this->parse_response( $response );
		return $response;
	}

	/**
	 * Function for get_headers
	 *
	 * @since 1.0.0
	 */
	public function get_headers() {
		$headers[] = 'Authorization:Bearer ' . $this->client_id;
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json';
		$headers[] = 'Accept-Version: 3.0';
		return $headers;
	}


	/**
	 * Function for parse_response
	 *
	 * @since 1.0.0
	 * @param string $response Response from reverb.
	 */
	public function parse_response( $response ) {
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
		$file = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-config.php';
		if ( file_exists( $file ) ) {
			include_once $file;
			$this->ced_reverb_config_instance = Ced_Reverb_Config::get_instance();
		}
	}
}
