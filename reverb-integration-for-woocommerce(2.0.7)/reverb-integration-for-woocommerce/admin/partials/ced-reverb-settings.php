<?php
/**
 * Global Order fields
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
if ( isset( $_POST['ced_reverb_global_settings'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}
	$post_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	if ( isset( $post_array['ced_reverb_auto_fetch_orders'] ) && 'on' == $post_array['ced_reverb_auto_fetch_orders'] ) {
		update_option( 'ced_reverb_auto_fetch_orders', 'on' );
	} else {
		delete_option( 'ced_reverb_auto_fetch_orders' );
	}


	if ( isset( $post_array['ced_reverb_auto_update_inventory'] ) && 'on' == $post_array['ced_reverb_auto_update_inventory'] ) {
		update_option( 'ced_reverb_auto_update_inventory', 'on' );
	} else {
		delete_option( 'ced_reverb_auto_update_inventory' );
	}

	if ( isset( $post_array['ced_reverb_auto_upload_product'] ) && 'on' == $post_array['ced_reverb_auto_upload_product'] ) {
		update_option( 'ced_reverb_auto_upload_product', 'on' );
	} else {
		delete_option( 'ced_reverb_auto_upload_product' );
	}

	if ( isset( $post_array['ced_reverb_auto_update_tracking'] ) && 'on' == $post_array['ced_reverb_auto_update_tracking'] ) {
		update_option( 'ced_reverb_auto_update_tracking', 'on' );
	} else {
		delete_option( 'ced_reverb_auto_update_tracking' );
	}



	if ( isset( $post_array['ced_reverb_set_reverbOrderNumber'] ) && 'on' == $post_array['ced_reverb_set_reverbOrderNumber'] ) {
		update_option( 'ced_reverb_set_reverbOrderNumber', 'on' );
	} else {
		delete_option( 'ced_reverb_set_reverbOrderNumber' );
	}

	if ( isset( $post_array['ced_reverb_status'] ) ) {
		$reverb_status = sanitize_text_field( $post_array['ced_reverb_status'] );
		update_option( 'ced_fetch_order_by_reverb_status', $reverb_status );
	}

	$notice = ' <div class="notice notice-success is-dismissible">
				        <p>' . __( 'Setting saved Successfully!', 'sample-text-domain' ) . '</p>
				    </div>';
}
?>
<div class="ced_reverb_heading">
	<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
	<div class="ced_reverb_child_element">
		<ul type="disc">
			<li><?php echo esc_html_e( 'In this section all the configuration related to product and order sync are provided.' ); ?></li>
			<li><?php echo esc_html_e( 'It is mandatory to fill/map the required attributes [ Required ] in Product Export Settings section.' ); ?></li>
			<li><?php echo esc_html_e( 'The Metakeys and Attributes List section will help you to choose the required metakey or attribute on which the product information is stored.These metakeys or attributes will furthur be used in Product Export Settings for listing products on reverb from woocommerce.' ); ?></li>
			<li>
			<?php
			echo esc_html_e(
				'For selecting the required metakey or attribute expand the Metakeys and Attributes List section enter the product name/keywords and list will be displayed under that . Select the metakey or attribute as per requirement and save settings.
			'
			);
			?>
			</li>
			<li><?php echo esc_html_e( 'Configure the order related settings in Order configuration.' ); ?></li>
			<li>
			<?php
			echo esc_html_e(
				'To automate the process related to inventory , order , enable the features as per requirement in Schedulers.
			'
			);
			?>
			</li>
		</ul>
	</div>
</div>
<?php require_once CED_REVERB_DIRPATH . 'admin/pages/ced-reverb-metakeys-template.php'; ?>
<form method="post" action="">
	<?php 
	/**
 	* Action hook for getting product setting data.
 	* @since 1.0.0
 	*/
	do_action( 'ced_reverb_render_product_settings' ); ?>
	<?php 
	/**
 	* Action hook for getting order setting.
 	* @since 1.0.0
 	*/
	do_action( 'ced_reverb_render_order_settings' ); ?>
	<?php 
	/**
 	* Action hook for getting scheduler setting fields.
 	* @since 1.0.0
 	*/
	do_action( 'ced_reverb_render_shedulers_settings' ); ?>
	<div class="ced-button-wrapper">
		<button type="submit" class="button-primary" name="ced_reverb_global_settings"><?php esc_html_e( 'Save Settings', 'reverb-woocommerce-integration' ); ?></button>
	</div>
</form>
