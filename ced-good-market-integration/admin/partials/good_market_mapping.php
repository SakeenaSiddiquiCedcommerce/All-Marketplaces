<?php
/**
 * Category Mapping
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
$regenerated_cat = get_option('ced_good_market_re_generated_cat');
if(empty($regenerated_cat)){
	include_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/class-good_market_integration-admin.php';
	$admin_main_class_file_object = new Good_Market_Integration_Admin(GOOD_MARKET_INTEGRATION_BASENAME,GOOD_MARKET_INTEGRATION_VERSION);
	$admin_main_class_file_object->ced_good_market_update_categories();
	update_option('ced_good_market_re_generated_cat', 'cat_regenerated');
}
$woo_store_categories     = get_terms( 'product_cat' );
$_per_page                = 10;
$page_no                  = '';
$ced_good_market_category = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/cats.json';
if ( file_exists( $ced_good_market_category ) ) {
	$ced_good_market_category = file_get_contents( $ced_good_market_category );
	$ced_good_market_category = json_decode( $ced_good_market_category, true );
}

?>
<div class="ced_good_market_section_wrapper">
	<div class="ced_good_market_heading">
		<?php echo esc_html_e( get_instuction_html() ); ?>
		<div class="ced_good_market_child_element default_modal">
			<ul type="disc">
				<li><?php echo esc_html_e( 'This section is for mapping your WooCommerce store categories to Good Market categories.' ); ?></li>
				<li><?php echo esc_html_e( 'For each WooCommerce store category select a Good Market category from the dropdown list.' ); ?></li>
				<li><?php echo esc_html_e( 'To make sure you are seeing the latest Good Market categories, click the Update Category button.' ); ?></li>
			</ul>
		</div>
	</div>

	<div>
	<table class="wp-list-table widefat fixed ced_good_market_table">
		<tbody>
			<tr>
				<td>
					<div class="good_market-button-wrap">
						<input type="button" class="button-primary" id="ced_good_market_update_categories" name="" value="Update category">
					</div>
				</td>
			</tr>
		</tbody>
		
	</table>
</div>

	<div class="ced_good_market_category_mapping_wrapper" id="ced_good_market_category_mapping_wrapper">
		<div class="ced_good_market_store_categories_listing" id="ced_good_market_store_categories_listing">
			<table class="wp-list-table widefat fixed striped posts ced_good_market_store_categories_listing_table" id="ced_good_market_store_categories_listing_table">
				<thead>
					<th colspan="3"><b><?php esc_html_e( 'WooCommerce Store Categories', 'good_market-woocommerce-integration' ); ?></b></th>
					<th colspan="4"><b><?php esc_html_e( 'Mapped to Good Market Category', 'good_market-woocommerce-integration' ); ?></b></th>
				</thead>
				<tbody>
					<?php
					foreach ( $woo_store_categories as $key => $value ) {
						?>
						<tr class="ced_good_market_store_category" id="<?php echo esc_attr( 'ced_good_market_store_category_' . $value->term_id ); ?>">
							<td colspan="3">
								<b class="ced_good_market_store_category_name" ><?php echo esc_attr( ced_good_market_categories_tree( $value, $value->name ) ); ?></b>
							</td>

							<td colspan="4">
								<select class="ced_good_market_category ced_good_market_select_category select2 ced_good_market_select2 select_boxes_cat_map" id="ced_goodmarket_cat_id_<?php echo esc_attr( $value->term_id ); ?>" name="ced_good_market_category"  data-store-category-id="<?php echo esc_attr( $value->term_id ); ?>" >
									<option value="">--<?php esc_html_e( 'Category not mapped', 'good_market-woocommerce-integration' ); ?>--</option>
									<?php
									$selected_categories = get_term_meta( $value->term_id, 'ced_good_market_category', true );
									foreach ( $ced_good_market_category  as $cat_name => $data ) {
										$cname = implode( ' -> ', $data );
										if ( isset( $cat_name ) && ! empty( $cat_name ) ) {

											if ( $selected_categories == $cat_name ) {
												?>
												<option value="<?php echo esc_attr( $cat_name ); ?>" selected><?php echo esc_attr( ( $cname ) ); ?></option>	
												<?php
											} else {

												?>
												<option value="<?php echo esc_attr( $cat_name ); ?>" ><?php echo esc_attr( ( $cname ) ); ?></option>	
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
<?php

function ced_good_market_categories_tree( $value, $cat_name ) {
	if ( 0 != $value->parent ) {
		$parent_id = $value->parent;
		$sbcatch2  = get_term( $parent_id );
		$cat_name  = $sbcatch2->name . ' --> ' . $cat_name;
		if ( 0 != $sbcatch2->parent ) {
			$cat_name = ced_good_market_categories_tree( $sbcatch2, $cat_name );
		}
	}
	return $cat_name;
}

?>