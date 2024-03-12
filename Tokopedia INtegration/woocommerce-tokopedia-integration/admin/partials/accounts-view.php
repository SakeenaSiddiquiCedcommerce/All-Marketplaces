<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_TOKOPEDIA_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}

	global $wpdb;
	$shops = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_accounts WHERE `shop_id`=%d", $activeShop ), 'ARRAY_A' );

if ( ! wp_get_schedule( 'ced_tokopedia_update_token_hourly_' . $activeShop ) ) {
	wp_schedule_event( time(), 'ced_tokopedia_1hour', 'ced_tokopedia_update_token_hourly_' . $activeShop );
}
	do_action( 'ced_tokopedia_update_token_hourly_' . $activeShop, $activeShop );
	// do_action( 'ced_tokopedia_register_ip_white_list_' . $activeShop, $activeShop );
?>
<div class="ced_tokopedia_account_configuration_wrapper">
	<div class="ced_tokopedia_account_configuration_fields">		
		<table class="wp-list-table widefat fixed striped ced_tokopedia_account_configuration_fields_table">
			<tbody>
				<?php
				foreach ( $shops as $key => $value ) {
					if ( isset( $value['shop_id'] ) ) {
						?>
						<tr>
							<th>
								<label><?php esc_html_e( 'Store Id', 'woocommerce-tokopedia-integration' ); ?></label>
							</th>
							<td>
								<label><?php echo esc_attr( $value['shop_id'] ); ?></label>
							</td>
						</tr>
						<?php
					}
				}
				?>

				<tr>
					<th>
						<label><?php esc_html_e( 'Store Name', 'woocommerce-tokopedia-integration' ); ?></label>
					</th>
					<td>
						<label><?php echo esc_attr( $activeShop ); ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Account Status', 'woocommerce-tokopedia-integration' ); ?></label>
					</th>
					<td>
						<?php
						foreach ( $shops as $key => $shop ) {
							if ( isset( $shop['account_status'] ) && 'InActive' == $shop['account_status'] ) {
								$inactive = 'selected';
								$active   = '';
							} else {
								$active   = 'selected';
								$inactive = '';
							}
						}
						?>
						<select class="ced_tokopedia_select select_boxes" id="ced_tokopedia_account_status">
							<option><?php esc_html_e( '--Select Status--', 'woocommerce-tokopedia-integration' ); ?></option>
							<option value="Active" <?php echo esc_attr( $active ); ?>><?php esc_html_e( 'Active', 'woocommerce-tokopedia-integration' ); ?></option>
							<option value="InActive" <?php echo esc_attr( $inactive ); ?>><?php esc_html_e( 'Inactive', 'woocommerce-tokopedia-integration' ); ?></option>
						</select>
						<a class="ced_tokopedia_update_status_message" data-id="<?php echo esc_attr( $activeShop ); ?>" id="ced_tokopedia_update_account_status" href="javascript:void(0);"><?php esc_html_e( 'Update Account Status', 'woocommerce-tokopedia-integration' ); ?></a>
					</td>
				</tr>			
			</tbody>
		</table>
	</div>

</div>
