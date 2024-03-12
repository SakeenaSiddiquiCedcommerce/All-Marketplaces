<?php
/**
 * Class file to manage the store related api calls
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
 * Class_Ced_Fmcw_Store_Api_Handle
 *
 * @since 1.0.0
 */
class Class_Ced_Fmcw_Store_Api_Handle {

	private static $_instance;
	/**
	 * Get_instance Instance.
	 *
	 * Ensures only one instance of CedwTiFindProducts is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return get_instance instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_cms_settings() {

		$action = 'webapi/rest/v1/cms';

		$fileNmae = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		include_once $fileNmae;
		$ced_fmcw_send_request = new Class_Ced_Fmcw_Send_Http_Request();

		$response = $ced_fmcw_send_request->get_request( $action );
		return $response;
	}

	public function authenticate_cms_page( $merchant_page_id = '', $page_id = '' ) {

		$action          = 'webapi/rest/v1/cms/authenticate';
		$post_parameters = array();
		$post_parameters = array(
			'id'      => $merchant_page_id,
			'page_id' => $page_id,
		);
		$fileNmae        = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		include_once $fileNmae;
		$ced_fmcw_send_request = new Class_Ced_Fmcw_Send_Http_Request();

		$response = $ced_fmcw_send_request->post_request( $action, $post_parameters );
		return $response;
	}
}