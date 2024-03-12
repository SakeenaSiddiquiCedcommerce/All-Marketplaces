<?php


/**
 * The admin-product related functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/admin
 */


class Class_Ced_Tokopedia_Products {
	/**
	 * Creating instance of this product class
	 *
	 * @var instance
	 */

	public static $_instance;
	/**
	 * Ced_Tokopedia_Config Instance.
	 *
	 * Ensures only one instance of Ced_Tokopedia_Config is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public function __construct() {
		$requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
		if ( file_exists( $requrest_tokopedia_file ) ) {
			require_once $requrest_tokopedia_file;
		}
		$this->obj_request = new tokopediaRequest();
	}

	/**
	 * Function for products data to be uploaded.
	 *
	 * @since 1.0.0
	 * @param array  $proIDs all prdoduct ids.
	 * @param string $shop_name active shop name of shop.
	 */
	public function prepareDataForUploading( $proIDs = array(), $shop_name ) {
		if ( is_array( $proIDs ) && ! empty( $proIDs ) ) {
			$shop_name = trim( $shop_name );
			self::prepareItems( $proIDs, $shop_name );
			$response = $this->uploadResponse;
			return $response;

		}
	}

	/**
	 * Function for preparing product data to be uploaded.
	 *
	 * @since 1.0.0
	 */
	public function prepareItems( $proIDs = array(), $shop_name = '' ) {

		$global_settings_data = get_option( 'ced_tokopedia_global_settings','' );
		$settings_data        = isset( $global_settings_data[$shop_name] ) ? $global_settings_data[$shop_name] : array();
		$product_list         = isset( $settings_data['ced_tokopedia_product_list_type'] ) ? $settings_data['ced_tokopedia_product_list_type'] : '';

		foreach ( $proIDs as $key => $value ) {

			$productData     = wc_get_product( $value );
			$image_id        = get_post_thumbnail_id( $value );
			$productType     = $productData->get_type();
			$alreadyUploaded = false;

			if ( 'variable' == $productType ) {
				// Get formatted Data
				$preparedData = $this->ced_tokopedia_get_formatted_data( $value, $shop_name ,'for_variable' );
				if ( 'Profile Not Assigned' == $preparedData || 'Quantity Cannot Be 0' == $preparedData ) {
					$error                = array();
					$error['msg']         = $preparedData;
					$this->uploadResponse = $error;
					return $this->uploadResponse;
				}

				$this->prepared_data = $preparedData;
				self::doUploadProductsTokopedia( $value, $shop_name );
				$response = $this->uploadResponse;
				if ( isset( $response['data']['success_rows_data'][0]['product_id'] ) ) {
					$upload_id = isset( $response['data']['success_rows_data'][0] ) ? $response['data']['success_rows_data'][0] : '';
					update_post_meta( $value, '_ced_tokopedia_upload_id_' . $shop_name, $upload_id );
					if ( $product_list =='true' ) {
						$activate = $this->ced_tokopedia_set_activate_inactive_product( $upload_id , $shop_name ,true );
					}else{
						$deactivate = $this->ced_tokopedia_set_activate_inactive_product( $upload_id , $shop_name ,false );		
					}
				}else{
					if (isset( $result['msg'] )) {
						$this->uploadResponse = $result;
					} else{
						$error_message['msg'] = isset( $result['header']['reason'] ) ? $result['header']['reason'] : 'Product is Not Uploaded on Tokopedia';
						$this->uploadResponse = $error_message;
					}
				}
			} else {

				$error_message = array();
				// Get formatted Data
				$preparedData = $this->ced_tokopedia_get_formatted_data( $value, $shop_name , 'for_simple' );
				if ( 'Profile Not Assigned' == $preparedData || 'Quantity Cannot Be 0' == $preparedData ) {
					$error                = array();
					$error['msg']         = $preparedData;
					$this->uploadResponse = $error;
					return $this->uploadResponse;
				}

				$this->prepared_data = $preparedData;
				self::doUploadProductsTokopedia( $value, $shop_name );
				$result  = $this->uploadResponse;
				if ( isset( $result['data']['success_rows_data'][0]['product_id'] ) ) {
					$upload_id = isset( $result['data']['success_rows_data'][0]['product_id'] ) ? $result['data']['success_rows_data'][0]['product_id'] : '';
					
					update_post_meta( $value, '_ced_tokopedia_upload_id_' . $shop_name, $upload_id );
					if ( $product_list =='true' ) {
						$activate = $this->ced_tokopedia_set_activate_inactive_product( $upload_id , $shop_name ,true );
					}else{
						$deactivate = $this->ced_tokopedia_set_activate_inactive_product( $upload_id , $shop_name ,false );
					}
				}else{
					if (isset( $result['msg'] )) {
						$this->uploadResponse = $result;
					} else{
						$error_message['msg'] = isset( $result['header']['reason'] ) ? $result['header']['reason'] : 'Product is Not Uploaded on Tokopedia';
						$this->uploadResponse = $error_message;
					}
				}
			}
		}
		return $this->uploadResponse;
	}


