<?php
/**
 * Global Settings
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

get_good_market_header();

$current_tab = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'product-specific';

?>

<div class="ced_good_market_global_field_wrapper">
	<div class="ced_good_market_global_field_content">
	</div>
	<div>
		<div class="ced_good_market_global_product_field_wrapper">
			
			<?php
			if ( 'global' == $current_tab ) {
				include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/pages/ced-good_market-global-product-fields.php';
			}
			?>
		</div>
		<div>
			<?php
			if ( 'order-specific' == $current_tab ) {
				include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/pages/ced-good_market-global-order-fields.php';
			}
			?>
		</div>
		<div>
			<?php
			if ( 'sync-specific' == $current_tab ) {
				include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/pages/ced-good_market-global-sync-fields.php';
			}
			?>
		</div>
	</div>
</div>
