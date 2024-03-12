<?php
/**
 * Configuration
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$save_details         = get_option( 'api_details' );
$save_details_display = json_decode( $save_details, 1 );
$email_val            = isset( $save_details_display['variables']['email'] ) ? esc_attr( $save_details_display['variables']['email'] ) : '';
$password_val         = isset( $save_details_display['variables']['password'] ) ? esc_attr( $save_details_display['variables']['password'] ) : '';
$vendor_id            = isset( $save_details_display['variables']['vendorId'] ) ? esc_attr( $save_details_display['variables']['vendorId'] ) : '';
get_good_market_header();
?>
<div class="ced_good_market_section_wrapper">
	<div class="ced_good_market_heading">
		<div class="ced_good_market_parent_element">
			<h2>
				<label><?php echo esc_html_e( 'Welcome', 'Good_Market_Woocommerce' ); ?></label>
				<span class="dashicons dashicons-arrow-down-alt2 ced_good_market_instruction_icon"></span>
			</h2>
		</div>
		<div class="ced_good_market_child_element default_modal">
			<ul type="disc">
				<li>Good Market is a curated community platform for social enterprises and other initiatives that prioritize people and planet over profit maximization. All participants go through an online application and review process to ensure they meet the minimum sector standards. If you are not Good Market Approved, you can <a target="_blank" href="https://www.goodmarket.global/apply">apply here</a>.
</li>
			</ul>
		</div>
	</div>
	<div>
		<table class="wp-list-table widefat fixed ced_good_market_table">
			<tbody>
				<tr>
					<td>
						<label><?php echo esc_html_e( 'Good Market Vendor ID', 'good_market-woocommerce-integration' ); ?></label>
						<?php // ced_good_market_tool_tip( 'Enter the Client ID obtained from Good Market developer portal' ); ?>
					</td>
					<td><input type="text" name="" id="vendor_id" class="ced_good_market_required_data" value="<?php echo esc_html( $vendor_id ); ?>"></td>
				</tr>
				<tr>
					<td>
						<label><?php echo esc_html_e( 'Good Market Email Login', 'good_market-woocommerce-integration' ); ?></label>
						<?php // ced_good_market_tool_tip( 'Enter the Client ID obtained from Good Market developer portal' ); ?>
					</td>
					<td><input type="email" name="" id="ced_good_market_client_id" class="ced_good_market_required_data" value="<?php echo esc_html( $email_val ); ?>"></td>
				</tr>
					<tr>
						<td></td>
						<td><div class="msg_dis"></div></td>
					</tr>
			</tbody>
			
		</table>
	</div>
	<div class="good_market-button-wrap">
		<input type="button" class="button-primary" id="ced_good_market_update_api_keys" name="" value="Fetch Token">
		<?php $details_updated = get_option( 'ced_good_market_configuration_details_saved', false ); ?>
		<?php
		if ( $details_updated ) {
			echo '<input type="button" class="button-primary" id="ced_good_market_validate_api_keys" name="" value="Validate">';
		}
		?>
	</div>
</div>

<?php
$good_market_data = get_option( 'good_market_data' );
if ( isset( $good_market_data['data'] ) && ! empty( $good_market_data['data'] ) ) {
	?>
	<div class="ced_good_market_heading">
		<?php echo esc_html( get_instuction_html( 'Syncing' ) ); ?>
		<div class="ced_good_market_child_element default_modal">
			<ul type="disc">
				<li><?php echo esc_html_e( 'Enable or disable automatic syncing between your WooCommerce store and Good Market.' ); ?></li>
			</ul>
		</div>
	</div>

	<?php

	if ( isset( $_POST['global_settings_submit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		$post_array                      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$auto_fetch_orders_and_inventory = isset( $post_array['ced_good_market_auto_fetch_orders_and_inventory'] ) ? $post_array['ced_good_market_auto_fetch_orders_and_inventory'] : '';

		if ( 'on' == $auto_fetch_orders_and_inventory ) {
			wp_clear_scheduled_hook( 'ced_good_market_auto_fetch_orders' );
			update_option( 'ced_good_market_auto_fetch_orders', $auto_fetch_orders_and_inventory );
			wp_schedule_event( time(), 'ced_good_market_15min', 'ced_good_market_auto_fetch_orders' );

			wp_clear_scheduled_hook( 'ced_good_market_auto_inventory_sync' );
			update_option( 'ced_good_market_auto_inventory_sync', $auto_fetch_orders_and_inventory );
			wp_schedule_event( time(), 'ced_good_market_15min', 'ced_good_market_auto_inventory_sync' );
		} else {
			delete_option( 'ced_good_market_auto_fetch_orders' );
			wp_clear_scheduled_hook( 'ced_good_market_auto_fetch_orders' );

			delete_option( 'ced_good_market_auto_inventory_sync' );
			wp_clear_scheduled_hook( 'ced_good_market_auto_inventory_sync' );
		}
	}

	$auto_inv_sync_saved         = get_option( 'ced_good_market_auto_inventory_sync' );
	$auto_fetch_orders_inv_saved = get_option( 'ced_good_market_auto_fetch_orders' );

	?>
	<div>
		<form method="post" action="">
			<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
			<table class="wp-list-table fixed widefat stripped">
				<thead></thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Sync inventory and orders', 'good_market-woocommerce-integration' ); ?></label>						
						</th>
						<td>
							<label class="switch">
								<input type="checkbox" name="ced_good_market_auto_fetch_orders_and_inventory" <?php echo ( 'on' == $auto_fetch_orders_inv_saved ) ? 'checked=checked' : ''; ?>>
								<span class="slider round"></span>
							</label>
						</td>
					</tr>
				</tr>
			</tbody>
		</table>
		<div class="good_market-button-wrap">
			<button type="submit" class="button button-primary" name="ced_good_market_global_sync"><?php esc_html_e( 'Save', 'good_market-woocommerce-integration' ); ?></button>
		</div>
	</form>
</div>
<div class="ced_good_market_heading">
	<?php echo esc_html( get_instuction_html( 'Currency Conversion' ) ); ?>
	<div class="ced_good_market_child_element default_modal">
		<ul type="disc">
			<li><?php echo esc_html_e( 'On the Good Market community platform, buyers can choose to see prices in their own currency, but the default currency is currently US Dollars. If your WooCommerce store is in a different currency, enter an exchange rate to convert to US Dollars so your prices appear correctly.' ); ?></li>
			<li><?php echo esc_html_e( 'If your store is in Indian Rupees and 1 INR = 0.012 USD, enter 0.012 below.' ); ?></li>
			<li><?php echo esc_html_e( 'If your store is in Euro and 1 EUR = 0.98, enter 0.98 below.' ); ?></li>
			<li><?php echo esc_html_e( 'If your store is in British Pounds and 1 GBP = 1.12 USD, enter 1.12 below.' ); ?></li>
		</ul>
	</div>
</div>
	<?php
	$ced_goodmarket_currency_convert_rate = get_option( 'ced_goodmarket_currency_convert_rate' );

	if ( isset( $_POST['global_settings_submit_date_convrsion'] ) ) {
		$ced_goodmarket_currency_convert_rate = isset( $_POST['ced_good_market_currency_convert_rate'] ) ? sanitize_text_field( $_POST['ced_good_market_currency_convert_rate'] ) : '';
		update_option( 'ced_goodmarket_currency_convert_rate', $ced_goodmarket_currency_convert_rate );

	}
	?>
<div>
		<form method="post" action="">
			<?php
			wp_nonce_field( 'global_settings', 'global_settings_submit_date_convrsion' );
			$input_field_condition = '';
			if ( get_woocommerce_currency() == 'USD' ) {
				$input_field_condition                = 'disabled';
				$ced_goodmarket_currency_convert_rate = '';
			}

			?>
			<table class="wp-list-table fixed widefat stripped">
				<thead></thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Convert your store currency to USD', 'good_market-woocommerce-integration' ); ?></label>						
						</th>
						<td>
							
								<input type="text" name="ced_good_market_currency_convert_rate" value="<?php echo esc_html( $ced_goodmarket_currency_convert_rate ); ?>" <?php echo esc_html( $input_field_condition ); ?>>
								<?php
								if ( get_woocommerce_currency() == 'USD' ) {
									?>
									<div class="ced_good_market_currency_info">
										Your WooCommerce store currency is <b>USD</b>. No conversion is required.
									</div>
									<?php
								} else {
									?>
								<div class="ced_good_market_currency_info">
									Your WooCommerce store currency is <b><?php echo esc_html( get_woocommerce_currency() ); ?></b> Enter the exchange rate to <b>USD</b>.
								</div>
									<?php
								}
								?>
						</td>
					</tr>
				</tr>
			</tbody>
		</table>
		<div class="good_market-button-wrap">
			<button type="submit" class="button button-primary" name="ced_good_market_save_currency_convert_rate"><?php esc_html_e( 'Save', 'good_market-woocommerce-integration' ); ?></button>
		</div>
	</form>
</div>


	<?php
}
