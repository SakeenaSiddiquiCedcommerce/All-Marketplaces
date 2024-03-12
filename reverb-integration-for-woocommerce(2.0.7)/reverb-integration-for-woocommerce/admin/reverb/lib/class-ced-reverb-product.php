<?php

class Ced_Reverb_Product {

	public static $_instance;


		/**
		 * Ced_reverb_Config Instance.
		 *
		 * Ensures only one instance of Ced_reverb_Config is loaded or can be loaded.
		 *
		 * author CedCommerce <plugins@cedcommerce.com>
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
		$this->loadDependency();
		$this->end_point_url = $this->ced_reverb_config_instance->end_point_url;
		$this->client_id     = $this->ced_reverb_config_instance->client_id;
		$this->environment   = $this->ced_reverb_config_instance->environment;
	}


	public function loadDependency() {
		$file = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
		$this->reverbSendHttpRequestInstance = new Ced_Reverb_Curl_Request();
		$configfile                          = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-config.php';
		if ( file_exists( $configfile ) ) {
			include_once $configfile;
			$this->ced_reverb_config_instance = Ced_Reverb_Config::get_instance();
		}
	}

	public function ced_reverb_prepareDataForUploading( $proIDs = array(), $Offset = 'False' ) {


		$final_response          = '';
			$this->error_message = '';
		foreach ( $proIDs as $key => $value ) {
			$prod_data = wc_get_product( $value );
			if ( ! is_object( $prod_data ) ) {
				continue;
			}

			$type = $prod_data->get_type();
			if ( 'variable' == $type ) {
				$prod_data  = wc_get_product( $value );
				$variations = $prod_data->get_available_variations();
				foreach ( $variations as $variation ) {
					$preparedData = array();
					$attributes   = $variation['attributes'];
					$variation_id = $variation['variation_id'];

					$preparedData = $this->getFormattedData( $variation_id, $attributes );
					$preparedData = json_encode( $preparedData, true );
					$listing_id   = get_post_meta( $variation_id, 'ced_reverb_listing_id' . $this->environment, true );
					if ( ! empty( $listing_id ) ) {
						$response = $this->doupdate( $preparedData, $listing_id, 'Product' );
					} else {
						$response = $this->doupload( $preparedData, 'Product' );
					}
					$error = $response['message'];
					if ( 'Your listing has been created. We are processing your request and will send you an email if any errors are found.' != $error ) {
						if ( isset( $error ) && null != $error || is_array( $error ) ) {
							$this->error_message .= 'Variation ID# ' . $variation_id . '->Error Message ->' . $error . '</br>';

						}
					} else {
						if ( isset( $response['listing']['id'] ) ) {
							$state = $response['listing']['state']['description'];
							update_post_meta($variation_id, 'ced_reverb_state', $state);
							update_post_meta( $variation_id, 'ced_reverb_listing_id' . $this->environment, $response['listing']['id'] );
							update_post_meta( $variation_id, 'ced_reverb_listing_url' . $this->environment, $response['listing']['_links']['web']['href'] );
							update_post_meta( $variation_id, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
							update_post_meta( $value, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
						}
					}
				}
			} else {
				$preparedData = $this->getFormattedData( $value );


				$preparedData = json_encode( $preparedData, true );

				$listing_id = get_post_meta( $value, 'ced_reverb_listing_id' . $this->environment, true );
				if ( ! empty( $listing_id ) ) {
					$response = $this->doupdate( $preparedData, $listing_id, 'Product' );
				} else {
					$response = $this->doupload( $preparedData, 'Product' );
				}

				$error = $response['message'];
				if ( 'Your listing has been created. We are processing your request and will send you an email if any errors are found.' != $error ) {
					if ( isset( $error ) && null != $error ) {
						$this->error_message .= 'Product ID# ' . $value . '->Error Message ->' . $error . '</br>';
					}
				} else {
					if ( isset( $response['listing']['id'] ) ) {
						$state = $response['listing']['state']['description'];
						update_post_meta($value, 'ced_reverb_state', $state);
						update_post_meta( $value, 'ced_reverb_listing_id' . $this->environment, $response['listing']['id'] );
						update_post_meta( $value, 'ced_reverb_listing_url' . $this->environment, $response['listing']['_links']['web']['href'] );
						update_post_meta( $value, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
						update_post_meta( $value, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
					}
				}
			}
		}
		if ( '' != $this->error_message ) {
			$message              = $this->error_message;
			$classes              = 'error is-dismissable';
			$this->final_response = array(
				'message' => $message,
				'classes' => $classes,
			);
		} else {
			$notice['message']    = __( 'Product uploaded/updated successfully.', 'ced-reverb' );
			$notice['classes']    = 'notice notice-success is-dismissable';
			$this->final_response = $notice;
		}
		$this->final_response = json_encode( $this->final_response );
		return $this->final_response;
	}


	public function ced_reverb_prepareUpdateInventory( $proIDs = array(), $Offset = 'False' ) {
		$final_response          = '';
			$this->error_message = '';
		foreach ( $proIDs as $key => $value ) {
			$prod_data = wc_get_product( $value );
			if ( ! is_object( $prod_data ) ) {
				continue;
			}

			$type = $prod_data->get_type();
			if ( 'variable' == $type ) {
				$prod_data  = wc_get_product( $value );
				$variations = $prod_data->get_available_variations();
				foreach ( $variations as $variation ) {
					$preparedData = array();
					$attributes   = $variation['attributes'];
					$variation_id = $variation['variation_id'];

					$preparedData = $this->ced_reverb_getFormattedDataForInventory( $variation_id, $attributes );
					$preparedData = json_encode( $preparedData, true );
					$listing_id   = get_post_meta( $variation_id, 'ced_reverb_listing_id' . $this->environment, true );
					if ( ! empty( $listing_id ) ) {
						$response = $this->doupdate( $preparedData, $listing_id, 'Product' );
						$error    = $response['message'];
						if ( "Your draft listing has been saved. You can come back to finish it when you're ready." != $error ) {
							if ( isset( $error ) && null != $error ) {
								$this->error_message .= 'Variation ID# ' . $variation_id . '->Error Message ->' . $error . '</br>';
							}
						} else {
							if ( isset( $response['listing']['id'] ) ) {
								$state = $response['listing']['state']['description'];
								update_post_meta($variation_id, 'ced_reverb_state', $state);
								update_post_meta( $variation_id, 'ced_reverb_listing_id' . $this->environment, $response['listing']['id'] );
								update_post_meta( $variation_id, 'ced_reverb_listing_url' . $this->environment, $response['listing']['_links']['web']['href'] );
								update_post_meta( $variation_id, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
								update_post_meta( $value, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
								update_post_meta( $variation_id, 'ced_reverb_last_sync_status' . $this->environment, gmdate( 'l jS \of F Y h:i:s A' ) );
							}
						}
					} else {
						$this->error_message .= 'variation ID# ' . $value . '->Need to upload first </br>';
					}
				}
			} else {
				$preparedData = $this->ced_reverb_getFormattedDataForInventory( $value );
				$preparedData = json_encode( $preparedData, true );

				$listing_id = get_post_meta( $value, 'ced_reverb_listing_id' . $this->environment, true );
				if ( ! empty( $listing_id ) ) {
					$response = $this->doupdate( $preparedData, $listing_id, 'Product' );

					if(isset($response['errors']) && !empty($response['errors'])){

						update_post_meta($value, 'ced_reverb_error_at_update_inventory', $response['errors']);
					}else{

						delete_post_meta($value, 'ced_reverb_error_at_update_inventory');
					}

					$error = $response['message'];
					if ( "Your draft listing has been saved. You can come back to finish it when you're ready." != $error ) {
						if ( isset( $error ) && null != $error ) {
							$this->error_message .= 'Product ID# ' . $value . '->Error Message ->' . $error . '</br>';
						}
					} else {
						if ( isset( $response['listing']['id'] ) ) {

							$state = $response['listing']['state']['description'];
							update_post_meta($value, 'ced_reverb_state', $state);
							update_post_meta( $value, 'ced_reverb_listing_id' . $this->environment, $response['listing']['id'] );
							update_post_meta( $value, 'ced_reverb_listing_url' . $this->environment, $response['listing']['_links']['web']['href'] );
							update_post_meta( $value, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
							update_post_meta( $value, 'ced_reverb_listing_status' . $this->environment, 'PUBLISHED' );
							update_post_meta( $value, 'ced_reverb_last_sync_status' . $this->environment, gmdate( 'l jS \of F Y h:i:s A' ) );
						}
					}
				} else {
					$this->error_message .= 'Product ID# ' . $value . '->Need to upload first </br>';
				}
			}
		}
		if ( '' != $this->error_message ) {
			$message              = $this->error_message;
			$classes              = 'error is-dismissable';
			$this->final_response = array(
				'message' => $message,
				'classes' => $classes,
			);
		} else {
			$notice['message']    = __( 'Product Inventory Updated successfully.', 'ced-reverb' );
			$notice['classes']    = 'notice notice-success is-dismissable';
			$this->final_response = $notice;
		}
		$this->final_response = json_encode( $this->final_response );
		return $this->final_response;
	}



	/**
	 * Remove selected products on REVERB.
	 * author CedCommerce <plugins@cedcommerce.com>
	 *
	 * @since 1.0.0
	 *
	 * @param array $proIds
	 */
	public function ced_reverb_reverbRemove( $proIds = array(), $isWriteXML = true ) {

		$final_response = array();
		$message        = '';
		foreach ( $proIds as $key => $productId ) {
			$_product    = wc_get_product( $productId );
			$productType = $_product->get_type();

			if ( 'variable' == $productType ) {
				$variations = $_product->get_available_variations();
				foreach ( $variations as $variation ) {
					$variation  = $variation['variation_id'];
					$listing_id = get_post_meta( $variation, 'ced_reverb_listing_id' . $this->environment, true );
					if ( isset( $listing_id ) && ! empty( $listing_id ) ) {
						$delete_product = $this->ced_reverb_remove_product( $listing_id );
						if ( 'Draft deleted' == $delete_product['message'] ) {
							delete_post_meta( $variation, 'ced_reverb_listing_id' . $this->environment );
							delete_post_meta( $variation, 'ced_reverb_listing_url' . $this->environment );
							delete_post_meta( $variation, 'ced_reverb_listing_status' . $this->environment );
							delete_post_meta( $productId, 'ced_reverb_listing_status' . $this->environment );
							$message        = 'Product Removed Successfully From Reverb';
							$classes        = 'notice notice-success is-dismissable';
							$final_response = array(
								'message' => $message,
								'classes' => $classes,
							);
						}else{

							$final_response = array(
								'message' => $delete_product['message'],
								'classes' => 'error is-dismissable',
							);
						}
					}
				}
			} else {
				$listing_id = get_post_meta( $productId, 'ced_reverb_listing_id' . $this->environment, true );
				if ( isset( $listing_id ) && ! empty( $listing_id ) ) {
					$delete_product = $this->ced_reverb_remove_product( $listing_id );
					if ( 'Draft deleted' == $delete_product['message'] ) {
						delete_post_meta( $productId, 'ced_reverb_listing_id' . $this->environment );
						delete_post_meta( $productId, 'ced_reverb_listing_url' . $this->environment );
						delete_post_meta( $productId, 'ced_reverb_listing_status' . $this->environment );

						$message        = 'Product Removed Successfully From Reverb';
						$classes        = 'notice notice-success is-dismissable';
						$final_response = array(
							'message' => $message,
							'classes' => $classes,
						);
					}else{

						$final_response = array(
							'message' => $delete_product['message'],
							'classes' => 'error is-dismissable',
						);
					}
				}
			}
		}
		return json_encode( $final_response );
	}

