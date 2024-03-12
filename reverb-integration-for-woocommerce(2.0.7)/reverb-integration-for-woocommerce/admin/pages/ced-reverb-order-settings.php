<div class="ced_reverb_heading">
<?php echo esc_html_e( get_reverb_instuctions_html( 'Order Configuration' ) ); ?>
<div class="ced_reverb_child_element">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
	<table class="wp-list-table fixed widefat ced_reverb_schedule_wrap">
		<tbody>
			<tr>
				<th><h4><?php echo esc_html_e( 'Reverb order status', 'reverb-woocommerce-integration' ); ?></h4></th>
				<th><h4><?php echo esc_html_e( 'Mapped with Woocommerce order status', 'reverb-woocommerce-integration' ); ?></h4></th>
			</tr>
			<?php
			$mapped_order_statuses = get_option( 'ced_reverb_mapped_order_statuses', array() );
			$reverb_order_statuses = array( 'unpaid', 'payment_pending', 'pending_review', 'blocked', 'paid', 'shipped', 'picked_up', 'received', 'refunded', 'cancelled' );
			$woo_order_statuses    = wc_get_order_statuses();
			foreach ( $reverb_order_statuses as $reverb_status ) {
				echo '<tr>';
				echo '<td>' . esc_attr( ucwords( $reverb_status ), 'reverb-woocommerce-integration' ) . '</td>';
				echo '<td>';
				echo "<select id='ced_reverb_map_order_status' data-reverb-order-status='" . esc_attr( $reverb_status ) . "'>";
				echo "<option value=''>---Order status not mapped---</option>";
				foreach ( $woo_order_statuses as $woo_status => $woo_label ) {
					echo "<option value='" . esc_attr( $woo_status, 'reverb-woocommerce-integration' ) . "' " . ( ( isset( $mapped_order_statuses[ $reverb_status ] ) && $woo_status == $mapped_order_statuses[ $reverb_status ] ) ? 'selected' : '' ) . '>' . esc_attr( $woo_label, 'reverb-woocommerce-integration' ) . '</option>';
				}
				echo '</select>';
				echo '</td>';
				echo '</tr>';
			}
			?>
			<tr>
				<?php
				$isScheduled = get_option( 'ced_reverb_set_reverbOrderNumber', '' );

				?>
				<th>
					<label><?php esc_attr_e( 'Show Reverb order number instead of woocommerce order id', 'woocommerce-reverb-integration' ); ?></label>
				</th>
				<td>

					<label class="switch">
						<input type="checkbox" name="ced_reverb_set_reverbOrderNumber" <?php echo ( 'on' == $isScheduled ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>

				</td>
			</tr>
			<tr>
				<th>
					<label><?php echo esc_html_e( 'Fetch Reverb order by status', 'reverb-woocommerce-integration' ); ?></label>
				</th>
				<td>
				<select id='ced_reverb_status' name="ced_reverb_status">
				<option value=''>--select--</option>
				<?php
				$selected                         = '';
				$ced_fetch_order_by_reverb_status = get_option( 'ced_fetch_order_by_reverb_status', '' );
				$reverb_order_statuses            = array( 'all', 'unpaid', 'awaiting _shipment' );
				foreach ( $reverb_order_statuses as $reverb_status ) {
					if ( $reverb_status == $ced_fetch_order_by_reverb_status ) {
						$selected = 'selected';
					} else {
						$selected = '';
					}
					?>
					<option value="<?php echo esc_attr( $reverb_status ); ?>"<?php echo esc_attr( $selected ); ?> ><?php echo esc_attr( ucfirst( $reverb_status ) ); ?></option>
				<?php	} ?>
			</select>
			<td>
			</tr>
		</tbody>
	</table>
</div>
</div>
