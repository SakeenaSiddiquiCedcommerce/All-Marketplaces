<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
ced_etsy_wcfm_get_header();
$woo_store_categories     = get_terms( 'product_cat' );
$etsyFirstLevelCategories = file_get_contents( CED_ETSY_WCFM_DIRPATH . 'public/etsy/lib/json/categoryLevel-1.json' );
$etsyFirstLevelCategories = json_decode( $etsyFirstLevelCategories, true );

$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
if( in_array( 'etsy', $enabled_marketplaces )  ) {

?>

<div class="ced_etsy_wcfm_category_mapping_wrapper" id="ced_etsy_wcfm_category_mapping_wrapper">

	<div class="ced_etsy_wcfm_store_categories_listing" id="ced_etsy_wcfm_store_categories_listing">
		<table class="wp-list-table widefat fixed striped posts ced_etsy_wcfm_store_categories_listing_table" id="ced_etsy_wcfm_store_categories_listing_table">
			<thead>
				<th><b><?php esc_html_e( 'Select', 'woocommerce-etsy-integration' ); ?></b></th>
				<th><b><?php esc_html_e( 'WooCommerce Store Categories', 'woocommerce-etsy-integration' ); ?></b></th>
				<th colspan="3"><b><?php esc_html_e( 'Mapped to Etsy Category', 'woocommerce-etsy-integration' ); ?></b></th>
				<td style="text-align: right;"><!-- <button class="ced_etsy_wcfm_custom_button"  name="ced_etsy_wcfm_refresh_categories" id="ced_etsy_wcfm_category_refresh_button">Refresh Categories</button> --></td>
			</thead>
			<tbody>
				<?php
				foreach ( $woo_store_categories as $key => $value ) {
					?>
					<tr class="ced_etsy_wcfm_store_category" id="<?php echo esc_attr( 'ced_etsy_wcfm_store_category_' . $value->term_id ); ?>">
						<td>
							<input type="checkbox" class="ced_etsy_wcfm_select_store_category_checkbox" name="ced_etsy_wcfm_select_store_category_checkbox[]" data-categoryID="<?php echo esc_attr( $value->term_id ); ?>"></input>
						</td>
						<td>
							<span class="ced_etsy_wcfm_store_category_name"><?php echo esc_attr( $value->name ); ?></span>
						</td>
						<?php
						$shop_name                   = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
						$category_mapped_to          = get_term_meta( $value->term_id, 'ced_etsy_wcfm_mapped_category_' . $shop_name, true );
						$alreadyMappedCategoriesName = get_option( 'ced_woo_etsy_wcfm_mapped_categories_name' . $shop_name, array() );
						$category_mapped_name_to     = isset( $alreadyMappedCategoriesName[ $shop_name ][ $category_mapped_to ] ) ? $alreadyMappedCategoriesName[ $shop_name ][ $category_mapped_to ] : '';
						if ( ! empty( $category_mapped_to ) && null != $category_mapped_to && ! empty( $category_mapped_name_to ) && null != $category_mapped_name_to ) {
							?>
							<td colspan="4">
								<span class="ced_etsy_wcfm_category_mapped">
									<b><?php echo esc_attr( $category_mapped_name_to ); ?></b>
								</span>
							</td>
							<?php
						} else {
							?>
							<td colspan="4">
								<span class="ced_etsy_wcfm_category_not_mapped">
									<b><?php esc_html_e( 'Category Not Mapped', 'woocommerce-etsy-integration' ); ?></b>
								</span>
							</td>
							<?php
						}
						?>
					</tr>

					<tr class="ced_etsy_wcfm_categories" id="<?php echo esc_attr( 'ced_etsy_wcfm_categories_' . $value->term_id ); ?>" style="display: none;">
						<td></td>
						<td data-catlevel="1">
							<select class="ced_etsy_wcfm_level1_category ced_etsy_wcfm_select_category select2 ced_etsy_wcfm_select2 select_boxes_cat_map" name="ced_etsy_wcfm_level1_category[]" data-level=1 data-storeCategoryID="<?php echo esc_attr( $value->term_id ); ?>" data-storeName="<?php echo esc_attr( $shop_name ); ?>" >
								<option value="">--<?php esc_html_e( 'Select', 'woocommerce-etsy-integration' ); ?>--</option>
								<?php
								foreach ( $etsyFirstLevelCategories as $key1 => $value1 ) {
									if ( isset( $value1['name'] ) && ! empty( $value1['name'] ) ) {
										?>
										<option value="<?php echo esc_attr( $value1['id'] ); ?>"><?php echo esc_attr( $value1['name'] ); ?></option>	
										<?php
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
	<div class="ced_etsy_wcfm_category_mapping_header ced_etsy_wcfm_hidden" id="ced_etsy_wcfm_category_mapping_header">
		<button class="ced_etsy_wcfm_add_button ced-wcfm-btn" data-etsyStoreName="<?php echo esc_attr( $shop_name ); ?>" id="ced_etsy_wcfm_save_category_button">
			<?php esc_html_e( 'Save', 'woocommerce-etsy-integration' ); ?>
		</button>
	</div>

</div>
<?php
} ?>