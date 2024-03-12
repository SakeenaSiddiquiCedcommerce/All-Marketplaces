<?php
/**
 *  Dokan Dashboard Template [ To redirect the page (Template load ) this ced_etsy.php file is responsible ]
 *
 *  Dokan Template for Taobao config
 *
 *  @since 2.4
 *
 *  @package dokan
 */

global $wp;
$request = $wp->request;
$active  = explode( '/', $request );
unset( $active[0] );

if ( $active ) {
	$active_menu = implode( '/', $active );
	if ( $active_menu == 'ced_etsy' ) {
		$active_menu = 'ced_etsy';
	}

	/*
	if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {
		$active_menu = 'products';
	}*/
} else {
	$active_menu = 'dashboard';
}
?>
<div class="dokan-dashboard-wrap">
	<?php
		/**
		 *  dokan_dashboard_content_before hook
		 *
		 *  @hooked get_dashboard_side_navigation
		 *
		 *  @since 2.4
		 */
		do_action( 'dokan_dashboard_content_before' );
	?>

	<div class="dokan-dashboard-content ced-ebay-dokan-dashboard-content">

		<?php
			/**
			 *  dokan_dashboard_content_before hook
			 *
			 *  @hooked show_seller_dashboard_notice
			 *
			 *  @since 2.4
			 */
			do_action( 'dokan_help_content_inside_before' );
			$status = checkLicenseValidationEtsyDokan();
			if ($status) {
				if ( 1 == count( explode( '/', $active_menu ) ) && $active_menu == 'ced_etsy' ) {
					$fileName = 'ced-etsy-accounts.php';
				} else {
					$fileName = explode( '/', $active_menu );
					if ( 1 == count( $fileName ) &&  'ced_etsy' == $fileName[0] ) {
						$fileName = 'ced-etsy-accounts.php';
					}else{
						if ( in_array( end( $fileName ), array( 'ced-etsy-settings', 'ced-etsy-accounts', 'add-shipping-profile-view', 'category-mapping-view', 'profile-edit-view', 'profiles-view'))) {
							$fileName = end( $fileName ) . '.php';
						}else{
							$fileName = 'ced-etsy-accounts.php';
						}
					}
				}
				if ( file_exists( CED_ETSY_DOKAN_DIRPATH . 'public/partials/'. $fileName ) ) {
					require_once $fileName;
				}
			} else {
				do_action( 'ced_etsy_license_panel' );
			}

			/**
			 *  dokan_dashboard_content_inside_after hook
			 *
			 *  @since 2.4
			 */
			do_action( 'dokan_dashboard_content_inside_after' );
			?>


	</div><!-- .dokan-dashboard-content -->

	<?php
		/**
		 *  dokan_dashboard_content_after hook
		 *
		 *  @since 2.4
		 */
		do_action( 'dokan_dashboard_content_after' );
	?>

</div><!-- .dokan-dashboard-wrap -->