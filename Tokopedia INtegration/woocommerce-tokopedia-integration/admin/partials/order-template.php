<?php
/**
 * Shipment Order Template
 *
 * @package  Woocommerce_Tokopedia_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $post;

$order_id                   = isset( $post->ID ) ? intval( $post->ID ) : '';
$merchant_order_id          = get_post_meta( $order_id, 'merchant_order_id', true );
$purchase_order_id          = get_post_meta( $order_id, 'purchaseOrderId', true );
$fulfillment_node           = get_post_meta( $order_id, 'fulfillment_node', true );
$order_detail               = get_post_meta( $order_id, 'order_detail', true );
$number_items               = 0;
$updated_order_status       = get_post_meta( $order_id, '_tokopedia_umb_order_status', true );
$umb_tokopedia_order_state  = get_post_meta( $order_id , 'ced_tokopedia_order_state', true );

if (  400 == $umb_tokopedia_order_state ) {
	$umb_tokopedia_order_status = 'Created';
}
if( 500 == $umb_tokopedia_order_state ){
	$umb_tokopedia_order_status = 'Shipped';
}

if ( 700 == $order_status ) {
	$_order = wc_get_order( $order_id );
	$_order->update_status( 'wc-completed' );
	$umb_tokopedia_order_status = 'Shipped';
}

if (  10 == $umb_tokopedia_order_state ) {
	$umb_tokopedia_order_status = 'Cancelled';
}

if ( $updated_order_status == 'Shipped' ) {
	$umb_tokopedia_order_status = $updated_order_status;	
}

$ced_tokopedia_tracking_code = get_post_meta( $order_id,'_tokopedia_umb_order_srn', true );
$ced_tokopedia_tracking_code = !empty( $ced_tokopedia_tracking_code ) ? $ced_tokopedia_tracking_code : '';

?>
<div id="umb_tokopedia_order_settings" class="panel woocommerce_options_panel">
	<div class="ced_tokopedia_loader" class="loading-style-bg" style="display: none;">
		<img src="<?php echo esc_url( CED_TOKOPEDIA_URL . 'admin/images/loading.gif' ); ?>">
	</div>

	<div class="options_group">
		<p class="form-field">
			<h3><center>
				<?php
				esc_html_e( 'TOKOPEDIA ORDER STATUS : ', 'woocommerce-tokopedia-integration' );
				echo esc_attr( strtoupper( $umb_tokopedia_order_status ) );
				if( $ced_tokopedia_tracking_code ){
					echo "<br>";
					esc_html_e( 'SHIPPING REFERENCE NO : ', 'woocommerce-tokopedia-integration' );
					echo esc_attr( $ced_tokopedia_tracking_code );
				}
				?>
			</center></h3>
		</p>
	</div>

	<div class="ced_tokopedia_error"></div>
	<div class="options_group umb_tokopedia_options"> 
			<div id="ced_tokopedia_shipment_wrap">
				<div>
					<table class="widefat fixed stripped">
						<tbody>
							<?php
							if ( 'Created' == $umb_tokopedia_order_status ) {
							?>
							<tr>
								<td>
									<span>Shipping Reference</span>
								</td>
								<td>
									<input type="text" name="" value="<?php echo $ced_tokopedia_tracking_code; ?>" id="ced_tokopedia_tracking_code" class="ced_tokopedia_required_data">
								</td>
							</tr>
							
							<tr>
								<td>
									<input type="button" class="button button-primary" name="" id="ced_tokopedia_submit_shipment" value="Submit" data-order-id="<?php echo esc_attr( $order_id ); ?>">
								</td>
								<td>	
									<span class="ced_spinner spinner"></span>									
								</td>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
	</div>    
</div>    
