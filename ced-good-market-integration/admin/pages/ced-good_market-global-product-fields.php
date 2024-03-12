<?php
/**
 * Global Product fields
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_good_market_header();

$fields_file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/class-ced-good_market-product-fields.php';
if ( file_exists( $fields_file ) ) {
	include_once $fields_file;
	$product_field_instance = Ced_Good_Market_Product_Fields::get_instance();
}
$ced_good_market_global_settings = get_option( 'ced_good_market_global_settings', array() );
if ( isset( $_POST['ced_good_market_global_save_button'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}
	$ced_good_market_global_settings = array();
	$is_active                       = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplace_name                = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'good_market';
	$ced_good_market_global_settings = get_option( 'ced_good_market_global_settings', array() );
	$global_settings_information = array();

	if ( isset( $_POST['ced_good_market_required_common'] ) ) {
		$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		foreach ( ( $post_array['ced_good_market_required_common'] ) as $key ) {
			$array_to_save = array();
			isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = $post_array[ $key ][0] : $array_to_save['default'] = '';
			if ( 'global_keyfeatures' == $key ) {
				isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = $post_array[ $key ] : $array_to_save['default'] = '';
			}
			if ( '_umb_' . $marketplace_name . '_subcategory' == $key ) {
				isset( $post_array[ $key ] ) ? $array_to_save['default'] = $post_array[ $key ] : $array_to_save['default'] = '';
			}
			isset( $post_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = $post_array[ $key . '_attribute_meta' ] : $array_to_save['metakey'] = 'null';
			$global_settings_information[ $key ] = $array_to_save;
		}
	}
	$ced_good_market_global_settings = json_encode( $global_settings_information );
	update_option( 'ced_good_market_global_settings', $ced_good_market_global_settings );
}
$ced_good_market_global_data = get_option( 'ced_good_market_global_settings', array() );
if ( ! empty( $ced_good_market_global_data ) ) {
	$ced_good_market_global_data = $ced_good_market_global_data;
	$ced_good_market_global_data = json_decode( $ced_good_market_global_data, true );
}

$global_fields_file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/good_market-global-setting.json';
$global_fields      = file_get_contents( $global_fields_file );
$global_fields      = json_decode( $global_fields, true );

$attributes           = wc_get_attribute_taxonomies();
$attr_options         = array();
$added_meta_keys      = get_option( 'ced_good_market_selected_metakeys', false );
$select_dropdown_html = '';

if ( $added_meta_keys && count( $added_meta_keys ) > 0 ) {
	foreach ( $added_meta_keys as $meta_key ) {
		if ( strpos( $meta_key, 'attribute_' ) !== false ) {
			$attr_options[ $meta_key ] = $meta_key;
		}
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributes_object ) {
		$attr_options[ 'umb_pattr_' . $attributes_object->attribute_name ] = $attributes_object->attribute_label;
	}
}

?>
<div class="ced_good_market_heading">
	<?php echo esc_html( get_instuction_html() ); ?>
	<div class="ced_good_market_child_element default_modal">
		<ul type="disc">
			<li><?php print_r( 'Please map all of the product attributes from your WooCommerce store to the Good Market options. Good Market only has 3 option types (Size, Color, and Type) so you may need to map multiple WooCommerce attributes to each Good Market option' ); ?></li>
			
		</ul>
	</div>
</div>
<form action="" method="post">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
	<div class="ced_good_market_heading">
		<div class="ced_goodmarket_global_attribute_mapping"><h2>Map Product Attributes</h2></div>
		
	<div class="ced_good_market_global_details_wrapper ced_good_market_child_element default_modal">
		<div class="ced_good_market_global_details_fields ced_good_market_global_tab_list_wrap">
			<?php
			if ( file_exists( $fields_file ) ) {
				if ( ! empty( $global_fields ) && ! empty( $global_fields ) ) {
					$global_data        = isset( $ced_good_market_global_data ) ? $ced_good_market_global_data : array();
					$market_place       = 'ced_good_market_required_common';
					$product_id         = 0;
					$index_to_use       = 0;
					$ced_good_market_id = '';
					echo '<div class="ced_good_market_global_tab_list_content ced_tab_labels">';
					foreach ( $global_fields as $index => $value ) {
						$class = '';
						if ( 'product_specific' == $index ) {
							$class = 'active';
						}
						echo '<div class="ced_good_market_global_tab_list ced_good_market_global_tab_label ' . esc_attr( $class ) . '" data-tab="' . esc_attr( $index ) . '">';
						echo '<label class="ced_good_market_global_tab_label">' . esc_attr( strtoupper( str_replace( '_', ' ', $index ) ) ) . '</label>';
						echo '</div>';
					}
					echo '</div>';
					echo '<div class="ced_good_market_global_tab_list_content ced_tab_content">';
					$shipping_template_array = array(
						'code'           => 'shipping_template',
						'default_value'  => null,
						'description'    => 'Add Shipping Template for item on Good Market.',
						'label'          => 'Shipping Templates',
						'required'       => false,
						'type'           => 'LIST',
						'type_parameter' => null,
						'values'         => null,
					);
					if ( isset( $ced_good_market_shipping_templates ) && is_array( $ced_good_market_shipping_templates ) ) {
						foreach ( $ced_good_market_shipping_templates['shippingTemplates'] as $key => $value ) {
							$shipping_template_array['values_list'][] = array(
								'code'  => $value['id'],
								'label' => $value['name'],
							);
						}
					}
					$fulfillment_center_array = array(
						'code'           => 'fulfillment_center',
						'default_value'  => null,
						'description'    => 'Add Fulfillment Center for  Template for item on Good Market.',
						'label'          => 'Fulfillment Center',
						'required'       => false,
						'type'           => 'LIST',
						'type_parameter' => null,
						'values'         => null,
					);
					if ( isset( $ced_good_market_fulfillment_centers ) && is_array( $ced_good_market_fulfillment_centers ) ) {
						foreach ( $ced_good_market_fulfillment_centers as $key => $value ) {
							$fulfillment_center_array['values_list'][] = array(
								'code'  => $value['shipNode'],
								'label' => $value['shipNodeName'],
							);
						}
					}

					foreach ( $global_fields as $key => $value ) {
						$style = 'none';
						if ( 'product_specific' == $key ) {
							$style = 'block';
						}
						echo '<div class="ced_good_market_global_fields_mapping" style="display :' . esc_attr( $style ) . '" id="' . esc_attr( $key ) . '"><table class="wp-list-table widefat stripped fixed"><tbody><tr><td><b>Good Market Option Types</b></td><td><b>WooCommerce Product Attribute</b></td></tr>';
						foreach ( $value as $index => $fields_data ) {
							$is_add_html    = false;
							$is_text        = true;
							$required       = isset( $fields_data['required'] ) ? $fields_data['required'] : false;
							$required_label = '*';
							$description    = isset( $fields_data['description'] ) ? $fields_data['description'] : '';
							if ( empty( $description ) ) {
								$description = isset( $fields_data['label'] ) ? $fields_data['label'] : '';
							}
							$field_id = trim( $fields_data['code'] );
							$default  = isset( $global_data[ $ced_good_market_id . '_' . $fields_data['code'] ] ) ? $global_data[ $ced_good_market_id . '_' . $fields_data['code'] ] : '';
							$default  = isset( $default['default'] ) ? $default['default'] : '';

							echo '<tr class="form-field ' . esc_attr( $key ) . '">';
							if ( 'LIST' == $fields_data['type'] ) {
								$value_for_dropdown = ! empty( $fields_data['values_list'] ) ? $fields_data['values_list'] : array();
								$product_field_instance->ced_good_market_render_dropdown_html(
									$field_id,
									ucfirst( $fields_data['label'] ),
									$value_for_dropdown,
									$ced_good_market_id,
									$product_id,
									$market_place,
									$description,
									$index_to_use,
									array(
										'case'  => 'global',
										'value' => $default,
									),
									$required,
									$required_label,
									false,
									false
								);
								$is_text = false;
							} else {
								$is_text = true;
								if ( 'keyfeatures' == $fields_data['code'] ) {
									$is_text     = false;
									$is_add_html = true;
								}
								$product_field_instance->ced_good_market_render_text_html(
									$field_id,
									ucfirst( $fields_data['label'] ),
									$ced_good_market_id,
									$product_id,
									$market_place,
									$description,
									$index_to_use,
									array(
										'case'  => 'global',
										'value' => $default,
									),
									'',
									$is_add_html,
									$required_label,
									'',
									'',
									true
								);
							}

							echo '<td>';
							if ( $is_add_html ) {
								echo '<tr class="form-field key_features"><input type="hidden" name="ced_good_market_required_common[]" value="global_keyfeatures_1"><td><input class="short" style="" name="global_keyfeatures[]" id="" value="" placeholder="" type="text"></td><td><input type="button" class="button button-primary ced_good_market_add_key_feature" value="+"></td></tr>';
							}
							if ( $is_text ) {
								$previous_selected_value = 'null';
								if ( isset( $global_data[ $ced_good_market_id . '_' . $fields_data['code'] ] ) && ! empty( $global_data[ $ced_good_market_id . '_' . $fields_data['code'] ] ) ) {
									$previous_selected_value = $global_data[ $ced_good_market_id . '_' . $fields_data['code'] ]['metakey'];
								}
								$select_id   = $ced_good_market_id . '_' . $fields_data['code'] . '_attribute_meta';
								$select_name = $ced_good_market_id . '_' . $fields_data['code'] . '_attribute_meta[]';
								?>
								<select class="ced_good_market_attribie_mapping" id="<?php echo esc_attr( $select_id ); ?>" multiple class="select2 ced_select_2" name="<?php echo esc_attr( $select_name ); ?>">
									<option value="null" > -- select -- </option>
									<?php
									if ( is_array( $attr_options ) ) {
										foreach ( $attr_options as $attr_key => $attr_name ) :
											// if ( trim( $previous_selected_value == $attr_key ) ) {
											$selected = '';
											if ( ! empty( $previous_selected_value ) && is_array( $previous_selected_value ) ) {
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
						echo '</tbody></table></div>';
					}
					echo '</div>';
				}
			}
			?>

		</div>
	</div>
</div>
	<div class="good_market-button-wrap">
		<input type="submit" class="ced_good_market_custom_button button button-primary" name="ced_good_market_global_save_button" value="Save">		
	</div>
</form>


<?php require_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/pages/ced-good_market-metakeys-template.php'; ?>
