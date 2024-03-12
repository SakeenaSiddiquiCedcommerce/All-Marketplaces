<div class="ced_good_market_heading">
	<div class="ced_good_market_render_meta_keys_wrapper ced_good_market_global_wrap">
		<div class="ced_good_market_parent_element">
			<h2>
				<label class="basic_heading ced_good_market_render_meta_keys_toggle"><?php esc_html_e( 'Add Custom Fields and Attributes for Mapping', 'good_market-woocommerce-integration' ); ?></label>
				<span class="dashicons dashicons-arrow-down-alt2 ced_good_market_instruction_icon"></span>
			</h2>
		</div>
		<div class="ced_good_market_child_element">
			<table class="wp-list-table widefat fixed">
				<tr>
					<td><label>Search for the product by its title</label></td>
					<td colspan="2"><input type="text" name="" id="ced_good_market_search_product_name">
						<ul class="ced-good_market-search-product-list">
						</ul>
					</td>
				</tr>
			</table>
			<div class="ced_good_market_render_meta_keys_content">
				<?php
				$meta_keys_to_be_displayed = get_option( 'ced_good_market_metakeys_to_be_displayed', array() );
				$added_meta_keys           = get_option( 'ced_good_market_selected_metakeys', array() );
				$metakey_html              = ced_good_market_render_html( $meta_keys_to_be_displayed, $added_meta_keys );
				print_r( $metakey_html );
				?>
			</div>
		</div>
	</div>
</div>
