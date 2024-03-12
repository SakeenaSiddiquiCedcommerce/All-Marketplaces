<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$fileName = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
require_once $fileName;
$ced_fmcw_send_request                = new Class_Ced_Fmcw_Send_Http_Request();
$saved_token_data_while_connect_to_fb = get_option( 'ced_fmcw_connected_fb_response_and_decoded_token_response', true );
$shop_id                              = isset( $saved_token_data_while_connect_to_fb['data']['shop_id'] ) ? $saved_token_data_while_connect_to_fb['data']['shop_id'] : '';
$refresh_token                        = get_option( 'ced_fmcw_refresh_token' );
$call_for_get_shop_details            = get_option( 'ced_fmcw_stord_whole_store_data' );
$business_id                          = isset( $call_for_get_shop_details['data']['business_manager_id'] ) ? $call_for_get_shop_details['data']['business_manager_id'] : '';
$parameters                           = array(
	'id'      => $business_id,
	'shop_id' => $shop_id,
);
$social_fb_action                     = 'webapi/rest/v1/business/catalogs';
$catalog_res                          = $ced_fmcw_send_request->get_request( $social_fb_action, $parameters );

$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', true );
$connected_catalog_id = '';
if ( ! empty( $catalog_and_page_id ) ) {
	$bussiness_id         = array_keys( $catalog_and_page_id )[0];
	$connected_catalog_id = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
}
if ( isset( $_POST['ced_facebook_scheduler_save_button'] ) ) {
	if ( ! isset( $_POST['facebook_settings_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['facebook_settings_actions'] ) ), 'facebook_settings' ) ) {
		return;
	}
	$sanitized_array                    = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$global_configuration_settings_data = isset( $sanitized_array['ced_fmcw_configuration_data'] ) ? $sanitized_array['ced_fmcw_configuration_data'] : array();
	update_option( 'ced_fmcw_configuration_settings', $global_configuration_settings_data );
	$ced_fmcw_reconnect_catalog = isset( $global_configuration_settings_data['ced_fmcw_reconnect_catalog']['value'] ) ? $global_configuration_settings_data['ced_fmcw_reconnect_catalog']['value'] : '';
	if ( ! empty( $ced_fmcw_reconnect_catalog ) ) {
		$catalog_and_page_id                                = get_option( 'ced_fmcw_catalog_and_page_id', array() );
		$catalog_and_page_id[ $bussiness_id ]['catalog_id'] = $ced_fmcw_reconnect_catalog;
		update_option( 'ced_fmcw_catalog_and_page_id', $catalog_and_page_id );
	}
	if ( isset( $_POST['ced_fmcw_configuration_data']['ced_fmcw_product_sync']['value'] ) && '0' != $_POST['ced_fmcw_configuration_data']['ced_fmcw_product_sync']['value'] ) {
		wp_clear_scheduled_hook( 'ced_fmcw_product_sync_scheduler_job' );
		wp_schedule_event( time(), sanitize_text_field( $_POST['ced_fmcw_configuration_data']['ced_fmcw_product_sync']['value'] ), 'ced_fmcw_product_sync_scheduler_job' );
	} else {
		wp_clear_scheduled_hook( 'ced_fmcw_product_sync_scheduler_job' );
	}
	if ( isset( $_POST['ced_fmcw_configuration_data']['ced_fmcw_order_sync']['value'] ) && '0' != $_POST['ced_fmcw_configuration_data']['ced_fmcw_order_sync']['value'] ) {
		wp_clear_scheduled_hook( 'ced_fmcw_order_sync_scheduler_job' );
		wp_schedule_event( time(), sanitize_text_field( $_POST['ced_fmcw_configuration_data']['ced_fmcw_order_sync']['value'] ), 'ced_fmcw_order_sync_scheduler_job' );
	} else {
		wp_clear_scheduled_hook( 'ced_fmcw_order_sync_scheduler_job' );
	}
}
$global_configuration_settings_data = get_option( 'ced_fmcw_configuration_settings', array() );
?>
<div class="ced-fmcw-wrapper-wrap">
	<div class="ced-fmcw-category-mapping-main-wrapper ced-facebook-back">
		<div class="ced_fmcw_configuration_heading_wrapper ced-fmcw-heading-wrapper">
			<h2 class="ced_fmcw_heading ced_fmcw_configuration_heading"> <?php esc_attr_e( 'Configuration', 'facebook-marketplace-connector-for-woocommerce' ); ?> </h2>
		</div>
		<form method="post" action="<?php esc_attr_e( admin_url() . '/admin.php?page=ced_fb&section=configuration' ); ?>">
			<div class="ced_fmcw_loader">
				<img src="<?php echo esc_attr( CED_FMCW_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_fmcw_category_loading_img" id="<?php echo 'ced_fmcw_category_loading_img_' . esc_attr( $value->term_id ); ?>">
			</div>
			<div class="ced_fmcw_facebook_panel_configuration">
				<div class="ced_fmcw_facebook_collapsess">
					<?php $ced_fmcw_product_sync = ''; ?>
					<?php $ced_fmcw_product_sync = isset( $global_configuration_settings_data['ced_fmcw_product_sync']['value'] ) ? $global_configuration_settings_data['ced_fmcw_product_sync']['value'] : ''; ?>
					<div  class="ced_fmcw_gender_field form-field">
						<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Select Product Sync frequency', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
						<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_configuration_data[ced_fmcw_product_sync][value] class="ced_fmcw_data_fields">
							<option disabled <?php echo ( '0' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'daily' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'twicedaily' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'ced_fb_6min' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?> value="ced_fb_6min"><?php esc_html_e( 'Every 6 Minutes', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'ced_fb_10min' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?>  value="ced_fb_10min"><?php esc_html_e( 'Every 10 Minutes', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'ced_fb_15min' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?>  value="ced_fb_15min"><?php esc_html_e( 'Every 15 Minutes', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'ced_fb_30min' == $ced_fmcw_product_sync ) ? 'selected' : ''; ?>  value="ced_fb_30min"><?php esc_html_e( 'Every 30 Minutes', 'ced-umb-fb' ); ?></option>
						</select></div>
					</div >
					<?php $ced_fmcw_order_sync = ''; ?>
					<?php
					$ced_fmcw_order_sync     = isset( $global_configuration_settings_data['ced_fmcw_order_sync']['value'] ) ? $global_configuration_settings_data['ced_fmcw_order_sync']['value'] : '';
					$ced_fmcw_store_location = isset( $global_configuration_settings_data['ced_fmcw_store_location']['value'] ) ? $global_configuration_settings_data['ced_fmcw_store_location']['value'] : '';
					if ( 'usa' == $ced_fmcw_store_location ) {
						?>
						<div  class="ced_fmcw_gender_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Select Order Sync frequency', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_configuration_data[ced_fmcw_order_sync][value] class="ced_fmcw_data_fields">
								<option disabled <?php echo ( '0' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-fb' ); ?></option>
								<option <?php echo ( 'daily' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'ced-umb-fb' ); ?></option>
								<option <?php echo ( 'twicedaily' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'ced-umb-fb' ); ?></option>
								<option <?php echo ( 'ced_fb_6min' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?> value="ced_fb_6min"><?php esc_html_e( 'Every 6 Minutes', 'ced-umb-fb' ); ?></option>
								<option <?php echo ( 'ced_fb_10min' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?>  value="ced_fb_10min"><?php esc_html_e( 'Every 10 Minutes', 'ced-umb-fb' ); ?></option>
								<option <?php echo ( 'ced_fb_15min' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?>  value="ced_fb_15min"><?php esc_html_e( 'Every 15 Minutes', 'ced-umb-fb' ); ?></option>
								<option <?php echo ( 'ced_fb_30min' == $ced_fmcw_order_sync ) ? 'selected' : ''; ?>  value="ced_fb_30min"><?php esc_html_e( 'Every 30 Minutes', 'ced-umb-fb' ); ?></option>
							</select></div>
						</div >
						<?php
					}
					$ced_fmcw_reconnect_catalog = '';
					?>
					<?php
					$ced_fmcw_reconnect_catalog = ! empty( $connected_catalog_id ) ? $connected_catalog_id : '';
					if ( empty( $ced_fmcw_reconnect_catalog ) ) {
						$ced_fmcw_reconnect_catalog = isset( $global_configuration_settings_data['ced_fmcw_reconnect_catalog']['value'] ) ? $global_configuration_settings_data['ced_fmcw_reconnect_catalog']['value'] : '';
					}
					?>
					<div  class="ced_fmcw_gender_field form-field">
						<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Reconnect Catalog', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
						<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_configuration_data[ced_fmcw_reconnect_catalog][value] class="ced_fmcw_data_fields">
							<option disabled <?php echo ( '0' == $ced_fmcw_reconnect_catalog ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-fb' ); ?></option>
							<?php
							if ( is_array( $catalog_res['data'] ) && ! empty( $catalog_res['data'] ) ) {
								foreach ( $catalog_res['data'] as $key => $value ) {
									$selected   = '';
									$value_id   = isset( $value['id'] ) ? sanitize_text_field( $value['id'] ) : '';
									$value_name = isset( $value['name'] ) ? sanitize_text_field( $value['name'] ) : '';
									if ( $ced_fmcw_reconnect_catalog == $value_id ) {
										$selected = 'selected';
									}
									printf( '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value_id ) . '">' . esc_attr( $value_name ) . '</option>' );
								}
							}
							?>
						</select></div>
					</div >
					<?php $ced_fmcw_store_location = ''; ?>
					<?php $ced_fmcw_store_location = isset( $global_configuration_settings_data['ced_fmcw_store_location']['value'] ) ? $global_configuration_settings_data['ced_fmcw_store_location']['value'] : ''; ?>
					<div  class="ced_fmcw_gender_field form-field">
						<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Select Country', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
						<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_configuration_data[ced_fmcw_store_location][value] class="ced_fmcw_data_fields">
							<option disabled <?php echo ( '0' == $ced_fmcw_store_location ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'usa' == $ced_fmcw_store_location ) ? 'selected' : ''; ?>  value="usa"><?php esc_html_e( 'USA', 'ced-umb-fb' ); ?></option>
							<option <?php echo ( 'other' == $ced_fmcw_store_location ) ? 'selected' : ''; ?>  value="other"><?php esc_html_e( 'Other', 'ced-umb-fb' ); ?></option>
						</select></div>
					</div >
				</div>
			</div>
			<?php
			wp_nonce_field( 'facebook_settings', 'facebook_settings_actions' );
			?>
			<input type="submit" class="ced_fb_custom_button save_profile_button" name="ced_facebook_scheduler_save_button" value="Save Configuration"></input>
		</form>
	</div></div>