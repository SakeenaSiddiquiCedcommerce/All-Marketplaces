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
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'wfs_category_map';
?>

<div class="ced_walmart_global_field_wrapper">
	<div class="ced_walmart_global_field_content">
		<div class="ced_walmart_global_field_header">
			<ul>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=wfs' ) ); ?>" id="wfs_category_map" class="
									<?php
									if ( 'wfs_category_map' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( strtoupper( 'WFS Category Mapping For Item Conversion' ), 'walmart-woocommerce-integration' ); ?></a>
			</li>
			<div class="vhr"></div>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=wfs&tab=wfs_profile_list' ) ); ?>" id="wfs_profile_list" class="
									<?php
									if ( 'wfs_profile_list' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( strtoupper( 'WFS Profile List For Item Conversion' ), 'walmart-woocommerce-integration' ); ?></a>
			</li>
			<div class="vhr"></div>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=wfs&tab=wfs_category_map_new_item' ) ); ?>" id="wfs_category_map" class="
									<?php
									if ( 'wfs_category_map_new_item' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( strtoupper( 'WFS Category Mapping For New Item' ), 'walmart-woocommerce-integration' ); ?></a>
			</li>
			<div class="vhr"></div>

			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_walmart&section=wfs&tab=wfs_new_item_profile_list' ) ); ?>" id="wfs_new_item_profile_list" class="
									<?php
									if ( 'wfs_new_item_profile_list' == $current_tab ) {
										echo 'active'; }
									?>
				"><?php esc_html_e( strtoupper( 'WFS Profile List For New Item' ), 'walmart-woocommerce-integration' ); ?></a>
			</li>

			
		</ul>
		</div>
	</div>
	<div>
		<div class="ced_walmart_global_product_field_wrapper">
			<?php
			if ( 'wfs_category_map' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-wfs-category-map.php';
			}
			?>
		</div>
		<div class="ced_walmart_global_product_field_wrapper">
			<?php
			if ( 'wfs_category_map_new_item' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-wfs-new-item-category-map.php';
			}
			?>
		</div>
		<div>
			<?php
			if ( 'wfs_new_item_profile_list' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-wfs-new-item-profile-list.php';
			}
			?>
		</div>
		<div>
			<?php
			if ( 'wfs_profile_list' == $current_tab ) {
				include_once CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-wfs-profile_list.php';
			}
			?>
		</div>

	</div>
</div>
