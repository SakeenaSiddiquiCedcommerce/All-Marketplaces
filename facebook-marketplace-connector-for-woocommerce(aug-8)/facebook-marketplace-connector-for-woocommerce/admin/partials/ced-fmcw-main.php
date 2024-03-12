<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$section          = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
$found_page       = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
$selected_catalog = get_option( 'ced_catalog_saved', '' );
if ( empty( $section ) && 'true' != $selected_catalog ) {
	$section = 'catalog-selection';
} elseif ( empty( $section ) && 'true' == $selected_catalog ) {
	$section = 'dashboard-view';
}
if ( 'ced_fb' == $found_page ) {
	?>
	<div class="ced-fmcw-main-pages-wrapper">
	<?php
	if ( 'catalog-selection' != $section ) {
		include_once CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-header.php';
	}
		$file_name = CED_FMCW_DIRPATH . 'admin/partials/' . $section . '.php';
		include_once $file_name;
	?>
	</div>
	<?php
}