<?php
/**
 * Configuration
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_walmart_header();

$partner_details = get_option( 'ced_walmart_us_partner_details', '' );

if ( empty( $partner_details ) ) {
	$partner_details = array();
} else {
	$partner_details = json_decode( $partner_details, true );
	$partne_id       = isset( $partner_details['partner']['partnerId'] ) ? $partner_details['partner']['partnerId'] : '';
	$partnerName     = isset( $partner_details['partner']['partnerName'] ) ? $partner_details['partner']['partnerName'] : '';
	$partnerStoreId  = isset( $partner_details['partner']['partnerStoreId'] ) ? $partner_details['partner']['partnerStoreId'] : '';
	if ( isset( $partner_details['configurations'][0] ) && 'ACCOUNT' == $partner_details['configurations'][0]['configurationName'] ) {
		$acc_status = $partner_details['configurations'][0]['configuration'] ['status'];
	}
	?>
	<ul class="partner_details">
		<li><a><b>Partner Id : </b><?php echo esc_attr($partne_id); ?></a></li>
		<li><a><b>Partner Name : </b><?php echo esc_attr($partnerName); ?></a></li>
		<li><a><b>Status : </b><span><?php echo esc_attr($acc_status); ?></span></a></li> 
	</ul>
	<?php
}



$ced_walmart_configuration_details = get_option( 'ced_walmart_configuration_details', array() );
$client_id                         = isset( $ced_walmart_configuration_details['client_id'] ) ? $ced_walmart_configuration_details['client_id'] : '';
$client_secret                     = isset( $ced_walmart_configuration_details['client_secret'] ) ? $ced_walmart_configuration_details['client_secret'] : '';
$environment                       = isset( $ced_walmart_configuration_details['environment'] ) ? $ced_walmart_configuration_details['environment'] : '';
$wfs                               = isset( $ced_walmart_configuration_details['wfs'] ) ? $ced_walmart_configuration_details['wfs'] : '';
?>

<div class="ced_walmart_section_wrapper">
	<div class="ced_walmart_heading">
		<?php echo esc_html_e( get_instuctions_html() ); ?>
		<div class="ced_walmart_child_element default_modal">
			<ul type="disc">
				<li><?php print( 'Enter the API keys ( Client Id & Client Secret )' ); ?></li>				
				<li><?php echo esc_html_e( 'Choose the integration environment.' ); ?></li>
				<li><?php echo esc_html_e( 'Save the details by clicking update and then validate the API keys.' ); ?></li>
			</ul>
		</div>
	</div>
	



</div>

<div>


	<table class="wp-list-table widefat fixed ced_walmart_table">
		<tbody>

			
			<tr>
				<td>
					<label><?php echo esc_html_e( 'Client ID', 'walmart-woocommerce-integration' ); ?></label>
					<?php ced_walmart_tool_tip( 'Enter the Client ID obtained from Walmart developer portal' ); ?>
				</td>
				<td><input type="text" name="" id="ced_walmart_client_id" class="ced_walmart_required_data" value="<?php echo esc_attr( $client_id ); ?>"></td>
			</tr>
			<tr>
				<td>
					<label><?php echo esc_html_e( 'Client Secret', 'walmart-woocommerce-integration' ); ?></label>
					<?php ced_walmart_tool_tip( 'Enter the Client Secret obtained from Walmart developer portal' ); ?>
				</td>
				<td><input type="text" name="" id="ced_walmart_client_secret" class="ced_walmart_required_data" value="<?php echo esc_attr( $client_secret ); ?>"></td>
			</tr>
			<tr>
				<td>
					<label><?php echo esc_html_e( 'Environment', 'walmart-woocommerce-integration' ); ?></label>
					<?php ced_walmart_tool_tip( 'Choose the integration mode. Choose Production if you want to manage products and orders on live Walmart account and Sandbox if you want to manage products and orders in test mode .' ); ?>
				</td>
				<td id="ced_walmart_environment_select"><select type="text" name="" id="ced_walmart_environment" class="ced_walmart_required_data">
					<option value="production" <?php echo ( 'production' == $environment ) ? 'selected=selected' : ''; ?>><?php esc_html_e( 'Production', 'walmart-woocommerce-integration' ); ?></option>
					<option value="sandbox" <?php echo ( 'sandbox' == $environment ) ? 'selected=selected' : ''; ?>><?php esc_html_e( 'Sandbox', 'walmart-woocommerce-integration' ); ?></option>
				</select></td>
			</tr>
			<tr>
				<td>
					<label><?php print_r( 'Use Walmart Fulfillment Service [ WFS ]' ); ?></label>
					<?php ced_walmart_tool_tip( 'You can enable this if you are approved for WFS program' ); ?>
				</td>
				<td><input type="checkbox" name="" id="ced_walmart_wfs" class="ced_walmart_required_data" <?php echo ( ( 'on' == $wfs ) ? 'checked' : '' ); ?>></td>
			</tr>
		</tbody>

	</table>
</div>
<div class="walmart-button-wrap">
	<input type="button" class="button-primary" id="ced_walmart_update_api_keys" name="" value="Update">
	<?php $details_updated = get_option( 'ced_walmart_configuration_details_saved', false ); ?>
	<?php
	if ( $details_updated ) {
		echo '<input type="button" class="button-primary" id="ced_walmart_validate_api_keys" name="" value="Validate">';
	}
	?>
</div>
</div>
