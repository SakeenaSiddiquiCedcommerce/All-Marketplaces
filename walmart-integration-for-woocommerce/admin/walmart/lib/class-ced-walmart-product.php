<?php
/**
 * Gettting order related data
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

/**
 * Ced_Walmart_Product
 *
 * @since 1.0.0
 * @param object $_instance Class instance.
 */
class Ced_Walmart_Product {


	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */

	public static $_instance;

	/**
	 * Ced_Walmart_Product Instance.
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
	 * Ced_Walmart_Product construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependency();
	}

	/**
	 * Ced_Walmart_Product loading dependency.
	 *
	 * @since 1.0.0
	 */
	public function load_dependency() {
		$ced_walmart_curl_file = CED_WALMART_DIRPATH . 'admin/walmart/lib/class-ced-walmart-curl-request.php';
		include_file( $ced_walmart_curl_file );
		$this->ced_walmart_curl_instance = Ced_Walmart_Curl_Request::get_instance();
	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_data.
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */
	public function ced_walmart_prepare_data( $walmart_product_ids = array(), $process_mode = 'CREATE' ) {
		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array              = array();
		$this->items_with_errors = false;
		foreach ( $walmart_product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( is_object( $_product ) ) {
				$status = get_post_status( $product_id );
				if ( 'publish' == $status ) {
					$category = $this->ced_get_item_category( $product_id );
					if ( $category ) {
						$subCategory = ced_walmart_subcategories( $category );
						$type        = $_product->get_type();
						if ( 'variable' == $type ) {
							$variations = $_product->get_children();
							if ( is_array( $variations ) && ! empty( $variations ) ) {
								$primary_variant = 'Yes';
								foreach ( $variations as $index => $variation_id ) {
									$product_data = $this->get_formatted_data( $variation_id, $category, $primary_variant, $subCategory );
									if ( $product_data ) {
										$feed_array['MPItem'][] = $product_data;
									}
									$primary_variant = 'No';
								}
							}
						} else {
							$product_data = $this->get_formatted_data( $product_id, $category, $primary_variant = '', $subCategory );
							if ( $product_data ) {
								$feed_array['MPItem'][] = $product_data;
							}
						}
					} else {
						continue;
					}
				}
			}
		}

		if ( ! empty( $feed_array ) ) {
			$feed_header    = $this->get_feed_header( $subCategory );
			$feed_array     = array_merge( $feed_header, $feed_array );
			$wp_upload_dir  = wp_upload_dir() ['basedir'];
			$walmart_folder = $wp_upload_dir . '/walmart';
			if ( ! is_dir( $walmart_folder ) ) {
				mkdir( $walmart_folder, 0755 );
			}
			$filepath = $walmart_folder . '/product-feed.json';
			file_put_contents( $filepath, json_encode( $feed_array ) );
			return $feed_array;
		} else {
			return false;
		}
	}

	/**
	 * Ced_Walmart_Product ced_get_item_category
	 *
	 * @since 1.0.0
	 * @param array $product_id.
	 */
	public function ced_get_item_category( $product_id ) {
		$term_list  = wp_get_post_terms(
			$product_id,
			'product_cat',
			array(
				'fields' => 'ids',
			)
		);
		$cat_id     = (int) $term_list[0];
		$mapped_cat = get_option( 'ced_mapped_cat' );
		$mapped_cat = json_decode( $mapped_cat, 1 );
		foreach ( $mapped_cat['profile'] as $key => $value ) {
			if ( in_array( $cat_id, $value['woo_cat'] ) ) {
				$category = $key;
				return $category;
			}
		}

	}

	/**
	 * Ced_Walmart_Product get_formatted_data.
	 *
	 * @since 1.0.0
	 * @param int $product_id.
	 */
	public function get_formatted_data( $product_id = 0, $category = '', $primary_variant = '', $subCategory = '' ) {
		$_product     = wc_get_product( $product_id );
		$product_data = $_product->get_data();
		if ( 'variation' == $_product->get_type() ) {
			$parent_id  = $_product->get_parent_id();
			$parent_sku = get_post_meta( $parent_id, '_sku', true );
			if ( empty( $parent_sku ) ) {
				$parent_sku = $parent_id;
			}
		}

		$sku = get_post_meta( $product_id, '_sku', true );

		/** Identifier type */
		$idenitifier_type = get_post_meta( $product_id, '_custom_identifier_type', true );
		if ( empty( $idenitifier_type ) ) {
			$idenitifier_type = $this->fetch_meta_value_of_the_product( $product_id, 'global_identifier_type', 'global' );
		}

		/** Identifier value */
		$idenitifier_value = get_post_meta( $product_id, '_custom_identifier_value', true );
		if ( empty( $idenitifier_value ) ) {
			$idenitifier_value = $this->fetch_meta_value_of_the_product( $product_id, 'global_identifier_value', 'global' );
		}
		if ( 14 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'GTIN';
		} elseif ( 13 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'EAN';
		} elseif ( 12 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'UPC';
		}
		/** Product title */
		$product_name = get_post_meta( $product_id, '_custom_title', true );
		if ( empty( $product_name ) ) {
			$product_name = $this->fetch_meta_value_of_the_product( $product_id, 'global_title', 'global' );
		}
		if ( empty( $product_name ) ) {
			$product_name = $product_data['name'];
		}

		/** Product price */
		$price = (float) get_post_meta( $product_id, '_custom_price', true );
		if ( empty( $price ) ) {
			$price = (float) $this->fetch_meta_value_of_the_product( $product_id, 'global_price', 'global' );
		}
		if ( empty( $price ) ) {
			$price = (float) get_post_meta( $product_id, '_price', true );
		}

		/** Price markup */
		$custom_markup_type  = get_post_meta( $product_id, '_custom_markup_type', true );
		$custom_markup_value = (float) get_post_meta( $product_id, '_custom_markup_value', true );
		$markup_type         = $this->fetch_meta_value_of_the_product( $product_id, 'global_markup_type', 'global' );
		$markup_value        = (float) $this->fetch_meta_value_of_the_product( $product_id, 'global_markup_value', true );

		if ( ! empty( $custom_markup_type ) && ! empty( $custom_markup_value ) ) {
			if ( 'fixed_increased' == $custom_markup_type ) {
				$price = $price + $custom_markup_value;
			} else {
				$price = $price + ( ( $custom_markup_value / 100 ) * $price );
			}
		} elseif ( ! empty( $markup_type ) && ! empty( $markup_value ) ) {
			if ( 'fixed_increased' == $markup_type ) {
				$price = $price + $markup_value;
			} else {
				$price = $price + ( ( $markup_value / 100 ) * $price );
			}
		}

		/** Product description */
		$short_description = get_post_meta( $product_id, '_custom_description', true );
		if ( empty( $short_description ) ) {
			$short_description = $this->fetch_meta_value_of_the_product( $product_id, 'global_description', 'global' );
		}
		if ( empty( $short_description ) && 'variation' == $_product->get_type() ) {
			$_parent           = wc_get_product( $_product->get_parent_id() );
			$parent_data       = $_parent->get_data();
			$short_description = $parent_data['description'];
			if ( empty( $short_description ) ) {
				$short_description = $parent_data['short_description'];
			}
		} elseif ( empty( $short_description ) ) {
			$short_description = $product_data['description'];
			if ( empty( $short_description ) ) {
				$short_description = $product_data['short_description'];
			}
		}
		$short_description = preg_replace( '/\[.*?\]/', '', $short_description );
		$short_description = substr( $short_description, 0, 3999 );

		/** Product brand */
		$brand = get_post_meta( $product_id, '_custom_product_brand', true );
		if ( empty( $brand ) ) {
			$brand = $this->fetch_meta_value_of_the_product( $product_id, 'global_product_brand', 'global' );
		}
		if ( empty( $brand ) ) {
			$brand = 'Unbranded';
		}

		// New Array created for $orderable
		$orderable = array();

		// Array Preparing for orderable
		$orderable['Orderable']['sku']                = $sku;
		$orderable['Orderable']['productName']        = $product_name;
		$orderable['Orderable']['brand']              = $brand;
		$orderable['Orderable']['price']              = round( $price, 2 );
		$orderable['Orderable']['productIdentifiers'] = array(
			'productId'     => $idenitifier_value,
			'productIdType' => $idenitifier_type,
		);

		/** Fullfillment lag time */
		$fullfillment_lag_time = get_post_meta( $product_id, '_custom_walmart_fulfillmentlagtime', true );
		if ( empty( $fullfillment_lag_time ) ) {
			$fullfillment_lag_time = $this->fetch_meta_value_of_the_product( $product_id, 'global_walmart_fulfillmentlagtime', 'global' );
		}

		if ( ! empty( $fullfillment_lag_time ) ) {
			$orderable['Orderable']['fulfillmentLagTime'] = (int) $fullfillment_lag_time;
		}

		/** Product weight */
		$weight = get_post_meta( $product_id, '_custom_package_weight', true );
		if ( empty( $weight ) ) {
			$weight = $this->fetch_meta_value_of_the_product( $product_id, 'global_package_weight', 'global' );
		}
		if ( empty( $weight ) ) {
			$weight = get_post_meta( $product_id, '_weight', 'true' );
		}

		if ( ! empty( $weight ) ) {
			$orderable['Orderable']['ShippingWeight'] = (float) $weight;
		}

		/** Floor price */
		$floor_price = get_post_meta( $product_id, '_custom_floor_price', true );
		if ( empty( $floor_price ) ) {
			$floor_price = $this->fetch_meta_value_of_the_product( $product_id, 'global_floor_price', 'global' );
		}

		if ( ! empty( $floor_price ) ) {
			$orderable['Orderable']['floorPrice'] = (float) $floor_price;

		}

		/** Price per Unit qty */
		$price_per_unit_qty = get_post_meta( $product_id, '_custom_pricePerUnit_Quantity', true );
		if ( empty( $price_per_unit_qty ) ) {
			$price_per_unit_qty = $this->fetch_meta_value_of_the_product( $product_id, 'global_pricePerUnit_Quantity', 'global' );
		}

		/** Price per Unit measure */
		$price_per_unit_measure = get_post_meta( $product_id, '_custom_pricePer_UnitUom', true );
		if ( empty( $price_per_unit_measure ) ) {
			$price_per_unit_measure = $this->fetch_meta_value_of_the_product( $product_id, 'global_pricePer_UnitUom', 'global' );
		}

		if ( ! empty( $price_per_unit_qty ) && ! empty( $price_per_unit_measure ) ) {

			$orderable['Orderable']['pricePerUnit'] = array(
				'pricePerUnitQuantity' => (float) $price_per_unit_qty,
				'pricePerUnitUom'      => ucfirst( $price_per_unit_measure ),
			);

		}

		/** Electronic Indicator */
		$electronics_Indicator = get_post_meta( $product_id, '_custom_electronics_Indicator', true );
		if ( empty( $electronics_Indicator ) ) {
			$electronics_Indicator = $this->fetch_meta_value_of_the_product( $product_id, 'global_electronics_Indicator', 'global' );
		}

		if ( ! empty( $electronics_Indicator ) ) {
			$orderable['Orderable']['electronicsIndicator'] = ucfirst( $electronics_Indicator );
		}

		/** Battery Technolgy Type*/
		$battery_technology_type = get_post_meta( $product_id, '_custom_battery_technology_type', true );
		if ( empty( $battery_technology_type ) ) {
			$battery_technology_type = $this->fetch_meta_value_of_the_product( $product_id, 'global_battery_technology_type', 'global' );
		}

		if ( ! empty( $battery_technology_type ) ) {
			$orderable['Orderable']['batteryTechnologyType'] = ucfirst( $battery_technology_type );
		}

		/** Chemical Aerosol Pesticide */
		$chemical_aerosol_pesticide = get_post_meta( $product_id, '_custom_chemical_aerosol_pesticide', true );
		if ( empty( $chemical_aerosol_pesticide ) ) {
			$chemical_aerosol_pesticide = $this->fetch_meta_value_of_the_product( $product_id, 'global_chemical_aerosol_pesticide', 'global' );
		}

		if ( ! empty( $chemical_aerosol_pesticide ) ) {

			$orderable['Orderable']['chemicalAerosolPesticide'] = ucfirst( $chemical_aerosol_pesticide );
		}

		/** Multipack  Quantity  */
		$multipack_quantity = get_post_meta( $product_id, '_custom_multipack_quantity', true );
		if ( empty( $multipack_quantity ) ) {
			$multipack_quantity = $this->fetch_meta_value_of_the_product( $product_id, 'global_multipack_quantity', 'global' );
		}

		if ( ! empty( $multipack_quantity ) ) {
			$orderable['Orderable']['multipackQuantity'] = (int) $multipack_quantity;
		}

		/** Ships In Original Packaging  */
		$shipsIn_original_packaging = get_post_meta( $product_id, '_custom_shipsIn_original_packaging', true );
		if ( empty( $shipsIn_original_packaging ) ) {
			$shipsIn_original_packaging = $this->fetch_meta_value_of_the_product( $product_id, 'global_shipsIn_original_packaging', 'global' );
		}

		if ( ! empty( $shipsIn_original_packaging ) ) {
			$orderable['Orderable']['shipsInOriginalPackaging'] = ucfirst( $shipsIn_original_packaging );
		}

		/** Must Ship Alone  */
		$mustShipAlone = get_post_meta( $product_id, '_custom_mustShipAlone', true );
		if ( empty( $mustShipAlone ) ) {
			$mustShipAlone = $this->fetch_meta_value_of_the_product( $product_id, 'global_mustShipAlone', 'global' );
		}

		if ( ! empty( $mustShipAlone ) ) {
			$orderable['Orderable']['MustShipAlone'] = ucfirst( $mustShipAlone );
		}

		/** Product Id Update    */
		$productId_Update = get_post_meta( $product_id, '_custom_productId_Update', true );
		if ( empty( $productId_Update ) ) {
			$productId_Update = $this->fetch_meta_value_of_the_product( $product_id, 'global_productId_Update', 'global' );
		}

		if ( ! empty( $productId_Update ) ) {
			$orderable['Orderable']['ProductIdUpdate'] = ucfirst( $productId_Update );
		}

		/** Sku Update */
		$sku_Update = get_post_meta( $product_id, '_custom_sku_Update', true );
		if ( empty( $sku_Update ) ) {
			$sku_Update = $this->fetch_meta_value_of_the_product( $product_id, 'global_sku_Update', 'global' );
		}

		if ( ! empty( $sku_Update ) ) {
			$orderable['Orderable']['SkuUpdate'] = ucfirst( $sku_Update );
		}

		$visible_data  = $this->fetch_visible_item_data( $product_id, $category, $short_description, $primary_variant );
		$product_array = array_merge( $orderable, $visible_data );

		$temp_arr['MPItem'][] = $product_array;

		$temp_header_validation = array(
			'MPItemFeedHeader' => array(
				'subCategory'    => $subCategory,
				'sellingChannel' => 'marketplace',
				'processMode'    => 'REPLACE',
				'locale'         => 'en',
				'version'        => '1.5',
				'subset'         => 'EXTERNAL',
				'mart'           => 'WALMART_US',

			),
		);

		$validate_temp_array     = array_merge( $temp_header_validation, $temp_arr );
		$this->validation_errors = false;
		$filepath                = CED_WALMART_DIRPATH . 'admin/walmart/lib/feeds/product-validation.json';
		file_put_contents( $filepath, json_encode( $validate_temp_array ) );
		if ( ! $this->validate_json_schema( $filepath, 'MP_ITEM_SPEC', $secondarypath = 'schema' ) ) {
			if ( 'variation' == $_product->get_type() ) {
				update_post_meta( $parent_id, '_validation_erros', $this->validation_errors );
			} else {
				update_post_meta( $product_id, '_validation_erros', $this->validation_errors );
			}
			return false;
		} else {
			if ( 'variation' == $_product->get_type() ) {
				delete_post_meta( $parent_id, '_validation_erros' );
			} else {
				delete_post_meta( $product_id, '_validation_erros' );
			}
			return $product_array;
		}
	}

	/**
	 * Ced_Walmart_Product fetch_visible_item_data
	 *
	 * @since 2.0.0
	 * @param array $product_id, $walmart_cat , $short_description='', $primary_variant=''.
	 */

	public function fetch_visible_item_data( $product_id, $walmart_cat, $short_description = '', $primary_variant = '' ) {

		$all_profile_visible = get_option( 'ced_walmart_cat_visible_' . $walmart_cat );
		$all_profile_visible = json_decode( $all_profile_visible, 1 );

		$attribute_type = get_option( 'ced_walmart_cat_type_attribute_' . $walmart_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_cat = get_option( 'ced_mapped_cat' );
		$mapped_cat = json_decode( $mapped_cat, 1 );

		$convertData = json_decode( $mapped_cat['profile'][ $walmart_cat ]['profile_data'], 1 );
		$visible     = array();

		$_product = wc_get_product( $product_id );
		if ( 'variation' == $_product->get_type() ) {
			$parent_id = $_product->get_parent_id();
		}

		foreach ( $all_profile_visible as $key => $value ) {
			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {
					foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
						$attribute_type_key = str_replace( ' ', '', $walmart_cat ) . '_' . $attribute_type_key;

						if ( $attribute_type_key == $mappedKey ) {
							$type = 'string';
							if ( 'integer' == $attribute_type_value ) {
								$type = 'integer';
							} elseif ( 'number' == $attribute_type_value ) {
								$type = 'float';
							} elseif ( 'array' == $attribute_type_value ) {
								$type = 'array';
							}
							if ( 'array' == $type ) {

								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									if ( is_float( $mappedValue['default'] ) ) {
										$visible['Visible'][ $walmart_cat ][ $key ][] = (float) $mappedValue['default'];
									} elseif ( ctype_digit( $mappedValue['default'] ) ) {
										$visible['Visible'][ $walmart_cat ][ $key ][] = (int) $mappedValue['default'];
									} else {
										$visible['Visible'][ $walmart_cat ][ $key ][] = $mappedValue['default'];
									}
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {

									if ( is_array( $mappedValue['metakey'] ) ) {

										foreach ( $mappedValue['metakey'] as $_meta_key ) {

											if ( is_float( $_meta_key ) ) {

												$casted_value = (float) get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );

											} elseif ( ctype_digit( $_meta_key ) ) {
												$casted_value = (int) get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
											} else {
												$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
											}

											if ( ! empty( $casted_value ) ) {
												$visible['Visible'][ $walmart_cat ][ $key ][] = $casted_value;
												break;
											}
										}
									}
								}
								if ( empty( $visible['Visible'][ $walmart_cat ][ $key ][0] ) ) {
									unset( $visible['Visible'][ $walmart_cat ][ $key ] );

								}
							} else {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value = $mappedValue['default'];
									settype( $casted_value, $type );
									$visible['Visible'][ $walmart_cat ][ $key ] = $casted_value;
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {

									if ( is_array( $mappedValue['metakey'] ) ) {
										foreach ( $mappedValue['metakey'] as $_meta_key ) {
											$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
											settype( $casted_value, $type );
											if ( ! empty( $casted_value ) ) {
												$visible['Visible'][ $walmart_cat ][ $key ] = $casted_value;
												break;
											}
										}
									}
								}
							}

							if ( empty( $visible['Visible'][ $walmart_cat ][ $key ] ) ) {
								unset( $visible['Visible'][ $walmart_cat ][ $key ] );

							}
						}
					}
				}

				if ( isset( $value['properties'] ) ) {

					foreach ( $value['properties'] as $extra_parameter_key => $extra_parameter_value ) {
						$category_formatted = str_replace( ' ', '', $walmart_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_cat ) . '_' . $attribute_type_key;

								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									} elseif ( 'array' == $attribute_type_value ) {
										$type = 'array';
									}

									if ( 'array' == $type ) {

										if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {

											if ( is_float( $mappedValue['default'] ) ) {
												$visible['Visible'][ $walmart_cat ][ $key ][][ $extra_parameter_key ] = (float) $mappedValue['default'];
											} elseif ( ctype_digit( $mappedValue['default'] ) ) {
												$visible['Visible'][ $walmart_cat ][ $key ][][ $extra_parameter_key ] = (int) $mappedValue['default'];
											} else {
												$visible['Visible'][ $walmart_cat ][ $key ][][ $extra_parameter_key ] = $mappedValue['default'];
											}
										} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {

											if ( is_array( $mappedValue['metakey'] ) ) {

												foreach ( $mappedValue['metakey'] as $_meta_key ) {

													if ( is_float( $_meta_key ) ) {

														$casted_value = (float) get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );

													} elseif ( ctype_digit( $_meta_key ) ) {
														$casted_value = (int) get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
													} else {
														$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
													}

													if ( ! empty( $casted_value ) ) {
														$visible['Visible'][ $walmart_cat ][ $key ][][ $extra_parameter_key ] = $casted_value;
														break;
													}
												}
											}
										}

										if ( empty( $visible['Visible'][ $walmart_cat ][ $key ][ $extra_parameter_key ] ) ) {
											unset( $visible['Visible'][ $walmart_cat ][ $key ][ $extra_parameter_key ] );

										}

										if ( empty( $visible['Visible'][ $walmart_cat ][ $key ][0] ) ) {
											unset( $visible['Visible'][ $walmart_cat ][ $key ] );

										}
									} else {

										if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
											$casted_value = $mappedValue['default'];

											settype( $casted_value, $type );
											$visible['Visible'][ $walmart_cat ][ $key ][ $extra_parameter_key ] = $casted_value;
										} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
											if ( is_array( $mappedValue['metakey'] ) ) {

												foreach ( $mappedValue['metakey'] as $_meta_key ) {
													$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );

													settype( $casted_value, $type );

													if ( ! empty( $casted_value ) ) {
														$visible['Visible'][ $walmart_cat ][ $key ][ $extra_parameter_key ] = $casted_value;
														break;

													}
												}
											}
										}

