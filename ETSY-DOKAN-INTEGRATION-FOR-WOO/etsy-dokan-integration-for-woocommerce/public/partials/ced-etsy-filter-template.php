<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$productStatus = '';
$uploaded      = '';
$notuploaded   = '';
$vendor_id     = check_if_etsy_vendor();
if ( ! $vendor_id ) {
	return;
}
if ( isset( $_POST['product_status_filter'] ) && $_POST['product_status_filter'] == 'ok' ) {
	$productStatus = isset( $_POST['product_status'] ) ? $_POST['product_status'] : '';
}

?>
<div class="ced_filter_select_wrapper">
<div class="ced-etsy-dokan-product-filter-wrapper">
	<form action="" method="post">
		
			<div class="ced-etsy-product-form">
			<?php
			if ( $productStatus == 'uploaded_on_etsy' ) {
				$uploaded = 'selected';
			} elseif ( $productStatus == 'notuploaded_on_etsy' ) {
				$notuploaded = 'selected';
			}
			?>
			<table>
				<tr><td><select name="product_status" id="product_status" class="product_status dokan-form-control chosen ced_etsy_filter_select">
				<option value="-1">--Select etsy Status--</option>
				<option value="uploaded_on_etsy" <?php echo $uploaded; ?>>Uploaded On etsy</option>
				<option value="notuploaded_on_etsy" <?php echo $notuploaded; ?>>Not Uploaded On etsy</option>
			</select></td>
			<td style="width: 20%"><button type="submit" name="product_status_filter" value="ok" class="dokan-btn dokan-btn-theme">Filter Products</button></td></tr>
			</table>
		</div>
		
	</form>
	</div>
</div>