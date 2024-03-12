<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/product-fields.php';
$profileID = isset( $_GET['profileID'] ) ? sanitize_text_field( wp_unslash( $_GET['profileID'] ) ) : '';

global $wpdb;
$tableName = $wpdb->prefix . 'ced_tokopedia_profiles';

if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_tokopedia_profile_save_button'] ) ) {

	if ( ! isset( $_POST['profile_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['profile_settings_submit'] ) ), 'ced_tokopedia_profile_save_button' ) ) {
		return;
	}
	$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$profileName     = $sanitized_array['ced_tokopedia_profile_name'];
	$marketplaceName = isset( $sanitized_array['marketplaceName'] ) ? $sanitized_array['marketplaceName'] : 'all';

	foreach ( $sanitized_array['ced_tokopedia_required_common'] as $key ) {

		$arrayToSave = array();
		isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['default'] = $sanitized_array[ $key ][0] : $arrayToSave['default'] = '';

		if ( '_umb_' . $marketplaceName . '_subcategory' == $key ) {
			isset( $sanitized_array[ $key ] ) ? $arrayToSave['default'] = $sanitized_array[ $key ] : $arrayToSave['default'] = '';
		}

		if ( '_umb_tokopedia_category' == $key && empty( $profileID ) ) {

			$profileCategoryNames = array();
			for ( $i = 1; $i < 8; $i++ ) {
				$profileCategoryNames[] = isset( $sanitized_array[ 'ced_tokopedia_level' . $i . '_category' ] ) ? $sanitized_array[ 'ced_tokopedia_level' . $i . '_category' ] : '';
			}
			$CategoryNames = array();
			foreach ( $profileCategoryNames as $key1 => $value1 ) {
				$CategoryNames[] = explode( ',', $value1[0] );
			}
			foreach ( $CategoryNames as $key2 => $value2 ) {
				if ( ! empty( $CategoryNames[ $key2 ][0] ) ) {
					$profile_category_id = $CategoryNames[ $key2 ][0];
				}
			}
			$category_id = $profile_category_id;
			isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['default'] = $category_id : $arrayToSave['default'] = '';

		}

		isset( $sanitized_array[ $key . '_attibuteMeta' ] ) ? $arrayToSave['metakey'] = $sanitized_array[ $key . '_attibuteMeta' ] : $arrayToSave['metakey'] = 'null';
		$updateinfo[ $key ] = $arrayToSave;
	}

	$updateinfo['selected_product_id']   = isset( $sanitized_array['selected_product_id'] ) ? sanitize_text_field( wp_unslash( $sanitized_array['selected_product_id'] ) ) : '';
	$updateinfo['selected_product_name'] = isset( $sanitized_array['ced_sears_pro_search_box'] ) ? sanitize_text_field( wp_unslash( $sanitized_array['ced_sears_pro_search_box'] ) ) : '';
	$updateinfo                          = json_encode( $updateinfo );


	if ( empty( $profileID ) ) {
		$profileCategoryNames = array();
		for ( $i = 1; $i < 8; $i++ ) {
			$profileCategoryNames[] = isset( $sanitized_array[ 'ced_tokopedia_level' . $i . '_category' ] ) ? $sanitized_array[ 'ced_tokopedia_level' . $i . '_category' ] : '';
		}
		$CategoryNames = array();
		foreach ( $profileCategoryNames as $key => $value ) {
			$CategoryNames[] = explode( ',', $value[0] );
			if ( ! empty( $CategoryNames[ $key ][1] ) ) {
				$CategoryName .= $CategoryNames[ $key ][1] . '-->';
			}
		}
		$catl        = strlen( $CategoryName );
		$profileName = substr( $CategoryName, 0, -3 );
		foreach ( $CategoryNames as $key1 => $value1 ) {
			if ( ! empty( $CategoryNames[ $key1 ][0] ) ) {
				$profile_category_id = $CategoryNames[ $key1 ][0];
			}
		}
		$profile_category_id = $profile_category_id;
		$shop_name           = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';

		$profileDetails = array(
			'profile_name'   => $profileName,
			'profile_status' => 'active',
			'profile_data'   => $updateinfo,
			'shop_name'      => $shop_name,
		);

		global $wpdb;
		$profileTableName = $wpdb->prefix . 'ced_tokopedia_profiles';
		$wpdb->insert( $profileTableName, $profileDetails );
		$profileId = $wpdb->insert_id;

		$profile_edit_url = admin_url( 'admin.php?page=ced_tokopedia&profileID=' . $profileId . '&section=profiles-view&panel=edit&shop_name=' . $shop_name );
		header( 'location:' . $profile_edit_url . '' );
	} elseif ( $profileID ) {

		$wpdb->update(
			$tableName,
			array(
				'profile_name'   => $profileName,
				'profile_status' => 'Active',
				'profile_data'   => $updateinfo,
			),
			array( 'id' => $profileID )
		);
	}
}

$tokopediaFirstLevelCategories = file_get_contents( CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/json/categoryLevel-1.json' );
$tokopediaFirstLevelCategories = json_decode( $tokopediaFirstLevelCategories, true );
$profile_data                  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `id`=%s ", $profileID ), 'ARRAY_A' );

if ( ! empty( $profile_data ) ) {

	$profile_category_data = json_decode( $profile_data[0]['profile_data'], true );

	$profile_category_data = isset( $profile_category_data ) ? $profile_category_data : '';

	$profile_category_id = isset( $profile_category_data['_umb_tokopedia_category']['default'] ) ? (int) $profile_category_data['_umb_tokopedia_category']['default'] : '';

	$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
}

$attributes  = wc_get_attribute_taxonomies();
$attrOptions = array();

$addedMetaKeys = get_option( 'ced_tokopedia_selected_metakeys', array() );

if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
	foreach ( $addedMetaKeys as $metaKey ) {
		$attrOptions[ $metaKey ] = $metaKey;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributesObject ) {
		$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
	}
}
$get_posts = get_posts(
	array(
		'numberposts' => 5,
		'post_type'   => array( 'product_variation' ),
		'fields'      => 'ids',
	)
);
foreach ( $get_posts as $key => $productId ) {
	$getPostCustom[] = get_post_custom( $productId );
	$_product        = wc_get_product( $productId );

	if ( 'variation' == $_product->get_type() ) {
		$parentId              = $_product->get_parent_id();
		$getParentPostCustom[] = get_post_custom( $parentId );
		$getPostCustom         = array_merge( $getPostCustom, $getParentPostCustom );
	}
}
if ( isset( $getPostCustom ) && ! empty( $getPostCustom ) ) {
	foreach ( $getPostCustom as $key => $value ) {
		$key                                     = str_replace( 'attribute_', '', $key );
		$getPostCustomend[ 'umb_pattr_' . $key ] = $key;
	}
}
if ( is_array( $getPostCustomend ) ) {
	$attrOptions = array_merge( $attrOptions, $getPostCustomend );
}
/* select dropdown setup */
ob_start();
$fieldID  = '{{*fieldID}}';
$selectId = $fieldID . '_attibuteMeta';
echo '<select id="' . esc_attr( $selectId ) . '" name="' . esc_attr( $selectId ) . '">';
echo '<option value="null"> -- select -- </option>';
if ( is_array( $attrOptions ) ) {
	foreach ( $attrOptions as $attrKey => $attrName ) :
		echo '<option value="' . esc_attr( $attrKey ) . '">' . esc_attr( $attrName ) . '</option>';
	endforeach;
}
echo '</select>';
$selectDropdownHTML   = ob_get_clean();
$productFieldInstance = Ced_Tokopedia_Product_Fields::get_instance();
$fields               = $productFieldInstance->get_custom_products_fields();
?>
<?php require_once CED_TOKOPEDIA_DIRPATH . 'admin/pages/ced-tokopedia-metakeys-template.php'; ?>
<form action="" method="post">
	<?php wp_nonce_field( 'ced_tokopedia_profile_save_button', 'profile_settings_submit' ); ?>
	<div class="ced_tokopedia_profile_details_wrapper">
		<div class="ced_tokopedia_config_details_fields">
			<table>
				<tbody>
					<thead>
						<tr>
							<th class="ced_tokopedia_profile_heading ced_tokopedia_settings_heading">
								<label class="basic_heading"><?php esc_html_e( 'BASIC DETAILS', 'woocommerce-tokopedia-integration' ); ?></label>
							</th>
						</tr>
					</thead>
					<tbody>		
						<tr>
							<td>
								<label><?php esc_html_e( 'Profile Name', 'woocommerce-tokopedia-integration' ); ?></label>
							</td>
							<?php
							if ( isset( $profile_data['profile_name'] ) ) {
								?>
								<td>
									<input type="text" name="ced_tokopedia_profile_name" value="<?php echo esc_attr( $profile_data['profile_name'] ); ?>">
								</td>
							</tr>
								<?php
							} else {
								?>
							<td data-catlevel="1" id="ced_tokopedia_categories_in_profile">
								<select class="ced_tokopedia_select_category_on_add_profile select2 ced_tokopedia_select2 ced_tokopedia_level1_category"  name="ced_tokopedia_level1_category[]" data-level=1 >
									<option value="">--<?php esc_html_e( '--Select--', 'woocommerce-tokopedia-integration' ); ?>--</option>
									<?php
									foreach ( $tokopediaFirstLevelCategories as $key1 => $value1 ) {
										if ( isset( $value1['name'] ) && ! empty( $value1['name'] ) ) {
											?>
											<option value="<?php echo esc_attr( $value1['id'] . ',' . $value1['name'] ); ?>"><?php echo esc_attr( $value1['name'] ); ?></option>	
											<?php
										}
									}
									?>
								</select>
							</td>
							<td><a><?php esc_html_e( '( Please Select Profile Name )', 'woocommerce-tokopedia-integration' ); ?></a></td>
								<?php
							}
							?>
						<tr>
							<th  class="ced_tokopedia_profile_heading basic_heading ced_tokopedia_settings_heading">
								<label class="basic_heading"><?php esc_html_e( 'GENERAL DETAILS', 'woocommerce-tokopedia-integration' ); ?></label>
							</th>
						</tr>
						<tr>
							<?php
							$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
							$marketPlace       = 'ced_tokopedia_required_common';
							$productID         = 0;
							$categoryID        = '';
							$indexToUse        = 0;
							if ( ! empty( $profile_data ) ) {

								$data = json_decode( $profile_data['profile_data'], true );
							}
							$productFieldInstance = Ced_Tokopedia_Product_Fields::get_instance();
							$fields               = $productFieldInstance->get_custom_products_fields();

							foreach ( $fields as $value ) {

								$isText   = true;
								$field_id = trim( $value['fields']['id'], '_' );

								if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
									$attributeNameToRender  = ucfirst( $value['fields']['label'] );
									$attributeNameToRender .= '<span class="ced_tokopedia_wal_required"> [ Required ]</span>';
								} else {

									$attributeNameToRender = ucfirst( $value['fields']['label'] );
								}

								$is_required = isset( $value['fields']['is_required'] ) ? $value['fields']['is_required'] : false;
								$default     = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
								// creating tr to make new options field
								echo '<tr class="form-field _umb_id_type_field ">';
								if ( '_select' == $value['type'] ) {
									$valueForDropdown = $value['fields']['options'];
									if ( '_umb_id_type' == $value['fields']['id'] ) {
										unset( $valueForDropdown['null'] );
									}
									$valueForDropdown = apply_filters( 'ced_tokopedia_alter_data_to_render_on_profile', $valueForDropdown, $field_id );
									$productFieldInstance->renderDropdownHTML(
										$field_id,
										$attributeNameToRender,
										$valueForDropdown,
										$categoryID,
										$productID,
										$marketPlace,
										$value['fields']['description'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$is_required
									);
									$isText = false;
								} elseif ( '_text_input' == $value['type'] ) {
									$productFieldInstance->renderInputTextHTML(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$value['fields']['description'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$is_required
									);
								} elseif ( '_checkbox' == $value['type'] ) {
									$productFieldInstance->rendercheckboxHTML(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$value['fields']['description'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$is_required
									);
									$isText = false;
								} elseif ( '_hidden' == $value['type'] ) {

									$profile_category_id = isset( $profile_category_id ) ? $profile_category_id : '';
									$productFieldInstance->renderInputTextHTMLhidden(
										$field_id,
										$attributeNameToRender,
										$categoryID,
										$productID,
										$marketPlace,
										$value['fields']['description'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $profile_category_id,
										),
										$is_required
									);
									$isText = false;
								}

								echo '<td>';
								if ( $isText ) {
									$previousSelectedValue = 'null';
									if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && 'null' == $data[ $value['fields']['id'] ]['metakey'] ) {
										$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
									}
									$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
									$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
									print_r( $updatedDropdownHTML );
								}
								echo '</td>';
								echo '</tr>';
							}
							?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div>
			<button id="save_shipping_settings"  name="ced_tokopedia_profile_save_button" class="button-ced_tokopedia ced_tokopedia_custom_button"><?php esc_html_e( 'Save Profile', 'woocommerce-tokopedia-integration' ); ?></button>			
		</div>

	</form>
