<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$settings_saved = 0;

$woo_store_categories = get_terms( 'product_cat' );

/* GET CATEGORIES */
$fb_categories     = file_get_contents( CED_FMCW_DIRPATH . 'admin/lib/json/category.json' );
$fb_categories     = json_decode( $fb_categories, true );
$level1_categories = array();
foreach ( $fb_categories as $key => $value ) {
	if ( isset( $value['level2'] ) && '' == $value['level2'] && isset( $value['level3'] ) && '' == $value['level3'] && isset( $value['level4'] ) && '' == $value['level4'] && isset( $value['level5'] ) && '' == $value['level5'] ) {
		$level1_categories[ $value['ID'] ] = $value['level1'];
	}
}
?>
<div class="ced_fmcw_loader">
	<img src="<?php echo esc_attr( CED_FMCW_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_fmcw_category_loading_img" id="<?php echo 'ced_fmcw_category_loading_img'; ?>">
</div>
<div class="ced-fmcw-wrapper">
	<div class="ced-fmcw-category-mapping-main-wrapper">
		<div class="ced_fmcw_configuration_heading_wrapper ced-fmcw-heading-wrapper">
			<h2 class="ced_fmcw_heading ced_fmcw_configuration_heading"> <?php esc_attr_e( 'Category Mapping', 'facebook-marketplace-connector-for-woocommerce' ); ?> </h2>
		</div>
		
		<div class="ced_fmcw_notification_wrapper">
			
			<div class="updated error ced_fmcw_notification_messages" id="ced_fmcw_category_mapping_failed" >
				<p><?php esc_attr_e( 'Category Mapping Failed.', 'facebook-marketplace-connector-for-woocommerce' ); ?></p>
			</div>
		
			<div class="updated success ced_fmcw_notification_messages" id="ced_fmcw_category_mapping_success" >
				<p><?php esc_attr_e( 'Store Categories are Successfully Mapped .', 'facebook-marketplace-connector-for-woocommerce' ); ?></p>
			</div>
		
			<?php
			if ( 1 == $settings_saved ) {
				?>
				<div class="updated success ced_fmcw_settings_saved_messages" id="ced_fmcw_settings_saved_messages" >
					<p><?php esc_attr_e( 'Settings saved successfully.', 'facebook-marketplace-connector-for-woocommerce' ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="updated success ced_fmcw_notification_messages" id="" >
					<p><?php esc_attr_e( 'Settings saved successfully.', 'facebook-marketplace-connector-for-woocommerce' ); ?></p>
				</div>
				<?php
			}
			?>
		
		</div>
		
		<div class="ced_fmcw_category_mapping_wrapper" id="ced_fmcw_category_mapping_wrapper">
		
			<div class="ced_fmcw_store_categories_listing" id="ced_fmcw_store_categories_listing">
				<table class="wp-list-table widefat fixed striped posts ced_fmcw_store_categories_listing_table" id="ced_fmcw_store_categories_listing_table">
					<thead>
						<th></th>
						<th><b><?php esc_attr_e( 'Store Categories', 'facebook-marketplace-connector-for-woocommerce' ); ?></b></th>
						<th colspan="4"><b><?php esc_attr_e( 'Mapped to', 'facebook-marketplace-connector-for-woocommerce' ); ?></b></th>
					</thead>
					<tbody>
						<?php
						foreach ( $woo_store_categories as $key => $value ) {
							?>
							<tr class="ced_fmcw_store_category" id="<?php echo 'ced_fmcw_store_category_' . esc_attr( $value->term_id ); ?>">
								<td>
									<input type="checkbox" class="ced_fmcw_select_store_caegory_checkbox" name="ced_fmcw_select_store_caegory_checkbox[]" data-categoryID="<?php echo esc_attr( $value->term_id ); ?>"></input>
								</td>
								<td>
									<span class="ced_fmcw_store_category_name"><?php echo esc_attr( $value->name ); ?></span>
								</td>
								<?php
								$category_mapped_to      = get_term_meta( $value->term_id, 'ced_fmcw_category_mapped', true );
								$category_mapped_name_to = get_term_meta( $value->term_id, 'ced_fmcw_category_mapped_name', true );
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
										<span class="ced_fmcw_category_not_mapped">
											<b><?php esc_attr_e( 'Category Not Mapped', 'facebook-marketplace-connector-for-woocommerce' ); ?></b>
										</span>
									</td>
									<?php
								}
								?>
							</tr>
		
							<tr class="ced_fmcw_categories" id="<?php echo 'ced_fmcw_categories_' . esc_attr( $value->term_id ); ?>">
								<td></td>
								<td data-catlevel="1">
									<select class="ced_fmcw_level1_category ced_fmcw_select_category" name="ced_fmcw_level1_category[]" data-level=1 data-storeCategoryID="<?php echo esc_attr( $value->term_id ); ?>">
										<option value="">--<?php esc_attr_e( 'Select', 'facebook-marketplace-connector-for-woocommerce' ); ?>--</option>
									<?php
									foreach ( $level1_categories as $key1 => $value1 ) {
										if ( '' != $value1 ) {
											?>
											<option value="<?php echo esc_attr( $key1 ); ?>"><?php echo esc_attr( $value1 ); ?></option>	
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
		</div>
		<div class="ced_fmcw_category_mapping_header ced_fmcw_hidden" id="ced_fmcw_category_mapping_header">
				<div class="ced_fmcw_category_mapping_status_header">
					<div>
						<div class="ced_fmcw_category_mapping_status_header_row">
							<div class="ced_fmcw_categories_selected"></td>
							<div class="ced_fmcw_categories_cancel">
								<button class="ced_fmcw_cancel_category_mapping">
									<?php esc_attr_e( 'Cancel', 'facebook-marketplace-connector-for-woocommerce' ); ?>
								</button>
							</div>
							<div class="ced_fmcw_categories_save">
								<button class="ced_fmcw_save_category_mapping">
									<?php esc_attr_e( 'Save', 'facebook-marketplace-connector-for-woocommerce' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
	</div>
</div>