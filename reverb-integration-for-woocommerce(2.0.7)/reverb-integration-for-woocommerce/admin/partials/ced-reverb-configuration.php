<?php
/**
 * Configuration
 *
 * @package  reverb_Integration_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_reverb_header();

$ced_reverb_configuration_details = get_option( 'ced_reverb_configuration_details', array() );
$client_id                        = isset( $ced_reverb_configuration_details['client_id'] ) ? $ced_reverb_configuration_details['client_id'] : '';
$environment                      = isset( $ced_reverb_configuration_details['environment'] ) ? $ced_reverb_configuration_details['environment'] : '';
?>

	<div class="ced_reverb_heading">
		<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
		<div class="ced_reverb_child_element">
			<ul type="disc">
				<li><?php echo esc_html_e( 'Enter the personal access token.' ); ?></li>
				<li><?php echo esc_html_e( 'If you have already generated the personal access token then you can copy it and paste in the input provided below.' ); ?></li>
				<li><?php echo esc_html_e( 'If you do not have have the personal access token then you can generate one from ' ); ?><a href="<?php echo esc_url( 'https://reverb.com/my/integration_tokens/new' ); ?> target="_blank">here.</a></li>
				<li><?php echo esc_html_e( 'Enter the token name of your choice and select all the scopes and hit generate new button at the bottom.' ); ?></li>
				<li><?php echo esc_html_e( 'Authorize the token.' ); ?></li>
			</ul>
		</div>
	</div>
<div class="ced_reverb_section_wrapper">
	<div>
		<table class="wp-list-table widefat ced_reverb_table">
			<tbody>
				<tr>
					<td>
						<label><?php echo esc_html_e( 'Personal Access Token', 'reverb-woocommerce-integration' ); ?></label></br>
						<span><i>[ <a style="text-decoration: underline;" href="https://reverb.com/my/api_settings" target="_blank"> Get or generate access token </a> ]</i></span>
					</td>
					<td><input type="text" name="" id="ced_reverb_client_id" class="ced_reverb_required_data" value="<?php echo esc_attr( $client_id ); ?>"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td></td>
					<td>
						<input type="button" class="button button-primary" id="ced_reverb_update_api_keys" name="" value="Authorize">
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<?php
?>
