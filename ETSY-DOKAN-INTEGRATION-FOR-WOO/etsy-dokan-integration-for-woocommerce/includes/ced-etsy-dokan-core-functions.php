<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function ced_etsy_dokan_write_logs( $filename, $stringTowrite ) {
	$dirTowriteFile = CED_ETSY_LOG_DIRECTORY;
	if ( defined( 'CED_ETSY_LOG_DIRECTORY' ) ) {
		if ( ! is_dir( $dirTowriteFile ) ) {
			if ( ! mkdir( $dirTowriteFile, 0755 ) ) {
				return;
			}
		}
		$fileTowrite = $dirTowriteFile . "/$filename";
		$fp          = fopen( $fileTowrite, 'a' );
		if ( ! $fp ) {
			return;
		}
		$fr = fwrite( $fp, $stringTowrite . "\n" );
		fclose( $fp );
	} else {
		return;
	}
}

function checkLicenseValidationEtsyDokan() {
	return true;
	$etsy_license        = get_option( 'ced_etsy_license', false );
	$etsy_license_key    = get_option( 'ced_etsy_license_key', false );
	$etsy_license_module = get_option( 'ced_etsy_license_module', false );
	$license_valid       = apply_filters( 'ced_etsy_license_check', false );

	if ( $license_valid ) {
		return true;
	} else {
		return false;
	}
}



function ced_etsy_dokan_inactive_shops( $de_shop_name = '' ) {

	$shops = get_option( 'ced_etsy_dokan_details', '' );
	if ( isset( $shops[get_current_user_id()][ $de_shop_name ]['details']['ced_shop_account_status'] ) && 'InActive' == $shops[get_current_user_id()][ $de_shop_name ]['details']['ced_shop_account_status'] ) {
		return true;
	}
}

function ced_etsy_dokan_request() {
	$req_file = CED_ETSY_DOKAN_DIRPATH . 'public/etsy-dokan/lib/class-ced-etsy-request.php';
	if ( file_exists( $req_file ) ) {
		require $req_file;
		return new Ced_Etsy_Request();
	}
	return false;

}

function ced_etsy_dokan_get_shop_id( $shop_name, $vendor_id='' ) {
	if (str_contains('/', $shop_name)) {
		$shop_name = str_replace('/', '', $shop_name );
	}
	if (empty( $vendor_id )) {
		$vendor_id = get_current_user_id();
	}
	$user_details = get_option( 'ced_etsy_dokan_details', array() );
	$shop_id      = isset( $user_details[$vendor_id][$shop_name]['details']['shop_id'] ) ? $user_details[$vendor_id][$shop_name]['details']['shop_id'] : '';
	return ! empty( $shop_id ) ? $shop_id : '';
}

function ced_ets_dokan_get_active_de_shop_name() {
	$saved_etsy_details = get_option( 'ced_etsy_dokan_details', array() );
	$shopName           = isset( $saved_etsy_details['details']['ced_etsy_dokan_de_shop_name'] ) ? $saved_etsy_details['details']['ced_etsy_dokan_de_shop_name'] : '';
	return $shopName;
}

function ced_etsy_dokan_tool_tip( $tip = '' ) {
	echo wc_help_tip( __( $tip, 'woocommerce-etsy-integration' ) );
}

