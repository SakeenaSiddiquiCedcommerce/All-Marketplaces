<?php
/**
 * Global Order fields
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
if ( isset( $_POST['ced_walmart_global_orders'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}
	$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

	if ( isset( $post_array['ced_walmart_global_order_config'] ) ) {
		foreach ( $post_array['ced_walmart_global_order_config'] as $option => $option_data ) {
			if ( ! empty( $option_data ) ) {
				update_option( $option, $option_data );
			} else {
				delete_option( $option );
			}
		}
	} else {
		delete_option( 'ced_walmart_auto_acknowledge_orders' );
		delete_option( 'ced_walmart_email_restriction' );
		delete_option( 'ced_walmart_show_walmart_order_number_on_order_page' );
	}

	if ( isset( $_POST['ced_walmart_order_prefix'] ) ) {
		$order_suffix_value = sanitize_text_field( $_POST['ced_walmart_order_prefix'] );
		update_option( 'ced_walmart_order_prefix', $order_suffix_value );
	}
	print_success_notice();
}

$auto_acknowledge          = get_option( 'ced_walmart_auto_acknowledge_orders', '' );
$email_restrict            = get_option( 'ced_walmart_email_restriction', '' );
$show_walmart_order_number = get_option( 'ced_walmart_show_walmart_order_number_on_order_page', '' );
$order_suffix              = get_option( 'ced_walmart_order_prefix', '' );
?>
<div class="ced_walmart_heading">
	<?php echo esc_html_e( get_instuctions_html() ); ?>
	<div class="ced_walmart_child_element default_modal default_modal">
		<ul type="disc">
			<li><?php echo esc_html_e( 'Configure order related settings for Walmart orders.' ); ?></li>
		</ul>
	</div>
</div>
<div>
	<form method="post" action="">
		<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>
		<table class="wp-list-table fixed widefat stripped">
			<thead></thead>
			<tbody>
				<tr>
					<th>
						<label><?php esc_html_e( 'Use walmart order number instead of woocommerce order id.', 'walmart-woocommerce-integration' ); ?>
							
						</label>
						<?php ced_walmart_tool_tip( 'Use walmart order number instead of woocommerce order id while creation of walmart orders in woocommerce' ); ?>
					</th>
					<td>
						<label class="switch">
							<input type="checkbox" name="ced_walmart_global_order_config[ced_walmart_show_walmart_order_number_on_order_page]" <?php echo ( 'on' == $show_walmart_order_number ) ? 'checked=checked' : ''; ?>>
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Walmart Order Prefix', 'walmart-woocommerce-integration' ); ?></label>
						<?php ced_walmart_tool_tip( 'Attach a prefix/string in walmart order id while creation of walmart orders in woocommerce' ); ?>

					</th>
					<td>
					<input type="text" name="ced_walmart_order_prefix" value=<?php echo esc_attr( $order_suffix ); ?> >
						
					</td>
				</tr>
			</tbody>
		</table>
		<div class="walmart-button-wrap">
			<button type="submit" class="button button-primary" name="ced_walmart_global_orders"><?php esc_html_e( 'Save', 'walmart-woocommerce-integration' ); ?></button>
		</div>
	</form>
</div>
