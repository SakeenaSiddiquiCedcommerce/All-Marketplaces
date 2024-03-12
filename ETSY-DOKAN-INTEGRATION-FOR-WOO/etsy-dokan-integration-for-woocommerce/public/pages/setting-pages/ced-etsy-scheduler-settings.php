<?php
	$activeShop                 = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';
	$renderDataOnGlobalSettings = get_option( 'ced_etsy_dokan_global_settings', false );
	$renderDataOnGlobalSettings = isset( $renderDataOnGlobalSettings[get_current_user_id()][$activeShop] ) ? $renderDataOnGlobalSettings[get_current_user_id()][$activeShop] : array();
	$auto_fetch_orders                 = isset( $renderDataOnGlobalSettings['ced_etsy_auto_fetch_orders'] ) ? $renderDataOnGlobalSettings['ced_etsy_auto_fetch_orders'] : '';
	$auto_confirm_orders               = isset( $renderDataOnGlobalSettings['ced_etsy_auto_import_product'] ) ? $renderDataOnGlobalSettings['ced_etsy_auto_import_product'] : '';
	$auto_update_inventory_woo_to_etsy = isset( $renderDataOnGlobalSettings['ced_etsy_auto_update_inventory'] ) ? $renderDataOnGlobalSettings['ced_etsy_auto_update_inventory'] : '';
	$auto_update_stock_etsy_to_woo     = isset( $renderDataOnGlobalSettings['ced_etsy_update_inventory_etsy_to_woo'] ) ? $renderDataOnGlobalSettings['ced_etsy_update_inventory_etsy_to_woo'] : '';
	$ced_etsy_auto_upload_product      = isset( $renderDataOnGlobalSettings['ced_etsy_auto_upload_product'] ) ? $renderDataOnGlobalSettings['ced_etsy_auto_upload_product'] : '';
?>
<div class="ced_etsy_heading">
<?php echo esc_html_e( ced_etsy_dokan_get_etsy_instuctions_html( 'Schedulers' ) ); ?>
<div class="ced_etsy_child_element">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
	<table class="wp-list-table fixed widefat ced_etsy_schedule_wrap">
		<tbody>
			<tr>
				<th>
					<label><?php echo esc_html_e( 'Fetch etsy orders', 'etsy-woocommerce-integration' ); ?></label>
					<?php ced_etsy_dokan_tool_tip( 'Auto fetch etsy orders and create in woocommerce.' ); ?>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_etsy_dokan_global_settings[ced_etsy_auto_fetch_orders]" <?php echo ( 'on' == $auto_fetch_orders ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
			<!-- <tr>
				<th>
					<label>
						<?php
						echo esc_html_e( 'Upload products to etsy', 'etsy-woocommerce-integration' );
						$profile_page = admin_url( 'admin.php?page=ced_etsy&section=profiles-view&de_shop_name=' . $activeShop );
						?>
						 </label>
					<?php ced_etsy_dokan_tool_tip( 'Auto upload products from woocommerce to etsy. Please choose the categories/profile that you want to be uploaded automatically in <a href="' . $profile_page . '">Profile</a> section.' ); ?>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_etsy_dokan_global_settings[ced_etsy_auto_upload_product]" <?php echo ( 'on' == $ced_etsy_auto_upload_product ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr> -->
			<tr>
				<th>
					<label><?php echo esc_html_e( 'Update inventory to etsy', 'etsy-woocommerce-integration' ); ?></label>
					<?php ced_etsy_dokan_tool_tip( 'Auto update price and stock from woocommerce to etsy.' ); ?>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_etsy_dokan_global_settings[ced_etsy_auto_update_inventory]" <?php echo ( 'on' == $auto_update_inventory_woo_to_etsy ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
			<!-- <tr>
				<th>
					<label><?php echo esc_html_e( 'Import products from etsy', 'etsy-woocommerce-integration' ); ?></label>
					<?php ced_etsy_dokan_tool_tip( 'Auto import the active listings from etsy to woocommerce.' ); ?>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_etsy_dokan_global_settings[ced_etsy_auto_import_product]" <?php echo ( 'on' == $auto_confirm_orders ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr> -->
		</tbody>
	</table>
</div>
</div>
