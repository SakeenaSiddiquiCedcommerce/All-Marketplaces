<?php

/**
 * Callback function for including header.
 *
 * @since 1.0.0
 */

$shop_name            = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';

function ced_etsy_wcfm_get_header() {
	$header_file = CED_ETSY_WCFM_DIRPATH . 'public/partials/ced-etsy-wcfm-header.php';
	ced_etsy_wcfm_include_file( $header_file );
}

/**
 * Callback function for including files.
 *
 * @since 1.0.0
 */
function ced_etsy_wcfm_include_file( $filepath = '' ) {

	if ( file_exists( $filepath ) ) {
		include_once $filepath;
		ced_etsy_wcfm_sync_existing_product();
		 
		// schedule_events_for_order();
		return true;
	}
	return false;
}

function schedule_events_for_order()
{
	
	if (! wp_next_scheduled ( 'ced_svd_cron' )) 
	{

	    wp_schedule_event( time(), 'oneminute', 'ced_svd_cron' );
	}
}

function ced_etsy_wcfm_sync_existing_product(){    
	$shop_name = ced_etsy_wcfm_get_shopname();
	if(empty($shop_name))
	{
        $shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';      
	}

	$vendor_id = ced_etsy_wcfm_get_vendor_id();
    $shop_id   = ced_etsy_wcfm_get_shop_id($shop_name,$vendor_id );
    if ( empty( $shop_id) || empty( $shop_name ) ) {
  	  return false;
    }
	if( ! get_transient('ced_etsy_wcfm_sync_ex_pro_'. $shop_name . '_' .ced_etsy_wcfm_get_vendor_id() ) ) {
	
		if ( !$shop_id ) {
			return;
		}

		$offset             = get_option( 'ced_etsy_wcfm_get_offset_sync_' . ced_etsy_wcfm_get_vendor_id() , '' );
		if ( empty( $offset ) ) {
			$offset = 0;
		}
		$query_args = array(
			'offset' => $offset,
			'limit'  => 25,
			'state'  => 'active',
		);

		do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
		$action   = "application/shops/{$shop_id}/listings";
		$response = Ced_Etsy_WCFM_API_Request( $shop_name )->get( $action, $shop_name, $query_args );
		
		if ( isset( $response['results'][0] ) ) {
			foreach ( $response['results'] as $key => $value ) {

				$sku = isset( $value['sku'][0] ) ? $value['sku'][0] : '';
				if ( ! empty( $sku ) ) {
					$product_id = ced_get_product_id_by_params( '_sku' , $sku );
					if ( $product_id ) {
						$_product = wc_get_product( $product_id );
						if ( 'variation' == $_product->get_type() ) {
							update_post_meta( $_product->get_parent_id(), '_ced_etsy_wcfm_url_' . $shop_name, $value['url'] );
							update_post_meta( $_product->get_parent_id(), '_ced_etsy_wcfm_listing_id_' . $shop_name, $value['listing_id'] );
						} else {
							update_post_meta( $product_id, '_ced_etsy_wcfm_url_' . $shop_name, $value['url'] );
							update_post_meta( $product_id, '_ced_etsy_wcfm_listing_id_' . $shop_name, $value['listing_id'] );
						}
					}
				}
			}
			if ( isset( $response['pagination']['next_offset'] ) && ! empty( $response['pagination']['next_offset'] ) ) {
				$next_offset = $response['pagination']['next_offset'];
			} else {
				$next_offset = 0;
			}
			update_option( 'ced_etsy_wcfm_get_offset_sync_' . ced_etsy_wcfm_get_vendor_id(), $next_offset );
		} else {
			update_option( 'ced_etsy_wcfm_get_offset_sync_' . ced_etsy_wcfm_get_vendor_id(), 0 );
		}
		
		set_transient( 'ced_etsy_wcfm_sync_ex_pro_'. $shop_name. '_' .ced_etsy_wcfm_get_vendor_id()  , true , 300 );
	}else{
		return;
	}

}

function ced_get_product_id_by_params( $meta_key = '', $meta_value = '' ) {
	if ( ! empty( $meta_value ) ) {
		$posts = get_posts(
			array(

				'numberposts' => -1,
				'post_type'   => array( 'product', 'product_variation' ),
				'meta_query'  => array(
					array(
						'key'     => $meta_key,
						'value'   => trim( $meta_value ),
						'compare' => '=',
					),
				),
				'fields'      => 'ids',

			)
		);
		if ( ! empty( $posts ) ) {
			return $posts[0];
		}
		return false;
	}
	return false;
}

/**
 * Callback function for navigation menus.
 *
 * @since 1.0.0
 */
