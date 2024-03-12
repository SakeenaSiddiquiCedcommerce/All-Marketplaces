<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/header.php';

$woo_store_categories          = get_terms( 'product_cat' );
$tokopediaFirstLevelCategories = file_get_contents( CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/json/categoryLevel-1.json' );
$tokopediaFirstLevelCategories = json_decode( $tokopediaFirstLevelCategories, true );
$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
?>
<div class="ced_tokopedia_category_mapping_wrapper" id="ced_tokopedia_category_mapping_wrapper">

	<div class="ced_tokopedia_store_categories_listing" id="ced_tokopedia_store_categories_listing">
		<table class="wp-list-table widefat fixed striped posts ced_tokopedia_store_categories_listing_table" id="ced_tokopedia_store_categories_listing_table">
			<thead>
				<th><b><?php esc_html_e( 'Select Categories to be Mapped', 'woocommerce-tokopedia-integration' ); ?></b></th>
				<th><b><?php esc_html_e( 'WooCommerce Store Categories', 'woocommerce-tokopedia-integration' ); ?></b></th>
				<th colspan="3"><b><?php esc_html_e( 'Mapped to Tokopedia Category', 'woocommerce-tokopedia-integration' ); ?></b></th>
				<td style="text-align: right;"><button class="ced_tokopedia_custom_button"  data-shopname="<?php esc_html_e( $shop_name ); ?>" name="ced_tokopedia_refresh_categories" id="ced_tokopedia_category_refresh_button">Refresh Categories</button></td>
			</thead>
			<tbody>
				<?php
				foreach ( $woo_store_categories as $key => $value ) {
					?>
					<tr class="ced_tokopedia_store_category" id="<?php echo esc_attr( 'ced_tokopedia_store_category_' . $value->term_id ); ?>">
						<td>
							<input type="checkbox" class="ced_tokopedia_select_store_category_checkbox" name="ced_tokopedia_select_store_category_checkbox[]" data-categoryID="<?php echo esc_attr( $value->term_id ); ?>"></input>
						</td>
						<td>
							<span class="ced_tokopedia_store_category_name"><?php echo esc_attr( $value->name ); ?></span>
						</td>
						<?php
						$shop_name                   = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
						$category_mapped_to          = get_term_meta( $value->term_id, 'ced_tokopedia_mapped_category_' . $shop_name, true );
						$alreadyMappedCategoriesName = get_option( 'ced_woo_tokopedia_mapped_categories_name', array() );
						$category_mapped_name_to     = isset( $alreadyMappedCategoriesName[ $shop_name ][ $category_mapped_to ] ) ? $alreadyMappedCategoriesName[ $shop_name ][ $category_mapped_to ] : '';
						if ( ! empty( $category_mapped_to ) && null != $category_mapped_to && ! empty( $category_mapped_name_to ) && null != $category_mapped_name_to ) {
							?>
							<td colspan="4">
								<span>
									<b><?php echo esc_attr( $category_mapped_name_to ); ?></b>
								</span>
							</td>
							<?php
						} else {
							?>
							<td colspan="4">
								<span class="ced_tokopedia_category_not_mapped">
									<b><?php esc_html_e( 'Category Not Mapped', 'woocommerce-tokopedia-integration' ); ?></b>
								</span>
							</td>
							<?php
						}
						?>
					</tr>

					<tr class="ced_tokopedia_categories" id="<?php echo esc_attr( 'ced_tokopedia_categories_' . $value->term_id ); ?>">
						<td></td>
						<td data-catlevel="1">
							<select class="ced_tokopedia_level1_category ced_tokopedia_select_category select2 ced_tokopedia_select2 select_boxes_cat_map" name="ced_tokopedia_level1_category[]" data-level=1 data-storeCategoryID="<?php echo esc_attr( $value->term_id ); ?>" data-storeName="<?php echo esc_attr( $shop_name ); ?>" >
								<option value="">--<?php esc_html_e( 'Select', 'woocommerce-tokopedia-integration' ); ?>--</option>
								<?php
								if ( !empty( $tokopediaFirstLevelCategories )) {
									foreach ( @$tokopediaFirstLevelCategories as $key1 => $value1 ) {
										if ( isset( $value1['name'] ) && ! empty( $value1['name'] ) ) {
											?>
											<option value="<?php echo esc_attr( $value1['id'] ); ?>"><?php echo esc_attr( $value1['name'] ); ?></option>
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
	<div class="ced_tokopedia_category_mapping_header ced_tokopedia_hidden" id="ced_tokopedia_category_mapping_header">
		<a class="button-primary" href="" data-tokopediaStoreName="<?php echo esc_attr( $shop_name ); ?>" id="">
			<?php esc_html_e( 'Cancel', 'woocommerce-tokopedia-integration' ); ?>
		</a>
		<button class="button-primary" data-tokopediaStoreName="<?php echo esc_attr( $shop_name ); ?>" id="ced_tokopedia_save_category_button">
			<?php esc_html_e( 'Save', 'woocommerce-tokopedia-integration' ); ?>
		</button>
	</div>

</div>