	/**
	 * Uploadig to tokopedia
	 *
	 * @since    1.0.0
	 */
	public function doUploadProductsTokopedia( $product_id = '', $shop_name = '' ) {

		// ini_set('display_errors', '1' );
		// ini_set('display_startup_errors', '1' );
		// error_reporting(E_ALL);

		$error_msg = array();
		$params = json_encode( $this->prepared_data ,true );
		$result = $this->obj_request->sendCurlPostMethod( 'upload_the_products' , $params , $shop_name );
		
// 		echo "<pre>";
// 		print_r( $params );
// 		print_r( $result );
		
		if ( isset( $result['data']['failed_rows_data'][0]['error'][0] ) || ! empty( $result['data']['failed_rows_data'][0]['error'][0] ) ) {
			$error_msg['msg'] = isset( $result['data']['failed_rows_data'][0]['error'][0] ) ? ucwords( $result['data']['failed_rows_data'][0]['error'][0] ) : 'Product Is Not Uploaded';
			$this->uploadResponse = $error_msg;
		} elseif( isset( $result['header']['reason'] ) || !empty( $result['header']['reason'] ) ){
			$error_msg['msg'] = isset( $result['header']['reason'] ) ? ucfirst( $result['header']['reason'] ) : 'Product Not Uploaded on Tokopedia !';
			$this->uploadResponse = $error_msg;
		}else {
			$this->uploadResponse = $result;
		}
	}

	/**
	 * Function for activating product
	 *
	 * @since 1.0.0
	 */
	public function ced_tokopedia_set_activate_inactive_product( $uploaded_id = '', $shop_name ='' , $activate_product = false  ) {
		$params = json_encode( array( "product_id" => array( (int)$uploaded_id ) ) , true );
		if ( $activate_product ) {
			$response = $this->obj_request->sendCurlPostMethod( 'set_activate_product' , $params , $shop_name );
			return $response;
		} else{
			$response = $this->obj_request->sendCurlPostMethod( 'set_inactivate_product' , $params , $shop_name );
			return $response;
		}
	}

	 /**
	  * Function for Check uploaded product Status
	  *
	  * @since 1.0.0
	  */
	public function getUploadedProductStatus( $uploadId = '', $shop_name = '' ) {	
		$result = $this->obj_request->sendCurlGetMethod( 'get_uploaded_status', $shop_name ,$uploadId );
		$response = isset( $result['data']['status'] ) ? $result['data']['status'] : 'No Uploaded';
		return $response;
	}
	 /**
	  * Function for deleting product.
	  *
	  * @since 1.0.0
	  */

	public function prepareDataForDelete( $proIDs = array(), $shop_name = '' ) {

		$message = array();
		foreach ( $proIDs as $key => $proID ) {
			
// 			delete_post_meta( $proID, '_ced_tokopedia_upload_id_' . $shop_name );
// 			die('Ambikesh now');
			
			$uploaded_id          = get_post_meta( $proID, '_ced_tokopedia_upload_id_' . $shop_name, true );
			$params               = json_encode( array( "product_id" => array( (int)$uploaded_id ) ) , true );
			$result               = $this->obj_request->sendCurlPostMethod( 'delete_product' , $params , $shop_name );
			//print_r($result);
			if ( isset( $result['data']['succeed_rows'] ) && empty( $result['data']['failed_rows'] ) ) {
				delete_post_meta( $proID, '_ced_tokopedia_upload_id_' . $shop_name );
				return $result;
			}else{
				$message['msg'] = isset( $result['data']['failed_rows_data'][0] ) ? $result['data']['failed_rows_data'][0] : get_the_title( $proID ) . 'Not Delete From Tokopedia';
				return $message;
			}
		}

	}

