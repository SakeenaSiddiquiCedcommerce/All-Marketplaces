<?php
/**
 * Core Functions
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * Callback function for current section.
 *
 * @since 1.0.0
 */
function get_active_sections() {
	return isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'configuration';
}

/**
 * Callback function for including files.
 *
 * @since 1.0.0
 */
function include_files( $filepath = '' ) {
	if ( file_exists( $filepath ) ) {
		include_once $filepath;
		return true;
	}
	return false;
}

/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function get_instuction_html( $label = '' ) {
	if ( empty( $label ) ) {
		 $label = 'Instructions';
	}
	?>
	<div class="ced_good_market_parent_element">
		<h2>
			<label><?php echo esc_html( $label, 'good_market-woocommerce-integration' ); ?></label>
			<span class="dashicons dashicons-arrow-down-alt2 ced_good_market_instruction_icon"></span>
		</h2>
	</div>
	<?php
}

function ced_good_market_tool_tip( $tip = '' ) {
	print_r( "</br><span class='cedcommerce-tip'>[ $tip ]</span>" );
}

function navigation_good_market() {
	$navigation_menus = array(
		'configuration'     => array(
			'url_end_point' => 'configuration',
			'href'          => admin_url( 'admin.php?page=ced_good_market' ),
		),
		'category mapping'  => array(
			'url_end_point' => 'mapping',
			'href'          => admin_url( 'admin.php?page=ced_good_market&section=mapping' ),
		),
		'Attribute Mapping' => array(
			'url_end_point' => 'global',
			'href'          => admin_url( 'admin.php?page=ced_good_market&section=global' ),
		),
		// 'Attribute Mapping'           => array('url_end_point'=>'profile-list','href'=>admin_url( 'admin.php?page=ced_good_market&section=profile-list' )),
		'products'          => array(
			'url_end_point' => 'products',
			'href'          => admin_url( 'admin.php?page=ced_good_market&section=products' ),
		),
		'Orders'            => array(
			'url_end_point' => 'orders',
			'href'          => admin_url( 'admin.php?page=ced_good_market&section=orders' ),
		),
		'Feeds'             => array(
			'url_end_point' => 'products_feeds',
			'href'          => admin_url( 'admin.php?page=ced_good_market&section=products_feeds' ),
		),
	);
	$navigation_menus = apply_filters( 'ced_good_market_navigation_menus', $navigation_menus );
	return $navigation_menus;
}

/**
 * Callback function for including header.
 *
 * @since 1.0.0
 */
function get_good_market_header() {
	$header_file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/header.php';
	include_files( $header_file );
}

/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function ced_good_market_render_html( $meta_keys_to_be_displayed = array(), $added_meta_keys = array() ) {
	$html  = '';
	$html .= '<table class="wp-list-table widefat fixed">';

	if ( isset( $meta_keys_to_be_displayed ) && is_array( $meta_keys_to_be_displayed ) && ! empty( $meta_keys_to_be_displayed ) ) {
		$total_items  = count( $meta_keys_to_be_displayed );
		$last_page_remain_amount = (int)$total_items%10;
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
				$html .= '<tbody style="' . esc_attr( $display ) . '" class="ced_good_market_metakey_list_' . $break_point . '              ced_good_market_metakey_body">';
				$html .= '<tr><td colspan="3"><label>CHECK THE CUSTOM FIELDS OR ATTRIBUTES</label></td>';
				$html .= '<td class="ced_good_market_pagination"><span>' . $total_items . ' items</span>';
				$html .= '<button class="button ced_good_market_navigation" data-page="1" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><<</b></button>';
				$html .= '<button class="button ced_good_market_navigation" data-page="' . esc_attr( $break_point - 1 ) . '" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><</b></button><span>' . $break_point . ' of ' . $pages;
				$html .= '</span><button class="button ced_good_market_navigation" data-page="' . esc_attr( $break_point + 1 ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>></b></button>';
				$html .= '<button class="button ced_good_market_navigation" data-page="' . esc_attr( $pages ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>>></b></button>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr><td><label>Select</label></td><td><label>Metakey / Attributes</label></td><td colspan="2"><label>Value</label></td>';

			}
			$checked    = ( in_array( $meta_key, $added_meta_keys ) ) ? 'checked=checked' : '';
			$html      .= '<tr>';
			$html      .= "<td><input type='checkbox' class='ced_good_market_meta_key' value='" . esc_attr( $meta_key ) . "'></input></td>";
			$html      .= '<td>' . esc_attr( $meta_key ) . '</td>';
			$meta_value = is_array( $meta_data ) ? $meta_data[0] : $meta_data;
			$html      .= '<td colspan="2">' . esc_attr( $meta_value ) . '</td>';
			$html      .= '</tr>';
			++$counter;
			if ( 10 == $counter || ($current_page == $pages && $last_page_remain_amount == $counter)) {
				$counter = 0;
				++$current_page;
				++$break_point;
				$html .= '<tr><td colsapn="4"><a href="" class="ced_good_market_custom_button button button-primary">Save</a></td></tr>';
				$html .= '</tbody>';
			}
		}
	} else {
		$html .= '<tr><td colspan="4" class="good_market-error">No data found. Please search the metakeys.</td></tr>';
	}
	$html .= '</table>';
	return $html;
}