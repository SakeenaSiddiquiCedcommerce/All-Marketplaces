<?php
/**
 * tokopediaRequest Class to handle different rquests.
 *
 * @class tokopediaRequest
 *
 * @version 1.0.0
 */
if (! class_exists ( 'tokopediaRequest' )) {
	class tokopediaRequest {
		/**
		 * tokopediaRequest Constructor.
		 */
		public function __construct() {
			
		}
		
		/**
		 * Function to call GET Curl Request 	
		 * @return array
		 * @since 1.0.0
		 *       
		 */

		public function sendCurlGetMethodForAcesssToken( $url_name="" , $client_id='' , $client_secret = '' ){
			
			if ( empty( $client_id ) ||$client_id == '' || $client_id == null|| empty( $client_secret ) || $client_secret == '' || $client_secret == null ) {
				return;
			}

			$baseString = $client_id . ':' . $client_secret;		
			$baseString = base64_encode( $baseString );
			$url = $this->ced_tokopedia_prepare_url_for_curl( $url_name );
			if( !empty( $baseString ) && !empty( $url ) ) {
				$curl = curl_init();				
				curl_setopt_array(
					$curl, array(			
					CURLOPT_URL            => $url,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST           => true,
					CURLOPT_HTTPHEADER     => array(
						'Authorization: Basic ' . $baseString,
						'Content-Length: 0',						
					),
				));
				$server_respose = curl_exec($curl);
				$err = curl_error($curl);
				curl_close( $curl );
				if ($err) {
					return "cURL Error #:" . $err;
				} else {
					return $server_respose;
				}
			}
		}

		/** 
		* Function to call GET Curl Request
		*/
		public function sendCurlGetMethod( $url_name="" , $shop_name='' , $uploaded_id = false ){
			
			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data     = ced_topedia_get_account_details_by_shop_name( $shop_name );
				
				$client_secret = $shop_data['client_secret'];
				$client_id     = $shop_data['client_id'];
				if( !  get_transient( 'ced_tokopedia_token' ) ) {
					$this->update_token( $shop_data );
				}				
				$access_token  = get_transient( 'ced_tokopedia_token' );
			}

			$url_name 			  = $this->ced_tokopedia_prepare_url_for_curl( $url_name , $shop_name , $uploaded_id );

			// echo "Url name :-  " . $url_name . "<br>";

			if( !empty( $access_token ) && !empty( $url_name ) ) {
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $url_name,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_SSL_VERIFYPEER => false,
				  CURLOPT_SSL_VERIFYHOST => false,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_ENCODING       => "",
				  CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST  => "GET",
				  CURLOPT_HTTPHEADER => array(
					"authorization: Bearer " . $access_token,
				  ),
				));

				$server_respose = curl_exec($curl);
				$err            = curl_error($curl);
				
				// var_dump($server_respose );
				// var_dump($access_token );
				// var_dump( $err );
				// die('Hero');
				
				curl_close($curl);
				if ($err) {
					return "cURL Error #:" . $err;
				} else {
					$response = json_decode( $server_respose , true );
					return $response;
				}
			}
		}
	
		public function update_token($shop_data = array()) {
			$client_secret = $shop_data['client_secret'];
				$client_id     = $shop_data['client_id'];
			$access_token = $this->sendCurlGetMethodForAcesssToken( 'ced_tokopedia_get_access_token', $client_id, $client_secret );
			if (!empty( $access_token ) ) {
				$access_token = json_decode( $access_token , true );
				
				$token        = isset( $access_token['access_token'] ) ? $access_token['access_token'] :'';
				$expires_in        = isset( $access_token['expires_in'] ) ? $access_token['expires_in'] :900;
				set_transient( 'ced_tokopedia_token' ,$token , $expires_in );
			}
		}

		/** 
		* Function to call POST Curl Request
		*/
		public function sendCurlPostMethod( $url_name='' , $bodyDataToSend = '' , $shop_name = '' ){

			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
				if( !  get_transient( 'ced_tokopedia_token' ) ) {
					$this->update_token( $shop_data );
				}				
				$access_token  = get_transient( 'ced_tokopedia_token' );
				
			}

			$url_name = $this->ced_tokopedia_prepare_url_for_curl( $url_name , $shop_name );
			
			// echo "URL =>" . $url_name . "<br>";
			// echo "Access Token =>" . $access_token . "<br>";
			// echo "<pre>";
			// print_r( $bodyDataToSend );
			// echo "</pre>";
 
			if (!empty( $url_name ) && !empty( $access_token ) ) {
				$curl = curl_init();			
				curl_setopt_array( $curl, array(
					
					CURLOPT_URL => $url_name,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_CUSTOMREQUEST  => "POST",
					CURLOPT_POST           => true,
					CURLOPT_POSTFIELDS =>  $bodyDataToSend,
					CURLOPT_HTTPHEADER => array(
						"Authorization: Bearer ".$access_token,
						"Content-Type: application/json",
					),
				));

				$server_respose = curl_exec( $curl );
				$err = curl_error($curl);

				// var_dump( $server_respose );
				// var_dump( $err );
				// var_dump( curl_errno( $curl ) );
				// die( "Post method to Update" );

				$server_respose = json_decode( $server_respose,true );
				curl_close($curl);

				if ($err) {
					return "cURL Error #:" . $err;
				} else {
					return $server_respose;
				}
			}
		}

		/** 
		* Function to call POST Curl Request
		*/
		public function sendshipmentCurlPostMethod( $action ='' , $bodyDataToSend = '' , $shop_name = '' ){

			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
				if( !  get_transient( 'ced_tokopedia_token' ) ) {
					$this->update_token( $shop_data );
				}				
				$access_token  = get_transient( 'ced_tokopedia_token' );
				
			}

			if (!empty( $action ) && !empty( $access_token ) ) {
				$curl = curl_init();
				if ( empty( $bodyDataToSend ) ) {
					curl_setopt_array( $curl, array(
						CURLOPT_URL => $action,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_CUSTOMREQUEST  => "POST",
						CURLOPT_POST           => true,
						CURLOPT_HTTPHEADER => array(
							"Authorization: Bearer ".$access_token,
							"Content-Type: application/json",
						),
					));
				}else{
					curl_setopt_array( $curl, array(
						CURLOPT_URL => $action,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_CUSTOMREQUEST  => "POST",
						CURLOPT_POST           => true,
						CURLOPT_POSTFIELDS =>  $bodyDataToSend,
						CURLOPT_HTTPHEADER => array(
							"Authorization: Bearer ".$access_token,
							"Content-Type: application/json",
						),
					));
				}

				$server_respose = curl_exec( $curl );
				$err = curl_error($curl);
				$server_respose = json_decode( $server_respose,true );
				curl_close($curl);
				if ($err) {
					return "cURL Error #:" . $err;
				} else {
					return $server_respose;
				}
			}
		}

		/** 
		* Function to call PACH Curl Request
		*/
		public function sendCurlPatchMethod( $url_name='' , $bodyDataToSend = '' , $shop_name = '' ){

			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
				if( !  get_transient( 'ced_tokopedia_token' ) ) {
					$this->update_token( $shop_data );
				}				
				$access_token  = get_transient( 'ced_tokopedia_token' );
			}
			$url = $this->ced_tokopedia_prepare_url_for_curl( $url_name , $shop_name  );
			if ( !empty( $url) && !empty( $access_token ) ) {
				$curl = curl_init();
				curl_setopt_array( $curl, array(
					
					CURLOPT_URL => $url,
					CURLOPT_FOLLOWLOCATION => true,				
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_RETURNTRANSFER => true,				
					CURLOPT_CUSTOMREQUEST  => "PATCH",
					CURLOPT_POSTFIELDS     => $bodyDataToSend,
					CURLOPT_HTTPHEADER     => array(
						"Authorization:Bearer ".$access_token,
						"Content-Type: application/json",
						
					),
				));

				$server_respose = curl_exec($curl);
				$server_respose = json_decode( $server_respose , true );	
				$err = curl_error($curl);			
				curl_close($curl);			
				if ($err) {
					return "cURL Error #:" . $err;
				} else {
					return $server_respose;
				}
			}
		}



		/** 
		* Function to call DELETE Curl Request
		*/
		public function sendCurlDeleteMethod($url_name='' , $bodyDataToSend = '' , $shop_name = '' ){
			
			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
				if( !  get_transient( 'ced_tokopedia_token' ) ) {
					$this->update_token( $shop_data );
				}				
				$access_token  = get_transient( 'ced_tokopedia_token' );
			}

			$url = $this->ced_tokopedia_prepare_url_for_curl( $url_name , $shop_name );
			if ( !empty( $url ) && !empty( $access_token ) ) {
				$curl = curl_init();			
				curl_setopt_array($curl, array(
					
					CURLOPT_URL => $url,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_RETURNTRANSFER => true,					
					CURLOPT_CUSTOMREQUEST  => "DELETE",
					CURLOPT_POSTFIELDS =>$bodyDataToSend,
					CURLOPT_HTTPHEADER => array(
						"Authorization:Bearer ".$access_token,
						"Content-Type: application/json",
					),
				));
				$server_respose = curl_exec($curl);	
				$err = curl_error($curl);
				curl_close($curl);
				if ($err) {
					return "cURL Error #:" . $err;
				} else {
					return $server_respose;
				}	
			}
		}

		private function ced_tokopedia_prepare_url_for_curl( $url_name='' , $shop_name='' , $uploaded_id = false ) {
			
			$last_created_order = strtotime( '-1 days' );
			// $current_time       = strtotime( 'now' );
			$current_time       = strtotime( '+1 day' );
			
			// strtotime('+1 day', strtotime($date)))
			$order_id           = $uploaded_id;

			if ( isset( $shop_name ) && ! empty( $shop_name ) ) {
				$shop_data    = ced_topedia_get_account_details_by_shop_name( $shop_name );
				$shop_id      = $shop_data['shop_id'];
				if( !  get_transient( 'ced_tokopedia_token' ) ) {
					$this->update_token( $shop_data );
				}				
				$access_token  = get_transient( 'ced_tokopedia_token' );
				$fsid         = $shop_data['fsid'];
			}

			if (!empty( $url_name )) {
				switch ( $url_name ) {
					case 'ced_tokopedia_get_access_token':
						$sending_url = 'https://accounts.tokopedia.com/token?grant_type=client_credentials';
						break;
					case 'get_etalase_ids':
						$sending_url = 'https://fs.tokopedia.net/inventory/v1/fs/' . $fsid . '/product/etalase?shop_id=' . $shop_id;
						break;
					case 'get_tokopedia_category':
						$sending_url = 'https://fs.tokopedia.net/inventory/v1/fs/' . $fsid . '/product/category';
						break;
					case 'upload_the_products':
						$sending_url = 'https://fs.tokopedia.net/v3/products/fs/' . $fsid . '/create?shop_id=' . $shop_id;
						break;
					case 'get_uploaded_status':
						$sending_url = 'https://fs.tokopedia.net/v2/products/fs/' . $fsid . '/status/' . $uploaded_id . '?shop_id=' . $shop_id;
						break;
					case 'update_product':
						$sending_url = 'https://fs.tokopedia.net/v3/products/fs/' . $fsid . '/edit?shop_id=' . $shop_id;
						break;
					case 'update_product_price':
						$sending_url = 'https://fs.tokopedia.net/inventory/v1/fs/' . $fsid . '/price/update?shop_id=' . $shop_id;
						break;
					case 'update_product_stock':
						$sending_url = 'https://fs.tokopedia.net/inventory/v1/fs/' . $fsid . '/stock/update?shop_id=' . $shop_id;
						break;
					case 'set_activate_product':
						$sending_url = 'https://fs.tokopedia.net/v1/products/fs/' . $fsid . '/active?shop_id=' . $shop_id;
						break;
					case 'set_inactivate_product':
						$sending_url = 'https://fs.tokopedia.net/v1/products/fs/' . $fsid . '/inactive?shop_id=' . $shop_id;
						break;
					case 'delete_product':
						$sending_url = 'https://fs.tokopedia.net/v3/products/fs/' . $fsid . '/delete?shop_id=' . $shop_id;
						break;
					case 'get_variation_by_category_id':
						$sending_url = 'https://fs.tokopedia.net/inventory/v1/fs/' . $fsid . '/category/get_variant?cat_id=' . $uploaded_id; /*is Category_id*/
						break;
					case 'get_all_orders':
						$sending_url = 'https://fs.tokopedia.net/v2/order/list?fs_id='.$fsid. '&shop_id=' .$shop_id . '&from_date=' . $last_created_order . '&to_date=' . $current_time . '&page=1&per_page=15';
						break;
					case 'register_ip_whitelist':
						$sending_url = 'https://fs.tokopedia.net/v1/fs/'.$fsid.'/whitelist';
						break;
					case 'get_ip_whitelist':
						$sending_url = 'https://fs.tokopedia.net/v1/fs/'.$fsid.'/whitelist';
						break;
					case 'get_order_shipment_level':
						$sending_url = 'https://fs.tokopedia.net/v1/order/'. $order_id .'/fs/'.$fsid.'/shipping-label?printed=0';
						break;
					default:
						$sending_url = 'Not Exist ' . $url_name;
						break;
				}	
			}
			return $sending_url;
		}
	}
}
?>