	/**
	 * Function for preparing product data to be updated.
	 *
	 * @since 1.0.0
	 */

	public function prepareDataForUpdating( $proIDs = array(), $shop_name = '' ) {
		
		$global_settings_data = get_option( 'ced_tokopedia_global_settings','' );
		$settings_data        = isset( $global_settings_data[$shop_name] ) ? $global_settings_data[$shop_name] : array();
		$product_list         = isset( $settings_data['ced_tokopedia_product_list_type'] ) ? $settings_data['ced_tokopedia_product_list_type'] : '';

		foreach ( $proIDs as $key => $value ) {			
			$arguments = $this->ced_tokopedia_get_formatted_data( $value , $shop_name , 'for_update_product' );

			if ( ! empty( $arguments ) ) {
				$params = json_encode( $arguments ,true );
				$result = $this->obj_request->sendCurlPatchMethod( 'update_product' , $params , $shop_name );

				// echo "<pre>";
				// print_r( $result );
				
				if ( isset( $result['data']['success_data'] ) && !empty( $result['data']['success_rows_data'][0]['product_id'] ) ) {
					$uploaded_id = $result['data']['success_rows_data'][0]['product_id'];
					if ( $product_list =='true' ) {
						$this->ced_tokopedia_set_activate_inactive_product( $uploaded_id , $shop_name ,true );
					}else{
						$this->ced_tokopedia_set_activate_inactive_product( $uploaded_id , $shop_name ,false );				
					}
					return $result;
				} else{

					if ( isset( $result['header']['reason'] ) ) {
						$error_msg['msg'] = isset( $result['header']['reason'] ) ? $result['header']['reason'] : 'Product Not Updated';
					} else {
						$error_msg['msg'] = isset( $result['data']['failed_rows_data'][0]['error'][0] ) ? $result['data']['failed_rows_data'][0]['error'][0] : 'Product Not Updated';
					}

					return $error_msg;
				}
			}
		}

		return $this->uploadResponse;
	}
	
	/**
	 * Function for preparing product price to be updated.
	 *
	 * @since 1.0.0
	 */

	public function prepareDataForUpdatingPrice( $proIDs = array(), $shop_name = '' ) {

		if ( is_array( $proIDs ) && ! empty( $proIDs ) ) {
			foreach ( $proIDs as $key => $proID ) {

				$global_settings_data = get_option( 'ced_tokopedia_global_settings','' );
				$settings_data        = isset( $global_settings_data[$shop_name] ) ? $global_settings_data[$shop_name] : array();

				$profileData = $this->getProfileAssignedData( $proID, $shop_name );
				$uploaded_id = get_post_meta( $proID, '_ced_tokopedia_upload_id_' . $shop_name, true );

				$profile_cc_rate     = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_cc_rate' );		
				if (empty( $profile_cc_rate ) ) {
					$profile_cc_rate = isset( $settings_data['ced_tokopedia_currency_conversion_rate'] ) ? $settings_data['ced_tokopedia_currency_conversion_rate'] : 1;
				}

				if ( 'false' == $profileData && ! $isPreview ) {
					return 'Profile Not Assigned';
				}

				$product = wc_get_product( $proID );
				if ( WC()->version > '3.0.0' ) {
					$productData  = $product->get_data();
					$productType  = $product->get_type();
					$sku          = $product->get_sku();
				}
				if ( 'variable' == $productType ) {

					$variations = $product->get_available_variations();
					foreach ($variations as $variation ) {
						$var_price    = get_post_meta( $variation['variation_id'] , '_price' , true );
						$var_sku   = get_post_meta( $variation['variation_id'] , '_sku' , true );
						$markup_price = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_markup_price' );
						$markup_type  = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_markup_type' );
						if ( !empty( $markup_price ) ) {
							if ( $markup_type == 'percentage') {
								$productPrice = $var_price + ( $markup_price / 100 * $var_price );
							} else{
								$productPrice = $markup_price + $var_price;
							}
						} else {
							$productPrice = (int) get_post_meta( $variation['variation_id'] , '_price' , true );
						}

						if ( ! empty( $var_sku ) ) {
							$value_to_update = array(
								'sku'       => $var_sku,
								'new_price' => $productPrice,
							);
						} else {
							$value_to_update = array(
								'product_id' => $uploaded_id,
								'new_price'  => $productPrice,
							);
						}

						$params[] = $value_to_update;
					}
				} else {		
					$productPrice = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_markup_price' );
					$markup_type  = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_markup_type' );
					if ( !empty( $productPrice ) ) {
						if ( $markup_type == 'percentage') {
							$productPrice = $productData['price'] + ( $productPrice / 100 * $productData['price'] );
						} else{
							$productPrice = $productPrice + $productData['price'];
						}
					} else {
						$productPrice = (int) $productData['price'];
					}

					$productPrice = $productPrice * $profile_cc_rate;

					if ( ! empty( $sku ) ) {
						$value_to_update = array(
							'sku'       => $sku,
							'new_price' => $productPrice,
						);
					} else {
						$value_to_update = array(
							'product_id' => $uploaded_id,
							'new_price'  => $productPrice,
						);
					}

					$params[] = $value_to_update;
				}

			}

			$params = json_encode( $params, true );
			$result = $this->obj_request->sendCurlPostMethod( 'update_product_price' , $params , $shop_name );
			$error_msg = array();
			if ( isset( $result['data']['succeed_rows'] ) && empty( $result['data']['failed_rows'] )  ) {
				return $result;
			} else{
				$error_msg['msg'] = isset( $result['data']['failed_rows_data'][0]['message'] ) ? ucwords( $result['data']['failed_rows_data'][0]['message'] ) : 'Price Not Updated';
				return $error_msg;
			}
				
		}

	}

