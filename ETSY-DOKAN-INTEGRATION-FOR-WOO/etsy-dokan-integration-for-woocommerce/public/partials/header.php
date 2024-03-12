<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$activeShop = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';

$shop_id      = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
$shop_details = get_option( 'ced_etsy_user_details', array() );
$shop_details = $shop_details[ get_current_user_id() ][0];
if ( isset( $_GET['section'] ) ) {
	$section = sanitize_text_field( wp_unslash( $_GET['section'] ) );
}
global $wp;
$request = $wp->request;
$active  = explode( '/', $request );
unset( $active[0] );
$active  = array_values( $active );
$section = $active[1];


if ( isset( $_GET['section'] ) ) {

	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
}
update_option( 'ced_etsy_active_shop', trim( $activeShop ) );
?>
<div class="ced_etsy_dokan_loader">
	<img src="<?php echo esc_url( CED_ETSY_DOKAN_URL . 'public/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_etsy_loading_img" >
</div>
<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper">
	<ul class="navigation">
		<li>
			<?php
			$url = dokan_get_navigation_url( 'ced_etsy/ced-etsy-settings' ) . '?de_shop_name=' . $activeShop;
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="
				<?php
				if ( 'ced-etsy-settings' == $section || 'add-shipping-profile-view' == $section ) {
					echo 'active'; }
					?>
					"><?php esc_html_e( 'Global Settings', 'woocommerce-etsy-integration' ); ?></a>
				</li>
				<li>
					<?php
					$url = dokan_get_navigation_url( 'ced_etsy/category-mapping-view' ) . '?de_shop_name=' . $activeShop;
					?>
					<a class="
					<?php
					if ( 'category-mapping-view' == $section ) {
						echo 'active'; }
						?>
						" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Category Mapping', 'woocommerce-etsy-integration' ); ?></a>
					</li>
					<li>
						<?php
						$url = dokan_get_navigation_url( 'ced_etsy/profiles-view' ) . '?de_shop_name=' . $activeShop;
						?>
						<a class="
						<?php
						if ( 'profiles-view' == $section || 'profile-edit-view' == $section ) {
							echo 'active'; }
							?>
							" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Profile', 'woocommerce-etsy-integration' ); ?></a>
						</li>
						<!-- <li>
							<?php
							//$url = dokan_get_navigation_url( 'ced_etsy/products-view' ) . '?de_shop_name=' . $activeShop;
							?>
							<a class="
							<?php
							//if ( 'products-view' == $section ) {
								//echo 'active'; }
								?>
								" href="<?php // echo esc_url( $url ); ?>"><?php //esc_html_e( 'Products', 'woocommerce-etsy-integration' ); ?></a>
							</li> -->
							<!-- 	<li>
								<?php
								// $url = dokan_get_navigation_url( 'ced_etsy/orders-view' ) . '?de_shop_name=' . $activeShop;
								?>
								<a class="
								<?php
								// if ( 'orders-view' == $section ) {
									// echo 'active'; }
									?>
									" href="<?php// echo esc_url( $url ); ?>"><?php // esc_html_e( 'Orders', 'woocommerce-etsy-integration' ); ?></a>
								</li>
								<li>
									<?php
									// $url = dokan_get_navigation_url( 'ced_etsy/product-importer' ) . '?de_shop_name=' . $activeShop;
									?>
									<a class="
									<?php
									// if ( 'product-importer' == $section ) {
										//echo 'active'; }
										?>
										" href="<?php // echo esc_url( $url ); ?>"><?php // esc_html_e( 'Importer', 'woocommerce-etsy-integration' ); ?></a>
									</li> -->
								</ul>
								<?php
								$active = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';
								if ( isset( $active ) ) {
									?>
									<span class="ced_etsy_current_account_name"><?php echo "<b>Etsy Shop</b> - <label style='color:#0073aa;'><b>" . esc_attr( $active ) . '</b></label>'; ?></span>
									<?php
								}
								?>
							</div>
