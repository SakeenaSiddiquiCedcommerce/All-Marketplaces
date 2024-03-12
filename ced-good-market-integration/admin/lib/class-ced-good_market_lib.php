<?php

class Ced_Good_Market_Request {

	/**
	 * Base URL for Good Market API.
	 *
	 * @var string
	 */
	public $base_url = 'https://12camels.com/graphql';

	/**
	 * Delete method Good Market API.
	 *
	 * @since    1.0.0

	/**
	 * *************************
	 *  POST METHOD Good Market API
	 * *************************
	 *
	 * @param string $action
	 * @param array  $parameters
	 * @param string $shop_name
	 * @param array  $query_args
	 * @param string $request_type
	 * @param string $content_type
	 * @return array
	 */

	public function good_market_post( $post_fields ) {

		$apiUrl                  = 'https://www.goodmarket.global/graphql';
		// $apiUrl       = 'https://staging.goodmarket.info/graphql';
		$api_response = wp_remote_post(
			$apiUrl,
			array(
				'body'        => $post_fields,
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'httpversion' => '1.0',
				'sslverify'   => false,
				'timeout'     => 120,
			)
		);
		if ( is_wp_error( $api_response ) ) {
			$error_message = $api_response->get_error_message();
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => $error_message,
				)
			);
		} else {
			$api_response = $api_response['body'];
		}
		return $api_response;
		die;
	}

	public function get_api_related_data() {
		$decoded_api_data     = array();
		$api_json_data        = get_option( 'good_market_data' );
		$save_details         = get_option( 'api_details' );
		$save_details_display = json_decode( $save_details, 1 );
		if ( ! empty( $api_json_data['data'] ) ) {
			$decoded_api_data = json_decode( $api_json_data['data'], 1 );
			$vendor_id        = isset( $decoded_api_data['data']['generateVendorToken']['vendor_id'] ) ? esc_attr( $decoded_api_data['data']['generateVendorToken']['vendor_id'] ) : '';
			$decoded_api_data['data']['generateVendorToken']['vendor_id'] = $vendor_id;
		}
		return $decoded_api_data;
	}

	public function get_good_market_attributes( $profile_id ) {

		$get_api_related_data = $this->get_api_related_data();
		$post_data            = array();
		$post_data['query']   = 'query getProductFormAttributes($category_id: Int!, $product_type: String!, $product_id: Int, $vendor_id: Int!, $hash_token:String) {
			productAllowedAttributes(category_id: $category_id, product_type: $product_type, product_id:$product_id ,vendor_id:$vendor_id, hash_token : $hash_token) {
				groupwise_attributes
				configurable_attributes {
					label
					title
					attribute_code
					name
					class
					value
					options
				}
				configurable_variants {
					name
					sku
					qty
					price
					weight
					attributes
					image
				}
				success
				message
			}
		}';
		if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) ) {

			$post_data['variables']['category_id']  = $profile_id;
			$post_data['variables']['vendor_id']    = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
			$post_data['variables']['product_id']   = 0;
			$post_data['variables']['product_type'] = 'configurable';
			$post_data['variables']['hash_token']   = $get_api_related_data['data']['generateVendorToken']['hash_token'];
		}
		$data_to_send_attri = json_encode( $post_data );
		$product_attri      = $this->good_market_post( $data_to_send_attri );
		return $product_attri;
	}
}