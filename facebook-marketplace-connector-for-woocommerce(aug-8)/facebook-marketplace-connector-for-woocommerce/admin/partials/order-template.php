<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $post;
$order_id                 = isset( $post->ID ) ? intval( $post->ID ) : '';
$onbuy_onbuy_order_status = get_post_meta( $order_id, '_onbuy_onbuy_order_status', true );
$_facebook_order_details  = get_post_meta( $order_id, '_facebook_order_details', true );
$merchant_order_id        = get_post_meta( $order_id, '_ced_fmcw_order_id', true );
$shop_id                  = get_post_meta( $order_id, '_ced_fmcw_order_page_id_', true );
$order_detail             = get_post_meta( $order_id, '_fmcw_order_complete_details', true );
$order_item               = get_post_meta( $order_id, '_fmcw_order_itemdata', true );
// $provider_list = CED_ONBUY_DIRPATH.'admin/onbuy/json/';
// $provider_list = $provider_list.'provider.json';
// if(file_exists($provider_list)){
// $provider_list = file_get_contents($provider_list);
// $shipping_providers = json_decode($provider_list,true);
// }
$shipping_providers       = array(
	'AUSTRALIA_POST'   => 'AUSTRALIA_POST',
	'CANADA_POST'      => 'CANADA_POST',
	'DHL'              => 'DHL',
	'DHL_ECOMMERCE_US' => 'DHL_ECOMMERCE_US',
	'EAGLE'            => 'EAGLE',
	'FEDEX'            => 'FEDEX',
	'FEDEX_UK'         => 'FEDEX_UK',
);
$number_items             = 0;
$onbuy_onbuy_order_status = get_post_meta( $order_id, '_fmcw_order_status', true );
$onbuy_order_status       = get_post_meta( $order_id, '_fmcw_order_status', true );
if ( empty( $onbuy_onbuy_order_status ) || 'Fetched' == $onbuy_onbuy_order_status ) {
	$onbuy_onbuy_order_status = __( 'Created', 'ced-onbuy' );
}
?>
<div id="onbuy_onbuy_order_settings" class="panel woocommerce_options_panel">
	<div class="options_group">
		<p class="form-field">
			<h3><center>
			<?php
			esc_attr_e( 'FACEBOOK ORDER STATUS : ', 'ced-onbuy' );
			echo esc_attr( strtoupper( $onbuy_order_status ) );
			?>
			</center></h3>
		</p>
	</div>
	<div class="ced_fmcw_loader">
		<img src="<?php echo esc_url( CED_FMCW_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_fmcw_loading_img" >
	</div>
	<?php
	$order_status = get_post_meta( $order_id, '_facebook_order_status_template', true );
	if ( empty( $order_status ) ) {
		?>
	<div class="ced_onbuy_order_template">
		<table class="wp-list-table widefat fixed">
			<thead>
				<th>Complete Shipment</th>
				<th>Cancel Order</th>
				<th>Refund</th>
			</thead>
			<tr>
				<td><input type="button" name="" data-id="ced_facebook_complete_dispatch_template" class="ced_onbuy_button ced_facebook_order_template_sbutton button" value="Complete Shipment"></td>
				<td><input type="button" name="" data-id="ced_facebook_cancel_template" class="ced_onbuy_button ced_facebook_order_template_sbutton button" value="Cancel Order"></td>
				<td><input type="button" name="" data-id="ced_facebook_refund_template" class="ced_onbuy_button ced_facebook_order_template_sbutton button" value="Refund"></td>
			</tr>
		</table>
	</div>
		<?php
	}
	$complete_dispatch = ( 'complete_dispatch' == $order_status ) ? 'display:block' : 'display:none';
	// $partials_dispatch = ($order_status == 'partials_dispatch') ? "display:block" : "display:none";
	$refund = ( 'refund' == $order_status ) ? 'display:block' : 'display:none';
	$cancel = ( 'cancel' == $order_status ) ? 'display:block' : 'display:none';
	?>


