<?php
/**
 * Category Mapping
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_good_market_header();
$feed_id = isset( $_GET['feed_id'] ) ? sanitize_text_field( $_GET['feed_id'] ) : 0;
require_once GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/lib/class-ced-good_market_lib.php';
$send_request_order_obj = new Ced_Good_Market_Request();
$bulk_data_id['query']  = 'query pendingBulkresponse($job_id: String!) {
    pendingBulkresponse(job_id: $job_id) {
        job_id
        success
        job_status
        error_result{product_sku
            message
        }
        product_ids{product_sku
            product_id
        }
    }
}';
// $feed_id = '89c42ee7-58c2-4f8a-b732-ddeca22a6500';
$bulk_data_id['variables']['job_id'] = $feed_id;
$feed_status_response                = $send_request_order_obj->good_market_post( json_encode( $bulk_data_id ) );
?>
		<div class="ced_good_market_wrap ced_good_market_wrap_extn">
			<div>
				<div class="ced_good_market_heading">
					<?php echo esc_html( get_instuction_html() ); ?>
					<div class="ced_good_market_child_element default_modal">
						<ul type="disc">
							<li><?php echo esc_html( 'Your current Feed ID - ' . $feed_id . '.' ); ?></li>
							<li><?php echo esc_html_e( 'The feed details are showing here' ); ?></li>
						</ul>
					</div>
				</div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<?php



						 //print_r(json_decode($feed_status_response));
						if ( isset( $feed_status_response ) && ! empty( $feed_status_response ) ) {
							$feed_status_response = json_decode( $feed_status_response, true );
							if ( ! isset( $feed_status_response['data']['pendingBulkresponse']['success'] ) ) {
								echo '<div class="good_market_feeds_no_error"> Feed <b>' . esc_attr( $feed_id ) . '</b> is in processing state, please wait and reload this page after sometime.
</div>';
								return;
							}
							echo '<table class="wp-list-table widefat fixed striped">';
							echo '<tbody>';
							echo '<tr><th>Serial No.</th><th>Product Sku</th><th>Message</th><th>Status</th></tr>';
								$count = 1;
							if ( isset( $feed_status_response['data']['pendingBulkresponse']['error_result'] ) && ! empty( $feed_status_response['data']['pendingBulkresponse']['error_result'] ) ) {
								$feed_status_response_error = $feed_status_response['data']['pendingBulkresponse']['error_result'];
								foreach ( $feed_status_response_error as $key => $val ) {
									$sku     = $val['product_sku'];
									$message = $val['message'];
									echo '<tr><td>' . esc_attr( $count ) . '</td><td>' . esc_attr( $sku ) . '</td><td>' . esc_attr( $message ) . '</td><td>Unsuccessful</td></tr>';
									$count++;
								}
							}
							if ( isset( $feed_status_response['data']['pendingBulkresponse']['product_ids'] ) && ! empty( $feed_status_response['data']['pendingBulkresponse']['product_ids'] ) ) {
								$feed_status_response_updated_products = $feed_status_response['data']['pendingBulkresponse']['product_ids'];
								foreach ( $feed_status_response_updated_products as $key => $val ) {
									$sku     = $val['product_sku'];
									$message = 'Product Updated';
									echo '<tr><td>' . esc_attr( $count ) . '</td><td>' . esc_attr( $sku ) . '</td><td>' . esc_attr( $message ) . '</td><td>Successful</td></tr>';
									$count++;
								}
							}
							echo '</tbody>';
							echo '</table>';
						}
						die;
						?>
						
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>