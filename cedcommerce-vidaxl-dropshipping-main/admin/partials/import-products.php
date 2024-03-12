<?php
/**
 * Import Products
 *
 * @package  Woocommerce_vidaXL_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_VIDAXL_DROPSHIPPING_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	include_once $file;
}
$general_functions = CED_VIDAXL_DROPSHIPPING_DIRPATH . 'includes/general-functions.php';
if ( file_exists( $general_functions ) ) {
	include_once $general_functions;
}

?>
<div class="ced-vidaxl-container">
	<h2><?php esc_html_e( 'Import Products', 'cedcommerce-vidaxl-dropshipping' ); ?></h2>

    <div id="ced-vidaxl-steps-frame">
		<div class="tab-content">
			<div class="ced-vidaxl-display-tab" id="step1">
				<ul>
					<li>
                        <table class="form-table">
                            <tbody>
						        <tr>
                                    <th>
                                        <label for="ced_vidaxl_feed_language"><?php esc_html_e( 'Select Feed Language', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
                                    </th>
                                    <td>
                                        <?php
                                        $countries_list = get_countries();						
                                        ?>
                                        <select id="ced_vidaxl_feed_language" name="ced_vidaxl_authorization_data[ced_vidaxl_feed_language]" class="ced-vidaxl-form-elements">
                                        <?php
                                            if( isset( $countries_list ) && !empty( $countries_list ) ){
                                                foreach($countries_list as $code => $country){
                                                    if( $code == $feed_language ){
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option value="'.$code.'" '.$selected.' >' .$country. '</option>';
                                                }		
                                            }						
                                        ?>	
                                        </select>
                                        <p class="ced-ced-vidaxl-form-desc"><?php esc_html_e( 'Select Country which you want to allow for ordering the product.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
										<p><i>( This process will take approx 5-10 minutes. )</i></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th>
                                        
                                    </th>
                                    <td>	
										<button name ="ced_vidaxl_save_authorization" class="ced-vidaxl-next-btn1 ced-vidaxl-button"><span>Next</span></button>
										<div class="vidaxl-loader-div"><span class=""></span></div>
										<!-- vidaxl-loader-circle -->
                                    </td>
                                </tr>                          
                            </tbody>
                        </table>
				    </li>
				</ul>
			</div>
			<div class="ced-vidaxl-display-tab" id="step2">
				<ul>
					<li>
						<table class="form-table">
							<tbody>
								<tr>
									<th>
										<label for="ced_vidaxl_min_price"><?php esc_html_e( 'Minimum Price', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
									</th>
									<td>
										<input type="number" id="ced_vidaxl_min_price" name="ced_vidaxl_min_price" class="ced-vidaxl-form-elements">
										<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Set Minimum Price for product import.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
									</td>
								</tr>

								<tr>
									<th>
										<label for="ced_vidaxl_max_price"><?php esc_html_e( 'Maximum Price', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
									</th>
									<td>
										<input type="number" id="ced_vidaxl_max_price" name="ced_vidaxl_max_price" class="ced-vidaxl-form-elements">
										<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Set Maximum Price for product import.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
									</td>
								</tr>

								<tr>
									<th>
										<label for="ced_vidaxl_use_draft"><?php esc_html_e( 'Use as Draft', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
									</th>
									<td>
									<label class="ced-vidaxl-checkbox">
										<input type="checkbox" id="ced_vidaxl_use_draft" name="ced_vidaxl_use_draft" >
										<span class="ced-vidaxl-slide"></span>
									</label>
									<p class=" ced-vidaxl-form-desc "><?php esc_html_e( 'Check this if you want to import products in Draft mode.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
									</td>
								</tr>
								<tr>
									<th>
										<label for="ced_vidaxl_update_option"><?php esc_html_e( 'Update Data', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
									</th>
									<td>
										<select id="ced_vidaxl_update_option" name="ced_vidaxl_update_option[]" class="ced-vidaxl-form-elements" multiple = 'multiple'>
											<option value = "title" >Title</option>
											<option value = "description" >Description</option>
											<option value = "price" >Price</option>
											<option value = "stock" >Stock</option>
											<option value = "category" >Category</option>
										</select>
										<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Select data which you want to update in the sync process.', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
									</td>
								</tr>
								<tr>
									<th>
										<label for="ced_vidaxl_category"><?php esc_html_e( 'Product Categories', 'cedcommerce-vidaxl-dropshipping' ); ?></label>
									</th>
									<td>
										<select id="ced_vidaxl_feed_category" name="ced_vidaxl_feed_category[]" class="ced-vidaxl-form-elements" multiple = 'multiple' >
										
										</select>
										<p class="ced-vidaxl-form-desc"><?php esc_html_e( 'Select Product Categories', 'cedcommerce-vidaxl-dropshipping' ); ?></p>
									</td>
								</tr>
								<tr>
									<th>
										
									</th>
									<td>	
										<button name ="ced_vidaxl_import_product_button" id="ced_vidaxl_import_product_button" class="ced-vidaxl-next-btn1 ced-vidaxl-button" style="width:200px;"><span>Import Products</span></button>
										<div class="vidaxl-loader-div"><span class=""></span></div>
										<!-- vidaxl-loader-circle -->
									</td>
								</tr>                          
							</tbody>
						</table>
					</li>
				</ul>
			</div>

		</div>
		<div id="snackbar"></div>								
		<div class="ced-vidaxl-progress-wrap">
			<div class="ced-vidaxl-line-progress-show">
				<div class="ced-vidaxl-straight-line"></div>
				<ul class="checkout-bar">
					<li class="ced-vidaxl-progress-circle active"><span>step 1</span></li>
					<li class="ced-vidaxl-progress-circle"><span>step 2</span></li>
				</ul>
			</div>
		</div>
	</div>
</div>