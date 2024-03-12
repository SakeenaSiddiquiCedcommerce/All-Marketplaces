<?php
/**
 * Connect your Facebook Account
 *
 * @package  Facebook_Marketplace_Connector_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$registration_data = get_option( 'ced_fmcw_registered_with_cedcommerce', array() );
$sAppId            = isset( $registration_data['reg_app_id'] ) ? $registration_data['reg_app_id'] : '';
$timezone          = get_option( 'timezone_string' );
$timezone          = wp_timezone_string();
?>
<div class="ced_fmcw_loader">
	<img src="<?php echo esc_attr( CED_FMCW_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_fmcw_category_loading_img" id="<?php echo 'ced_fmcw_category_loading_img'; ?>">
</div>
<div class="ced-fmcw-wrappers ced-facbook-confirm-accout-holder">
	<div class="ced-fmcw-fb-account-connect-wrapper">
		<div class="ced-fmcw-wrap-text">
			<div class="ced-fmcw-fb-account-connect-field-wrapper-wrap">
				<div class="ced-facebook-confirm-connect-wrap">
					<div class="ced-fmcw-heading-wrapper">
						<h2><?php printf( 'Connect to <span class="ced_facebook_heading">facebook</span>', 'facebook-marketplace-connector-for-woocommerce' ); ?></h2>
					</div>
					<div class='ced_facebook_content_below_title'>
						<p><?php printf( 'Showcase your products on Facebook and reach shoppers across the globe.', 'facebook-marketplace-connector-for-woocommerce' ); ?></p>
					</div>
					<div class="ced-fmcw-fb-account-connect-field-text">
						<h3>Account not connected</h3>
					</div>
					
				</div>
				<div class="ced-fmcw-fb-account-connect-field-wrapper ced-fmcw-registration-field-wrapper">
					<div class="ced_fmcw_connect_content">Manage all of your business activity on Facebook, Messenger and Instagram from one place with Meta Business Suite.</div>
					<input type="button" data-sAppId="<?php echo esc_attr( $sAppId ); ?>" data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>"data-timezone="<?php echo esc_attr( $timezone ); ?>" data-identifier="<?php echo esc_attr( 'woo-' . home_url() ); ?>" class="ced-fmcw-button ced-fmcw-fb-account-connect-button" id="ced-fmcw-fb-account-connect" value="<?php esc_attr_e( 'Connect', 'facebook-marketplace-connector-for-woocommerce' ); ?>">
					<p>Click this button for activate your account</p>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
