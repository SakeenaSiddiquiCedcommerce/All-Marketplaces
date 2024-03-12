<?php

$ced_walmart_insight_file = CED_WALMART_DIRPATH . 'admin/insights/class-ced-walmart-insights.php';

if ( file_exists( $ced_walmart_insight_file ) ) {
	include_once $ced_walmart_insight_file;
}

$insight_obj           = new Ced_Walmart_Insights();
$pro_seller_badge_data = $insight_obj->ced_walmart_pro_seller_badge_data();
$trueSign              = "<i class='fa fa-check-circle' style='color:green;' aria-hidden='true'></i>";
$falseSign             = "<i class='fa fa-times-circle' style='color:red; ' aria-hidden='true'></i>";




?>




<div class="card" style="height: 100%;">
	<div class="card-body">
		<img src="<?php echo esc_url( CED_WALMART_URL . 'admin/images/Pro-Seller.png' ); ?>"><h5 class="card-title">Become a Pro Seller <i class="fa fa-check-circle" aria-hidden="true"></i></h5> 
		<p class="pro-card-p text-muted">Stand out to customers and get rewarded.</p>
		<?php
		if ( isset( $pro_seller_badge_data ) && ! empty( $pro_seller_badge_data ) ) {

			?>
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#prosellermodal" >View progress</button>

			<?php
		} else {
			?>
			<button type="button" class="btn btn-danger">No data available , click Update Insights</button>

		<?php } ?>


	</div>
</div>


<!-- Modal For Pro Seller Badge Data -->

<div class="modal fade mt-3" id="prosellermodal" tabindex="-1" aria-labelledby="prosellermodal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-lg">
		<div class="modal-content">

			<div class="bg-dark
			w-100 p-3"><h4 class="text-white">Pro Seller Status</h4>

		</div>
		<div class="bg-lighten-xl"><h4>Become a Pro Seller <i class="fa fa-check-circle" aria-hidden="true"></i></h4>  
			<span>Badge Since : <?php echo esc_attr( substr( $pro_seller_badge_data['badgedSince'], 0, strpos( $pro_seller_badge_data['badgedSince'], 'T' ) ) ); ?> </span>

			<div class="row">
				<div class="col-3"> 
					<span>Has Badge : <?php print_r( $pro_seller_badge_data['hasBadge'] ? $trueSign : $falseSign ); ?> </span>
				</div>

				<div class="col-3"> 
					<span>Is Eligible : <?php print_r( $pro_seller_badge_data['isEligible'] ? $trueSign : $falseSign ); ?> </span>
				</div>
				<div class="col-3"> 
					<span>Is Prohibited : <?php print_r( $pro_seller_badge_data['isProhibited'] ? $trueSign : $falseSign ); ?> </span>
				</div>
			</div>
			<?php
			if ( $pro_seller_badge_data['badgeStatus'] ) {
				echo '<span>Badge Status :' . esc_attr( $pro_seller_badge_data['badgeStatus'] ) . '</span>';
			}
			?>
			<div class="progress mt-2">
				<div class="progress-bar" role="progressbar" style="width:<?php echo esc_attr( $pro_seller_badge_data['healthyCountPercentage'] ); ?>%" aria-valuenow="<?php echo esc_attr( $pro_seller_badge_data['healthyCount'] ); ?>" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $pro_seller_badge_data['criteriaCount'] ); ?>">
					<?php echo esc_attr( $pro_seller_badge_data['healthyCount'] ); ?> / <?php echo esc_attr( $pro_seller_badge_data['criteriaCount'] ); ?> criteria met
				</div>
			</div>
			<p class="mt-2">Meet all the criteria and qualifications* below to unlock incredible benefits and stand out to your customers.<a target="_blank" class="text-decoration-none" href="https://sellerhelp.walmart.com/seller/s/guide?article=000009472">Learn more</a>
			</p>
		</div>
		<div class="modal-body">
			<table class="table table-striped">
				<thead>
					<th> MEETS CRITERIA </th>
					<th class='text-center'> PASS / FAIL </th>
				</thead> 
				<tbody>
					<?php
					foreach ( $pro_seller_badge_data['meetsCriteria'] as $key => $value ) {
						$signForCriteria = $value ? $trueSign : $falseSign;
						echo '<tr>';
						echo '<td>' . esc_attr( ucwords( $key ) ) . '</td>';
						print_r( "<td class='text-center'>" . $signForCriteria . ' </td>' );
						echo '</tr>';
					}

					?>
				</tbody>
			</table>
			<table class="table table-striped">
				<thead>
					<th> CRITERIA </th>
					<th class='text-center'> CURRENT MATRICS </th>
				</thead> 
				<tbody>
					<?php
					foreach ( $pro_seller_badge_data['criteriaData'] as $key => $value ) {
						$toolTip = '';
						foreach ( $pro_seller_badge_data['recommendations'] as $key_recommendations => $value_recommendations ) {
							if ( $key == $key_recommendations ) {
								$toolTip = "<span data-bs-toggle='tooltip' title='" . $value_recommendations . "'> <i class='fa fa-info-circle' style='color:red;'></i> </span>";
							}
						}
						echo '<tr>';
						print_r( '<td>' . ucwords( $key ) . $toolTip . ' </td>' );
						echo "<td class='text-center'>" . esc_attr( $value ) . ' </td>';
						echo '</tr>';
					}

					?>
				</tbody>
			</table>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		</div>
	</div>
</div>
</div>



