<?php
$section = ced_etsy_wcfm_get_active_section();
?>
<div class="collapse wcfm-collapse" id="wcfm_report_details">

	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-chart-line"></span>
		<span class="wcfm-page-heading-text">
			<?php do_action( 'wcfm_page_heading' ); ?>

		</div>
		<div class="wcfm-collapse-content ced-etsy-wcfm-wrapper-main">
			<?php
			$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/ced-etsy-wcfm-accounts.php';
			if( 'global-settings' == $section ) {
				$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/ced-etsy-wcfm-global-settings.php';
			}
			if( 'category-mapping' == $section ) {
				$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/ced-etsy-wcfm-category-mapping.php';
			}
			if( 'products' == $section ) {
				$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-products-list.php';
			}
			if( 'profiles' == $section ) {
				$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-profiles-list.php';
			}
			if( 'orders' == $section ) {
				$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/class-ced-etsy-wcfm-orders-list.php';
			}
			if ( 'import' == $section ) {
				$file = CED_ETSY_WCFM_DIRPATH . '/public/partials/class-ced-etsy-wcfm-import-product-list.php';
			}

			ced_etsy_wcfm_include_file($file);