function get_etsy_wcfm_navigation_menus() {

	$shop_name            = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
	/**
	 ***********************
	 * NAVIGATION MENU WCFM
	 ***********************
	 */
	$navigation_menus = array(
		'global-settings'  => get_wcfm_url() . 'ced-etsy?section=global-settings&shop_name=' . $shop_name,
		'category-mapping' => get_wcfm_url() . 'ced-etsy?section=category-mapping&shop_name=' . $shop_name,
		'profiles'         => get_wcfm_url() . 'ced-etsy?section=profiles&shop_name=' . $shop_name,
		'products'         => get_wcfm_url() . 'ced-etsy?section=products&shop_name=' . $shop_name,
		// 'orders'           => get_wcfm_url() . 'ced-etsy?section=orders&shop_name=' . $shop_name,
		'import'           => get_wcfm_url() . 'ced-etsy?section=import&shop_name=' . $shop_name,
	);
	$navigation_menus = apply_filters( 'ced_etsy_wcfm_navigation_menus', $navigation_menus );
	return $navigation_menus;
}

/**
 * Callback function for current section.
 *
 * @since 1.0.0
 */
function ced_etsy_wcfm_get_active_section() {
	return isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'configuration';
}

function ced_etsy_wcfm_get_shopname() {
	$shopName = get_option( 'ced_etsy_wcfm_shop_name_' .ced_etsy_wcfm_get_vendor_id(), '' );
	if (!empty( $shopName ) ) {
		return $shopName;
	}
	return false;
}

/**
 * Callback function for current section.
 *
 * @since 1.0.0
 */
function ced_etsy_wcfm_get_vendor_id() {
	$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	return $user_id;
}
/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function get_etsy_wcfm_instuctions_html( $label = 'Instructions' ) {
	?>
	<div class="ced_etsy_wcfm_parent_element">
		<h2>
			<label><?php echo esc_html_e( "$label", 'etsy-woocommerce-integration' ); ?></label>
			<span class="dashicons dashicons-arrow-down-alt2 ced_etsy_wcfm_instruction_icon"></span>
		</h2>
	</div>
	<?php
}

/**
 **********************************************
 * Get Product id by listing id and Shop Name
 **********************************************
 * @since 1.0.0
 */
function get_product_id_by_shopname_and_listing_id( $shop_name = '' , $listing = '', $post_status = 'publish' ) {
	
	if ( empty( $shop_name ) || empty( $listing ) ) {
		return;
	}	
	$if_exists = get_posts( 
		array(
			'numberposts'=>-1,
			'post_type'=>'product',
			'post_status'  => $post_status,
			'meta_query'=>array(
				array(
					'key'=>'_ced_etsy_wcfm_listing_id_' . $shop_name,
					'value'=> $listing,
					'compare'=>'='
				)
			),
			'fields'=>'ids'
		) 
	);
	$product_id  = isset( $if_exists[0] ) ? $if_exists[0] : '';
	return $product_id;
}

/**
 *****************************************
 * Ced WCFM api request to the Etsy store
 *****************************************
 * @since 1.0.0
 */
function Ced_Etsy_WCFM_API_Request( $shop_name = '', $vendor_id = '' ){
	require_once CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/vendor/class-ced-wcfm-etsy-api-request.php';
	$request_obj = new Ced_WCFM_Etsy_API_Request( $shop_name, $vendor_id );
	return $request_obj;
}

function ced_etsy_wcfm_acccounts( $all = false, $shop_name='', $vendor_id='' ) {
	$saved_etsy_wcfm_details = get_option( 'ced_etsy_wcfm_accounts' ,array() );
	if (!is_array($saved_etsy_wcfm_details)) {
	 $saved_etsy_wcfm_details = json_decode( $saved_etsy_wcfm_details,true );
	}
	if ($all) {
		return $saved_etsy_wcfm_details;
	}
	$saved_etsy_wcfm_details = isset( $saved_etsy_wcfm_details[$vendor_id][$shop_name] ) ? $saved_etsy_wcfm_details[$vendor_id][$shop_name] : array();
	return $saved_etsy_wcfm_details;
}

function ced_etsy_wcfm_get_shop_id( $shop_name='', $vendor_id='' ){
	if ('' == $vendor_id || empty( $vendor_id ) || NULL == $vendor_id ) {
		$vendor_id = ced_etsy_wcfm_get_vendor_id();
	}
	$saved_etsy_wcfm_details = get_option( 'ced_etsy_wcfm_accounts' ,array() );
	if (!is_array($saved_etsy_wcfm_details)) {
	 $saved_etsy_wcfm_details = json_decode( $saved_etsy_wcfm_details,true );
	}
	$shop_id = isset( $saved_etsy_wcfm_details[$vendor_id][$shop_name]['details']['shop_id'] ) ? $saved_etsy_wcfm_details[$vendor_id][$shop_name]['details']['shop_id'] : 0;
	return $shop_id;
}