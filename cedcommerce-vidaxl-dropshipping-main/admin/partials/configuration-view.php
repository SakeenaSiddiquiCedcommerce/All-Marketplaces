<?php
/**
 * Authorization/ configuration
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
$general_functions = CED_VIDAXL_DROPSHIPPING_DIRPATH . 'includes/general-functions.php';
if ( file_exists( $general_functions ) ) {
	include_once $general_functions;
}
if ( isset( $_POST['ced_vidaxl_save_authorization'] ) ) {

	if ( ! isset( $_POST['ced_vidaxl_authorization_data_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ced_vidaxl_authorization_data_submit'] ) ), 'ced_vidaxl_auth_settings' ) ){
		return;
	}
	
	$sanitized_array	= filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$auth_data        	= array();
	$auth_data          = get_option( 'ced_vidaxl_authorization_data', array() );
	$auth_data			= isset( $sanitized_array['ced_vidaxl_authorization_data'] ) ? ( $sanitized_array['ced_vidaxl_authorization_data'] ) : array();
	update_option( 'ced_vidaxl_authorization_data', $auth_data );

	echo '<div class="notice notice-success settings-error is-dismissible" ><p>' . esc_html( __( 'Details Saved Successfully', 'cedcommerce-vidaxl-dropshipping' ) ) . '</p><button type="button" class="notice-dismiss"></button></div>';
}
$render_authorization_data = get_option( 'ced_vidaxl_authorization_data', false );
?>
<div class="ced-vidaxl-container">
	<h2><?php esc_html_e( 'Authorization Details [Production/Sandbox]', 'cedcommerce-vidaxl-dropshipping' ); ?></h2>
	<form method="post" action="">
	<?php wp_nonce_field( 'ced_vidaxl_auth_settings', 'ced_vidaxl_authorization_data_submit' ); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<?php
						$order_region = isset( $render_authorization_data['ced_vidaxl_order_region'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_order_region'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_order_region"><?php esc_html_e( 'Order Region', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>
						<?php
						$countries_list = get_countries();						
						?>
						<select id="ced_vidaxl_order_region" name="ced_vidaxl_authorization_data[ced_vidaxl_order_region]" class="ced-vidaxl-form-elements">
						<?php
							if( isset( $countries_list ) && !empty( $countries_list ) ){
								foreach($countries_list as $code => $country){
									if( $code == $order_region ){
										$selected = 'selected';
									}else{
										$selected = '';
									}
									echo '<option value="'.$code.'" '.$selected.' >' .$country. '</option>';
								}		
							}						
						?>	
						</select>
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Select Country which you want to allow for ordering the product.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<?php
						$email = isset( $render_authorization_data['ced_vidaxl_email'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_email'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_email"><?php esc_html_e( 'Email', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>	
						<input type="email" name="ced_vidaxl_authorization_data[ced_vidaxl_email]" id="ced_vidaxl_email" class="ced-vidaxl-form-elements" value="<?php echo esc_attr( $email ); ?>">
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Enter email which is linked with your vidaXL account.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>

				<tr>
					<?php
						$api_token = isset( $render_authorization_data['ced_vidaxl_api_token'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_api_token'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_api_token"><?php esc_html_e( 'API Token', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>	
						<input type="text" name="ced_vidaxl_authorization_data[ced_vidaxl_api_token]" id="ced_vidaxl_api_token" class="ced-vidaxl-form-elements" value="<?php echo esc_attr( $api_token ); ?>">
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Enter API token from vidaXL account. (Click Here) to know how to get API Token', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>

				<tr>
					<?php
						$test_mode = isset( $render_authorization_data['ced_vidaxl_enable_test_mode'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_enable_test_mode'] ) : '';
						if ( 'on' == $test_mode ) {
							$test_mode = 'checked';
							$style   = 'display: contents';
						} else {
							$test_mode = '';
							$style   = 'display: none';
						}
					?>
					<th>
						<label for="ced_vidaxl_enable_test_mode"><?php esc_html_e( 'Enable Test Mode', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>
						<label class="ced-vidaxl-checkbox">
							<input type="checkbox" id="ced_vidaxl_enable_test_mode" name="ced_vidaxl_authorization_data[ced_vidaxl_enable_test_mode]" <?php echo esc_attr( $test_mode ); ?> >
							<span class="ced-vidaxl-slide"></span>
						</label>
						<p class=" ced-vidaxl-form-desc "><?php esc_html_e( 'Click to Enable Test Mode.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<?php
						$sandbox_email = isset( $render_authorization_data['ced_vidaxl_sandbox_email'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_sandbox_email'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_sandbox_email"><?php esc_html_e( 'SandBox Email', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>	
						<input type="email" name="ced_vidaxl_authorization_data[ced_vidaxl_sandbox_email]" id="ced_vidaxl_sandbox_email" class="ced-vidaxl-form-elements" value="<?php echo esc_attr( $sandbox_email ); ?>">
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Enter email which is linked with your vidaXL account.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>

				<tr>
					<?php
						$sandbox_api_token = isset( $render_authorization_data['ced_vidaxl_sandbox_api_token'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_sandbox_api_token'] ) : '';
					?>
					<th>
						<label for="ced_vidaxl_sandbox_api_token"><?php esc_html_e( 'SandBox API Token', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
					</th>
					<td>	
						<input type="text" name="ced_vidaxl_authorization_data[ced_vidaxl_sandbox_api_token]" id="ced_vidaxl_sandbox_api_token" class="ced-vidaxl-form-elements" value="<?php echo esc_attr( $sandbox_api_token ); ?>">
						<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Enter API token from vidaXL account. (Click Here) to know how to get API Token', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						
					</th>
					<td>	
					<button name ="ced_vidaxl_save_authorization" class="ced-vidaxl-button"><span>Save</span></button>
					</td>
				</tr>                          
			</tbody>
		</table>
	</form>
</div>