										if ( empty( $visible['Visible'][ $walmart_cat ][ $key ][ $extra_parameter_key ] ) ) {
											unset( $visible['Visible'][ $walmart_cat ][ $key ][ $extra_parameter_key ] );

										}

										if ( empty( $visible['Visible'][ $walmart_cat ][ $key ] ) ) {
											unset( $visible['Visible'][ $walmart_cat ][ $key ] );

										}
									}
								}
							}
						}
					}
				}

				if ( isset( $value['items'] ) ) {
					foreach ( $value['items']['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_cat ) . '_' . $attribute_type_key;
								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {

										if ( is_float( $mappedValue['default'] ) ) {
											$visible['Visible'][ $walmart_cat ][ $key ][0][ $extra_parameter_key ] = (float) $casted_value;
										} elseif ( ctype_digit( $mappedValue['default'] ) ) {
											$visible['Visible'][ $walmart_cat ][ $key ][0][ $extra_parameter_key ] = (int) $casted_value;
										} else {
											$visible['Visible'][ $walmart_cat ][ $key ][0][ $extra_parameter_key ] = $casted_value;
										}
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {

										if ( is_array( $mappedValue['metakey'] ) ) {

											foreach ( $mappedValue['metakey'] as $_meta_key ) {

												if ( is_float( $_meta_key ) ) {

													$casted_value = (float) get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );

												} elseif ( ctype_digit( $_meta_key ) ) {
													$casted_value = (int) get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
												} else {
													$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $_meta_key ), 1 );
												}

												if ( ! empty( $casted_value ) ) {
													$visible['Visible'][ $walmart_cat ][ $key ][0][ $extra_parameter_key ] = $casted_value;
													break;
												}
											}
										}
									}

									if ( empty( $visible['Visible'][ $walmart_cat ][ $key ][0][ $extra_parameter_key ] ) ) {
										unset( $visible['Visible'][ $walmart_cat ][ $key ][0][ $extra_parameter_key ] );

									}

									if ( empty( $visible['Visible'][ $walmart_cat ][ $key ][0] ) ) {
										unset( $visible['Visible'][ $walmart_cat ][ $key ] );

									}
								}
							}
						}
					}
				}
			}
		}
		unset( $visible['Visible'][ $walmart_cat ]['unit'] );
		unset( $visible['Visible'][ $walmart_cat ]['measure'] );

		if ( isset( $visible['Visible'][ $walmart_cat ]['labelImage'] ) ) {
			if ( empty( $visible['Visible'][ $walmart_cat ]['labelImage'][0]['labelImageURL'] ) ) {
				$visible['Visible'][ $walmart_cat ]['labelImage'][0]['labelImageURL'] = $image;
			}
		}

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

		// Additional images
		$gallery_images = array();
		if ( ! empty( $attachment_ids ) ) {
			foreach ( $attachment_ids as $attachment_id ) {
				if ( empty( wp_get_attachment_url( $attachment_id ) ) ) {
					continue;
				}
				$gallery_images[] = (string) wp_get_attachment_url( $attachment_id );
			}
		}

		$visible['Visible'][ $walmart_cat ]['shortDescription'] = $short_description;
		$visible['Visible'][ $walmart_cat ]['mainImageUrl']     = $image;
		if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
			$visible['Visible'][ $walmart_cat ]['productSecondaryImageURL'] = array_filter( $gallery_images );
		}

		// Adding Variation Group ID (i.e Parent SKU )
		if ( ! empty( $parent_id ) ) {
			$parent_sku = get_post_meta( $parent_id, '_sku', true );
			$visible['Visible'][ $walmart_cat ]['variantGroupId'] = $parent_sku;
			$variant_attribute                                    = $this->ced_walmart_fetch_variantAttributeNames( $product_id, $walmart_cat, $visible );
			if ( isset( $variant_attribute ) && ! empty( $variant_attribute ) ) {
				$visible['Visible'][ $walmart_cat ]['variantAttributeNames'] = $variant_attribute;
			}

			$visible['Visible'][ $walmart_cat ]['isPrimaryVariant'] = $primary_variant;
		}

		return $visible;

	}

	/**
	 * Ced_Walmart_Product ced_walmart_fetch_variantAttributeNames
	 *
	 * @since 2.0.0
	 * @param array       $mapped_cat.
	 * @param $product_id, $walmart_cat
	 */

	public function ced_walmart_fetch_variantAttributeNames( $product_id, $walmart_cat, $visible ) {
		$visible_variable_attribute = get_option( 'ced_walmart_cat_visible_variable_attr' . $walmart_cat );
		$visible_variable_attribute = json_decode( $visible_variable_attribute, 1 );
		$variant_attributes         = array();

		foreach ( $visible_variable_attribute as $key ) {

			foreach ( $visible['Visible'][ $walmart_cat ] as $existkey => $existvalue ) {
				$temp = array();
				if ( $key == $existkey ) {
					$variant_attributes[] = $key;
				}
			}
		}

		return $variant_attributes;
	}

	/**
	 * Ced_Walmart_Product ced_walmart_fetch_additionalProductAttributes
	 *
	 * @since 2.0.0
	 * @param array $product_id.
	 */
	public function ced_walmart_fetch_additionalProductAttributes( $product_id ) {
		$additonal_attribute = array();
		$_var_pro            = wc_get_product( $product_id );
		$var_attr            = $_var_pro->get_variation_attributes();
		foreach ( $var_attr as $key => $value ) {
			$temp                          = array();
			$temp['productAttributeName']  = ucwords(
				str_replace(
					array(
						'attribute_',
						'attribute_pa',
						'pa_',
					),
					'',
					$key
				)
			);
			$temp['productAttributeValue'] = ucwords( $value );
			$additonal_attribute[]         = $temp;
		}

		return $additonal_attribute;
	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_stock_data.
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */
	public function ced_walmart_prepare_stock_data( $walmart_product_ids = array() ) {
		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array = array();
		foreach ( $walmart_product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( is_object( $_product ) ) {
				$status = get_post_status( $product_id );
				if ( 'publish' == $status ) {
					$type = $_product->get_type();
					if ( 'variable' == $type ) {
						$variations = $_product->get_children();
						if ( is_array( $variations ) && ! empty( $variations ) ) {
							foreach ( $variations as $index => $variation_id ) {
								$product_data = $this->get_stock( $variation_id );
								if ( $product_data ) {
									$feed_array['Inventory'][] = $product_data;
								}
							}
						}
					} else {
						$product_data = $this->get_stock( $product_id );
						if ( $product_data ) {
							$feed_array['Inventory'][] = $product_data;
						}
					}
				}
			}
		}
		if ( ! empty( $feed_array ) ) {
			$feed_header    = array(
				'InventoryHeader' => array(
					'version' => '1.4',
				),
			);
			$feed_array     = array_merge( $feed_header, $feed_array );
			$wp_upload_dir  = wp_upload_dir() ['basedir'];
			$walmart_folder = $wp_upload_dir . '/walmart';
			if ( ! is_dir( $walmart_folder ) ) {
				mkdir( $walmart_folder, 0755 );
			}
			$filepath = $walmart_folder . '/stock-feed.json';
			file_put_contents( $filepath, json_encode( $feed_array ) );
			$filepath = wp_upload_dir() ['baseurl'] . '/walmart/stock-feed.json';
			return $feed_array;
		} else {
			return false;
		}
	}

	/**
	 * Ced_Walmart_Product get_stock.
	 *
	 * @since 1.0.0
	 * @param int $walmart_product_id.
	 */
	public function get_stock( $product_id ) {
		$is_uploaded_on_walmart = get_post_meta( $product_id, 'ced_walmart_product_uploaded' . wifw_environment(), true );
		if ( empty( $is_uploaded_on_walmart ) ) {
			return false;
		}

		$this->profile_assigned = true;

		$stock = get_post_meta( $product_id, '_custom_stock', true );
		if ( '' == $stock ) {
			$stock = (int) $this->fetch_meta_value_of_the_product( $product_id, 'global_stock', 'global' );
		}
		if ( '' == $stock ) {
			$stock = (int) get_post_meta( $product_id, '_stock', true );
		}
		if ( $stock < 0 ) {
			$stock = 0;
		}

		$sku = get_post_meta( $product_id, '_sku', true );
		if ( ! empty( $sku ) ) {
			$stock_data = array(
				'sku'      => (string) $sku,
				'quantity' => array(
					'unit'   => 'EACH',
					'amount' => $stock,
				),
			);
			return $stock_data;
		}
		return false;
	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_price_data.
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */
	public function ced_walmart_prepare_price_data( $walmart_product_ids = array() ) {
		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array = array();
		foreach ( $walmart_product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( is_object( $_product ) ) {
				$status = get_post_status( $product_id );
				if ( 'publish' == $status ) {
					$type = $_product->get_type();
					if ( 'variable' == $type ) {
						$variations = $_product->get_children();
						if ( is_array( $variations ) && ! empty( $variations ) ) {
							foreach ( $variations as $index => $variation_id ) {
								$product_data = $this->get_price( $variation_id );
								if ( $product_data ) {
									$feed_array['Price'][] = $product_data;
								}
							}
						}
					} else {
						$product_data = $this->get_price( $product_id );
						if ( $product_data ) {
							$feed_array['Price'][] = $product_data;
						}
					}
				}
			}
		}
		if ( ! empty( $feed_array ) ) {
			$feed_header    = array(
				'PriceHeader' => array(
					'version' => '1.7',
				),
			);
			$feed_array     = array_merge( $feed_header, $feed_array );
			$wp_upload_dir  = wp_upload_dir() ['basedir'];
			$walmart_folder = $wp_upload_dir . '/walmart';
			if ( ! is_dir( $walmart_folder ) ) {
				mkdir( $walmart_folder, 0755 );
			}
			$filepath = $walmart_folder . '/price-feed.json';
			file_put_contents( $filepath, json_encode( $feed_array ) );
			$filepath = wp_upload_dir() ['baseurl'] . '/walmart/price-feed.json';
			return $feed_array;
		} else {
			return false;
		}
	}

	/**
	 * Ced_Walmart_Product get_price.
	 *
	 * @since 1.0.0
	 * @param int $walmart_product_id.
	 */
	public function get_price( $product_id ) {
		$is_uploaded_on_walmart = get_post_meta( $product_id, 'ced_walmart_product_uploaded' . wifw_environment(), true );
		if ( empty( $is_uploaded_on_walmart ) ) {
			return false;
		}

		$this->profile_assigned = true;

		$price = get_post_meta( $product_id, '_custom_price', true );
		if ( empty( $price ) ) {
			$price = $this->fetch_meta_value_of_the_product( $product_id, 'global_price', 'global' );
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

		$sku = get_post_meta( $product_id, '_sku', true );
		if ( ! empty( $sku ) && ! empty( $price ) ) {
			$price_data = array(
				'sku'     => (string) $sku,
				'pricing' => array(
					array(
						'currentPrice'        => array(
							'currency' => 'USD',
							'amount'   => (float) $price,
						),
						'currentPriceType'    => 'REDUCED',
						'currentPriceType'    => 'REDUCED',
						'comparisonPriceType' => 'BASE',
						'comparisonPrice'     => array(
							'currency' => 'USD',
							'amount'   => (float) $price,
						),
					),
				),
			);
			return $price_data;
		}
		return false;
	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_wfs_data
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */

	public function ced_walmart_prepare_wfs_data( $walmart_product_ids = array() ) {
		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array              = array();
		$this->items_with_errors = false;
		$process_mode            = '';
		foreach ( $walmart_product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( is_object( $_product ) ) {
				$status = get_post_status( $product_id );
				if ( 'publish' == $status ) {

					$walmart_status = get_post_meta( $product_id, 'ced_walmart_product_uploaded' . wifw_environment(), true );
					if ( isset( $walmart_status ) && ! empty( $walmart_status ) ) {
						$product_state = get_post_meta( $product_id, 'ced_walmart_product_status' . wifw_environment(), true );
						if ( 'PUBLISHED' == $product_state ) {
							$walmart_wfs_cat = $this->ced_get_wfs_cat( $product_id, $upload_type = 'convert' );
							if ( $walmart_wfs_cat ) {
								$walmart_wfs_sub_cat = $this->ced_get_wfs_sub_cat( $product_id, $upload_type = 'convert' );

								$type = $_product->get_type();
								if ( 'variable' == $type ) {

									$variations = $_product->get_children();
									if ( is_array( $variations ) && ! empty( $variations ) ) {
										$primary_variant = 'Yes';
										foreach ( $variations as $index => $variation_id ) {
											$product_data = $this->get_formatted_data_wfs( $variation_id, $primary_variant, $walmart_wfs_cat, $process_mode, $walmart_wfs_sub_cat );
											if ( $product_data ) {
												$feed_array['SupplierItem'][] = $product_data;
											}
											$primary_variant = 'No';
										}
									}
								} else {
									$product_data = $this->get_formatted_data_wfs( $product_id, '', $walmart_wfs_cat, $process_mode, $walmart_wfs_sub_cat );
									if ( $product_data ) {
										$feed_array['SupplierItem'][] = $product_data;
									}
								}
							} else {
								continue;
							}
						}
					} else {
						continue;
					}
				}
			}
		}
		if ( ! empty( $feed_array ) ) {

			$walmart_wfs_cat = str_replace( ' ', '_', $walmart_wfs_cat );
			$feed_header     = array(
				'SupplierItemFeedHeader' => array(
					'subCategory'    => $walmart_wfs_sub_cat,
					'sellingChannel' => 'fbw',
					'processMode'    => 'REPLACE',
					'locale'         => 'en',
					'version'        => '1.3',
					'subset'         => 'EXTERNAL',
				),
			);
			$feed_array      = array_merge( $feed_header, $feed_array );
			return $feed_array;

		} else {
			return false;
		}

	}

	/**
	 * Ced_Walmart_Product ced_get_wfs_cat
	 *
	 * @since 1.0.0
	 * @param array $product_id.
	 */
	public function ced_get_wfs_cat( $product_id, $upload_type ) {
		$term_list = wp_get_post_terms(
			$product_id,
			'product_cat',
			array(
				'fields' => 'ids',
			)
		);
		$cat_id    = (int) $term_list[0];

		if ( 'convert' == $upload_type ) {
			$mapped_wfs_cat = get_option( 'ced_mapped_wfs_cat' );
			$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		} else {
			$mapped_wfs_cat = get_option( 'ced_mapped_wfs_new_item_cat' );
			$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		}

		foreach ( $mapped_wfs_cat['profile'] as $key => $value ) {
			if ( in_array( $cat_id, $value['woo_cat'] ) ) {
				$category = $key;
				return $category;
			}
		}

	}

	/**
	 * Ced_Walmart_Product ced_get_wfs_cat
	 *
	 * @since 1.0.0
	 * @param array $product_id.
	 */
	public function ced_get_wfs_sub_cat( $product_id, $upload_type ) {
		$term_list = wp_get_post_terms(
			$product_id,
			'product_cat',
			array(
				'fields' => 'ids',
			)
		);
		$cat_id    = (int) $term_list[0];

		if ( 'convert' == $upload_type ) {
			$walmart_wfs_sub_cat = get_term_meta( $cat_id, 'ced_walmart_wfs_subcategory', true );

		} else {
			$walmart_wfs_sub_cat = get_term_meta( $cat_id, 'ced_walmart_wfs_new_item_subcategory', true );
		}

		return $walmart_wfs_sub_cat;
	}

	public function get_formatted_data_wfs( $product_id = 0, $primary_variant = 'No', $walmart_wfs_cat = '', $process_mode = 'REPLACE', $walmart_wfs_sub_cat = '' ) {
		$_product     = wc_get_product( $product_id );
		$product_data = $_product->get_data();
		if ( 'variation' == $_product->get_type() ) {
			$parent_id  = $_product->get_parent_id();
			$parent_sku = get_post_meta( $parent_id, '_sku', true );
			if ( empty( $parent_sku ) ) {
				$parent_sku = $parent_id;
			}
		}
		$sku = get_post_meta( $product_id, '_sku', true );

		/** Identifier type */
		$idenitifier_type = get_post_meta( $product_id, '_custom_identifier_type', true );
		if ( empty( $idenitifier_type ) ) {
			$idenitifier_type = $this->fetch_meta_value_of_the_product( $product_id, 'global_identifier_type', 'global' );
		}

		/** Identifier value */
		$idenitifier_value = get_post_meta( $product_id, '_custom_identifier_value', true );
		if ( empty( $idenitifier_value ) ) {
			$idenitifier_value = $this->fetch_meta_value_of_the_product( $product_id, 'global_identifier_value', 'global' );
		}
		if ( 14 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'GTIN';
		} elseif ( 13 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'EAN';
		} elseif ( 12 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'UPC';
		}

		/** Product title */
		$product_name = get_post_meta( $product_id, '_custom_title', true );
		if ( empty( $product_name ) ) {
			$product_name = $this->fetch_meta_value_of_the_product( $product_id, 'global_title', 'global' );
		}
		if ( empty( $product_name ) ) {
			$product_name = $product_data['name'];
		}

		/** Product price */
		$price = (float) get_post_meta( $product_id, '_custom_price', true );
		if ( empty( $price ) ) {
			$price = (float) $this->fetch_meta_value_of_the_product( $product_id, 'global_price', 'global' );
		}
		if ( empty( $price ) ) {
			$price = (float) get_post_meta( $product_id, '_price', true );
		}

		/** Product brand */
		$brand = get_post_meta( $product_id, '_custom_product_brand', true );
		if ( empty( $brand ) ) {
			$brand = $this->fetch_meta_value_of_the_product( $product_id, 'global_product_brand', 'global' );
		}
		if ( empty( $brand ) ) {
			$brand = 'Unbranded';
		}

		/** Product tax code */
		$product_tax_code = get_post_meta( $product_id, '_custom_product_taxcode', true );
		if ( empty( $product_tax_code ) ) {
			$product_tax_code = $this->fetch_meta_value_of_the_product( $product_id, 'global_product_taxcode', 'global' );
		}
		$orderable = $this->fetch_orderable_data( $product_id, $walmart_wfs_cat, $sku, $product_name, $brand, $product_tax_code, $idenitifier_value, $idenitifier_type, $price );

		$visible   = $this->fetch_visible_data( $product_id, $walmart_wfs_cat );
		$tradeItem = $this->fetch_tradeItem_data( $product_id, $walmart_wfs_cat, $sku );

		$data = array_merge( $orderable, $visible, $tradeItem );

		$temp_arr['SupplierItem'][] = $data;

		$temp_header_validation = array(
			'SupplierItemFeedHeader' => array(
				'subCategory'    => $walmart_wfs_sub_cat,
				'sellingChannel' => 'fbw',
				'processMode'    => 'REPLACE',
				'locale'         => 'en',
				'version'        => '1.3',
				'subset'         => 'EXTERNAL',
			),
		);

		$validate_temp_array = array_merge( $temp_header_validation, $temp_arr );

		$this->validation_errors = false;
		$filepath                = CED_WALMART_DIRPATH . 'admin/walmart/lib/feeds/product-validation.json';
		file_put_contents( $filepath, json_encode( $validate_temp_array ) );
		if ( ! $this->validate_json_schema( $filepath, 'ConvertSpecRequest', $secondarypath = 'schema/wfs_convert' ) ) {
			if ( 'variation' == $_product->get_type() ) {
				$parent_id = $_product->get_parent_id();
				update_post_meta( $parent_id, '_validation_erros_wfs', $this->validation_errors );
			} else {
				update_post_meta( $product_id, '_validation_erros_wfs', $this->validation_errors );
			}
			return false;
		} else {
			if ( 'variation' == $_product->get_type() ) {
				$parent_id = $_product->get_parent_id();
				delete_post_meta( $parent_id, '_validation_erros_wfs' );
			} else {
				delete_post_meta( $product_id, '_validation_erros_wfs' );
			}
			$wp_upload_dir  = wp_upload_dir() ['basedir'];
			$walmart_folder = $wp_upload_dir . '/walmart';
			if ( ! is_dir( $walmart_folder ) ) {
				mkdir( $walmart_folder, 0755 );
			}
			$filepath = $walmart_folder . '/convert-wfs-feed.json';
			file_put_contents( $filepath, json_encode( $feed_array ) );
			$filepath = wp_upload_dir() ['baseurl'] . '/walmart/convert-wfs-feed.json';
			return $data;
		}

	}

	public function fetch_orderable_data( $product_id, $walmart_wfs_cat, $sku, $product_name, $brand, $product_tax_code, $idenitifier_value, $idenitifier_type, $price ) {
		$all_wfs_profile_orderable = get_option( 'ced_walmart_wfs_orderable_' . $walmart_wfs_cat );
		$all_wfs_profile_orderable = json_decode( $all_wfs_profile_orderable, 1 );

		$attribute_type = get_option( 'ced_wfs_type_attribute_' . $walmart_wfs_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_wfs_cat = get_option( 'ced_mapped_wfs_cat' );
		$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		$convertData = json_decode( $mapped_wfs_cat['profile'][ $walmart_wfs_cat ]['profile_data'], 1 );

		$orderable                             = array();
		$orderable['Orderable']['sku']         = $sku;
		$orderable['Orderable']['productName'] = $product_name;
		$orderable['Orderable']['brand']       = $brand;
		$orderable['Orderable']['price']       = floatval( $price );
		// $orderable['Orderable']['productTaxCode']       = intval( $product_tax_code );
		$orderable['Orderable']['productIdentifiers'] = array(
			'productId'     => $idenitifier_value,
			'productIdType' => $idenitifier_type,
		);
		foreach ( $all_wfs_profile_orderable as $key => $value ) {
			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {
					foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {

						if ( $attribute_type_key == $key ) {

							$type = 'string';
							if ( 'integer' == $attribute_type_value ) {
								$type = 'integer';
							} elseif ( 'number' == $attribute_type_value ) {
								$type = 'float';
							}

							if ( 'safetyDataSheet' == $key ) {

								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value                     = $mappedValue['default'];
									$orderable['Orderable'][ $key ][] = $casted_value;

								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value                     = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									$orderable['Orderable'][ $key ][] = $casted_value;
								}

								if ( empty( $orderable['Orderable'][ $key ][0] ) ) {
									unset( $orderable['Orderable'][ $key ] );
								}
							} elseif ( 'StreetDate' == $key || 'startDate' == $key || 'endDate' == $key ) {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$date                           = $mappedValue['default'];
									$date                           = gmdate( DATE_ISO8601, strtotime( $date ) );
									$orderable['Orderable'][ $key ] = $date;

								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$date                           = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									$date                           = gmdate( DATE_ISO8601, strtotime( $date ) );
									$orderable['Orderable'][ $key ] = $date;
								}

								if ( empty( $orderable['Orderable'][ $key ] ) ) {
									unset( $orderable['Orderable'][ $key ] );
								}
							} else {

								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value = $mappedValue['default'];
									settype( $casted_value, $type );
									$orderable['Orderable'][ $key ] = $casted_value;

								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									settype( $casted_value, $type );
									$orderable['Orderable'][ $key ] = $casted_value;
								}
							}

							if ( empty( $orderable['Orderable'][ $key ] ) ) {
								unset( $orderable['Orderable'][ $key ] );
							}
						}
					}
				}

				if ( isset( $value['properties'] ) ) {

					foreach ( $value['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;

								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];
										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $orderable['Orderable'][ $key ][ $extra_parameter_key ] ) ) {
										unset( $orderable['Orderable'][ $key ][ $extra_parameter_key ] );

									}

									if ( empty( $orderable['Orderable'][ $key ] ) ) {
										unset( $orderable['Orderable'][ $key ] );

									}
								}
							}
						}
					}
				}
				if ( isset( $value['items'] ) ) {

					foreach ( $value['items']['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;

								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];

										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][0][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][0][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $orderable['Orderable'][ $key ][0][ $extra_parameter_key ] ) ) {
										unset( $orderable['Orderable'][ $key ][0][ $extra_parameter_key ] );

									}

									if ( empty( $orderable['Orderable'][ $key ][0] ) ) {
										unset( $orderable['Orderable'][ $key ][0] );

									}
								}
							}
						}
					}
				}
			}
		}
		return $orderable;

	}

	public function fetch_visible_data( $product_id, $walmart_wfs_cat ) {

		$all_wfs_profile_visible = get_option( 'ced_walmart_wfs_visible_' . $walmart_wfs_cat );
		$all_wfs_profile_visible = json_decode( $all_wfs_profile_visible, 1 );

		$attribute_type = get_option( 'ced_wfs_type_attribute_' . $walmart_wfs_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_wfs_cat = get_option( 'ced_mapped_wfs_cat' );
		$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		$convertData = json_decode( $mapped_wfs_cat['profile'][ $walmart_wfs_cat ]['profile_data'], 1 );
		$visible     = array();
		$_product    = wc_get_product( $product_id );
		/** Product images */
		if ( 'variation' == $_product->get_type() ) {
			$image = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) : '';
		} else {
			$image = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) : '';
		}

		$visible['Visible'][ $walmart_wfs_cat ]['mainImageUrl'] = $image;
		foreach ( $all_wfs_profile_visible as $key => $value ) {
			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {

					if ( 'productSecondaryImageURL' == $key || 'smallPartsWarnings' == $key ) {
						if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
							$visible['Visible'][ $walmart_wfs_cat ][ $key ][] = $mappedValue['default'];
						} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
							$visible['Visible'][ $walmart_wfs_cat ][ $key ][] = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
						}

						if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0] ) ) {
							unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

						}
					} else {
						if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
							$visible['Visible'][ $walmart_wfs_cat ][ $key ] = $mappedValue['default'];
						} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
							$visible['Visible'][ $walmart_wfs_cat ][ $key ] = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
						}
					}

					if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ] ) ) {
						unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

					}
				}

				if ( isset( $value['items'] ) ) {
					foreach ( $value['items']['properties'] as $extra_parameter_key => $extra_parameter_value ) {
						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;
								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];
										settype( $casted_value, $type );
										$visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] ) ) {
										unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] );

									}

									if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0] ) ) {
										unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0] );

									}
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $visible['Visible'][ $walmart_wfs_cat ]['labelImage'][0]['labelImageURL'] ) ) {
			$visible['Visible'][ $walmart_wfs_cat ]['labelImage'][0]['labelImageURL'] = $image;
		}
		return $visible;

	}

	public function fetch_tradeItem_data( $product_id, $walmart_wfs_cat, $sku ) {
		$all_wfs_profile_tradeItem = get_option( 'ced_walmart_wfs_tradeItem_' . $walmart_wfs_cat );
		$all_wfs_profile_tradeItem = json_decode( $all_wfs_profile_tradeItem, 1 );

		$attribute_type = get_option( 'ced_wfs_type_attribute_' . $walmart_wfs_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_wfs_cat = get_option( 'ced_mapped_wfs_cat' );
		$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		$convertData = json_decode( $mapped_wfs_cat['profile'][ $walmart_wfs_cat ]['profile_data'], 1 );
		$tradeItem   = array();

		$tradeItem['TradeItem']['sku'] = $sku;
		foreach ( $all_wfs_profile_tradeItem as $key => $value ) {

			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {
					foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
						if ( $attribute_type_key == $key ) {
							$type = 'string';
							if ( 'integer' == $attribute_type_value ) {
								$type = 'integer';
							} elseif ( 'number' == $attribute_type_value ) {
								$type = 'float';
							}
							if ( 'countryOfOriginAssembly' == $key ) {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value                     = $mappedValue['default'];
									$tradeItem['TradeItem'][ $key ][] = $casted_value;
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value                     = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									$tradeItem['TradeItem'][ $key ][] = $casted_value;
								}
							} else {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value = $mappedValue['default'];
									settype( $casted_value, $type );
									$tradeItem['TradeItem'][ $key ] = $casted_value;
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									settype( $casted_value, $type );
									$tradeItem['TradeItem'][ $key ] = $casted_value;
								}
							}

							if ( empty( $tradeItem['TradeItem'][ $key ] ) ) {
								unset( $tradeItem['TradeItem'][ $key ] );

							}
						}
					}
				}

				if ( isset( $value['properties'] ) ) {
					foreach ( $value['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;

						if ( $category_formatted == $mappedKey ) {
							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;
								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];
										settype( $casted_value, $type );
										$tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] ) ) {
										unset( $tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] );

									}

									if ( empty( $tradeItem['TradeItem'][ $key ] ) ) {
										unset( $tradeItem['TradeItem'][ $key ] );

									}
								}
							}
						}
					}
				}
			}
		}

		return $tradeItem;

	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_wfs_data
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */

	public function ced_walmart_prepare_wfs_new_item_data( $walmart_product_ids = array() ) {
		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array              = array();
		$this->items_with_errors = false;
		$process_mode            = '';
		foreach ( $walmart_product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( is_object( $_product ) ) {
				$status = get_post_status( $product_id );
				if ( 'publish' == $status ) {
					$walmart_wfs_cat = $this->ced_get_wfs_cat( $product_id, $upload_type = 'new' );
					if ( $walmart_wfs_cat ) {
						$walmart_wfs_sub_cat = $this->ced_get_wfs_sub_cat( $product_id, $upload_type = 'new' );

						$type = $_product->get_type();
						if ( 'variable' == $type ) {

							$variations = $_product->get_children();
							if ( is_array( $variations ) && ! empty( $variations ) ) {
								$primary_variant = 'Yes';
								foreach ( $variations as $index => $variation_id ) {
									$product_data = $this->get_formatted_data_new_item_wfs( $variation_id, $primary_variant, $walmart_wfs_cat, $process_mode, $walmart_wfs_sub_cat );
									if ( $product_data ) {
										$feed_array['MPItem'][] = $product_data;
									}
									$primary_variant = 'No';
								}
							}
						} else {
							$product_data = $this->get_formatted_data_new_item_wfs( $product_id, '', $walmart_wfs_cat, $process_mode, $walmart_wfs_sub_cat );
							if ( $product_data ) {
								$feed_array['MPItem'][] = $product_data;
							}
						}
					} else {
						continue;
					}
				}
			}
		}
		if ( ! empty( $feed_array ) ) {

			$walmart_wfs_cat = str_replace( ' ', '_', $walmart_wfs_cat );
			$feed_header     = array(
				'MPItemFeedHeader' => array(
					'subCategory'    => $walmart_wfs_sub_cat,
					'sellingChannel' => 'marketplacewfs',
					'processMode'    => 'REPLACE',
					'locale'         => 'en',
					'version'        => '1.1',
					'subset'         => 'EXTERNAL',
				),
			);
			$feed_array      = array_merge( $feed_header, $feed_array );
			return $feed_array;

		} else {
			return false;
		}

	}

	public function get_formatted_data_new_item_wfs( $product_id = 0, $primary_variant = 'No', $walmart_wfs_cat = '', $process_mode = 'REPLACE', $walmart_wfs_sub_cat = '' ) {
		$_product = wc_get_product( $product_id );

		$product_data = $_product->get_data();
		if ( 'variation' == $_product->get_type() ) {
			$parent_id  = $_product->get_parent_id();
			$parent_sku = get_post_meta( $parent_id, '_sku', true );
			if ( empty( $parent_sku ) ) {
				$parent_sku = $parent_id;
			}
		}
		$sku = get_post_meta( $product_id, '_sku', true );

		/** Identifier type */
		$idenitifier_type = get_post_meta( $product_id, '_custom_identifier_type', true );
		if ( empty( $idenitifier_type ) ) {
			$idenitifier_type = $this->fetch_meta_value_of_the_product( $product_id, 'global_identifier_type', 'global' );
		}

		/** Identifier value */
		$idenitifier_value = get_post_meta( $product_id, '_custom_identifier_value', true );
		if ( empty( $idenitifier_value ) ) {
			$idenitifier_value = $this->fetch_meta_value_of_the_product( $product_id, 'global_identifier_value', 'global' );
		}
		if ( 14 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'GTIN';
		} elseif ( 13 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'EAN';
		} elseif ( 12 == strlen( $idenitifier_value ) ) {
			$idenitifier_type = 'UPC';
		}

		/** Product title */
		$product_name = get_post_meta( $product_id, '_custom_title', true );
		if ( empty( $product_name ) ) {
			$product_name = $this->fetch_meta_value_of_the_product( $product_id, 'global_title', 'global' );
		}
		if ( empty( $product_name ) ) {
			$product_name = $product_data['name'];
		}

		/** Product price */
		$price = (float) get_post_meta( $product_id, '_custom_price', true );
		if ( empty( $price ) ) {
			$price = (float) $this->fetch_meta_value_of_the_product( $product_id, 'global_price', 'global' );
		}
		if ( empty( $price ) ) {
			$price = (float) get_post_meta( $product_id, '_price', true );
		}

		/** Product brand */
		$brand = get_post_meta( $product_id, '_custom_product_brand', true );
		if ( empty( $brand ) ) {
			$brand = $this->fetch_meta_value_of_the_product( $product_id, 'global_product_brand', 'global' );
		}
		if ( empty( $brand ) ) {
			$brand = 'Unbranded';
		}

		/** Product tax code */
		$product_tax_code = get_post_meta( $product_id, '_custom_product_taxcode', true );

		/** Product description */
		$short_description = get_post_meta( $product_id, '_custom_description', true );
		if ( empty( $short_description ) ) {
			$short_description = $this->fetch_meta_value_of_the_product( $product_id, 'global_description', 'global' );
		}
		if ( empty( $short_description ) && 'variation' == $_product->get_type() ) {
			$_parent           = wc_get_product( $_product->get_parent_id() );
			$parent_data       = $_parent->get_data();
			$short_description = $parent_data['description'];
			if ( empty( $short_description ) ) {
				$short_description = $parent_data['short_description'];
			}
		} elseif ( empty( $short_description ) ) {
			$short_description = $product_data['description'];
			if ( empty( $short_description ) ) {
				$short_description = $product_data['short_description'];
			}
		}
		$short_description = preg_replace( '/\[.*?\]/', '', $short_description );
		$short_description = substr( $short_description, 0, 3999 );

		if ( empty( $product_tax_code ) ) {
			$product_tax_code = $this->fetch_meta_value_of_the_product( $product_id, 'global_product_taxcode', 'global' );
		}
		$orderable = $this->fetch_orderable_new_item_data( $product_id, $walmart_wfs_cat, $sku, $product_name, $brand, $product_tax_code, $idenitifier_value, $idenitifier_type, $price );

		$visible   = $this->fetch_visible_new_item_data( $product_id, $walmart_wfs_cat, $short_description );
		$tradeItem = $this->fetch_tradeItem_new_item_data( $product_id, $walmart_wfs_cat, $sku );

		$data = array_merge( $orderable, $visible, $tradeItem );

		$temp_arr['MPItem'][] = $data;

		$temp_header_validation = array(
			'MPItemFeedHeader' => array(
				'subCategory'    => $walmart_wfs_sub_cat,
				'sellingChannel' => 'marketplacewfs',
				'processMode'    => 'REPLACE',
				'locale'         => 'en',
				'version'        => '1.1',
				'subset'         => 'EXTERNAL',
			),
		);

		$validate_temp_array = array_merge( $temp_header_validation, $temp_arr );

		$this->validation_errors = false;
		$filepath                = CED_WALMART_DIRPATH . 'admin/walmart/lib/feeds/product-validation.json';
		file_put_contents( $filepath, json_encode( $validate_temp_array ) );
		if ( ! $this->validate_json_schema( $filepath, 'MP_WFS_ITEM_SPEC', $secondarypath = 'schema/wfs_convert' ) ) {
			if ( 'variation' == $_product->get_type() ) {
				$parent_id = $_product->get_parent_id();
				update_post_meta( $parent_id, '_validation_erros_wfs', $this->validation_errors );
			} else {
				update_post_meta( $product_id, '_validation_erros_wfs', $this->validation_errors );
			}
			return false;
		} else {
			if ( 'variation' == $_product->get_type() ) {
				$parent_id = $_product->get_parent_id();
				delete_post_meta( $parent_id, '_validation_erros_wfs' );
			} else {
				delete_post_meta( $product_id, '_validation_erros_wfs' );
			}
			$wp_upload_dir  = wp_upload_dir() ['basedir'];
			$walmart_folder = $wp_upload_dir . '/walmart';
			if ( ! is_dir( $walmart_folder ) ) {
				mkdir( $walmart_folder, 0755 );
			}
			$filepath = $walmart_folder . '/convert-wfs-new-item-feed.json';
			file_put_contents( $filepath, json_encode( $feed_array ) );
			$filepath = wp_upload_dir() ['baseurl'] . '/walmart/convert-wfs-new-item-feed.json';
			return $data;
		}

	}

	public function fetch_orderable_new_item_data( $product_id, $walmart_wfs_cat, $sku, $product_name, $brand, $product_tax_code, $idenitifier_value, $idenitifier_type, $price ) {
		$all_wfs_profile_orderable = get_option( 'ced_walmart_wfs_new_item_orderable_' . $walmart_wfs_cat );
		$all_wfs_profile_orderable = json_decode( $all_wfs_profile_orderable, 1 );

		$attribute_type = get_option( 'ced_wfs_new_item_type_attribute_' . $walmart_wfs_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_wfs_cat = get_option( 'ced_mapped_wfs_new_item_cat' );
		$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		$convertData = json_decode( $mapped_wfs_cat['profile'][ $walmart_wfs_cat ]['profile_data'], 1 );

		$orderable                             = array();
		$orderable['Orderable']['sku']         = $sku;
		$orderable['Orderable']['productName'] = $product_name;
		$orderable['Orderable']['brand']       = $brand;
		$orderable['Orderable']['price']       = floatval( $price );
		// $orderable['Orderable']['productTaxCode']       = intval( $product_tax_code );
		$orderable['Orderable']['productIdentifiers'] = array(
			'productId'     => $idenitifier_value,
			'productIdType' => $idenitifier_type,
		);
		foreach ( $all_wfs_profile_orderable as $key => $value ) {
			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {
					foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {

						if ( $attribute_type_key == $key ) {

							$type = 'string';
							if ( 'integer' == $attribute_type_value ) {
								$type = 'integer';
							} elseif ( 'number' == $attribute_type_value ) {
								$type = 'float';
							}

							if ( 'safetyDataSheet' == $key ) {

								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value                     = $mappedValue['default'];
									$orderable['Orderable'][ $key ][] = $casted_value;

								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value                     = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									$orderable['Orderable'][ $key ][] = $casted_value;
								}

								if ( empty( $orderable['Orderable'][ $key ][0] ) ) {
									unset( $orderable['Orderable'][ $key ] );
								}
							} elseif ( 'StreetDate' == $key || 'startDate' == $key || 'endDate' == $key ) {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$date                           = $mappedValue['default'];
									$date                           = gmdate( DATE_ISO8601, strtotime( $date ) );
									$orderable['Orderable'][ $key ] = $date;

								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$date                           = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									$date                           = gmdate( DATE_ISO8601, strtotime( $date ) );
									$orderable['Orderable'][ $key ] = $date;
								}

								if ( empty( $orderable['Orderable'][ $key ] ) ) {
									unset( $orderable['Orderable'][ $key ] );
								}
							} else {

								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value = $mappedValue['default'];
									settype( $casted_value, $type );
									$orderable['Orderable'][ $key ] = $casted_value;

								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									settype( $casted_value, $type );
									$orderable['Orderable'][ $key ] = $casted_value;
								}
							}

							if ( empty( $orderable['Orderable'][ $key ] ) ) {
								unset( $orderable['Orderable'][ $key ] );
							}
						}
					}
				}

				if ( isset( $value['properties'] ) ) {

					foreach ( $value['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;

								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];
										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $orderable['Orderable'][ $key ][ $extra_parameter_key ] ) ) {
										unset( $orderable['Orderable'][ $key ][ $extra_parameter_key ] );

									}

									if ( empty( $orderable['Orderable'][ $key ] ) ) {
										unset( $orderable['Orderable'][ $key ] );

									}
								}
							}
						}
					}
				}
				if ( isset( $value['items'] ) ) {

					foreach ( $value['items']['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;

								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];

										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][0][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$orderable['Orderable'][ $key ][0][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $orderable['Orderable'][ $key ][0][ $extra_parameter_key ] ) ) {
										unset( $orderable['Orderable'][ $key ][0][ $extra_parameter_key ] );

									}

									if ( empty( $orderable['Orderable'][ $key ][0] ) ) {
										unset( $orderable['Orderable'][ $key ][0] );

									}
								}
							}
						}
					}
				}
			}
		}
		return $orderable;

	}

	public function fetch_visible_new_item_data( $product_id, $walmart_wfs_cat, $short_description = '' ) {

		$all_wfs_profile_visible = get_option( 'ced_walmart_wfs_new_item_visible_' . $walmart_wfs_cat );
		$all_wfs_profile_visible = json_decode( $all_wfs_profile_visible, 1 );

		$attribute_type = get_option( 'ced_wfs_new_item_type_attribute_' . $walmart_wfs_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_wfs_cat = get_option( 'ced_mapped_wfs_new_item_cat' );
		$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		$convertData = json_decode( $mapped_wfs_cat['profile'][ $walmart_wfs_cat ]['profile_data'], 1 );
		$visible     = array();
		$_product    = wc_get_product( $product_id );

		/** Product images */
		if ( 'variation' == $_product->get_type() ) {
			$image = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) : '';
		} else {
			$image = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'full' ) : '';
		}
		$visible['Visible'][ $walmart_wfs_cat ]['shortDescription'] = $short_description;
		$visible['Visible'][ $walmart_wfs_cat ]['mainImageUrl']     = $image;

		foreach ( $all_wfs_profile_visible as $key => $value ) {
			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {

					foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
						$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;

						if ( $attribute_type_key == $mappedKey ) {
							$type = 'string';
							if ( 'integer' == $attribute_type_value ) {
								$type = 'integer';
							} elseif ( 'number' == $attribute_type_value ) {
								$type = 'float';
							} elseif ( 'array' == $attribute_type_value ) {
								$type = 'array';
							}

							if ( 'array' == $type ) {

								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$visible['Visible'][ $walmart_wfs_cat ][ $key ][] = (float) $mappedValue['default'];
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$visible['Visible'][ $walmart_wfs_cat ][ $key ][] = (float) get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
								}

								if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0] ) ) {
									unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

								}
							} else {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value = $mappedValue['default'];
									settype( $casted_value, $type );
									$visible['Visible'][ $walmart_wfs_cat ][ $key ] = $casted_value;
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									settype( $casted_value, $type );
									$visible['Visible'][ $walmart_wfs_cat ][ $key ] = $casted_value;
								}
							}

							if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ] ) ) {
								unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

							}
						}
					}
				}

				if ( isset( $value['properties'] ) ) {

					foreach ( $value['properties'] as $extra_parameter_key => $extra_parameter_value ) {
						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;

								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									} elseif ( 'array' == $attribute_type_value ) {
										$type = 'array';
									}

									if ( 'array' == $type ) {

										if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
											$casted_value = $mappedValue['default'];
											$visible['Visible'][ $walmart_wfs_cat ][ $key ][][ $extra_parameter_key ] = (float) $casted_value;
										} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
											$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
											$visible['Visible'][ $walmart_wfs_cat ][ $key ][][ $extra_parameter_key ] = float( $casted_value );
										}

										if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][ $extra_parameter_key ] ) ) {
											unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ][ $extra_parameter_key ] );

										}

										if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0] ) ) {
											unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

										}
									} else {

										if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
											$casted_value = $mappedValue['default'];
											settype( $casted_value, $type );
											$visible['Visible'][ $walmart_wfs_cat ][ $key ][ $extra_parameter_key ] = $casted_value;
										} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
											$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
											settype( $casted_value, $type );
											$visible['Visible'][ $walmart_wfs_cat ][ $extra_parameter_key ] = $casted_value;
										}

										if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][ $extra_parameter_key ] ) ) {
											unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ][ $extra_parameter_key ] );

										}

										if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ] ) ) {
											unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

										}
									}
								}
							}
						}
					}
				}

				if ( isset( $value['items'] ) ) {
					foreach ( $value['items']['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;
						if ( $category_formatted == $mappedKey ) {

							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;
								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];
										settype( $casted_value, $type );
										$visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] ) ) {
										unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0][ $extra_parameter_key ] );

									}

									if ( empty( $visible['Visible'][ $walmart_wfs_cat ][ $key ][0] ) ) {
										unset( $visible['Visible'][ $walmart_wfs_cat ][ $key ] );

									}
								}
							}
						}
					}
				}
			}
		}
		unset( $visible['Visible'][ $walmart_wfs_cat ]['unit'] );
		unset( $visible['Visible'][ $walmart_wfs_cat ]['measure'] );
		if ( empty( $visible['Visible'][ $walmart_wfs_cat ]['labelImage'][0]['labelImageURL'] ) ) {
			$visible['Visible'][ $walmart_wfs_cat ]['labelImage'][0]['labelImageURL'] = $image;
		}

		return $visible;

	}

	public function fetch_tradeItem_new_item_data( $product_id, $walmart_wfs_cat, $sku ) {
		$all_wfs_profile_tradeItem = get_option( 'ced_walmart_wfs_new_item_tradeItem_' . $walmart_wfs_cat );
		$all_wfs_profile_tradeItem = json_decode( $all_wfs_profile_tradeItem, 1 );

		$attribute_type = get_option( 'ced_wfs_new_item_type_attribute_' . $walmart_wfs_cat );
		$attribute_type = json_decode( $attribute_type, 1 );

		$mapped_wfs_cat = get_option( 'ced_mapped_wfs_new_item_cat' );
		$mapped_wfs_cat = json_decode( $mapped_wfs_cat, 1 );

		$convertData = json_decode( $mapped_wfs_cat['profile'][ $walmart_wfs_cat ]['profile_data'], 1 );
		$tradeItem   = array();

		$tradeItem['TradeItem']['sku'] = $sku;
		foreach ( $all_wfs_profile_tradeItem as $key => $value ) {

			foreach ( $convertData as $mappedKey => $mappedValue ) {
				$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key;
				if ( $category_formatted == $mappedKey ) {
					foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
						if ( $attribute_type_key == $key ) {
							$type = 'string';
							if ( 'integer' == $attribute_type_value ) {
								$type = 'integer';
							} elseif ( 'number' == $attribute_type_value ) {
								$type = 'float';
							}
							if ( 'countryOfOriginAssembly' == $key ) {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value                     = $mappedValue['default'];
									$tradeItem['TradeItem'][ $key ][] = $casted_value;
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value                     = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									$tradeItem['TradeItem'][ $key ][] = $casted_value;
								}
							} else {
								if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
									$casted_value = $mappedValue['default'];
									settype( $casted_value, $type );
									$tradeItem['TradeItem'][ $key ] = $casted_value;
								} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
									$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
									settype( $casted_value, $type );
									$tradeItem['TradeItem'][ $key ] = $casted_value;
								}
							}

							if ( empty( $tradeItem['TradeItem'][ $key ] ) ) {
								unset( $tradeItem['TradeItem'][ $key ] );

							}
						}
					}
				}

				if ( isset( $value['properties'] ) ) {
					foreach ( $value['properties'] as $extra_parameter_key => $extra_parameter_value ) {

						$category_formatted = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $key . '_' . $extra_parameter_key;

						if ( $category_formatted == $mappedKey ) {
							foreach ( $attribute_type as $attribute_type_key => $attribute_type_value ) {
								$attribute_type_key = str_replace( ' ', '', $walmart_wfs_cat ) . '_' . $attribute_type_key;
								if ( $attribute_type_key == $mappedKey ) {
									$type = 'string';
									if ( 'integer' == $attribute_type_value ) {
										$type = 'integer';
									} elseif ( 'number' == $attribute_type_value ) {
										$type = 'float';
									}

									if ( isset( $mappedValue['default'] ) && ! empty( $mappedValue['default'] ) ) {
										$casted_value = $mappedValue['default'];
										settype( $casted_value, $type );
										$tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] = $casted_value;
									} elseif ( isset( $mappedValue['metakey'] ) && ! empty( $mappedValue['metakey'] ) ) {
										$casted_value = get_post_meta( $product_id, str_replace( ' ', '', $mappedValue['metakey'] ), 1 );
										settype( $casted_value, $type );
										$tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] = $casted_value;
									}

									if ( empty( $tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] ) ) {
										unset( $tradeItem['TradeItem'][ $key ][ $extra_parameter_key ] );

									}

									if ( empty( $tradeItem['TradeItem'][ $key ] ) ) {
										unset( $tradeItem['TradeItem'][ $key ] );

									}
								}
							}
						}
					}
				}
			}
		}

		return $tradeItem;

	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_shipping_template_data.
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */
	public function ced_walmart_prepare_shipping_template_data( $walmart_product_ids = array(), $action = 'Add' ) {
		$this->items_with_errors = false;
		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array = array();
		foreach ( $walmart_product_ids as $product_id ) {
			$_product             = wc_get_product( $product_id );
			$shipping_template_id = get_post_meta( $product_id, '_custom_shipping_template', 1 );
			$fulfillment_id       = get_post_meta( $product_id, '_custom_fulfillment_center', 1 );

			if ( empty( $shipping_template_id ) ) {
				$shipping_template_id = $this->fetch_meta_value_of_the_product( $product_id, 'global_shipping_template', 'global' );
			}

			if ( empty( $fulfillment_id ) ) {
				$fulfillment_id = $this->fetch_meta_value_of_the_product( $product_id, 'global_fulfillment_center', 'global' );
			}

			if ( ! empty( $shipping_template_id ) && ! empty( $fulfillment_id ) ) {
				if ( is_object( $_product ) ) {
					$status = get_post_status( $product_id );
					if ( 'publish' == $status ) {

						$walmart_status = get_post_meta( $product_id, 'ced_walmart_product_uploaded' . wifw_environment(), true );
						if ( isset( $walmart_status ) && ! empty( $walmart_status ) ) {
							$product_state = get_post_meta( $product_id, 'ced_walmart_product_status' . wifw_environment(), true );
							if ( 'PUBLISHED' == $product_state ) {

								$type = $_product->get_type();
								if ( 'variable' == $type ) {
									$product_data = array();
									$variations   = $_product->get_children();
									if ( is_array( $variations ) && ! empty( $variations ) ) {
										foreach ( $variations as $index => $variation_id ) {
											$variation_sku                   = get_post_meta( $variation_id, '_sku', 1 );
											$product_data['PreciseDelivery'] = array(
												'sku' => $variation_sku,
												'actionType' => $action,
												'shippingTemplateId' => $shipping_template_id,
												'fulfillmentCenterId' => $fulfillment_id,
											);
											if ( $product_data ) {
												$feed_array['Item'][] = $product_data;
											}
										}
									}
								} else {
									$product_sku                     = $_product->get_sku();
									$product_data['PreciseDelivery'] = array(
										'sku'        => $product_sku,
										'actionType' => $action,
										'shippingTemplateId' => $shipping_template_id,
										'fulfillmentCenterId' => $fulfillment_id,
									);
									if ( $product_data ) {
										$feed_array['Item'][] = $product_data;
									}
								}
							}
						} else {
							continue;
						}
					}
				}
			}
		}
		if ( ! empty( $feed_array ) ) {
			$feed_header    = array(
				'ItemFeedHeader' => array(
					'sellingChannel' => 'precisedelivery',
					'locale'         => 'en',
					'version'        => '1.0',
				),
			);
			$feed_array     = array_merge( $feed_header, $feed_array );
			$wp_upload_dir  = wp_upload_dir() ['basedir'];
			$walmart_folder = $wp_upload_dir . '/walmart';
			if ( ! is_dir( $walmart_folder ) ) {
				mkdir( $walmart_folder, 0755 );
			}
			$filepath = $walmart_folder . '/shipping-template-feed.json';
			file_put_contents( $filepath, json_encode( $feed_array ) );
			$filepath = wp_upload_dir() ['baseurl'] . '/walmart/shipping-template-feed.json';
			return $feed_array;
		} else {
			return false;
		}
	}

	/**
	 * Ced_Walmart_Product ced_walmart_prepare_retire_items.
	 *
	 * @since 1.0.0
	 * @param array $walmart_product_ids.
	 */

	public function ced_walmart_prepare_retire_items( $walmart_product_ids = array() ) {

		if ( ! is_array( $walmart_product_ids ) || empty( $walmart_product_ids ) ) {
			return false;
		}
		$feed_array = array();
		foreach ( $walmart_product_ids as $product_id ) {

			$_product = wc_get_product( $product_id );
			if ( is_object( $_product ) ) {
				$status = get_post_status( $product_id );
				if ( 'publish' == $status ) {

					$walmart_status = get_post_meta( $product_id, 'ced_walmart_product_uploaded' . wifw_environment(), true );
					if ( isset( $walmart_status ) && ! empty( $walmart_status ) ) {
						$product_state = get_post_meta( $product_id, 'ced_walmart_product_status' . wifw_environment(), true );
						if ( 'PUBLISHED' == $product_state ) {
							$type = $_product->get_type();
							if ( 'variable' == $type ) {
								$sku = get_post_meta( $product_id, '_sku', 1 );
								if ( $sku ) {
									$feed_array['RetireItem'][]['sku'] = $sku;
								}
								$variations = $_product->get_children();
								if ( is_array( $variations ) && ! empty( $variations ) ) {

									foreach ( $variations as $index => $variation_id ) {
										$sku = get_post_meta( $variation_id, '_sku', 1 );
										if ( $sku ) {
											$feed_array['RetireItem'][]['sku'] = $sku;
										}
									}
								}
							} else {
								$sku = get_post_meta( $product_id, '_sku', 1 );
								if ( $sku ) {
									$feed_array['RetireItem'][]['sku'] = $sku;
								}
							}
						}
					} else {
						continue;
					}
				}
			}
		}
		if ( ! empty( $feed_array ) ) {
			$currentDate = gmdate( 'Y-m-d H:i:s' );
			$feed_header = array(
				'RetireItemHeader' => array(
					'feedDate' => gmdate( DATE_ISO8601, strtotime( gmdate( $currentDate ) ) ),
					'version'  => '1.0',
				),
			);
			$feed_array  = array_merge( $feed_header, $feed_array );

			return $feed_array;

		} else {

			return false;
		}

	}

	/**
	 * Ced_Walmart_Product get_feed_header.
	 *
	 * @since 1.0.0
	 */
	public function get_feed_header( $subCategory ) {
		$date      = gmdate( 'Y-m-d', time() );
		$time      = gmdate( 'h:i:s', time() );
		$date_time = $date . 'T' . $time . 'Z';

		$feed_header['MPItemFeedHeader'] = array(
			'feedDate'       => $date_time,
			'subCategory'    => $subCategory,
			'sellingChannel' => 'marketplace',
			'locale'         => 'en',
			'processMode'    => 'REPLACE',
			'subset'         => 'EXTERNAL',
			'mart'           => 'WALMART_US',
			'version'        => '1.5',
		);
		return $feed_header;
	}
	/**
	 * Function for getting assigned profile to a product
	 *
	 * @since 1.0.0
	 * @param array $product_id Product Id.
	 * @param int   $meta_key Metakey.
	 */
	public function fetch_meta_value_of_the_product( $product_id, $meta_key, $global = '' ) {
		if ( ! empty( $global ) ) {
			$ced_walmart_global_data = get_option( 'ced_walmart_global_settings', array() );

			if ( ! empty( $ced_walmart_global_data ) ) {
				$ced_walmart_global_data = $ced_walmart_global_data;
				$ced_walmart_global_data = json_decode( $ced_walmart_global_data, true );

				$product_profile_data = $ced_walmart_global_data;
			}
		} else {
			$product_profile_data = $this->profile_data;
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
				$temp_profile_data['metakey'] = trim( $temp_profile_data['metakey'] );
				// if woo attribute is selected
				if ( strpos( $temp_profile_data['metakey'], 'umb_pattr_' ) !== false ) {

					$woo_attribute = explode( 'umb_pattr_', $temp_profile_data['metakey'] );
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
									$woo_attribute_value = $temp_value->name;
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
									$woo_attribute_value = $temp_value->name;
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
				} else {

					$value = get_post_meta( $product_id, $temp_profile_data['metakey'], true );
					if ( '_thumbnail_id' == $temp_profile_data['metakey'] ) {
						$value = wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $product_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
					}
					if ( ! isset( $value ) || empty( $value ) ) {
						if ( '0' != $parent_id ) {

							$value = get_post_meta( $parent_id, $temp_profile_data['metakey'], true );
							if ( '_thumbnail_id' == $temp_profile_data['metakey'] ) {
								$value = wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
							}

							if ( ! isset( $value ) || empty( $value ) ) {
								$value = get_post_meta( $product_id, $meta_key, true );
							}
						} else {
							$value = get_post_meta( $product_id, $meta_key, true );
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
	 * Ced_Walmart_Product validate_json_schema.
	 *
	 * @since 1.0.0
	 * @param string $filepath.
	 * @param string $schema.
	 */
	public function validate_json_schema( $filepath = '', $schema = 'MP_ITEM_SPEC', $secondarypath = 'schema' ) {

		if ( empty( $filepath ) ) {
			return false;
		}
		require_once CED_WALMART_DIRPATH . 'admin/walmart/lib/vendor/autoload.php';
		$filedata  = json_decode( file_get_contents( $filepath ) );
		$validator = new Validator();
		$validator->validate(
			$filedata,
			(object) array(
				'$ref' => 'file://' . CED_WALMART_DIRPATH . 'admin/walmart/lib/json-schema/' . $secondarypath . '/' . $schema . '.json',
			),
			Constraint::CHECK_MODE_APPLY_DEFAULTS
		);
		if ( $validator->isValid() ) {
			return true;
		} else {
			$validation_errors       = array();
			$this->items_with_errors = true;
			foreach ( $validator->getErrors() as $errors ) {
				if ( isset( $errors['pointer'] ) && isset( $errors['message'] ) ) {
					$attribute                       = explode( '.', $errors['property'] );
					$attribute                       = $attribute[ count( $attribute ) - 1 ];
					$validation_errors[ $attribute ] = $errors['message'];
				}
			}
			$this->validation_errors = $validation_errors;

		}
	}

	/**
	 * Ced_Walmart_Product make_multi_dimensional_array.
	 *
	 * @since 1.0.0
	 * @param string $indexs.
	 * @param string $value.
	 */
	public function make_multi_dimensional_array( $indexs, $value ) {
		$counter         = count( $indexs ) - 1;
		$array_to_return = array();
		while ( $counter >= 0 ) {
			if ( count( $indexs ) - 1 == $counter ) {
				$array_to_return[ $indexs[ $counter ] ] = $value;
			} else {
				$array_to_return[ $indexs[ $counter ] ] = $array_to_return;
				unset( $array_to_return[ $indexs[ $counter + 1 ] ] );
			}
			$counter--;
		}
		return $array_to_return;
	}

	/**
	 * Ced_Walmart_Product make_multi_dimensional_array.
	 *
	 * @since 1.0.0
	 * @param array  $temp.
	 * @param array  $product_meta_info_to_send.
	 * @param string $current_index.
	 */
	public function perform_recursive_array_merge( $temp, $product_meta_info_to_send, $current_index ) {
		if ( array_key_exists( $current_index, $product_meta_info_to_send ) ) {
			return array_merge_recursive( $product_meta_info_to_send[ $current_index ], $temp );
		}
	}

	/**
	 * Ced_Walmart_Product ced_walmart_upload.
	 *
	 * @since 1.0.0
	 * @param string $filepath.
	 */
	public function ced_walmart_upload( $filepath = '', $feed_type = 'MP_ITEM' ) {
		$action                 = 'feeds';
		$query_args['feedType'] = $feed_type;
		$parameters['file']     = $filepath;
		$status                 = 400;

		/** Refresh token hook for walmart
		 *
		 * @since 1.0.0
		 */
		do_action( 'ced_walmart_refresh_token' );

		if ( is_array( $filepath ) ) {
			$parameters = array();
			$parameters = $filepath;
		}
		$response = $this
		->ced_walmart_curl_instance
		->ced_walmart_post_request( $action, $parameters, $query_args );
		if ( isset( $response['feedId'] ) ) {
			$ced_walmart_import_data                         = get_option( 'ced_walmart_import_data' . wifw_environment(), array() );
			$import_id                                       = $response['feedId'];
			$ced_walmart_import_data[ $import_id ]['feedId'] = $import_id;
			$ced_walmart_import_data[ $import_id ]['type']   = $feed_type;
			$ced_walmart_import_data[ $import_id ]['time']   = gmdate( 'l jS \of F Y h:i:s A' );
			update_option( 'ced_walmart_import_data' . wifw_environment(), $ced_walmart_import_data );
			if ( $this->items_with_errors ) {
				$response['items_with_erros'] = true;
			}
		}
		return $response;
	}
}

