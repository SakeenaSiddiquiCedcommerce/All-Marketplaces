<?php
/**
 * Core Functions
 *
 * @package  Woocommerce_Shopee_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * CoreFunctions ced_fmcw_check_if_already_registered.
 *
 * @since 1.0.0
 */
function ced_fmcw_check_if_already_registered() {
	$is_registered = get_option( 'ced_fmcw_registered_with_cedcommerce', false );
	return $is_registered;
}

/**
 * CoreFunctions ced_fmcw_check_fb_already_connected.
 *
 * @since 1.0.0
 */
function ced_fmcw_check_fb_already_connected() {
	$is_fb_account_connected = get_option( 'ced_fmcw_fb_account_connected', false );
	return $is_fb_account_connected;
}

function ced_fmcw_check_setup_completed() {

	$is_setup_completed = get_option( 'ced_fmcw_setup_completed', false );
	if ( 'yes' == $is_setup_completed ) {
		return true;
	}
	return false;
}

