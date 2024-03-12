<?php
/**
 * Reverb Main
 *
 * @package  reverb_Integration_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-configuration.php';
if ( 'ced_reverb' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-configuration.php';
} elseif ( 'global_settings' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-settings.php';
} elseif ( 'category_mapping' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-category-mapping.php';
} elseif ( 'profile_view' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-profile-view.php';
} elseif ( 'products' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/class-ced-reverb-products-list.php';
} elseif ( 'orders' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/class-ced-reverb-orders-list.php';
} elseif ( 'import' == get_reverb_active_section() ) {
	$file = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-import-product.php';
} elseif('error_log' == get_reverb_active_section()){
	$file = CED_REVERB_DIRPATH.'admin/partials/ced-reverb-error-logs.php';

	
}
reverb_include_file( $file );
