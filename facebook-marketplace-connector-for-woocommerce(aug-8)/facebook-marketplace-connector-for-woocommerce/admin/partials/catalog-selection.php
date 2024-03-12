<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$saved_token_data_while_connect_to_fb = get_option( 'ced_fmcw_connected_fb_response_and_decoded_token_response', true );
$shop_id                              = isset( $saved_token_data_while_connect_to_fb['data']['shop_id'] ) ? $saved_token_data_while_connect_to_fb['data']['shop_id'] : '';
$registration_data                    = get_option( 'ced_fmcw_registered_with_cedcommerce', array() );
$registration_data_token              = isset( $registration_data['reg_refresh_token'] ) ? $registration_data['reg_refresh_token'] : '';
$social_fb_action                     = 'core/token/getTokenByRefresh';
$fileName                             = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
require_once $fileName;
$ced_fmcw_send_request      = new Class_Ced_Fmcw_Send_Http_Request();
$call_for_get_refresh_token = $ced_fmcw_send_request->get_refresh_token( $social_fb_action, $registration_data_token );
$refresh_token              = isset( $call_for_get_refresh_token['data']['token'] ) ? $call_for_get_refresh_token['data']['token'] : '';
$catalog_res                = array();
if ( ! empty( $refresh_token ) ) {
	update_option( 'ced_fmcw_refresh_token', $refresh_token );
	$social_fb_action          = 'webapi/rest/v1/shop';
	$call_for_get_shop_details = $ced_fmcw_send_request->get_shop_details( $social_fb_action, $refresh_token, $shop_id );
	if ( $call_for_get_shop_details['success'] ) {
		update_option( 'ced_fmcw_stord_whole_store_data', $call_for_get_shop_details['data'] );
		$business_id      = isset( $call_for_get_shop_details['data']['data']['business_manager_id'] ) ? $call_for_get_shop_details['data']['data']['business_manager_id'] : '';
		$parameters       = array(
			'id'      => $business_id,
			'shop_id' => $shop_id,
		);
		$social_fb_action = 'webapi/rest/v1/business/catalogs';
		$catalog_res      = $ced_fmcw_send_request->get_request( $social_fb_action, $parameters );
	}
}
?>
	<div class="ced-section-page-wrapper">
		<div class="ced-section-common-container ced-facebook-common-content-wrap">
			<div class="ced-section-cmmon-content">
				<div class="ced-step-wrapper">
					<div class="ced-product-catelog-wrpper">
						<div class="ced-prodcut-content">
							<ul>
								<li>Select your <span class="ced_facebook_heading">facebook</span> Product Catalog</li>
							</ul>
							<p>A catalogue is a container that holds information about all of the items that you want to promote on Facebook and Instagram.</p>
						</div>
					</div>
					<div class="ced-facebook-content-wrap">
						<div class="ced-content-main">
							<div class="ced-section-main-head">
								<p>Choose Facebook product catelog in which you want to upload your product(s)</p>
							</div>
							<div class="ced-select-dropdown-wrapper">
								<div class="ced-content-dropdown-wrapper">
									<div class="ced-dropdown-wrap">
										<select class="ced_selected_catalog_id">
											<?php
											if ( is_array( $catalog_res['data'] ) && ! empty( $catalog_res['data'] ) ) {
												foreach ( $catalog_res['data'] as $key => $value ) {
													$value_id   = isset( $value['id'] ) ? sanitize_text_field( $value['id'] ) : '';
													$value_name = isset( $value['name'] ) ? sanitize_text_field( $value['name'] ) : '';
													printf( '<option value="' . esc_attr( $value_id ) . '">' . esc_attr( $value_name ) . '</option>' );
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="ced-next-button-wrap">
								<div class="ced-back-wrapper">
									<span class="ced-button blue ced_save_catalog" data-business_id="<?php esc_attr_e( $business_id ); ?>"><a href="#">Next</a></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>