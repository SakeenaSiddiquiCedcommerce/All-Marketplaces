<?php

/**
 * Gettting order related data
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * Ced_Good_Market_Product
 *
 * @since 1.0.0
 * @param object $_instance Class instance.
 */
class Ced_Good_Market_Product {


	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */

	public static $_instance;

	/**
	 * Ced_Good_Market_Product Instance.
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
	 * Ced_Good_Market_Product construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependency();
	}

	/**
	 * Ced_Good_Market_Product loading dependency.
	 *
	 * @since 1.0.0
	 */
	public function load_dependency() {

	}

	/**
	 * Ced_Good_Market_Product ced_good_market_prepare_data.
	 *
	 * @since 1.0.0
	 * @param array $good_market_product_ids.
	 */
	public function ced_good_market_prepare_data( $product_id, $process_mode = 'CREATE' ) {

		if ( empty( $product_id ) ) {
			return;
		}
		$vari_sku = array();
		$_product = wc_get_product( $product_id );
		if ( is_object( $_product ) ) {
			$status   = get_post_status( $product_id );
			$category = $this->ced_get_item_category( $product_id );
			if ( ! $category ) {
				$primary_variant = 'test';
				$subCategory     = 'test';
				$type            = $_product->get_type();
				if ( 'variable' == $type ) {
					$product_data = $this->good_market_get_formatted_data(  $category, $subCategory, $product_id, $primary_variant ='' );
					if ( empty( $product_data ) ) {
						return;
					}
					$variations = $_product->get_children();
					if ( is_array( $variations ) && ! empty( $variations ) ) {
						$primary_variant = 'Yes';

						foreach ( $variations as $index => $variation_id ) {

							$vari_pro_data            = $this->good_market_get_variation_data( $variation_id );
							$product_variation_data[] = $vari_pro_data['data'];
							// $vari_sku[] =  $vari_pro_data['var_sku_data'][0];
							if ( ! empty( $product_variation_data[0]['config_attributes'] ) && is_array( $product_variation_data[0]['config_attributes'] ) ) {
								foreach ( $product_variation_data[0]['config_attributes'] as $val_config ) {
									$config_attributes[] = $val_config;
								}
							}
						}

						$data_product_send['query']     = 'mutation saveProduct($vendor_id: Int!,$product_data: String! , $hash_token: String ) {
							saveProduct(vendor_id: $vendor_id, product_data:$product_data , hash_token:$hash_token){
								success
								message
								product_id
								child_product {
									product_id
									sku
								}
							}
						}';
						$attribute_set_id               = get_option( $this->profile_id . 'attribute_set_id' );
						$pro_data_to_send['type']       = 'configurable';
						$pro_data_to_send['set']        = ! empty( $attribute_set_id ) ? $attribute_set_id : 22;
						$image_role                     = array(
							'image'        => 'image1',
							'small_image'  => 'image1',
							'thumbnail'    => 'image1',
							'swatch_image' => '',
						);
						$pro_data_to_send['image_role'] = json_encode( $image_role );
						$vpro_data_pd                   = json_encode( $product_data );
						$saved_product_id               = get_post_meta( $product_id, 'saved_good_market_product_id', 1 );

						// If product already exists, $pro_data['id'] is to update the prdouct.
						if ( ! empty( $saved_product_id ) && is_numeric( $saved_product_id ) ) {

							$pro_data_to_send['id'] = $saved_product_id;
						}
						$pro_data_to_send['product']             = $vpro_data_pd;
						$pro_data_to_send['category_id']         = $this->profile_id;
						$pro_data_to_send['configurable_matrix'] = $product_variation_data;
						$pro_data_to_send['config_attributes']   = $config_attributes;
						// print_r($pro_data_to_send);die;
						$data_product_send['variables']['product_data']               = json_encode( $pro_data_to_send );
						$data_product_send['variables']['product_data']['image_role'] = '{"image":"","small_image":"","thumbnail":"image1","swatch_image":""}';
					}
				} else {
					$product_data = $this->good_market_get_formatted_data( $category, $subCategory, $product_id, $primary_variant ='');

					if ( $product_data ) {
						$data_product_send['query'] = 'mutation saveProduct($vendor_id: Int!,$product_data: String!, $hash_token: String) {
							saveProduct(vendor_id: $vendor_id, product_data:$product_data , hash_token:$hash_token){
								success
								message
								product_id
							}
						}';
						$pro_data                   = array();
						$pro_data['product']        = json_encode( $product_data );
						$pro_data['type']           = 'simple';
						$saved_product_id           = get_post_meta( $product_id, 'saved_good_market_product_id', 1 );

						// If product already exists, $pro_data['id'] is to update the prdouct.
						if ( ! empty( $saved_product_id ) && is_numeric( $saved_product_id ) ) {

							$pro_data['id'] = $saved_product_id;
						}
						$attribute_set_id                               = get_option( $this->profile_id . 'attribute_set_id' );
						$pro_data['set']                                = ! empty( $attribute_set_id ) ? $attribute_set_id : 22;
						$data_product_send['variables']['product_data'] = json_encode( $pro_data );
					}
				}
			}
		}

		if ( ! empty( $data_product_send ) ) {
			$data_product_send_all['data'] = $data_product_send;
			// $data_product_send_all['sku_array'] = $vari_sku;
			return $data_product_send_all;
		} else {

			return false;
		}
	}
	/**
	 * Ced_Good_Market_Product ced_good_market_prepare_bulk_data.
	 *
	 * @since 1.0.0
	 * @param array $good_market_product_ids.
	 */
	public function ced_good_market_prepare_bulk_data( $product_id, $process_mode = 'CREATE' ) {
		// $process_mode = 'CREATE';
		if ( empty( $product_id ) ) {
			return;
		}
		$vari_sku = array();
		$_product = wc_get_product( $product_id );
		if ( is_object( $_product ) ) {
			$status   = get_post_status( $product_id );
			$category = $this->ced_get_item_category( $product_id );
			if ( $category ) {
				$primary_variant = 'test';
				$subCategory     = 'test';
				$type            = $_product->get_type();
				if ( 'variable' == $type ) {
					$product_data = $this->good_market_get_formatted_data( $category, $subCategory, $product_id, $primary_variant =''  );
					if ( empty( $product_data ) ) {
						return;
					}
					$variations = $_product->get_children();
					if ( is_array( $variations ) && ! empty( $variations ) ) {
						$primary_variant = 'Yes';

						foreach ( $variations as $index => $variation_id ) {

							$vari_pro_data = $this->good_market_get_variation_data( $variation_id, $product_id );
							if ( 'not_matched' == $vari_pro_data ) {
								return 'not_matched';
							}
							$product_variation_data[]      = $vari_pro_data['data'];
							$varitiona_products_qunatities = 0;
							foreach ( $product_variation_data as $product_variation_data_key => $product_variation_data_val ) {
								$product_variation_data_val    = json_decode( $product_variation_data_val['sources'], true );
								$varitiona_products_qunatities = $varitiona_products_qunatities + (int) $product_variation_data_val[0]['quantity'];
							}
							if ( ! empty( $product_variation_data[0]['config_attributes'] ) && is_array( $product_variation_data[0]['config_attributes'] ) ) {
								foreach ( $product_variation_data[0]['config_attributes'] as $val_config ) {
									$config_attributes[] = $val_config;
								}
							}
						}
						$location_saved_data = get_option( 'location_data', true );
						$allSources          = array();
						$allSources[]        = array(
							'source_code'   => $location_saved_data[0]['source_code'],
							'name'          => $location_saved_data[0]['name'],
							'quantity'      => $varitiona_products_qunatities,
							'source_status' => 1,
							'status'        => 1,
						);
						unset( $product_data['sources'] );
						$product_data['sources'] = json_encode( $allSources );

						$attribute_set_id = get_option( $this->profile_id . 'attribute_set_id' );
						if ( empty( $attribute_set_id ) ) {
							GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
							$good_market_Request    = new Ced_Good_Market_Request();
							$products_attri         = $good_market_Request->get_good_market_attributes( $this->profile_id );
							$products_attri_decoded = json_decode( $products_attri, 1 );
							$schema                 = json_decode( $products_attri_decoded['data']['productAllowedAttributes']['groupwise_attributes'], 1 );
							update_option( $this->profile_id . 'attribute_set_id', $schema['attribute_set_id'] );
							$attribute_set_id = get_option( $this->profile_id . 'attribute_set_id' );

						}
						$pro_data_to_send['type']       = 'configurable';
						$pro_data_to_send['set']        = ! empty( $attribute_set_id ) ? $attribute_set_id : '';
						$image_role                     = array(
							'image'        => 'image1',
							'small_image'  => 'image1',
							'thumbnail'    => 'image1',
							'swatch_image' => '',
						);
						$pro_data_to_send['image_role'] = json_encode( $image_role );
						$vpro_data_pd                   = json_encode( $product_data );
						$saved_product_id               = get_post_meta( $product_id, 'saved_good_market_product_id', 1 );

						// If product already exists, $pro_data['id'] is to update the prdouct.
						if ( ! empty( $saved_product_id ) && is_numeric( $saved_product_id ) ) {

							$pro_data_to_send['id'] = $saved_product_id;
						}
						$pro_data_to_send['product']             = $vpro_data_pd;
						$pro_data_to_send['category_id']         = $this->profile_id;
						$pro_data_to_send['configurable_matrix'] = $product_variation_data;
						$pro_data_to_send['config_attributes']   = $config_attributes;
						// print_r($pro_data_to_send);die;
						$data_product_send['variables']['product_data']               = json_encode( $pro_data_to_send );
						// $data_product_send['variables']['product_data']['image_role'] = '{"image":"","small_image":"","thumbnail":"image1","swatch_image":""}';
					}
				} else {
					$product_data = $this->good_market_get_formatted_data(  $category, $subCategory, $product_id, $primary_variant ='' );

					if ( $product_data ) {
						$pro_data            = array();
						$pro_data['product'] = json_encode( $product_data );
						$pro_data['type']    = 'simple';
						$saved_product_id    = get_post_meta( $product_id, 'saved_good_market_product_id', 1 );

						// If product already exists, $pro_data['id'] is to update the prdouct.
						if ( ! empty( $saved_product_id ) && is_numeric( $saved_product_id ) ) {

							$pro_data['id'] = $saved_product_id;
						}
						$attribute_set_id = get_option( $this->profile_id . 'attribute_set_id' );
						if ( empty( $attribute_set_id ) ) {
							GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
							$good_market_Request    = new Ced_Good_Market_Request();
							$products_attri         = $good_market_Request->get_good_market_attributes( $this->profile_id );
							$products_attri_decoded = json_decode( $products_attri, 1 );
							$schema                 = json_decode( $products_attri_decoded['data']['productAllowedAttributes']['groupwise_attributes'], 1 );
							update_option( $this->profile_id . 'attribute_set_id', $schema['attribute_set_id'] );
							$attribute_set_id = get_option( $this->profile_id . 'attribute_set_id' );

						}
						$pro_data['set']                                = ! empty( $attribute_set_id ) ? $attribute_set_id : '';
						$image_role                                     = array(
							'image'        => 'image1',
							'small_image'  => 'image1',
							'thumbnail'    => 'image1',
							'swatch_image' => '',
						);
						$pro_data['image_role']                         = json_encode( $image_role );
						$data_product_send['variables']['product_data'] = json_encode( $pro_data );
						// $data_product_send['variables']['product_data']['image_role'] = '{"image":"","small_image":"","thumbnail":"image1","swatch_image":""}';

					}
				}
			}
		}

		if ( ! empty( $data_product_send ) ) {
			$data_product_send_all = $data_product_send['variables']['product_data'];
			return $data_product_send_all;
		} else {

			return $product_id;
		}
	}

	public function good_market_get_variation_data( $variation_id, $parent_product_id ) {
		$v_product = wc_get_product( $variation_id );
		$image_url_id       = get_post_thumbnail_id( $variation_id );
		$woo_products_image = $this->get_img_data( $image_url_id );
		$gmarket_variant_attributes = array(
			'_type',
			'_color',
			'_size',
		);
		$var_pro                    = array();
		$this->ced_good_market_check_profile( $variation_id );
		$category_id = $this->profile_id;
		foreach ( $gmarket_variant_attributes as $variant_attr ) {
			$attr_data    = $this->fetch_meta_value_of_the_product( $variation_id, $variant_attr, 'global' );
			$variant_attr = str_replace( '_', '', $variant_attr );
			$attribute    = $v_product->get_variation_attributes();
			if ( ! empty( $attr_data ) ) {
				$var_pro['configurable_attribute'][ $variant_attr ] = html_entity_decode($attr_data);
				$var_pro['config_attributes'][]                     = $variant_attr;
			} else {
				$attribule_array = array();
				foreach ( $attribute as $attr_name => $value ) {
					$taxonomy          = $attr_name;
					// $attr_name         = str_replace( 'pa_', '', $attr_name );
					$attr_name         = str_replace( 'attribute_', '', $attr_name );
					$trm_obj = get_term_by('slug',$value,$attr_name);
					if ( is_object( $trm_obj ) ) {
						$value = $trm_obj->name;
					}
					$attr_name         = wc_attribute_label( $attr_name, $v_product );
					$attr_name_by_slug = get_taxonomy( $taxonomy );
					if ( is_object( $attr_name_by_slug ) ) {
						$attr_name = $attr_name_by_slug->label;
					}
					if ( strtolower($attr_name) == strtolower($variant_attr) ) {
						$var_pro['configurable_attribute'][ $attr_name ] = html_entity_decode( $value );
						$var_pro['config_attributes'][]                  = $attr_name;
					}
				}
			}
		}
		if ( empty( $var_pro ) || count( $var_pro['configurable_attribute'] ) != count( $attribute ) ) {
			return 'not_matched';
		}
		if ( empty( $var_pro ) ) {
			$attribute = $v_product->get_variation_attributes();

			$attribule_array = array();
			foreach ( $attribute as $attr_name => $value ) {
				$taxonomy          = $attr_name;
				$attr_name         = str_replace( 'pa_', '', $attr_name );
				$attr_name         = str_replace( 'attribute_', '', $attr_name );
				$attr_name         = wc_attribute_label( $attr_name, $_product );
				$attr_name_by_slug = get_taxonomy( $taxonomy );
				if ( is_object( $attr_name_by_slug ) ) {
					$attr_name = $attr_name_by_slug->label;
				}
				$var_pro['configurable_attribute'][ $attr_name ] = wc_attribute_label( $value );
				$var_pro['config_attributes'][]                  = $attr_name;
			}
		}

		$sku = get_post_meta( $variation_id, '_sku', true );
		if ( empty( $sku ) || ! $sku ) {
			$currentDomain = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '';
			$sku           = $currentDomain . '_' . $variation_id;
		}
		$title               = $v_product->get_title();
		$var_pro['name']     = $title . '_' . $sku;
		$var_pro['sku']      = $sku;
		$location_saved_data = get_option( 'location_data', true );
		$allSources          = array();
		$stock               = $this->get_stock( $variation_id );
		$manage_stock        = get_post_meta( $parent_product_id, '_manage_stock', true );
		$v_manage_stock      = get_post_meta( $variation_id, '_manage_stock', true );
		$stock_status        = get_post_meta( $parent_product_id, '_stock_status', true );
		if ( 'instock' == $stock_status && 'yes' == $manage_stock && 'no' == $v_manage_stock ) {
			$stock = get_post_meta( $parent_product_id, '_stock', true );
		}
		$allSources[]          = array(
			'source_code'   => $location_saved_data[0]['source_code'],
			'name'          => $location_saved_data[0]['name'],
			'quantity'      => $stock,
			'source_status' => 1,
			'status'        => 1,
		);
		$var_pro['sources']    = json_encode( $allSources );
		$var_pro['integ_type'] = 'wooCommerce';
		$var_pro['price']      = $this->get_price( $variation_id );
		$var_pro['image']      = $woo_products_image;
		$data_var_send['data'] = $var_pro;
		return $data_var_send;
	}


	/**
	 * Ced_Good_Market_Product ced_get_item_category
	 *
	 * @since 1.0.0
	 * @param array $product_id.
	 */
	public function ced_get_item_category( $product_id ) {
		$term_list  = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		$cat_id     = (int) $term_list[0];
		$mapped_cat = get_option( 'good_market_mapped_cat' );
		$mapped_cat = json_decode( $mapped_cat, 1 );

		if ( ! empty( $mapped_cat ) && is_array( $mapped_cat ) ) {

			foreach ( $mapped_cat['profile'] as $key => $value ) {
				if ( in_array( $cat_id, $value['woo_cat'] ) ) {
					$category = $key;
					return $category;
				}
			}
		}

	}



	/**
	 * Ced_Good_Market_Product get_formatted_data.
	 *
	 * @since 1.0.0
	 * @param int $product_id.
	 */
	public function good_market_get_formatted_data( $category, $subCategory, $product_id = 0, $primary_variant = '') {

		$this->ced_good_market_check_profile( $product_id );
		$_product     = wc_get_product( $product_id );
		$product_data = $_product->get_data();
		if ( 'variation' == $_product->get_type() ) {
			$parent_id  = $_product->get_parent_id();
			$parent_sku = get_post_meta( $parent_id, '_sku', true );
			if ( empty( $parent_sku ) ) {
				$parent_sku = $parent_id;
			}
		}
		$profile_id                  = $this->profile_id;
/*		$ced_good_market_profile_pro = json_decode( $profile_data_good_mart, 1 );
		if ( is_array( $ced_good_market_profile_pro ) ) {
			$ced_good_market_profile_map_data = isset( $ced_good_market_profile_pro['profile'][ $profile_id ]['profile_data'] ) ? json_decode( $ced_good_market_profile_pro['profile'][ $profile_id ]['profile_data'], 1 ) : array();
		}
*/		$ced_good_market_global_map_data = get_option( 'ced_good_market_global_settings', true );
		$ced_good_market_global_map_data = json_decode( $ced_good_market_global_map_data, true );
		if ( ! empty( $ced_good_market_global_map_data ) && is_array( $ced_good_market_global_map_data ) ) {
			foreach ( $ced_good_market_global_map_data as $profile_key => $profile_value ) {
				$pro_arr_key = str_replace( $profile_id . '_', '', $profile_key );
				$pro_arr_val = $this->fetch_meta_value_of_the_product( $product_id, $profile_key, 'global' );
				if ( empty( $pro_nmw['name'] ) ) {
					$pro_nmw['name'] = $product_data['name'];
				}
				if ( empty( $pro_nmw['price'] ) ) {
					$pro_nmw['price'] = $this->get_price( $product_id );
				}
				$pro_nmw['sku'] = $this->fetch_meta_value_of_the_product( $product_id, '_sku', '' );
				if ( empty( $pro_nmw['sku'] ) ) {
					$currentDomain  = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '';
					$pro_nmw['sku'] = $currentDomain . '_' . $product_id;
				}
			}
		} else {
			$pro_nmw['name'] = $product_data['name'];
			$pro_nmw['sku']  = $this->fetch_meta_value_of_the_product( $product_id, '_sku', '' );
			if ( empty( $pro_nmw['sku'] ) ) {
				$currentDomain  = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '';
				$pro_nmw['sku'] = $currentDomain . '_' . $product_id;
			}
			if ( empty( $pro_nmw['price'] ) ) {
					$pro_nmw['price'] = $this->get_price( $product_id );
			}
		}
		/** Product description */
		$description = get_post_meta( $product_id, '_custom_description', true );
		if ( empty( $description ) ) {
			$description = $this->fetch_meta_value_of_the_product( $product_id, 'global_description', '' );
		}
		if ( empty( $description ) && 'variation' == $_product->get_type() ) {
			$_parent     = wc_get_product( $_product->get_parent_id() );
			$parent_data = $_parent->get_data();
			$description = $parent_data['description'];
			if ( empty( $description ) ) {
				$description = $parent_data['short_description'];
			}
		} elseif ( empty( $description ) ) {
			$description = $product_data['description'];
			if ( empty( $description ) ) {
				$description = $product_data['short_description'];
			}
		}
		$description = preg_replace( '/\[.*?\]/', '', $description );
		$short_description = $product_data['short_description'];

		/** Product weight */
		$weight = get_post_meta( $product_id, '_custom_package_weight', true );
		if ( empty( $weight ) ) {
			$weight = $this->fetch_meta_value_of_the_product( $product_id, 'global_package_weight', '' );
		}
		if ( empty( $weight ) ) {
			$weight = get_post_meta( $product_id, '_weight', 'true' );
		}

		$stock                     = $this->get_stock( $product_id );
		$attachment_ids            = array();
		$image_url_id              = $product_data['image_id'];
		$featured_attachment_ids[] = $image_url_id;
		/** Product images */
		if ( 'variation' == $_product->get_type() ) {
			$image          = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) : '';
			$attachment_ids = $_product->get_gallery_image_ids();
			if ( empty( $image ) ) {
				$image           = wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'full' ) : '';
				$_parent_product = wc_get_product( $parent_id );
				$attachment_ids  = $_parent_product->get_gallery_image_ids();
			}
		} else {
			$attachment_ids = $_product->get_gallery_image_ids();
			$image          = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) : '';
		}
			$attachment_ids = array_merge( $featured_attachment_ids, $attachment_ids );

		// Additional images
		$gallery_images = array();
		if ( ! empty( $attachment_ids ) ) {
			foreach ( $attachment_ids as $key => $attachment_id ) {
				if ( empty( wp_get_attachment_url( $attachment_id ) ) ) {
					continue;
				}
				$key_ar                    = 'image' . ( $key + 1 );
				$img_media_send[ $key_ar ] = $this->get_img_data( $attachment_id );
			}
		}

		$location_saved_data = get_option( 'location_data', true );
		if ( empty( $description ) ) {
			$description = $product_data['name'];
		}
		$weight_unit = get_option( 'woocommerce_weight_unit' );
		if ( 'kg' == $weight_unit ) {
			$weight = (float)$weight * 1000;
		} elseif ( 'lbs' == $weight_unit ) {
			$weight = (float)$weight * 453.6;
		} elseif ( 'oz' == $weight_unit ) {
			$weight = (float)$weight * 28.35;
		}

		if ( $_product->is_virtual( 'yes' ) ) {
			$weight             = 0;
			$product_has_weight = 0;
		} else {
			$product_has_weight = 1;
		}
