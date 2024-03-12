<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once CED_ETSY_DOKAN_DIRPATH . 'public/partials/header.php';
/**
 ************************************************
 * SAVING VALUE OF THE SELECTED SHIPPING PROFILE
 ************************************************
 */
// var_dump( get_option( 'ced_etsy_dokan_de_shop_name_'. get_current_user_id(), '' ) );
if ( isset( $_POST['global_settings'] ) ) {
	
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}

	$sanitized_array          = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$settings                 = array();
	$settings                 = get_option( 'ced_etsy_dokan_global_settings', array() );
	$ced_etsy_dokan_global_settings = isset( $sanitized_array['ced_etsy_dokan_global_settings'] ) ? $sanitized_array['ced_etsy_dokan_global_settings'] : array();
	wp_clear_scheduled_hook( 'ced_etsy_inventory_scheduler_job_' . $activeShop );
	wp_clear_scheduled_hook( 'ced_etsy_auto_import_schedule_job_' . $activeShop );
	wp_clear_scheduled_hook( 'ced_etsy_order_scheduler_job_' . $activeShop );

	$auto_import_schedule  = isset( $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_import_product'] ) ? $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_import_product'] : '';
	$inventory_schedule    = isset( $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_update_inventory'] ) ? $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_update_inventory'] : '';
	$order_schedule        = isset( $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_fetch_orders'] ) ? $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_fetch_orders'] : '';
	$auto_import_by_status = isset( $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_import_status_info'] ) ? $sanitized_array['ced_etsy_dokan_global_settings']['ced_etsy_auto_import_status_info'] : '';

	if ( ! empty( $auto_import_by_status ) ) {
		update_option( 'ced_etsy_import_by_status_' . $activeShop, $auto_import_by_status );
	}

	if ( ! empty( $auto_import_schedule ) ) {
		wp_schedule_event( time(), 'ced_etsy_30min', 'ced_etsy_auto_import_schedule_job_' . $activeShop );
		update_option( 'ced_etsy_auto_import_schedule_job_' . $activeShop, $activeShop );
	}
	if ( ! empty( $inventory_schedule ) ) {
		wp_schedule_event( time(), 'ced_etsy_10min', 'ced_etsy_inventory_scheduler_job_' . $activeShop );
		update_option( 'ced_etsy_inventory_scheduler_job_' . $activeShop, $activeShop );
	}

	if ( ! empty( $order_schedule ) ) {
		wp_schedule_event( time(), 'ced_etsy_15min', 'ced_etsy_order_scheduler_job_' . $activeShop );
		update_option( 'ced_etsy_order_scheduler_job_' . $activeShop, $activeShop );
	}

	if ( ! isset( $_POST['shipping_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shipping_settings_submit'] ) ), 'saveShippingTemplates' ) ) {
		return;
	}

	$marketplace_name           = isset( $_POST['marketplaceName'] ) ? sanitize_text_field( wp_unslash( $_POST['marketplaceName'] ) ) : 'etsy';
	$offer_settings_information = array();

	$array_to_save = array();
	if ( isset( $_POST['ced_etsy_required_common'] ) ) {
		foreach ( ( $sanitized_array['ced_etsy_required_common'] ) as $key ) {
			isset( $sanitized_array[ $key ][0] ) ? $array_to_save['default'] = $sanitized_array[ $key ][0] : $array_to_save['default'] = '';

			if ( '_umb_' . $marketplace_name . '_subcategory' == $key ) {
				isset( $sanitized_array[ $key ] ) ? $array_to_save['default'] = $sanitized_array[ $key ] : $array_to_save['default'] = '';
			}

			isset( $sanitized_array[ $key . '_attribute_meta' ] ) ? $array_to_save['metakey'] = $sanitized_array[ $key . '_attribute_meta' ] : $array_to_save['metakey'] = 'null';
			$offer_settings_information['product_data'][ $key ]                               = $array_to_save;
		}
	}

	$settings[get_current_user_id()][ $activeShop ] = array_merge( $ced_etsy_dokan_global_settings, $offer_settings_information );
	update_option( 'ced_etsy_dokan_global_settings', $settings );

	/**
	 ************************************************
	 * SAVING VALUE OF THE SELECTED SHIPPING PROFILE
	 ************************************************
	 */
	if ( isset( $_POST['ced_etsy_shipping_details']['ced_etsy_selected_shipping_template'] ) ) {
		$saved_etsy_details                                      = get_option( 'ced_etsy_dokan_details', array() );
		$saved_etsy_details[get_current_user_id()][ $activeShop ]['shippingTemplateId'] = sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_details']['ced_etsy_selected_shipping_template'] ) );
		update_option( 'ced_etsy_dokan_details', $saved_etsy_details );
	}
}
?>

<div class="ced_etsy_heading ">
	<?php echo esc_html_e( ced_etsy_dokan_get_etsy_instuctions_html() ); ?>
	<div class="ced_etsy_child_element dokan-alert-warning">
		<?php
				$activeShop = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( $_GET['de_shop_name'] ) : '';

				$instructions = array(
					'In this section all the configuration related to product and order sync are provided.',
					'It is mandatory to fill/map the required attributes [ <span style="color:red;">*</span> ] in <a>Product Export Settings</a> section.',
					'View the  information of each attribute using the tooltip icon next to the attribute name.',
					'The <a>Metakeys and Attributes List</a> section will help you to choose the required metakey or attribute on which the product information is stored.These metakeys or attributes will furthur be used in <a>Product Export Settings</a> for listing products on etsy from woocommerce.',
					'For selecting the required metakey or attribute expand the <a>Metakeys and Attributes List</a> section enter the product name/keywords and list will be displayed under that . Select the metakey or attribute as per requirement and save settings.',
					'Configure the order related settings in <a>Order Import Settings</a>.',
					'Choose the Shiping profile in <a>Shipping Profiles</a> to be used while listing a product on etsy from woocommerce or you can also add new using <a>Add New</a> button.',
					'To automate the process related to inventory , order and import product sync , enable the features as per requirement in <a>Schedulers</a>.',
				);

				echo '<ul class="ced_etsy_instruction_list" type="disc">';
				foreach ( $instructions as $instruction ) {
					print_r( "<li>$instruction</li>" );
				}
				echo '</ul>';

				?>
	</div>
</div>
<?php do_action( 'ced_etsy_dokan_render_meta_keys_settings' ); ?>
<form method="post" action="">
		<?php
			wp_nonce_field( 'global_settings', 'global_settings_submit' );
			/**
			 ************************************************
			 * ALL DO ACTIONS FOR THE RENDER FILES
			 ************************************************
			 */
			$all_actions_for_render_settings_tab = array(
				'ced_etsy_dokan_render_product_settings',
				'ced_etsy_dokan_render_order_settings',
				'ced_etsy_dokan_render_shipping_profiles',
				'ced_etsy_dokan_render_shedulers_settings',
			);
			foreach ( $all_actions_for_render_settings_tab as $key => $value ) {
				// Add all attributes
				do_action( $value );
			}
			?>
	<div class="left">
		<button id=""  type="submit" name="global_settings" class="ced-dokan-btn" ><?php esc_html_e( 'Save Settings', 'woocommerce-etsy-integration' ); ?></button>
	</div>
</form>
