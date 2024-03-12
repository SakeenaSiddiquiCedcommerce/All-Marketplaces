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
$woo_store_categories = get_terms( 'product_cat' );
$_per_page            = 10;
$page_no              = '';
$ced_walmart_category = CED_WALMART_DIRPATH . 'admin/walmart/lib/json/MP_ITEM_SPEC.json';
if ( file_exists( $ced_walmart_category ) ) {
	$ced_walmart_category = file_get_contents( $ced_walmart_category );
	$ced_walmart_category = json_decode( $ced_walmart_category, true );
	$ced_walmart_category = $ced_walmart_category['properties']['MPItem']['items']['properties']['Visible']['properties'];
}

?>
<div class="ced_walmart_section_wrapper">
	<div class="ced_walmart_heading">
		<?php echo esc_html_e( get_instuctions_html() ); ?>
		<div class="ced_walmart_child_element default_modal">
			<ul type="disc">
				<li><?php echo esc_html_e( 'This section is for mapping your WooCommerce store categories to Walmart categories.' ); ?></li>
				<li><?php echo esc_html_e( 'Choose the WooCommerce category which you want to map by selecting the checkbox.' ); ?></li>
				<li><?php echo esc_html_e( 'The list of Walmart  categories will be displayed.' ); ?></li>
				<li><?php echo esc_html_e( 'You need to choose the Walmart category from the list.' ); ?></li>
			</ul>
		</div>
	</div>
	<div class="ced_walmart_category_mapping_wrapper" id="ced_walmart_category_mapping_wrapper">
		<div class="ced_walmart_store_categories_listing" id="ced_walmart_store_categories_listing">
			<table class="wp-list-table widefat fixed striped posts ced_walmart_store_categories_listing_table" id="ced_walmart_store_categories_listing_table">
				<thead>
					<th colspan="3"><b><?php esc_html_e( 'WooCommerce Store Categories', 'walmart-woocommerce-integration' ); ?></b></th>
					<th colspan="4"><b><?php esc_html_e( 'Mapped to Walmart Category', 'walmart-woocommerce-integration' ); ?></b></th>
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
								<select class="ced_walmart_category ced_walmart_select_category select2 ced_walmart_select2 select_boxes_cat_map" id="ced_walmart_category" name="ced_walmart_category"  data-store-category-id="<?php echo esc_attr( $value->term_id ); ?>" >
									<option value="">--<?php esc_html_e( 'Category not mapped', 'walmart-woocommerce-integration' ); ?>--</option>
									<?php
									$selected_categories = get_term_meta( $value->term_id, 'ced_walmart_category', true );
									foreach ( $ced_walmart_category  as $index => $data ) {
										$cat_name = $index;
										if ( isset( $cat_name ) && ! empty( $cat_name ) ) {

											if ( $selected_categories == $cat_name ) {
												?>
												<option value="<?php echo esc_attr( $cat_name ); ?>" selected><?php echo esc_attr( strtoupper( $cat_name ) ); ?></option>	
												<?php
											} else {

												?>
												<option value="<?php echo esc_attr( $cat_name ); ?>" ><?php echo esc_attr( strtoupper( $cat_name ) ); ?></option>	
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
