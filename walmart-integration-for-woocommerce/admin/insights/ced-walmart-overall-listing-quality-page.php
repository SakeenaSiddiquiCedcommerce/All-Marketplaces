<?php


$ced_walmart_insight_file = CED_WALMART_DIRPATH . 'admin/insights/class-ced-walmart-insights.php';

if ( file_exists( $ced_walmart_insight_file ) ) {
	include_once $ced_walmart_insight_file;
}

$insight_obj          = new Ced_Walmart_Insights();
$listing_quality_data = $insight_obj->ced_walmart_overall_listing_quality_data();



?>





<div class="card" style="height: 100%;">
  <div class="card-body">
	  <img src="<?php echo esc_url( CED_WALMART_URL . 'admin/images/Item-Status.png' ); ?>" >
	<div class="row ced-card-wrap-wrapper">
		<div class="col-6">
		   <h5 class="card-title">Listing Quality</h5>
		   <button class="btn btn-primary ced-button-wrap" data-bs-toggle="modal" data-bs-target="#overallListingQuality">View breakdown</button>
	   </div>
	   <div class="col-6">
		<div class="progress-circle" data-value='<?php echo esc_attr( $listing_quality_data['overAllQuality'] ); ?>'>
		  <span class="progress-circle-left">
			<span class="progress-circle-bar border-primary"></span>
		</span>
		<span class="progress-circle-right">
			<span class="progress-circle-bar border-primary"></span>
		</span>
		<div class="progress-circle-value w-100 h-100 rounded-circle d-flex align-items-center justify-content-center">
			<div class="h2 font-weight-bold"><?php echo esc_attr( $listing_quality_data['overAllQuality'] ); ?><sup class="small">%</sup></div>
		</div>
	</div>
</div>
</div>

</div>
</div>


<div class="modal fade" id="overallListingQuality" tabindex="-1" aria-labelledby="overallListingQuality" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
	<div class="modal-content">

		<div class="bg-dark w-100 p-3"><h4 class="text-white">Listing Quality Breakdown</h4>
		</div>

		<div class="modal-body">

			<div class="row">
				<div class="col-5">
					<p>Content & Discoverability</p>
				</div>
				<div class="col-5">
				 <div class="progress mb-4">
				  <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo esc_attr( $listing_quality_data['contentScore'] ); ?>%;" aria-valuenow="<?php echo esc_attr( $listing_quality_data['contentScore'] ); ?>" aria-valuemin="0" aria-valuemax="100">
				  </div>
			  </div>
		  </div>

		  <div class="col-2">
			 <p><b><?php echo esc_attr( $listing_quality_data['contentScore'] ); ?>%</b></p>
		 </div>
	 </div>



	 <div class="row">
		<div class="col-5">
			<p>Offer</p>
		</div>
		<div class="col-5">
		 <div class="progress mb-4">
			<div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo esc_attr( $listing_quality_data['offerScore'] ); ?>%;" aria-valuenow="<?php echo esc_attr( $listing_quality_data['offerScore'] ); ?>" aria-valuemin="0" aria-valuemax="100">
			</div>
		</div>
	</div>

	<div class="col-2">
	 <p><b><?php echo esc_attr( $listing_quality_data['offerScore'] ); ?>%</b></p>
 </div>
</div>


<div class="row">
	<div class="col-5">
		<p> Ratings & Reviews</p>
	</div>
	<div class="col-5">
	 <div class="progress mb-4">
		<div class="progress-bar bg-success" role="progressbar" style="width: <?php echo esc_attr( $listing_quality_data['ratingReviewScore'] ); ?>%;" aria-valuenow="<?php echo esc_attr( $listing_quality_data['ratingReviewScore'] ); ?>" aria-valuemin="0" aria-valuemax="5">
		</div>
	</div>
</div>

<div class="col-2">
 <p><b><?php echo esc_attr( $listing_quality_data['ratingReviewScore'] ); ?>%</b></p>
</div>
</div>

<hr>



<div class="row">

	<div class="col-7">
		<h5> 
			Post-Purchase Quality
		</h5>
		<p class="pro-card-p text-muted"><?php echo esc_attr( $listing_quality_data['defectRatio'] ); ?> % of your catalog has issues</p>

	</div>

	<div class="col-5">
		<h4 class="text-danger mx-4"> 
		   <?php echo esc_attr( $listing_quality_data['itemDefectCnt'] ); ?>
	   </h4>
	   <p class="pro-card-p">
		items with issues
	</p>

</div>

</div>




</div>
<div class="modal-footer">
	<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>