/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function ced_etsy_dokan_render_html( $meta_keys_to_be_displayed = array(), $added_meta_keys = array() ) {
	$html  = '';
	$html .= '<table class="wp-list-table widefat fixed striped ced_etsy_config_table">';

	if ( isset( $meta_keys_to_be_displayed ) && is_array( $meta_keys_to_be_displayed ) && ! empty( $meta_keys_to_be_displayed ) ) {
		$total_items  = count( $meta_keys_to_be_displayed );
		$pages        = ceil( $total_items / 10 );
		$current_page = 1;
		$counter      = 0;
		$break_point  = 1;

		foreach ( $meta_keys_to_be_displayed as $meta_key => $meta_data ) {
			$display = 'display : none';
			if ( 0 == $counter ) {
				if ( 1 == $break_point ) {
					$display = 'display : contents';
				}
				$html .= '<tbody style="' . esc_attr( $display ) . '" class="ced_etsy_metakey_list_' . $break_point . '  			ced_etsy_metakey_body">';
				$html .= '<tr><td colspan="3"><label>CHECK THE METAKEYS OR ATTRIBUTES</label></td>';
				$html .= '<td class="ced_etsy_pagination"><span>' . $total_items . ' items</span>';
				$html .= '<button class="button ced_etsy_dokan_navigation" data-page="1" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><<</b></button>';
				$html .= '<button class="button ced_etsy_dokan_navigation" data-page="' . esc_attr( $break_point - 1 ) . '" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><</b></button><span>' . $break_point . ' of ' . $pages;
				$html .= '</span><button class="button ced_etsy_dokan_navigation" data-page="' . esc_attr( $break_point + 1 ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>></b></button>';
				$html .= '<button class="button ced_etsy_dokan_navigation" data-page="' . esc_attr( $pages ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>>></b></button>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr><td><label>Select</label></td><td><label>Metakey / Attributes</label></td><td colspan="2"><label>Value</label></td>';

			}
			$checked    = ( in_array( $meta_key, $added_meta_keys ) ) ? 'checked=checked' : '';
			$html      .= '<tr>';
			$html      .= "<td><input type='checkbox' class='ced_etsy_dokan_meta_key' value='" . esc_attr( $meta_key ) . "' " . $checked . '></input></td>';
			$html      .= '<td>' . esc_attr( $meta_key ) . '</td>';
			$meta_value = ! empty( $meta_data[0] ) ? $meta_data[0] : '';
			$html      .= '<td colspan="2">' . esc_attr( $meta_value ) . '</td>';
			$html      .= '</tr>';
			++$counter;
			if ( 10 == $counter || $break_point == $pages ) {
				$counter = 0;
				++$break_point;
				// $html .= '<tr><td colsapn="4"><a href="" class="ced_etsy_custom_button button button-primary">Save</a></td></tr>';
				$html .= '</tbody>';
			}
		}
	} else {
		$html .= '<tr><td colspan="4" class="etsy-error">No data found. Please search the metakeys.</td></tr>';
	}
	$html .= '</table>';
	return $html;
}


function ced_etsy_dokan_get_shipping_profiles( $shop_name, $vendor_id  ){
	if (str_contains('/', $shop_name)) {
		$shop_name = str_replace('/', '', $shop_name );
	}
	do_action( 'ced_etsy_dokan_refresh_token', $shop_name, $vendor_id );
	$shop_id            = ced_etsy_dokan_get_shop_id( $shop_name, $vendor_id );
	$shipping_templates = array();
	if ($shop_id) {
		$e_shpng_tmplts     = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, "application/shops/{$shop_id}/shipping-profiles", 'GET', array(), array(), $vendor_id );
		if ( isset( $e_shpng_tmplts['count'] ) && $e_shpng_tmplts['count'] >= 1 ) {
			foreach ( $e_shpng_tmplts['results'] as $key => $value ) {
				$shipping_templates[ $value['shipping_profile_id'] ] = $value['title'];
			}
		}
	}
	return $shipping_templates;
}
/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function ced_etsy_dokan_get_etsy_instuctions_html( $label = 'Instructions' ) {
	?>
	<div class="ced_etsy_dokan_parent_element">
		<h2>
			<label><?php echo esc_html_e( $label, 'etsy-woocommerce-integration' ); ?></label>
			<span class="dashicons dashicons-arrow-down-alt2 ced_etsy_instruction_icon"></span>
		</h2>
	</div>
	<?php
}

/**
 * *********************************************
 * Get Product id by listing id and Shop Name
 * *********************************************
 *
 * @since 1.0.0
 */
function ced_etsy_dokan_get_product_id_by_shopname_and_listing_id( $de_shop_name = '', $listing = '' ) {

	if ( empty( $de_shop_name ) || empty( $listing ) ) {
		return;
	}
	$if_exists  = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'product',
			'meta_query'  => array(
				array(
					'key'     => '_ced_etsy_listing_id_' . $de_shop_name,
					'value'   => $listing,
					'compare' => '=',
				),
			),
			'fields'      => 'ids',
		)
	);
	$product_id = isset( $if_exists[0] ) ? $if_exists[0] : '';
	return $product_id;
}


function get_etsy_de_shop_name($vendor_id = '') {
	if (empty( $vendor_id)) {
		$vendor_id = get_current_user_id();
	}
	$de_shop_name = isset( $_GET['de_shop_name'] ) ? $_GET['de_shop_name'] : '';
	if (empty( $de_shop_name ) ) {
		$de_shop_name = get_option( 'ced_etsy_dokan_de_shop_name_'. $vendor_id, '' );
	}
	if ( empty( $de_shop_name ) ) {
		$etsy_users_details = get_option('ced_etsy_dokan_details' , array() );
		$de_shop_name   = isset($etsy_users_details[$vendor_id][$de_shop_name]['details']['ced_etsy_dokan_de_shop_name']) ? $etsy_users_details[$vendor_id][$de_shop_name]['details']['ced_etsy_dokan_de_shop_name'] : '';
	}
	return $de_shop_name;

}

function check_if_etsy_vendor() {
	$current_user = wp_get_current_user();
	$vendor_id    = '';
	if ( in_array( 'seller', (array) $current_user->roles ) ) {
		$vendor_id = $current_user->ID;
	}
	if ( empty( $vendor_id ) ) {
		return false;
	} else {
		return true;
	}
}

function ced_etsy_filter( $shop_name ){
	if (str_contains('/', $shop_name)) {
		$shop_name = str_replace('/', '', $shop_name );
	}
	return $shop_name;
}