/*		var_dump($location_saved_data);
		die('dfg');*/
		$allSources   = array();
		$allSources[] = array(
			'source_code'   => $location_saved_data[0]['source_code'],
			'name'          => $location_saved_data[0]['name'],
			'quantity'      => $stock,
			'source_status' => 1,
			'status'        => 1,
		);
		$product_data = array(
			'status'             => 1,
			'visibility'         => 4,
			'weight'             => !empty($weight) ? round( $weight ) : 0,
			'category_ids'       => array(
				'0' => $profile_id,
			),
			'integ_type'         => 'wooCommerce',
			'sources'            => json_encode( $allSources ),
			'description'        => strip_tags( $description, '<p><b>' ),
			'short_description'  => strip_tags( $short_description, '<p><b>' ),
			'media_gallery'      => json_encode( array( 'images' => json_encode( $img_media_send ) ) ),
			'meta_title'         => $product_data['name'],
			'meta_keyword'       => $product_data['name'],
			'meta_description'   => $product_data['name'],
			'url_key'            => $pro_nmw['sku'],
			'product_has_weight' => $product_has_weight,

		);
		$product_data_merge = array_merge( $pro_nmw, $product_data );
		$saved_product_id   = get_post_meta( $product_id, 'saved_good_market_product_id', 1 );

						// If product already exists, $pro_data['id'] is to update the prdouct.
		if ( ! empty( $saved_product_id ) && is_numeric( $saved_product_id ) ) {

			unset( $product_data_merge['media_gallery'] );
		}
		if(isset($product_data_merge['quantityandstockstatus'])){		
			$product_data_merge['quantity_and_stock_status'] = $product_data_merge['quantityandstockstatus'];
			unset( $product_data_merge['quantityandstockstatus'] );
		}
		// print_r($product_data_merge);die;
		return $product_data_merge;

	}


	public function get_img_data( $image_url_id ) {
		$woo_products_image = wp_get_attachment_url( $image_url_id );
		// print_r(exif_imagetype($woo_products_image));die;

		if ( ! empty( $woo_products_image ) ) {
			$imgdata           = file_get_contents( $woo_products_image );
			$mime_type         = getimagesizefromstring( $imgdata );
			$img_send_data     = base64_encode( $imgdata );
			$image_data_api[0] = home_url();
			$image_data_api_file['file'] = $woo_products_image;

			// $image_data_api_file['file'] = 'https://woodemo.cedcommerce.com/woocommerce/goodmarket/wp-content/uploads/2022/09/cap-2.jpg';
			$image_data_api_file['file_name'] = basename( $woo_products_image );
			$img_file_wrp['file']             = json_encode( $image_data_api_file );
			return ( $img_file_wrp );
		}
		return '';
	}


	/**
	 * Ced_Good_Market_Product get_stock.
	 *
	 * @since 1.0.0
	 * @param int $good_market_product_id.
	 */
	public function get_stock( $product_id ) {

		$this->profile_assigned = true;

		$stock = get_post_meta( $product_id, '_custom_stock', true );
		if ( '' == $stock ) {
			$stock = (int) $this->fetch_meta_value_of_the_product( $product_id, '_stock', '' );
		}
		if ( '' == $stock ) {
			$stock = (int) get_post_meta( $product_id, '_stock', true );
		}
		if ( $stock < 0 ) {
			$stock = 0;
		}

		return $stock;
	}

	/**
	 * Ced_Good_Market_Product get_price.
	 *
	 * @since 1.0.0
	 * @param int $good_market_product_id.
	 */
	public function get_price( $product_id ) {

		$this->profile_assigned = true;

		$price = get_post_meta( $product_id, '_custom_price', true );
		if ( empty( $price ) ) {
			$price = $this->fetch_meta_value_of_the_product( $product_id, '_price', '' );
		}
		if ( empty( $price ) ) {
			$price = get_post_meta( $product_id, '_price', true );
		}

		$custom_markup_type  = get_post_meta( $product_id, '_custom_markup_type', true );
		$custom_markup_value = get_post_meta( $product_id, '_custom_markup_value', true );
		$markup_type         = $this->fetch_meta_value_of_the_product( $product_id, 'global_markup_type', 'global' );
		$markup_value        = $this->fetch_meta_value_of_the_product( $product_id, 'global_markup_value', 'global' );

		if ( ! empty( $custom_markup_type ) && ! empty( $custom_markup_value ) ) {
			if ( 'fixed_increased' == $custom_markup_type ) {
				$price = (float) $price + (float) $custom_markup_value;
			} else {
				$price = (float) $price + ( ( (float) $custom_markup_value / 100 ) * (float) $price );
			}
		} elseif ( ! empty( $markup_type ) && ! empty( $markup_value ) ) {
			if ( 'fixed_increased' == $markup_type ) {
				$price = (float) $price + (float) $markup_value;
			} else {
				$price = (float) $price + ( ( (float) $markup_value / 100 ) * (float) $price );
			}
		}
		if ( get_woocommerce_currency() == 'USD' ) {
			return $price;
		} else {
			$ced_goodmarket_currency_convert_rate = get_option( 'ced_goodmarket_currency_convert_rate' );
			$price                                = (float) $price * (float) $ced_goodmarket_currency_convert_rate;
			$price                                = !empty($price) ? round( $price, 2 ) : '';
			return $price;
		}
	}


	/**
	 * Function for getting assigned profile to a product
	 *
	 * @since 1.0.0
	 * @param array $product_id Product Id.
	 * @param int   $meta_key Metakey.
	 */
	public function fetch_meta_value_of_the_product( $product_id, $meta_key, $global = '' ) {
		$value = '';
		if ( ! empty( $global ) ) {
			$ced_good_market_global_data = get_option( 'ced_good_market_global_settings', array() );
			if ( ! empty( $ced_good_market_global_data ) ) {
				$ced_good_market_global_data = $ced_good_market_global_data;
				$ced_good_market_global_data = json_decode( $ced_good_market_global_data, true );

				$product_profile_data = $ced_good_market_global_data;
			}
		} else {
			$product_profile_data = '';
			if(isset($this->profile_data)) {
				$product_profile_data = $this->profile_data;
			}
			// print_r($product_profile_data);die;
		}
		$_product     = wc_get_product( $product_id );
		$product_type = $_product->get_type();
		if ( 'variation' == $product_type ) {
			$parent_id = $_product->get_parent_id();
		} else {
			$parent_id = '0';
		}
		if ( ! empty( $product_profile_data ) && isset( $product_profile_data[ $meta_key ] ) ) {

			$profile_data      = $product_profile_data[ $meta_key ];
			$temp_profile_data = $profile_data;

			if ( isset( $temp_profile_data['default'] ) && ! empty( $temp_profile_data['default'] ) && '' != $temp_profile_data['default'] && ! is_null( $temp_profile_data['default'] ) ) {
				$value = $temp_profile_data['default'];
			} elseif ( isset( $temp_profile_data['metakey'] ) && ! empty( $temp_profile_data['metakey'] ) && '' != $temp_profile_data['metakey'] && ! is_null( $temp_profile_data['metakey'] ) ) {
				$temp_profile_data['metakey'] = $temp_profile_data['metakey'];
					// if woo attribute is selected''
				if ( is_array( $temp_profile_data['metakey'] ) ) {
					foreach ( $temp_profile_data['metakey'] as $_sample_meta_key ) {
						if ( strpos( $_sample_meta_key, 'umb_pattr_' ) !== false ) {
							$woo_attribute = explode( 'umb_pattr_', $_sample_meta_key );
							$woo_attribute = end( $woo_attribute );

							if ( 'variation' == $_product->get_type() ) {

								$attributes = $_product->get_variation_attributes();
								if ( isset( $attributes[ 'attribute_pa_' . $woo_attribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $woo_attribute ] ) ) {
									$woo_attribute_value = $attributes[ 'attribute_pa_' . $woo_attribute ];
									if ( '0' != $parent_id ) {
										$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $woo_attribute );
									}
								} else {
									$woo_attribute_value = $_product->get_attribute( 'pa_' . $woo_attribute );

									$woo_attribute_value = explode( ',', $woo_attribute_value );
									$woo_attribute_value = $woo_attribute_value[0];

									if ( '0' != $parent_id ) {
										$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
									} else {
										$product_terms = get_the_terms( $product_id, 'pa_' . $woo_attribute );
									}
								}
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $temp_key => $temp_value ) {
										if ( $temp_value->slug == $woo_attribute_value ) {
											$woo_attribute_value = html_entity_decode($temp_value->name);
											break;
										}
									}
									if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
										$value = $woo_attribute_value;
									} else {
										$value = get_post_meta( $product_id, $meta_key, true );
									}
								} else {
									$value = get_post_meta( $product_id, $meta_key, true );
								}
							} else {

								$woo_attribute_value = $_product->get_attribute( 'pa_' . $woo_attribute );
								$product_terms       = get_the_terms( $product_id, 'pa_' . $woo_attribute );
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $temp_key => $temp_value ) {
										if ( $temp_value->slug == $woo_attribute_value ) {
											$woo_attribute_value = html_entity_decode($temp_value->name);
											break;
										}
									}
									if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
										$value = $woo_attribute_value;
									} else {
										$value = get_post_meta( $product_id, $meta_key, true );
									}
								} else {
									$value = get_post_meta( $product_id, $meta_key, true );
								}
							}
							if ( ! empty( $value ) ) {
								break;
							}
						} else {
							$value = get_post_meta( $product_id, $_sample_meta_key, true );
							if ( '_thumbnail_id' == $_sample_meta_key ) {
								$value = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
							}
							if ( ! isset( $value ) || empty( $value ) ) {
								if ( '0' != $parent_id ) {

									$value = get_post_meta( $parent_id, $_sample_meta_key, true );
									if ( '_thumbnail_id' == $_sample_meta_key ) {
										$value = wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
									}

									if ( ! isset( $value ) || empty( $value ) ) {
										$value = get_post_meta( $product_id, $meta_key, true );
									}
								} else {
									$value = get_post_meta( $product_id, $meta_key, true );
								}
							}
							if ( ! empty( $value ) ) {
									break;
							}
						}
					}
				}
			} else {
				$value = get_post_meta( $product_id, $meta_key, true );
			}
		} else {
			$value = get_post_meta( $product_id, $meta_key, true );
		}
		return $value;

	}

	/**
	 * *****************************************
	 * GET ASSIGNED PRODUCT DATA FROM PROFILES
	 * *****************************************
	 *
	 * @since 1.0.0
	 *
	 * @param array $product_id Product lsting  ids.
	 *
	 * @link  http://www.cedcommerce.com/
	 * @return $profile_data assigined profile data .
	 */

	public function ced_good_market_check_profile( $product_id = '' ) {
		if ( 'variation' == $this->ced_pro_type( $product_id ) ) {
			$product_id = $this->parent_id;
		}

		$wc_product  = wc_get_product( $product_id );
		$data        = $wc_product->get_data();
		$category_id = isset( $data['category_ids'] ) ? $data['category_ids'] : array();
		foreach ( $category_id as $key => $value ) {
			$profile_id = get_term_meta( $value, 'ced_good_market_category', true );

			if ( ! empty( $profile_id ) ) {
				break;
			}
		}

		if ( isset( $profile_id ) && ! empty( $profile_id ) ) {
			$this->profile_id                  = $profile_id;
			$this->is_profile_assing           = true;
			$profile_data_good_mart            = get_option( 'ced_mapped_cat_good_market' );
			$ced_good_market_profile_data_decd = json_decode( $profile_data_good_mart, 1 );
			if ( is_array( $ced_good_market_profile_data_decd ) ) {
				$ced_good_market_profile_data = isset( $ced_good_market_profile_data_decd['profile'][ $profile_id ]['profile_data'] ) ? $ced_good_market_profile_data_decd['profile'][ $profile_id ]['profile_data'] : array();
			}
		} else {
			$this->is_profile_assing = false;
			return 'false';
		}
		$this->profile_data = isset( $ced_good_market_profile_data ) ? json_decode( $ced_good_market_profile_data, 1 ) : '';
		return $this->profile_data;
	}


	/**
	 * **********************************************
	 * Get Woocommerce Product Data, Type, Parent ID.
	 * **********************************************
	 *
	 * @since 1.0.0
	 *
	 * @param string $pr_id Product lsting  ids.
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
	 * **********************************************
	 * Get Woocommerce Product Data, Type, Parent ID.
	 * **********************************************
	 *
	 * @since 1.0.0
	 *
	 * @param string $pr_id Product lsting  ids.
	 *
	 * @link  http://www.cedcommerce.com/
	 * @return string Woo product type.
	 */

	public function ced_good_market_prepare_delete_data( $product_arry = array() ) {

		$data_delete_pro = array();
		if ( ! empty( $product_arry ) ) {

			foreach ( $product_arry as $key_prp_del => $val_prp_del ) {
				$saved_good_market_product_id = get_post_meta( $val_prp_del, 'saved_good_market_product_id', true );
				if ( ! empty( $saved_good_market_product_id ) ) {

					$data_delete_pro[ $key_prp_del ]['product_id'] = $saved_good_market_product_id;
					delete_post_meta( $val_prp_del, 'saved_good_market_product_id' );
				}
			}
		}
		$array_del_product['query']                 = 'mutation massDelete($vendor_id: Int!, $products: String!, $hash_token: String) {
			productMassDelete(vendor_id: $vendor_id, products: $products, hash_token: $hash_token) {
				count
			}
		}';
		$array_del_product['variables']['products'] = json_encode( $data_delete_pro );
		return $array_del_product;
	}
}