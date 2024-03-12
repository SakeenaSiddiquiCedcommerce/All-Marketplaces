<?php
/**
 * Shipping Template
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_walmart_header();

?>

<div>
	<div class="row mt-2">
		<div class="col-10"> 
		   <h4>Listing Quality & Rewards</h4>
		   <span>
			   Listing quality provides you with insights on how customers view your published items. 
		   </span>
	   </div>
	   <div class="col-2 mx-auto">
		  <button type="button" class="btn btn-success" id="ced_walmart_insight_refresh" data-bs-toggle="button" autocomplete="off">Update Insights</button>
	  </div>
  </div>

  <!------ Overall Quality Card ----- -->

  <div class="row">
	<div class="col col-4">
		<?php require_once CED_WALMART_DIRPATH . 'admin/insights/ced-walmart-pro-seller-badge-page.php'; ?>
	</div>

	<div class="col col-4">
		<?php require_once CED_WALMART_DIRPATH . 'admin/insights/ced-walmart-overall-listing-quality-page.php'; ?>
 </div>

 <div class="col col-4">
		<?php require_once CED_WALMART_DIRPATH . 'admin/insights/ced-walmart-unpublished-report.php'; ?>
</div>
</div>
</div>



