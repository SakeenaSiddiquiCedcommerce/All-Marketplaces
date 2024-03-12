<?php

/**
 *  Profile section to be rendered
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file_product_fields = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/class-ced-good_market-product-fields.php';
get_good_market_header();
include_files( $file_product_fields );
$class_product_fields_instance = new Ced_Good_Market_Product_Fields();
$profile_id                    = isset( $_GET['profile_id'] ) ? sanitize_text_field( wp_unslash( $_GET['profile_id'] ) ) : '';
$profile_id                    = str_replace( ' and ', ' & ', $profile_id );
if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_good_market_profile_save_button'] ) ) {
	// echo"<pre>";
	// print_r($_POST);die;

	if ( ! isset( $_POST['profile_creation_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['profile_creation_submit'] ) ), 'profile_creation' ) ) {
		return;
	}
	$is_active                    = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplace_name             = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'good_market';
	$ced_good_market_profile_data = array();
	if ( isset( $_POST['ced_good_market_required_common'] ) ) {
		$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		foreach ( ( $post_array['ced_good_market_required_common'] ) as $key ) {
			$array_to_save = array();
			isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = trim( $post_array[ $key ][0] ) : $array_to_save['default'] = '';
			if ( '_umb_' . $marketplace_name . '_subcategory' == $key ) {
				isset( $post_array[ $key ] ) ? $array_to_save['default'] = trim( $post_array[ $key ] ) : $array_to_save['default'] = '';
			}
			isset( $post_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = $post_array[ $key . '_attribute_meta' ] : $array_to_save['metakey'] = 'null';
			$ced_good_market_profile_data[ $key ]                                        = $array_to_save;

		}
	}

	$ced_good_market_profile_data    = json_encode( $ced_good_market_profile_data );
	$ced_good_market_profile_details = get_option( 'ced_mapped_cat_good_market' );
	$ced_good_market_profile_details = json_decode( $ced_good_market_profile_details, 1 );

	if ( $profile_id ) {
		$ced_good_market_profile_details['profile'][ $profile_id ]['profile_data'] = $ced_good_market_profile_data;
		update_option( 'ced_mapped_cat_good_market', json_encode( $ced_good_market_profile_details ) );
	}
}

$ced_good_market_profile_details = get_option( 'ced_mapped_cat_good_market' );
$ced_good_market_profile_details = json_decode( $ced_good_market_profile_details, 1 );

$ced_good_market_profile_data  = isset( $ced_good_market_profile_details['profile'][ $profile_id ] ) ? $ced_good_market_profile_details['profile'][ $profile_id ] : array();
$ced_good_market_category_data = json_decode( $ced_good_market_profile_data['profile_data'], true );

$ced_good_market_category_id = isset( $ced_good_market_category_data['_umb_good_market_category']['default'] ) ? $ced_good_market_category_data['_umb_good_market_category']['default'] : '';
$attributes                  = wc_get_attribute_taxonomies();
$attr_options                = array();
$added_meta_keys             = get_option( 'ced_good_market_selected_metakeys', array() );
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
	<div class="ced_good_market_heading">
		<?php echo esc_html( get_instuction_html() ); ?>
		<div class="ced_good_market_child_element default_modal">
			<ul type="disc">
				<li><?php echo esc_html_e( 'This section is for mapping the "Good Market" category specific attributes with your woocommerce store attributes.' ); ?></li>
				<li><?php echo esc_html_e( 'You will find the list of Woocommerce attributes/metakeys in the selection box on the right side.' ); ?></li>
				<li><?php echo esc_html_e( 'If you are unable to see the corresponding attribute for mapping , you can select the attributes/metakeys using the METAKEYS AND ATTRIBUTES LIST(product related data) below.' ); ?></li>
				<li><?php echo esc_html_e( 'Type any product name and list of related product will be displayed . Choose any one product and list of attributes/metakeys will be listed.' ); ?></li>
				<li><?php echo esc_html_e( 'Select the attributes/metakeys you want to use for mapping and then click save.' ); ?></li>
			</ul>
		</div>
	</div>

	<?php include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/pages/ced-good_market-metakeys-template.php'; ?>
	<form action="" method="post">
		<?php wp_nonce_field( 'profile_creation', 'profile_creation_submit' ); ?>
		<div class="ced_good_market_profile_details_wrapper">
			<div class="ced_good_market_profile_details_fields">
				<table id="ced_good_market_general_profile_details">
					<tbody>
						<tr>
							<td>
								<label><?php esc_html_e( 'Profile Name', 'good_market-woocommerce-integration' ); ?></label>
							</td>
							<?php

							if ( isset( $profile_id ) ) {
								?>
								<td>
									<label><b class="good_market-success"><?php echo esc_attr( $profile_id ); ?></b></label>
								</td>
							</tr>
								<?php
							}
							?>
						<tr>
							<?php
							if ( file_exists( $file_product_fields ) ) {

								$cat_attributes   = array();
								$get_profile_name = $profile_id;
								$profile_data     = $ced_good_market_category_data;
								$cat_profile_data = get_option( 'category_att' );
								if ( empty( $cat_profile_data[ $profile_id ] ) ) {
									$data = array();
									GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
									$good_market_Request           = new Ced_Good_Market_Request();
									$products_attri                = $good_market_Request->get_good_market_attributes( $profile_id );
									$cat_attributes[ $profile_id ] = $products_attri;
									update_option( 'category_att', $cat_attributes );
									$products_attri_decoded = json_decode( $products_attri, 1 );
								} else {

									$products_attri_decoded = json_decode( $cat_profile_data[ $profile_id ], 1 );

								}

								$market_place                  = 'ced_good_market_required_common';
								$index_to_use                  = 0;
								$product_id                    = 0;
								$attribute_data_required_label = '';
								$attribute_data_required       = false;
								$isVariationExist              = false;
								$schema                        = json_decode( $products_attri_decoded['data']['productAllowedAttributes']['groupwise_attributes'], 1 );
								$attribute_set_id              = get_option( $profile_id . 'attribute_set_id' );
								if ( empty( $attribute_set_id ) ) {
									update_option( $profile_id . 'attribute_set_id', $schema['attribute_set_id'] );
								}
								$cat_search                     = $schema['product-details'];
								$type_good_market_cat_attribute = $cat_search;
								$temp                           = array();
								// echo"<pre>";
								foreach ( $cat_search['attributes'] as $key => $value ) {
									$skip_sku = array( 'sku', 'name', 'category_ids' );
									if ( ! in_array( $value['attribute_code'], $skip_sku ) ) {

										$isText = false;
										echo '<tr class"line">';
										$attribute_id   = $value['attribute_code'];
										$field_data     = isset( $profile_data[ $profile_id . '_' . $attribute_id ] ) ? $profile_data[ $profile_id . '_' . $attribute_id ] : array();
										$default        = isset( $field_data['default'] ) ? $field_data['default'] : null;
										$metakey        = isset( $field_data['metakey'] ) ? $field_data['metakey'] : null;
										$attribute_name = $value['label'];
										if ( 'color' == $value['attribute_code'] || 'size' == $value['attribute_code'] || 'material' == $value['attribute_code'] || 'type' == $value['attribute_code'] ) {
											$isVariationExist = true;
											$isText           = true;

										}
										if ( 'select' == $value['type'] ) {
											$values = array();

											if ( is_array( $value['source_options'] ) && ! empty( $value['source_options'] ) ) {
												echo '<td>';
												foreach ( $value['source_options'] as $key_opt => $value_opt ) {
													$values[ $value_opt['value'] ] = $value_opt['label'];
												}
											}
											$class_product_fields_instance->ced_good_market_render_dropdown_html(
												$attribute_id,
												$attribute_name,
												$values,
												$profile_id,
												$product_id,
												$market_place,
												$attribute_description         = null,
												$index_to_use,
												$additional_info               = array(
													'case' => 'profile',
													'value' => $default,
												),
												$is_required                   = '',
												$attribute_data_required_label = '',
												$objectFulfilled               = '',
												$isVariationExist
											);
											echo '</td>';
										} elseif ( 'text' == $value['type'] || 'weight' == $value['type'] ) {
											$isText = true;
											echo '<td>';
											$class_product_fields_instance->ced_good_market_render_text_html(
												$attribute_id,
												$attribute_name,
												$profile_id,
												$product_id,
												$market_place,
												$attribute_description       = null,
												$index_to_use,
												$additional_info             = array(
													'case' => 'profile',
													'value' => $default,
												),
												$conditionally_required      = false,
												$is_add_html                 = false,
												$conditionally_required_text = '',
												$input_type                  = '',
												$objectFulfilled             = '',
												$isVariationExist            = false
											);
											echo '</td>';
										}


										if ( $isText ) {
											echo '<td>';
											$previous_selected_value = 'null';
											if ( isset( $profile_data[ $profile_id . '_' . str_replace( ' ', '', $attribute_id ) ] ) && 'null' != $profile_data[ $profile_id . '_' . str_replace( ' ', '', $attribute_id ) ]['metakey'] ) {
												$previous_selected_value = $profile_data[ $profile_id . '_' . str_replace( ' ', '', $attribute_id ) ]['metakey'];
											}
											$select_id = $profile_id . '_' . str_replace( ' ', '', $attribute_id ) . '_attribute_meta';
											?>
											<select id="<?php echo esc_attr( $select_id ); ?>" name="<?php echo esc_attr( $select_id ); ?>">
												<option value="null" selected> -- select -- </option>
												<?php
												if ( is_array( $attr_options ) ) {

													if ( ! $isVariationExist ) {

														foreach ( $attr_options as $attr_key => $attr_name ) :
															if ( trim( $previous_selected_value ) == trim( $attr_key ) ) {
																$selected = 'selected';
															} else {
																$selected = '';
															}
															?>
															<option value="<?php echo esc_attr( trim( $attr_key ) ); ?>  " <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
															<?php
														endforeach;
													} else {
														foreach ( $attr_options as $attr_key => $attr_name ) :

															if ( strpos( $attr_key, 'attribute_' ) !== false || strpos( $attr_key, 'umb_pattr_' ) !== false ) {

																if ( trim( $previous_selected_value ) == trim( $attr_key ) ) {
																	$selected = 'selected';
																} else {
																	$selected = '';
																}
																?>
																<option value="<?php echo esc_attr( trim( $attr_key ) ); ?>  " <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
																<?php
															}
														endforeach;
													}
												}
												?>
											</select>
											<?php
											echo '<td>';
										}
										echo '</tr>';
									}

									update_option( 'ced_good_market_cat_type_attribute_' . $get_profile_name, json_encode( $type_good_market_cat_attribute ) );

								}
							}
							echo '</table>';
							?>
						</tr>
					</tbody>
					<div class="good_market-button-wrap">
						<button class="button button-primary" name="ced_good_market_profile_save_button"><?php esc_html_e( 'Save Profile', 'good_market-woocommerce-integration' ); ?></button>

					</div>
				</table>

			</div>
		</div>
	</form>

	<?php
}

?>
