<?php
class Class_Ced_Wcfm_Etsy_Products {
	/**
	 * Listing ID variable
	 *
	 * @var int
	 */
	public $listing_id;
	/**
	 * Ced Etsy global settings
	 *
	 * @var array
	 */
	public $ced_wcfm_global_settings;
	/**
	 * Profile assign flag
	 *
	 * @var bool
	 */
	public $is_profile_assing;
	/**
	 * Mapped profile data.
	 *
	 * @var int
	 */
	public $profile_data;
	/**
	 * Mapped profile data.
	 *
	 * @var int
	 */
	public $var_array;

	/**
	 * Is product type dowloadable or not.
	 *
	 * @var array
	 */
	public $is_downloadable;
	/**
	 * Downloadable file data.
	 *
	 * @var string
	 */
	public $downloadable_data;
	/**
	 * Product Type variable
	 *
	 * @var int
	 */
	public $product_type;

	/**
	 * Etsy shop name.
	 *
	 * @var string
	 */
	public $shop_name;

	/**
	 * Product ID.
	 *
	 * @var int
	 */
	public $product;

	/**
	 * Etsy Payload response.
	 *
	 * @var string
	 */
	public $response;
	public $product_id;
	public $pro_data = array();
	public $profile_id;
	public $profile_name;
	public $prod_obj;
	public $parent_id;
	public $product_arguements = array();
	public $error              = array();

	public $required;
	public $recommended;
	public $optional;
	public $shipping;
	public $personalization;
	public static $_instance;

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

	/**
	 * Hold the Woocommerce product.
	 *
	 * @since    2.0.8
	 * @var      string    $ced_product    Wocommerce product.
	 */
	public $ced_product;
	/**
	 * The listing ID of uploaded product.
	 *
	 * @since    1.0.0
	 * @var      string    $l_id    The listing ID of the product.
	 */
	private $l_id;
	/**
	 * Is this auto or manual flag
	 * 
	 * @since 2.2.4
	 * @var strig $is_cron Is cron flag
	 */

	public $is_cron;

	/**
	 * 
	 * Prepared product Payload.
	 * 
	 * @since    1.0.0
	 * @var      string    $product_payload .
	 */
	private $product_payload;


