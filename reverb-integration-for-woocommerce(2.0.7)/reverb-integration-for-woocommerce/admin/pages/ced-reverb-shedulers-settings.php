<?php
$auto_fetch_orders              = get_option( 'ced_reverb_auto_fetch_orders', '' );
$auto_update_inventory          = get_option( 'ced_reverb_auto_update_inventory', '' );
$ced_reverb_auto_upload_product = get_option( 'ced_reverb_auto_upload_product', '' );
?>
<div class="ced_reverb_heading">
<?php echo esc_html_e( get_reverb_instuctions_html( 'Shedulers' ) ); ?>
<div class="ced_reverb_child_element">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
	<table class="wp-list-table fixed widefat ced_reverb_schedule_wrap">
		<tbody>
			<tr><td><h4>Order Schedules</h4></td></tr>
			<tr>
				<th>
					<label><?php echo esc_html_e( 'Auto fetch orders from Reverb', 'reverb-woocommerce-integration' ); ?></label>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_reverb_auto_fetch_orders" <?php echo ( 'on' == $auto_fetch_orders ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
			<tr>
				<?php
				$ced_reverb_auto_update_tracking = get_option( 'ced_reverb_auto_update_tracking', '' );
				?>
				<th>
					<label><?php esc_attr_e( 'Auto Update Tracking', 'woocommerce-reverb-integration' ); ?></label>
				</th>
				<td>

					<label class="switch">
						<input type="checkbox" name="ced_reverb_auto_update_tracking" <?php echo ( 'on' == $ced_reverb_auto_update_tracking ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
			<tr><td><h4>Product Schedules</h4></tr></td>
			<tr>
				<th>
					<label><?php echo esc_html_e( 'Auto update Inventory from Woocommerce', 'reverb-woocommerce-integration' ); ?></label>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_reverb_auto_update_inventory" <?php echo ( 'on' == $auto_update_inventory ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
			<tr>
				<th>
					<label>
						<?php
						echo esc_html_e( 'Auto Upload products to Reverb', 'reverb-woocommerce-integration' );
						$profile_page = admin_url( 'admin.php?page=ced_reverb&section=profile_view' );
						?>
						 </label>
					<?php ced_reverb_tool_tip( 'Auto upload products from woocommerce to reverb. Please choose the categories/profile that you want to be uploaded automatically in <a href="' . $profile_page . '">Profile</a> section.' ); ?>
				</th>
				<td>
					<label class="switch">
						<input type="checkbox" name="ced_reverb_auto_upload_product" <?php echo ( 'on' == $ced_reverb_auto_upload_product ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
</div>
</div>
