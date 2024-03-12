<?php
ced_etsy_wcfm_get_header();
$fields_file = CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-product-fields.php';
ced_etsy_wcfm_include_file($fields_file);
$ced_etsy_wcfm_global_settings   = get_option( 'ced_etsy_wcfm_global_settings', '' );
$shop_name = isset($_GET['shop_name']) ? sanitize_text_field( $_GET['shop_name'] ) : '';
$vendor_id=ced_etsy_wcfm_get_vendor_id();
do_action('action_to_get_orders_from_etsy',$vendor_id,$shop_name);
if( empty($ced_etsy_wcfm_global_settings) ){
	$ced_etsy_wcfm_global_settings = array();
} else {
	$ced_etsy_wcfm_global_settings = json_decode($ced_etsy_wcfm_global_settings,true);
}
if ( isset( $_POST['ced_etsy_wcfm_global_settings'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}
	$is_active                  = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplace_name           = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'etsy';
	

	$offer_settings_information = array();

	if ( isset( $_POST['ced_etsy_wcfm_required_common'] ) ) {
		$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		foreach ( ( $post_array['ced_etsy_wcfm_required_common'] ) as $key ) {

			$array_to_save = array();
			isset( $post_array[ $key ][0] ) ? $array_to_save['default'] = $post_array[ $key ][0] : $array_to_save['default'] = '';

			if ( '_umb_' . $marketplace_name . '_subcategory' == $key ) {
				isset( $post_array[ $key ] ) ? $array_to_save['default'] = $post_array[ $key ] : $array_to_save['default'] = '';
			}

			isset( $post_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = $post_array[ $key . '_attribute_meta' ] : $array_to_save['metakey'] = 'null';
			$offer_settings_information[ $key ] = $array_to_save;
		}
	}
	$ced_etsy_wcfm_global_settings[ ced_etsy_wcfm_get_vendor_id() ][ $shop_name ] =  $offer_settings_information ;
	update_option( 'ced_etsy_wcfm_global_settings', json_encode($ced_etsy_wcfm_global_settings) );
}
$data                 = isset($ced_etsy_wcfm_global_settings[ ced_etsy_wcfm_get_vendor_id() ][ $shop_name ]) ? $ced_etsy_wcfm_global_settings[ ced_etsy_wcfm_get_vendor_id() ][ $shop_name ] : array();

$product_field_instance = Ced_Etsy_Wcfm_Product_Fields::get_instance();
$product_fields = $product_field_instance->get_etsy_wcfm_custom_products_fields();
$attributes           = wc_get_attribute_taxonomies();
$attr_options         = array();
$added_meta_keys      = get_option( 'ced_etsy_wcfm_selected_metakeys' . ced_etsy_wcfm_get_vendor_id(), array() );
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

$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
if( in_array( 'etsy', $enabled_marketplaces )  ) {

?>
<form method="post" action="">
	<div class="ced_etsy_wcfm_heading">
		<?php 
		// echo esc_html_e( get_etsy_wcfm_instuctions_html( 'Product Configuration' ) ); 
		?>
		<div class="ced_etsy_wcfm_child_element">
			<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
			<table class="wp-list-table fixed widefat ced_etsy_wcfm_schedule_wrap">
				<?php

				echo '<tr class="form-field _umb_id_type_field ">';
				echo '<th>Attribute</th>';
				echo '<th>Default Value</th>';
				echo '<th>Pick Value From</th>';
				echo '</tr>';

				foreach ( $product_fields as $field_data ) {
					if ( '_umb_etsy_wcfm_category' == $field_data['id'] ) {
						continue;
					}
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
					$default      = isset( $data[ $field_data['fields']['id'] ]['default'] ) ? $data[ $field_data['fields']['id'] ]['default'] : '';
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
					}

					echo '<td>';
					$previous_selected_value = 'null';
					if ( isset( $data[ $field_data['fields']['id'] ]['metakey'] ) && 'null' != $data[ $field_data['fields']['id'] ]['metakey'] ) {
						$previous_selected_value = $data[ $field_data['fields']['id'] ]['metakey'];
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
				?>
			</table>
		</div>
	</div>
	<div class="">
		<button type="submit" class="button button-primary ced-wcfm-btn" name="ced_etsy_wcfm_global_settings"><?php esc_html_e( 'Save', 'etsy-woocommerce-integration' ); ?></button>
	</div>
</form>
<?php 
} 

?>