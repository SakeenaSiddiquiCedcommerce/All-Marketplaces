<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="ced_fmcw_facebook_attributes" class="panel woocommerce_options_panel">
	<div id="ced_fmcw_accordians">
		<div class="ced_fmcw_facebook_panel">
			<div class="ced_fmcw_facebook_panel_heading">
				<h4><?php esc_attr_e( 'Facebook Product Data', 'facebook-marketplace-connector-for-woocommerce' ); ?></h4>
			</div>
			<div class="ced_fmcw_facebook_collapse">
				<div class="options_group ced_fmcw_facebook_label_data">
					<p class="form-field ced_fmcw_availability_field ">
						<?php $ced_fmcw_availability = ''; ?>
						<?php $ced_fmcw_availability = get_post_meta( $post->ID, 'ced_fmcw_availability', true ); ?>
						<label><span><?php esc_attr_e( 'Availability', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_availability" id="ced_fmcw_availability" class="ced_fmcw_data_fields">
							<option><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="in stock" 
							<?php
							if ( 'in stock' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'In Stock', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="out of stock" 
							<?php
							if ( 'out of stock' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Out of Stock', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="preorder" 
							<?php
							if ( 'preorder' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Pre Order', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="available for order" 
							<?php
							if ( 'available for order' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Available for Order', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="discontinued" 
							<?php
							if ( 'discontinued' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Discontinued', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="pending" 
							<?php
							if ( 'pending' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Pending', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					<?php $ced_fmcw_condition = ''; ?>
					<?php $ced_fmcw_condition = get_post_meta( $post->ID, 'ced_fmcw_condition', true ); ?>
					<p class="ced_fmcw_condition_field form-field">
						<label><span><?php esc_attr_e( 'Product Condition', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_condition" id="ced_fmcw_condition" class="ced_fmcw_data_fields">
							<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="new" 
							<?php
							if ( 'new' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'New', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="refurbished" 
							<?php
							if ( 'refurbished' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Refurbished', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used" 
							<?php
							if ( 'used' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used_like_new" 
							<?php
							if ( 'used_like_new' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used Like New', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used_good" 
							<?php
							if ( 'used_good' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used Good', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used_fair" 
							<?php
							if ( 'used_fair' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used Fair', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="cpo" 
							<?php
							if ( 'cpo' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'CPO', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="open_box_new" 
							<?php
							if ( 'open_box_new' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Open Box New', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					
					<?php $ced_fmcw_agegrp = ''; ?>
					<?php $ced_fmcw_agegrp = get_post_meta( $post->ID, 'ced_fmcw_agegrp', true ); ?>
					<p class="ced_fmcw_agegrp_field form-field">
						<label><span><?php esc_attr_e( 'Preferred Age Group', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_agegrp" id="ced_fmcw_agegrp" class="ced_fmcw_data_fields">
							<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="newborn" 
							<?php
							if ( 'newborn' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'New Born', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="infant" 
							<?php
							if ( 'infant' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'infant', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="toddler" 
							<?php
							if ( 'toddler' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Toddler', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="kids" 
							<?php
							if ( 'kids' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Kids', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="adult" 
							<?php
							if ( 'adult' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Adult', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="all ages" 
							<?php
							if ( 'all ages' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'All Ages', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="teen" 
							<?php
							if ( 'teen' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Teen', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					<?php $ced_fmcw_gender = ''; ?>
					<?php $ced_fmcw_gender = get_post_meta( $post->ID, 'ced_fmcw_gender', true ); ?>
					<p class="ced_fmcw_gender_field form-field">
						<label><span><?php esc_attr_e( 'Select Preferred Audience', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_gender" id="ced_fmcw_gender" class="ced_fmcw_data_fields">
							<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="male" 
							<?php
							if ( 'male' == $ced_fmcw_gender ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Male', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="female" 
							<?php
							if ( 'female' == $ced_fmcw_gender ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Female', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="unisex" 
							<?php
							if ( 'unisex' == $ced_fmcw_gender ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Unisex', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					<?php $ced_fmcw_brand = ''; ?>
					<?php $ced_fmcw_brand = get_post_meta( $post->ID, 'ced_fmcw_brand', true ); ?>
					<p class="ced_fmcw_color_field form-field">
						<label><span><?php esc_attr_e( 'Brand', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<input type="text" class="ced_fmcw_data_fields" id="ced_fmcw_brand" name="ced_fmcw_brand" value="<?php echo esc_attr( $ced_fmcw_brand ); ?>"></input>
					</p>
					<?php $ced_fmcw_mpn = ''; ?>
					<?php $ced_fmcw_mpn = get_post_meta( $post->ID, 'ced_fmcw_mpn', true ); ?>
					<p class="ced_fmcw_material_field form-field">
						<label><span><?php esc_attr_e( 'MPN', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<input type="text" class="ced_fmcw_data_fields" id="ced_fmcw_mpn" name="ced_fmcw_mpn" value="<?php echo esc_attr( $ced_fmcw_mpn ); ?>"></input>
					</p>
					<?php $ced_fmcw_gtin = ''; ?>
					<?php $ced_fmcw_gtin = get_post_meta( $post->ID, 'ced_fmcw_gtin', true ); ?>
					<p class="ced_fmcw_material_field form-field">
						<label><span><?php esc_attr_e( 'GTIN', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<input type="text" class="ced_fmcw_data_fields" id="ced_fmcw_gtin" name="ced_fmcw_gtin" value="<?php echo esc_attr( $ced_fmcw_gtin ); ?>"></input>
					</p>
					<?php
						wp_nonce_field( 'facebook_product_edit', 'facebook_product_edit_actions' );
					?>
						
				</div>
			</div>
		</div>
		<?php do_action( 'ced_fmcw_add_more_data_field_section' ); ?>
	</div>
</div>