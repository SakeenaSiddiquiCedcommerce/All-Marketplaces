<?php
/**
 * Category Mapping
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
$woo_store_categories             = get_terms( 'product_cat' );
$_per_page                        = 10;
$page_no                          = '';
$ced_walmart_category_wfs_file    = CED_WALMART_DIRPATH . 'admin/walmart/lib/json-schema/schema/wfs_convert/MP_WFS_ITEM_SPEC.json';
$ced_walmart_subcategory_wfs_file = CED_WALMART_DIRPATH . 'admin/walmart/lib/json-schema/schema/wfs_convert/walmart-subcategories-wfs.json';
if ( file_exists( $ced_walmart_category_wfs_file ) ) {
	$walmart_wfs_categories = file_get_contents( $ced_walmart_category_wfs_file );
	$walmart_wfs_categories = json_decode( $walmart_wfs_categories, true );
	$wfs_cat_search         = $walmart_wfs_categories['properties']['MPItem']['items']['properties']['Visible']['properties'];
}
if ( file_exists( $ced_walmart_subcategory_wfs_file ) ) {
	$walmart_subcategories_wfs = file_get_contents( $ced_walmart_subcategory_wfs_file );
	$walmart_subcategories_wfs = json_decode( $walmart_subcategories_wfs, true );
}
?>
<div class="ced_walmart_section_wrapper">
	<div class="ced_walmart_heading">
		<?php echo esc_html_e( get_instuctions_html() ); ?>
		<div class="ced_walmart_child_element default_modal">
			<ul type="disc">
				<li><?php echo esc_html_e( 'This section is for mapping your Woocommerce store categories to Walmart WFS categories.' ); ?></li>
				<li><?php echo esc_html_e( 'Choose the Woocommerce category which you want to map by selecting the checkbox.' ); ?></li>
				<li><?php echo esc_html_e( 'The list of Walmart WFS categories will be displayed.' ); ?></li>
				<li><?php echo esc_html_e( 'You need to choose the Walmart WFS category from the list.' ); ?></li>
			</ul>
		</div>
	</div>
	<div class="ced_walmart_category_mapping_wrapper" id="ced_walmart_category_mapping_wrapper">
		<div class="ced_walmart_store_categories_listing" id="ced_walmart_store_categories_listing">
			<table class="wp-list-table widefat fixed striped posts ced_walmart_store_categories_listing_table" id="ced_walmart_store_categories_listing_table">
				<thead>
					<th colspan="3"><b><?php esc_html_e( 'WooCommerce Store Categories', 'walmart-woocommerce-integration' ); ?></b></th>
					<th colspan="4"><b><?php esc_html_e( 'Mapped to Walmart WFS Category', 'walmart-woocommerce-integration' ); ?></b></th>
					<th colspan="4"><b><?php esc_html_e( 'WFS Subcategory  ', 'walmart-woocommerce-integration' ); ?></b></th>
				</thead>
				<tbody>
					<?php
					foreach ( $woo_store_categories as $key => $value ) {
						?>
						<tr class="ced_walmart_store_category" id="<?php echo esc_attr( 'ced_walmart_store_category_' . $value->term_id ); ?>">
							<td colspan="3">
								<b class="ced_walmart_store_category_name" ><?php echo esc_attr( $value->name ); ?></b>
							</td>

							<td colspan="4">
								<select class="ced_walmart_category_wfs ced_walmart_select_category_wfs select2 ced_walmart_select2 select_boxes_cat_wfs_map" id="ced_walmart_category_new_item_wfs" name="ced_walmart_category_new_item_wfs"  data-store-category-id="<?php echo esc_attr( $value->term_id ); ?>" >
									<option value="">--<?php esc_html_e( 'Select', 'walmart-woocommerce-integration' ); ?>--</option>
									<?php
									$selected_wfs_categories = get_term_meta( $value->term_id, 'ced_walmart_wfs_new_item_category', true );
									foreach ( $wfs_cat_search as $index => $data ) {
										$subcat_name = $index;
										if ( isset( $subcat_name ) && ! empty( $subcat_name ) ) {

											if ( $selected_wfs_categories == $subcat_name ) {
												?>
												<option value="<?php echo esc_attr( $subcat_name ); ?>" selected><?php echo esc_attr( strtoupper( $subcat_name ) ); ?></option>	
												<?php
											} else {

												?>
												<option value="<?php echo esc_attr( $subcat_name ); ?>" ><?php echo esc_attr( strtoupper( $subcat_name ) ); ?></option>	
												<?php
											}
										}
									}
									?>
								</select>
							</td>

							<td colspan="4">
								<select class="ced_walmart_subcategory_wfs ced_walmart_select_subcategory_wfs select2 ced_walmart_select2 select_boxes_subcat_wfs_map" id="ced_walmart_subcategory_new_item_wfs" name="ced_walmart_subcategory_new_item_wfs"  data-store-category-id="<?php echo esc_attr( $value->term_id ); ?>" >
									<option value="">--<?php esc_html_e( 'Select', 'walmart-woocommerce-integration' ); ?>--</option>
									<?php
									$selected_subcategories = get_term_meta( $value->term_id, 'ced_walmart_wfs_new_item_subcategory', true );
									foreach ( $walmart_subcategories_wfs['subCategory'] as $index => $data ) {
										$subcat_name = $data;
										if ( isset( $subcat_name ) && ! empty( $subcat_name ) ) {

											if ( $selected_subcategories == $subcat_name ) {
												?>
												<option value="<?php echo esc_attr( $subcat_name ); ?>" selected><?php echo esc_attr( strtoupper( str_replace( '_', ' ', $subcat_name ) ) ); ?></option>	
												<?php
											} else {

												?>
												<option value="<?php echo esc_attr( $subcat_name ); ?>" ><?php echo esc_attr( strtoupper( str_replace( '_', ' ', $subcat_name ) ) ); ?></option>	
												<?php
											}
										}
									}
									?>
								</select>
							</td>
							
						</tr>
						<?php
					}
					?>

				</tbody>
			</table>
		</div>

	</div>

</div>
