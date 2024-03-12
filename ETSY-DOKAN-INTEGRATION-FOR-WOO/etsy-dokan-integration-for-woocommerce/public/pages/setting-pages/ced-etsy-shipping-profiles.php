<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( ced_etsy_filter( $_GET['de_shop_name'] ) ) ) : '';
$savedShippingDetails     = '';
$savedTempShippingDetails = get_option( 'ced_etsy_dokan_details', array() );
$savedShippingDetails     = isset( $savedTempShippingDetails[get_current_user_id()][$shop_name]['shippingTemplateId'] ) ? $savedTempShippingDetails[get_current_user_id()][ $shop_name ]['shippingTemplateId'] : '';
$vendor_id                = get_current_user_id();
$shippingTemplates        = ced_etsy_dokan_get_shipping_profiles( $shop_name, $vendor_id);
$isShopInActive           = ced_etsy_dokan_inactive_shops( $shop_name );
if ( $isShopInActive ) {
	echo '<div class="notice notice-error"><p>Shop is not Active.Please Activate your Shop in order to save Shipping Template</p></div>';

}
?>
<div class="ced_etsy_heading">
	<?php echo esc_html_e( ced_etsy_dokan_get_etsy_instuctions_html( 'Shipping Profiles' ) ); ?>
	<div class="ced_etsy_child_element">
		<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
		<?php wp_nonce_field( 'saveShippingTemplates', 'shipping_settings_submit' ); ?>
		<div id="update-button">
				<table class="wp-list-table widefat ced_etsy_config_table"  id="">
					<?php

					if ( is_array( $shippingTemplates ) && ! empty( $shippingTemplates ) ) {
						$shipping_templates                = get_option( 'ced_etsy_shipping_templates', array() );
						$shipping_templates[ $shop_name ] = $shippingTemplates;
						update_option( 'ced_etsy_shipping_templates', $shipping_templates );
						?>
						<tbody>
							<tr>
								<th><label class="ced_bold"><?php esc_html_e( 'Choose Shipping Profile', 'ced-etsy' ); ?></label>
									<?php ced_etsy_dokan_tool_tip( 'Shipping profile to be used for uploading products on Etsy.' ); ?>
								</th>
								<td class="manage-column">
									<select class="select_boxes" name="ced_etsy_shipping_details[ced_etsy_selected_shipping_template]" value="">
										<option value=""><?php esc_html_e( '--SELECT--', 'ced-etsy' ); ?></option>
										<?php
										foreach ( $shippingTemplates as $key1 => $value1 ) {
											$selected = '';
											if ( $key1 == $savedShippingDetails ) {
												$selected = 'selected';
											}
											?>
											<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $key1 ); ?>"><?php echo esc_html( $value1 ); ?></option>
											<?php
										}
										?>
									</select>
								</td>

								

								<?php
					} else {
						?>
								<td>
									<th><?php esc_html_e( 'Create a Shipping Profile', 'ced-etsy' ); ?></th>
								</td>
								<?php
					}
					?>
							<td>
								<?php
								$url = dokan_get_navigation_url( 'ced_etsy/add-shipping-profile-view?de_shop_name=' . $shop_name );
								?>
								<a href="<?php echo esc_attr( $url ); ?>" class="button ced-dokan-btn" >Add New</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
