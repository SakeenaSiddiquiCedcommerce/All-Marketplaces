<div class="ced_tokopedia_heading">
	<div class="ced_tokopedia_render_meta_keys_wrapper ced_tokopedia_global_wrap">
		<div class="ced_tokopedia_parent_element">			<h2>
				<label class="basic_heading ced_tokopedia_render_meta_keys_toggle"><?php esc_html_e( 'METAKEYS AND ATTRIBUTES LIST', 'woocommerce-tokopedia-integration' ); ?></label>				
				<span class="dashicons dashicons-arrow-down-alt2 ced_tokopedia_instruction_icon"></span>
			</h2>
		</div>		
		<div class="ced_tokopedia_child_element">
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<td><label>Search for the product by its title</label></td>
					<td colspan="2"><input type="text" name="" id="ced_tokopedia_search_product_name">
						<ul class="ced-tokopedia-search-product-list">
						</ul>
					</td>
				</tr>
			</table>
			<div class="ced_tokopedia_render_meta_keys_content">
				<?php
				$meta_keys_to_be_displayed = get_option( 'ced_tokopedia_metakeys_to_be_displayed', array() );
				$added_meta_keys           = get_option( 'ced_tokopedia_selected_metakeys', array() );
				$metakey_html              = ced_tokopedia_render_html( $meta_keys_to_be_displayed, $added_meta_keys );
				print_r( $metakey_html );
				?>
			</div>
		</div>
	</div>
</div>
