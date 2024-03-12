<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/header.php';

if ( isset( $_POST['global_settings'] ) ) {

	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}

	$sanitized_array         = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$settings                = array();
	$settings                = get_option( 'ced_tokopedia_global_settings', array() );
	$settings[ $activeShop ] = isset( $sanitized_array['ced_tokopedia_global_settings'] ) ? $sanitized_array['ced_tokopedia_global_settings'] : array();

	update_option( 'ced_tokopedia_global_settings', $settings );
	if ( isset( $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_scheduler_info'] ) ) {

		update_option( 'tokopedia_auto_syncing' . $activeShop, 'on' );
		wp_clear_scheduled_hook( 'ced_tokopedia_inventory_scheduler_job_' . $activeShop );
		wp_clear_scheduled_hook( 'ced_tokopedia_order_scheduler_job_' . $activeShop );
		wp_clear_scheduled_hook( 'ced_tokopedia_auto_upload_schedule_job_' . $activeShop );

		$inventory_schedule = isset( $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_inventory_schedule_info'] ) ? $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_inventory_schedule_info'] : '';


		$order_schedule                = isset( $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_order_schedule_info'] ) ? $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_order_schedule_info'] : '';
		$auto_upload_schedule          = isset( $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_auto_update_schedule_info'] ) ? $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_auto_update_schedule_info'] : '';
		$auto_upload_category_schedule = isset( $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_auto_update_category_schedule_info'] ) ? $sanitized_array['ced_tokopedia_global_settings']['ced_tokopedia_auto_update_category_schedule_info'] : '';


		if ( ! empty( $inventory_schedule ) ) {

			wp_schedule_event( time(), $inventory_schedule, 'ced_tokopedia_inventory_scheduler_job_' . $activeShop );
			update_option( 'ced_tokopedia_inventory_scheduler_job_' . $activeShop, $activeShop );

		}

		if ( ! empty( $order_schedule ) ) {

			wp_schedule_event( time(), $order_schedule, 'ced_tokopedia_order_scheduler_job_' . $activeShop );
			update_option( 'ced_tokopedia_order_scheduler_job_' . $activeShop, $activeShop );
		}
		if ( ! empty( $auto_upload_schedule ) ) {

			wp_schedule_event( time(), $auto_upload_schedule, 'ced_tokopedia_auto_upload_schedule_job_' . $activeShop );
			update_option( 'ced_tokopedia_auto_upload_schedule_job_' . $activeShop, $activeShop );

		}
		if ( ! empty( $auto_upload_category_schedule ) ) {

			wp_schedule_event( time(), $auto_upload_category_schedule, 'ced_tokopedia_auto_upload_category_schedule_job_' . $activeShop );
			update_option( 'ced_tokopedia_auto_update_category_schedule_info' . $activeShop, $activeShop );
		}
	} else {
		wp_clear_scheduled_hook( 'ced_tokopedia_inventory_scheduler_job_' . $activeShop );
		wp_clear_scheduled_hook( 'ced_tokopedia_order_scheduler_job_' . $activeShop );
		update_option( 'tokopedia_auto_syncing' . $activeShop, 'off' );
	}
}

$renderDataOnGlobalSettings = get_option( 'ced_tokopedia_global_settings', false );
$shop_name                  = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';


global $wpdb;
$shops        = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_accounts WHERE `shop_id`=%d", $shop_name ), 'ARRAY_A' );
$etalase_data = isset( $shops[0]['shop_data'] ) ? json_decode( $shops[0]['shop_data'] , true ) : '';
$all_etalase  = isset( $etalase_data['etalase_data'] ) ? $etalase_data['etalase_data'] : '';

?>
<form method="post" action="">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
	<div class="navigation-wrapper">
		<div>				
			<table class="wp-list-table widefat fixed  ced_tokopedia_global_settings_fields_table">
				<thead>
					<tr>
						<th class="ced_tokopedia_settings_heading">
							<label class="basic_heading">
								<?php esc_html_e( 'GENERAL DETAILS ', 'woocommerce-tokopedia-integration' ); ?>
							</label>
						</th>
					</tr>
				</thead>				
				<tbody>
					<tr>
						<?php
						$cc_rate = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_currency_conversion_rate'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_currency_conversion_rate'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Currency conversion rate', 'woocommerce-tokopedia-integration' ); ?></label>
						</th>
						<td>
							<input type="text" name="ced_tokopedia_global_settings[ced_tokopedia_currency_conversion_rate]" class="ced_tokopedia_select" placeholder="Currency Conversion Value" value="<?php echo $cc_rate; ?>">
						</td>
					</tr>
					<tr>
						<?php
						$pro_condition = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_product_condition'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_product_condition'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Product Condition*', 'woocommerce-tokopedia-integration' ); ?></label>
						</th>
						<td>
							<select name="ced_tokopedia_global_settings[ced_tokopedia_product_condition]" class="ced_tokopedia_select ced_tokopedia_global_select_box select_boxes"  data-fieldId="">
								<option value=""><?php esc_html_e( '--Select--', 'woocommerce-tokopedia-integration' ); ?></option>
								
								<option <?php echo ( 'NEW' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'NEW'; ?>"><?php esc_html_e( 'New Product', 'woocommerce-tokopedia-integration' ); ?></option>
								<option <?php echo ( 'USED' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'USED'; ?>"><?php esc_html_e( 'Used Product ', 'woocommerce-tokopedia-integration' ); ?></option>
							</option>							
						</select>
					</td>
				</tr>
				<tr>
					<?php
					$pro_condition = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_product_list_type'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_product_list_type'] : '';
					?>
					<th>
						<label><?php esc_html_e( 'Product List Type', 'woocommerce-tokopedia-integration' ); ?></label>
					</th>
					<td>
						<select name="ced_tokopedia_global_settings[ced_tokopedia_product_list_type]" class="ced_tokopedia_select ced_tokopedia_global_select_box select_boxes"  data-fieldId="">
							<option value=""><?php esc_html_e( '--Select--', 'woocommerce-tokopedia-integration' ); ?></option>
							<option <?php echo ( 'true' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'true'; ?>"><?php esc_html_e( 'Active', 'woocommerce-tokopedia-integration' ); ?></option>
							<option <?php echo ( 'false' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'false'; ?>"><?php esc_html_e( 'Draft', 'woocommerce-tokopedia-integration' ); ?></option>					
						</select>
					</td>
				</tr>
				<tr>
					<?php
					$pro_condition = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_product_status'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_product_status'] : '';

					?>
					<th>
						<label><?php esc_html_e( 'Product Status*', 'woocommerce-tokopedia-integration' ); ?></label>
					</th>
					<td>
						<select name="ced_tokopedia_global_settings[ced_tokopedia_product_status]" class="ced_tokopedia_select ced_tokopedia_global_select_box select_boxes"  data-fieldId="">
							<option value=""><?php esc_html_e( '--Select--', 'woocommerce-tokopedia-integration' ); ?></option>
							
							<option <?php echo ( 'UNLIMITED' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'UNLIMITED'; ?>"><?php esc_html_e( 'UNLIMITED', 'woocommerce-tokopedia-integration' ); ?></option>
							<option <?php echo ( 'LIMITED' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'LIMITED'; ?>"><?php esc_html_e( 'LIMITED', 'woocommerce-tokopedia-integration' ); ?></option>
							<option <?php echo ( 'EMPTY' == $pro_condition ) ? 'selected' : ''; ?> value="<?php echo 'EMPTY'; ?>"><?php esc_html_e( 'EMPTY', 'woocommerce-tokopedia-integration' ); ?>
						</option>
						
					</select>
				</td>
				</tr>
			<tr>
				<?php
				$productSupply = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_weight_unit'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_weight_unit'] : '';
				?>
				<th>
					<label><?php esc_html_e( 'Weight Unit*', 'woocommerce-tokopedia-integration' ); ?></label>
				</th>
				<td>
					<select name="ced_tokopedia_global_settings[ced_tokopedia_weight_unit]" class="ced_tokopedia_select ced_tokopedia_global_select_box select_boxes"  data-fieldId="">
						<option value=""><?php esc_html_e( '--Select--', 'woocommerce-tokopedia-integration' ); ?></option>
						<option <?php echo ( 'GR' == $productSupply ) ? 'selected' : ''; ?> value="GR"><?php esc_html_e( 'Gram', 'woocommerce-tokopedia-integration' ); ?></option>
						<option <?php echo ( 'KG' == $productSupply ) ? 'selected' : ''; ?> value="KG"><?php esc_html_e( 'Kilogram', 'woocommerce-tokopedia-integration' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label><?php esc_html_e( 'SCHEDULER', 'woocommerce-tokopedia-integration' ); ?></label>
				</th>
				<td>
					<?php
					$checked = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_scheduler_info'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_scheduler_info'] : '';
					if ( 'on' == $checked ) {
						$checked = 'checked';
						$style   = 'display: contents';
					} else {
						$checked = '';
						$style   = 'display: none';

					}
					?>
					<input class="ced_tokopedia_disabled_text_field" type="checkbox"  id="ced_tokopedia_scheduler_info" name="ced_tokopedia_global_settings[ced_tokopedia_scheduler_info]" <?php echo esc_attr( $checked ); ?>>(check this to schedule events)
				</td>
			</tr>
		</tbody>
		<tbody class="ced_tokopedia_scheduler_info" style="<?php echo esc_attr( $style ); ?>" >
			<tr>
				<?php
				$order_schedule = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_order_schedule_info'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_order_schedule_info'] : '';
				?>
				<th>
					<label><?php esc_html_e( 'Order Sync Scheduler', 'woocommerce-tokopedia-integration' ); ?></label>
				</th>
				<td>
					<select name="ced_tokopedia_global_settings[ced_tokopedia_order_schedule_info]" class="ced_tokopedia_select ced_tokopedia_global_select_box select_boxes_scheduler" data-fieldId="ced_tokopedia_order_schedule_info">
						<option <?php echo ( '0' == $order_schedule ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'daily' == $order_schedule ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'twicedaily' == $order_schedule ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_6min' == $order_schedule ) ? 'selected' : ''; ?> value="ced_tokopedia_6min"><?php esc_html_e( 'Every 6 Minutes', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_10min' == $order_schedule ) ? 'selected' : ''; ?>  value="ced_tokopedia_10min"><?php esc_html_e( 'Every 10 Minutes', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_15min' == $order_schedule ) ? 'selected' : ''; ?>  value="ced_tokopedia_15min"><?php esc_html_e( 'Every 15 Minutes', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_30min' == $order_schedule ) ? 'selected' : ''; ?>  value="ced_tokopedia_30min"><?php esc_html_e( 'Every 30 Minutes', 'ced-umb-tokopedia' ); ?></option>
					</select>
				</td>
			</tr>
			
			<tr>
				<?php
				$inventory_schedule = isset( $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_inventory_schedule_info'] ) ? $renderDataOnGlobalSettings[ $activeShop ]['ced_tokopedia_inventory_schedule_info'] : '';
				?>
				<th>
					<label><?php esc_html_e( 'Inventory Sync Scheduler', 'woocommerce-tokopedia-integration' ); ?></label>
				</th>
				<td>
					<select name="ced_tokopedia_global_settings[ced_tokopedia_inventory_schedule_info]" class="ced_tokopedia_select ced_tokopedia_global_select_box select_boxes_scheduler" data-fieldId="ced_tokopedia_inventory_schedule_info">
						<option <?php echo ( '0' == $inventory_schedule ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'daily' == $inventory_schedule ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'twicedaily' == $inventory_schedule ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_6min' == $inventory_schedule ) ? 'selected' : ''; ?> value="ced_tokopedia_6min"><?php esc_html_e( 'Every 6 Minutes', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_10min' == $inventory_schedule ) ? 'selected' : ''; ?>  value="ced_tokopedia_10min"><?php esc_html_e( 'Every 10 Minutes', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_15min' == $inventory_schedule ) ? 'selected' : ''; ?>  value="ced_tokopedia_15min"><?php esc_html_e( 'Every 15 Minutes', 'ced-umb-tokopedia' ); ?></option>
						<option <?php echo ( 'ced_tokopedia_30min' == $inventory_schedule ) ? 'selected' : ''; ?>  value="ced_tokopedia_30min"><?php esc_html_e( 'Every 30 Minutes', 'ced-umb-tokopedia' ); ?></option>

					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>
</div>
<div align="left">
	<button id="save_global_settings"  name="global_settings" class="ced_tokopedia_custom_button" ><?php esc_html_e( 'Save', 'woocommerce-tokopedia-integration' ); ?></button>
</div>
</form>

