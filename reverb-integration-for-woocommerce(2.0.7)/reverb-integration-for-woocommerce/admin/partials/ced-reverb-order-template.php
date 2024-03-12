<?php
/**
 * Shipment Order Template
 *
 * @package  Woocommerce_Kogan_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $post;

$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';

$purchaseOrderId = get_post_meta( $order_id, 'purchaseOrderId', true );
$order_detail    = get_post_meta( $order_id, 'ced_reverb_order_details', true );

$_reverb_order_details = get_post_meta( $order_id, '_reverb_order_details', true );
if ( isset( $_reverb_order_details['status'] ) && 'shipped' == $_reverb_order_details['status'] ) {
	update_post_meta( $order_id, '_reverb_umb_order_status', 'Shipped' );
	$umb_reverb_order_status = get_post_meta( $order_id, '_reverb_umb_order_status', true );
}


$provider_list = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
$provider_list = $provider_list . 'provider.json';
if ( file_exists( $provider_list ) ) {
	$provider_list = file_get_contents( $provider_list );
	$provider_list = json_decode( $provider_list, true );
}

$umb_reverb_order_status = get_post_meta( $order_id, '_reverb_umb_order_status', true );

if ( empty( $umb_reverb_order_status ) || 'Fetched' == $umb_reverb_order_status ) {
	$umb_reverb_order_status = __( 'Created', 'ced-reverb' );
}

?>
<div id="ced_reverb_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php echo esc_url( plugin_dir_url( __dir__ ) ); ?>../../admin/images/BigCircleBall.gif">
</div>

	 <div class="options_group">
		 <p class="form-field">
			  <h3><center>
			  <?php
				esc_attr_e( 'REVERB ORDER STATUS : ', 'ced-reverb' );
				echo esc_attr( strtoupper( $umb_reverb_order_status ) );
				?>
				</center></h3>
		  </p>
	  </div>
	  <div class="options_group umb_reverb_options"> 
		<?php
		if ( 'Cancelled' == $umb_reverb_order_status ) {
			?>
				 <h1 style="text-align:center;"><?php esc_attr_e( 'ORDER CANCELLED ', 'ced-reverb' ); ?></h1>
			<?php
		}

		if ( 'Cancelled' != $umb_reverb_order_status && 'Shipped' != $umb_reverb_order_status ) {
			?>
			  <input type="hidden" id="reverb_orderid" value="<?php echo esc_attr( $purchaseOrderId ); ?>" readonly>
			<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
			  <h2 class="title"><?php esc_attr_e( 'Shipment Information', 'ced-reverb' ); ?>:                   
			  </h2>
			  <!-- Ship Complete Order -->
			
			  <div id="ced_reverb_complete_order_shipping">
					 <table class="wp-list-table widefat fixed striped">
					<tbody>
					
						<tr>
							<td><b><?php esc_attr_e( 'Tracking Number', 'ced-reverb' ); ?></b></td>
							<td><input type="text" id="umb_reverb_tracking_number" value=""></td>
						</tr>
						<tr>
							<td><b><?php esc_attr_e( 'Shipping Provider', 'ced-reverb' ); ?></b></td>
							<td>
								<select id="reverb_shipping_providers" name="reverb_shipping_providers">
								<option selected value="--">-Selected-</option>
									<?php

									foreach ( $provider_list['shipping_providers'] as $key => $value ) {
										echo '<option value="' . esc_attr( $value['name'] ) . '">' . esc_attr( $value['name'] );

									}
									?>
								</select>

							 </td>
						</tr>
 
					</tbody>
				</table>	
			 </div>
		   <input data-order_id ="<?php echo esc_attr( $order_id ); ?>" type="button" class="button" id="ced_reverb_shipment_submit" value="Submit Shipment">
		   <!-- Ship Order by LIne Item -->
			<?php
		} elseif ( 'Shipped' == $umb_reverb_order_status ) {
			 $orderDetais = get_post_meta( $order_id, '_umb_order_details', true );

			 $trackingno   = '';
			 $deliveryDate = '';
			if ( is_array( $orderDetais ) && ! empty( $orderDetais ) ) {
				$trackingno = $orderDetais['trackingNo'];
				$provider   = $orderDetais['provider'];
			}

			if ( ! is_array( $orderDetais ) && empty( $orderDetais ) ) {
				$orderDetais = get_post_meta( $order_id, '_reverb_order_details', true );

				?>
					  <input type="hidden" id="reverb_orderid" value="<?php echo esc_attr( $orderDetais['order_number'] ); ?>" readonly>
					 
					  <h2 class="title"><?php esc_attr_e( 'Shipment Information', 'ced-reverb' ); ?>:                   
					  </h2>
					
					  <!-- Ship Complete Order -->
					
					  <div id="ced_reverb_complete_order_shipping">
							 <table class="wp-list-table widefat fixed striped">
							<tbody>
								<tr>
									   <td><b><?php esc_attr_e( 'Shipping Provider', 'ced-reverb' ); ?></b></td>
									   <td><?php echo esc_attr( $orderDetais['shipping_provider'] ); ?></td>
								   </tr>
								   <tr>
									   <td><b><?php esc_attr_e( 'Shipping Tracking Code', 'ced-reverb' ); ?></b></td>
									   <td><?php echo esc_attr( $orderDetais['shipping_code'] ); ?></td>
								   </tr>
								   <tr>
									   <td><b><?php esc_attr_e( 'Shipping Status', 'ced-reverb' ); ?></b></td>
									   <td>
								<?php
								$shipping_status = $orderDetais['status'];
								$shipping_status = str_replace( '_', ' ', $shipping_status );
								$shipping_status = ucfirst( $shipping_status );
								echo esc_attr( $shipping_status );
								?>
									 </td>
								   </tr>
								<?php
								if ( $orderDetais['_links'] ) {
									foreach ( $orderDetais['_links'] as $key => $value ) {
										if ( 'web_tracking' == $key || 'web_listing' == $key ) {
											$key = str_replace( '_', ' ', $key );
											$key = ucfirst( $key );
											?>
													<tr>
													   <td><b><?php echo esc_attr( $key ); ?></b></td>
													   <td><a href="<?php echo esc_attr( $value['href'] ); ?>">
																			   <?php
																				echo esc_attr( $key );
																				?>
													</a></td>
												   </tr>
											 <?php
										}
									}
								}
								?>
							</tbody>
						</table>	
					 </div>
					 <?php

			} else {
				?>
				 <input type="hidden" id="reverb_orderid" value="<?php echo esc_attr( $purchaseOrderId ); ?>" readonly>
				<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
				  <h2 class="title"><?php esc_attr_e( 'Shipment Information', 'ced-reverb' ); ?>:                   
				  </h2>
				  <!-- Ship Complete Order -->
				  <div id="ced_reverb_complete_order_shipping">
						 <table class="wp-list-table widefat fixed striped">
						<tbody>
							 
							<tr>
								<td><b><?php esc_attr_e( 'Shipping  Tracking No.', 'ced-reverb' ); ?></b></td>
								<td>
									<?php echo esc_attr( $orderDetais['trackingNo'] ); ?>
								</td>
							</tr>
							<tr>
								<td><b><?php esc_attr_e( 'Shipping Service Provider', 'ced-reverb' ); ?></b></td>
								<td>
									<?php echo esc_attr( $orderDetais['provider'] ); ?>
								</td>
							</tr>
							  
						</tbody>
					</table>	
				 </div>
				 <?php
			}
		}
		?>
</div>    
</div>    
