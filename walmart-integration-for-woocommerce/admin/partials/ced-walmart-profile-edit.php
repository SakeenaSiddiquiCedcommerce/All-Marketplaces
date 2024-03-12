<?php

/**
 *  Profile section to be rendered
 *
 * @package  Woocommerce_Walmart_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file_product_fields = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-product-fields.php';
get_walmart_header();
include_file( $file_product_fields );
$class_product_fields_instance = new Ced_Walmart_Product_Fields();
$profile_id                    = isset( $_GET['profile_id'] ) ? sanitize_text_field( wp_unslash( $_GET['profile_id'] ) ) : '';
$profile_id                    = str_replace( ' and ', ' & ', $profile_id );
if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_walmart_profile_save_button'] ) ) {

	if ( ! isset( $_POST['profile_creation_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['profile_creation_submit'] ) ), 'profile_creation' ) ) {
		return;
	}
	$is_active                = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplace_name         = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'walmart';
	$ced_walmart_profile_data = array();
	if ( isset( $_POST['ced_walmart_required_common'] ) ) {
		$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		foreach ( ( $post_array['ced_walmart_required_common'] ) as $key ) {
			$array_to_save = array();
			isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = trim( $post_array[ $key ][0] ) : $array_to_save['default'] = '';
			if ( '_umb_' . $marketplace_name . '_subcategory' == $key ) {
				isset( $post_array[ $key ] ) ? $array_to_save['default'] = trim( $post_array[ $key ] ) : $array_to_save['default'] = '';
			}
			isset( $post_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = $post_array[ $key . '_attribute_meta' ] : $array_to_save['metakey'] = 'null';
			$ced_walmart_profile_data[ $key ] = $array_to_save;
		}
	}
	$ced_walmart_profile_data    = json_encode( $ced_walmart_profile_data );
	$ced_walmart_profile_details = get_option( 'ced_mapped_cat' );
	$ced_walmart_profile_details = json_decode( $ced_walmart_profile_details, 1 );

	if ( $profile_id ) {
		$ced_walmart_profile_details['profile'][ $profile_id ]['profile_data'] = $ced_walmart_profile_data;
		update_option( 'ced_mapped_cat', json_encode( $ced_walmart_profile_details ) );
	}
}

$ced_walmart_profile_details = get_option( 'ced_mapped_cat' );
$ced_walmart_profile_details = json_decode( $ced_walmart_profile_details, 1 );
$ced_walmart_profile_data    = isset( $ced_walmart_profile_details['profile'][ $profile_id ] ) ? $ced_walmart_profile_details['profile'][ $profile_id ] : array();
$ced_walmart_category_id     = json_decode( $ced_walmart_profile_data['profile_data'], true );
$ced_walmart_category_id     = isset( $ced_walmart_category_id['_umb_walmart_category']['default'] ) ? $ced_walmart_category_id['_umb_walmart_category']['default'] : '';
$attributes                  = wc_get_attribute_taxonomies();
$attr_options                = array();
$added_meta_keys             = get_option( 'ced_walmart_selected_metakeys', array() );
$select_dropdown_html        = '';


if ( $added_meta_keys && count( $added_meta_keys ) > 0 ) {
	foreach ( $added_meta_keys as $meta_key ) {
		$attr_options[ $meta_key ] = $meta_key;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributes_object ) {
		$attr_options[ 'umb_pattr_' . $attributes_object->attribute_name ] = $attributes_object->attribute_label;
	}
}

if ( ! empty( $profile_id ) ) {
	?>
	<div class="ced_walmart_heading">
		<?php echo esc_html_e( get_instuctions_html() ); ?>
		<div class="ced_walmart_child_element default_modal">
			<ul type="disc">
				<li><?php echo esc_html_e( 'This section is for mapping the Walmart category specific attributes with your WooCommerce store attributes.' ); ?></li>
				<li><?php echo esc_html_e( 'You will find the list of WooCommerce attributes/metakeys in the selection box on the right side.' ); ?></li>
				<li><?php echo esc_html_e( 'If you are unable to see the corresponding attribute for mapping , you can select the attributes/metakeys using the METAKEYS AND ATTRIBUTES LIST below.' ); ?></li>
				<li><?php echo esc_html_e( 'Type any product name and list of related product will be displayed . Choose any one product and list of attributes/metakeys will be listed.' ); ?></li>
				<li><?php echo esc_html_e( 'Select the attributes/metakeys you want to use for mapping and then click save.' ); ?></li>
			</ul>
		</div>
	</div>

	<?php include_once CED_WALMART_DIRPATH . 'admin/pages/ced-walmart-metakeys-template.php'; ?>
	<form action="" method="post">
		<?php wp_nonce_field( 'profile_creation', 'profile_creation_submit' ); ?>
		<div class="ced_walmart_profile_details_wrapper">
			<div class="ced_walmart_profile_details_fields">
				<table id="ced_walmart_general_profile_details">
					<tbody>
						<tr>
							<td>
								<label><?php esc_html_e( 'Profile Name', 'walmart-woocommerce-integration' ); ?></label>
							</td>
							<?php

							if ( isset( $profile_id ) ) {
								?>
								<td>
									<label><b class="walmart-success"><?php echo esc_attr( $profile_id ); ?></b></label>
								</td>
							</tr>
								<?php
							}
							?>
						<tr>
							<?php
							if ( file_exists( $file_product_fields ) ) {
								$ced_walmart_product_fields = $class_product_fields_instance->ced_walmart_get_custom_products_fields();
								if ( ! empty( $ced_walmart_product_fields ) && ! empty( $ced_walmart_product_fields ) ) {
									$required_in_any_case = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
									$market_place         = 'ced_walmart_required_common';
									$product_id           = 0;
									$index_to_use         = 0;

									foreach ( $ced_walmart_product_fields as $index => $fields_data ) {
										$required = $fields_data['required'];
										$is_text  = true;
										$field_id = trim( $fields_data['fields']['id'], '_' );

										$attribute_name_to_render = ucfirst( $fields_data['fields']['label'] );
										$profile_data             = json_decode( $ced_walmart_profile_data['profile_data'], true );

										$default = isset( $profile_data[ $fields_data['fields']['id'] ]['default'] ) ? $profile_data[ $fields_data['fields']['id'] ]['default'] : '';

										echo '<tr class="form-field _umb_id_type_field ">';
										if ( '_hidden' == $fields_data['type'] ) {
											$class_product_fields_instance->render_input_text_html_hidden(
												$field_id,
												$attribute_name_to_render,
												'',
												$product_id,
												$market_place,
												$fields_data['fields']['description'],
												$index_to_use,
												array(
													'case' => 'profile',
													'value' => $ced_walmart_category_id,
												),
												$required
											);
											$is_text = false;
										} else {
											$is_text = true;
										}

										echo '<td>';
										if ( $is_text ) {

											$previous_selected_value = 'null';
											if ( isset( $profile_data[ $fields_data['fields']['id'] ]['metakey'] ) && 'null' != $profile_data[ $fields_data['fields']['id'] ]['metakey'] ) {
												$previous_selected_value = $profile_data[ $fields_data['fields']['id'] ]['metakey'];
											}


											$select_id = $fields_data['fields']['id'] . '_attribute_meta';
											?>
											<select id="<?php echo esc_attr( $select_id ); ?>" name="<?php echo esc_attr( $select_id ); ?>">
												<option value="null" selected> -- select -- </option>
												<?php
												if ( is_array( $attr_options ) ) {

													foreach ( $attr_options as $attr_key => $attr_name ) :
														if ( trim( $previous_selected_value ) == $attr_key ) {
															$selected = 'selected';
														} else {
															$selected = '';
														}
														?>
														<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
														<?php
													endforeach;
												}
												?>
											</select>
											<?php
										}
										echo '</td>';
										echo '</tr>';
									}
								}

								$get_profile_name     = $profile_id;
								$schema               = CED_WALMART_DIRPATH . 'admin/walmart/lib/json/MP_ITEM_SPEC.json';
								$schema               = file_get_contents( $schema );
								$schema               = json_decode( $schema, true );
								$attributes_to_escape = array( 'sku', 'productIdentifiers', 'productIdentifier', 'productIdType', 'productIdentifiers', 'productIdentifier', 'productId', 'productName', 'keyFeatures', 'keyFeaturesValue', 'shortDescription', 'mainImageUrl', 'MPOffer', 'productTaxCode', 'MPOffer', 'price', 'MPOffer', 'MinimumAdvertisedPrice', 'MPOffer', 'StartDate', 'MPOfferEndDate', 'MPOffer', 'ShippingWeight', 'measure', 'MPOffer', 'unit', 'brand', 'variantGroupId', 'additionalProductAttributes', 'isPrimaryVariant', 'swatchImages', 'variantAttributeNames' );

								$market_place                  = 'ced_walmart_required_common';
								$index_to_use                  = 0;
								$product_id                    = 0;
								$attribute_data_required_label = '';
								$attribute_data_required       = false;

								$cat_search                 = $schema['properties']['MPItem']['items']['properties']['Visible']['properties'];
								$type_walmart_cat_attribute = array();
								$temp                       = array();
								foreach ( $cat_search as $key => $value ) {
									if ( $key == $get_profile_name ) {

										$key = str_replace( ' ', '', $key );
										if ( ! is_array( $value ) ) {
											continue;
										}



										update_option( 'ced_walmart_cat_visible_' . $get_profile_name, json_encode( unserialize( str_replace( array( 'NAN;', 'INF;' ), '0;', serialize( $value['properties'] ) ) ) ) );
										$merged_preapre          = $value['properties'];
										$merged_preapre_required = $value['required'];
										$variationAttr           = $value['properties']['variantAttributeNames']['items']['enum'];

										foreach ( $merged_preapre as $attributesKey => $attributesValue ) {

											if ( in_array( $attributesKey, $attributes_to_escape ) ) {
												if ( 'variantAttributeNames' == $attributesKey ) {
													$temp = $attributesValue;
												}

												continue;
											}


											$attribute_data_required_label = '';
											$attribute_data_required       = false;
											$objectFulfilled               = '';
											$isVariationExist              = false;
											if ( in_array( $attributesKey, $merged_preapre_required ) ) {
												$attribute_data_required_label = 'Required';
												$attribute_data_required       = true;
											}

											if ( in_array( $attributesKey, $variationAttr ) ) {
												$isVariationExist = true;
											}



											if ( 'object' == $attributesValue['type'] ) {
												foreach ( $attributesValue['properties'] as $objectkey => $objectvalue ) {

													if ( ! isset( $objectvalue['enum'] ) && ! isset( $objectvalue['items']['enum'] ) ) {
														$isText          = true;
														$objectFulfilled = $attributesKey;
														if ( isset( $objectvalue['title'] ) && ! empty( $objectvalue['title'] ) ) {
															$field_name_to_render = ucfirst( $objectvalue['title'] );
														} else {
															continue;
														}
														echo '<tr>';
														$type_walmart_cat = 'text';
														$input_type       = 'text';
														if ( 'integer' == $objectvalue['type'] || 'number' == $objectvalue['type'] ) {
															$type_walmart_cat = 'number';
														} elseif ( isset( $objectvalue['format'] ) && 'date-time' == $objectvalue['format'] ) {
															$type_walmart_cat = 'datetime-local';
															$input_type       = 'datetime-local';
														} elseif ( isset( $objectvalue['format'] ) && 'uri' == $objectvalue['format'] ) {
															$type_walmart_cat = 'url';
															$input_type       = 'text';
														}
														$type_walmart_cat_attribute[ $attributesKey . '_' . $objectkey ] = $type_walmart_cat;
														$field_id   = $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey );
														$field_data = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();

														$default = isset( $field_data['default'] ) ? $field_data['default'] : null;
														$metakey = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
														$class_product_fields_instance->ced_walmart_render_text_html(
															$attributesKey . '_' . $objectkey,
															$field_name_to_render,
															$key,
															$product_id,
															$market_place,
															$objectvalue['title'],
															$index_to_use,
															array(
																'case'  => 'profile',
																'value' => $default,
															),
															$attribute_data_required,
															false,
															$attribute_data_required_label,
															$input_type,
															$objectFulfilled,
															$isVariationExist
														);

														echo '<td>';
														if ( $isText ) {
															$previous_selected_value = 'null';
															if ( isset( $profile_data[ $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) ] ) && 'null' != $profile_data[ $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) ]['metakey'] ) {
																$previous_selected_value = $profile_data[ $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) ]['metakey'];
															}
															$select_id = $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) . '_attribute_meta';
															?>
															<select id="<?php echo esc_attr( $select_id ); ?>" name="<?php echo esc_attr( $select_id ); ?>[]" multiple>
																<option value="null"> -- select -- </option>
																<?php

																if ( is_array( $attr_options ) ) {
																	foreach ( $attr_options as $attr_key => $attr_name ) :

																		if ( is_array( $previous_selected_value ) ) {
																			if ( in_array( $attr_key, $previous_selected_value ) ) {
																				$selected = 'selected';
																			} else {
																				$selected = '';
																			}
																		}

																		?>
																		<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
																		<?php
																	endforeach;
																}
																?>
															</select>
															<?php
														}
														echo '</td>';
														echo '</tr>';
													} elseif ( isset( $objectvalue['enum'] ) && ! empty( $objectvalue['enum'] ) ) {
														$temp_value_for_dropdown = array();
														$type_walmart_cat        = 'text';
														if ( 'integer' == $objectvalue['type'] || 'number' == $objectvalue['type'] ) {
															$type_walmart_cat = 'number';
														} elseif ( isset( $objectvalue['format'] ) && 'date-time' == $objectvalue['format'] ) {
															$type_walmart_cat = 'datetime-local';
														} elseif ( isset( $objectvalue['format'] ) && 'uri' == $objectvalue['format'] ) {
															$type_walmart_cat = 'url';
														}
														$type_walmart_cat_attribute[ $attributesKey . '_' . $objectkey ] = $type_walmart_cat;
														foreach ( $objectvalue['enum'] as $key_attributesValue => $value__attributesValue ) {
															$temp_value_for_dropdown[ $value__attributesValue ] = $value__attributesValue;

														}
														$value_for_dropdown   = $temp_value_for_dropdown;
														$field_name_to_render = $objectvalue['title'];
														$objectFulfilled      = $attributesKey;
														$field_id             = $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey );

														$field_data = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();
														$default    = isset( $field_data['default'] ) ? $field_data['default'] : null;
														$metakey    = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
														$class_product_fields_instance->ced_walmart_render_dropdown_html(
															$attributesKey . '_' . $objectkey,
															$field_name_to_render,
															$value_for_dropdown,
															$key,
															$product_id,
															$market_place,
															$objectvalue['title'],
															$index_to_use,
															array(
																'case'  => 'profile',
																'value' => $default,
															),
															$attribute_data_required,
															$attribute_data_required_label,
															$objectFulfilled,
															$isVariationExist
														);

														$isText = false;
													}
												}
											}

											$isText = true;
											if ( isset( $attributesValue['title'] ) && ! empty( $attributesValue['title'] ) ) {
												$field_name_to_render = ucfirst( $attributesValue['title'] );


											} else {
												continue;
											}
											echo '<tr>';
											if ( ! isset( $attributesValue['enum'] ) && ! isset( $attributesValue['items']['enum'] ) && ! isset( $attributesValue['items']['properties'] ) ) {

												$type_walmart_cat = 'text';
												$input_type       = 'text';
												if ( 'integer' == $attributesValue['type'] || 'number' == $attributesValue['type'] ) {
													$type_walmart_cat = 'number';
												} elseif ( isset( $attributesValue['format'] ) && 'date-time' == $attributesValue['format'] ) {
													$type_walmart_cat = 'datetime-local';
													$input_type       = 'datetime-local';
												} elseif ( isset( $attributesValue['format'] ) && 'uri' == $attributesValue['format'] ) {
													$type_walmart_cat = 'url';
													$input_type       = 'text';
												} elseif ( 'array' == $attributesValue['type'] ) {
													$type_walmart_cat = 'array';
												}
												$type_walmart_cat_attribute[ $attributesKey ] = $type_walmart_cat;
												$field_id                                     = $key . '_' . str_replace( ' ', '', $attributesKey );
												$field_data                                   = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();

												$default = isset( $field_data['default'] ) ? $field_data['default'] : null;
												$metakey = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
												$class_product_fields_instance->ced_walmart_render_text_html(
													$attributesKey,
													$field_name_to_render,
													$key,
													$product_id,
													$market_place,
													$attributesValue['title'],
													$index_to_use,
													array(
														'case'  => 'profile',
														'value' => $default,
													),
													$attribute_data_required,
													false,
													$attribute_data_required_label,
													$input_type,
													$objectFulfilled,
													$isVariationExist
												);

												echo '<td>';
												if ( $isText ) {
													$previous_selected_value = 'null';
													if ( isset( $profile_data[ $key . '_' . str_replace( ' ', '', $attributesKey ) ] ) && 'null' != $profile_data[ $key . '_' . str_replace( ' ', '', $attributesKey ) ]['metakey'] ) {
														$previous_selected_value = $profile_data[ $key . '_' . str_replace( ' ', '', $attributesKey ) ]['metakey'];
													}
													$select_id = $key . '_' . str_replace( ' ', '', $attributesKey ) . '_attribute_meta';
													?>
													<select id="<?php echo esc_attr( $select_id ); ?>" name="<?php echo esc_attr( $select_id ); ?>[]" multiple>
														<option value="null"> -- select -- </option>
														<?php

														if ( is_array( $attr_options ) ) {
															foreach ( $attr_options as $attr_key => $attr_name ) :
																$selected = '';
																if ( is_array( $previous_selected_value ) ) {
																	if ( in_array( $attr_key, $previous_selected_value ) ) {
																		$selected = 'selected';
																	} else {
																		$selected = '';
																	}
																}

																?>
																<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
																<?php
															endforeach;
														}
														?>
													</select>
													<?php
												}
												echo '</td>';
												echo '</tr>';

											} elseif ( isset( $attributesValue['enum'] ) && ! empty( $attributesValue['enum'] ) ) {
												$type_walmart_cat = 'text';
												if ( 'integer' == $attributesValue['type'] || 'number' == $attributesValue['type'] ) {
													$type_walmart_cat = 'number';
												} elseif ( isset( $attributesValue['format'] ) && 'date-time' == $attributesValue['format'] ) {
													$type_walmart_cat = 'datetime-local';
												} elseif ( isset( $attributesValue['format'] ) && 'uri' == $attributesValue['format'] ) {
													$type_walmart_cat = 'url';
												}
												$type_walmart_cat_attribute[ $attributesKey ] = $type_walmart_cat;
												$temp_value_for_dropdown                      = array();
												foreach ( $attributesValue['enum'] as $key_attributesValue => $value__attributesValue ) {
													$temp_value_for_dropdown[ $value__attributesValue ] = $value__attributesValue;
												}
												$value_for_dropdown = $temp_value_for_dropdown;
												$field_id           = $key . '_' . str_replace( ' ', '', $attributesKey );
												$field_data         = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();
												$default            = isset( $field_data['default'] ) ? $field_data['default'] : null;
												$metakey            = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
												$class_product_fields_instance->ced_walmart_render_dropdown_html(
													$attributesKey,
													$field_name_to_render,
													$value_for_dropdown,
													$key,
													$product_id,
													$market_place,
													$attributesValue['title'],
													$index_to_use,
													array(
														'case'  => 'profile',
														'value' => $default,
													),
													$attribute_data_required,
													$attribute_data_required_label,
													$objectFulfilled,
													$isVariationExist
												);
												$isText = false;
											} elseif ( 'array' == $attributesValue['type'] ) {
												if ( isset( $attributesValue['items']['enum'] ) && ! empty( $attributesValue['items']['enum'] ) ) {

													$temp_value_for_dropdown = array();
													foreach ( $attributesValue['items']['enum'] as $key_attributesValue => $value__attributesValue ) {
														$temp_value_for_dropdown[ $value__attributesValue ] = $value__attributesValue;
													}
													$type_walmart_cat                             = 'array';
													$type_walmart_cat_attribute[ $attributesKey ] = $type_walmart_cat;

													$value_for_dropdown = $temp_value_for_dropdown;
													$field_id           = $key . '_' . str_replace( ' ', '', $attributesKey );
													$field_data         = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();
													$default            = isset( $field_data['default'] ) ? $field_data['default'] : null;
													$metakey            = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
													$class_product_fields_instance->ced_walmart_render_dropdown_html(
														$attributesKey,
														$field_name_to_render,
														$value_for_dropdown,
														$key,
														$product_id,
														$market_place,
														$attributesValue['title'],
														$index_to_use,
														array(
															'case'  => 'profile',
															'value' => $default,
														),
														$attribute_data_required,
														$attribute_data_required_label,
														$objectFulfilled,
														$isVariationExist
													);

													$isText = false;

												} elseif ( isset( $attributesValue['items']['type'] ) && 'object' == $attributesValue['items']['type'] ) {

													foreach ( $attributesValue['items']['properties'] as $objectkey => $objectvalue ) {

														if ( isset( $objectvalue['enum'] ) ) {
															$temp_value_for_dropdown = array();
															$type_walmart_cat        = 'array';

															$type_walmart_cat_attribute[ $attributesKey . '_' . $objectkey ] = $type_walmart_cat;

															foreach ( $objectvalue['enum'] as $key_attributesValue => $value__attributesValue ) {
																$temp_value_for_dropdown[ $value__attributesValue ] = $value__attributesValue;

															}
															$value_for_dropdown   = $temp_value_for_dropdown;
															$field_name_to_render = $objectvalue['title'];
															$objectFulfilled      = $attributesKey;
															$field_id             = $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey );

															$field_data = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();
															$default    = isset( $field_data['default'] ) ? $field_data['default'] : null;
															$metakey    = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
															$class_product_fields_instance->ced_walmart_render_dropdown_html(
																$attributesKey . '_' . $objectkey,
																$field_name_to_render,
																$value_for_dropdown,
																$key,
																$product_id,
																$market_place,
																$objectvalue['title'],
																$index_to_use,
																array(
																	'case'  => 'profile',
																	'value' => $default,
																),
																$attribute_data_required,
																$attribute_data_required_label,
																$objectFulfilled,
																$isVariationExist
															);

															$isText = false;

														} else {

															$isText          = true;
															$objectFulfilled = $attributesKey;
															if ( isset( $objectvalue['title'] ) && ! empty( $objectvalue['title'] ) ) {
																$field_name_to_render = ucfirst( $objectvalue['title'] );
															} else {
																continue;
															}
															echo '<tr>';
															$type_walmart_cat = 'array';

															if ( 'integer' == $objectvalue['type'] || 'number' == $objectvalue['type'] ) {
																$input_type = 'text';
															} elseif ( isset( $objectvalue['format'] ) && 'date-time' == $objectvalue['format'] ) {

																$input_type = 'datetime-local';
															} elseif ( isset( $objectvalue['format'] ) && 'uri' == $objectvalue['format'] ) {
																$input_type = 'text';
															}
															$type_walmart_cat_attribute[ $attributesKey . '_' . $objectkey ] = $type_walmart_cat;
															$field_id   = $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey );
															$field_data = isset( $profile_data[ $field_id ] ) ? $profile_data[ $field_id ] : array();

															$default = isset( $field_data['default'] ) ? $field_data['default'] : null;
															$metakey = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
															$class_product_fields_instance->ced_walmart_render_text_html(
																$attributesKey . '_' . $objectkey,
																$field_name_to_render,
																$key,
																$product_id,
																$market_place,
																$objectvalue['title'],
																$index_to_use,
																array(
																	'case'  => 'profile',
																	'value' => $default,
																),
																false,
																false,
																$attribute_data_required_label,
																$input_type,
																$objectFulfilled,
																$isVariationExist
															);

															echo '<td>';
															if ( $isText ) {
																$previous_selected_value = 'null';
																if ( isset( $profile_data[ $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) ] ) && 'null' != $profile_data[ $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) ]['metakey'] ) {
																	$previous_selected_value = $profile_data[ $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) ]['metakey'];
																}
																$select_id = $key . '_' . $attributesKey . '_' . str_replace( ' ', '', $objectkey ) . '_attribute_meta';
																?>
																<select id="<?php echo esc_attr( $select_id ); ?>" name="<?php echo esc_attr( $select_id ); ?>[]" multiple class="select2">
																	<option value="null"> -- select -- </option>
																	<?php

																	if ( is_array( $attr_options ) ) {
																		foreach ( $attr_options as $attr_key => $attr_name ) :
																			$selected = '';
																			if ( is_array( $previous_selected_value ) ) {
																				if ( in_array( $attr_key, $previous_selected_value ) ) {
																					$selected = 'selected';
																				} else {
																					$selected = '';
																				}
																			}

																			?>
																			<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
																			<?php
																		endforeach;
																	}
																	?>
																</select>
																<?php
															}
															echo '</td>';
															echo '</tr>';


														}
													}
												}
											}
										}

										if ( ! empty( $temp ) ) {
											foreach ( $temp['items']['enum'] as $variantAttributeName ) {
												update_option( 'ced_walmart_cat_visible_variable_attr' . $get_profile_name, json_encode( $temp['items']['enum'] ) );
											}
										}

										break;}
								}
									update_option( 'ced_walmart_cat_type_attribute_' . $get_profile_name, json_encode( $type_walmart_cat_attribute ) );


							}
								echo '</table>';
							?>
							</tr>
						</tbody>
						<div class="walmart-button-wrap">
							<button class="button button-primary" name="ced_walmart_profile_save_button"><?php esc_html_e( 'Save Profile', 'walmart-woocommerce-integration' ); ?></button>

						</div>
					</table>

				</div>
			</div>
		</form>

		<?php
}

?>