<div id="ced_facebook_complete_dispatch_template" style = "<?php echo esc_attr( $complete_dispatch ); ?>" >
	<input type="hidden" id="facebook_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
	<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
	<input type="hidden" id="facebook_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
	<h2 class="title"><?php esc_attr_e( 'Shipment Information', 'ced-onbuy' ); ?> </h2>
	<div id="ced_onbuy_complete_order_shipping">
		<table class="wp-list-table widefat fixed striped">
			<tbody>
				<?php
				$tracking_number = isset( $_facebook_order_details['trackingNo'] ) ? $_facebook_order_details['trackingNo'] : '';
				$provider        = isset( $_facebook_order_details['provider'] ) ? $_facebook_order_details['provider'] : '';
				?>
				<tr>
					<td><b><?php esc_attr_e( 'Tracking Number', 'ced-onbuy' ); ?></b></td>
					<td><input type="text" id="_facebook_tracking_number_complete" value="<?php echo esc_attr( $tracking_number ); ?>"></td>
				</tr>
				<tr>
					<td><b><?php esc_attr_e( 'Shipping Provider', 'ced-onbuy' ); ?></b></td>
					<td>
						<select id="facebook_shipping_providers_complete" name="facebook_shipping_providers_complete">
							<?php
							$options = "<option value='0'>--Select Shipiing Provider--</option>";
							foreach ( $shipping_providers as $key => $value ) {
								if ( $key == $provider ) {
									$options .= '<option selected value="' . $key . '" >' . $value;
								} else {
									$options .= '<option  value="' . $key . '" >' . $value;
								}
							}
							print_r( $options );
							?>
						</select>

					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
	if ( empty( $order_status ) ) {
		?>
	<input data-order-type ="complete" type="button" class="button" id="ced_facebook_shipment_submit" value="Submit Shipment">
		<?php
	}
	?>
</div>


<div id="ced_facebook_refund_template" style = "<?php echo esc_attr( $refund ); ?>" >
	<?php	if ( 'refund' == $order_status ) { ?>
		<h1 style="text-align:center;"><?php esc_attr_e( 'ORDER REFUNDED ', 'onbuy-integration-for-woocommerce' ); ?></h1>
		<?php	} else { ?>
		<input type="hidden" id="facebook_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
		<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
		<input type="hidden" id="facebook_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<th>Refund reason</th>
				<th>Refund Info</th>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="refund_reason_id<?php echo esc_attr( $unq_id ); ?>" id="refund_reason_id">
							<option value="0">--Select Reason--</option>
							<option value="BUYERS_REMORSE">BUYERS REMORSE</option>
							<option value="DAMAGED_GOODS">DAMAGED GOODS</option>
							<option value="NOT_AS_DESCRIBED">NOT AS DESCRIBED</option>
							<option value="QUALITY_ISSUE">QUALITY ISSUE</option>
							<option value="WRONG_ITEM">WRONG ITEM</option>
							<option value="REFUND_REASON_OTHER">Other issue (please specify)</option>
						</select>
					</td>
					<td>
						<input type="text" name="qty_refund" size="50" placeholder="Information" id="refund_info" class="ced_facebook_refund_info"/>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		if ( empty( $order_status ) ) {
			?>
		<input data-order_id ="<?php echo esc_attr( $order_id ); ?>" type="button" class="button" id="ced_facebook_refund_submit" value="Refund Order">
			<?php
		}
		}
		?>

	</div>


	<div id="ced_facebook_cancel_template" style = "<?php echo esc_attr( $cancel ); ?>" >
		<?php	if ( 'cancel' == $order_status ) { ?>
		<h1 style="text-align:center;"><?php esc_attr_e( 'ORDER CANCELLED ', 'onbuy-integration-for-woocommerce' ); ?></h1>
		<?php	} else { ?>
		<input type="hidden" id="facebook_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
		<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
		<input type="hidden" id="facebook_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<th>Cancellation reason</th>
				<th>Cancellation Additional Info</th>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="cancel_reason_id<?php echo esc_attr( $unq_id ); ?>" id="cancel_reason_id">
							<option value="0">--Select Reason--</option>
							<option value="CUSTOMER_REQUESTED">CUSTOMER REQUESTED</option>
							<option value="OUT_OF_STOCK">OUT OF STOCK</option>
							<option value="INVALID_ADDRESS">INVALID ADDRESS</option>
							<option value="SUSPICIOUS_ORDER">SUSPICIOUS ORDER</option>
							<option value="CANCEL_REASON_OTHER">Other Issue (please specify)</option>
						</select>
					</td>
					<td>
						<input type="text" name="qty_cancel" size="50" placeholder="Information" id="cancel_info" class="ced_onbuy_cancel_info"/>
					</td>
				</tr>
			</tbody>
		</table>
			<?php
			if ( empty( $order_status ) ) {
				?>
		<input data-order_id ="<?php echo esc_attr( $order_id ); ?>" type="button" class="button" id="ced_facebook_cancel_submit" value="Cancel Order">
				<?php
			}
		}
		?>
	</div>
</div>
