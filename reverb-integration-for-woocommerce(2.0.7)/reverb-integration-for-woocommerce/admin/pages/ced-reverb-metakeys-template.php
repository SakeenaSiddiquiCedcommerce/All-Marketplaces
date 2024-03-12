<div class="ced_reverb_heading">
	<div class="ced_reverb_render_meta_keys_wrapper ced_reverb_global_wrap">
		<div class="ced_reverb_parent_element">
			<h2>
				<label class="basic_heading ced_reverb_render_meta_keys_toggle"><?php esc_html_e( 'METAKEYS AND ATTRIBUTES LIST', 'reverb-woocommerce-integration' ); ?></label>
				<span class="dashicons dashicons-arrow-down-alt2 ced_reverb_instruction_icon"></span>
			</h2>
		</div>
		<div class="ced_reverb_child_element">
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<td><label>Search for the product by its title</label></td>
					<td colspan="2"><input type="text" name="" id="ced_reverb_search_product_name">
						<ul class="ced-reverb-search-product-list">
						</ul>
					</td>
				</tr>
			</table>
			<div class="ced_reverb_render_meta_keys_content">
				<?php
				$meta_keys_to_be_displayed = get_option( 'ced_reverb_metakeys_to_be_displayed', array() );
				$added_meta_keys           = get_option( 'ced_reverb_selected_metakeys', array() );
				$metakey_html              = ced_reverb_render_html( $meta_keys_to_be_displayed, $added_meta_keys );
				print_r( $metakey_html );
				?>
			</div>
		</div>
	</div>
</div>
