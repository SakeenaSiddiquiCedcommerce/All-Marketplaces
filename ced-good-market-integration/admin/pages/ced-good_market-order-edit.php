<?php
/**
 * Order edit section to be rendered
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

get_good_market_header();

$order_id                     = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : 0;
$ced_good_market_order_status = get_post_meta( $order_id, '_ced_good_market_order_status', true );
$good_market_shipped_details  = get_post_meta( $order_id, '_ced_good_market_shipped_data', true );
if ( ( isset( $good_market_shipped_details ) && ! empty( $good_market_shipped_details ) ) || ( isset( $ced_good_market_order_status ) && ! empty( $ced_good_market_order_status ) ) ) {
	$merchant_order_id       = get_post_meta( $order_id, 'merchant_order_id', true );
	$good_market_order_id    = get_post_meta( $order_id, 'order_detail', true );
	$good_market_order_id    = $good_market_order_id['order_increment_id'];
	$purchase_order_id       = get_post_meta( $order_id, 'purchaseOrderId', true );
	$fulfillment_node        = get_post_meta( $order_id, 'fulfillment_node', true );
	$order_detail            = get_post_meta( $order_id, 'order_detail', true );
	$order_items             = get_post_meta( $order_id, 'order_items', true );
	$order_shipment_tracking = get_post_meta( $order_id, 'good_market_order_shipment_tracking', true );
	$number_track            = ! empty( $order_shipment_tracking[0]['number'] ) ? $order_shipment_tracking[0]['number'] : '';
	$title_track             = ! empty( $order_shipment_tracking[0]['title'] ) ? $order_shipment_tracking[0]['title'] : '';
	$carrier_code            = ! empty( $order_shipment_tracking[0]['carrier_code'] ) ? $order_shipment_tracking[0]['carrier_code'] : '';
	$tracking_comment        = ! empty( $order_shipment_tracking[0]['comment'] ) ? $order_shipment_tracking[0]['comment'] : '';
	$track_number_saved      = !empty($number_track) ? 'disabled' : '';
	$title_track_saved       = !empty($title_track) ? 'disabled' : '';
	$tracking_comment_saved  = !empty($tracking_comment) ? 'disabled' : '';

	$number_items = 0;
	// Get order status

	$ced_good_market_order_status = get_post_meta( $order_id, '_ced_good_market_order_status', true );

	if ( empty( $ced_good_market_order_status ) ) {
		$ced_good_market_order_status = __( 'Created', 'good_market-woocommerce-integration' );
	}
	?>
	<div id="ced_good_market_order_settings" class="panel woocommerce_options_panel ced_good_market_section_wrapper">
		<div class="options_group">
			<p class="form-field">
				<h3><center>
					<?php
					esc_html_e( 'GOOD MARKET ORDER STATUS : ', 'good_market-woocommerce-integration' );
					echo esc_attr( strtoupper( $ced_good_market_order_status ) );
					?>
				</center></h3>
			</p>
		</div>
		<div class="ced_good_market_heading"></div>
		<div class="options_group umb_good_market_options"> 
			<?php

			// if ( 'Fetched' == $ced_good_market_order_status ) {
			?>
			<!-- <input type="button" class="button-primary" value="Cancel Order" data-order_id = "<?php echo esc_attr( $order_id ); ?>" id="ced_good_market_cancel_action"/> -->

			<input type="hidden" id="good_market_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
			<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
			<h2 class="title"><?php esc_html_e( 'Shipment Information', 'good_market-woocommerce-integration' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<tbody>
					<tr>
						<td><b><?php esc_html_e( 'Reference Order Id on Good Market', 'good_market-woocommerce-integration' ); ?></b></td>
						<td class="good_market-success"><?php echo esc_attr( $good_market_order_id ); ?></td>
					</tr>
					<tr>
						<td><b><?php esc_html_e( 'Tracking number', 'good_market-woocommerce-integration' ); ?></b></td>
						<td><input class="ced_good_market_required_data input-text required-entry" <?php echo $track_number_saved ?> type="text" id="ced_good_market_tracking_num" value="<?php echo esc_html( $number_track ); ?>" /></td>
					</tr>
					<tr> 
						<td><b><?php esc_html_e( 'Name of shipping service', 'good_market-woocommerce-integration' ); ?></b></td>
						<td><input class="ced_good_market_required_data input-text required-entry"  type="text" id="ced_good_market_tracking_title" <?php echo $title_track_saved ?> value="<?php echo esc_html( $title_track ); ?>" /></td>
					</tr>
					
					<tr>
						<td><b><?php esc_html_e( 'Comments', 'good_market-woocommerce-integration' ); ?></b></td>
						<td><input class="ced_good_market_required_data input-text"  type="text" id="ced_good_market_tracking_comment" <?php echo $tracking_comment_saved ?> value="<?php echo esc_html( $tracking_comment ); ?>" /></td>
					</tr>

				</tbody>
			</table>	
			<h2 class="title"><?php esc_html_e( 'Shipment Items', 'good_market-woocommerce-integration' ); ?></h2>
			<table class=" widefat fixed striped">
				<thead>
					<tr class="headings">
						<th><?php esc_html_e( 'Product sku', 'good_market-woocommerce-integration' ); ?></th>
						<th><?php esc_html_e( 'Quantity ordered', 'good_market-woocommerce-integration' ); ?></th>
						<th><?php esc_html_e( 'Quantity to Ship', 'good_market-woocommerce-integration' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $order_items as $k => $valdata ) {
							// print_r($valdata);die('gh');
						$number_items++;
						$cancel_qty      = 0;
						$real_cancel_qty = 0;
						$avail_qty       = $valdata['qty_ordered'];
						$line_number     = $k;
						$ship_qty        = (int) ( $valdata['qty_ordered'] );

						?>
						<tr>
							<td>
								<input type="hidden" id="lineNumber_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $valdata['product_id'] ); ?>">
								<input type="hidden" id="sku_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $valdata['sku'] ); ?>">
								<strong><?php echo esc_attr( $valdata['sku'] ); ?></strong>
							</td>
							<td>
								<input type="hidden" id="qty_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $valdata['qty_ordered'] ); ?>">
								<strong><?php echo esc_attr( $valdata['qty_ordered'] ); ?></strong>
							</td>
							<?php
							if ( $avail_qty > 0 ) :
								?>
								<td>
									<strong><?php echo esc_attr( $avail_qty ); ?></strong>
									<!-- <input class="admin__control-text" type="text" maxlength="70" id="ship_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $avail_qty ); ?>" onkeypress="return isNumberKey(event);"> -->
								</td>

							<?php else : ?>
								<td>
									<!-- <input type="hidden" id="ship_<?php // echo esc_attr( $k ); ?>" value="<?php // echo esc_attr( $avail_qty ); ?>"> -->
									<strong><?php echo esc_attr( $avail_qty ); ?></strong>
									<strong 
									<?php
									if ( $avail_qty <= 0 ) {
										echo ' style="color: #EE0000" ';}
									?>
										>
										<?php echo esc_attr( $avail_qty ); ?>
									</strong>
								</td>
								<td>
									<input type="hidden" id="can_<?php echo esc_attr( $k ); ?>"value="<?php echo esc_attr( $cancel_qty ); ?>">
									<strong 
									<?php
									if ( $avail_qty <= 0 ) {
										echo ' style="color: #EE0000" ';}
									?>
										> 
										<?php echo esc_attr( $cancel_qty ); ?>
									</strong>
								</td>
							<?php endif; ?>


						</tr>
						<?php

					}
					?>
				</tbody>
			</table>
			<?php
			$order_shipment = get_post_meta( $order_id, '_ced_good_market_order_status', true );
			if ( 'Shipped' != $order_shipment ) {
				?>

				<input data-items="<?php echo esc_attr( $number_items ); ?>" type="button" class="button-primary" id="ced_good_market_shipment_submit" value="Ship">

				<?php
			}
			?>
		</div>    
	</div>    
	<?php
}
?>