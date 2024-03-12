<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$updateinfo   = array();
$attributes   = wc_get_attribute_taxonomies();
$attr_options = array();
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributes_object ) {
		$attr_options[ 'umb_pattr_' . $attributes_object->attribute_name ] = $attributes_object->attribute_label;
	}
}
if ( isset( $_GET['profileID'] ) && ! empty( $_GET['profileID'] ) ) {
	$profile_id = sanitize_text_field( wp_unslash( $_GET['profileID'] ) );
}

if ( isset( $_POST['ced_facebook_profile_save_button'] ) ) {

	if ( ! isset( $_POST['facebook_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['facebook_profiles_actions'] ) ), 'facebook_profiles' ) ) {
				return;
	}

	$updateinfo = isset( $_POST['ced_fmcw_profile_data'] ) ? sanitize_text_field( $_POST['ced_fmcw_profile_data'] ) : array();

	if ( empty( $profile_id ) ) {

		$profile_name    = 'testing Profile';
		$profile_details = array(
			'profile_name'   => $profile_name,
			'profile_status' => 'active',
			'profile_data'   => json_encode( $updateinfo ),
		);
		global $wpdb;
		$profile_table_name = 'wp_ced_fb_profiles';

		$wpdb->insert( $profile_table_name, $profile_details );
		$profile_id       = $wpdb->insert_id;
		$profile_edit_url = admin_url( 'admin.php?page=ced_fb&section=profile-view&profileID=' . $profile_id );
		header( 'location:' . $profile_edit_url . '' );

	} elseif ( $profile_id ) {
		global $wpdb;
		$table_name = 'wp_ced_fb_profiles';
		$wpdb->update(
			$table_name,
			array(
				'profile_status' => 'active',
				'profile_data'   => json_encode( $updateinfo ),
			),
			array( 'id' => $profile_id )
		);
	}
}

global $wpdb;
$profile_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM wp_ced_fb_profiles WHERE `id` = %d ', $profile_id ), 'ARRAY_A' );
if ( ! empty( $profile_data ) ) {
	$profile_category_data = json_decode( $profile_data[0]['profile_data'], true );
}
// print_r($profile_category_data);
?>
<div class="ced_fmcw_loader">
	<img src="<?php echo esc_attr( CED_FMCW_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_fmcw_category_loading_img" id="<?php echo 'ced_fmcw_category_loading_img_' . esc_attr( $value->term_id ); ?>">
</div>

<form method="post">
<div class="ced-fmcw-wrapper-wrap">
	<div class="ced-fmcw-category-mapping-main-wrapper">
		<div class="ced_fmcw_configuration_heading_wrapper ced-fmcw-heading-wrapper">
			<h2 class="ced_fmcw_heading ced_fmcw_configuration_heading"> <?php esc_attr_e( 'Profile', 'facebook-marketplace-connector-for-woocommerce' ); ?> </h2>
		</div>
		<div id="ced_fmcw_accordians">
			<div class="ced_fmcw_facebook_panel">
				<div class="ced_fmcw_facebook_panel_heading">
					<h4><?php esc_attr_e( 'Product Availability Fields', 'facebook-marketplace-connector-for-woocommerce' ); ?></h4>
				</div>
				<div class="ced_fmcw_facebook_collapse">
					<?php $ced_fmcw_profile_name = $profile_category_data['ced_fmcw_profile_name']['value']; ?>
					<input type="hidden" name=ced_fmcw_profile_data[ced_fmcw_profile_name][value] value="<?php echo esc_attr( $ced_fmcw_profile_name ); ?>"></input>
					<div class="options_group ced_fmcw_facebook_label_data">
						<div  class="form-field ced_fmcw_availability_field ">
						 <?php $ced_fmcw_profile_name = ''; ?>
							<?php $ced_fmcw_availability = ''; ?>
							<?php $ced_fmcw_availability = $profile_category_data['ced_fmcw_availability']['value']; ?>
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Availability', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_profile_data[ced_fmcw_availability][value] class="ced_fmcw_data_fields">
								<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
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
							</select></div>
						</div >
						<?php $ced_fmcw_available_date = ''; ?>
						<?php $ced_fmcw_available_date = $profile_category_data['ced_fmcw_available_date']['value']; ?>
						<div  class="ced_fmcw_availability_date_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Available Date', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><input type="text" name=ced_fmcw_profile_data[ced_fmcw_available_date][value] class="ced_fmcw_data_fields ced_fmcw_datepicker" value="<?php echo esc_attr( $ced_fmcw_available_date ); ?>"></input></div>
						</div >
						<?php $ced_fmcw_expiration_date = ''; ?>
						<?php $ced_fmcw_expiration_date = $profile_category_data['ced_fmcw_expiration_date']['value']; ?>
						<div  class="ced_fmcw_expiration_date_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Expiration Date', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><input type="text" name=ced_fmcw_profile_data[ced_fmcw_expiration_date][value] class="ced_fmcw_data_fields ced_fmcw_datepicker" value="<?php echo esc_attr( $ced_fmcw_expiration_date ); ?>"></input></div>
						</div >
						<?php $ced_fmcw_max_handling_time = ''; ?>
						<?php $ced_fmcw_max_handling_time = $profile_category_data['ced_fmcw_max_handling_time']['value']; ?>
						<div  class="ced_fmcw_handling_time_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Maximum Handling Time (in business days)', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><input type="text" name=ced_fmcw_profile_data[ced_fmcw_max_handling_time][value] class="ced_fmcw_data_fields" value="<?php echo esc_attr( $ced_fmcw_max_handling_time ); ?>"></input></div>
						</div >
						<?php $ced_fmcw_min_handling_time = ''; ?>
						<?php $ced_fmcw_min_handling_time = $profile_category_data['ced_fmcw_min_handling_time']['value']; ?>
						<div class="ced_fmcw_handling_time_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Minimum Handling Time (in business days)', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"> <input type="text" name=ced_fmcw_profile_data[ced_fmcw_min_handling_time][value] class="ced_fmcw_data_fields" value="<?php echo esc_attr( $ced_fmcw_min_handling_time ); ?>"></input></div>
						</div >
					</div>
				</div>
			</div>

			<div class="ced_fmcw_facebook_panel">
				<div class="ced_fmcw_facebook_panel_heading">
					<h4><?php esc_attr_e( 'Description Details', 'facebook-marketplace-connector-for-woocommerce' ); ?></h4>
				</div>
				<div class="ced_fmcw_facebook_collapse">
					<div class="options_group ced_fmcw_facebook_label_data">
						<?php $ced_fmcw_condition = ''; ?>
						<?php $ced_fmcw_condition = $profile_category_data['ced_fmcw_condition']['value']; ?>
						<div  class="ced_fmcw_condition_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Product Condition', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_profile_data[ced_fmcw_condition][value] class="ced_fmcw_data_fields">
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
							</select></div>
						</div >
						<?php $ced_fmcw_agegrp = ''; ?>
						<?php $ced_fmcw_agegrp = $profile_category_data['ced_fmcw_agegrp']['value']; ?>
						<div  class="ced_fmcw_agegrp_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Preferred Age Group', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_profile_data[ced_fmcw_agegrp][value] class="ced_fmcw_data_fields">
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
							</select></div>
						</div >
						<?php $ced_fmcw_gender = ''; ?>
						<?php $ced_fmcw_gender = $profile_category_data['ced_fmcw_gender']['value']; ?>
						<div  class="ced_fmcw_gender_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Select Preferred Audience', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_leaf_node"><select name=ced_fmcw_profile_data[ced_fmcw_gender][value] class="ced_fmcw_data_fields">
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
							</select></div>
						</div >
						<?php $ced_fmcw_brand = ''; ?>
						<?php $ced_fmcw_brand = $profile_category_data['ced_fmcw_brand']['value']; ?>
						<div  class="ced_fmcw_color_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'Brand', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_parent_leaf_node"><div class="ced_wmcw_leaf_node"><input type="text" class="ced_fmcw_data_fields" name=ced_fmcw_profile_data[ced_fmcw_brand][value] value="<?php echo esc_attr( $ced_fmcw_brand ); ?>"></input></div>
							<div class="ced_wmcw_leaf_node"><select id="ced_fmcw_profile_data[ced_fmcw_brand][metakey]" name="ced_fmcw_profile_data[ced_fmcw_brand][metakey]">
								<option value="null" selected> -- select -- </option>
								<?php
								$previous_selected_value = $profile_category_data['ced_fmcw_brand']['metakey'];
								if ( is_array( $attr_options ) ) {
									foreach ( $attr_options as $attr_key => $attr_name ) :
										if ( trim( $previous_selected_value == $attr_key ) ) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										?>
										<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
										<?php
									endforeach;
								}
								?>
							</select></div></div>
						</div >
						<?php $ced_fmcw_mpn = ''; ?>
						<?php $ced_fmcw_mpn = $profile_category_data['ced_fmcw_mpn']['value']; ?>
						<div  class="ced_fmcw_material_field form-field">
							<div class="ced_wmcw_leaf_node"><label><span><?php esc_attr_e( 'MPN', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label></div>
							<div class="ced_wmcw_parent_leaf_node"><div class="ced_wmcw_leaf_node"><input type="text" class="ced_fmcw_data_fields" name=ced_fmcw_profile_data[ced_fmcw_mpn][value] value="<?php echo esc_attr( $ced_fmcw_mpn ); ?>"></input></div>
							<div class="ced_wmcw_leaf_node"><select id="ced_fmcw_profile_data[ced_fmcw_mpn][metakey]" name="ced_fmcw_profile_data[ced_fmcw_mpn][metakey]">
								<option value="null" selected> -- select -- </option>
								<?php
								$previous_selected_value = $profile_category_data['ced_fmcw_mpn']['metakey'];
								if ( is_array( $attr_options ) ) {
									foreach ( $attr_options as $attr_key => $attr_name ) :
										if ( trim( $previous_selected_value == $attr_key ) ) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										?>
										<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
										<?php
									endforeach;
								}
								?>
							</select></div></div>
						</div >
					</div>
				</div>
			</div>
			<?php do_action( 'ced_fmcw_add_more_data_field_section' ); ?>
		</div>
		<input type="submit" class="ced_shopee_custom_button save_profile_button" name="ced_facebook_profile_save_button" value="Save Profile"></input>
		<?php
			wp_nonce_field( 'facebook_profiles', 'facebook_profiles_actions' );
		?>
		</form>
	</div>
</div>
