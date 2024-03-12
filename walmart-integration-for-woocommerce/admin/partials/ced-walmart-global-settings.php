<?php
/**
 * Global Settings
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_walmart_header();
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'product-specific';
?>

<div class="ced_walmart_global_field_wrapper">
	<div class="ced_walmart_global_field_content">
		<div class="ced_walmart_global_field_header">
			<ul>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=global_settings' ) ); ?>" id="product-specific" class="
									<?php
									if ( 'product-specific' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( 'Product', 'walmart-woocommerce-integration' ); ?></a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=global_settings&tab=order-specific' ) ); ?>" id="order-specific" class="
									<?php
									if ( 'order-specific' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( 'Order', 'walmart-woocommerce-integration' ); ?></a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=global_settings&tab=sync-specific' ) ); ?>" id="sync-specific" class="
									<?php
									if ( 'sync-specific' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( 'Crons', 'walmart-woocommerce-integration' ); ?></a>
			</li>
		</ul>
		</div>
	</div>
	<div>
		<div class="ced_walmart_global_product_field_wrapper">
			
			<?php
			if ( 'product-specific' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/pages/ced-walmart-global-product-fields.php';
			}
			?>
		</div>
		<div>
			<?php
			if ( 'order-specific' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/pages/ced-walmart-global-order-fields.php';
			}
			?>
		</div>
		<div>
			<?php
			if ( 'sync-specific' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/pages/ced-walmart-global-sync-fields.php';
			}
			?>
		</div>
	</div>
</div>