	public function __construct( $product_id = '', $shop_name = '', $listing_id = '' ) {
		$this->shop_name = $shop_name;
		$this->ced_wcfm_global_settings = get_option( 'ced_etsy_wcfm_global_settings', array() );
		$this->shop_name           = $shop_name;
		$this->product_id          = $product_id;
		$this->listing_id          = $listing_id;
		if ( $this->shop_name ) {
			$this->ced_wcfm_global_settings = isset( $this->ced_wcfm_global_settings[ $this->shop_name ] ) ? $this->ced_wcfm_global_settings[ $this->shop_name ] : $this->ced_wcfm_global_settings;
		}
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

	public function ced_etsy_upload_product_to_etsy( $pro_ids = array(), $shop_name = '', $is_cron = false ) {
		$this->is_cron = $is_cron;
		if ( ! is_array( $pro_ids ) ) {
			$pro_ids = array( $pro_ids );
		}
		if ( is_array( $pro_ids ) && ! empty( $pro_ids ) ) {
			$shop_name = trim( $shop_name );
			$response  = self::prepare_items( $pro_ids, $shop_name, $is_cron );
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
	private function prepare_items( $pro_ids = array(), $shop_name = '', $is_sync = false ) {

		if ( '' == $shop_name || empty( $shop_name ) ) {
			return;
		}
		$notification = array();
		$vendor_id    = !empty( ced_etsy_wcfm_get_vendor_id() ) ? ced_etsy_wcfm_get_vendor_id() : 0;
		foreach ( $pro_ids as $key => $pr_id ) {
			$already_uploaded = get_post_meta( $pr_id, '_ced_etsy_wcfm_listing_id_' .$vendor_id. $shop_name, true );
			if ( $already_uploaded ) {
				$notification['status']  = 400;
				$notification['message'] = 'Product already uploaded';
				continue;
			}
			$this->ced_product 		= wc_get_product( absint( $pr_id ) );
			$pro_type          		= $this->ced_product->get_type();
			$supported_pro_type     = apply_filters('woocommerce_etsy_integration_product_type', array('variable','simple') );
			$notification['status'] = 400;
			if (!in_array($pro_type, $supported_pro_type)) {
				$notification['message'] = $pro_type .' product type not supported';
				continue;
			}

			$prepared_payload      = $this->get_formatted_data( $pr_id, $shop_name );
			$this->product_payload = apply_filters( 'ced_etsy_product_upload_payload', $prepared_payload, $pr_id, $pro_type, $shop_name );
			if ( isset( $this->product_payload['has_error'] ) ) {
				$notification['message'] = $this->product_payload['error'];
				continue;
			}

			/**
			 * **************************
			 * 	UPLOAD PORDUCT TO ETSY 
			 * **************************
			 * 
			 */
			self::ced_etsy_upload_product_woo_to_etsy( $pr_id, $shop_name );
			$response = $this->upload_response;
			if ( isset( $response['listing_id'] ) ) {
				$this->l_id = isset( $response['listing_id'] ) ? $response['listing_id'] : '';
				$upload_image_to_etsy = $this->ced_etsy_prep_and_upload_img( $pr_id, $shop_name, $this->l_id );
				update_post_meta( $pr_id, '_ced_etsy_wcfm_listing_id_'. $shop_name, $this->l_id );
				update_post_meta( $pr_id, '_ced_etsy_wcfm_url_' . $shop_name, $response['url'] );
				update_post_meta( $pr_id, '_ced_etsy_pro_state_' . $shop_name, $response['state'] );
				update_post_meta( $pr_id, '_ced_etsy_listing_data_' . $shop_name, json_encode( $response ) );
				$notification['status']  = 200;
				$notification['message'] = 'Product uploaded successfully';
				if ('simple' == $pro_type ) {
					$update_attributes = $this->ced_etsy_update_inventory_to_etsy( $pr_id, $shop_name );
					if ( $this->is_downloadable ) {
						$upload_image_to_etsy = $this->ced_etsy_upload_downloadable( $pr_id, $shop_name, $this->l_id, $payload->downloadable_data );
					}
					goto ced_update_state;
				}
				if ( 'variable' == $pro_type ) {
					$variation_payload = $this->ced_variation_details( $pr_id, $shop_name );
					$offerings_payload = apply_filters( 'ced_etsy_product_variation_offerings', $variation_payload, $pr_id, $pro_type, $shop_name );
					$var_response      = $this->ced_etsy_update_variation_inventory_to_etsy( $pr_id, $this->l_id, $shop_name, $offerings_payload, false );
					if ( ! isset( $var_response['products'][0]['product_id'] ) ) {
						$this->data['variation'] = $offerings_payload;
						$response                = $var_response;
						$notification['status']  = 400;
						$notification['message'] = isset( $var_response['error'] ) ? $var_response['error'] : '';
						$this->ced_etsy_delete_product( array( $pr_id ), $shop_name, false );
						continue;
					}
				}
				ced_update_state:
				if ( 'active' == $this->get_state() ) {
					$activate = $this->ced_etsy_activate_product( $pr_id, $shop_name );
				}
			} elseif ( isset( $response['error'] ) ) {
				$notification['message'] = $response['error'];
			} else {
				$notification['message'] = json_encode( $response );
			}
		}
		return $notification;
	}

	 /**
	  * ***********************************
	  * UPDATE LISTING OFFERINGS TO ETSY
	  * ***********************************
	  *
	  * @since 1.0.0
	  *
	  * @param array  $product_ids Product lsting  ids.
	  * @param string $shop_name Active shopName.
	  *
	  * @return $response ,
	  */
	public function ced_etsy_update_variation_inventory_to_etsy( $product_id = '', $listing_id = '', $shop_name = '', $offerings_payload = '', $is_sync = false ) {
		/** Refresh token
		 *
		 * @since 2.0.0
		 */
		$vendor_id = ced_etsy_wcfm_get_vendor_id();
		do_action( 'ced_etsy_wcfm_refresh_token', $shop_name, $vendor_id );
		$response = Ced_Etsy_WCFM_API_Request($shop_name)->put( "application/listings/{$listing_id}/inventory", $offerings_payload, $shop_name );
		return $response;
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

	private function ced_etsy_upload_product_woo_to_etsy( $product_id, $shop_name ) {
		/**
		 * Refresh token
		 *
		 * @since 2.0.0
		 */
		$vendor_id = ced_etsy_wcfm_get_vendor_id();
		do_action( 'ced_etsy_wcfm_refresh_token', $shop_name, $vendor_id );
		$shop_id  = ced_etsy_wcfm_get_shop_id( $shop_name, $vendor_id );		
		$response = Ced_Etsy_WCFM_API_Request( $shop_name )->post( "application/shops/{$shop_id}/listings", $this->product_payload, $shop_name );	
		/**
		 * ************************************************
		 *  Update post meta after uploading the Products.
		 * ************************************************
		 *
		 * @since 2.0.8
		 */
		if ( isset( $response['listing_id'] ) ) {
			update_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_'. $shop_name, $response['listing_id'] );
			update_post_meta( $product_id, '_ced_etsy_pro_state_' . $shop_name, $response['state'] );
			update_post_meta( $product_id, '_ced_etsy_wcfm_url_' . $shop_name, $response['url'] );
		}

		if ( isset( $response['error'] ) ) {
			$error                 = array();
			$error['error']        = isset( $response['error'] ) ? $response['error'] : 'some error occured';
			$this->upload_response = $error;
		} else {
			$this->upload_response = $response;
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
	public function ced_etsy_prep_and_upload_img( $p_id = '', $shop_name = '', $listing_id = '' ) {
		if ( empty( $p_id ) || empty( $shop_name ) ) {
			return;
		}
		$this->ced_product = isset( $this->ced_product ) ? $this->ced_product : wc_get_product( $p_id );
		$prnt_img_id       = get_post_thumbnail_id( $p_id );
		if ( WC()->version < '3.0.0' ) {
			$attachment_ids = $this->ced_product->get_gallery_attachment_ids();
		} else {
			$attachment_ids = $this->ced_product->get_gallery_image_ids();
		}
		$previous_thum_ids = get_post_meta( $p_id, 'ced_etsy_wcfm_previous_thumb_ids' . $listing_id, true );
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
				$image_result = self::ced_etsy_image_upload_to_etsy( $listing_id, $p_id, $attachment_id, $shop_name );
				if ( isset( $image_result['listing_image_id'] ) ) {
					$previous_thum_ids[ $attachment_id ] = $image_result['listing_image_id'];
					update_post_meta( $p_id, 'ced_etsy_wcfm_previous_thumb_ids' . $listing_id, $previous_thum_ids );
				}
			}
		}

		/*
		|===================
		| UPLOAD MAIN IMAGE
		|===================
		*/
		if ( ! isset( $previous_thum_ids[ $prnt_img_id ] ) ) {
			$image_result = self::ced_etsy_image_upload_to_etsy( $listing_id, $p_id, $prnt_img_id, $shop_name );
			if ( isset( $image_result['listing_image_id'] ) ) {
				$previous_thum_ids[ $prnt_img_id ] = $image_result['listing_image_id'];
				update_post_meta( $p_id, 'ced_etsy_wcfm_previous_thumb_ids' . $listing_id, $previous_thum_ids );
			}
		}
	}



	public function ced_etsy_update_inventory_to_etsy( $product_ids = array(), $shop_name = '', $is_sync = false ) {

		if ( ! is_array( $product_ids ) ) {
			$product_ids = array( $product_ids );
		}
		$notification = array();
		$shop_name    = empty( $shop_name ) ? $this->shop_name : $shop_name;
		$product_ids  = empty( $product_ids ) ? $this->product_id : $product_ids;
		foreach ( $product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( empty( $this->listing_id ) ) {
				$this->listing_id = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_'. $shop_name, true );
			}
			if ( 'variable' == $_product->get_type() ) {
				$offerings_payload = $this->ced_variation_details( $product_id, $shop_name );
				$response          = $this->ced_etsy_update_variation_inventory_to_etsy( $product_id, $this->listing_id, $shop_name, $offerings_payload, false );
			} else {
				$this->get_formatted_data( $product_id, $shop_name );
				$sku      = get_post_meta( $product_id, '_sku', true );
				$response = Ced_Etsy_WCFM_API_Request( $shop_name )->get( "application/listings/{$this->listing_id}/inventory", $shop_name );
				if ( isset( $response['products'][0] ) ) {
					if ( (int) $this->get_quantity() <= 0 ) {
						$response = $this->ced_etsy_deactivate_product( $product_id, $shop_name );
						update_post_meta( $product_id, '_ced_etsy_listing_data_' . $shop_name, json_encode( $response ) );
						$input_payload = array( $this->listing_id );
					} else {
						$product_payload = $response;
						$product_payload['products'][0]['offerings'][0]['quantity'] = (int) $this->get_quantity();
						$product_payload['products'][0]['offerings'][0]['price']    = (float) $this->get_price();
						$product_payload['products'][0]['sku']                      = (string) $sku;
						unset( $product_payload['products'][0]['is_deleted'] );
						unset( $product_payload['products'][0]['product_id'] );
						unset( $product_payload['products'][0]['offerings'][0]['is_deleted'] );
						unset( $product_payload['products'][0]['offerings'][0]['offering_id'] );
						/** Refresh token
						 *
						 * @since 2.0.0
						 */
						do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
						$input_payload = $product_payload;
						$response      = Ced_Etsy_WCFM_API_Request( $shop_name )->put( "application/listings/{$this->listing_id}/inventory", $product_payload, $shop_name );
					}
				}
			}
			if ( isset( $response['products'][0] ) ) {
				$notification['status']  = 200;
				$notification['message'] = 'Product inventory updated successfully';
			} elseif ( isset( $response['listing_id'] ) ) {
				$notification['status']  = 200;
				$notification['message'] = 'Product deactivated on etsy';
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

	public function ced_etsy_image_upload_to_etsy( $l_id, $pr_id, $img_id, $shop_name ) {
		$image_path = get_attached_file( $img_id );
		$image_name = basename( $image_path );
		/**
		 * Refresh token
		 *
		 * @since 2.0.0
		 */
		do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
		$shop_id  = ced_etsy_wcfm_get_shop_id( $shop_name );
		$response = Ced_Etsy_WCFM_API_Request($shop_name)->ced_etsy_upload_image_and_file( 'image', "application/shops/{$shop_id}/listings/{$l_id}/images", $image_path, $image_name, $shop_name );
		return $this->ced_etsy_parse_response( $response );

	}

	/**
	 * **********************
	 * Paser JSON into array
	 * **********************
	 *
	 * @since 1.0.0
	 *
	 * @param int    $json Json string.
	 *
	 * @return String
	 */
	public function ced_etsy_parse_response( $json ) {
		return json_decode( $json, true );
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
	public function ced_etsy_upload_downloadable( $p_id = '', $shop_name = '', $l_id = '', $downloadable_data = array() ) {
		$listing_files_uploaded = get_post_meta( $p_id, '_ced_etsy_product_files_uploaded' . $l_id, true );
		if ( empty( $listing_files_uploaded ) ) {
			$listing_files_uploaded = array();
		}
		if ( ! empty( $downloadable_data ) ) {
			$count = 0;
			foreach ( $downloadable_data as $data ) {
				if ( $count > 4 ) {
					break;
				}
				$file_data = $data->get_data();
				if ( isset( $listing_files_uploaded[ $file_data['id'] ] ) ) {
					continue;
				}
				try {
					$file_path = str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_data['file'] );
					/** Refresh token
					 *
					 * @since 2.0.0
					 */
					do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
					$shop_id  = ced_etsy_wcfm_get_shop_id( $shop_name );
					$response = Ced_Etsy_WCFM_API_Request($shop_name)->ced_etsy_upload_image_and_file( 'file', "application/shops/{$shop_id}/listings/{$l_id}/files", $file_path, $file_data['name'], $shop_name );
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
	 * Delete Listing from Etsy.
	 *
	 * @return array
	 */
	public function ced_etsy_delete_product( $product_ids = array(), $shop_name = '', $log = true ) {
		if ( ! is_array( $product_ids ) ) {
			$product_ids = array( $product_ids );
		}
		$notification = array();
		foreach ( $product_ids as $product_id ) {
			$product    = wc_get_product( $product_id );
			$listing_id = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_'. $shop_name, true );
			if ( $listing_id ) {
				/** Refresh token
				 *
				 * @since 2.0.0
				 */
				do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
				$action   = "application/listings/{$listing_id}";
				$response = Ced_Etsy_WCFM_API_Request($shop_name)->delete( $action, $shop_name );
				if ( ! isset( $response['error'] ) ) {
					delete_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' .$vendor_id. $shop_name );
					delete_post_meta( $product_id, '_ced_etsy_pro_state_' . $shop_name );
					delete_post_meta( $product_id, '_ced_etsy_wcfm_url_' . $shop_name );
					delete_post_meta( $product_id, '_ced_etsy_product_files_uploaded' . $listing_id );
					delete_post_meta( $product_id, 'ced_etsy_wcfm_previous_thumb_ids' . $listing_id );
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
	  * ***********************
	  * UPDATE PRODUCT TO ETSY
	  * ***********************
	  *
	  * @since 1.0.0
	  *
	  * @param array  $product_ids Product lsting  ids.
	  * @param string $shop_name Active shopName.
	  *
	  * @return $response ,
	  */
	public function ced_etsy_update_product_on_etsy( $product_ids = array(), $shop_name = '' ) {

		if ( ! is_array( $product_ids ) ) {
			$product_ids = array( $product_ids );
		}
		$notification = array();
		$shop_name    = empty( $shop_name ) ? $this->shop_name : $shop_name;
		$product_ids  = empty( $product_ids ) ? $this->product_id : $product_ids;
		foreach ( $product_ids as $product_id ) {
			if ( empty( $this->listing_id ) ) {
				$this->listing_id = get_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, true );
			}
			$arguements = $this->get_formatted_data( $product_id, $shop_name );
			if ( isset( $arguements['has_error'] ) ) {
						$notification['status']  = 400;
						$notification['message'] = $arguements['error'];
			} else {
				$arguements['state'] = $this->get_state();
				$shop_id             = ced_etsy_wcfm_get_shop_id( $shop_name );
				$action              = "application/shops/{$shop_id}/listings/{$this->listing_id}";
				/** Refresh token
				 *
				 * @since 2.0.0
				 */
				do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
				$response = Ced_Etsy_WCFM_API_Request( $shop_name )->put( $action, $arguements, $shop_name );
				if ( isset( $response['listing_id'] ) ) {
					update_post_meta( $product_id, '_ced_etsy_listing_data_' . $shop_name, json_encode( $response ) );
					$notification['status']  = 200;
					$notification['message'] = 'Product updated successfully';
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
	 * **********************************************
	 * Get Woocommerce Product Data, Type, Parent ID.
	 * **********************************************
	 *
	 * @since 1.0.0
	 *
	 * @param string $pr_id Product lsting  ids.
	 * @param string $shop_name Active shopName.
	 *
	 * @link  http://www.cedcommerce.com/
	 * @return string Woo product type.
	 */

	public function ced_pro_type( $pr_id = '' ) {
		if ( empty( $pr_id ) ) {
			$pr_id = $this->product_id;
		}
		$wc_product = wc_get_product( $pr_id );
		if ( is_bool( $wc_product ) ) {
			return false;
		}
		$this->prod_obj     = $wc_product;
		$this->product      = $wc_product->get_data();
		$this->product_type = $wc_product->get_type();
		$this->parent_id    = 0;
		if ( 'variation' == $this->product_type ) {
			$this->parent_id = $wc_product->get_parent_id();
		}
		return $this->product_type;
	}

	/**
	 * *****************************************
	 * GET ASSIGNED PRODUCT DATA FROM PROFILES
	 * *****************************************
	 *
	 * @since 1.0.0
	 *
	 * @param array  $product_id Product lsting  ids.
	 * @param string $shop_name Active Etsy shopName.
	 *
	 * @link  http://www.cedcommerce.com/
	 * @return $profile_data assigined profile data .
	 */

	public function ced_etsy_check_profile( $product_id = '', $shop_name = '' ) {
		if ( 'variation' == $this->ced_pro_type( $product_id ) ) {
			$product_id = $this->parent_id;
		}

		$wc_product  = wc_get_product( $product_id );
		$data        = $wc_product->get_data();
		$category_id = isset( $data['category_ids'] ) ? $data['category_ids'] : array();
		foreach ( $category_id as $key => $value ) {
			$profile_id = get_term_meta( $value, 'ced_etsy_wcfm_profile_id_' . $shop_name, true );

			if ( ! empty( $profile_id ) ) {
				break;

			}
		}
		if ( isset( $profile_id ) && ! empty( $profile_id ) ) {
			$this->profile_id              = $profile_id;
			$this->is_profile_assing       = true;
			$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details' . $shop_name, array() );
			$ced_etsy_wcfm_profile_data    = isset( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ] ) ? $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ] : array();
			$profile_data                  = json_decode( $ced_etsy_wcfm_profile_data['profile_data'], true );
		} else {
			$this->is_profile_assing = false;
			return 'false';
		}
		$this->profile_data = isset( $profile_data ) ? $profile_data : '';
		return $this->profile_data;
	}


	/**
	 * **********************************************
	 * GET FORMATTED DATA FOR UPLOAD/UPDATE PRODUCTS
	 * **********************************************
	 *
	 * @since 1.0.0
	 *
	 * @param int    $product_id Woo Product ids.
	 * @param string $shop_name Active etsy shop name.
	 *
	 * @return $arguments all possible arguments .
	 */
	public function get_formatted_data( $product_id = '', $shop_name = '' ) {
		$this->ced_etsy_check_profile( $product_id, $shop_name );
		$this->product_id       = $product_id;
		$this->pro_data         = array();
		$this->is_downloadable  = isset( $this->product['downloadable'] ) ? $this->product['downloadable'] : 0;
		if ( $this->is_downloadable ) {
			$this->downloadable_data = isset( $this->product['downloads'] ) ? $this->product['downloads'] : array();
		}
	 	ced_etsy_wcfm_include_file( CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-product-fields.php' );
		$product_field_instance = Ced_Etsy_Wcfm_Product_Fields::get_instance();
		$etsy_data_field        = $product_field_instance->get_etsy_wcfm_custom_products_fields();
		foreach ( $etsy_data_field as $section_attributes ) {
			$meta_key = $section_attributes['id'];
			$pro_val  = get_post_meta( $product_id, $meta_key, true );// getting info from product level
			if ( '' == $pro_val ) {
				$pro_val = $this->fetch_meta_value( $product_id, $meta_key );// getting info from profile level
			}
			if ( '' == $pro_val ) {
				$pro_val = isset( $this->ced_wcfm_global_settings['product_data'][ $meta_key ]['default'] ) ? $this->ced_wcfm_global_settings['product_data'][ $meta_key ]['default'] : '';// getting info from global level
			}
			if ( '' == $pro_val ) {
				$metakey = isset( $this->ced_wcfm_global_settings['product_data'][ $meta_key ]['metakey'] ) ? $this->ced_wcfm_global_settings['product_data'][ $meta_key ]['metakey'] : '';// getting info from global level
				if ( ! empty( $metakey ) ) {
					$pro_val = $this->fetch_meta_value( $product_id, $metakey );// getting info from global level
				}
			}
			$this->pro_data[ trim( str_replace( '_ced_etsy_wcfm_', ' ', $meta_key ) ) ] = ! empty( $pro_val ) ? $pro_val : '';

		}
		if ( ! $this->is_profile_assing ) {
			$this->error['has_error'] = true;
			$this->error['error']     = 'Category not mapped';
			return $this->error;
		}

		if ( ! $this->prepare_required_fields() ) {
			return $this->error;
		}

		$this->prepare_rec_opt_ship_per_fields();
		return $this->product_arguements;
	}


	public function prepare_required_fields() {
		$required_fields = array(
			'quantity',
			'title',
			'description',
			'price',
			'who_made',
			'when_made',
			'taxonomy_id',
			'is_supply',
		);
		if(!$this->is_downloadable){
			array_push( $required_fields, "shipping_profile_id" );
		}
		$valid     = true;
		$error_msg = '';
		foreach ( $required_fields as $index ) {
			if ( method_exists( $this, 'get_' . $index ) ) {
				$info = call_user_func( array( $this, 'get_' . $index ) );
				if ( false !== $info ) {
					$this->product_arguements[ $index ] = $info;
				} else {
					$valid      = false;
					$error_msg .= '[ ' . ucwords( $index ) . ' is required but missing from product information ]';
				}
			}
		}

		if ( ! $valid ) {
			$this->error['has_error'] = true;
			$this->error['error']     = $error_msg;
		}

		return $valid;
	}

	public function prepare_rec_opt_ship_per_fields() {
		$required_fields = array(
			'materials',
			'shop_section_id',
			'tags',
			'styles',
			'production_partner_ids',
			'processing_min',
			'processing_max',
			'is_personalizable',
			'personalization_is_required',
			'personalization_char_count_max',
			'personalization_instructions',
			'is_customizable',
			'is_taxable',
		);

		if ( $this->get_shipping_profile_id() && !$this->is_downloadable ) {
			$required_fields = array_merge(
				$required_fields,
				array(
					'item_weight',
					'item_length',
					'item_width',
					'item_height',
					'item_weight_unit',
					'item_dimensions_unit',
				)
			);
		}elseif($this->is_downloadable){
			$required_fields = array_merge(
				$required_fields,
				array('type')
			);
		}

		foreach ( $required_fields as $index ) {
			if ( method_exists( $this, 'get_' . $index ) ) {
				$info = call_user_func( array( $this, 'get_' . $index ) );
				if ( false !== $info ) {
					$this->product_arguements[ $index ] = $info;
				}
			}
		}
	}

	public function get_type(){
		if ($this->is_downloadable) {
			return 'download';
		}
		return 'physical';
	}

	public function get_quantity() {
		$quantity = isset( $this->pro_data['stock'] ) ? $this->pro_data['stock'] : '';
		if ( '' === $quantity ) {
			$quantity = get_post_meta( $this->product_id, '_stock', true );
			if ( 'variable' == $this->product_type ) {
				$quantity = 1;
			}
			$manage_stock = get_post_meta( $this->product_id, '_manage_stock', true );
			$stock_status = get_post_meta( $this->product_id, '_stock_status', true );
			if ( 'instock' == $stock_status && 'no' == $manage_stock ) {
				$quantity = isset( $this->pro_data['default_stock'] ) ? $this->pro_data['default_stock'] : 1;
			}

			if ( $quantity > 999 ) {
				$quantity = 999;
			}

			if ( $quantity <= 0 ) {
				$quantity = 0;
			}
		}
		/** Alter etsy product qty
				 *
				 * @since 2.0.0
				 */
		return ( '' === $quantity ) ? false : apply_filters( 'ced_etsy_quantity', (int) $quantity, $this->product_id, $this->shop_name );
	}

	public function get_title() {
		$title = isset( $this->pro_data['title'] ) ? $this->pro_data['title'] : '';
		$title = ! empty( $title ) ? $title : $this->product['name'];
		// $title = isset( $this->pro_data['title_pre'] ) ? $this->pro_data['title_pre'] : '' . ' ' . $title . ' ' . isset( $this->pro_data['title_post'] ) ? $this->pro_data['title_post'] : '';
		if ( '' != trim( $title ) ) {
			/** Alter etsy product title
				 *
				 * @since 2.0.0
				 */
			return apply_filters( 'ced_etsy_title', (string) trim( $title ), $this->product_id, $this->shop_name );
		}
		return false;
	}

	public function get_description() {
		$description = isset( $this->pro_data['description'] ) ? $this->pro_data['description'] : '';
		$description = ! empty( $description ) ? $description : $this->product['description'];
		if ( '' != trim( strip_tags( $description ) ) ) {
			/** Alter etsy product description
				 *
				 * @since 2.0.0
				 */
			return apply_filters( 'ced_etsy_description', (string) trim( strip_tags( html_entity_decode( $description ) ) ), $this->product_id, $this->shop_name );
		}
		return false;

	}

	public function get_price() {
		$price = isset( $this->pro_data['price'] ) ? $this->pro_data['price'] : '';

		if ( 'variable' == $this->product_type ) {
			$variations = $this->prod_obj->get_available_variations();
			if ( isset( $variations['0']['display_regular_price'] ) ) {
				$price = $variations['0']['display_regular_price'];
			}
		}

		$price        = ! empty( $price ) ? $price : $this->product['price'];
		$markup_type  = $this->pro_data['markup_type'];
		$markup_value = isset( $this->pro_data['markup_value'] ) ? (float) $this->pro_data['markup_value'] : 0.00;
		if ( ! empty( $markup_type ) && '' !== $markup_value ) {
			$price = ( 'Fixed_Increased' == $markup_type ) ? ( (float) $price + $markup_value ) : ( (float) $price + ( ( $markup_value / 100 ) * (float) $price ) );
		}
		
		if( 'bundle' == $this->product_type ){
		    return apply_filters( 'ced_etsy_price', (float) round( $price, 2 ), $this->product_id, $this->product_type, $this->shop_name );
		}
		
		if ( '' != (float) round( $price, 2 ) ) {
			/** Alter etsy product price
			 *
			 * @since 2.0.0
			 */
			return apply_filters( 'ced_etsy_price', (float) round( $price, 2 ), $this->product_id, $this->shop_name );
		}
		return false;

	}

	public function get_who_made() {
		$who_made = ! empty( $this->pro_data['who_made'] ) ? $this->pro_data['who_made'] : 'i_did';
		return (string) $who_made;
	}

	public function get_when_made() {
		$when_made = ! empty( $this->pro_data['when_made'] ) ? $this->pro_data['when_made'] : '2020_2022';
		return (string) $when_made;
	}

	public function get_taxonomy_id() {
		$taxonomy_id = $this->fetch_meta_value( $this->product_id, '_umb_etsy_wcfm_category' );
		if ( (int) $taxonomy_id ) {
			return (int) $taxonomy_id;
		}
		return false;
	}

	public function get_shipping_profile_id() {
		$shipping_profile = ! empty( $this->pro_data['shipping_profile'] ) ? $this->pro_data['shipping_profile'] : 0;
		if ( doubleval( $shipping_profile ) ) {
			return doubleval( $shipping_profile );
		}
		return false;
	}

	public function get_is_supply() {
		$supply = isset( $this->pro_data['product_supply'] ) ? $this->pro_data['product_supply'] : '';
		$product_supply = ( 'true' == $supply ) ? 1 : 0;
		return (int) $product_supply;
	}


	public function get_materials() {
		$get_materials = ! empty( $this->pro_data['materials'] ) ? $this->pro_data['materials'] : array();
		$material_info = array();
		if ( ! empty( $get_materials ) ) {
			$explode_materials = array_filter( explode( ',', $get_materials ) );
			foreach ( $explode_materials as $key_tags => $material ) {
				$material = str_replace( ' ', '-', $material );
				$material = preg_replace( '/[^A-Za-z0-9\-]/', '', $material );
				$material = str_replace( '-', ' ', $material );
				if ( $key_tags <= 12 && strlen( $material ) <= 20 ) {
					$material_info[] = $material;
				}
			}
			$material_info = array_filter( array_values( array_unique( $material_info ) ) );
			if ( ! empty( $material_info ) ) {
				return $material_info;
			}
		}
		return false;
	}

	public function get_shop_section_id() {
		$shop_section = ! empty( $this->pro_data['shop_section'] ) ? $this->pro_data['shop_section'] : 0;
		if ( (int) $shop_section ) {
			return (int) $shop_section;
		}
		return false;
	}

	public function get_tags() {
		$get_tags = ! empty( $this->pro_data['tags'] ) ? $this->pro_data['tags'] : array();
		$tag_info = array();
		if ( ! empty( $get_tags ) ) {
			$explode_materials = array_filter( explode( ',', $get_tags ) );
			foreach ( $explode_materials as $key_tags => $tag_name ) {
				$tag_name = str_replace( ' ', '-', $tag_name );
				$tag_name = preg_replace( '/[^A-Za-z0-9\-]/', '', $tag_name );
				$tag_name = str_replace( '-', ' ', $tag_name );
				if ( $key_tags <= 12 && strlen( $tag_name ) <= 20 ) {
					$tag_info[] = $tag_name;
				}
			}
			$tag_info = array_filter( array_values( array_unique( $tag_info ) ) );
			if ( ! empty( $tag_info ) ) {
				return $tag_info;
			}
		}
		return false;
	}

	public function get_styles() {
		$get_styles = ! empty( $this->pro_data['styles'] ) ? $this->pro_data['styles'] : array();
		$style_info = array();
		if ( ! empty( $get_styles ) ) {
			$explode_materials = array_filter( explode( ',', $get_styles ) );
			foreach ( $explode_materials as $key_tags => $style ) {
				$style = str_replace( ' ', '-', $style );
				$style = preg_replace( '/[^A-Za-z0-9\-]/', '', $style );
				$style = str_replace( '-', ' ', $style );
				if ( $key_tags <= 2 && strlen( $style ) <= 20 ) {
					$style_info[] = $style;
				}
			}
			$style_info = array_filter( array_values( array_unique( $style_info ) ) );
			if ( ! empty( $style_info ) ) {
				return $style_info;
			}
		}
		return false;
	}

	public function get_production_partner_ids() {
		$shipping_profile = ! empty( $this->pro_data['production_partners'] ) ? $this->pro_data['production_partners'] : array();
		if ( ! empty( $shipping_profile ) ) {
			return $shipping_profile;
		}
		return false;
	}

	public function get_processing_min() {
		$processing_min = ! empty( $this->pro_data['processing_min'] ) ? (int) $this->pro_data['processing_min'] : 1;
		return $processing_min;
	}

	public function get_processing_max() {
		$processing_max = ! empty( $this->pro_data['processing_max'] ) ? (int) $this->pro_data['processing_max'] : 3;
		return $processing_max;
	}

	public function get_item_weight() {
		$item_weight = ! empty( $this->pro_data['item_weight'] ) ? $this->pro_data['item_weight'] : get_post_meta( $this->product_id, '_weight', true );
		if ( ! empty( $item_weight ) ) {
			return (float) $item_weight;
		}
		return false;
	}

	public function get_item_length() {
		$item_length = ! empty( $this->pro_data['item_length'] ) ? $this->pro_data['item_length'] : get_post_meta( $this->product_id, '_length', true );
		if ( ! empty( $item_length ) ) {
			return (float) $item_length;
		}
		return false;
	}

	public function get_item_width() {
		$item_width = ! empty( $this->pro_data['item_width'] ) ? $this->pro_data['item_width'] : get_post_meta( $this->product_id, '_width', true );
		if ( ! empty( $item_width ) ) {
			return (float) $item_width;
		}
		return false;
	}

	public function get_item_height() {
		$item_height = ! empty( $this->pro_data['item_height'] ) ? $this->pro_data['item_height'] : get_post_meta( $this->product_id, '_height', true );
		if ( ! empty( $item_height ) ) {
			return (float) $item_height;
		}
		return false;
	}

	public function get_item_weight_unit() {
		$item_weight_unit = ! empty( $this->pro_data['item_weight_unit'] ) ? $this->pro_data['item_weight_unit'] : get_option( 'woocommerce_weight_unit', '' );
		if ( ! empty( $item_weight_unit ) ) {
			return (string) $item_weight_unit;
		}
		return false;
	}

	public function get_item_dimensions_unit() {
		$item_dimensions_unit = ! empty( $this->pro_data['item_dimensions_unit'] ) ? $this->pro_data['item_dimensions_unit'] : get_option( 'woocommerce_dimension_unit', '' );
		if ( ! empty( $item_dimensions_unit ) ) {
			return (string) $item_dimensions_unit;
		}
		return false;
	}

	public function get_is_personalizable() {
		$ced_etsy_prs = isset( $this->pro_data['is_personalizable'] ) ? $this->pro_data['is_personalizable'] : 0;
		$is_personalizable = ( 'true' == $ced_etsy_prs ) ? 1 : 0;
		return (int) $is_personalizable;
	}

	public function get_personalization_is_required() {
		$prsn_rqd = isset( $this->pro_data['personalization_is_required'] ) ? $this->pro_data['personalization_is_required'] : 0;
		$personalization_is_required = ( 'true' ==  $prsn_rqd ) ? 1 : 0;
		return (int) $personalization_is_required;
	}

	public function get_personalization_char_count_max() {
		$personalization_char_count_max = ! empty( $this->pro_data['personalization_char_count_max'] ) ? $this->pro_data['personalization_char_count_max'] : false;
		if ( (int) $personalization_char_count_max ) {
			return (int) $personalization_char_count_max;
		}
		return false;
	}

	public function get_personalization_instructions() {
		$personalization_instructions = ! empty( $this->pro_data['personalization_instructions'] ) ? $this->pro_data['personalization_instructions'] : '';
		if ( ! empty( $personalization_instructions ) ) {
			return (string) $personalization_instructions;
		}
		return false;
	}

	public function get_is_customizable() {
		$is_cstmzble = isset( $this->pro_data['is_customizable'] ) ? $this->pro_data['is_customizable'] : 0;
		$is_customizable = ( 'true' == $is_cstmzble ) ? 1 : 0;
		return (int) $is_customizable;
	}
	public function get_is_taxable() {
		$is_txble = isset( $this->pro_data['is_taxable'] ) ? $this->pro_data['is_taxable'] : 0;
		$is_taxable = ( 'true' == $is_txble ) ? 1 : 0;
		return (int) $is_taxable;
	}

	public function get_state() {
		$product_list_type = ! empty( $this->ced_wcfm_global_settings['product_data']['_ced_etsy_product_list_type']['default'] ) ? $this->ced_wcfm_global_settings['product_data']['_ced_etsy_product_list_type']['default'] : 'draft';
		return (string) $product_list_type;
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
	 * @return $reponse
	 */

	public function ced_variation_details( $product_id = '', $shop_name = '', $is_sync = false ) {
		$property_ids = array();
		$product      = wc_get_product( $product_id );
		$variations   = $product->get_available_variations();
		$attributes   = array();
		$parent_sku   = get_post_meta( $product_id, '_sku', true );
		$parent_attributes = $product->get_variation_attributes();
		$possible_combinations = array_values( wc_array_cartesian(( $parent_attributes )) );
		$no_property_to_use = count($parent_attributes);
		$com_to_be_prepared    = array();
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
			$var_array            = array();
			$_count               = 0;
			$var_att_array        = '';
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
				if(!$attribute_one_mapped) {
					$property_name_one = ucwords( str_replace( array( 'attribute_pa_', 'attribute_' ), array( '', '' ), $property_name ) );
					$attribute_one_mapped = true;
				}

				if ( $count > 0 ) {
					if(!$attribute_two_mapped) {
						$property_name_two = ucwords( str_replace( array( 'attribute_pa_', 'attribute_' ), array( '', '' ), $property_name ) );
					}
					$property_id = 514;
					$attribute_two_mapped = true;
				}

				$property_values[] = array(
					'property_id'   => (int) $property_id,
					'value_ids'     => array( $property_id ),
					'property_name' => ucwords( str_replace( array( 'attribute_pa_', 'attribute_' ), array( '', '' ), $property_name ) ),
					'values'        => array( ucwords( strtolower( $property_value ) ) ),

				);

				$var_att_array .= $property_value . '~';
				$count++;
				$property_ids[] = $property_id;
			}
			if ( isset( $com_to_be_prepared[ strtolower( $var_att_array ) ] ) ) {
				unset( $com_to_be_prepared[ strtolower( $var_att_array ) ] );
			}
			$this->get_formatted_data( $var_id, $shop_name );
			$price        = $this->get_price();
			$var_quantity = $this->get_quantity();
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
		foreach ( $com_to_be_prepared as $combination ) {
			$property_values_remaining = array_values( array_filter( explode( '~', $combination ) ) );
			if ( isset( $property_values_remaining[1] ) ) {
				$offer_info[] = array(

					'sku'             => '',
					'property_values' => array(
						array(
							'property_id'   => (int) 513,
							'value_ids'     => array( 513 ),
							'property_name' => $property_name_one,
							'values'        => array(
								isset( $property_values_remaining[0] ) ? ucwords( strtolower( $property_values_remaining[0] ) ) : '',
							),
						),
						array(
							'property_id'   => (int) 514,
							'value_ids'     => array( 514 ),
							'property_name' => $property_name_two,
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
							'property_name' => $property_name_one,
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

	private function fetch_meta_value( $product_id, $metaKey, $is_variation = false ) {
		if ( isset( $this->is_profile_assing ) && $this->is_profile_assing ) {
			$_product = wc_get_product( $product_id );
			if ( ! is_object( $_product ) ) {
				return false;
			}

			if ( '_woocommerce_title' == $metaKey ) {
				$product = wc_get_product( $product_id );
				return $product->get_title();
			}if ( '_woocommerce_short_description' == $metaKey ) {
				$product = wc_get_product( $product_id );
				if ( $product->get_type() == 'variation' ) {
					$_parent_obj = wc_get_product( $product->get_parent_id() );
					return $_parent_obj->get_short_description();
				}
				return $product->get_short_description();

			}if ( '_woocommerce_description' == $metaKey ) {
				$product = wc_get_product( $product_id );
				if ( $product->get_type() == 'variation' ) {
					$_parent_obj = wc_get_product( $product->get_parent_id() );
					return $_parent_obj->get_description();
				}
				return $product->get_description();
			}

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

					if ( '_woocommerce_title' == $tempProfileData['metakey'] ) {
						$product = wc_get_product( $product_id );
						return $product->get_title();
					}if ( '_woocommerce_short_description' == $tempProfileData['metakey'] ) {
						$product = wc_get_product( $product_id );
						if ( $product->get_type() == 'variation' ) {
							$_parent_obj = wc_get_product( $product->get_parent_id() );
							return $_parent_obj->get_short_description();
						}
						return $product->get_short_description();

					}if ( '_woocommerce_description' == $tempProfileData['metakey'] ) {
						$product = wc_get_product( $product_id );
						if ( $product->get_type() == 'variation' ) {
							$_parent_obj = wc_get_product( $product->get_parent_id() );
							return $_parent_obj->get_description();
						}
						return $product->get_description();
					}

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
										$product_terms = get_the_terms( $product_idresponse, 'pa_' . $wooAttribute );
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