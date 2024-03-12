<?php
$feed_handle = isset( $_GET['feedID'] ) ? sanitize_text_field( wp_unslash( $_GET['feedID'] ) ) : '';
$page_id     = isset( $_GET['auth_page'] ) ? sanitize_text_field( wp_unslash( $_GET['auth_page'] ) ) : '';

if ( ! empty( $page_id ) ) {
		$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', true );
		$bussiness_id         = array_keys( $catalog_and_page_id )[0];
		$connected_catalog_id = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
}

if ( ! empty( $feed_handle ) ) {
	$feed_action = 'webapi/rest/v1/product/batch/status';
	$parameters  = array(
		'catalog_id' => $connected_catalog_id,
		'page_id'    => $page_id,
		'handle'     => $feed_handle,
	);
	$fileName    = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
	include_once $fileName;
	$ced_fmcw_send_request = new Class_Ced_Fmcw_Send_Http_Request();

	$response      = $ced_fmcw_send_request->get_request( $feed_action, $parameters );
	$feed_response = array();
	if ( isset( $response['success'] ) && true == $response['success'] ) {
		$feed_data = isset( $response['data'][0] ) ? $response['data'][0] : array();
		if ( 'finished' == $feed_data['status'] ) {

			$social_fb_warnings = array();
			$social_fb_errors   = array();
			if ( is_array( $feed_data['warnings'] ) && ! empty( $feed_data['warnings'] ) ) {
				$feeds_warning_products_response_data = array();
				foreach ( $feed_data['warnings'] as $key_data => $value_data ) {
					$feeds_warning_products_response_data[ $value_data['id'] ][] = $value_data['message'];
				}
			}
			if ( is_array( $feed_data['errors'] ) && ! empty( $feed_data['errors'] ) ) {
				$feeds_error_products_response_data = array();
				foreach ( $feed_data['errors'] as $key_data_error => $value_data_error ) {
					$feeds_error_products_response_data[ $value_data_error['id'] ][] = $value_data_error['message'];
				}
			}
		}

		$feed_response['success']                       = true;
		$feed_response['data'][0]['status']             = $feed_data['status'];
		$feed_response['data'][0]['warnings']           = $feeds_warning_products_response_data;
		$feed_response['data'][0]['errors']             = $feeds_error_products_response_data;
		$feed_response['data'][0]['errors_total_count'] = isset( $feed_data['errors_total_count'] ) ? $feed_data['errors_total_count'] : '';
	} else {
		$html_for_pending_status  = '<div class="ced_fmcw_single_feed_content">';
		$html_for_pending_status .= '<div class="ced_fmcw_single_feed_header">';
		$html_for_pending_status .= '<h5>Feed Response</h5></div>';
		$html_for_pending_status .= '<div class="ced_facebook_feed_pending_wrapper">';
		$html_for_pending_status .= 'Feed is in pending status please refersh this page again after few minutes .....';
		$html_for_pending_status .= '</div></div>';
		print_r( $html_for_pending_status );

	}
}
// print_r($feed_response); die('ppp');
if ( is_array( $feed_response ) && ! empty( $feed_response ) ) {
	global $wpdb;
	$updated_status   = $wpdb->get_results( $wpdb->prepare( "UPDATE {$wpdb->prefix}ced_fb_feeds_status SET `feed_status` = %d WHERE `feed_id` = %s", 1, $feed_handle ), 'ARRAY_A' );
	$products_in_feed = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );

	if ( isset( $feed_response['success'] ) && $feed_response['success'] ) {
		if ( isset( $feed_response['data'][0] ) ) {
			foreach ( $feed_response['data'] as $key3 => $feed_data ) {
				if ( isset( $feed_data['status'] ) && 'finished' == $feed_data['status'] ) {
					if ( isset( $feed_data['errors_total_count'] ) && 0 == $feed_data['errors_total_count'] ) {
						$products_in_feed = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
						$feed_product_ids = $products_in_feed[ $feed_handle ];
						foreach ( $feed_product_ids as $key => $Id ) {

							update_post_meta( $Id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id, 'true' );
							delete_post_meta( $Id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id );
							delete_post_meta( $Id, 'ced_fmcw_products_warning_' . $connected_catalog_id );
							delete_post_meta( $Id, 'ced_fmcw_product_with_errors_' . $connected_catalog_id );
							delete_post_meta( $Id, 'ced_fmcw_product_errors_' . $connected_catalog_id );

						}
					} else {
						if ( is_array( $feed_data['errors'] ) && ! empty( $feed_data['errors'] ) ) {
							$social_fb_error_ids = array();
							foreach ( $feed_data['errors'] as $product_id => $social_fb_error ) {
								$product_errors_lists   = array();
								$product_errors_lists[] = array(
									'severity' => 'error',
									'message'  => $social_fb_error,
								);
								$product_parent_id      = wp_get_post_parent_id( $product_id );
								if ( ! empty( $product_parent_id ) ) {
									$product_id = $product_parent_id;
								}
								$social_fb_error_ids[] = $product_id;
								update_post_meta( $product_id, 'ced_fmcw_product_errors_' . $connected_catalog_id, $product_errors_lists );
								delete_post_meta( $product_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id );
								update_post_meta( $product_id, 'ced_fmcw_product_with_errors_' . $connected_catalog_id, 'yes' );
								delete_post_meta( $product_id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id, 'true' );
							}
							$social_fb_error_ids = array_unique( $social_fb_error_ids );
							$products_in_feed    = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
							$feed_product_ids    = $products_in_feed[ $feed_handle ];
							foreach ( $feed_product_ids as $key => $Id ) {
								if ( ! in_array( $Id, $social_fb_error_ids ) ) {
									update_post_meta( $Id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id, 'true' );
									delete_post_meta( $Id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id );
									delete_post_meta( $Id, 'ced_fmcw_product_with_errors_' . $connected_catalog_id );
								}
							}
						}
					}

					if ( is_array( $feed_data['warnings'] ) && ! empty( $feed_data['warnings'] ) ) {
						foreach ( $feed_data['warnings'] as $product_id => $social_fb_error ) {

							$product_parent_id = wp_get_post_parent_id( $product_id );
							if ( ! empty( $product_parent_id ) ) {
								$product_id = $product_parent_id;
							}
							$product_warning_lists   = array();
							$product_warning_lists[] = array(
								'severity' => 'warning',
								'message'  => $social_fb_error,
							);
							update_post_meta( $product_id, 'ced_fmcw_products_warning_' . $connected_catalog_id, $product_warning_lists );
							delete_post_meta( $product_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id );
						}
					}
				} else {
					$new_upload_feed_handles[ $key ][] = $handle;
					continue;
				}
			}
		}
	}
}
if ( is_array( $feed_response ) && ! empty( $feed_response ) ) {
	if ( isset( $feed_response['success'] ) && $feed_response['success'] ) {
		if ( isset( $feed_response['data'][0] ) ) {
			?>
						<div class="ced_fmcw_single_feed_content">
							<div class="ced_fmcw_single_feed_header">
								<h5><?php esc_html_e( 'Feed Response', 'facebook-marketplace-connector-for-woocommerce' ); ?></h5>
						</div>
				<?php
				foreach ( $feed_response['data'] as $key => $feed_data ) {
					// print_r($feed_data);
					?>
							<div class="ced_fmcw_single_feed_status_heading">
								<h6 class="ced_fmcw_single_feed_heading_status_title">Feed Status : <?php echo esc_attr( $feed_data['status'] ); ?></h6>
								<dl>
								<dt class="ced_fmcw_send_request_heading">
									Product Errors
								</dt>
							<?php
							if ( is_array( $feed_data['errors'] ) && ! empty( $feed_data['errors'] ) ) {
								foreach ( $feed_data['errors'] as $social_fb_error_key => $social_fb_error_value ) {
									echo '<dd><b>Product Name : </b>' . esc_attr( get_the_title( $social_fb_error_key ) ) . '</br>';
									foreach ( $social_fb_error_value as $key => $values ) {
										echo '<b> Message : </b>' . esc_attr( $values );
									}
									echo '</dd>';
								}
							} else {
								echo '<dd>No Errors to show</dd>';
							}
							?>
								<dt class="ced_fmcw_send_request_heading-wraning">
									Product Warnings
								</dt>
							<?php
							if ( is_array( $feed_data['warnings'] ) && ! empty( $feed_data['warnings'] ) ) {
								foreach ( $feed_data['warnings'] as $warning_key => $warning_value ) {
									echo '<dd><b>Product Name : </b>' . esc_attr( get_the_title( $warning_key ) ) . '</br>';
									foreach ( $warning_value as $key => $values ) {
										echo '<b> Message : </b>' . esc_attr( $values ) . '</br>';
									}
									echo '</dd>';
								}
							} else {
								echo '<dd>No Errors to show</dd>';
							}
							?>
							</dl>
							</div>
						<?php
				}
				echo '</div>';
		}
	}
}

