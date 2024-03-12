<?php

if ( isset( $_POST['ced_reverb_global_settings'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}
	$ced_reverb_global_settings = array();
	$is_active                  = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplace_name           = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'reverb';
	$ced_reverb_global_settings = get_option( 'ced_reverb_global_settings', array() );

	$offer_settings_information = array();

	if ( isset( $_POST['ced_reverb_required_common'] ) ) {
		$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		foreach ( ( $post_array['ced_reverb_required_common'] ) as $key ) {

			$array_to_save = array();
			isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = $post_array[ $key ][0] : $array_to_save['default'] = '';

			if ( '_umb_' . $marketplace_name . '_subcategory' == $key ) {
				isset( $post_array[ $key ] ) ? $array_to_save['default'] = $post_array[ $key ] : $array_to_save['default'] = '';
			}

			isset( $post_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = $post_array[ $key . '_attribute_meta' ] : $array_to_save['metakey'] = 'null';
			$offer_settings_information[ $key ] = $array_to_save;
		}
	}
	$ced_reverb_global_settings = json_encode( $offer_settings_information );
	update_option( 'ced_reverb_global_settings', $ced_reverb_global_settings );
}
$ced_reverb_global_data = get_option( 'ced_reverb_global_settings', array() );
//$ced_reverb_global_data = json_decode($ced_reverb_global_data, true);
// echo '<pre>';
// print_r($ced_reverb_global_data);
// die;
if ( ! empty( $ced_reverb_global_data ) ) {
	$ced_reverb_global_data = $ced_reverb_global_data;

	$data                   = json_decode( $ced_reverb_global_data, true );

}

$attributes           = wc_get_attribute_taxonomies();
$attr_options         = array();
$added_meta_keys      = get_option( 'ced_reverb_selected_metakeys', array() );
$added_meta_keys      = array_merge( $added_meta_keys, array( '_woocommerce_title', '_woocommerce_short_description', '_woocommerce_description' ) );
$select_dropdown_html = '';

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
$product_specific_attribute_key = '';
?>
<div class="ced_reverb_heading">
	<?php echo esc_html_e( get_reverb_instuctions_html( 'Product Export Settings' ) ); ?>
	<div class="ced_reverb_child_element">
		<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
		<table class="wp-list-table fixed widefat ced_reverb_schedule_wrap">
			<?php
			$file = CED_REVERB_DIRPATH . 'admin/partials/class-ced-reverb-product-fields.php';
			reverb_include_file( $file );
			$product_field_instance = new Ced_Reverb_Product_Fields();
			$product_fields         = $product_field_instance->ced_reverb_get_custom_products_fields();


			if ( ! empty( $product_fields ) ) {

				echo '<tr class="form-field _umb_id_type_field ">';
				echo '<th><b>Attribute</b></th>';
				echo '<th><b>Default Value</b></th>';
				echo '<th><b>Pick Value From</b></th>';
				echo '</tr>';

				$product_specific_attribute_key = get_option( 'ced_reverb_product_specific_attribute_key', '' );


				foreach ( $product_fields as $field_data ) {
					if ( '_umb_reverb_category' == $field_data['id'] ) {
						continue;
					}
					$check    = false;
					$field_id = isset( $field_data['id'] ) ? $field_data['id'] : '';
					if ( empty( $product_specific_attribute_key ) ) {
						$product_specific_attribute_key = array( $field_id );
					} else {
						foreach ( $product_specific_attribute_key as $key => $product_key ) {
							if ( $product_key == $field_id ) {
								$check = true;
								break;
							}
						}
						if ( false == $check ) {
							$product_specific_attribute_key[] = $field_id;
						}
					}
					update_option( 'ced_reverb_product_specific_attribute_key', $product_specific_attribute_key );

					echo '<tr class="form-field _umb_id_type_field ">';
					$label = isset( $field_data['fields']['label'] ) ? $field_data['fields']['label'] : '';

					$field_id     = trim( $field_id, '_' );
					$category_id  = '';
					$product_id   = '';
					$market_place = 'ced_reverb_required_common';
					$description  = isset( $field_data['fields']['description'] ) ? $field_data['fields']['description'] : '';
					$required     = isset( $field_data['required'] ) ? (bool) $field_data['required'] : '';
					$index_to_use = 0;
					$default      = isset( $data[ '_ced_reverb_' . $field_data['fields']['id'] ]['default'] ) ? $data[ '_ced_reverb_' . $field_data['fields']['id'] ]['default'] : '';
					$field_value  = array(
						'case'  => 'profile',
						'value' => $default,
					);

					if ( '_text_input' == $field_data['type'] ) {
						$product_field_instance->render_input_text_html(
							$field_id,
							$label,
							$category_id,
							$product_id,
							$market_place,
							$index_to_use,
							$field_value,
							$required,
							$description
						);
					} elseif ( '_select' == $field_data['type'] ) {
						$value_for_dropdown = $field_data['fields']['options'];
						$product_field_instance->render_dropdown_html(
							$field_id,
							$label,
							$value_for_dropdown,
							$category_id,
							$product_id,
							$market_place,
							$index_to_use,
							$field_value,
							$required,
							$description
						);
					}

					echo '<td>';
					$previous_selected_value = 'null';
					if ( isset( $data[ '_ced_reverb_' . $field_data['fields']['id'] ]['metakey'] ) && 'null' != $data[ '_ced_reverb_' . $field_data['fields']['id'] ]['metakey'] ) {
						$previous_selected_value = $data[ '_ced_reverb_' . $field_data['fields']['id'] ]['metakey'];
					}
					$select_id = '_ced_reverb_' . $field_data['fields']['id'] . '_attribute_meta';
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
								<option value="<?php echo esc_attr( $attr_key ); ?>  "<?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
								<?php
							endforeach;
						}
						?>
					</select>
					<?php


					echo '</td>';
					echo '</tr>';
				}
			}
			?>
		</table>
	</div>
</div>

