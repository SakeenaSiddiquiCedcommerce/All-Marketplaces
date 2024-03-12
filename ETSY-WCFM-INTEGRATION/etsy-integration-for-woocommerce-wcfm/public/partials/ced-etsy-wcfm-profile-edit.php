<?php
/**
 * Profile section to be rendered
 *
 * @package  Woocommerce_Jumia_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

ced_etsy_wcfm_get_header();
ced_etsy_wcfm_include_file(CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-product-fields.php');
$product_field_instance       = Ced_Etsy_Wcfm_Product_Fields::get_instance();
$ced_etsy_wcfm_product_fields = $product_field_instance->get_etsy_wcfm_custom_products_fields();
$shop_name                    = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
$profile_id                   = isset( $_GET['profile_id'] ) ? sanitize_text_field( wp_unslash( $_GET['profile_id'] ) ) : '';

if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_etsy_wcfm_profile_save_button'] ) ) {
	if ( ! isset( $_POST['profile_creation_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['profile_creation_submit'] ) ), 'profile_creation' ) ) {
		return;
	}

	$is_active        = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplace_name = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'etsy_wcfm';

	$ced_etsy_wcfm_profile_data = array();

	if ( isset( $_POST['ced_etsy_wcfm_required_common'] ) ) {
		$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		foreach ( ( $post_array['ced_etsy_wcfm_required_common'] ) as $key ) {
			$array_to_save = array();
			isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = $post_array[ $key ][0]  : $array_to_save['default'] = array();
			isset( $post_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = trim( $post_array[ $key . '_attribute_meta' ] ) : $array_to_save['metakey'] = 'null';
			$ced_etsy_wcfm_profile_data[ $key ] = $array_to_save;
		}
	}

	$ced_etsy_wcfm_profile_data    = json_encode( $ced_etsy_wcfm_profile_data );
	$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details' . $shop_name, array() );
	if ( $profile_id || $profile_id === 0 || $profile_id === '0' ) {
		$ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ]['profile_data'] = $ced_etsy_wcfm_profile_data;
		update_option( 'ced_etsy_wcfm_profile_details' . $shop_name, $ced_etsy_wcfm_profile_details );
	}
}
$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details' . $shop_name, array() );
$ced_etsy_wcfm_profile_data    = isset( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ] ) ? $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ] : array();
$ced_etsy_wcfm_profile_details     = json_decode( $ced_etsy_wcfm_profile_data['profile_data'], true );
$ced_etsy_wcfm_category_id     = isset( $ced_etsy_wcfm_profile_details['_umb_etsy_wcfm_category']['default'] ) ? (int)$ced_etsy_wcfm_profile_details['_umb_etsy_wcfm_category']['default'] : '';
$vendor_id   	            = !empty( ced_etsy_wcfm_get_vendor_id() ) ? ced_etsy_wcfm_get_vendor_id() : 0;
echo "Vendor ID in Profile : " . $vendor_id;
do_action( 'ced_etsy_wcfm_refresh_token', $shop_name, $vendor_id );
$getTaxonomyNodeProperties  = Ced_Etsy_WCFM_API_Request( $shop_name, $vendor_id )->get( "application/seller-taxonomy/nodes/{$ced_etsy_wcfm_category_id}/properties", $shop_name );
$all_properties                 = isset( $getTaxonomyNodeProperties['results'] ) ? $getTaxonomyNodeProperties['results'] : array();
$taxonomyList               = $product_field_instance->get_taxonomy_node_properties( $all_properties );
$attributes                 = wc_get_attribute_taxonomies();
$attr_options               = array();
$added_meta_keys            = array_merge(get_option( 'ced_etsy_wcfm_selected_metakeys', array() ) , array('_woocommerce_title','_woocommerce_short_description','_woocommerce_description'));
$select_dropdown_html       = '';

if ( $added_meta_keys && count( $added_meta_keys ) > 0 ) {
	foreach ( $added_meta_keys as $meta_key ) {
		$attr_options[trim( $meta_key )] = $meta_key;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributes_object ) {
		$attr_options[trim( 'umb_pattr_' . $attributes_object->attribute_name )] = $attributes_object->attribute_label;
	}
}

$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
if( in_array( 'etsy', $enabled_marketplaces )  ) {
	if ( ! empty( $profile_id ) || $profile_id === 0 || $profile_id === '0' ) {
		?>
		<form action="" method="post">
			<?php wp_nonce_field( 'profile_creation', 'profile_creation_submit' ); ?>
			<div class="ced_etsy_wcfm_profile_details_wrapper">
				<div class="ced_etsy_wcfm_profile_details_fields">
					<table class="ced_etsy_wcfm_profile_details_table">
						<tbody>		
							<tr>
								<td>
									<label><?php esc_html_e( 'Profile Name', 'woocommerce-etsy_wcfm-integration' ); ?></label>
								</td>
								<?php

								if ( isset( $ced_etsy_wcfm_profile_data['profile_name'] ) ) {
									?>
									<td>
										<label><b><?php echo esc_attr( $ced_etsy_wcfm_profile_data['profile_name'] ); ?></b></label>
									</td>
									<td></td>
								</tr>
								<?php
							}
							?>
							<tr>
								<?php

									$market_place         = 'ced_etsy_wcfm_required_common';
									$product_id           = 0;
									$index_to_use         = 0;
									
									?>
									<th  class="ced_etsy_wcfm_profile_heading basic_heading ced_etsy_wcfm_settings_heading" colspan="4">
										<label class="basic_heading"><?php esc_html_e( 'PRODUCT SPECIFIC', 'woocommerce-etsy_wcfm-integration' ); ?></label>
									</th>
									<?php
									if ( ! empty( $ced_etsy_wcfm_product_fields ) && ! empty( $ced_etsy_wcfm_product_fields ) ) {
										$required_in_any_case = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
										foreach ( $ced_etsy_wcfm_product_fields as $index => $field_data ) {
									$isText = true;

											$check    = false;
											$field_id = isset( $field_data['id'] ) ? $field_data['id'] : '';

											echo '<tr class="form-field _umb_id_type_field ">';
											$label = isset( $field_data['fields']['label'] ) ? $field_data['fields']['label'] : '';

											$field_id     = trim( $field_id, '_' );
											$category_id  = '';
											$product_id   = '';
											$market_place = 'ced_etsy_wcfm_required_common';
											$description  = isset( $field_data['fields']['description'] ) ? $field_data['fields']['description'] : '';
											$required     = isset( $field_data['required'] ) ? (bool) $field_data['required'] : '';
											$index_to_use = 0;
											$default      = isset( $ced_etsy_wcfm_profile_details[ $field_data['fields']['id'] ]['default'] ) ? $ced_etsy_wcfm_profile_details[ $field_data['fields']['id'] ]['default'] : '';
											$field_value  = array(
												'case'  => 'profile',
												'value' => $default,
											);

											if ( '_text_input' == $field_data['type'] ) {
												$product_field_instance->renderInputTextHTML(
													$field_id,
													$label,
													$category_id,
													$product_id,
													$market_place,
													$description,
													$index_to_use,
													$field_value,
													$required
												);
											} elseif ( '_select' == $field_data['type'] ) {
												$value_for_dropdown = $field_data['fields']['options'];
												$product_field_instance->renderDropdownHTML(
													$field_id,
													$label,
													$value_for_dropdown,
													$category_id,
													$product_id,
													$market_place,
													$description,
													$index_to_use,
													$field_value,
													$required
												);
											}elseif ( '_hidden' == $field_data['type'] ) {

										$product_field_instance->renderInputTextHTMLhidden(
											$field_id,
											$label,
											$category_id,
											$product_id,
											$market_place,
											$description,
											$index_to_use,
											array(
												'case'  => 'profile',
												'value' => $ced_etsy_wcfm_category_id,
											),
											$required
										);
										$isText = false;
										echo "<td>";
										echo "</td>";
									}

											if($isText) {
												echo '<td>';
											$previous_selected_value = 'null';
											if ( isset( $ced_etsy_wcfm_profile_details[ $field_data['fields']['id'] ]['metakey'] ) && 'null' != $ced_etsy_wcfm_profile_details[ $field_data['fields']['id'] ]['metakey'] ) {
												$previous_selected_value = $ced_etsy_wcfm_profile_details[ $field_data['fields']['id'] ]['metakey'];
											}
											$select_id = $field_data['fields']['id'] . '_attribute_meta';
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
														<option value="<?php echo esc_attr( $attr_key ); ?>"<?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
														<?php
													endforeach;
												}
												?>
											</select>
											<?php


											echo '</td>';
											}
											echo '</tr>';
										}
										?>
										<tr>
										<th  class="ced_etsy_profile_heading ced_etsy_wcfm_profile_heading basic_heading ced_etsy_settings_heading" colspan="4">
											<label class="basic_heading"><?php esc_html_e( 'CATEGORY SPECIFIC', 'woocommerce-etsy-integration' ); ?></label>
										</th>
										<?php
										foreach ( $taxonomyList as $key => $value ) {

											$isText   = true;
											$field_id = trim( $value['fields']['id'], '_' );
											$default  = isset( $ced_etsy_wcfm_profile_details[ $value['fields']['id'] ] ) ? $ced_etsy_wcfm_profile_details[ $value['fields']['id'] ] : '';
											$default  = isset( $default['default'] ) ? $default['default'] : '';
											echo '<tr class="form-field _umb_brand_field ">';
											if ( '_select' == $value['type'] ) {
												$valueForDropdown     = $value['fields']['options'];
												$tempValueForDropdown = array();
												foreach ( $valueForDropdown as $key => $_value ) {
													$tempValueForDropdown[ $key ] = $_value;
												}
												$valueForDropdown = $tempValueForDropdown;

												$product_field_instance->renderDropdownHTML(
													$field_id,
													ucfirst( $value['fields']['label'] ),
													$valueForDropdown,
													$category_id,
													$product_id,
													$market_place,
													$value['fields']['description'],
													$index_to_use,
													array(
														'case' => 'profile',
														'value' => $default,
													)
												);
												$isText = true;
											} else {
												continue;
											}

											echo '<td>';
											if ( $isText ) {
											$previous_selected_value = 'null';
											if ( isset( $ced_etsy_wcfm_profile_details[ $value['fields']['id'] ]['metakey'] ) && 'null' != $ced_etsy_wcfm_profile_details[ $value['fields']['id'] ]['metakey'] ) {
												$previous_selected_value = $ced_etsy_wcfm_profile_details[ $value['fields']['id'] ]['metakey'];
											}
											$select_id = $value['fields']['id'] . '_attribute_meta';
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
														<option value="<?php echo esc_attr( $attr_key ); ?>"<?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
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
								?>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div>
				<button class="ced_etsy_wcfm_custom_button save_profile_button ced-wcfm-btn" name="ced_etsy_wcfm_profile_save_button" ><?php esc_html_e( 'Save Profile', 'woocommerce-etsy_wcfm-integration' ); ?></button>

			</div>
		</form>
		<?php
	}

}