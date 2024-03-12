<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_reverb_header();

$profileID = isset( $_GET['profileID'] ) ? sanitize_text_field( $_GET['profileID'] ) : '';
$notice    = '';

$reverb_profiles = get_option( 'ced_reverb_profiles_list', array() );
$categoryid      = '';

if ( ! empty( $reverb_profiles[ $profileID ] ) && is_array( $reverb_profiles ) ) {
	$category_id = $reverb_profiles[ $profileID ]['reverb_cat_id'];
}

if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_reverb_profile_save_button'] ) ) {
	$ced_reverb_profile_edit_nonce = isset( $_POST['ced_reverb_profile_edit_nonce'] ) ? sanitize_text_field( $_POST['ced_reverb_profile_edit_nonce'] ) : '';
	if ( wp_verify_nonce( $ced_reverb_profile_edit_nonce, 'ced_reverb_profile_edit_nonce' ) ) {
		$is_active                    = isset( $_POST['profile_status'] ) ? 'active' : 'active';
		$marketplaceName              = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( $_POST['marketplaceName'] ) : 'all';
		$updateinfo                   = array();
		$ced_reverb_profile_wholeData = isset( $_POST['ced_reverb_required_common'] ) ? array_map( 'sanitize_text_field', $_POST['ced_reverb_required_common'] ) : '';
		if ( ! empty( $ced_reverb_profile_wholeData ) ) {
			foreach ( $ced_reverb_profile_wholeData as $key ) {
				$arrayToSave = array();

				isset( $_POST[ $key ][0] ) ? $arrayToSave['default'] = sanitize_text_field( $_POST[ $key ][0] ) : $arrayToSave['default'] = '';
				if ( '_umb_' . $marketplaceName . '_subcategory' == $key ) {
					isset( $_POST[ $key ] ) ? $arrayToSave['default'] = sanitize_text_field( $_POST[ $key ] ) : $arrayToSave['default'] = '';
				}
				if ( '_umb_reverb_category' == $key && '' == $profileID ) {
					$profileCategoryNames = array();
					for ( $i = 1; $i < 8; $i++ ) {
						$profileCategoryNames[] = isset( $_POST[ 'ced_reverb_level' . $i . '_category' ] ) ? sanitize_text_field( $_POST[ 'ced_reverb_level' . $i . '_category' ] ) : '';
					}


					$CategoryNames = array();
					foreach ( $profileCategoryNames as $key1 => $value1 ) {
						if ( isset( $value1[0] ) && ! empty( $value1[0] ) ) {
							$CategoryName = $value1[0];
						}
					}
					$categoryid = $CategoryName;
					isset( $_POST[ $key ][0] ) ? $arrayToSave['default'] = $categoryid : $arrayToSave['default'] = '';

				}

				isset( $_POST[ $key . '_attibuteMeta' ] ) ? $arrayToSave['metakey'] = sanitize_text_field( $_POST[ $key . '_attibuteMeta' ] ) : $arrayToSave['metakey'] = 'null';
				$updateinfo[ $key ] = $arrayToSave;
			}
		}

		$updateinfo = json_encode( $updateinfo );

		if ( '' != $profileID ) {
			$category_specificData = get_option( 'ced_reverb_profile_data', array() );
			if ( empty( $category_specificData ) ) {
				$category_specificData[ $category_id ] = $updateinfo;
			} else {
				if ( is_array( $category_specificData ) ) {

					foreach ( $category_specificData as $key => $value ) {
						if ( $key == $category_id ) {
							$category_specificData[ $key ] = $updateinfo;
							$check                         = true;
							break;
						}
					}
				}
				if ( false == $check ) {
					$category_specificData[ $category_id ] = $updateinfo;
				}
			}
			update_option( 'ced_reverb_profile_data', $category_specificData );
		}
	}
}

