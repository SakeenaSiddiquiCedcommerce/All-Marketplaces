<?php

$ced_walmart_insight_file = CED_WALMART_DIRPATH . 'admin/insights/class-ced-walmart-insights.php';

if ( file_exists( $ced_walmart_insight_file ) ) {
	include_once $ced_walmart_insight_file;
}

$insight_obj                 = new Ced_Walmart_Insights();
$unpublished_item_count_data = $insight_obj->ced_walmart_unpublished_counts();


?>
<div class="card" style="height: 100%;">
	<div class="card-body">
		<img  src="<?php echo esc_url( CED_WALMART_URL . 'admin/images/Listing-Quality.png' ); ?>">
		<h5 class="card-title">Unpublished Items Status</h5>
		<p class="pro-card-p text-muted"> Show how many items are unpublished for each reason. </p>
		<div class="row">
			<div class="col-12"> 
				<a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemCountModal">View Counts</a>
			</div>
<!-- 			<div class="col-5"> 
				<a href="#" class="btn btn-primary" id="ced_show_unpublished_items" data-bs-toggle="modal" data-bs-target="#unpublished_items">View product info</a>
			</div> -->
		</div>
	</div>
</div>


<!-- Modal for Unpublished Item count and price -->

<div class="modal fade" id="itemCountModal" tabindex="-1" aria-labelledby="itemCountModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">

			<div class="bg-dark w-100 p-3">
				<h4 class="text-white">Unpublished Item Counts</h4>
				<span class="text-white"> Data of past 30 days</span>
			</div>

			<div class="modal-body-data modal-body">
				<table class="table table-sm table-borderless">
					<thead>
						<th class="text-muted text-center"> ISSUES </th>
						<th class="text-muted text-center"> ITEMS AFFECTED </th>
						<th class="text-muted text-center"> UNPUBLISHED VALUE </th>
					</thead>
					<tbody>
						<?php
						if ( is_array( $unpublished_item_count_data ) && ! empty( $unpublished_item_count_data ) ) {
							foreach ( $unpublished_item_count_data as $key ) {
								$number = $key['unpublishedValue'];
								if ( $number < 1000000 ) {
									$number_formatted = number_format( $number ) . ' K';
								} elseif ( $number < 1000000000 ) {
									$number_formatted = number_format( $number / 1000000, 1 ) . ' M';
								} else {
									$number_formatted = number_format( $number / 1000000000, 1 ) . ' B';
								}
								echo "<tr><td class='text-center'>" . esc_attr( $key['unpublishedReasonCode'] ) . "</td><td class='text-center'>" . esc_attr( $key['unpublishedCount'] ) . "</td><td class='text-center'>$ " . esc_attr( $number_formatted ) . '</td> </tr>';
							}
						}
						?>

					</tbody></table>

				</div>
			</div>
		</div>
	</div>




	<!-- Modal for listing products  -->


	<div class="modal fade" id="unpublished_items" tabindex="-1" aria-labelledby="unpublished_items" aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable ced-product-modal-lg">
			<div class="modal-content">

				<div class="bg-dark w-100 p-3">
					<h4 class="text-white">Unpublished Items Breakdown</h4>
				</div>

				<div class="modal-body-data modal-body modal-body-unpublished">

				</div>
			</div>
		</div>
