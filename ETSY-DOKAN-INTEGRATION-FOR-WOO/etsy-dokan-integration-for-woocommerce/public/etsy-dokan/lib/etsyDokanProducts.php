<?php
if ( ! class_exists( 'Class_Ced_Etsy_Dokan_Products' ) ) {
	class Class_Ced_Etsy_Dokan_Products{
		
		public static $_instance;
		private $renderDataOnGlobalSettings;
		private $saved_etsy_details;
		private $de_shop_name;
		private $vendor_id;
		/**
		 * Ced_Etsy_Config Instance.
		 *
		 * Ensures only one instance of Ced_Etsy_Config is loaded or can be loaded.
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

		public function __construct( $shop_name = '', $vendor_id = '' ) {
			$this->de_shop_name = !empty( $shop_name ) ? $shop_name : '';
			$this->vendor_id = !empty( $vendor_id ) ? $vendor_id : get_current_user_id();
			// Get all global settings data
			$renderDataOnGlobalSettings       = get_option( 'ced_etsy_dokan_global_settings', array() );
			$this->renderDataOnGlobalSettings = isset( $renderDataOnGlobalSettings[$this->vendor_id] ) ? $renderDataOnGlobalSettings[$this->vendor_id] : $renderDataOnGlobalSettings;
			// Saved detials for Etsy
			$saved_etsy_details               = get_option( 'ced_etsy_dokan_details', array() );
			$this->saved_etsy_details         = isset( $saved_etsy_details[$this->vendor_id] ) ? $saved_etsy_details[$this->vendor_id] : $saved_etsy_details;
		}


		/**
		 * ********************************************
		 * Function for products data to be uploaded.
		 * ********************************************
		 *
		 * @since 1.0.0
		 *
		 * @param array  $prodIDs Checked Product ids
		 * @param string $shopName Active Shop Name
		 */

		public function prepareDataForUploadingProduct( $proIDs = array(), $de_shop_name = '' ) {
			if ( is_array( $proIDs ) && ! empty( $proIDs ) ) {
				$de_shop_name = trim( $de_shop_name );
				self::prepareDokanItems( $proIDs, $de_shop_name );
				$response = $this->uploadResponse;
				return $response;

			}
		}

		/**
		 * *****************************************************
		 * Function for preparing product data to be uploaded.
		 * *****************************************************
		 *
		 * @since 1.0.0
		 *
		 * @param array  $prodIDs Checked Product ids
		 * @param string $shopName Active Shop Name
		 *
		 * @return Uploaded Ids
		 */
		private function prepareDokanItems( $proIDs = array(), $de_shop_name = '' ) {

			foreach ( $proIDs as $key => $value ) {
				$productData     = wc_get_product( $value );
				$image_id        = get_post_thumbnail_id( $value );
				$productType     = $productData->get_type();
				$alreadyUploaded = false;
				if ( 'variable' == $productType ) {
					$attributes = $productData->get_variation_attributes();
					if ( count( $attributes ) > 2 ) {
						$error                = array();
						$error['msg']         = 'Varition attributes cannot be more than 2 . Etsy accepts variations using two attributes only.';
						$this->uploadResponse = $error;
						return $this->uploadResponse;
					}
					$preparedData = $this->getDokanFormattedData( $value, $de_shop_name );				
					if ( 'Profile Not Assigned' == $preparedData || 'Quantity Cannot Be 0' == $preparedData ) {
						$error                = array();
						$error['msg']         = $preparedData;
						$this->uploadResponse = $error;
						return $this->uploadResponse;
					}
					$this->data = $preparedData;
					self::douploadDokanProduct( $value, $de_shop_name );
					$response = $this->uploadResponse;
					if ( isset( $response['listing_id'] ) ) {
						$listingID = isset( $response['listing_id'] ) ? $response['listing_id'] : '';
						update_post_meta( $value, '_ced_etsy_listing_id_' . $de_shop_name, $response['listing_id'] );
						update_post_meta( $value, '_CED_ETSY_DOKAN_URL_' . $de_shop_name, $response['url'] );
						$var_response = $this->dokan_update_variation_sku_to_etsy( $listingID, $value, $de_shop_name );
						if ( ! isset( $var_response['products'][0]['product_id'] ) ) {
							$this->prepareDokanDataForDelete( array( $value ), $de_shop_name );
							foreach ( $var_response as $key => $value ) {
								$error                = array();
								$error['msg']         = isset( $key ) ? ucwords( str_replace( '_', ' ', $key ) ) : '';
								$this->uploadResponse = $error;
								return $this->uploadResponse;

							}
						}
						$this->ced_etsy_dokan_prep_and_upload_img( $value, $de_shop_name, $listingID );
					}
				} else {
					$preparedData = $this->getDokanFormattedData( $value, $de_shop_name );
					if ( 'Profile Not Assigned' == $preparedData || 'Quantity Cannot Be 0' == $preparedData ) {
						$error                = array();
						$error['msg']         = $preparedData;
						$this->uploadResponse = $error;
						return $this->uploadResponse;
					}
					$this->data = $preparedData;
					self::douploadDokanProduct( $value, $de_shop_name );
					$response = $this->uploadResponse;
					if ( isset( $response['listing_id'] ) ) {
						$listingID = isset( $response['listing_id'] ) ? $response['listing_id'] : '';
						update_post_meta( $value, '_ced_etsy_listing_id_' . $de_shop_name, $response['listing_id'] );
						update_post_meta( $value, '_CED_ETSY_DOKAN_URL_' . $de_shop_name, $response['url'] );
						$this->ced_etsy_dokan_prep_and_upload_img( $value, $de_shop_name, $listingID );
						$this->ced_etsy_dokan_upload_attributes( $value, $listingID,  $de_shop_name );
						if ( $this->is_downloadable ) {
							$digital_response = $this->ced_dokan_upload_downloadable_to_etsy( $value, $de_shop_name, $listingID );
						}
					}
				}
			}
			return $this->uploadResponse;
		}

		private function ced_etsy_dokan_upload_attributes( $productId, $listing_id, $shop_name ) {
		 	if ( isset( $productId ) ) {
		 		if ( isset( $listing_id ) ) {
		 			$categoryId = (int) $this->fetchDokanMetaValueOfProduct( $productId, '_umb_etsy_category' );
		 			if ( isset( $categoryId ) ) {
		 				$params                    = array( 'taxonomy_id' => $categoryId );
		 				do_action('ced_etsy_dokan_refresh_token', $shop_name );
		 				$getTaxonomyNodeProperties = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name , "application/seller-taxonomy/nodes/{$categoryId}/properties" );
		 				$getTaxonomyNodeProperties = $getTaxonomyNodeProperties['results'];
		 				if ( isset( $getTaxonomyNodeProperties ) && is_array( $getTaxonomyNodeProperties ) && ! empty( $getTaxonomyNodeProperties ) ) {
		 					$attribute_meta_data = get_post_meta( $productId, 'ced_etsy_attribute_data', true );
		 					foreach ( $getTaxonomyNodeProperties as $key => $value ) {
		 						$property = ! empty( $attribute_meta_data[ ( '_ced_etsy_property_id_' . $value['property_id'] ) ] ) ? $attribute_meta_data[ ( '_ced_etsy_property_id_' . $value['property_id'] ) ] : 0;
		 						if ( empty( $property ) ) {
		 							$property = $this->fetchDokanMetaValueOfProduct( $productId, '_ced_etsy_property_id_' . $value['property_id'] );
		 						}
		 						foreach ( $value['possible_values'] as $tax_value ) {
		 							if ( $tax_value['name'] == $property ) {
		 								$property = $tax_value['value_id'];
		 								break;
		 							}
		 						}

		 						if ( isset( $property ) && ! empty( $property ) ) {
		 							$property_id[ $value['property_id'] ] = $property;
		 						}
		 					}
		 				}
		 				if ( isset( $property_id ) && ! empty( $property_id ) ) {
		 					foreach ( $property_id as $key => $value ) {
								$property_id_to_listing = (int) $key;
								$value_ids              = (int) $value;
								$params                 = array(
									'property_id' => (int) $property_id_to_listing,
									'value_ids'   => array( (int) $value_ids ),
									'values'      => array( (string) $value_ids ),
								);
								$shop_id             = ced_etsy_dokan_get_shop_id( $shop_name );
		 						$response             = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name , "application/shops/{$shop_id}/listings/{$listing_id}/properties/{$property_id_to_listing}" , "PUT" , array() , $params );
		 					}
		 				}
		 				update_post_meta( $productId, 'ced_etsy_attribute_uploaded', 'true' );
		 			}
		 		}
		 	}
		 }


		/**
		 * ***************************
		 * Upload downloadable files
		 * ***************************
		 *
		 * @since 2.0.8
		 *
		 * @param array  $p_id Checked Product ids
		 * @param string $shopName Active Shop Name
		 *
		 * @return
		 */
		private function ced_dokan_upload_downloadable_to_etsy( $p_id = '', $shop_name = '', $l_id = '' ) {
			$listing_files_uploaded = get_post_meta( $p_id, '_ced_etsy_product_files_uploaded' . $l_id, true );
			if ( empty( $listing_files_uploaded ) ) {
				$listing_files_uploaded = array();
			}
			$downloadable_files = $this->downloadable_data;
			if ( ! empty( $downloadable_files ) ) {
				$count = 0;
				foreach ( $downloadable_files as $data ) {
					if ( $count > 4 ) {
						break;
					}
					$file_data = $data->get_data();
					if ( isset( $listing_files_uploaded[ $file_data['id'] ] ) ) {
						continue;
					}
					try {
						$file_path = str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_data['file'] );
						do_action( 'ced_etsy_dokan_refresh_token', $shop_name );
						$shop_id  = ced_etsy_dokan_get_shop_id( $shop_name );
						$response = ced_etsy_dokan_request()->ced_etsy_dokan_upload_image_and_file( 'file', "application/shops/{$shop_id}/listings/{$l_id}/files", $file_path, $file_data['name'], $shop_name );
						if ( isset( $response['listing_file_id'] ) ) {
							$listing_files_uploaded[ $file_data['id'] ] = $response['listing_file_id'];
							update_post_meta( $p_id, '_ced_etsy_product_files_uploaded' . $l_id, $listing_files_uploaded );
						}
					} catch ( Exception $e ) {
						$this->error_msg['msg'] = 'Message:' . $e->getMessage();
						return $this->error_msg;
					}
				}
			}
		}


		/**
		 * *************************
		 * Update uploaded images.
		 * *************************
		 *
		 * @since 2.0.8
		 *
		 * @param array  $p_id Checked Product ids
		 * @param string $shopName Active Shop Name
		 *
		 * @return
		 */
		public function ced_etsy_dokan_prep_and_upload_img( $p_id = '', $shop_name = '', $listing_id = '' ) {
			if ( empty( $p_id ) || empty( $shop_name ) ) {
				return;
			}
			$woo_product = wc_get_product( $p_id );
			$prnt_img_id       = get_post_thumbnail_id( $p_id );
			if ( WC()->version < '3.0.0' ) {
				$attachment_ids = $woo_product->get_gallery_attachment_ids();
			} else {
				$attachment_ids = $woo_product->get_gallery_image_ids();
			}
			$previous_thum_ids = get_post_meta( $p_id, 'ced_etsy_previous_thumb_ids' . $listing_id, true );
			if ( empty( $previous_thum_ids ) || ! is_array( $previous_thum_ids ) ) {
				$previous_thum_ids = array();
			}
			$attachment_ids = array_slice( $attachment_ids, 0, 9 );
			if ( ! empty( $attachment_ids ) ) {
				foreach ( array_reverse( $attachment_ids ) as $attachment_id ) {
					if ( isset( $previous_thum_ids[ $attachment_id ] ) ) {
						continue;
					}

					/*
					|=======================
					| UPLOAD GALLERY IMAGES
					|=======================
					*/
					$image_result = self::do_dokan_image_upload( $listing_id, $p_id, $attachment_id, $shop_name );
					if ( isset( $image_result['listing_image_id'] ) ) {
						$previous_thum_ids[ $attachment_id ] = $image_result['listing_image_id'];
						update_post_meta( $p_id, 'ced_etsy_previous_thumb_ids' . $listing_id, $previous_thum_ids );
					}
				}
			}

			/*
			|===================
			| UPLOAD MAIN IMAGE
			|===================
			*/
			if ( ! isset( $previous_thum_ids[ $prnt_img_id ] ) ) {
				$image_result = self::do_dokan_image_upload( $listing_id, $p_id, $prnt_img_id, $shop_name );
				if ( isset( $image_result['listing_image_id'] ) ) {
					$previous_thum_ids[ $prnt_img_id ] = $image_result['listing_image_id'];
					update_post_meta( $p_id, 'ced_etsy_previous_thumb_ids' . $listing_id, $previous_thum_ids );
				}
			}
		}

		/**
		 * ************************************
		 * UPLOAD IMAGED ON THE ETSY SHOP ;)
		 * ************************************
		 *
		 * @since 1.0.0
		 *
		 * @param int    $l_id Product listing ids.
		 * @param int    $pr_id Product ids .
		 * @param int    $img_id Image Ids.
		 * @param string $shop_name Active Shop Name
		 *
		 * @return Nothing [Message]
		 */
		public function do_dokan_image_upload( $l_id, $pr_id, $img_id, $shop_name ) {
			$image_path = get_attached_file( $img_id );
			$image_name = basename( $image_path );
			do_action( 'ced_etsy_dokan_refresh_token', $shop_name );
			$shop_id  = ced_etsy_dokan_get_shop_id( $shop_name );
			$response = ced_etsy_dokan_request()->ced_etsy_dokan_upload_image_and_file( 'image', "application/shops/{$shop_id}/listings/{$l_id}/images", $image_path, $image_name, $shop_name );
			return $this->ced_etsy_parse_response( $response );

		}

		public function update_images_on_etsy_dokan( $product_ids = array(), $shop_name = '' ) {
			if ( ! is_array( $product_ids ) ) {
				$product_ids = array( $product_ids );
			}
			$shop_id      = ced_etsy_dokan_get_shop_id( $shop_name );
			$notification = array();
			if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {
				foreach ( $product_ids as $pr_id ) {
					$listing_id = get_post_meta( $pr_id, '_ced_etsy_listing_id_' . $shop_name, true );
					update_post_meta( $pr_id, 'ced_etsy_previous_thumb_ids' . $listing_id, '' );
					do_action( 'ced_etsy_dokan_refresh_token', $shop_name );
					$etsy_images = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, "application/listings/{$listing_id}/images", 'GET' );
					$etsy_images = isset( $etsy_images['results'] ) ? $etsy_images['results'] : array();
					foreach ( $etsy_images as $key => $image_info ) {
						$main_image_id = isset( $image_info['listing_image_id'] ) ? $image_info['listing_image_id'] : '';
						// Get all the listing Images form Etsy
						$action   = "application/shops/{$shop_id}/listings/{$listing_id}/images/{$main_image_id}";
						$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, $action, 'DELETE' );
					}
					$this->ced_etsy_dokan_prep_and_upload_img( $pr_id, $shop_name, $listing_id );
					$notification['status']  = 200;
					$notification['message'] = 'Image updated successfully';
				}
			}
			return $notification;
		}

		public function ced_etsy_parse_response( $json ) {
			return json_decode( $json, true );
		}

		/**
		 * *********************************************
		 * PREPARE DATA FOR UPDATING DATA TO ETSY SHOP
		 * *********************************************
		 *
		 * @since 1.0.0
		 *
		 * @param array  $proIDs Product lsting  ids.
		 * @param string $de_shop_name Active shopName.
		 *
		 * @return Nothing[Updating only Uploaded attribute ids]
		 */

		public function prepareDokanDataForUpdating( $product_ids = array(), $de_shop_name='', $vendor_id ='' ) {
			if ( ! is_array( $product_ids ) ) {
				$product_ids = array( $product_ids );
			}
			$notification = array();
			foreach ( $product_ids as $product_id ) {
				$this->listing_id = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, true );
				$arguements       = $this->get_dokan_custom_field_value_and_profile_field_value( $product_id, $de_shop_name, 'getDokanFormattedData' );
				if ( isset( $arguements['has_error'] ) ) {
					$notification['status']  = 400;
					$notification['message'] = $arguements['error'];
				} else {
					$shop_id             = ced_etsy_dokan_get_shop_id( $de_shop_name );
					$action              = "application/shops/{$shop_id}/listings/{$this->listing_id}";
					do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name );
					$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name, $action, 'PUT', array(), $arguements );
					if ( isset( $response['listing_id'] ) ) {
						update_post_meta( $product_id, '_ced_etsy_listing_data_' . $de_shop_name, json_encode( $response ) );
						$this->ced_etsy_dokan_upload_attributes( $product_id, $response['listing_id'],  $de_shop_name );
						$notification['status']  = 200;
						$notification['message'] = $arguements['title'] . ' updated successfully';
					} elseif ( isset( $response['error'] ) ) {
						$notification['status']  = 400;
						$notification['message'] = $response['error'];
					} else {
						$notification['status']  = 400;
						$notification['message'] = json_encode( $response );
					}
				}
			}
			return $notification;

		}

		 /**
		  * Function for preparing product stock to be updated.
		  *
		  * @since 1.0.0
		  */



		 /**
		  * *****************************************************
		  * PREPARING DATA FOR UPDATING INVENTORY TO ETSY SHOP
		  * *****************************************************
		  *
		  * @since 1.0.0
		  *
		  * @param array   $proIDs Product lsting  ids.
		  * @param string  $de_shop_name Active shopName.
		  * @param boolean $is_sync condition for is sync.
		  *
		  * @return $response ,
		  */

		 public function prepareDokanDataForUpdatingInventory( $product_ids = array(), $de_shop_name, $is_sync = false ) {
		 	if ( ! is_array( $product_ids ) ) {
		 		$product_ids = array( $product_ids );
		 	}
		 	$notification = array();
		 	foreach ( $product_ids as $product_id ) {
		 		$_product     = wc_get_product( $product_id );
		 		$product_type = $_product->get_type();
	 			$listing_id   = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, true );
		 		if ( 'variable' == $product_type ) {
		 			$response = $this->dokan_update_variation_sku_to_etsy( $listing_id, $product_id, $de_shop_name, false );
		 		} else {
		 			$sku      = get_post_meta( $product_id, '_sku', true );
		 			do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name );
		 			$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name, "application/listings/{$listing_id}/inventory", 'GET' );
		 			if ( isset( $response['products'][0] ) ) {
						$quantity = (int) $this->get_quantity( $product_id );
						if ( isset( $quantity ) && $quantity < 1 ) {
							$response = $this->ced_etsy_deactivate_product( array( $value ), $de_shop_name );
						} else{
			 					if ( empty( $sku ) ) {
			 						$sku = (string) $value;
			 					}
			 					$product_payload = $response;
			 					$product_payload['products'][0]['offerings'][0]['quantity'] = $quantity;
			 					$product_payload['products'][0]['offerings'][0]['price']    = (float) $this->get_price( $product_id );
			 					$product_payload['products'][0]['sku']                      = (string) $sku;
			 					unset( $product_payload['products'][0]['is_deleted'] );
			 					unset( $product_payload['products'][0]['product_id'] );
			 					unset( $product_payload['products'][0]['offerings'][0]['is_deleted'] );
			 					unset( $product_payload['products'][0]['offerings'][0]['offering_id'] );
			 					do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name );
			 					$input_payload = $product_payload;
			 					$response      = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name, "application/listings/{$listing_id}/inventory", 'PUT', array(), $product_payload );
			 				}
			 			}
		 			}
		 		if ( isset( $response['products'][0] ) ) {
		 			$notification['status']  = 200;
		 			$notification['message'] = 'Product inventory updated successfully';
		 		} elseif ( isset( $response['error'] ) ) {
		 			$notification['status']  = 400;
		 			$notification['message'] = $response['error'];
		 		} else {
		 			$notification['status']  = 400;
		 			$notification['message'] = json_encode( $response );
		 		}
		 	}
		 	return $notification;

		 }



		 public function ced_etsy_activate_product( $product_ids = array(), $shop_name = '' ) {

		 	if ( ! is_array( $product_ids ) ) {
		 		$product_ids = array( $product_ids );
		 	}
		 	$shop_name   = empty( $shop_name ) ? $this->shop_name : $shop_name;
		 	$product_ids = empty( $product_ids ) ? $this->product_id : $product_ids;
		 	foreach ( $product_ids as $product_id ) {
	 			$listing_id          = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $shop_name, true );
		 		$arguements['state'] = 'active';
		 		$shop_id             = ced_etsy_dokan_get_shop_id( $shop_name );
		 		$action              = "application/shops/{$shop_id}/listings/{$listing_id}";
		 		do_action( 'ced_etsy_dokan_refresh_token', $shop_name );
		 		$this->response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, $action, 'PUT', array(), $arguements );
		 		return $this->response;
		 	}
		 }

		 public function ced_etsy_deactivate_product( $product_ids = array(), $shop_name = '' ) {

		 	if ( ! is_array( $product_ids ) ) {
		 		$product_ids = array( $product_ids );
		 	}
		 	$shop_name   = empty( $shop_name ) ? $this->shop_name : $shop_name;
		 	$product_ids = empty( $product_ids ) ? $this->product_id : $product_ids;
		 	foreach ( $product_ids as $product_id ) {
		 		$listing_id = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $shop_name, true );
		 		$arguements['state'] = 'inactive';
		 		$shop_id             = ced_etsy_dokan_get_shop_id( $shop_name );
		 		$action              = "application/shops/{$shop_id}/listings/{$listing_id}";
		 		do_action( 'ced_etsy_dokan_refresh_token', $shop_name );
		 		$this->response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, $action, 'PUT', array(), $arguements );
		 		return $this->response;
		 	}
		 }

		 /**
		  * *******************************
		  * DELETE THE LISTINGS FORM ETSY
		  * *******************************
		  *
		  * @since 1.0.0
		  *
		  * @param array  $proIDs Product lsting  ids.
		  * @param string $de_shop_name Active shopName.
		  *
		  * @return $response deleted product ids ,
		  */

		 public function prepareDokanDataForDelete( $product_ids = array(), $de_shop_name ) {
		 	if ( ! is_array( $product_ids ) ) {
		 		$product_ids = array( $product_ids );
		 	}
		 	$notification = array();
		 	foreach ( $product_ids as $product_id ) {
		 		$listing_id = get_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, true );
		 		if ( $listing_id ) {
		 			do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name );
		 			$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name, "application/listings/{$listing_id}", 'DELETE' );
		 			if ( ! isset( $response['error'] ) ) {
		 				delete_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name );
		 				delete_post_meta( $product_id, '_CED_ETSY_DOKAN_URL_' . $de_shop_name );
		 				$notification['status']  = 200;
		 				$notification['message'] = 'Product removed successfully';
		 				$response['results']     = $notification;
		 			} elseif ( isset( $response['error'] ) ) {
		 				$notification['status']  = 400;
		 				$notification['message'] = $response['error'];
		 			} else {
		 				$notification['status']  = 400;
		 				$notification['message'] = json_encode( $response );
		 			}
		 		}
		 	}
		 	return $notification;
		 }

		 /**
		  * *****************************************
		  * GET FORMATTED DATA FOR UPLOAD PRODUCTS
		  * *****************************************
		  *
		  * @since 1.0.0
		  *
		  * @param array  $proIDs Product lsting  ids.
		  * @param string $de_shop_name Active shopName.
		  * @param bool   $isPreview boolean.
		  *
		  * @return $arguments all possible arguments .
		  */

		 private function getDokanFormattedData( $proID = array(), $de_shop_name = '', $isPreview = false ) {

		 	$profileData = $this->getProfileAssignedData( $proID, $de_shop_name );
		 	if ( 'false' == $profileData && ! $isPreview ) {
		 		return 'Profile Not Assigned';
		 	}
		 	$this->is_downloadable   = false;
		 	$this->downloadable_data = array();
		 	$arguements = $this->get_dokan_custom_field_value_and_profile_field_value( $proID, $de_shop_name, 'getDokanFormattedData' );
		 	if ( ! empty( $arguements ) ) {
		 		return $arguements;
		 	}
		 }

		/**
		 * *****************************************
		 * GET ASSIGNED PRODUCT DATA FROM PROFILES
		 * *****************************************
		 *
		 * @since 1.0.0
		 *
		 * @param array  $proId Product lsting  ids.
		 * @param string $shopId Active shopName.
		 *
		 * @link  http://www.cedcommerce.com/
		 * @return $profile_data assigined profile data .
		 */

		private function getProfileAssignedData( $proId, $shopId ) {
			$data = wc_get_product( $proId );
			$type = $data->get_type();
			if ( 'variation' == $type ) {
				$proId = $data->get_parent_id();
			}

			global $wpdb;
			$productData = wc_get_product( $proId );
			$product     = $productData->get_data();
			$category_id = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
			$profile_id  = /*get_post_meta( $proId, 'ced_etsy_dokan_profile_assigned' . $shopId, true )*/'';
			$vendor_id   = get_current_user_id();
			if ( ! empty( $profile_id ) ) {
				$profile_id = $profile_id;
			} else {
				foreach ( $category_id as $key => $value ) {
					$profile_id = get_term_meta( $value, 'ced_etsy_dokan_profile_id_' . $shopId .'_'. $vendor_id , true );
					if(empty($profile_id)) {
						$profile_id =get_term_meta( $value, 'ced_etsy_dokan_profile_id_' . strtolower($shopId) .'_'. $vendor_id, true );
					}
					if ( ! empty( $profile_id ) ) {
						break;

					}
				}
			}
			if ( isset( $profile_id ) && ! empty( $profile_id ) ) {
				$this->isProfileAssignedToProduct = true;
				$profile_data                     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `id`=%d", $profile_id ), 'ARRAY_A' );

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
		 * *****************************************
		 * UPDATE VARIATION SKU TO ETSY SHOP
		 * *****************************************
		 *
		 * @since 1.0.0
		 *
		 * @param array  $listing_id Product lsting  ids.
		 * @param array  $productId Product  ids.
		 * @param string $shopId Active shopName.
		 *
		 * @link  http://www.cedcommerce.com/
		 * @return $response
		 */

		private function dokan_update_variation_sku_to_etsy( $listing_id, $productId, $shop_name, $is_sync = false ) {
			$offerings_payload = $this->ced_dokan_variation_details( $productId, $shop_name );
			do_action( 'ced_etsy_dokan_refresh_token', $shop_name );
			$response = ced_etsy_dokan_request()->ced_remote_request( $shop_name, "application/listings/{$listing_id}/inventory", 'PUT', array(), $offerings_payload );
			if ( isset( $response['products'][0]['product_id'] ) ) {
				update_post_meta( $productId, 'ced_etsy_last_updated' . $shop_name, gmdate( 'l jS \of F Y h:i:s A' ) );
			}
		}


		/**
		 * *****************************************
		 * GET VARIATION DATA TO UPDATE ON ETSY
		 * *****************************************
		 *
		 * @since 1.0.0
		 *
		 * @param string $product_id Product lsting  ids.
		 * @param string $shop_name Product  ids.
		 * @param string $is_sync Active shopName.
		 *
		 * @link  http://www.cedcommerce.com/
		 * @return $response
		 */

		public function ced_dokan_variation_details( $product_id = '', $shop_name = '', $is_sync = false ) {
			$property_ids = array();
			$product      = wc_get_product( $product_id );
			$variations   = $product->get_available_variations();
			$attributes   = array();
			$parent_sku   = get_post_meta( $product_id, '_sku', true );
			$parent_attributes = $product->get_variation_attributes();
			$possible_combinations = array_values( wc_array_cartesian(( $parent_attributes )) );
			$no_property_to_use = count($parent_attributes);
			$com_to_be_prepared = array();
			foreach ( $possible_combinations as $po_attr => $po_values ) {
				$att_name_po = '';
				$po_values   = array_reverse( $po_values );

				foreach ( $po_values as $kk => $po_value ) {
					if ( ! isset( $parent_attributes[ $kk ] ) ) {
						continue;
					}
					$att_name_po .= $po_value . '~';
				}

				$com_to_be_prepared[ trim( strtolower( $att_name_po ) ) ] = trim( strtolower( $att_name_po ) );
			}
			foreach ( $variations as $variation ) {
				$var_id               = $variation['variation_id'];
				$attribute_one_mapped = false;
				$attribute_two_mapped = false;
				$var_product          = wc_get_product( $variation['variation_id'] );
				$attributes           = $var_product->get_variation_attributes();
				$count                = 0;
				$property_values      = array();
				$offerings            = array();
				$var_array = array();
				$_count = 0;

				$var_att_array                        = '';
				foreach ( $attributes as $property_name => $property_value ) {
					$product_terms = get_the_terms( $product_id, $property_name );
					if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
						foreach ( $product_terms as $tempkey => $tempvalue ) {
							if ( $tempvalue->slug == $property_value ) {
								$property_value = $tempvalue->name;
								break;
							}
						}
					}
					$_count ++;
					$property_id = 513;
					if(!isset($p_name_1)) {
						$p_name_1 = ucwords( str_replace( array( 'attribute_pa_', 'attribute_' ), array( '', '' ), $property_name ) );
					}

					if ( $count > 0 ) {
						if(!isset($p_name_2)) {
							$p_name_2 = ucwords( str_replace( array( 'attribute_pa_', 'attribute_' ), array( '', '' ), $property_name ) );
						}
						$property_id = 514;
					}


					$property_values[] = array(
						'property_id'   => (int) $property_id,
						'value_ids'     => array( $property_id ),
						'property_name' => ucwords( str_replace( array( 'attribute_pa_', 'attribute_' ), array( '', '' ), $property_name ) ),
						'values'        => array( ucwords( strtolower( $property_value ) ) ),

					);

					$var_att_array                    .= $property_value . '~';
					$count++;
					$property_ids[] = $property_id;
				}
				if ( isset( $com_to_be_prepared[ strtolower( $var_att_array ) ] ) ) {
					unset( $com_to_be_prepared[ strtolower( $var_att_array ) ] );
				}
				$price        = $this->get_price( $variation['variation_id'] );
				$var_quantity = $this->get_quantity( $variation['variation_id'] );
				$var_sku      = $variation['sku'];
				if ( empty( $var_sku ) || strlen( $var_sku ) > 32 || $parent_sku == $var_sku ) {
					$var_sku = (string) $variation['variation_id'];
				}

				$offerings      = array(
					array(
						'price'      => (float) $price,
						'quantity'   => (int) $var_quantity,
						'is_enabled' => 1,
					),
				);
				$variation_info = array(
					'sku'             => $var_sku,
					'property_values' => $property_values,
					'offerings'       => $offerings,
				);
				$offer_info[]   = $variation_info;
			}
			$remaining_combination = $com_to_be_prepared;
					foreach ( $remaining_combination as $combination ) {
						$property_values_remaining = array_values( array_filter( explode( '~', $combination ) ) );
						if ( isset( $property_values_remaining[1] ) ) {
							$offer_info[] = array(

								'sku'             => '',
								'property_values' => array(
									array(
										'property_id'   => (int) 513,
										'value_ids'     => array( 513 ),
										'property_name' => $p_name_1,
										'values'        => array(
											isset( $property_values_remaining[0] ) ? ucwords( strtolower( $property_values_remaining[0] ) ) : '',
										),
									),
									array(
										'property_id'   => (int) 514,
										'value_ids'     => array( 514 ),
										'property_name' => $p_name_2,
										'values'        => array(
											isset( $property_values_remaining[1] ) ? ucwords( strtolower( $property_values_remaining[1] ) ) : '',
										),
									),
								),
								'offerings'       => array(
									array(
										'price'      => (float) $price,
										'quantity'   => 0,
										'is_enabled' => 0,
									),
								),

							);
						} elseif ( isset( $property_values_remaining[0] ) ) {
							$offer_info[] = array(

								'sku'             => '',
								'property_values' => array(
									array(
										'property_id'   => (int) 513,
										'value_ids'     => array( 513 ),
										'property_name' => $p_name_1,
										'values'        => array(
											isset( $property_values_remaining[0] ) ? ucwords( strtolower( $property_values_remaining[0] ) ) : '',
										),
									),

								),
								'offerings'       => array(
									array(
										'price'      => (float) $price,
										'quantity'   => 0,
										'is_enabled' => 0,
									),
								),

							);
						}
					}

			$property_ids = array_unique( $property_ids );
			$property_ids = implode( ',', $property_ids );
			$payload      = array(
				'products'             => $offer_info,
				'price_on_property'    => $property_ids,
				'quantity_on_property' => $property_ids,
				'sku_on_property'      => $property_ids,
			);
			return $payload;
				
		}

		public function get_quantity( $product_id ) {
			$productQuantity = 0;
			$manage_stock    = get_post_meta( $product_id, '_manage_stock', true );
			$productQuantity = get_post_meta( $product_id, '_ced_etsy_stock', true );
			if ( '' == $productQuantity ) {
				$productQuantity = (int) $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_stock' );
			}
			if ( empty( $productQuantity ) ) {
				$productQuantity = get_post_meta( $product_id, '_stock', true );
			}
			$stock_status    = get_post_meta( $product_id, '_stock_status', true );
			if ( trim( $stock_status ) == 'instock' && trim( $manage_stock ) == 'no' ) {
				$productQuantity = 1;
			}
			return $productQuantity;
		}

		public function get_price( $product_id ) {
			$woo_product  = wc_get_product($product_id);
			$productData  = $woo_product->get_data();
			$productPrice = (float) $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_price' );
			if ( empty( $productPrice ) ) {
				$productPrice = (float) $productData['price'];
			}
			$markuptype_at_profile_lvl = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_markup_type' );
			$markupValue               = (float) $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_markup_value' );
			if ( 'Percentage_Increased' == $markuptype_at_profile_lvl ) {
				$productPrice = (float) $productPrice + ( ( (float) $markupValue / 100 ) * (float) $productPrice );
			} else {
				$productPrice = (float) $productPrice + (float) $markupValue;
			}
			return $productPrice;
		}

		/**
		 * *************************************************************************************************************
		 * This function fetches meta value of a product in accordance with profile assigned and meta value available.
		 * *************************************************************************************************************
		 *
		 * @since 1.0.0
		 *
		 * @param int    $product_id Product  ids.
		 * @param string $metaKey meta key name .
		 * @param bool   $is_variation variation or not.
		 *
		 * @link  http://www.cedcommerce.com/
		 * @return $meta data
		 */

		private function get_dokan_custom_field_value_and_profile_field_value( $product_id = '', $de_shop_name = '', $calling_from = '' ) {

			if ( empty( $product_id ) ) {
				return;
			}
			
			$profileData = $this->getProfileAssignedData( $product_id, $de_shop_name );
			if ( 'false' == $profileData && ! $isPreview ) {
				return 'Profile Not Assigned';
			}

			$product            = wc_get_product( $product_id );
			$productData        = $product->get_data();
			$productType        = $product->get_type();
			$productTitle       = get_post_meta( $product_id, '_ced_etsy_title', true );
			$productPrefix      = get_post_meta( $product_id, '_ced_etsy_title_pre', true );
			$productPostfix     = get_post_meta( $product_id, '_ced_etsy_title_post', true );
			$productDescription = get_post_meta( $product_id, '_ced_etsy_description', true );

			$this->is_downloadable = isset( $productData['downloadable'] ) ? $productData['downloadable'] : 0;
			if ( $this->is_downloadable ) {
				$this->downloadable_data = $productData['downloads'];
			}

			// Product Title
			if ( empty( $productTitle ) ) {
				$productTitle = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_title' );
			}
			if ( empty( $productTitle ) ) {
				$productTitle = $productData['name'];
			}

			if ( empty( $productPrefix ) ) {
				$productPrefix = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_title_pre' );
			}

			if ( empty( $productPostfix ) ) {
				$productPostfix = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_title_post' );
			}

			$productTitle = $productPrefix . ' ' . $productTitle . ' ' . $productPostfix;

			if ( empty( $productDescription ) ) {
				$productDescription = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_description' );
			}
			if ( empty( $productDescription ) ) {
				$productDescription = $productData['description'] . '</br>' . $productData['short_description'];
			}
			$productPrice = $this->get_price( $product_id );
			if ( 'variable' == $productType ) {
				$variations = $product->get_available_variations();
				if ( isset( $variations['0']['display_regular_price'] ) ) {
					$productPrice    = $variations['0']['display_regular_price'];
					$varId           = $variations['0']['variation_id'];
					$productQuantity = 1;
				}
			} else {
				$manage_stock    = get_post_meta( $product_id, '_manage_stock', true );
				$stock_status    = get_post_meta( $product_id, '_stock_status', true );
				$productQuantity = $this->get_quantity( $product_id );

			}

			$materials = get_post_meta( $product_id, '_ced_etsy_materials', true );
			// Materials
			if ( empty( $materials ) ) {
				$materials = array( $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_materials' ) );
			}

			// Tags
			$tags = get_post_meta( $product_id, '_ced_etsy_tags', true );
			if ( empty( $tags ) ) {
				$tags = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_tags' );
			}

			if ( empty( $tags ) ) {
				$current_tag = get_the_terms( $product_id, 'product_tag' );
				if ( is_array( $current_tag ) ) {
					$tags = array();
					foreach ( $current_tag as $key_tags => $tag ) {
						if ( $key_tags <= 12 ) {
							$tags[ $key_tags ] = $tag->name;
						}
					}
					$tags = implode( ',', $tags );
				}
			}

			if ( ! empty( $tags ) ) {
				$tags = array( $tags );
			}

			$who_made = get_post_meta( $product_id, '_ced_etsy_who_made', true );
			if ( empty( $who_made ) ) {
				$who_made = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_who_made' );
			}

			$recipient = get_post_meta( $product_id, '_ced_etsy_recipient', true );
			if ( empty( $recipient ) ) {
				$recipient = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_recipient' );
			}

			$occasion = get_post_meta( $product_id, '_ced_etsy_occasion', true );
			if ( empty( $occasion ) ) {
				$occasion = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_occasion' );
			}

			$when_made = get_post_meta( $product_id, '_ced_etsy_when_made', true );
			if ( empty( $when_made ) ) {
				$when_made = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_when_made' );
			}

			$producTUploadType = get_post_meta( $product_id, '_ced_etsy_product_list_type', true );
			if ( empty( $producTUploadType ) ) {
				$producTUploadType = ! empty( $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_product_list_type' ) ) ? $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_product_list_type' ) : 'draft';
			}

			$shop_section_id = get_post_meta( $product_id, '_ced_etsy_shop_section', true );
			if ( empty( $shop_section_id ) ) {
				$shop_section_id = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_shop_section' );
			}

			$is_supply = get_post_meta( $product_id, '_ced_etsy_product_supply', true );
			if ( empty( $is_supply ) ) {
				$is_supply = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_product_supply' );
			}

			if ( isset( $is_supply ) && ! empty( $is_supply ) && 'true' == $is_supply ) {
				$is_supply = 1;
			}
			if ( isset( $is_supply ) && ! empty( $is_supply ) && 'false' == $is_supply ) {
				$is_supply = 0;
			}

			$processing_min = get_post_meta( $product_id, '_ced_etsy_processing_min', true );
			if ( empty( $processing_min ) ) {
				$processing_min = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_processing_min' );
			}

			$processing_max = get_post_meta( $product_id, '_ced_etsy_processing_max', true );
			if ( empty( $processing_max ) ) {
				$processing_max = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_processing_max' );
			}
			$saved_shop_etsy_details = isset( $this->saved_etsy_details[get_current_user_id()][$de_shop_name] ) ? $this->saved_etsy_details[get_current_user_id()][ $de_shop_name ] : array();
			$shippingTemplateId      = get_post_meta( $product_id, '_ced_etsy_shipping_profile', true );
			if ( empty( $shippingTemplateId ) ) {
				$shippingTemplateId = $this->fetchDokanMetaValueOfProduct( $product_id, '_ced_etsy_shipping_profile' );
			}
			if ( empty( $shippingTemplateId ) ) {
				if ( empty( $shippingTemplateId ) ) {
					$shippingTemplateId = isset( $saved_shop_etsy_details['shippingTemplateId'] ) ? $saved_shop_etsy_details['shippingTemplateId'] : '';
				}
			}
			$categoryId = $this->fetchDokanMetaValueOfProduct( $product_id, '_umb_etsy_category' );
			if ( ! empty( $calling_from ) && 'getDokanFormattedData' == $calling_from ) {
				$arguements = array(
					'quantity'             => (int) $productQuantity,
					'title'                => trim( $productTitle ),
					'description'          => strip_tags( $productDescription ),
					'price'                => (float) $productPrice,
					'shipping_profile_id' => doubleval( $shippingTemplateId ),
					'shop_section_id'      => null,
					'non_taxable'          => true,
					'state'                => $producTUploadType,
					'processing_min'       => (int) ! empty( $processing_min ) ? $processing_min : 1,
					'processing_max'       => (int) ! empty( $processing_max ) ? $processing_max : 3,
					'taxonomy_id'          => (int) $categoryId,
					'who_made'             => ! empty( $who_made ) ? $who_made : 'i_did',
					'is_supply'            => isset( $is_supply ) ? (int) $is_supply : 0,
					'when_made'            => ! empty( $when_made ) ? $when_made : 'made_to_order',
				);
			}

			if ( ! empty( $tags ) ) {
				$arguements['tags'] = $tags[0];
			}
			if ( ! empty( $materials ) ) {
				$arguements['materials'] = $materials[0];
			}

			if ( ! empty( $calling_from ) && 'prepareDokanDataForUpdating' == $calling_from ) {
				$arguements = array(
					'title'                => trim( $productTitle ),
					'description'          => strip_tags( $productDescription ),
					'shipping_template_id' => doubleval( $shippingTemplateId ),
					'shop_section_id'      => (int) $shop_section_id,
					'state'                => $producTUploadType,
					'taxonomy_id'          => (int) $categoryId,
					'who_made'             => $who_made,
					'is_supply'            => (bool) $is_supply,
					'when_made'            => ! empty( $when_made ) ? $when_made : 'made_to_order',
				);

				if ( ! empty( $tags ) ) {
					$arguements['tags'] = $tags;
				}
				if ( ! empty( $materials ) ) {
					$arguements['materials'] = $materials;
				}
			}

			if ( ! empty( $recipient ) ) {
				$arguements['recipient'] = $recipient;
			}

			if ( ! empty( $occasion ) ) {
				$arguements['occasion'] = $occasion;
			}
			return $arguements;
		}
		/**
		 * *************************************************************************************************************
		 * This function fetches meta value of a product in accordance with profile assigned and meta value available.
		 * *************************************************************************************************************
		 *
		 * @since 1.0.0
		 *
		 * @param int    $product_id Product  ids.
		 * @param string $metaKey meta key name .
		 * @param bool   $is_variation variation or not.
		 *
		 * @link  http://www.cedcommerce.com/
		 * @return $meta data
		 */

		private function fetchDokanMetaValueOfProduct( $product_id, $metaKey, $is_variation = false ) {

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


		/**
		 * ****************************************************
		 * UPLOADING THE VARIABLE AND SIMPLE PROUCT TO ETSY
		 * ****************************************************
		 *
		 * @since 1.0.0
		 *
		 * @param int    $product_id Product  ids.
		 * @param string $de_shop_name Active shop Name.
		 *
		 * @link  http://www.cedcommerce.com/
		 * @return Uploaded product Ids.
		 */

		private function douploadDokanProduct( $product_id, $de_shop_name ) {
			$vendor_id = get_current_user_id();
			$shop_id   = ced_etsy_dokan_get_shop_id( $de_shop_name, $vendor_id );
			do_action( 'ced_etsy_dokan_refresh_token', $de_shop_name, $vendor_id );
			$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $de_shop_name,"application/shops/{$shop_id}/listings", 'POST', array(), $this->data, $vendor_id );
			/**
			 * ************************************************
			 *  Update post meta after uploading the Products.
			 * ************************************************
			 *
			 * @since 2.0.8
			 */

			if ( isset( $response['listing_id'] ) ) {
				update_post_meta( $product_id, '_ced_etsy_listing_id_' . $de_shop_name, $response['listing_id'] );
				update_post_meta( $product_id, '_CED_ETSY_DOKAN_URL_' . $de_shop_name, $response['url'] );
			}

			if ( isset( $response['error'] ) ) {
				$error                 = array();
				$error['error']        = isset( $response['error'] ) ? $response['error'] : 'some error occured';
				$this->uploadResponse = $error;
			} else {
				$this->uploadResponse = $response;
			}
		}

	}
}
