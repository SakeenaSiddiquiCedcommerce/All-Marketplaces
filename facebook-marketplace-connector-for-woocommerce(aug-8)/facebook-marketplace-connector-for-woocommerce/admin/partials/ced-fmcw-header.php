<?php
/**
 * Header of the extensiom
 *
 * @package  Facebook_Marketplace_Connector_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/*
$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
global $wpdb;
$shop_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM wp_ced_shopee_accounts WHERE `shop_id` = %d ', $shop_id ), 'ARRAY_A' );
$shop_details = $shop_details[0];
$countries    = ced_shopee_countries();
if ( isset( $_GET['section'] ) ) {
	$section = sanitize_text_field( wp_unslash( $_GET['section'] ) );
}*/
?>
<div class="ced_fmcw_loader">
	<img src="<?php echo esc_url( CED_FMCW_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_fmcw_loading_img" >
</div>
<div class="ced_fmcw_header_div">
	<?php
	$catalog_and_page_id = array_values( get_option( 'ced_fmcw_catalog_and_page_id', true ) );
	// print_r($catalog_and_page_id);
	$catalog_id  = isset( $catalog_and_page_id[0]['catalog_id'] ) ? $catalog_and_page_id[0]['catalog_id'] : '';
	$page_id     = isset( $catalog_and_page_id[0]['page_id'] ) ? $catalog_and_page_id[0]['page_id'] : '';
	$header_html = "<span class = 'ced_fmcw_header_title'>
	Connected Page ID :  <span class = 'ced_fmcw_header_value'>" . $page_id . " </span>	
	</span>
	<span class = 'ced_fmcw_header_title'>
	Connected Catalog ID : <span class = 'ced_fmcw_header_value'>" . $catalog_id . '</span>	
	</span>';
	print_r( $header_html );
	?>
</div>
<div class="success-admin-notices is-dismissible"></div>
<div class="ced-fmcw-navigation-wrapper">
	<div class="sidebar">
		<div class="ced-facebook-sidebar-home-section">
			<div class="ced-facebook-sidebar-menu-content">
				<i class="fa fa-angle-double-left" aria-hidden="true"></i>
			</div>
		</div>
		<ul class="ced-facebook-sidebar-menu-nav-links">
			<li>
				<div class="ced-facebook-sidebar-menu-link">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=dashboard-view' ) ); ?>" class="
						<?php
						if ( 'dashboard-view' == $section ) {
							echo 'active';
						}
						?>
						">
						<i class="fa fa-tachometer" aria-hidden="true"></i>
						<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Dashboard', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
					</div>
				</li>
				<li>
					<div class="ced-facebook-sidebar-menu-link">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=products-view' ) ); ?>" class="
							<?php
							if ( 'products-view' == $section ) {
								echo 'active';
							}
							?>
							"><i class="fa fa-cubes" aria-hidden="true"></i>
							<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Products', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
						</div>
					</li>
					<li>
						<div class="ced-facebook-sidebar-menu-link">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=settings-view' ) ); ?>" class="
								<?php
								if ( 'settings-view' == $section ) {
									echo 'active';
								}
								?>
								">
								<i class="fa fa-cogs" aria-hidden="true"></i>
								<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Settings', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
							</div>
						</li>
						<li>
							<div class="ced-facebook-sidebar-menu-link">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=category-mapping-view' ) ); ?>" class="
									<?php
									if ( 'category-mapping-view' == $section ) {
										echo 'active';
									}
									?>
									">
									<i class="fa fa-map-signs" aria-hidden="true"></i>
									<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Category Mapping', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
								</div>
							</li>
							<?php
							$global_configuration_settings_data = get_option( 'ced_fmcw_configuration_settings', array() );
							$ced_fmcw_store_location            = isset( $global_configuration_settings_data['ced_fmcw_store_location']['value'] ) ? $global_configuration_settings_data['ced_fmcw_store_location']['value'] : '';
							if ( 'usa' == $ced_fmcw_store_location ) {
								?>
								<li>
									<div class="ced-facebook-sidebar-menu-link">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=order-view' ) ); ?>" class="
											<?php
											if ( 'order-view' == $section ) {
												echo 'active';
											}
											?>
											">
											<i class="fas fa-rss-square" aria-hidden="true"></i>
											<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Orders', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
										</div>
									</li>
								<?php
							}
							?>
								<li>
									<div class="ced-facebook-sidebar-menu-link">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=feed-view' ) ); ?>" class="
											<?php
											if ( 'feed-view' == $section ) {
												echo 'active';
											}
											?>
											">
											<i class="fab fa-staylinked" aria-hidden="true"></i>
											<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Feeds', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
										</div>
									</li>
									<li>
										<div class="ced-facebook-sidebar-menu-link">
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=configuration' ) ); ?>" class="
												<?php
												if ( 'configuration' == $section ) {
													echo 'active';
												}
												?>
												">
												<i class="fa fa-tools" aria-hidden="true"></i>
												<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'Configuration', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
											</div>
										</li>
										<li>
											<div class="ced-facebook-sidebar-menu-link">
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_fb&section=cedfb-faq' ) ); ?>" class="
													<?php
													if ( 'cedfb-faq' == $section ) {
														echo 'active';
													}
													?>
													">
													<i class="fas fa-question-circle" aria-hidden="true"></i>
													<span class="ced-facebook-sidebar-menu-link-name"><?php esc_html_e( 'FAQ', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></a>
												</li>
											</ul>
										</div>
									</div>