	 /**
	  * Function for preparing product stock to be updated.
	  *
	  * @since 1.0.0
	  */

	public function prepareDataForUpdatingStock( $proIDs = array(), $shop_name = '' ) {

		if ( is_array( $proIDs ) && ! empty( $proIDs ) ) {
			foreach ( $proIDs as $key => $proID ) {

				$profileData = $this->getProfileAssignedData( $proID, $shop_name );
				$uploaded_id = get_post_meta( $proID, '_ced_tokopedia_upload_id_' . $shop_name, true );
				if ( 'false' == $profileData ) {
					return 'Profile Not Assigned';
				}
				
				$product = wc_get_product( $proID );

				if ( WC()->version > '3.0.0' ) {

					$productData  = $product->get_data();
					$productType  = $product->get_type();

					if ( 'variable' == $productType ) {
						$variations = $product->get_available_variations();
						foreach ($variations as $variation ) {
							$var_stock = get_post_meta( $variation['variation_id'] , '_stock' , true );
							$var_sku   = get_post_meta( $variation['variation_id'] , '_sku' , true );
							if ( ! empty( $var_sku ) ) {
								$value_to_update = array(
									'sku'       => $var_sku,
									'new_stock' => $var_stock,
								);
							} else {
								 $value_to_update = array(
									 'product_id' => $uploaded_id,
									 'new_stock'  => $var_stock,
								 );
							}
							$params[] = $value_to_update;
						}
					} else{

						$sku          = $product->get_sku();
						$stock_qty    = $product->get_stock_quantity();
		 				$manage_stock = get_post_meta( $proID , '_manage_stock' , true ) ;
		 				$stock_status = get_post_meta( $proID , '_stock_status' , true) ;
						if ( trim($stock_status) == 'instock' &&  trim($manage_stock) == 'no' ) {
		 					$stock_qty = 1 ;
		 				}
						if ( ! empty( $sku ) ) {
							$value_to_update = array(
								'sku'       => $sku,
								'new_stock' => $stock_qty,
							);
						} else {
							 $value_to_update = array(
								 'product_id' => $uploaded_id,
								 'new_stock'  => $stock_qty,
							 );
						}
						$params[] = $value_to_update;

					}

				}

			}

			$params = json_encode( $params , true );
			$result = $this->obj_request->sendCurlPostMethod( 'update_product_stock' , $params , $shop_name );
			$error_msg = array();
			if ( isset( $result['data']['succeed_rows'] ) && empty( $result['data']['failed_rows'] )  ) {
				return $result;
			} else{
				$error_msg['msg'] = isset( $result['data']['failed_rows_data'][0]['message'] ) ? ucwords( $result['data']['failed_rows_data'][0]['message'] ) : 'Stock Not Updated';
				return $error_msg;
			}
		}

	}