	public function doImageUpload( $listingID, $product_id ) {
		$product    = wc_get_product( $product_id );
		$image_path = '';
		if ( 'variation' == $product->get_type() ) {
			$variant_parent_id = $product->get_parent_id();
			$parentId          = $variant_parent_id;

			$image_id   = get_post_meta( $product_id, '_thumbnail_id', true );
			$image_path = get_attached_file( $image_id );
			if ( isset( $image_path ) && empty( $image_path ) ) {
				$image_id   = get_post_meta( $parentId, '_thumbnail_id', true );
				$image_path = get_attached_file( $image_id );
			}
		} else {
			$image_id   = get_post_meta( $product_id, '_thumbnail_id', true );
			$image_path = get_attached_file( $image_id );
		}
		if ( ! empty( $image_path ) && ! empty( $listingID ) ) {
			$args = array(
				'params' => array(
					'listing_id' => $listingID,
				),
				'data'   => array(
					'image' => array( '@' . $image_path . ';type=image/jpeg' ),
				),
			);
			return $args;
		}
	}


	/**
	 * This function is used to get product data
	 * getFormattedData
	 *
	 * @param  mixed $proIds
	 * @param  mixed $shopId
	 * @param  mixed $attributesforVariation
	 * @return void
	 */
	public function getFormattedData( $proIds = array(), $attributesforVariation = '' ) {

		
		$profileData = $this->ced_reverb_getProfileAssignedData( $proIds );
		if ( ! $this->profile_assigned ) {
			return;
		}
		$product = wc_get_product( $proIds );

		if ( '3.0.0' < WC()->version ) {
			$product_data       = $product->get_data();
			$product_attributes = $product->get_attributes();
			$productType        = $product->get_type();
			$description        = $product_data['description'] . ' ' . $product_data['short_description'];

			$custom_description = get_post_meta( $proIds, '_ced_reverb_custom_description', true );
			if ( ! empty( $custom_description ) ) {
				$description = $custom_description;
			}

			if ( 'variation' == $product->get_type() ) {
				$parentId           = $product->get_parent_id();
				$parentProduct      = wc_get_product( $parentId );
				$parentProductData  = $parentProduct->get_data();
				$product_attributes = $parentProduct->get_attributes();
				$description        = $parentProductData['description'] . '</br>' . $parentProductData['short_description'];

				$custom_description = get_post_meta( $parentId, '_ced_reverb_custom_description', true );
				if ( ! empty( $custom_description ) ) {
					$description = $custom_description;
				}
			}
			$title        = $product_data['name'];
			$custom_title = get_post_meta( $proIds, 'ced_reverb_custom_title', true );
			if ( ! empty( $custom_title ) ) {
				$title = $custom_title;
			}

			$price = (float) $product_data['price'];
			if ( 'variation' == $productType ) {
				$parent_id      = $product->get_parent_id();
				$parent_product = wc_get_product( $parent_id );
				$parent_product = $parent_product->get_data();
			}
			$stock_qty = (int) $product_data['stock_quantity'];
		}

		$weight         = get_post_meta( $proIds, '_weight', true );
		$package_length = get_post_meta( $proIds, '_length', true );
		$package_width  = get_post_meta( $proIds, '_width', true );
		$package_height = get_post_meta( $proIds, '_height', true );
		$item_sku       = get_post_meta( $proIds, '_sku', true );
		$keywords       = get_post_meta( $proIds, 'ced_reverb_product_keywords', true );
		$dimension_unit = get_option( 'woocommerce_dimension_unit', true );
		$weight_unit    = get_option( 'woocommerce_weight_unit', true );

		$description = preg_replace( '/<img[^>]+\>/i', '', $description );
		$description = preg_replace( '/<\/?a[^>]*>/', '', $description );

		$product = wc_get_product( $proIds );
		if ( $product->get_type() == 'variable' ) {
			$variations = $product->get_available_variations();
			if ( $variations ) {
				if ( 0 == $weight || '' == $weight ) {
					$weight = $variations[0]['weight'];
				}
				if ( 0 == $package_width || '' == $package_width ) {
					$package_width = $variations[0]['dimensions']['width'];
				}
				if ( 0 == $package_height || '' == $package_height ) {
					$package_height = $variations[0]['dimensions']['height'];
				}
				if ( 0 == $package_length || '' == $package_length ) {
					$package_length = $variations[0]['dimensions']['length'];
				}
			}
		}

		// get category
		$category_id        = $this->fetchMetaValueOfProduct( $proIds, '_umb_reverb_category' );
		$args['categories'] = array(
			array(
				'uuid' => $category_id,
			),
		);

		// get product specific attribute
		$product_specific_attribute_key = get_option( 'ced_reverb_product_specific_attribute_key_' . $category_id );

		if ( isset( $product_specific_attribute_key ) && is_array( $product_specific_attribute_key ) && ! empty( $product_specific_attribute_key ) ) {
			foreach ( $product_specific_attribute_key as $key => $product_key ) {
				foreach ( $profileData as $key => $value ) {
					if ( '_ced_reverb_' . $product_key == $key ) {
						if ( ! empty( get_post_meta( $proIds, $key, true ) ) ) {
							$args[ $product_key ] = get_post_meta( $proIds, $key, true );
						} elseif ( ! empty( $this->fetchMetaValueOfProduct( $proIds, $key ) ) ) {
							$args[ $product_key ] = $this->fetchMetaValueOfProduct( $proIds, $key );
						}
					}
				}
			}
		}

		$ced_reverb_misce_fields_attribute_key = get_option( 'ced_reverb_misce_fields_attribute_key_' . $category_id );

		if ( isset( $ced_reverb_misce_fields_attribute_key ) && is_array( $ced_reverb_misce_fields_attribute_key ) && ! empty( $ced_reverb_misce_fields_attribute_key ) ) {
			foreach ( $ced_reverb_misce_fields_attribute_key as $key => $product_key ) {
				foreach ( $profileData as $key => $value ) {
					if ( '_ced_reverb_' . $product_key == $key ) {

						if ( ! empty( get_post_meta( $proIds, $key, true ) ) ) {
							$args[ $product_key ] = get_post_meta( $proIds, $key, true );
						} elseif ( ! empty( $this->fetchMetaValueOfProduct( $proIds, $key ) ) ) {
							$args[ $product_key ] = $this->fetchMetaValueOfProduct( $proIds, $key );
						}
					}
				}
			}
		}

		$price = $this->get_updated_price( $proIds, $price );

		if ( isset( $args['upc_does_not_apply'] ) && null != $args['upc_does_not_apply'] ) {
			if ( 'false' == $args['upc_does_not_apply'] ) {
				$args['upc_does_not_apply'] = false;
			} else {
				$args['upc_does_not_apply'] = true;
				unset( $args['upc'] );
			}
		}

		$stock_status_check = get_post_meta($proIds, '_stock_status', true);

		if($stock_status_check == "outofstock"){

			$args['publish'] ='false';
			
		}elseif ($stock_status_check == "instock") {
			
			$args['publish'] = 'true';
		}

		if ( isset( $args['publish'] ) && null != $args['publish'] ) {
			if ( 'false' == $args['publish'] ) {
				$args['publish'] = false;
			} else {
				$args['publish'] = true;
			}
		}

		if ( isset( $args['tax_exempt'] ) && null != $args['tax_exempt'] ) {
			if ( 'false' == $args['tax_exempt'] ) {
				$args['tax_exempt'] = false;
			} else {
				$args['tax_exempt'] = true;
			}
		}

		if ( isset( $args['offers_enabled'] ) && null != $args['offers_enabled'] ) {
			if ( 'false' == $args['offers_enabled'] ) {
				$args['offers_enabled'] = false;
			} else {
				$args['offers_enabled'] = true;
			}
		}

		if ( empty( $args['sku'] ) ) {
			$args['sku'] = $item_sku;
		}

		if ( empty( $args['title'] ) ) {
			$args['title'] = $title;
		}

		if ( ! empty( $args['title_prefix'] ) ) {
			$args['title'] = ucwords( $args['title_prefix'] ) . ' - ' . $args['title'];
		}
		if ( ! empty( $args['title_suffix'] ) ) {
			$args['title'] = $args['title'] . ' - ' . ucwords( $args['title_suffix'] );
		}

		if ( empty( $args['description'] ) ) {
			$args['description'] = $description;
		}

		if ( ! empty( $args['condition'] ) ) {
			$args['condition'] = array( 'uuid' => $args['condition'] );
		}

		if ( ! empty( $price ) ) {
			$args['price'] = array( 'amount' => $price );
		}
		if ( ! empty( $args['price_currency'] ) ) {
			$args['price']['currency'] = $args['price_currency'];
		}

		$manage_stock = get_post_meta( $proIds, '_manage_stock', true );

		// get manage stock status
		$stock_status = get_post_meta( $proIds, '_stock_status', true );
		if ( trim( $stock_status ) == 'outofstock' ) {
			$stock_qty = 0;
		} elseif ( trim( $stock_status ) == 'instock' && trim( $manage_stock ) == 'no' ) {
			if ( ! empty( $args['default_stock'] ) ) {
				$stock_qty = $args['default_stock'];

			} else {
				$stock_qty = 1;
			}
		}

		if($stock_status_check == "outofstock"){

			

			$args['has_inventory'] ='false';
			
		}elseif ($stock_status_check == "instock") {
			
			$args['has_inventory'] = 'true';
		}
		//$args['has_inventory'] = 'true';
		if ( ! empty( $stock_qty ) ) {
			$args['inventory'] = (int) $stock_qty;
		}elseif ($stock_qty == 0 || $stock_qty == "" || $stock_qty == "0") {
			
			$args['inventory'] = 0;
		}

		$shipping_rates_final = array();

		if ( isset( $args['shipping_us_con'] ) && ! empty( $args['shipping_us_con'] ) ) {
			$args['shipping_us_con'] = (float) $args['shipping_us_con'];
			$shipping_us_con_final   = array(
				'region_code' => $args['shipping_regions'],
				'rate'        => array(
					'amount'   => $args['shipping_us_con'],
					'currency' => $args['price_currency'],
				),
			);
			$shipping_rates_final[]  = $shipping_us_con_final;
		}

		if ( isset( $args['shipping_other_then_us_con'] ) && ! empty( $args['shipping_other_then_us_con'] ) ) {
			$args['shipping_other_then_us_con'] = (float) $args['shipping_other_then_us_con'];
			$shipping_other_then_us_con_final   = array(
				'region_code' => $args['shipping_regions'],
				'rate'        => array(
					'amount'   => $args['shipping_other_then_us_con'],
					'currency' => $args['price_currency'],
				),
			);
			$shipping_rates_final[]             = $shipping_other_then_us_con_final;

		}

		if ( empty( $args['shipping_profile_id'] ) ) {
			if ( is_array( $shipping_rates_final ) && ! empty( $shipping_rates_final ) ) {
				$args['shipping'] = array(
					'local' => 1,
					'rates' => $shipping_rates_final,
				);
			}
		}

		unset( $args['shipping_us_con'] );
		unset( $args['shipping_other_then_us_con'] );
		unset( $args['price_currency'] );
		unset( $args['inventory_feature'] );
		unset( $args['inventory_difference'] );
		unset( $args['markup_price'] );
		unset( $args['markup_type'] );
		unset( $args['shipping_regions'] );
		unset( $args['default_stock'] );
		unset( $args['title_suffix'] );
		unset( $args['title_prefix'] );

		if ( $product->get_type() == 'variation' ) {
			$variant_parent_id = $product->get_parent_id();
			$parent_sku        = get_post_meta( $variant_parent_id, '_sku', true );

			$parentId = $variant_parent_id;

			$pictureUrl = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) : '';
			if ( isset( $pictureUrl ) && ! empty( $pictureUrl ) ) {
				$args['photos'][0] = $pictureUrl;
			} else {
				$pictureUrl        = wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'full' ) : '';
				$args['photos'][0] = $pictureUrl;
			}

