<?php
/**
 * Walmart Main
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( 'ced_walmart' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-configuration.php';
} elseif ( 'global_settings' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-global-settings.php';
} elseif ( 'category_mapping' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-category-mapping.php';
} elseif ( 'profiles' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-profiles-list.php';
} elseif ( 'products' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-products-list.php';
} elseif ( 'feeds' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-feeds-list.php';
} elseif ( 'orders' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-orders-list.php';
} elseif ( 'shipping_template' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-shipping-template-list.php';
} elseif ( 'wfs' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-wfs-setting.php';
} elseif ( 'insights' == get_active_section() ) {
	$file = CED_WALMART_DIRPATH . 'admin/insights/ced-walmart-insights-main.php';
}
include_file( $file );
