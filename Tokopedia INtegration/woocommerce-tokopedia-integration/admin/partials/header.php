<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$activeShop = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';

if ( isset( $_GET['section'] ) ) {

	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
}
update_option( 'ced_tokopedia_active_shop', trim( $activeShop ) );
?>
<div class="ced_tokopedia_loader">
	<img src="<?php echo esc_url( CED_TOKOPEDIA_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_tokopedia_loading_img" >
</div>
<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper">
	<ul class="navigation">
		
				<li>
					<?php
					$url = admin_url( 'admin.php?page=ced_tokopedia&section=global-settings-view&shop_name=' . $activeShop );
					?>
					<a href="<?php echo esc_attr( $url ); ?>" class="
						<?php
						if ( 'global-settings-view' == $section ) {
							echo 'active'; }
						?>
							"><?php esc_html_e( 'Global Settings', 'woocommerce-tokopedia-integration' ); ?></a>
						</li>
							<li>
									<?php
									$url = admin_url( 'admin.php?page=ced_tokopedia&section=category-mapping-view&shop_name=' . $activeShop );
									?>
									<a class="
									<?php
									if ( 'category-mapping-view' == $section ) {
										echo 'active'; }
									?>
										" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Category Mapping', 'woocommerce-tokopedia-integration' ); ?></a>
									</li>
									<li>
										<?php
										$url = admin_url( 'admin.php?page=ced_tokopedia&section=profiles-view&shop_name=' . $activeShop );
										?>
										<a class="
										<?php
										if ( 'profiles-view' == $section ) {
											echo 'active'; }
										?>
											" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Profile', 'woocommerce-tokopedia-integration' ); ?></a>
										</li>
										<li>
											<?php
											$url = admin_url( 'admin.php?page=ced_tokopedia&section=products-view&shop_name=' . $activeShop );
											?>
											<a class="
											<?php
											if ( 'products-view' == $section ) {
												echo 'active'; }
											?>
												" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Products', 'woocommerce-tokopedia-integration' ); ?></a>
											</li>
											<li>
												<?php
												$url = admin_url( 'admin.php?page=ced_tokopedia&section=orders-view&shop_name=' . $activeShop );
												?>
												<a class="
												<?php
												if ( 'orders-view' == $section ) {
													echo 'active'; }
												?>
													" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Orders', 'woocommerce-tokopedia-integration' ); ?></a>
												</li>
					
											</ul>
											<?php
											$active = ced_topedia_get_account_details_by_shop_name( $activeShop )['shop_name'];
											if ( isset( $active ) ) {
												?>
												<span class="ced_tokopedia_current_account_name"><?php echo "<b>Current Shop</b> - <label style='color:#0073aa;'><b>" . esc_attr( $active ) . '</b></label>'; ?></span>
												<?php
											}
											?>

										</div>
<?php esc_attr( display_tokopedia_support_html() ); ?>
