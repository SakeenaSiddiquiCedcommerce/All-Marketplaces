<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once CED_ETSY_DOKAN_DIRPATH . 'public/partials/header.php';
$shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';
$countries = file_get_contents( CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/json/countries.json' );
if ( '' != $countries ) {
	$countries = json_decode( $countries, true );
}

$regions = file_get_contents( CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/json/regions.json' );
if ( '' != $regions ) {
	$regions = json_decode( $regions, true );
}

$country_list = array();
if ( ! empty( $countries ) ) {
	foreach ( $countries['results'] as $key => $value ) {
		$country_list[ $value['country_id'] ] = $value['name'];
	}
}
$region_list = array();
if ( ! empty( $regions ) ) {
	foreach ( $regions['results'] as $key => $value ) {
		$region_list[ $value['region_id'] ] = $value['region_name'];
	}
}

if ( isset( $_POST['shipping_settings'] ) ) {
	if ( ! isset( $_POST['shipping_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shipping_settings_submit'] ) ), 'shipping_settings' ) ) {
		return;
	}
	$shipping_title      = isset( $_POST['ced_etsy_shipping_title'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_title'] ) ) : '';
	$country_id          = isset( $_POST['ced_etsy_shipping_country_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_country_id'] ) ) : '';
	$destination_id      = isset( $_POST['ced_etsy_shipping_destination_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_destination_id'] ) ) : '';
	$primary_cost        = isset( $_POST['ced_etsy_shipping_primary_cost'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_primary_cost'] ) ) : '';
	$secondary_cost      = isset( $_POST['ced_etsy_shipping_secondary_cost'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_secondary_cost'] ) ) : '';
	$region_id           = isset( $_POST['ced_etsy_shipping_region_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_region_id'] ) ) : '';
	$min_process_time    = isset( $_POST['ced_etsy_shipping_min_process_time'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_min_process_time'] ) ) : '';
	$max_process_time    = isset( $_POST['ced_etsy_shipping_max_process_time'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_max_process_time'] ) ) : '';
	$origin_postal_code  = isset( $_POST['ced_etsy_origin_postal_code'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_origin_postal_code'] ) ) : '';
	$shipping_carrier_id = ! empty( $_POST['ced_etsy_shipping_carrier_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_shipping_carrier_id'] ) ) : '';
	$mail_class          = ! empty( $_POST['ced_etsy_mail_class'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_mail_class'] ) ) : '';
	$min_delivery_time   = isset( $_POST['ced_etsy_min_delivery_time'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_min_delivery_time'] ) ) : '';
	$max_delivery_time   = isset( $_POST['ced_etsy_max_delivery_time'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_max_delivery_time'] ) ) : '';

	$found_require_attr_error = '';
	if ( $primary_cost < $secondary_cost ) {
		$found_require_attr_error = 'Secondary cost can not be greater than primary cost';
	} elseif ( $min_delivery_time > $max_delivery_time ) {
		$found_require_attr_error = 'Minimum delivery time can not be greater than maximun delivery time';
	} else {
		if ( ! empty( $shipping_title ) && ! empty( $country_id ) ) {
			$params = array(
				'title'                   => "$shipping_title",
				'origin_country_iso'      => (string) $country_id,
				'primary_cost'            => (float) $primary_cost,
				'secondary_cost'          => (float) $secondary_cost,
				'destination_country_iso' => (string) $destination_id,
				'destination_region'      => (string) $region_id,
				'min_processing_time'     => (int) $min_process_time,
				'max_processing_time'     => (int) $max_process_time,
			);

			if ( ! empty( $origin_postal_code ) ) {
				$params['origin_postal_code'] = (string) $origin_postal_code;
			}

			if ( ! empty( $min_delivery_time ) ) {
				$params['min_delivery_days'] = (int) $min_delivery_time;

			}

			if ( ! empty( $max_delivery_time ) ) {
				$params['max_delivery_days'] = (int) $max_delivery_time;

			}
			/** Refresh token
			 *
			 * @since 2.0.0
			 */
			$vendor_id = get_current_user_id();
			$shop_id   = ced_etsy_dokan_get_shop_id( $shop_name, $vendor_id );
			die( 'Shop ID :-' . $shop_id );
			do_action( 'ced_etsy_dokan_refresh_token', $shop_name, $vendor_id );
			$_action  = "application/shops/{$shop_id}/shipping-profiles";
			die($_action);
			$response = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, $_action, 'POST', array(), $params , $vendor_id );
			echo "<pre>";
			print_r( $response );
			if ( isset( $response['shipping_profile_id'] ) ) {
				echo '<div class="notice notice-success" ><p>' . esc_html( __( 'Shipping Template Created', 'woocommerce-etsy-integration' ) ) . '</p></div>';
			} else {
				$_error = isset( $response['error'] ) ? ucfirst( str_replace( '_', ' ', $response['error'] ) ) : 'Shipping profile not created';
				echo '<div class="notice notice-error" ><p>' . esc_html( __( $_error ) ) . '</p></div>';
			}
		} else {
			echo '<div class="notice notice-error" ><p>' . esc_html( __( 'Required Fields Missing', 'woocommerce-etsy-integration' ) ) . '</p></div>';
		}
	}
	if ( ! empty( $found_require_attr_error ) ) {
		echo '<div class="notice notice-error" ><p>' . esc_html( __( $found_require_attr_error, 'woocommerce-etsy-integration' ) ) . '</p></div>';
	}
}
?>
<div class="ced_etsy_wrap">
	<div class="ced_etsy_account_configuration_wrapper">	
		<div class="ced_etsy_account_configuration_fields">	
			<form method="post" action="">
				<?php wp_nonce_field( 'shipping_settings', 'shipping_settings_submit' ); ?>
				<table class="wp-list-table widefat ced_etsy_account_configuration_fields_table">
					<thead>
						<tr><th colspan="2">Enter details for the Shipping Template</th></tr>
					</thead>
					<tbody>
						<tr>
							<th>
								<label><?php esc_html_e( 'Title', 'woocommerce-etsy-integration' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Shipping Title', 'woocommerce-etsy-integration' ); ?>" class="short" type="text" name="ced_etsy_shipping_title" required></input>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Origin Country', 'woocommerce-etsy-integration' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<select name="ced_etsy_shipping_country_id" class="select short ced_etsy_shipping_country_id" required>
									<option value="0"><?php esc_html_e( '--Select--', 'woocommerce-etsy-integration' ); ?></option>
									<?php
									foreach ( $country_list as $key => $value ) {
										?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?></option>
										<?php
									}
									?>
								</select>
							</td>
						</tr>
						<tr class="">
							<th>
								<label><?php esc_html_e( 'Origin Postal Code', 'product-lister-etsy' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter origin postal code', 'product-lister-etsy' ); ?>" class="short" type="text" name="ced_etsy_origin_postal_code" required></input>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Minimum Delivery Time', 'product-lister-etsy' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Min Delivery Days', 'product-lister-etsy' ); ?>" class="short" type="text" name="ced_etsy_min_delivery_time" required></input>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Maximum Delivery Time', 'product-lister-etsy' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Max Delivery Days', 'product-lister-etsy' ); ?>" class="short" type="text" name="ced_etsy_max_delivery_time" required></input>
							</td>
						</tr>

						<tr>
							<th>
								<label><?php esc_html_e( 'Destination Country', 'woocommerce-etsy-integration' ); ?></label>
							</th>
							<td>
								<select name="ced_etsy_shipping_destination_id" class="select short">
									<option value="0"><?php esc_html_e( '--Select--', 'woocommerce-etsy-integration' ); ?></option>
									<?php
									foreach ( $country_list as $key => $value ) {
										?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?></option>
										<?php
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Primary Cost', 'woocommerce-etsy-integration' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Primary Cost', 'woocommerce-etsy-integration' ); ?>" class="short" type="text" name="ced_etsy_shipping_primary_cost" required></input>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Secondary Cost', 'woocommerce-etsy-integration' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Secondary Cost', 'woocommerce-etsy-integration' ); ?>" class="short" type="text" name="ced_etsy_shipping_secondary_cost" required></input>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Destination Region', 'woocommerce-etsy-integration' ); ?></label>
							</th>
							<td>
								<select name="ced_etsy_shipping_region_id" class="select short">
									<option value="0"><?php esc_html_e( '--Select--', 'woocommerce-etsy-integration' ); ?></option>
									<?php
									foreach ( $region_list as $key => $value ) {
										?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?></option>
										<?php
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Minimum Processing Days', 'woocommerce-etsy-integration' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Min Processing Days', 'woocommerce-etsy-integration' ); ?>" class="short" type="text" name="ced_etsy_shipping_min_process_time" required></input>
							</td>
						</tr>
						<tr>
							<th>
								<label><?php esc_html_e( 'Maximum Processing Days', 'woocommerce-etsy-integration' ); ?></label><span style="color: red; margin-left:5px; ">*</span>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'Enter Max Processing Days', 'woocommerce-etsy-integration' ); ?>" class="short" type="text" name="ced_etsy_shipping_max_process_time" required></input>
							</td>
						</tr>

					</tbody>
				</table>
				<div align="" class="ced-button-wrapper">
					<button id="save_shipping_settings"  name="shipping_settings" class="button-primary"><?php esc_html_e( 'Create new', 'woocommerce-etsy-integration' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
