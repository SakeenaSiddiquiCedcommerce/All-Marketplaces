<div class="ced_etsy_heading">
<?php echo esc_html_e( ced_etsy_dokan_get_etsy_instuctions_html( 'Order Import Settings' ) ); ?>
<div class="ced_etsy_child_element">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
	<table class="wp-list-table fixed widefat ced_etsy_schedule_wrap">
		<tbody>
			<?php
			$activeShop                 = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';
			$renderDataOnGlobalSettings = get_option( 'ced_etsy_dokan_global_settings', array() );
			$renderDataOnGlobalSettings = isset( $renderDataOnGlobalSettings[get_current_user_id()][$activeShop] ) ? $renderDataOnGlobalSettings[get_current_user_id()][$activeShop] : '';
			$ListType                   = isset( $renderDataOnGlobalSettings['ced_fetch_etsy_order_by_status'] ) ? $renderDataOnGlobalSettings['ced_fetch_etsy_order_by_status'] : '';
			$use_etsy_order_no          = isset( $renderDataOnGlobalSettings['use_etsy_order_no'] ) ? $renderDataOnGlobalSettings['use_etsy_order_no'] : '';
			$default_order_status       = isset( $renderDataOnGlobalSettings['default_order_status'] ) ? $renderDataOnGlobalSettings['default_order_status'] : '';
			$update_tracking            = isset( $renderDataOnGlobalSettings['update_tracking'] ) ? $renderDataOnGlobalSettings['update_tracking'] : '';
			?>
			<tr>
				<td>
					<label>
					<?php
					esc_html_e( 'Default woocommerce status', 'woocommerce-etsy-integration' );
					ced_etsy_dokan_tool_tip( 'Choose the order status in which you want to create etsy orders . Default is processing.' );
					?>
					</label>
				</td>
				<?php
				$woo_order_statuses = wc_get_order_statuses();
				echo '<td>';
				echo "<select name='ced_etsy_dokan_global_settings[default_order_status]'>";
				echo "<option value=''>---Not mapped---</option>";
				foreach ( $woo_order_statuses as $woo_status => $woo_label ) {
					echo "<option value='" . esc_attr( $woo_status ) . "' " . ( ( isset( $default_order_status ) && $woo_status == $default_order_status ) ? 'selected' : '' ) . '>' . esc_attr( $woo_label ) . '</option>';
				}
				echo '</select>';
				?>
			</tr>
			<tr>
				<td>
					
					<label>
					<?php
					esc_html_e( 'Fetch etsy order by status', 'woocommerce-etsy-integration' );
					ced_etsy_dokan_tool_tip( 'Choose the order status to be fetched from etsy . Default is all status and limit 15 latest orders.' );
					?>
						
					</label>
				</td>
				<td>
					 <select name="ced_etsy_dokan_global_settings[ced_fetch_etsy_order_by_status]">
						<option <?php echo ( 'all' == $ListType ) ? 'selected' : ''; ?> value="all"><?php esc_html_e( 'All', 'woocommerce-etsy-integration' ); ?></option>
						<option <?php echo ( 'open' == $ListType ) ? 'selected' : ''; ?> value="open"><?php esc_html_e( 'Open', 'woocommerce-etsy-integration' ); ?></option>
						<option <?php echo ( 'unshipped' == $ListType ) ? 'selected' : ''; ?> value="unshipped"><?php esc_html_e( 'Unshipped', 'woocommerce-etsy-integration' ); ?></option>
						<option <?php echo ( 'unpaid' == $ListType ) ? 'selected' : ''; ?> value="unpaid"><?php esc_html_e( 'Unpaid', 'woocommerce-etsy-integration' ); ?></option>
						<option <?php echo ( 'completed' == $ListType ) ? 'selected' : ''; ?> value="completed"><?php esc_html_e( 'Completed', 'woocommerce-etsy-integration' ); ?></option>
						<option <?php echo ( 'processing' == $ListType ) ? 'selected' : ''; ?> value="processing"><?php esc_html_e( 'Processing', 'woocommerce-etsy-integration' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label>
						<?php
						echo esc_html_e( 'Use etsy order number', 'etsy-woocommerce-integration' );
						ced_etsy_dokan_tool_tip( 'Use etsy order number instead of woocommerce id when creating etsy orders in woocommerce.' );
						?>
						 </label>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_etsy_dokan_global_settings[use_etsy_order_no]" <?php echo ( 'on' == $use_etsy_order_no ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
			<tr>
				<th>
					<label>
						<?php
						echo esc_html_e( 'Auto update tracking', 'etsy-woocommerce-integration' );
						ced_etsy_dokan_tool_tip( 'Auto update tracking information on etsy if using <a href="https://woocommerce.com/products/shipment-tracking" target="_blank">Shipment Tracking</a> plugin.' );
						?>
						 </label>

				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_etsy_dokan_global_settings[update_tracking]" <?php echo ( 'on' == $update_tracking ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
</div>
</div>