	public function ced_tokopedia_get_formatted_data( $proID = array(), $active_shop = '', $calling_from = '' ) {

		$profileData          = $this->getProfileAssignedData( $proID, $active_shop );
		$categoryId           = $this->fetchMetaValueOfProduct( $proID, '_umb_tokopedia_category' );
		$global_settings_data = get_option( 'ced_tokopedia_global_settings','' );
		$settings_data        = isset( $global_settings_data[$active_shop] ) ? $global_settings_data[$active_shop] : array();

		if ( 'false' == $profileData ) {
			return 'Profile Not Assigned';
		}

		$product    = wc_get_product( $proID );
		$image_id   = get_post_thumbnail_id( $proID );
		$image_path = get_attached_file( $image_id );
		$image_url  = wp_get_attachment_url( $image_id );
		$image_data = file_get_contents( $image_url );

		if ( WC()->version > '3.0.0' ) {

			$productData        = $product->get_data();
			$productType        = $product->get_type();
			$sku                = $product->get_sku();
			$currency           = get_woocommerce_currency();
			$status  = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_product_status' );
			if ( empty( $status ) ) {
				$status  = isset( $settings_data['ced_tokopedia_product_status'] ) ? $settings_data['ced_tokopedia_product_status'] : '';
				if ( empty( $status )  ) {		
					if ( ! $product->managing_stock() && ! $product->is_in_stock() ) {
						$status = 'EMPTY';
					} elseif ( $product->is_in_stock() < 1 ) {
						$status = 'UNLIMITED';
					} else {
						$status = 'LIMITED';
					}
				}
			}

		}

		$productTitle       = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_title' );		
		if ( empty( $productTitle ) ) {
			$productTitle = $productData['name'];
		}

		$productDescription = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_description' );
		if ( empty( $productDescription ) ) {
			$productDescription = empty( $productData['description'] ) ? $productData['description'] : $productData['short_description'];
		}

		$productPrice = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_markup_price' );
		$markup_type  = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_markup_type' );
		if ( !empty( $productPrice ) ) {
			if ( $markup_type == 'percentage') {
				$productPrice = $productData['price'] + ( $productPrice / 100 * $productData['price'] );
			} else{
				$productPrice = $productPrice + $productData['price'];
			}
		} else {
			$productPrice = (int) $productData['price'];
		}

		$productQuantity       = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_stock' );
		if ( empty( $productQuantity ) ) {
			$productQuantity = get_post_meta( $proID, '_stock', true );
			if ( 'variable' == $productType ) {
				$productQuantity = 1;
			}
		}

		$profile_cc_rate     = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_cc_rate' );		
		if (empty( $profile_cc_rate ) ) {
			$profile_cc_rate = isset( $settings_data['ced_tokopedia_currency_conversion_rate'] ) ? $settings_data['ced_tokopedia_currency_conversion_rate'] : 1;
		}

		$weight_unit   = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_weight_unit' );
		
		if ( empty( $weight_unit ) ) {
			$weight_unit = isset( $settings_data['ced_tokopedia_weight_unit'] ) ? $settings_data['ced_tokopedia_weight_unit'] : get_option( 'woocommerce_weight_unit' );
		}

		$weight   = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_weight' );
		if ( empty( $weight ) ) {
			$weight = $product->get_weight();
		}

		$min_order = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_min_order' );		
		if ( empty( $min_order ) ) {
			$min_order = 1;	
		}

		if ( 'variable' == $productType ) {

			$variations = $product->get_available_variations();
			if ( isset( $variations['0']['display_regular_price'] ) ) {
				$productPrice = $variations['0']['display_regular_price'];
			}
		} else {

			$manage_stock = get_post_meta( $proID, '_manage_stock', true );
			$stock_status = get_post_meta( $proID, '_stock_status', true );
			if ( trim( $stock_status ) == 'instock' && trim( $manage_stock ) == 'no' ) {
				$productQuantity = 1;
			}
		}

		$productCondition = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_product_condition' );
		if ( empty( $productCondition ) ) {
			$productCondition = isset( $settings_data['ced_tokopedia_product_condition'] ) ? $settings_data['ced_tokopedia_product_condition'] : 'NEW';
		}

		$etalase_id = $this->fetchMetaValueOfProduct( $proID, '_ced_tokopedia_etalase_id' );

		if ( 'variable' == $productType ) {
			/** VARIATION DATA PREPARING */

			$_product          = wc_get_product( $proID );
			$variations        = $_product->get_available_variations();
			$productAttributes = $_product->get_variation_attributes();
			$is_primary_prod   = true;
			$variation_product = array();

			foreach ( $variations as $variation ) {

				$product_id           = $variation['variation_id'];
				$var_product          = wc_get_product( $product_id );
				$productAttributes    = $var_product->get_variation_attributes();
				$manage_stock         = get_post_meta( $variation['variation_id'], '_manage_stock', true );
				$stock_status         = get_post_meta( $variation['variation_id'], '_stock_status', true );
				$var_price            = (float) $variation['display_price'];

				if ( 'instock' == trim( $stock_status ) ) {
					$var_status = 'LIMITED';
				} else {
					$var_status = 'EMPTY';
				}

				if ( trim( $manage_stock ) == 'yes' ) {
					$variation_max_qty = get_post_meta( $variation['variation_id'], '_stock', true );
				}

				$var_sku = $variation['sku'];
				if ( isset( $variation['image']['url'] ) && $variation['image']['url'] < 5 ) {
					$var_image_url = $variation['image']['url'];
				}
				$var_id = $variation['variation_id'];

				if ( $is_primary_prod ) {
					$combination = array( 0 , 1 );
					$variation_product[] = array(
						"is_primary"  => true,
						"status"      => (string)$var_status,
						"price"       => (int) $var_price,
						"stock"       => (int) $variation_max_qty,
						"sku"         => (string)$var_sku,
						"combination" => array(0),
						"pictures"    => array( array( "file_path" => $var_image_url ) ),

					);
					$is_primary_prod = false;
				} else{
					$combination = array( 0 , 1 );
					$variation_product[] = array(
						"status"      => (string)$var_status,
						"price"       => (int)$var_price,
						"stock"       => (int) $variation_max_qty,
						"sku"         => (string)$var_sku,
						"combination" => array(1),
						"pictures"    => array( array( "file_path" => $var_image_url )),
					);
				}

			}

			$size_chart[] = array(
						"file_path" => $var_image_url,
					);

			/* GET VARIATION BY CATORY ID WITH API */

			$selection_data = $this->obj_request->sendCurlGetMethod( 'get_variation_by_category_id', $active_shop ,$categoryId );
			$selection_data = isset( $selection_data['data'] ) ? $selection_data['data'] : '';

			$selections     = array();
			$options        = array();
			if (is_array( $selection_data ) && !empty( $selection_data ) ) {
				foreach ( $selection_data as $key => $value ) {
					$selections['id'] = isset( $value['variant_id'] ) ? $value['variant_id'] : '';
					$unit_values      = isset( $value['units'] ) ? $value['units'] : '';

					if (is_array( $unit_values ) && !empty( $unit_values ) && isset( $unit_values ) ) {
						foreach ( $unit_values as $key1 => $value1 ) {

							$selections['unit_id'] = isset( $value1['unit_id'] ) ? $value1['unit_id'] : '';
							$section_values        = isset( $value1['values'] ) ? $value1['values'] : '';
							$options               = array();
							if (is_array( $section_values ) && !empty( $section_values ) && isset( $section_values ) ) {
								foreach ( $section_values as $key2 => $value2 ) {
								   		$options[] = array(
												'hex_code'      => (string)$value2['hex_code'],
												'unit_value_id' => (int)$value2['value_id'],
												'value'         => (string)$value2['value'],
											);			
								}				
							}
						}
					}
					$selections['options'] = $options;
					$all_selections_data[] = $selections;
				}
			}
		}
		
		$productPrice = $productPrice * $profile_cc_rate;
		$pictures[] = array(
				"file_path" => $image_url,
			);

		$arguments = array();		
		$arguments = array(
			"name"              => $productTitle,
			"condition"         => $productCondition,
			"description"       => strip_tags( $productDescription ),
			"sku"               => $sku,
			"price"             => (int) $productPrice,
			"status"            => $status,
			"stock"             => (int) $productQuantity,
			"min_order"         => 1,
			"category_id"       => (int) $categoryId,
			"price_currency"    => (string)'IDR',
			"weight"            => (int)$weight,
			"weight_unit"       => (string)strtoupper($weight_unit),
			"is_free_return"    => (bool)false,
			"is_must_insurance" => (bool)false,
			"etalase"           => array(
				'id' => (int)$etalase_id,
			),
			"pictures"          => $pictures,
		);

		$uploaded_pro_id = array();
		$uploaded_id = get_post_meta( $proID, '_ced_tokopedia_upload_id_' . $active_shop, true );
		if ( !empty( $calling_from ) && $calling_from == 'for_update_product' ) {
			$uploaded_pro_id['id'] = intval( $uploaded_id );
			$arguments = array_merge( $uploaded_pro_id , $arguments );
		}

		if ( !empty( $calling_from ) && $calling_from === 'for_simple' || 'simple' == $productType /*|| $calling_from === 'for_update_product'*/ ) {
			$args['products'][] = $arguments;
			return $args;
		}

		if ( !empty( $calling_from ) && $calling_from ==='for_variable' || 'variable' == $productType /*|| $calling_from === 'for_update_product'*/ ) {
			$arguments["variant"] = array(
				"products"   => $variation_product,
				"selection"  => $all_selections_data ,
				"sizecharts" => $size_chart,
			);
			$args['products'][] = $arguments;
			return $args;
		}
	}

