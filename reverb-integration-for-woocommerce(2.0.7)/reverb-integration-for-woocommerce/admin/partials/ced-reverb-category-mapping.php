<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$header = CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-header.php';
if ( file_exists( $header ) ) {
	require_once $header;
}
$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';

$woo_store_categories = get_terms( 'product_cat' );
$folderName           = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
$catFirstLevelFile    = $folderName . 'categories.json';

if ( file_exists( $catFirstLevelFile ) ) {
	$catFirstLevel = file_get_contents( $catFirstLevelFile );
	$catFirstLevel = json_decode( $catFirstLevel, true );
	$catFirstLevel = isset( $catFirstLevel['categories'] ) ? $catFirstLevel['categories'] : array();
}
?>
<div class="ced_reverb_cat_mapping ced_reverb_toggle_wrapper">
	<div class="ced_reverb_heading">
		<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
		<div class="ced_reverb_child_element">
			<ul type="disc">
				<li><?php echo esc_html_e( 'In this section you will need to map the woocommerce store categories to the reverb categories.' ); ?></li>
				<li><?php echo esc_html_e( 'You need to select the woocommerce category using the checkbox on the left side and list of reverb categories will appear in dropdown.Select the reverb category in which you want to list the products of the selected woocommerce category on reverb.' ); ?></li>
				<li><?php echo esc_html_e( 'Click Save mapping option at the bottom.Once you map the categories' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_reverb&section=profile_view' ) ); ?>">profiles</a> <?php esc_attr_e( 'will automatically be created and you can use the Profiles in order to override the settings of Product Export Settings in Global Settings at category level.' ); ?></li>
			</ul>
		</div>
	</div>
	<div class="ced_reverb_toggle_section">
		<div>
			<table class="ced_reverb_cat_mapping_section wp-list-table widefat fixed striped posts">
				<thead>
					<th><b><?php esc_html_e( 'Select', 'woocommerce-reverb-integration' ); ?></b></th>
					<th><b><?php esc_html_e( 'Store Categories', 'woocommerce-reverb-integration' ); ?></b></th>
					<th colspan="3"><b><?php esc_html_e( 'Mapped to Reverb Category', 'woocommerce-reverb-integration' ); ?></b></th>
					<td><input type="button" class="button-primary" name="" id="ced_reverb_update_categories" value="Update Reverb Categories"></td>
				</thead>
				<tbody>
					<?php
					foreach ( $woo_store_categories as $key => $value ) {
						?>
						<tr class="ced_reverb_store_category" id="<?php echo 'ced_reverb_store_category_' . esc_attr( $value->term_id ); ?>">
						<td>
							<input type="checkbox" class="ced_reverb_select_store_category_checkbox" name="ced_reverb_select_store_category_checkbox[]" data-categoryID="<?php echo esc_attr( $value->term_id ); ?>"></input>
						</td>
						<td>
							<span class="ced_reverb_store_category_name"><?php echo esc_attr( $value->name ); ?></span>
						</td>
						<?php

						$category_mapped_to           = get_term_meta( $value->term_id, 'ced_reverb_mapped_category', true );
						 $alreadyMappedCategoriesName = get_option( 'ced_reverb_profiles_list', array() );
						$category_mapped_name_to      = '';
						if ( ! empty( $alreadyMappedCategoriesName ) ) {
							foreach ( $alreadyMappedCategoriesName as $key => $categories_data ) {
								foreach ( $categories_data['woo_categories'] as $key => $woo_categories ) {
									if ( $woo_categories == $value->term_id ) {
										$category_mapped_name_to = $categories_data['reverb_cat_name'];
									}
								}
							}
						}



						if ( '' != $category_mapped_to && null != $category_mapped_to && '' != $category_mapped_name_to && null != $category_mapped_name_to ) {
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
									<span class="ced_reverb_category_not_mapped">
										<b style="color: red"><?php esc_attr_e( 'Category Not Mapped', 'ced-reverb' ); ?></b>
									</span>
								</td>
							<?php
						}
						?>
					</tr>
					<tr class="ced_reverb_categories" id="<?php echo 'ced_reverb_' . esc_attr( $value->term_id ); ?>">
						<td></td>
						<td data-catlevel="1">
							<select class="ced_reverb_level1_category ced_reverb_select_category select2 ced_reverb_select2 select_boxes_cat_map" name="ced_reverb_level1_category[]" data-level=1 data-storeCategoryID="<?php echo esc_attr( $value->term_id ); ?>" data-reverbStoreId="<?php echo esc_attr( $shop_id ); ?>">
								<option value="">--<?php esc_html_e( 'Select', 'woocommerce-reverb-integration' ); ?>--</option>
							<?php
							foreach ( $catFirstLevel as $key1 => $value1 ) {
								if ( isset( $value1 ) && '' != $value1['uuid'] ) {
									?>
									<option value="<?php echo esc_attr( $value1['uuid'] ); ?>"><?php echo esc_attr( $value1['full_name'] ); ?></option>	
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
		<div class="ced_reverb_category_mapping_header ced_reverb_hidden" id="ced_reverb_category_mapping_header">
							<p><button class="ced_reverb_cancel_category_mapping button-primary">
								<?php esc_attr_e( 'Cancel', 'ced-reverb' ); ?>
							</button>
							<button class="ced_reverb_save_category_mapping button-primary">
								<?php esc_attr_e( 'Save Mapping', 'ced-reverb' ); ?>
							</button></p>
		</div>
	</div>
</div>