$attributes         = wc_get_attribute_taxonomies();
$attrOptions        = array();
$addedMetaKeys      = get_option( 'ced_reverb_selected_metakeys', array() );
$addedMetaKeys      = array_merge( $addedMetaKeys, array( '_woocommerce_title', '_woocommerce_short_description', '_woocommerce_description' ) );
$selectDropdownHTML = '';

if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
	foreach ( $addedMetaKeys as $key => $metaKey ) {
		if ( is_array( $metaKey ) ) {
			continue;
		}
		$attrOptions[ $metaKey ] = $metaKey;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributesObject ) {
		$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
	}
}
/* select dropdown setup */
ob_start();
$fieldID  = '{{*fieldID}}';
$selectId = '_ced_reverb_' . $fieldID . '_attibuteMeta';

$selectDropdownHTML .= '<select id="' . $selectId . '" name="' . $selectId . '">';
$selectDropdownHTML .= '<option value="null"> -- select -- </option>';
if ( is_array( $attrOptions ) ) {
	foreach ( $attrOptions as $attrKey => $attrName ) :
		$selectDropdownHTML .= '<option value="' . $attrKey . '">' . $attrName . '</option>';
	endforeach;
}
$selectDropdownHTML .= '</select>';


$file = CED_REVERB_DIRPATH . 'admin/partials/class-ced-reverb-product-fields.php';
reverb_include_file( $file );
$productFieldInstance = new Ced_Reverb_Product_Fields();

$fields       = $productFieldInstance->ced_reverb_get_custom_products_fields();
$misce_fields = $productFieldInstance->ced_reverb_get_custom_miscellaneous_fields();

$shipping_options = array();

$reverbRequest     = new Ced_Reverb_Curl_Request();
$shipping_profiles = $reverbRequest->ced_reverb_get_request( 'shop' );

// $shipping_profiles = json_decode($shipping_profile,true);

if ( isset( $shipping_profiles['shipping_profiles'] ) && is_array( $shipping_profiles ) && ! empty( $shipping_profiles['shipping_profiles'] ) ) {
	$shipping_prof = $shipping_profiles['shipping_profiles'];
}

if ( isset( $shipping_prof ) && is_array( $shipping_prof ) && ! empty( $shipping_prof ) ) {
	foreach ( $shipping_prof as $key => $value ) {
		$shipping_options[ $value['id'] ] = $value['name'];
	}
}

$reverbCategorieslevel1 = file_get_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/categories.json' );
$reverbCategorieslevel1 = json_decode( $reverbCategorieslevel1, true );
$reverbCategorieslevel1 = $reverbCategorieslevel1['categories'];


