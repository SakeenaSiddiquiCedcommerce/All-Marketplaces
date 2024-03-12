<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function checkLicenseValidationTokopedia() {

	return true;
	$tokopedia_license        = get_option( 'ced_tokopedia_license', false );
	$tokopedia_license_key    = get_option( 'ced_tokopedia_license_key', false );
	$tokopedia_license_module = get_option( 'ced_tokopedia_license_module', false );
	$license_valid            = apply_filters( 'ced_tokopedia_license_check', false );

	if ( $license_valid ) {
		return true;
	} else {
		return false;
	}
}

function display_tokopedia_support_html() {
	?>

	<div class="ced_contact_menu_wrap">
		<input type="checkbox" href="#" class="ced_menu_open" name="menu-open" id="menu-open" />
		<label class="ced_menu_button" for="menu-open">
			<img src="<?php echo esc_url( CED_TOKOPEDIA_URL . 'admin/images/icon.png' ); ?>" alt="" title="Click to Chat">
		</label>
		<a href="https://join.skype.com/UHRP45eJN8qQ" class="ced_menu_content ced_skype" target="_blank"> <i class="fa fa-skype" aria-hidden="true"></i> </a>
		<a href="https://chat.whatsapp.com/BcJ2QnysUVmB1S2wmwBSnE" class="ced_menu_content ced_whatsapp" target="_blank"> <i class="fa fa-whatsapp" aria-hidden="true"></i> </a>
	</div>

	<?php
}

function ced_tokopedia_inactive_shops( $shop_id = '' ) {
	global $wpdb;
	$shops = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_accounts WHERE `shop_id`=%d", $shop_id ), 'ARRAY_A' );
	foreach ( $shops as $key => $shop ) {
		if ( isset( $shop['account_status'] ) && 'InActive' == $shops['account_status'] ) {
			return true;
		}
	}
}

function ced_tokopedia_get_active_shop_name() {
	$saved_tokopedia_details = get_option( 'ced_tokopedia_details', array() );
	$shopName                = isset( $saved_tokopedia_details['details']['ced_tokopedia_shop_name'] ) ? $saved_tokopedia_details['details']['ced_tokopedia_shop_name'] : '';
	return $shopName;
}

/**
 * Callback function for display html which are searching in the search box.
 *
 * @since 1.0.0
 */
function ced_tokopedia_render_html( $meta_keys_to_be_displayed = array(), $added_meta_keys = array() ) {
	$html  = '';
	$html .= '<table class="wp-list-table widefat fixed striped">';

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
				$html .= '<tbody style="' . esc_attr( $display ) . '" class="ced_tokopedia_metakey_list_' . $break_point . '  			ced_tokopedia_metakey_body">';
				$html .= '<tr><td colspan="3"><label>CHECK THE METAKEYS OR ATTRIBUTES</label></td>';
				$html .= '<td class="ced_tokopedia_pagination"><span>' . $total_items . ' items</span>';
				$html .= '<button class="button ced_tokopedia_navigation" data-page="1" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><<</b></button>';
				$html .= '<button class="button ced_tokopedia_navigation" data-page="' . esc_attr( $break_point - 1 ) . '" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><</b></button><span>' . $break_point . ' of ' . $pages;
				$html .= '</span><button class="button ced_tokopedia_navigation" data-page="' . esc_attr( $break_point + 1 ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>></b></button>';
				$html .= '<button class="button ced_tokopedia_navigation" data-page="' . esc_attr( $pages ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>>></b></button>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr><td><label>Select</label></td><td><label>Metakey / Attributes</label></td><td colspan="2"><label>Value</label></td>';

			}
			$checked    = ( in_array( $meta_key, $added_meta_keys ) ) ? 'checked=checked' : '';
			$html      .= '<tr>';
			$html      .= "<td><input type='checkbox' class='ced_tokopedia_meta_key' value='" . esc_attr( $meta_key ) . "' " . $checked . '></input></td>';
			$html      .= '<td>' . esc_attr( $meta_key ) . '</td>';
			$meta_value = ! empty( $meta_data[0] ) ? $meta_data[0] : '';
			$html      .= '<td colspan="2">' . esc_attr( $meta_value ) . '</td>';
			$html      .= '</tr>';
			++$counter;
			if ( 10 == $counter ) {
				$counter = 0;
				++$break_point;
				$html .= '<tr><td colsapn="4"><a href="" class="ced_tokopedia_custom_button button button-primary">Save</a></td></tr>';
				$html .= '</tbody>';
			}
		}
	} else {
		$html .= '<tr><td colspan="4" class="tokopedia-error">No data found. Please search the metakeys.</td></tr>';
	}
	$html .= '</table>';
	return $html;
}

function ced_topedia_get_account_details_by_shop_name( $shop_id = '' ) {
		
		if ( empty( $shop_id ) || $shop_id =='' || $shop_id == null ) {
			return;
		}
		global $wpdb;
		$shop_data      = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_accounts WHERE `shop_id`=%d", $shop_id ), 'ARRAY_A' );
		$shop_data      = $shop_data[0];
		$shop_id        = $shop_data['shop_id'];
		$shop_name        = $shop_data['name'];
		$account_status = $shop_data['account_status'];
		$access_token   = $shop_data['access_token'];
		$shop_data      = json_decode( $shop_data['shop_data'], true );
		$fsid           = $shop_data['fsid'];
		$client_id      = $shop_data['client_id'];
		$client_secret  = $shop_data['client_secret'];
		$user_id        = $shop_data['user_id'];

		$all_details = array(
			'access_token'   => $access_token,
			'shop_id'        => $shop_id,
			'fsid'           => $fsid,
			'client_id'      => $client_id,
			'client_secret'  => $client_secret,
			'shop_name'  => $shop_name,
			'user_id'        => $user_id,
			'account_status' => $account_status
		);

		return $all_details;
		
}


// Function to get the current IP address
function ced_tokopedia_get_current_ip() {
	
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}