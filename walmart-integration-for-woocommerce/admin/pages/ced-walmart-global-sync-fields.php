<?php
/**
 * Global Order fields
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
 get_walmart_header();
if ( isset( $_POST['ced_walmart_global_sync'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}
	$post_array          = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$auto_fetch_orders   = isset( $post_array['ced_walmart_auto_fetch_orders'] ) ? $post_array['ced_walmart_auto_fetch_orders'] : '';
	$auto_update_product = isset( $post_array['ced_walmart_auto_update_product'] ) ? $post_array['ced_walmart_auto_update_product'] : '';
	if ( 'on' == $auto_fetch_orders ) {
		wp_clear_scheduled_hook( 'ced_walmart_auto_fetch_orders' );
		update_option( 'ced_walmart_auto_fetch_orders', $auto_fetch_orders );
		wp_schedule_event( time(), 'ced_walmart_15min', 'ced_walmart_auto_fetch_orders' );
	} else {
		wp_clear_scheduled_hook( 'ced_walmart_auto_fetch_orders' );
		delete_option( 'ced_walmart_auto_fetch_orders' );
	}
	if ( 'on' == $auto_update_product ) {
		wp_clear_scheduled_hook( 'ced_walmart_auto_update_product' );
		update_option( 'ced_walmart_auto_update_product', $auto_update_product );
		wp_schedule_event( time(), 'ced_walmart_30min', 'ced_walmart_auto_update_product' );
	} else {
		wp_clear_scheduled_hook( 'ced_walmart_auto_update_product' );
		delete_option( 'ced_walmart_auto_update_product' );
	}
	print_success_notice();
}
$auto_fetch_orders   = get_option( 'ced_walmart_auto_fetch_orders', '' );
$auto_update_product = get_option( 'ced_walmart_auto_update_product', '' );
?>
<div class="ced_walmart_heading">
	<?php echo esc_html_e( get_instuctions_html() ); ?>
	<div class="ced_walmart_child_element default_modal">
		<ul type="disc">
			<li><?php echo esc_html_e( 'Configure the CRONS for automation of different process.' ); ?></li>
		</ul>
	</div>
</div>
<div>
	<form method="post" action="">
		<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
		<table class="wp-list-table fixed widefat stripped">
			<thead></thead>
			<tbody>
				<tr>
					<th>
						<label><?php esc_html_e( 'Fetch Walmart orders', 'walmart-woocommerce-integration' ); ?></label>
						<?php ced_walmart_tool_tip( 'Auto fetch walmart orders and create in woocommerce.' ); ?>						
					</th>
					<td>
						<label class="switch">
						<input type="checkbox" name="ced_walmart_auto_fetch_orders" <?php echo ( 'on' == $auto_fetch_orders ) ? 'checked=checked' : ''; ?>>
						<span class="slider round"></span>
					</label>
				</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Update price and stock.' ); ?></label>
						<?php ced_walmart_tool_tip( 'Auto update price and stock from woocommerce to walmart.' ); ?>	
					</th>
						<td>
							<label class="switch">
							<input type="checkbox" name="ced_walmart_auto_update_product" <?php echo ( 'on' == $auto_update_product ) ? 'checked=checked' : ''; ?>>
							<span class="slider round"></span>
						</label>
					</td>
					</tr>
					</tbody>
				</table>
				<div class="walmart-button-wrap">
					<button type="submit" class="button button-primary" name="ced_walmart_global_sync"><?php esc_html_e( 'Save', 'walmart-woocommerce-integration' ); ?></button>
				</div>
			</form>
		</div>