$ced_reverb_profile_data = get_option( 'ced_reverb_profile_data', array() );
$fieldsData              = '';
if ( ! empty( $ced_reverb_profile_data ) ) {
	if ( isset( $ced_reverb_profile_data[ $category_id ] ) && ! empty( $ced_reverb_profile_data[ $category_id ] ) ) {
		$fieldsData = $ced_reverb_profile_data[ $category_id ];
		$fieldsData = json_decode( $fieldsData, true );
	}
}
?>
<?php
require_once CED_REVERB_DIRPATH . 'admin/pages/ced-reverb-metakeys-template.php';
?>
<form action="" method="post">
<input type="hidden" id="ced_reverb_profile_edit_nonce" name="ced_reverb_profile_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ced_reverb_profile_edit_nonce' ) ); ?>"/>	
	<div class="ced_reverb_heading">
		<?php echo esc_html_e( get_reverb_instuctions_html( 'BASIC INFORMATION' ) ); ?>
		<div class="ced_reverb_child_element">
			<table class="wp-list-table fixed widefat ced_reverb_config_table">
				<tr>
					<td>
						<label><?php esc_html_e( 'Profile Name', 'woocommerce-reverb-integration' ); ?></label>
					</td>
					<?php

					if ( isset( $reverb_profiles[ $profileID ]['reverb_cat_name'] ) ) {
						?>
						<td>
							<input type="text" name="ced_reverb_profile_name" value="<?php echo esc_attr( $reverb_profiles[ $profileID ]['reverb_cat_name'] ); ?>">
						</td>
					</tr>
						<?php
					}
					?>
				<tr>
				</table>
			</div>
		</div>

			<div class="ced_reverb_heading">
				<?php echo esc_html_e( get_reverb_instuctions_html( 'Product Export Settings' ) ); ?>
			<div class="ced_reverb_child_element">
				<button class="button glob" profile-id=<?php echo $profileID; ?>>Copy Data from Global Settings</button>
				<table class="wp-list-table ced_reverb_global_settings">
				<tbody>	
				<tr>
						<td><b>Reverb Attribute</b></td>
						<td><b>Default Value</b></td>
						<td><b>Pick Value From</b></td>
					</tr>	
					
					<tr>
						<?php
						$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
						$requiredAnyCase   = '';
						global $global_CED_reverb_Render_Attributes;
						$marketPlace        = 'ced_reverb_required_common';
						$productID          = 0;
						$categoryID         = '';
						$indexToUse         = 0;
						$selectDropdownHTML = $selectDropdownHTML;
						$description        = '';

							// product specific custom attribute
							$product_specific_attribute_key = get_option( 'ced_reverb_product_specific_attribute_key_' . $category_id );

						if ( ! empty( $fields ) ) {
							foreach ( $fields as $value ) {
								$isText      = true;
								$check       = false;
								$description = '';
								$field_id    = trim( $value['fields']['id'], '_' );
								if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
									$attributeNameToRender  = ucfirst( $value['fields']['label'] );
									$attributeNameToRender .= '<span class="ced_reverb_wal_required">' . __( '[ Required ]', 'woocommerce-reverb-integration' ) . '</span>';
								} else {
									$attributeNameToRender = ucfirst( $value['fields']['label'] );
								}
								$default = isset( $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['default'] ) ? $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['default'] : '';

								if ( null != $value['fields']['description'] ) {
									$description = $value['fields']['description'];
								} else {
									$description = $field_id;
								}


								if ( empty( $product_specific_attribute_key ) ) {
									$product_specific_attribute_key = array( $field_id );
								} else {
									foreach ( $product_specific_attribute_key as $key => $offer_key ) {
										if ( $offer_key == $field_id ) {
											$check = true;
										}
									}
									if ( false == $check ) {
										$product_specific_attribute_key[] = $field_id;
									}
								}
								update_option( 'ced_reverb_product_specific_attribute_key_' . $category_id, $product_specific_attribute_key );

								if ( '_select' == $value['type'] ) {
									echo '<tr class="form-field _umb_id_type_field ">';
									$valueForDropdown = $value['fields']['options'];
									if ( '_umb_id_type' == $value['fields']['id'] ) {
										unset( $valueForDropdown['null'] );
									}
									/**
 									* Filter hook for filtering dropdown values in profile edit page.
 									* @since 1.0.0
 									*/
									$valueForDropdown = apply_filters( 'ced_reverb_alter_data_to_render_on_profile', $valueForDropdown, $field_id );

									$productFieldInstance->render_dropdown_html(
										$field_id,
										$attributeNameToRender,
										$valueForDropdown,
										$categoryID,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$description
									);
									$isText = true;
								} elseif ( '_text_input' == $value['type'] ) {
									echo '<tr class="form-field _umb_id_type_field ">';
									$productFieldInstance->render_input_text_html(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$description
									);
								} elseif ( '_hidden' == $value['type'] ) {
									echo '<tr class="form-field _umb_id_type_field ">';
									$productFieldInstance->render_input_text_html_hidden(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $category_id,
										),
										$value['fields']['description']
									);
									$isText = false;
								} else {
									$isText = true;
								}

								echo '<td>';
								if ( $isText ) {
									$previousSelectedValue = 'null';
									if ( isset( $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['metakey'] ) && 'null' != $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['metakey'] ) {
										$previousSelectedValue = $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['metakey'];
									}
									$previousSelectedValue = trim( $previousSelectedValue );
									$updatedDropdownHTML   = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
									$updatedDropdownHTML   = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
									print_r( $updatedDropdownHTML );
								}
								echo '</td>';
								echo '</tr>';
							}
						}

						$misce_fields_attribute_key = get_option( 'ced_reverb_misce_fields_attribute_key_' . $category_id );

						if ( ! empty( $misce_fields ) ) {
							foreach ( $misce_fields as $value ) {
								$isText      = true;
								$check       = false;
								$description = '';
								$field_id    = trim( $value['fields']['id'], '_' );
								if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
									$attributeNameToRender  = ucfirst( $value['fields']['label'] );
									$attributeNameToRender .= '<span class="ced_reverb_wal_required">' . __( '[ Required ]', 'woocommerce-reverb-integration' ) . '</span>';
								} else {
									$attributeNameToRender = ucfirst( $value['fields']['label'] );
								}
								$default = isset( $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['default'] ) ? $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['default'] : '';

								if ( null != $value['fields']['description'] ) {
									$description = $value['fields']['description'];
								} else {
									$description = $field_id;
								}


								if ( empty( $misce_fields_attribute_key ) ) {
									$misce_fields_attribute_key = array( $field_id );
								} else {
									foreach ( $misce_fields_attribute_key as $key => $offer_key ) {
										if ( $offer_key == $field_id ) {
											$check = true;
										}
									}
									if ( false == $check ) {
										$misce_fields_attribute_key[] = $field_id;
									}
								}
								update_option( 'ced_reverb_misce_fields_attribute_key_' . $category_id, $misce_fields_attribute_key );

								if ( '_select' == $value['type'] ) {
									echo '<tr class="form-field _umb_id_type_field ">';
									$valueForDropdown = $value['fields']['options'];
									if ( '_umb_id_type' == $value['fields']['id'] ) {
										unset( $valueForDropdown['null'] );
									}
									/**
 									* Filter hook for filtering data in dropdown of profile edit page.
 									* @since 1.0.0
 									*/
									$valueForDropdown = apply_filters( 'ced_reverb_alter_data_to_render_on_profile', $valueForDropdown, $field_id );

									$productFieldInstance->render_dropdown_html(
										$field_id,
										$attributeNameToRender,
										$valueForDropdown,
										$categoryID,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$description
									);
									$isText = false;
								} elseif ( '_text_input' == $value['type'] ) {
									echo '<tr class="form-field _umb_id_type_field ">';
									$productFieldInstance->render_input_text_html(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$description
									);
								} elseif ( '_hidden' == $value['type'] ) {
									echo '<tr class="form-field _umb_id_type_field ">';
									$productFieldInstance->render_input_text_html_hidden(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $category_id,
										),
										$value['fields']['description']
									);
									$isText = false;
								} else {
									$isText = false;
								}

								echo '<td>';
								if ( $isText ) {
									$previousSelectedValue = 'null';
									if ( isset( $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['metakey'] ) && 'null' != $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['metakey'] ) {
										$previousSelectedValue = $fieldsData[ '_ced_reverb_' . $value['fields']['id'] ]['metakey'];
									}
									$previousSelectedValue = trim( $previousSelectedValue );
									$updatedDropdownHTML   = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
									$updatedDropdownHTML   = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
									print_r( $updatedDropdownHTML );
								}
								echo '</td>';
								echo '</tr>';
							}
						}
						?>
					</tbody>
				</table>
				</div>
			</div>
		<div>
			<button class="ced_reverb_custom_button save_profile_button button-primary" name="ced_reverb_profile_save_button" ><?php esc_attr_e( 'Save Profile', 'woocommerce-reverb-integration' ); ?></button>

		</div>
	</form>