			$attachment_ids = $product->get_gallery_image_ids();
			if ( empty( $attachment_ids ) ) {
				$variant_parent_id = $product->get_parent_id();
				$parent_product    = wc_get_product( $variant_parent_id );
				$attachment_ids    = $parent_product->get_gallery_image_ids();
			}
			if ( ! empty( $attachment_ids ) ) {
				$count = 1;
				foreach ( $attachment_ids as $attachment_id ) {
					if ( $count > 8 ) {
						continue;
					}
					$args['photos'][ $count ] = wp_get_attachment_url( $attachment_id );
					++$count;
				}
			}
		} else {
			$pictureUrl = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) : '';

			$args['photos'][0] = $pictureUrl;

			$attachment_ids = $product->get_gallery_image_ids();
			if ( ! empty( $attachment_ids ) ) {
				$count = 1;
				foreach ( $attachment_ids as $attachment_id ) {
					if ( $count > 8 ) {
						continue;
					}
					$args['photos'][ $count ] = wp_get_attachment_url( $attachment_id );
					++$count;
				}
			}
		}

		// hold product description
		if ( empty( $args['description'] ) ) {
			$args['description'] = $description;
		}

		if(isset($args['description']) && !empty($args['description'])){

			$args['description'] = nl2br($args['description']);
		}
		ksort( $args );

		return $args;

	}

	/**
	 * This function is used to get price
	 * get_updated_price
	 *
	 * @param  mixed $proIds
	 * @param  mixed $price
	 */
	public function get_updated_price( $proIds, $price ) {
		$markup_type = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_markup_type' );

			$markup_value = (int) $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_markup_price' );
			$custom_price = get_post_meta( $proIds, '_ced_reverb_custom_price', true );
		if ( empty( $custom_price ) ) {
			$fetch_price = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_custom_price' );
			if ( ! empty( $fetch_price ) ) {
				$price = $fetch_price;
			}
		} else {
			$price = $custom_price;
		}

		if ( ! empty( $markup_type ) ) {
			if ( ! empty( $markup_value ) ) {

				if ( 'Fixed_Increased' == $markup_type ) {
					$price = $price + $markup_value;
				} elseif ( 'Fixed_Decreased' == $markup_type ) {
					if ( $markup_value >= $price ) {
						$price = $price;
					} else {
						$price = $price - $markup_value;
					}
				} elseif ( 'Percentage_Increased' == $markup_type ) {
					$price = ( $price + ( ( $markup_value / 100 ) * $price ) );
				} elseif ( 'Percentage_Decreased' == $markup_type ) {
					$percentage_Decreased_price = ( $markup_value / 100 ) * $price;
					if ( $percentage_Decreased_price >= $price ) {
						$price = $price;
					} else {
						$price = ( $price - ( ( $markup_value / 100 ) * $price ) );
					}
				}
			}
		}
		return $price;

	}




	public function ced_reverb_getFormattedDataForInventory( $proIds = array(), $attributesforVariation = '' ) {
		$profileData = $this->ced_reverb_getProfileAssignedData( $proIds );

		$product = wc_get_product( $proIds );

		if ( WC()->version > '3.0.0' ) {
			$product_data       = $product->get_data();
			$product_attributes = $product->get_attributes();
			$productType        = $product->get_type();
			$description        = $product_data['description'] . ' ' . $product_data['short_description'];
			$title              = '';

			$custom_description = get_post_meta( $proIds, '_ced_reverb_description', true );
			if ( ! empty( $custom_description ) ) {
				$description = $custom_description;
			}

			if ( $product->get_type() == 'variation' ) {
				$parentId           = $product->get_parent_id();
				$parentProduct      = wc_get_product( $parentId );
				$parentProductData  = $parentProduct->get_data();
				$product_attributes = $parentProduct->get_attributes();
				$description        = $parentProductData['description'] . '</br>' . $parentProductData['short_description'];

				$custom_description = get_post_meta( $parentId, '_ced_reverb_description', true );
				if ( ! empty( $custom_description ) ) {
					$description = $custom_description;
				}
			}

			$custom_title = get_post_meta( $proIds, '_ced_reverb_title', true );
			if ( ! empty( $custom_title ) ) {
				$title = $custom_title;
			} else {
				$title = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_title' );
				if ( empty( $title ) ) {
					$title = $product_data['name'];
				}
			}

			$price = (float) $product_data['price'];
			if ( 'variation' == $productType ) {
				$parent_id      = $product->get_parent_id();
				$parent_product = wc_get_product( $parent_id );
				$parent_product = $parent_product->get_data();
			}
			$stock_qty = (int) $product_data['stock_quantity'];
		}

		$inventory_feature = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_inventory_feature' );
		if ( isset( $inventory_feature ) && 'enable' == $inventory_feature ) {
			$inventory_difference = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_inventory_difference' );
			if ( isset( $inventory_difference ) && ! empty( $inventory_difference ) ) {
				$stock_qty = $stock_qty + $inventory_difference;
			}
		}

		$default_stock = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_default_stock' );
		$manage_stock  = get_post_meta( $proIds, '_manage_stock', true );
		// get manage stock status
		$stock_status = get_post_meta( $proIds, '_stock_status', true );
		if ( 'outofstock' == trim( $stock_status ) ) {
			$stock_qty = 0;
		} elseif ( trim( $stock_status ) == 'instock' && trim( $manage_stock ) == 'no' ) {
			if ( ! empty( $default_stock ) ) {
				$stock_qty = $default_stock;
			} else {
				$stock_qty = 1;
			}
		}

		if($stock_status == 'outofstock'){

			$args['has_inventory'] = 'false';
		}elseif ($stock_status == 'instock') {
			
			$args['has_inventory'] = 'true';
		}

		if ( $stock_qty < 0 ) {
			$stock_qty = 0;
		}

		if ( ! empty( $stock_qty ) ) {
			$args['inventory'] = (int) $stock_qty;
		}elseif ($stock_qty == 0 || $stock_qty == "" || $stock_qty == '0') {
			
			$args['inventory'] = 0;
		}

		$price = $this->get_updated_price( $proIds, $price );
		if ( ! empty( $price ) ) {
			$args['price'] = array( 'amount' => $price );
		}

		$currency = $this->fetchMetaValueOfProduct( $proIds, '_ced_reverb_price_currency' );
		if ( ! empty( $currency ) && null != $currency && '--select--' != $currency ) {
			$args['price']['currency'] = $currency;
		}
		return $args;
	}


	/*
	*
	*function for getting profile data of the product
	*
	*
	*/
	public function ced_reverb_getProfileAssignedData( $proIds ) {
		$_product = wc_get_product( $proIds );
		if ( 'variation' == $_product->get_type() ) {
			$proIds = $_product->get_parent_id();
		}
		$_product     = wc_get_product( $proIds );
		$product_data = $_product->get_data();
		$category_ids = isset( $product_data['category_ids'] ) ? $product_data['category_ids'] : array();

		$this->profile_assigned = false;
		foreach ( $category_ids as $key => $cat_id ) {
			$category_id = get_term_meta( $cat_id, 'ced_reverb_mapped_category', true );
			if ( ! empty( $category_id ) ) {
				$this->category_id      = $category_id;
				$this->profile_assigned = true;
				break;
			}
		}
		if ( ! $this->profile_assigned ) {
			return;
		}

		$ced_reverb_profile_data = get_option( 'ced_reverb_profile_data', array() );
		$profile_data            = '';

		if ( ! empty( $ced_reverb_profile_data ) ) {
			$profile_data = $ced_reverb_profile_data[ $category_id ];
			$profile_data = json_decode( $profile_data, true );
		}

		$this->profile_data = isset( $profile_data ) ? $profile_data : array();
		return $this->profile_data;
	}

	/*
	*
	*function for getting meta value of the product
	*
	*
	*/
	public function fetchMetaValueOfProduct( $proIds, $metaKey, $is_sync = false, $sync_data = array() ) {

		if ( $is_sync ) {
			$this->profile_assigned = true;
			$this->profile_data     = $sync_data;
		}

		if ( '_woocommerce_title' == $metaKey ) {
			$product = wc_get_product( $proIds );
			return $product->get_title();
		}if ( '_woocommerce_short_description' == $metaKey ) {
			$product = wc_get_product( $proIds );
			if ( $product->get_type() == 'variation' ) {
				$_parent_obj = wc_get_product( $product->get_parent_id() );
				return $_parent_obj->get_short_description();
			}
			return $product->get_short_description();

		}if ( '_woocommerce_description' == $metaKey ) {
			$product = wc_get_product( $proIds );
			if ( $product->get_type() == 'variation' ) {
				$_parent_obj = wc_get_product( $product->get_parent_id() );
				return $_parent_obj->get_description();
			}
			return $product->get_description();
		}

		if ( isset( $this->profile_assigned ) && $this->profile_assigned ) {
			$_product = wc_get_product( $proIds );
			if ( $_product->get_type() == 'variation' ) {
				$parentId = $_product->get_parent_id();
			} else {
				$parentId = '0';
			}
			if ( ! empty( $this->profile_data ) && isset( $this->profile_data[ $metaKey ] ) ) {
				$profileData     = $this->profile_data[ $metaKey ];
				$tempProfileData = $profileData;
				if ( isset( $tempProfileData['default'] ) && ! empty( $tempProfileData['default'] ) && '' != $tempProfileData['default'] && ! is_null( $tempProfileData['default'] ) ) {
					$value = $tempProfileData['default'];
				} elseif ( isset( $tempProfileData['metakey'] ) && ! empty( $tempProfileData['metakey'] ) && 'null' != $tempProfileData['metakey'] ) {

					if ( '_woocommerce_title' == $tempProfileData['metakey'] ) {
						$product = wc_get_product( $proIds );
						return $product->get_title();
					}if ( '_woocommerce_short_description' == $tempProfileData['metakey'] ) {
						$product = wc_get_product( $proIds );
						if ( $product->get_type() == 'variation' ) {
							$_parent_obj = wc_get_product( $product->get_parent_id() );
							return $_parent_obj->get_short_description();
						}
						return $product->get_short_description();

					}if ( '_woocommerce_description' == $tempProfileData['metakey'] ) {
						$product = wc_get_product( $proIds );
						if ( $product->get_type() == 'variation' ) {
							$_parent_obj = wc_get_product( $product->get_parent_id() );
							return $_parent_obj->get_description();
						}
						return $product->get_description();
					}

					if ( strpos( $tempProfileData['metakey'], 'umb_pattr_' ) !== false ) {

						$wooAttribute = explode( 'umb_pattr_', $tempProfileData['metakey'] );
						$wooAttribute = end( $wooAttribute );

						if ( $_product->get_type() == 'variation' ) {
							$var_product = wc_get_product( $parentId );
							$attributes  = $var_product->get_variation_attributes();
							if ( isset( $attributes[ 'attribute_pa_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $wooAttribute ] ) ) {
								$wooAttributeValue = $attributes[ 'attribute_pa_' . $wooAttribute ];
								if ( '0' != $parentId ) {
									$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
								} else {
									$product_terms = get_the_terms( $proIds, 'pa_' . $wooAttribute );
								}
							} else {
								$wooAttributeValue = $var_product->get_attribute( 'pa_' . $wooAttribute );
								$wooAttributeValue = explode( ',', $wooAttributeValue );
								$wooAttributeValue = $wooAttributeValue[0];

								if ( '0' != $parentId ) {
									$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
								} else {
									$product_terms = get_the_terms( $proIds, 'pa_' . $wooAttribute );
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
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						} else {
							$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );
							$product_terms     = get_the_terms( $proIds, 'pa_' . $wooAttribute );
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
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} elseif ( ! empty( $wooAttributeValue ) ) {
								$value = $wooAttributeValue;
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						}
					} else {

						$value = get_post_meta( $proIds, $tempProfileData['metakey'], true );
						if ( '_thumbnail_id' == $tempProfileData['metakey'] ) {
							$value = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'thumbnail' ) : '';
						}
						if ( ! isset( $value ) || empty( $value ) || '' == $value || is_null( $value ) || '0' == $value || 'null' == $value ) {
							if ( '0' != $parentId ) {

								$value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
								if ( '_thumbnail_id' == $tempProfileData['metakey'] ) {
									$value = wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) : '';
								}

								if ( ! isset( $value ) || empty( $value ) || '' == $value || is_null( $value ) ) {
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						}
					}
				} else {
					$value = get_post_meta( $proIds, $metaKey, true );
				}
			} else {
				$value = get_post_meta( $proIds, $metaKey, true );
			}
			return $value;
		}

	}
	public function doupload( $file, $uploadType) {

		$response = $this->uploadToreverb( $file, $uploadType);
		return $response;

	}
	public function doupdate( $file, $id, $uploadType) {

		$response = $this->updateToreverb( $file, $id, $uploadType);
		return $response;

	}
	public function uploadToreverb( $parameters, $uploadType = '') {
		require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		if ( 'Product' == $uploadType ) {
			$action = 'listings';
		}

		$parameters     = $parameters;
		$sendRequestObj = new Ced_Reverb_Curl_Request();

		$response = $sendRequestObj->ced_reverb_request( $action, $parameters, $uploadType );
		return $response;
	}

	public function updateToreverb( $parameters, $id, $uploadType) {
		require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		if ( 'Product' == $uploadType ) {
			$action = 'listings/' . $id;
		}

		$parameters     = $parameters;
		$sendRequestObj = new Ced_Reverb_Curl_Request();

		$response = $sendRequestObj->ced_reverb_request( $action, $parameters, $uploadType, 'PUT' );
		return $response;
	}

	public function uploadListingImage( $parameters = '', $id = '', $uploadType = '', $isCron = false ) {
		require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		if ( 'Product' == $uploadType ) {
			$action = 'listings/' . $id . '/images';
		}

		$parameters     = $parameters;
		$sendRequestObj = new Ced_Reverb_Curl_Request();

		$response = $sendRequestObj->ced_reverb_request( $action, $parameters, $uploadType, 'PUT' );
		return $response;
	}

	public function ced_reverb_remove_product( $listing_id ) {
		require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		$sendRequestObj = new Ced_Reverb_Curl_Request();
		$response       = $sendRequestObj->ced_reverb_request( 'listings/' . $listing_id, '', '', 'DELETE' );
		return $response;
	}
}
