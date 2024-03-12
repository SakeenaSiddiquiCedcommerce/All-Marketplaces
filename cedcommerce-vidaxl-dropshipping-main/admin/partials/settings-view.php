<?php
/**
 * Settings
 *
 * @package  Woocommerce_vidaXL_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_VIDAXL_DROPSHIPPING_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	include_once $file;
}
if ( isset( $_POST['ced_vidaxl_save_settings'] ) ) {

	if ( ! isset( $_POST['ced_vidaxl_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ced_vidaxl_settings_submit'] ) ), 'settings_data' ) ){
		return;
	}
	
	$sanitized_array	= filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$settings_data      = array();
	$settings_data      = get_option( 'ced_vidaxl_settings_data', array() );
	$settings_data		= isset( $sanitized_array['ced_vidaxl_settings_data'] ) ? ( $sanitized_array['ced_vidaxl_settings_data'] ) : array();
	update_option( 'ced_vidaxl_settings_data', $settings_data );
	if ( isset( $_POST['ced_vidaxl_settings_data']['ced_fmcw_vidaxl_productimage_update'] ) && '0' != $_POST['ced_vidaxl_settings_data']['ced_fmcw_vidaxl_productimage_update'] ) {
		wp_clear_scheduled_hook( 'ced_fmcw_vidaxl_productimage_update_scheduler_job' );
		wp_schedule_event( time(), sanitize_text_field( $_POST['ced_vidaxl_settings_data']['ced_fmcw_vidaxl_productimage_update'] ), 'ced_fmcw_vidaxl_productimage_update_scheduler_job' );
	} else {
		wp_clear_scheduled_hook( 'ced_fmcw_vidaxl_productimage_update_scheduler_job' );
	}

	echo '<div class="notice notice-success settings-error is-dismissible" ><p>' . esc_html( __( 'Settings Saved Successfully', 'cedcommerce-vidaxl-dropshipping' ) ) . '</p><button type="button" class="notice-dismiss"></button></div>';
}
$render_settings_data = get_option( 'ced_vidaxl_settings_data', false );
?>
<div class="ced-vidaxl-container">
	<h2><?php esc_html_e( 'General Settings', 'cedcommerce-vidaxl-dropshipping' ); ?></h2>
	<form method="post" action="">
	<?php wp_nonce_field( 'settings_data', 'ced_vidaxl_settings_submit' ); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<?php
						$push_order_status = isset( $render_settings_data['ced_vidaxl_push_order_status'] ) ? sanitize_text_field( $render_settings_data['ced_vidaxl_push_order_status'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_push_order_status"><?php esc_html_e( 'Push Orders of Status', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>
						<select id="ced_vidaxl_push_order_status" name="ced_vidaxl_settings_data[ced_vidaxl_push_order_status]" class="ced-vidaxl-form-elements">
							<option value="" <?php echo ( '' == $push_order_status ) ? 'selected' : ''; ?> >Select Order Status</option>
							<option value="on-hold" <?php echo ( 'on-hold' == $push_order_status ) ? 'selected' : ''; ?>>On Hold</option>
							<option value="processing" <?php echo ( 'processing' == $push_order_status ) ? 'selected' : ''; ?> >Processing</option>
							<option value="completed" <?php echo ( 'completed' == $push_order_status ) ? 'selected' : ''; ?>>Completed</option>
							<option value="manual" <?php echo ( 'manual' == $push_order_status ) ? 'selected' : ''; ?>>Manual</option>
						</select>
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Select Order status in which you want to send your orders to vidaXL.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<?php
						$use_price_in_wc = isset( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) ? sanitize_text_field( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_use_price_in_wc"><?php esc_html_e( 'Use Price in WooCommerce', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>
						<select id="ced_vidaxl_use_price_in_wc" name="ced_vidaxl_settings_data[ced_vidaxl_use_price_in_wc]" class="ced-vidaxl-form-elements">
							<option value="" <?php echo ( '' == $use_price_in_wc ) ? 'selected' : ''; ?> >Select Price</option>
							<option value="webshop" <?php echo ( 'webshop' == $use_price_in_wc ) ? 'selected' : ''; ?>>WebShop Price</option>
							<option value="b2b" <?php echo ( 'b2b' == $use_price_in_wc ) ? 'selected' : ''; ?> >B2B Price</option>
						</select>
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Select price which you want to import as WooCommerce Price.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>

				<tr>
					<?php
						$markup_type = isset( $render_settings_data['ced_vidaxl_markup_type'] ) ? sanitize_text_field( $render_settings_data['ced_vidaxl_markup_type'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_markup_type"><?php esc_html_e( 'Markup Type', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>
						<select id="ced_vidaxl_markup_type" name="ced_vidaxl_settings_data[ced_vidaxl_markup_type]" class="ced-vidaxl-form-elements">
							<option value="" <?php echo ( '' == $markup_type ) ? 'selected' : ''; ?> >Select Markup Type</option>
							<option value="fixed" <?php echo ( 'fixed' == $markup_type ) ? 'selected' : ''; ?>>Fixed Price Markup</option>
							<option value="percentage" <?php echo ( 'percentage' == $markup_type ) ? 'selected' : ''; ?> >Percentage Markup</option>
						</select>
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Select markup type for the price.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<?php
						$markup_value = isset( $render_settings_data['ced_vidaxl_markup_value'] ) ? sanitize_text_field( $render_settings_data['ced_vidaxl_markup_value'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_markup_value"><?php esc_html_e( 'Markup Value', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>	
						<input type="text" name="ced_vidaxl_settings_data[ced_vidaxl_markup_value]" id="ced_vidaxl_markup_value" class="ced-vidaxl-form-elements" value="<?php echo esc_attr( $markup_value ); ?>">
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Set Markup Value (Do not write % sign or any other symbol)', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>
					<?php
						$ced_fmcw_vidaxl_productimage_update = isset( $render_settings_data['ced_fmcw_vidaxl_productimage_update'] ) ? sanitize_text_field( $render_settings_data['ced_fmcw_vidaxl_productimage_update'] ) : '';
					?>
					<div  class="ced_fmcw_gender_field form-field">
						<th>
							<label for="ced_vidaxl_markup_value"><?php esc_html_e( 'Enable Product Image Creation', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
						</th>
						<td>
						<div class="ced_wmcw_leaf_node">
							<select name=ced_vidaxl_settings_data[ced_fmcw_vidaxl_productimage_update] class="ced_fmcw_data_fields">
							<option <?php echo ( '0' == $ced_fmcw_vidaxl_productimage_update ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-vodaXl' ); ?></option>
							<option <?php echo ( 'ced_vidaXl_image_10min' == $ced_fmcw_vidaxl_productimage_update ) ? 'selected' : ''; ?>  value="ced_vidaXl_image_10min"><?php esc_html_e( 'Every 10 Minutes', 'ced-umb-vodaXl' ); ?></option>
							<option <?php echo ( 'ced_vidaXl_image_15min' == $ced_fmcw_vidaxl_productimage_update ) ? 'selected' : ''; ?>  value="ced_vidaXl_image_15min"><?php esc_html_e( 'Every 15 Minutes', 'ced-umb-vodaXl' ); ?></option>
							<option <?php echo ( 'ced_vidaXl_image_20min' == $ced_fmcw_vidaxl_productimage_update ) ? 'selected' : ''; ?>  value="ced_vidaXl_image_20min"><?php esc_html_e( 'Every 20 Minutes', 'ced-umb-vodaXl' ); ?></option>
						</select></div>
					</div >
					</td>
				<tr>
					<th>
						
					</th>
					<td>	
					<button name ="ced_vidaxl_save_settings" class="ced-vidaxl-button"><span>Save</span></button>
					</td>
				</tr>                          
			</tbody>
		</table>
	</form>
</div>