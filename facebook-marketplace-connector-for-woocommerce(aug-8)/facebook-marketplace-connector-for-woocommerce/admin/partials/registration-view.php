<?php
/**
 * Register to CedCommerce Connector Section
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
$reg_email         = isset( $registration_data['reg_email'] ) ? $registration_data['reg_email'] : '';
?>
<div class="ced_fmcw_loader">
	<img src="<?php echo esc_attr( CED_FMCW_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_fmcw_category_loading_img" id="<?php echo 'ced_fmcw_category_loading_img'; ?>">
</div>
<div class="ced-fmcw-wrappers ced-facebook-registraction-wrapper">
	<div class="ced-fmcw-registration-wrapper ced-facebook-registration-heading">
		<div class="ced-fmcw-heading-wrapper">
			<h2><?php printf( 'Connect to<span class="ced_facebook_heading">facebook</span>', 'facebook-marketplace-connector-for-woocommerce' ); ?></h2>
			<div class='ced_facebook_content_below_title'>
				<p><?php printf( 'Experience real-time automation with Facebook Marketplace Connector for WooCommerce.', 'facebook-marketplace-connector-for-woocommerce' ); ?></p>
			</div>
		</div>
		<div class="ced-fmcw-registration-field-wrapper">
			<div class="ced_gmcw_show_error"></div>
			<input type="text" class="ced-fmcw-text-fields ced-fmcw-registration-email-field" id="ced-fmcw-registration-reg_email-field" value="<?php echo esc_attr( $reg_email ); ?>" placeholder="<?php esc_attr_e( 'Enter your email address to Register', 'facebook-marketplace-connector-for-woocommerce' ); ?>">
			<select class="ced_facebook_selected_country">
				<option disabled selected>--Select Country--</option>
				<option value= "usa">USA</option>
				<option value= "other">Other than USA</option>
			</select>
			<input type="button" class="ced-fmcw-button ced-fmcw-registration-button" id="ced-fmcw-register-to-cedcommerce" value="<?php esc_attr_e( 'Connect', 'facebook-marketplace-connector-for-woocommerce' ); ?>">
		</div>
	</div>
</div>
</div>
