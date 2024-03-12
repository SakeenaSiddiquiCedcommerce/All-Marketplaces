<?php
/**
 * Good Market Main
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! empty( get_active_sections() ) ) {
	$file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/good_market_' . get_active_sections() . '.php';
} else {
	$file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/good_market_configuration.php';

}

include_files( $file );