	/**
	 * This function fetches data in accordance with profile assigned to product.
	 *
	 * @name getProfileAssignedData()
	 * @link  http://www.cedcommerce.com/
	 */
	public function getProfileAssignedData( $proId, $shopId ) {

		$data = wc_get_product( $proId );
		$type = $data->get_type();

		if ( 'variation' == $type ) {
			$proId = $data->get_parent_id();
		}
		global $wpdb;
		$productData = wc_get_product( $proId );
		$product     = $productData->get_data();
		$category_id = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
		$profile_id  = get_post_meta( $proId, 'ced_tokopedia_profile_assigned' . $shopId, true );

		if ( ! empty( $profile_id ) ) {
			$profile_id = $profile_id;
		} else {
			foreach ( $category_id as $key => $value ) {
				$profile_id = get_term_meta( $value, 'ced_tokopedia_profile_id_' . $shopId, true );
				if ( ! empty( $profile_id ) ) {
					break;
				}
			}
		}

		if ( isset( $profile_id ) && ! empty( $profile_id ) ) {

			$this->isProfileAssignedToProduct = true;
			$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `id`=%s ", $profile_id ), 'ARRAY_A' );

			if ( is_array( $profile_data ) ) {
				$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$profile_data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();
			}
		} else {
			$this->isProfileAssignedToProduct = false;
			return 'false';
		}
		$this->profile_data = isset( $profile_data ) ? $profile_data : '';
		return $this->profile_data;
	}


