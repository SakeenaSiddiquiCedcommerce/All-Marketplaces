<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
require_once CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-header.php';
$catalog_and_page_id         = get_option( 'ced_fmcw_catalog_and_page_id', true );
$bussiness_id                = array_keys( $catalog_and_page_id )[0];
$connected_catalog_id        = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
$shop_name                   = 'Facebook Marketplace';
$total_products1             = get_posts(
	array(
		'numberposts' => -1,
		'post_type'   => 'product',
	)
);
$total_uploaded_products     = 0;
$total_not_uploaded_products = 0;
$total_errored_products      = 0;
$total_import_products       = 0;
if ( ! empty( $total_products1 ) ) {
	$total_import_products = count( wp_list_pluck( $total_products1, 'ID' ) );
}
$total_products2 = get_posts(
	array(
		'numberposts'  => -1,
		'post_type'    => 'product',
		'meta_key'     => 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id,
		'meta_compare' => 'EXISTS',
		'fields'       => 'ids',
	)
);
if ( ! empty( $total_products2 ) ) {
	$total_uploaded_products = count( $total_products2 );
}
$total_products3 = get_posts(
	array(
		'numberposts'  => -1,
		'post_type'    => 'product',
		'meta_key'     => 'ced_fmcw_product_with_errors_' . $connected_catalog_id,
		'meta_compare' => 'EXISTS',
		'fields'       => 'ids',
	)
);
if ( ! empty( $total_products3 ) ) {
	$total_errored_products = count( $total_products3 );
}
$total_products4 = get_posts(
	array(
		'numberposts'  => -1,
		'post_type'    => 'product',
		'meta_key'     => 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id,
		'meta_compare' => 'NOT EXISTS',
		'fields'       => 'ids',
	)
);
if ( ! empty( $total_products4 ) ) {
	$total_not_uploaded_products = count( $total_products4 );
}
?>
<div class="ced-facebbok-wrapper">
	<div class="ced-mfacebook-page-container">
		<div class="ced-mfacebook-page-wrapper">
			<div class="ced-mfacebook-page-wrap">
				<div class="ced-mfacebook-welcome-text">
					<div class="ced-mfacebook-welcome">
						<h2>Hi, Welcome aboard on Facebook Marketplace Integration Dashboard</h2>
					</div>
				</div>
				<div class="ced-mfacebook-product-dashboard-listing-wrap">
					<div class="ced-mfacebook-product-dashboard-content-common-holder">
						<div class="ced-mfacebook-dasboard-box-container">
							<div class="ced-mfacebook-dashboard-active ced-facebook-com-width">
								<div class="ced-mfacebook-common-text">
									<p>Total Products</p>
									<h5><?php echo esc_attr( $total_import_products ); ?></h5>
								</div>
							</div>
							<div class="ced-mfacebook-dashboard-non-active ced-facebook-com-width">
								<div class="ced-mfacebook-common-text">
									<p>Product Uploaded</p>
									<h5><?php echo esc_attr( $total_uploaded_products ); ?></h5>
								</div>
							</div>
							<div class="ced-mfacebook-dashboard-non-active ced-facebook-com-width">
								<div class="ced-mfacebook-common-text">
									<p>Product Not Uploaded</p>
									<h5><?php echo esc_attr( $total_not_uploaded_products ); ?></h5>
								</div>
							</div>
							<div class="ced-mfacebook-dashboard-stock ced-facebook-com-width">
								<div class="ced-mfacebook-common-text">
									<p>Product with errors</p>
									<h5><?php echo esc_attr( $total_errored_products ); ?></h5>
								</div>
							</div>
						</div>
						<div class="ced-mfacebook-product-common-dashboard-container">
							<div class="ced-mfacebook-product-dasboard-wrap-common">
								<div class="ced-mfacebook-proudct-text-content">
									<h4><?php echo esc_attr( $shop_name ); ?></h4>
								</div>
								<div class="ced-mfacebook-product-field-wrap">
									<div class="ced-dashboard-mfacebook-product-list-home">
										<div class="ced-mfacebook-product-listing">
											<div class="ced-mfacebook-product-list-holder">
											</div>
										</div>
										<div class="ced-mfacebook-product-totle-container">
											<div class="ced-face-total-product-holder">
												<a target="_blank" href="<?php esc_html_e( admin_url() . '/admin.php?page=ced_fb&section=products-view' ); ?>"><span class="ced_fmcw_manage_products">Manage Products</span></a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="ced-mfacebook-product-reset-button-holder">
								<div class="ced-reset-button-mfacebook-common">
									<button class="ced_reset_facebook_entire_setup">Reset Setting</button>
								</div>
							</div>
						</div>
					</div>
					<div class="ced-mfacebook-notification-common-holder">
						<div class="ced-mfacebook-notification-common-text">
							<div class="ced-mfacebook-listing">
								<h3><?php esc_attr_e( 'Recent Activities', 'facebook-marketplace-connector-for-woocommerce' ); ?></h3>
							</div>
							<div class="ced-mfacebook-notification-common-text-wrap">
								<?php





								// $all_feeds             = get_option( 'ced_fmcw_all_product_feeds', array() );
								$all_products_in_feeds = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
								global $wpdb;
								$ced_fmcw_update_feeds_data = $wpdb->get_results( $wpdb->prepare( "SELECT * from {$wpdb->prefix}ced_fb_feeds_status ORDER BY `id` DESC LIMIT %d", 7 ), 'ARRAY_A' );

								// $ced_fmcw_update_feeds_data          = $wpdb->get_results( "SELECT * from {$wpdb->prefix}ced_fb_feeds_status", 'ARRAY_A' );
								// $ced_fmcw_update_feeds_data          = array_reverse( $ced_fmcw_update_feeds_data );
								$call_for_get_shop_details = get_option( 'ced_fmcw_stord_whole_store_data' );
								$connected_page            = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';
								// print_r($ced_fmcw_update_feeds_data);
								// die('ppp');
								if ( ! empty( $ced_fmcw_update_feeds_data ) ) {
									foreach ( $ced_fmcw_update_feeds_data as $ced_fmcw_update_feeds_key => $feed ) {

											// if ( $count <= 10 ) {
										$feed_type   = isset( $feed['feed_type'] ) ? $feed['feed_type'] : '';
										$feed_handle = isset( $feed['feed_id'] ) ? $feed['feed_id'] : '';
										$date        = isset( $feed['feed_time'] ) ? $feed['feed_time'] : '';
										// if ( 'PRODUCTS UPLOAD' == $feed_type ) {
										// $class = 'ced-fmcw-feed-upload-log';
										// $title =
										// } elseif ( 'PRODUCTS UPDATE' == $feed_type ) {
										// $class = 'ced-fmcw-feed-update-log';
										// } elseif ( 'PRODUCTS DELETE' == $feed_type ) {
										// $class = 'ced-fmcw-feed-remove-log';
										// }
										if ( ! empty( $feed_handle ) ) {
											$number_of_products = isset( $all_products_in_feeds[ $feed['feed_id'] ] ) ? count( $all_products_in_feeds[ $feed['feed_id'] ] ) : 0;
											?>
										<div class="ced-mfacebook-common-text-wrap">
											<div class="ced-mfacebook-notification-listion-icon">

											</div>
											<div class="ced-mfacebook-product-notification">
												<span><label>
													<?php echo esc_attr( $number_of_products ); ?>
													<?php echo esc_attr( ucfirst( $feed_type ) ); ?>
												
												</label></span><br><span></span><span class="ced-mfacebook-notification-highlight"><a href="<?php esc_attr_e( admin_url() . '/admin.php?page=ced_fb&section=feed-view&feedID=' . $feed_handle . '&auth_page=' . $connected_page . '&panel=edit' ); ?>">Feed Details</a></span><br><span class="ced-mfacebook-notification-date"><?php esc_attr_e( $date ); ?></span>
											</div>
										</div>
											<?php
										}
										// $count++;
									}

									// }
								} else {
									?>
							<div class="ced-mfacebook-common-text-wrap">
											<div class="ced-mfacebook-notification-listion-icon">

											</div>
											<div class="ced-mfacebook-product-notification">
												No Feed to Show;
											</div>
										</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
