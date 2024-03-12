<?php
/**
 * Shipment Order Template
 *
 * @package  Woocommerce_Etsy_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $post;

$order_id              = isset( $post->ID ) ? intval( $post->ID ) : '';
$umb_etsy_order_status = get_post_meta( $order_id, '_etsy_dokan_umb_order_status', true );

$merchant_order_id = get_post_meta( $order_id, 'merchant_order_id', true );
$purchase_order_id = get_post_meta( $order_id, 'purchaseOrderId', true );
$fulfillment_node  = get_post_meta( $order_id, 'fulfillment_node', true );
$order_detail      = get_post_meta( $order_id, 'order_detail', true );
$order_item        = get_post_meta( $order_id, 'order_items', true );

if ( isset( $order_item[0] ) ) {
	$order_items = $order_item;
} else {
	$order_items[0] = $order_item['orderLine'];
}

$number_items          = 0;
$umb_etsy_order_status = get_post_meta( $order_id, '_etsy_dokan_umb_order_status', true );
if ( empty( $umb_etsy_order_status ) || 'Fetched' == $umb_etsy_order_status ) {
	$umb_etsy_order_status = 'Created';
}
$shippment_carriers = json_decode( @file_get_contents( CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/json/ced-etsy-shipment-carrier-name.json' ), true );
$shippment_carriers = isset( $shippment_carriers['options'] ) ?  $shippment_carriers['options'] : array();
?>

<div id="umb_etsy_order_settings" class="panel woocommerce_options_panel">
	<div class="ced_etsy_dokan_loader" class="loading-style-bg" style="display: none;">
		<img src="<?php echo esc_url( CED_ETSY_DOKAN_URL . 'public/images/loading.gif' ); ?>">
	</div>

	<div class="options_group">
		<p class="form-field">
			<h3><center>
				<?php
				esc_html_e( 'ETSY ORDER STATUS OF VENDORS : ', 'woocommerce-etsy-integration' );
				echo esc_attr( strtoupper( $e_order_status ) );
				?>
			</center></h3>
		</p>
	</div>
	<div class="ced_etsy_error"></div>
	<div class="options_group umb_etsy_options"> 
		<?php
		if ( 'Created' == $umb_etsy_order_status ) {
			?>
			<div id="ced_etsy_shipment_wrap">
				<div>
					<table class="widefat fixed stripped">
						<tbody>
							<tr>
								<td>
									<span>Shipping Carrier</span>
								</td>
								<td>
									<select class="ced_etsy_carrier_names_opts" name="ced_etsy_carrier_name[]" id="ced_etsy_carrier_name_opt">
										<?php
											foreach ( $shippment_carriers as $s_key => $s_val ) {
												if ( isset( $s_val['label'] ) && ! empty( $s_val['label'] ) ) {
													?>
													<option value="<?php echo esc_attr( $s_val['value'] ); ?>"><?php echo esc_attr( $s_val['label'] ); ?></option>
													<?php
												}
											}
										?>
										<!-- <option value="">Other</option> -->
									</select>
									<!-- <input type="text" name="" id="ced_etsy_carrier_name" class="ced_etsy_required_data hide"> -->
								</td>
							</tr>
							<tr>
								<td>
									<span>Tracking Number</span>
								</td>
								<td>
									<input type="text" name="" id="ced_etsy_tracking_code" class="ced_etsy_required_data">
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" class="button button-primary" name="" id="ced_etsy_dokan_submit_shipment" value="Submit" data-order-id="<?php echo esc_attr( $order_id ); ?>">
								</td>
								<td>	
									<span class="ced_spinner spinner"></span>									
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}
		?>
	</div>    
</div>    