	/**
	 * This function fetches meta value of a product in accordance with profile assigned and meta value available.
	 *
	 * @name fetchMetaValueOfProduct()
	 * @link  http://www.cedcommerce.com/
	 */

	public function fetchMetaValueOfProduct( $product_id, $metaKey, $is_variation = false ) {

		if ( isset( $this->isProfileAssignedToProduct ) && $this->isProfileAssignedToProduct ) {

			$_product = wc_get_product( $product_id );

			if ( WC()->version < '3.0.0' ) {
				if ( 'variation' == $_product->product_type ) {
					$parentId = $_product->parent->id;
				} else {
					$parentId = '0';
				}
			} else {
				if ( 'variation' == $_product->get_type() ) {
					$parentId = $_product->get_parent_id();
				} else {
					$parentId = '0';
				}
			}

			if ( ! empty( $this->profile_data ) && isset( $this->profile_data[ $metaKey ] ) ) {

				$profileData     = $this->profile_data[ $metaKey ];
				$tempProfileData = $this->profile_data[ $metaKey ];

				if ( isset( $tempProfileData['default'] ) && ! empty( $tempProfileData['default'] ) && ! empty( $tempProfileData['default'] ) && ! is_null( $tempProfileData['default'] ) ) {

					$value = $tempProfileData['default'];

				} elseif ( isset( $tempProfileData['metakey'] ) ) {

					if ( strpos( $tempProfileData['metakey'], 'umb_pattr_' ) !== false ) {

						$wooAttribute = explode( 'umb_pattr_', $tempProfileData['metakey'] );

						$wooAttribute = end( $wooAttribute );

						if ( WC()->version < '3.0.0' ) {

							if ( 'variation' == $_product->product_type ) {

								$attributes = $_product->get_variation_attributes();
								if ( isset( $attributes[ 'attribute_pa_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $wooAttribute ] ) ) {
									$wooAttributeValue = $attributes[ 'attribute_pa_' . $wooAttribute ];
									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $wooAttribute );
									}
								} else {

									$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );

									$wooAttributeValue = explode( ',', $wooAttributeValue );
									$wooAttributeValue = $wooAttributeValue[0];
									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $wooAttribute );
									}
								}

								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {

									foreach ( $product_terms as $tempkey => $tempvalue ) {

										if ( $tempvalue->slug == $wooAttributeValue ) {

											$wooAttributeValue = $tempvalue->name;
											break;
										}
									}

									if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
										$value = $wooAttributeValue;
									} else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								} else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							} else {
								$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );
								$product_terms     = get_the_terms( $product_id, 'pa_' . $wooAttribute );
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $tempkey => $tempvalue ) {
										if ( $tempvalue->slug == $wooAttributeValue ) {
											$wooAttributeValue = $tempvalue->name;
											break;
										}
									}
									if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
										$value = $wooAttributeValue;
									} else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								} else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							}
						} else {
							if ( 'variation' == $_product->get_type() ) {

								$attributes = $_product->get_variation_attributes();
								if ( isset( $attributes[ 'attribute_pa_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $wooAttribute ] ) ) {

									$wooAttributeValue = $attributes[ 'attribute_pa_' . $wooAttribute ];
									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $wooAttribute );
									}
								} elseif ( isset( $attributes[ 'attribute_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_' . $wooAttribute ] ) ) {

									$wooAttributeValue = $attributes[ 'attribute_' . $wooAttribute ];

									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $wooAttribute );
									}
								} else {

									$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );
									$wooAttributeValue = explode( ',', $wooAttributeValue );
									$wooAttributeValue = $wooAttributeValue[0];

									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $wooAttribute );
									}
								}

								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $tempkey => $tempvalue ) {
										if ( $tempvalue->slug == $wooAttributeValue ) {
											$wooAttributeValue = $tempvalue->name;
											break;
										}
									}
									if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
										$value = $wooAttributeValue;
									} else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								} elseif ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
									$value = $wooAttributeValue;
								} else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							} else {
								$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );
								$product_terms     = get_the_terms( $product_id, 'pa_' . $wooAttribute );
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $tempkey => $tempvalue ) {
										if ( $tempvalue->slug == $wooAttributeValue ) {
											$wooAttributeValue = $tempvalue->name;
											break;
										}
									}
									if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
										$value = $wooAttributeValue;
									} else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								} else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							}
						}
					} else {

						$value = get_post_meta( $product_id, $tempProfileData['metakey'], true );
						if ( '_thumbnail_id' == $tempProfileData['metakey'] ) {
							$value = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
						}
						if ( ! isset( $value ) || empty( $value ) || '' == $value || is_null( $value ) || '0' == $value || 'null' == $value ) {
							if ( '0' != $parentId ) {

								$value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
								if ( '_thumbnail_id' == $tempProfileData['metakey'] ) {
									$value = wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) : '';
								}

								if ( ! isset( $value ) || empty( $value ) || '' == $value || is_null( $value ) ) {
									$value = get_post_meta( $product_id, $metaKey, true );

								}
							} else {
								$value = get_post_meta( $product_id, $metaKey, true );
							}
						}
					}
				} else {
					$value = get_post_meta( $product_id, $metaKey, true );
				}
			} else {
				$value = get_post_meta( $product_id, $metaKey, true );
			}
		} else {
			$value = get_post_meta( $product_id, $metaKey, true );
		}

		return $value;
	}

}
