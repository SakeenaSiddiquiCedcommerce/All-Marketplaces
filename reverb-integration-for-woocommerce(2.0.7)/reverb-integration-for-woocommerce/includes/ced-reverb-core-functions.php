<?php
/**
 * Core Functions
 *
 * @package  Reverb_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * License check ced_reverb_check_license.
 *
 * @since 1.0.0
 */
function ced_reverb_check_license() {
	return true;
	$reverb_license        = get_option( 'ced_reverb_license', false );
	$reverb_license_key    = get_option( 'ced_reverb_license_key', false );
	$reverb_license_module = get_option( 'ced_reverb_license_module', false );
	/**
 	* Filter hook for licensing.
 	* @since 1.0.0
 	*/
	$license_valid         = apply_filters( 'ced_reverb_license_check', false );
	if ( $license_valid ) {
		return true;
	} else {
		return true;
	}
}
function ced_reverb_tool_tip( $tip = '' ) {
	echo wc_help_tip( __( $tip, 'reverb-woocommerce-integration' ) );
}
/**
 * Callback function for including header.
 *
 * @since 1.0.0
 */
function get_reverb_header() {
	$header_file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-header.php';
	reverb_include_file( $header_file );
}
/**
 * Callback function for including files.
 *
 * @since 1.0.0
 */
function reverb_include_file( $filepath = '' ) {
	if ( file_exists( $filepath ) ) {
		include_once $filepath;
		return true;
	}
	return false;
}

/**
 * Environment check for reverb
 *
 * @since 1.0.0
 */
function rifw_environment() {
	$config_details = get_option( 'ced_reverb_configuration_details', array() );
	return '';
	if ( isset( $config_details['environment'] ) && 'sandbox' == $config_details['environment'] ) {
		return '_sandbox';
	}
}

/**
 * Callback function for navigation menus.
 *
 * @since 1.0.0
 */
function get_reverb_navigation_menus() {
	$navigation_menus = array(
		'configuration'    => admin_url( 'admin.php?page=ced_reverb' ),
		'global_settings'  => admin_url( 'admin.php?page=ced_reverb&section=global_settings' ),
		'category_mapping' => admin_url( 'admin.php?page=ced_reverb&section=category_mapping' ),
		'profile_view'     => admin_url( 'admin.php?page=ced_reverb&section=profile_view' ),
		'products'         => admin_url( 'admin.php?page=ced_reverb&section=products' ),
		'orders'           => admin_url( 'admin.php?page=ced_reverb&section=orders' ),
		'import'           => admin_url( 'admin.php?page=ced_reverb&section=import' ),
		// 'error_logs'	   => admin_url('admin.php?page=ced_reverb&section=error_log'),
	);
	/**
 	* Filter hook for filtering columns on product page of plugin.
 	* @since 1.0.0
 	*/
	$navigation_menus = apply_filters( 'ced_reverb_navigation_menus', $navigation_menus );
	return $navigation_menus;
}

/**
 * Callback function for current section.
 *
 * @since 1.0.0
 */
function get_reverb_active_section() {
	return isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'configuration';
}

/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function get_reverb_instuctions_html( $label = 'Instructions' ) {
	?>
	<div class="ced_reverb_parent_element">
		<h2>
			<label><?php echo esc_html_e( $label, 'reverb-woocommerce-integration' ); ?></label>
			<span class="dashicons dashicons-arrow-down-alt2 ced_reverb_instruction_icon"></span>
		</h2>
	</div>
	<?php
}

/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function ced_reverb_render_html( $meta_keys_to_be_displayed = array(), $added_meta_keys = array() ) {
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
				$html .= '<tbody style="' . esc_attr( $display ) . '" class="ced_reverb_metakey_list_' . $break_point . '  			ced_reverb_metakey_body">';
				$html .= '<tr><td colspan="3"><label>CHECK THE METAKEYS OR ATTRIBUTES</label></td>';
				$html .= '<td class="ced_reverb_pagination"><span>' . $total_items . ' items</span>';
				$html .= '<button class="button ced_reverb_navigation" data-page="1" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><<</b></button>';
				$html .= '<button class="button ced_reverb_navigation" data-page="' . esc_attr( $break_point - 1 ) . '" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><</b></button><span>' . $break_point . ' of ' . $pages;
				$html .= '</span><button class="button ced_reverb_navigation" data-page="' . esc_attr( $break_point + 1 ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>></b></button>';
				$html .= '<button class="button ced_reverb_navigation" data-page="' . esc_attr( $pages ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>>></b></button>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr><td><label>Select</label></td><td><label>Metakey / Attributes</label></td><td colspan="2"><label>Value</label></td>';

			}
			$checked    = ( in_array( $meta_key, $added_meta_keys ) ) ? 'checked=checked' : '';
			$html      .= '<tr>';
			$html      .= "<td><input type='checkbox' class='ced_reverb_meta_key' value='" . esc_attr( $meta_key ) . "' " . $checked . '></input></td>';
			$html      .= '<td>' . esc_attr( $meta_key ) . '</td>';
			$meta_value = ! empty( $meta_data[0] ) ? $meta_data[0] : '';
			$html      .= '<td colspan="2">' . esc_attr( $meta_value ) . '</td>';
			$html      .= '</tr>';
			++$counter;
			if ( 10 == $counter ) {
				$counter = 0;
				++$break_point;
				$html .= '</tbody>';
			}
		}
	} else {
		$html .= '<tr><td colspan="4" class="reverb-error">No data found. Please search the metakeys.</td></tr>';
	}
	$html .= '</table>';
	return $html;
}

