<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://woocommerce.com/vendor/cedcommerce/
 * @since      1.0.0
 *
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/admin
 */
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Facebook_Marketplace_Connector_For_Woocommerce
 * @subpackage Facebook_Marketplace_Connector_For_Woocommerce/admin
 */
class Facebook_Marketplace_Connector_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_filter( 'cron_schedules', array( $this, 'my_fb_cron_schedules' ) );
		add_action( 'admin_init', array( $this, 'ced_fb_add_schedulers' ) );
		add_action( 'ced_fmcw_feed_process', array( $this, 'ced_fmcw_get_product_feed_status' ) );
		add_action( 'wp_ajax_ced_fmcw_feed_process', array( $this, 'ced_fmcw_get_product_feed_status' ) );
		add_action( 'wp_ajax_nopriv_ced_fmcw_feed_process', array( $this, 'ced_fmcw_get_product_feed_status' ) );
		add_action( 'wp_ajax_ced_fb_write_error_log', array( $this, 'export_error_log' ) );
		add_action( 'wp_ajax_ced_fb_write_uploaded_log', array( $this, 'export_uploaded_log' ) );
		add_action( 'wp_ajax_ced_get_errors_for_product', array( $this, 'ced_get_errors_for_product' ) );
		add_action( 'ced_fmcw_order_sync_scheduler_job', array( $this, 'ced_facebook_get_orders_function' ) );
		add_action( 'ced_fmcw_product_sync_scheduler_job', array( $this, 'ced_fmcw_product_inventory_sync' ) );
		add_action( 'admin_notices', array( $this, 'ced_fmcw_admin_notices' ) );
		add_action( 'wp_ajax_ced_fmcw_save_active_pages', array( $this, 'ced_fmcw_save_active_pages' ) );
		add_action( 'wp_ajax_ced_fmcw_product_sync_scheduler_job', array( $this, 'ced_fmcw_product_inventory_sync' ) );
		add_action( 'wp_ajax_nopriv_ced_fmcw_product_sync_scheduler_job', array( $this, 'ced_fmcw_product_inventory_sync' ) );
		add_action( 'admin_init', array( $this, 'ced_fmcw_get_save_data_while_connect_to_FB' ) );

	}
	public function ced_decodeToken( $token, $check = true, $key = false ) {
		try {

			if ( count( explode( '.', $token ) ) > 2 ) {
				list($header, $payload, $signature) = explode( '.', $token );
				$header                             = json_decode( base64_decode( $header ), true );
				if ( ! $key ) {
					$key = '';
					if ( 'RS256' == $header['alg'] ) {
						$key = file_get_contents( $this->dir . 'connector.pub' );
					}
					if ( 'HS256' == $header['alg'] ) {
						$key = file_get_contents( $this->dir . 'connector.pem' );
					}
				}
				$jwt    = JWT::decode( $token, new Key( $key, $header['alg'] ) );
				$jwt    = json_decode( json_encode( $jwt ), 1 );
				$result = $jwt;
			} else {
				$result = array(
					'success' => false,
					'code'    => 'invalid_token',
					'message' => 'Token is not valid anymore',
				);
			}
		} catch ( Exception $e ) {
			$result = $e->getMessage();
		}
		return $result;
	}

	public function ced_fmcw_get_save_data_while_connect_to_FB() {
		if ( isset( $_GET['data'] ) && isset( $_GET['page'] ) && 'ced_fb' == $_GET['page'] && isset( $_GET['success'] ) && isset( $_GET['marketplace'] ) && 'meta' == $_GET['marketplace'] ) {
			$token_data = sanitize_text_field( $_GET['data'] );
			update_option( 'ced_get_after_during_on_boarding_to_meta', $token_data );
			$registration_data     = get_option( 'ced_fmcw_registered_with_cedcommerce', array() );
			$publicKey             = isset( $registration_data['reg_public_key'] ) ? $registration_data['reg_public_key'] : '';
			$created_user_response = get_option( 'ced_fmcw_user_create_response' );
			$apps_private_key      = $created_user_response['apps'][0]['private_key'];
			$data_private_key      = $created_user_response['private_key'];
			$result                = $this->ced_decodeToken( $token_data, true, $publicKey );
			// $result = get_option('ced_fmcw_connected_fb_response_and_decoded_token_response', true);
			if ( ! empty( $result ) && is_array( $result ) ) {
				update_option( 'ced_fmcw_connected_fb_response_and_decoded_token_response', $result );
				$shop_id = isset( $result['data']['shop_id'] ) ? $result['data']['shop_id'] : '';
				update_option( 'ced_fmcw_fb_account_details', $result );
				update_option( 'ced_fmcw_fb_shop_id', $shop_id );

				update_option( 'ced_fmcw_fb_account_connected', true );

				$redirect_url = admin_url( 'admin.php?page=ced_fb' );
				header( "Location: $redirect_url" );
			}
		}
	}
	public function ced_reset_facebook_entire_setup() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			delete_option( 'ced_fmcw_merchant_page_authenticated' );
			delete_option( 'ced_fmcw_fb_account_details' );
			delete_option( 'ced_fmcw_fb_cms_settings' );
			delete_option( 'ced_fmcw_registered_with_cedcommerce' );
			delete_option( 'ced_fmcw_fb_account_connected' );
			delete_option( 'ced_fmcw_setup_completed' );
			delete_option( 'ced_fmcw_fb_shop_id' );
			delete_option( 'ced_fmcw_user_registration_data' );
			delete_option( 'ced_fmcw_fbe_data' );
			delete_option( 'ced_catalog_saved' );
			echo json_encode(
				array(
					'status'       => 'success',
					'redirect_url' => admin_url() . 'admin.php?page=ced_fb',
				)
			);
		}
		wp_die();
	}
	public function ced_facebook_get_orders_function() {
		$file_order = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-order.php';
		if ( file_exists( $file_order ) ) {
			include_once $file_order;
		}

			$order_obj = new Class_CedFacebookOrders();
			$order_obj->ced_facebook_get_the_orders();
	}
	public function ced_fmcw_save_active_pages() {

		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$active_pages    = isset( $sanitized_array['active_pages'] ) ? wp_unslash( $sanitized_array['active_pages'] ) : array();
			update_option( 'ced_fmcw_active_merchant_pages', $active_pages );
		}
		wp_die();
	}
	public function ced_get_errors_for_product() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', true );
			$bussiness_id         = array_keys( $catalog_and_page_id )[0];
			$connected_catalog_id = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
			$product_id           = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
			if ( ! empty( $product_id ) ) {
				$product_title = get_the_title( $product_id );
				$error_arr     = get_post_meta( $product_id, 'ced_fmcw_product_errors_warning_' . $connected_catalog_id, true );
				$errors_data   = get_post_meta( $product_id, 'ced_fmcw_product_errors_' . $connected_catalog_id, true );
				$warning_data  = get_post_meta( $product_id, 'ced_fmcw_products_warning_' . $connected_catalog_id, true );
				$errors        = '';
				$warnings      = '';
				if ( ! empty( $errors_data[0]['message'] ) && is_array( $errors_data[0]['message'] ) ) {
					foreach ( $errors_data[0]['message'] as $key => $errorsLists ) {
						$errors .= '<li>' . $errorsLists . '</li>';
					}
				} else {
					$errors = '<li>No Errors to show</li>';
				}
				if ( ! empty( $warning_data[0]['message'] ) && is_array( $warning_data[0]['message'] ) ) {
					foreach ( $warning_data[0]['message'] as $key => $warningLists ) {
						$warnings .= '<li>' . $warningLists . '</li>';
					}
				} else {
					$warnings = '<li>No Warnings to show</li>';
				}
				if ( empty( $errors ) ) {
					$errors = '<li>No Errors to show</li>';
				}
				if ( empty( $warnings ) ) {
					$warnings = '<li>No Warnings to show</li>';
				}
				$responed_array = array(
					'title'   => $product_title,
					'error'   => $errors,
					'warning' => $warnings,
				);
				echo json_encode( $responed_array );
				wp_die();
			}
		}
	}
	/*
	 *  wp-admin/admin-ajax.php?action=ced_fmcw_product_sync_scheduler_job
	 */
	public function ced_fmcw_product_inventory_sync() {
		$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', true );
		$bussiness_id         = array_keys( $catalog_and_page_id )[0];
		$connected_catalog_id = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
		$products_to_sync     = get_option( 'ced_fmcw_chunk_product', array() );
		if ( empty( $products_to_sync ) ) {
			$products         = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => 'product',
					'meta_key'     => 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id,
					'meta_compare' => 'EXISTS',
					'fields'       => 'ids',
				)
			);
			$products_to_sync = array_chunk( $products, 100 );
		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			$this->ced_fmcw_product_update( $products_to_sync[0] );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_fmcw_chunk_product', $products_to_sync );
		}

	}
	public function ced_fmcw_admin_notices() {

		if ( ! session_id() ) {
			session_start();
		}

		if ( ! empty( $_SESSION ) ) {
			if ( '200' == $_SESSION['ced_fmcw_upload_status_code'] ) {
				$class = 'notice notice-success';
			} elseif ( '400' == $_SESSION['ced_fmcw_upload_status_code'] ) {
				$class = 'notice notice-error';
			}

			if ( isset( $_SESSION['ced_fmcw_upload_status_short_message'] ) ) {
				?>
				<div class="<?php echo esc_attr( $class ); ?>"><p><?php echo esc_attr( $_SESSION['ced_fmcw_upload_status_short_message'] ); ?></p></div>
				<?php
			}

			unset( $_SESSION['ced_fmcw_upload_status_code'] );
			unset( $_SESSION['ced_fmcw_upload_status_short_message'] );
			unset( $_SESSION['ced_fmcw_upload_status_long_message'] );
		}
	}

	public function ced_fb_add_schedulers() {
		if ( ! empty( $_REQUEST['marketplace'] ) && 'facebook' == $_REQUEST['marketplace'] ) {
			update_option( 'ced_fmcw_auth_post_data', $_REQUEST );
			update_option( 'ced_fmcw_setup_completed', 'yes' );
		}
		if ( ! wp_get_schedule( 'ced_fmcw_feed_process' ) ) {
			wp_schedule_event( time(), 'ced_fb_6min', 'ced_fmcw_feed_process' );
		}
	}
	public function my_fb_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['ced_fb_6min'] ) ) {
			$schedules['ced_fb_6min'] = array(
				'interval' => 6 * 60,
				'display'  => __( 'Once every 6 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_fb_10min'] ) ) {
			$schedules['ced_fb_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_fb_15min'] ) ) {
			$schedules['ced_fb_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_fb_30min'] ) ) {
			$schedules['ced_fb_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		return $schedules;
	}
	/*
	 *  wp-admin/admin-ajax.php?action=ced_fmcw_feed_process
	*/
	public function ced_fmcw_get_product_feed_status() {
		$catalog_and_page_id       = get_option( 'ced_fmcw_catalog_and_page_id', true );
		$bussiness_id              = array_keys( $catalog_and_page_id )[0];
		$connected_catalog_id      = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
		$upload_feed_handles       = get_option( 'ced_fmcw_upload_update_feed_handles_' . $connected_catalog_id, array() );
		$upload_feed_handles       = get_option( 'ced_fmcw_all_product_feeds_' . $connected_catalog_id, array() );
		$products_in_feed          = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
		$delete_feed_handles       = get_option( 'ced_fmcw_delete_feed_handles_' . $connected_catalog_id, array() );
		$call_for_get_shop_details = get_option( 'ced_fmcw_stord_whole_store_data' );
		$connected_page            = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';

		global $wpdb;
		$prefix              = $wpdb->prefix;
		$table_name          = $prefix . 'ced_fb_feeds_status';
		$sql                 = "SELECT * from $table_name WHERE `feed_status`='0'";
		$feeds_data_response = $wpdb->get_results( $sql, 'ARRAY_A' );
		$handle              = $feeds_data_response[0]['feed_id'];  // first inserted data with feed id
		if ( ! empty( $handle ) ) {
			$new_upload_feed_handles = array();
			$feed_action             = 'webapi/rest/v1/product/batch/status';
			$parameters              = array(
				'catalog_id' => $connected_catalog_id,
				'page_id'    => $connected_page,
				'handle'     => $handle,
			);

			$fileNmae = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
			include_once $fileNmae;
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
				$feed_response['data'][0]['handle']             = $handle;
				$feed_response['data'][0]['warnings']           = $feeds_warning_products_response_data;
				$feed_response['data'][0]['errors']             = $feeds_error_products_response_data;
				$feed_response['data'][0]['errors_total_count'] = isset( $feed_data['errors_total_count'] ) ? $feed_data['errors_total_count'] : '';

			}
			print_r( $feed_response );
			echo 'hello';
			if ( is_array( $feed_response ) && ! empty( $feed_response ) ) {

				$products_in_feed = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
				if ( isset( $feed_response['success'] ) && $feed_response['success'] ) {

					if ( isset( $feed_response['data'][0] ) ) {
						foreach ( $feed_response['data'] as $key3 => $feed_data ) {
							if ( isset( $feed_data['status'] ) && 'finished' == $feed_data['status'] ) {
								$feed_handle = isset( $feed_data['handle'] ) ? $feed_data['handle'] : '';
								global $wpdb;
								$updated_status = $wpdb->get_results( $wpdb->prepare( "UPDATE {$wpdb->prefix}ced_fb_feeds_status SET `feed_status` = %d WHERE `feed_id` = %s", 1, $feed_handle ), 'ARRAY_A' );
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
											update_post_meta( $product_id, 'ced_fmcw_product_with_errors_' . $connected_catalog_id, 'yes' );
											delete_post_meta( $product_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id );
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
										$social_fb_error_ids[] = $product_id;
										// update_post_meta( $product_id, 'ced_fmcw_products_warning_'.$connected_catalog_id, $product_warning_lists );
										// // 		update_post_meta( $product_id, 'ced_fmcw_product_with_errors_'.$connected_catalog_id, 'yes' );
										// delete_post_meta( $product_id, 'ced_fmcw_product_upload_submitted_'.$connected_catalog_id );
										// delete_post_meta( $product_id, 'ced_fmcw_uploaded_on_facebook_'.$connected_catalog_id, 'true' );
									}
									// $social_fb_error_ids = array_unique( $social_fb_error_ids );
									// $products_in_feed    = get_option( 'ced_fmcw_products_in_feed_'.$connected_catalog_id, array() );
									// $feed_product_ids    = $products_in_feed[ $feed_handle ];
									// foreach ( $feed_product_ids as $key => $Id ) {
									// if ( ! in_array( $Id, $social_fb_error_ids ) ) {
									// update_post_meta( $Id, 'ced_fmcw_uploaded_on_facebook_'.$connected_catalog_id, 'true' );
									// delete_post_meta( $Id, 'ced_fmcw_product_upload_submitted_'.$connected_catalog_id );
									// delete_post_meta( $Id, 'ced_fmcw_product_with_errors_'.$connected_catalog_id );
									// }
									// }
								}
							} else {
								$new_upload_feed_handles[ $key ][] = $handle;
								continue;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Facebook_Marketplace_Connector_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Facebook_Marketplace_Connector_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/facebook-marketplace-connector-for-woocommerce-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Facebook_Marketplace_Connector_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Facebook_Marketplace_Connector_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/facebook-marketplace-connector-for-woocommerce-admin.js?v=1.0.1', array( 'jquery' ), $this->version, false );
		$ajax_nonce     = wp_create_nonce( 'ced-fmcw-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
		);
		wp_localize_script( $this->plugin_name, 'ced_fmcw_admin_obj', $localize_array );
	}

	public function ced_fmcw_admin_menu() {

		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'CedCommerce', 'facebook-marketplace-connector-for-woocommerce' ), __( 'CedCommerce', 'facebook-marketplace-connector-for-woocommerce' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), 'dashicons-store', 12 );
			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );
				}
			}
		}
	}

	/**
	 * Facebook_Marketplace_Connector_For_Woocommerce_Admin ced_fmcw_add_marketplace_menus_to_array.
	 *
	 * @since 1.0.0
	 * @param array $menus Marketplace menus.
	 */
	public function ced_fmcw_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'Facebook',
			'slug'            => 'facebook-marketplace-connector-for-woocommerce',
			'menu_link'       => 'ced_fb',
			'instance'        => $this,
			'function'        => 'ced_facebook_accounts_page',
			'card_image_link' => CED_FMCW_URL . 'admin/images/fbmp.png',
		);
		return $menus;
	}

	/**
	 * Facebook_Marketplace_Connector_For_Woocommerce_Admin ced_marketplace_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_marketplace_listing_page() {

		$active_marketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
			require CED_FMCW_DIRPATH . 'admin/partials/marketplaces.php';
		}
	}

	/**
	 * Facebook_Marketplace_Connector_For_Woocommerce_Admin ced_facebook_accounts_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_facebook_accounts_page() {

		$is_registered           = ced_fmcw_check_if_already_registered();
		$is_fb_account_connected = ced_fmcw_check_fb_already_connected();

		$is_setup_completed = ced_fmcw_check_setup_completed();
		if ( 'yes' == $is_setup_completed ) {
			$file_name = CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-main.php';
			if ( file_exists( $file_name ) ) {
				include_once $file_name;
			}
		} else {
			if ( ! $is_registered ) {
				$file_accounts = CED_FMCW_DIRPATH . 'admin/partials/registration-view.php';
				// $file_accounts = CED_FMCW_DIRPATH . 'admin/partials/catalog-selection.php';
				if ( file_exists( $file_accounts ) ) {
					include_once $file_accounts;
				}
			} elseif ( ! $is_fb_account_connected ) {

				$file_accounts = CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-connect-fb-account.php';
				if ( file_exists( $file_accounts ) ) {
					include_once $file_accounts;
				}
			} else {

				// $file_accounts = CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-accounts.php';
				$file_accounts = CED_FMCW_DIRPATH . 'admin/partials/catalog-selection.php';

				if ( file_exists( $file_accounts ) ) {
					include_once $file_accounts;
				}
			}
		}
		/*
		include_once CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		$obj = new Class_Ced_Fmcw_Send_Http_Request();
		$obj->get_request();*/
	}

	public function ced_fmcw_authenticate_cms_page() {

		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$merchant_page_id = isset( $_POST['merchant_page_id'] ) ? sanitize_text_field( $_POST['merchant_page_id'] ) : '';
			$page_id          = isset( $_POST['page_id'] ) ? sanitize_text_field( $_POST['page_id'] ) : '';
			$catalog_id       = isset( $_POST['catalog_id'] ) ? sanitize_text_field( $_POST['catalog_id'] ) : '';
			if ( ! empty( $merchant_page_id ) ) {

				$fileName = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-store-api-handle.php';
				include_once $fileName;

				$store_api_handler = Class_Ced_Fmcw_Store_Api_Handle::get_instance();

				$authenticate_page_repsonse = $store_api_handler->authenticate_cms_page( $merchant_page_id, $page_id );
				if ( isset( $authenticate_page_repsonse['success'] ) && isset( $authenticate_page_repsonse['data']['success'] ) ) {

					$authenticated_pages = get_option( 'ced_fmcw_merchant_page_authenticated', array() );
					$catalog_and_page_id = get_option( 'ced_fmcw_catalog_and_page_id', array() );
					if ( empty( $authenticated_pages ) ) {
						$authenticated_pages = array();
					}

					$authenticated_pages[]                    = $merchant_page_id;
					$catalog_and_page_id[ $merchant_page_id ] = array(
						'catalog_id' => $catalog_id,
						'page_id'    => $page_id,
					);

					update_option( 'ced_fmcw_merchant_page_authenticated', $authenticated_pages );
					update_option( 'ced_fmcw_active_merchant_pages', $authenticated_pages );
					update_option( 'ced_fmcw_catalog_and_page_id', $catalog_and_page_id );

					echo json_encode(
						array(
							'status'  => '200',
							'message' => 'Authenticated',
						)
					);
					wp_die();
				}
			}
		}
		wp_die();
	}


	public function ced_fmcw_manage_product_bulk_action() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$action    = isset( $_POST['operation'] ) ? sanitize_text_field( $_POST['operation'] ) : '';
			$post_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$post_ids  = isset( $post_data['facebook_shopping_products_ids'] ) ? $post_data['facebook_shopping_products_ids'] : array();
			/*
			* Function part for Uploading products to Facebook Marketplace
			* @since 1.0.0
			*/
			if ( 'ced_fmcw_upload_to_facebook' == $action || 'ced_fmcw_update_on_facebook' == $action ) {
				if ( session_id() == '' ) {
					session_start();
				}
				$cms_settings               = get_option( 'ced_fmcw_fb_cms_settings', array() );
				$refresh_token              = get_option( 'ced_fmcw_refresh_token' );
				$call_for_get_shop_details  = get_option( 'ced_fmcw_stord_whole_store_data' );
				$connected_page             = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';
				$catalog_and_page_id        = get_option( 'ced_fmcw_catalog_and_page_id', true );
				$bussiness_id               = array_keys( $catalog_and_page_id )[0];
				$connected_catalog_id       = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
				$to_be_uploaded_product_ids = array();
				$to_be_updated_product_ids  = array();

				$product_data_to_be_uploaded   = array();
				$variable_upload_product_array = array();
				$variable_update_product_array = array();
				if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
					foreach ( $post_ids as $key => $product_id ) {
						$profile_data = $this->ced_fb_get_profile_assigned_data( $product_id );
						if ( get_post_meta( $product_id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id, true ) != 'true' ) {
							$_product = wc_get_product( $product_id );

							$is_variable                  = 0;
							$product                      = $_product->get_data();
							$to_be_uploaded_product_ids[] = $product_id;
							if ( $_product->get_type() == 'variable' ) {
								$is_variable                                  = 1;
								$variable_upload_product_array[ $product_id ] = $_product->get_children();
								continue;

							}
							$price = $product['regular_price'];
							if ( empty( $price ) ) {
								$price = get_post_meta( $product_id, 'woo_ua_opening_price', true );
							}

							$product_data_to_be_uploaded['data'][ $key ]['price'] = $price;

							$cat_ids = isset( $product['category_ids'] ) ? $product['category_ids'] : array();

							if ( ! empty( $cat_ids ) ) {
								foreach ( $cat_ids as $key1 => $value1 ) {
									$category     = get_term_meta( $value1, 'ced_fmcw_category_mapped', true );
									$categoryName = get_term_meta( $value1, 'ced_fmcw_category_mapped_name', true );

									if ( '' != $category ) {
										break;
									}
								}
							}

							$product_data_to_be_uploaded['data'][ $key ]['name']      = $product['name'];
							$product_data_to_be_uploaded['data'][ $key ]['brand']     = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_brand' );
							$product_data_to_be_uploaded['data'][ $key ]['mpn']       = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_mpn' );
							$product_data_to_be_uploaded['data'][ $key ]['gtin']      = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_gtin' );
							$product_data_to_be_uploaded['data'][ $key ]['gender']    = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_gender' );
							$product_data_to_be_uploaded['data'][ $key ]['age_group'] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_agegrp' );
							// $product_data_to_be_uploaded['data'][$key]["color"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_color' );
							// $product_data_to_be_uploaded['data'][$key]["material"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_material' );
							// $product_data_to_be_uploaded['data'][$key]["size"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_size' );
							$product_data_to_be_uploaded['data'][ $key ]['url']      = get_the_permalink( $product_id );
							$product_data_to_be_uploaded['data'][ $key ]['category'] = $categoryName;

							$stock = get_post_meta( $product_id, '_stock', true );
							if ( empty( $stock ) ) {
								$stock = 0;
							}

							$product_data_to_be_uploaded['data'][ $key ]['inventory'] = (int) $stock;

							$currency = get_option( 'woocommerce_currency', '' );
							if ( empty( $currency ) ) {
								$currency = 'USD';
							}
							$currency = 'USD';
							$product_data_to_be_uploaded['data'][ $key ]['currency'] = $currency;

							$product_data_to_be_uploaded['data'][ $key ]['source_id'] = "$product_id";
							// $product_data_to_be_uploaded['data'][$key]["group_id"] = $product_id;

							$stock_status = get_post_meta( $product_id, '_stock_status', true );
							$product_data_to_be_uploaded['data'][ $key ]['availability'] = '';
							if ( empty( $product_data_to_be_uploaded['data'][ $key ]['availability'] ) ) {
								if ( 'instock' == $stock_status || $stock > 0 ) {
									$product_data_to_be_uploaded['data'][ $key ]['availability'] = 'in stock';
									$product_data_to_be_uploaded['data'][ $key ]['inventory']    = (int) $stock;
								} else {
									$product_data_to_be_uploaded['data'][ $key ]['availability'] = 'out of stock';
								}
							}

							$product_data_to_be_uploaded['data'][ $key ]['condition'] = 'new';

							$description = preg_replace( '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/', '', $product['description'] );
							$product_data_to_be_uploaded['data'][ $key ]['description'] = $description;
							if ( isset( $product['sale_price'] ) && ! empty( $product['sale_price'] ) ) {
								$product_data_to_be_uploaded['data'][ $key ]['offer_price'] = $product['sale_price'];

								$sale_date_from = '';
								$sale_date_to   = '';
								if ( isset( $product['date_on_sale_from'] ) ) {
									$product_data_to_be_uploaded['data'][ $key ]['offer_price_start_date'] = $product['date_on_sale_from']->date;
								}
								if ( isset( $product['date_on_sale_to'] ) ) {
									$product_data_to_be_uploaded['data'][ $key ]['offer_price_end_date'] = $product['date_on_sale_to']->date;
								}
							}

							$product_data_to_be_uploaded['data'][ $key ]['main_image'] = get_the_post_thumbnail_url( $product_id );

							$attachment_ids = $product['gallery_image_ids'];
							if ( ! empty( $attachment_ids ) ) {
								$additionalImageLinks = array();
								$count                = 1;
								foreach ( $attachment_ids as $attachment_id ) {
									if ( $count >= 10 ) {
										break;
									}
									$additionalImageLinks[] = wp_get_attachment_url( $attachment_id );
									$count                  = ++$count;
								}

								if ( is_array( $additionalImageLinks ) && ! empty( $additionalImageLinks ) ) {
									$product_data_to_be_uploaded['data'][ $key ]['images'] = $additionalImageLinks;
								}

								// foreach ( $product['attributes'] as $attr_name => $attr_value ) {
								// $attr_name = strtolower( str_replace( 'pa_', '', $attr_name ) );

								// if ( 'color' == $attr_name || 'size' == $attr_name ) {
								// $product_data_to_be_uploaded['data'][ $key ]['variant_attributes'][] = $attr_name;
								// } else {
								// $product_data_to_be_uploaded['data'][ $key ]['variant_attributes'][] = $attr_name;
								// }

								// $attr_values = $attr_value->get_data();
								// $value_arr   = array();
								// foreach ( $attr_values['options'] as $key11 => $value11 ) {
								// $value_arr[] = ucfirst( get_term( $value11 )->name );
								// }
								// $product_data_to_be_uploaded['data'][ $key ][ $attr_name ] = implode( ',', $value_arr );

								// }

							}
						} else {
							$_product = wc_get_product( $product_id );

							$is_variable                 = 0;
							$to_be_updated_product_ids[] = $product_id;
							if ( $_product->get_type() == 'variable' ) {
								$is_variable                                  = 1;
								$variable_update_product_array[ $product_id ] = $_product->get_children();
								continue;

							}

							$product = $_product->get_data();

							$cat_ids = isset( $product['category_ids'] ) ? $product['category_ids'] : array();

							if ( ! empty( $cat_ids ) ) {
								foreach ( $cat_ids as $key1 => $value1 ) {
									$category     = get_term_meta( $value1, 'ced_fmcw_category_mapped', true );
									$categoryName = get_term_meta( $value1, 'ced_fmcw_category_mapped_name', true );

									if ( '' != $category ) {
										break;
									}
								}
							}

							$product_data_to_be_updated['data'][ $key ]['name']      = $product['name'];
							$product_data_to_be_updated['data'][ $key ]['brand']     = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_brand' );
							$product_data_to_be_updated['data'][ $key ]['mpn']       = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_mpn' );
							$product_data_to_be_updated['data'][ $key ]['gender']    = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_gender' );
							$product_data_to_be_updated['data'][ $key ]['age_group'] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_agegrp' );
							$product_data_to_be_updated['data'][ $key ]['gtin']      = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_gtin' );
							// $product_data_to_be_updated['data'][$key]["color"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_color' );
							// $product_data_to_be_updated['data'][$key]["material"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_material' );
							// $product_data_to_be_updated['data'][$key]["size"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_size' );
							$product_data_to_be_updated['data'][ $key ]['url']      = get_the_permalink( $product_id );
							$product_data_to_be_updated['data'][ $key ]['category'] = $categoryName;

							$stock = get_post_meta( $product_id, '_stock', true );
							if ( empty( $stock ) ) {
								$stock = 0;
							}

							$product_data_to_be_updated['data'][ $key ]['inventory'] = (int) $stock;

							$product_data_to_be_updated['data'][ $key ]['price'] = $product['regular_price'];

							$currency = get_option( 'woocommerce_currency', '' );
							if ( empty( $currency ) ) {
								$currency = 'USD';
							}
							// $currency = 'USD';
							$product_data_to_be_updated['data'][ $key ]['currency'] = $currency;

							$product_data_to_be_updated['data'][ $key ]['source_id'] = "$product_id";
							// $product_data_to_be_updated['data'][$key]["group_id"] = $product_id;

							$stock_status = get_post_meta( $product_id, '_stock_status', true );
							$product_data_to_be_updated['data'][ $key ]['availability'] = '';
							if ( empty( $product_data_to_be_updated['data'][ $key ]['availability'] ) ) {
								if ( 'instock' == $stock_status || $stock > 0 ) {
									$product_data_to_be_updated['data'][ $key ]['availability'] = 'in stock';
									$product_data_to_be_updated['data'][ $key ]['inventory']    = (int) $stock;
								} else {
									$product_data_to_be_updated['data'][ $key ]['availability'] = 'out of stock';
									$product_data_to_be_updated['data'][ $key ]['inventory']    = 0;
								}
							}
							$product_data_to_be_updated['data'][ $key ]['condition'] = 'new';
							$description = preg_replace( '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/', '', $product['description'] );
							$product_data_to_be_updated['data'][ $key ]['description'] = $description;

							if ( isset( $product['sale_price'] ) && ! empty( $product['sale_price'] ) ) {
								$product_data_to_be_updated['data'][ $key ]['offer_price'] = $product['sale_price'];

								$sale_date_from = '';
								$sale_date_to   = '';
								if ( isset( $product['date_on_sale_from'] ) ) {
									$product_data_to_be_updated['data'][ $key ]['offer_price_start_date'] = $product['date_on_sale_from']->date;
								}
								if ( isset( $product['date_on_sale_to'] ) ) {
									$product_data_to_be_updated['data'][ $key ]['offer_price_end_date'] = $product['date_on_sale_to']->date;
								}
							}

							$product_data_to_be_updated['data'][ $key ]['main_image'] = get_the_post_thumbnail_url( $product_id );

							$attachment_ids = $product['gallery_image_ids'];
							if ( ! empty( $attachment_ids ) ) {
								$additionalImageLinks = array();
								$count                = 1;
								foreach ( $attachment_ids as $attachment_id ) {
									if ( $count >= 10 ) {
										break;
									}
									$additionalImageLinks[] = wp_get_attachment_url( $attachment_id );
									$count                  = ++$count;
								}

								if ( is_array( $additionalImageLinks ) && ! empty( $additionalImageLinks ) ) {
									$product_data_to_be_updated['data'][ $key ]['images'] = $additionalImageLinks;
								}
							}

							/*
							foreach ( $product['attributes'] as $attr_name => $attr_value ) {
								$attr_name = strtolower( str_replace( 'pa_', '', $attr_name ) );
								if ( 'color' == $attr_name || 'size' == $attr_name ) {
									$product_data_to_be_updated['data'][ $key ]['variant_attributes'][] = $attr_name;
								} else {
									$product_data_to_be_updated['data'][ $key ]['variant_attributes'][] = $attr_name;
								}

								$attr_values = $attr_value->get_data();
								$value_arr   = array();
								foreach ( $attr_values['options'] as $key11 => $value11 ) {
									$value_arr[] = ucfirst( get_term( $value11 )->name );
								}
								$product_data_to_be_updated['data'][ $key ][ $attr_name ] = implode( ',', $value_arr );

							}*/
						}
					}

					if ( ! empty( $variable_upload_product_array ) ) {
						$product_data_to_be_uploaded['data'] = ! empty( $product_data_to_be_uploaded['data'] ) ? array_values( $product_data_to_be_uploaded['data'] ) : array();
						$index                               = count( $product_data_to_be_uploaded['data'] );
						foreach ( $variable_upload_product_array as $parent_id => $value ) {
							$parent_data = wc_get_product( $parent_id );
							$parent_data = $parent_data->get_data();
							foreach ( $value as $variation_position => $variation_id ) {
								$variation_product = wc_get_product( $variation_id );
								$variation_attr    = $variation_product->get_variation_attributes();
								$variation_data    = $variation_product->get_data();
								$cat_ids           = isset( $variation_data['category_ids'] ) ? $variation_data['category_ids'] : array();

								if ( ! empty( $cat_ids ) ) {
									foreach ( $cat_ids as $key1 => $value1 ) {
										$category     = get_term_meta( $value1, 'ced_fmcw_category_mapped', true );
										$categoryName = get_term_meta( $value1, 'ced_fmcw_category_mapped_name', true );

										if ( '' != $category ) {
											break;
										}
									}
								}
								$product_data_to_be_uploaded['data'][ $index ]['name']      = $parent_data['name'];
								$product_data_to_be_uploaded['data'][ $index ]['brand']     = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_brand' );
								$product_data_to_be_uploaded['data'][ $index ]['mpn']       = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_mpn' );
								$product_data_to_be_uploaded['data'][ $index ]['gtin']      = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_gtin' );
								$product_data_to_be_uploaded['data'][ $index ]['gender']    = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_gender' );
								$product_data_to_be_uploaded['data'][ $index ]['age_group'] = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_agegrp' );
								// $product_data_to_be_uploaded['data'][$index]["color"] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_color' );
								// $product_data_to_be_uploaded['data'][$index]["material"] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_material' );
								// $product_data_to_be_uploaded['data'][$index]["size"] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_size' );
								$product_data_to_be_uploaded['data'][ $index ]['url']      = get_the_permalink( $variation_id );
								$product_data_to_be_uploaded['data'][ $index ]['category'] = $categoryName;

								$stock          = get_post_meta( $variation_id, '_stock', true );
								$manage_stock   = get_post_meta( $parent_id, '_manage_stock', true );
								$v_manage_stock = get_post_meta( $variation_id, '_manage_stock', true );
								$stock_status   = get_post_meta( $parent_id, '_stock_status', true );
								if ( 'instock' == $stock_status && 'yes' == $manage_stock && 'no' == $v_manage_stock ) {
									$stock = get_post_meta( $parent_id, '_stock', true );
								}

								$product_data_to_be_uploaded['data'][ $index ]['inventory'] = $stock;

								$product_data_to_be_uploaded['data'][ $index ]['price'] = $variation_data['regular_price'];

								$currency = get_option( 'woocommerce_currency', '' );
								if ( empty( $currency ) ) {
									$currency = 'USD';
								}
								// $currency = 'USD';
								$product_data_to_be_uploaded['data'][ $index ]['currency'] = $currency;

								$product_data_to_be_uploaded['data'][ $index ]['source_id'] = "$variation_id";
								$product_data_to_be_uploaded['data'][ $index ]['group_id']  = "$parent_id";
								$product_data_to_be_uploaded['data'][ $index ]['position']  = $variation_position + 1;

								$stock_status = get_post_meta( $variation_id, '_stock_status', true );
								$product_data_to_be_uploaded['data'][ $index ]['availability'] = '';
								if ( empty( $product_data_to_be_uploaded['data'][ $index ]['availability'] ) ) {
									if ( 'instock' == $stock_status || $stock > 0 ) {
										$product_data_to_be_uploaded['data'][ $index ]['availability'] = 'in stock';
										$product_data_to_be_uploaded['data'][ $index ]['inventory']    = (int) $stock;
									} else {
										$product_data_to_be_uploaded['data'][ $index ]['availability'] = 'out of stock';
										$product_data_to_be_uploaded['data'][ $index ]['inventory']    = 0;
									}
								}

								$product_data_to_be_uploaded['data'][ $index ]['condition'] = 'new';
								// print_r($product_data_to_be_uploaded);
								// die('pppp');
								$description = preg_replace( '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/', '', $parent_data['description'] );
								$product_data_to_be_uploaded['data'][ $index ]['description'] = $description;

								if ( isset( $variation_data['sale_price'] ) && ! empty( $variation_data['sale_price'] ) ) {
									$product_data_to_be_uploaded['data'][ $index ]['offer_price'] = $variation_data['sale_price'];

									$sale_date_from = '';
									$sale_date_to   = '';
									if ( isset( $variation_data['date_on_sale_from'] ) ) {
										$product_data_to_be_uploaded['data'][ $index ]['offer_price_start_date'] = $variation_data['date_on_sale_from']->date;
									}
									if ( isset( $variation_data['date_on_sale_to'] ) ) {
										$product_data_to_be_uploaded['data'][ $index ]['offer_price_end_date'] = $variation_data['date_on_sale_to']->date;
									}
								}

								if ( ! empty( get_the_post_thumbnail_url( $variation_id ) ) ) {
									$product_data_to_be_uploaded['data'][ $index ]['main_image'] = get_the_post_thumbnail_url( $variation_id );
								} else {
									$product_data_to_be_uploaded['data'][ $index ]['main_image'] = get_the_post_thumbnail_url( $parent_id );
								}

								$attachment_ids = ! empty( $variation_data['gallery_image_ids'] ) ? $variation_data['gallery_image_ids'] : $parent_data['gallery_image_ids'];
								if ( ! empty( $attachment_ids ) ) {
									$additionalImageLinks = array();
									$count                = 1;
									foreach ( $attachment_ids as $attachment_id ) {
										if ( $count >= 10 ) {
											break;
										}
										$additionalImageLinks[] = wp_get_attachment_url( $attachment_id );
										$count                  = ++$count;
									}

									if ( is_array( $additionalImageLinks ) && ! empty( $additionalImageLinks ) ) {
										$product_data_to_be_uploaded['data'][ $index ]['images'] = $additionalImageLinks;
									}
								}
								foreach ( $variation_attr as $attr_name => $attr_value ) {
									// $attr_name = ucfirst(str_replace("attribute_pa_","",$attr_name));
									$attr_name = str_replace( 'attribute_pa_', '', $attr_name );
									if ( 'color' == $attr_name || 'size' == $attr_name ) {
										$product_data_to_be_uploaded['data'][ $index ]['variant_attributes'][] = $attr_name;

									} else {
										$product_data_to_be_uploaded['data'][ $index ]['variant_attributes'][] = $attr_name;
									}

									$product_data_to_be_uploaded['data'][ $index ][ $attr_name ] = ucfirst( $attr_value );

								}
								$index++;
							}
						}
					}

					if ( ! empty( $variable_update_product_array ) ) {
						$product_data_to_be_updated['data'] = ! empty( $product_data_to_be_updated['data'] ) ? array_values( $product_data_to_be_updated['data'] ) : array();
						$index                              = count( $product_data_to_be_updated['data'] );
						foreach ( $variable_update_product_array as $parent_id => $value ) {
							$parent_data = wc_get_product( $parent_id );
							$parent_data = $parent_data->get_data();
							foreach ( $value as $variation_position => $variation_id ) {
								$variation_product = wc_get_product( $variation_id );
								$variation_attr    = $variation_product->get_variation_attributes();
								$variation_data    = $variation_product->get_data();
								$cat_ids           = isset( $variation_data['category_ids'] ) ? $variation_data['category_ids'] : array();

								if ( ! empty( $cat_ids ) ) {
									foreach ( $cat_ids as $key1 => $value1 ) {
										$category     = get_term_meta( $value1, 'ced_fmcw_category_mapped', true );
										$categoryName = get_term_meta( $value1, 'ced_fmcw_category_mapped_name', true );

										if ( '' != $category ) {
											break;
										}
									}
								}
								$product_data_to_be_updated['data'][ $index ]['name']      = $parent_data['name'];
								$product_data_to_be_updated['data'][ $index ]['brand']     = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_brand' );
								$product_data_to_be_updated['data'][ $index ]['mpn']       = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_mpn' );
								$product_data_to_be_updated['data'][ $index ]['gtin']      = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_gtin' );
								$product_data_to_be_updated['data'][ $index ]['gender']    = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_gender' );
								$product_data_to_be_updated['data'][ $index ]['age_group'] = $this->fetch_meta_value_of_product( $variation_id, 'ced_fmcw_agegrp' );
								// $product_data_to_be_updated['data'][$index]["color"] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_color' );
								// $product_data_to_be_updated['data'][$index]["material"] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_material' );
								// $product_data_to_be_updated['data'][$index]["size"] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_size' );
								$product_data_to_be_updated['data'][ $index ]['url']      = get_the_permalink( $variation_id );
								$product_data_to_be_updated['data'][ $index ]['category'] = $categoryName;

								$stock = get_post_meta( $variation_id, '_stock', true );
								if ( empty( $stock ) ) {
									$stock = 0;
								}

								$product_data_to_be_updated['data'][ $index ]['inventory'] = $stock;

								$product_data_to_be_updated['data'][ $index ]['price'] = $variation_data['regular_price'];

								$currency = get_option( 'woocommerce_currency', '' );
								if ( empty( $currency ) ) {
									$currency = 'USD';
								}
								$currency = 'USD';
								$product_data_to_be_updated['data'][ $index ]['currency'] = $currency;

								$product_data_to_be_updated['data'][ $index ]['source_id'] = "$variation_id";
								$product_data_to_be_updated['data'][ $index ]['group_id']  = "$parent_id";
								$product_data_to_be_updated['data'][ $index ]['position']  = $variation_position + 1;

								$stock_status = get_post_meta( $variation_id, '_stock_status', true );
								$product_data_to_be_updated['data'][ $index ]['availability'] = '';
								if ( empty( $product_data_to_be_updated['data'][ $index ]['availability'] ) ) {
									if ( 'instock' == $stock_status || $stock > 0 ) {
										$product_data_to_be_updated['data'][ $index ]['availability'] = 'in stock';
										$product_data_to_be_updated['data'][ $index ]['inventory']    = (int) $stock;
									} else {
										$product_data_to_be_updated['data'][ $index ]['availability'] = 'out of stock';
										$product_data_to_be_updated['data'][ $index ]['inventory']    = 0;
									}
								}

								$product_data_to_be_updated['data'][ $index ]['condition'] = 'new';
								$description = preg_replace( '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/', '', $parent_data['description'] );
								$product_data_to_be_updated['data'][ $index ]['description'] = $description;

								if ( isset( $variation_data['sale_price'] ) && ! empty( $variation_data['sale_price'] ) ) {
									$product_data_to_be_updated['data'][ $index ]['offer_price'] = $variation_data['sale_price'];

									$sale_date_from = '';
									$sale_date_to   = '';
									if ( isset( $variation_data['date_on_sale_from'] ) ) {
										$product_data_to_be_updated['data'][ $index ]['offer_price_start_date'] = $variation_data['date_on_sale_from']->date;
									}
									if ( isset( $variation_data['date_on_sale_to'] ) ) {
										$product_data_to_be_updated['data'][ $index ]['offer_price_end_date'] = $variation_data['date_on_sale_to']->date;
									}
								}

								if ( ! empty( get_the_post_thumbnail_url( $variation_id ) ) ) {
									$product_data_to_be_updated['data'][ $index ]['main_image'] = get_the_post_thumbnail_url( $variation_id );
								} else {
									$product_data_to_be_updated['data'][ $index ]['main_image'] = get_the_post_thumbnail_url( $parent_id );
								}

								$attachment_ids = ! empty( $variation_data['gallery_image_ids'] ) ? $variation_data['gallery_image_ids'] : $parent_data['gallery_image_ids'];
								if ( ! empty( $attachment_ids ) ) {
									$additionalImageLinks = array();
									$count                = 1;
									foreach ( $attachment_ids as $attachment_id ) {
										if ( $count >= 10 ) {
											break;
										}
										$additionalImageLinks[] = wp_get_attachment_url( $attachment_id );
										$count                  = ++$count;
									}

									if ( is_array( $additionalImageLinks ) && ! empty( $additionalImageLinks ) ) {
										$product_data_to_be_updated['data'][ $index ]['images'] = $additionalImageLinks;
									}
								}
								foreach ( $variation_attr as $attr_name => $attr_value ) {
									// $attr_name = ucfirst(str_replace("attribute_pa_","",$attr_name));
									$attr_name = trim( str_replace( 'attribute_pa_', '', $attr_name ) );

									if ( 'color' == $attr_name || 'size' == $attr_name ) {
										$product_data_to_be_updated['data'][ $index ]['variant_attributes'][] = $attr_name;
									} else {
										$product_data_to_be_updated['data'][ $index ]['variant_attributes'][] = $attr_name;
									}

									$product_data_to_be_updated['data'][ $index ][ $attr_name ] = ucfirst( $attr_value );

								}
								$index++;
							}
						}
					}
				}

				if ( ! empty( $product_data_to_be_uploaded ) ) {
					$product_data_to_be_uploaded['page_id']    = $connected_page;
					$product_data_to_be_uploaded['catalog_id'] = $connected_catalog_id;
					$action                                    = 'webapi/rest/v1/product';
					$fileNmae                                  = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
					include_once $fileNmae;
					$ced_fmcw_send_request               = new Class_Ced_Fmcw_Send_Http_Request();
					$product_data_to_be_uploaded['data'] = array_values( $product_data_to_be_uploaded['data'] );
					// print_r($product_data_to_be_uploaded);
					// die('pppp');
					$upload_response = $ced_fmcw_send_request->upload_product( $product_data_to_be_uploaded, $action );
					if ( is_array( $upload_response ) && ! empty( $upload_response ) ) {
						if ( isset( $upload_response['success'] ) && $upload_response['success'] ) {
							if ( isset( $upload_response['data']['handles'] ) && ! empty( $upload_response['data']['handles'] ) ) {
								$upload_feed_handles                   = array();
								$upload_feed_handles                   = get_option( 'ced_fmcw_upload_update_feed_handles_' . $connected_catalog_id, array() );
								$handles                               = isset( $upload_response['data']['handles'][0] ) ? $upload_response['data']['handles'][0] : $upload_response['data']['handles'];
								$ced_fmcw_uploaded_feed                = array();
								$ced_fmcw_uploaded_feed['feed_id']     = $handles;
								$ced_fmcw_uploaded_feed['feed_status'] = '0';
								$ced_fmcw_uploaded_feed['feed_time']   = gmdate( 'l jS \of F Y h:i:s A' );
								$ced_fmcw_uploaded_feed['feed_type']   = 'Products Uploaded';
								global $wpdb;
								$prefix                     = $wpdb->prefix;
								$ced_fmcw_feed_status_table = $prefix . 'ced_fb_feeds_status';
								$good_market_insert_id      = $this->insert_in_db( $ced_fmcw_uploaded_feed, $ced_fmcw_feed_status_table );
								if ( ! empty( $to_be_uploaded_product_ids ) ) {
									$products_in_feed             = array();
									$products_in_feed             = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
									$products_in_feed[ $handles ] = $to_be_uploaded_product_ids;
									update_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, $products_in_feed );

									foreach ( $to_be_uploaded_product_ids as $key1 => $submitted_product_id ) {
										update_post_meta( $submitted_product_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id, 'yes' );
										update_post_meta( $submitted_product_id, 'ced_fmcw_product_upload_submitted_handle_' . $connected_catalog_id, $handles );
									}
									$product_upload = array(
										'status'  => '200',
										'message' => 'Product Submitted Successfully.',
									);
									echo json_encode( $product_upload );
									wp_die();
								}
							}
						} else {
							$product_upload = array(
								'status'  => '200',
								'message' => 'Unable to Submit Product.',
							);
							echo json_encode( $product_upload );
							wp_die();
						}
					}
				}

				if ( ! empty( $product_data_to_be_updated ) ) {
					$product_data_to_be_updated['page_id']    = $connected_page;
					$product_data_to_be_updated['catalog_id'] = $connected_catalog_id;
					$action                                   = 'webapi/rest/v1/product';
					$fileNmae                                 = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
					include_once $fileNmae;
					$ced_fmcw_send_request              = new Class_Ced_Fmcw_Send_Http_Request();
					$product_data_to_be_updated['data'] = array_values( $product_data_to_be_updated['data'] );
					$update_response                    = $ced_fmcw_send_request->update_product( $product_data_to_be_updated, $action );
					if ( is_array( $update_response ) && ! empty( $update_response ) ) {
						if ( isset( $update_response['success'] ) && $update_response['success'] ) {
							if ( isset( $update_response['data']['handles'] ) && ! empty( $update_response['data']['handles'] ) ) {
								$update_feed_handles = array();
								$update_handles      = isset( $update_response['data']['handles'][0] ) ? $update_response['data']['handles'][0] : $update_response['data']['handles'];

								$ced_fmcw_udated_feed                = array();
								$ced_fmcw_udated_feed['feed_id']     = $update_handles;
								$ced_fmcw_udated_feed['feed_status'] = '0';
								$ced_fmcw_udated_feed['feed_time']   = gmdate( 'l jS \of F Y h:i:s A' );
								$ced_fmcw_udated_feed['feed_type']   = 'Products Updated';
								global $wpdb;
								$prefix                     = $wpdb->prefix;
								$ced_fmcw_feed_status_table = $prefix . 'ced_fb_feeds_status';
								$good_market_insert_id      = $this->insert_in_db( $ced_fmcw_udated_feed, $ced_fmcw_feed_status_table );
								update_option( 'ced_fmcw_all_product_feeds', $all_product_feeds );
								if ( ! empty( $to_be_updated_product_ids ) ) {
									$products_in_feed                    = array();
									$products_in_feed                    = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
									$products_in_feed[ $update_handles ] = $to_be_updated_product_ids;
									update_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, $products_in_feed );
									foreach ( $to_be_updated_product_ids as $key1 => $submitted_product_id ) {
										update_post_meta( $submitted_product_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id, 'yes' );
										update_post_meta( $submitted_product_id, 'ced_fmcw_product_upload_submitted_handle_' . $connected_catalog_id, $update_handles );
									}
									$product_updated = array(
										'status'  => '200',
										'message' => 'Product Submitted Successfully.',
									);
									echo json_encode( $product_updated );
									wp_die();
								}
							} elseif ( isset( $update_response['data']['validation_status'] ) && ! empty( $update_response['data']['validation_status'] ) ) {
								$feed_errors = '';
								foreach ( $update_response['data']['validation_status'] as $key => $value ) {
									foreach ( $value['errors'] as $k => $err ) {
										$feed_errors .= '<span>Product : ' . get_the_title( $value['retailer_id'] ) . ' - ' . $err['message'] . '</span><br>';
									}
								}
								$product_update = array(
									'status'  => '400',
									'message' => $feed_errors,
								);
								echo json_encode( $product_update );
								wp_die();
							}
						} else {
							$product_update = array(
								'status'  => '400',
								'message' => 'Unable to Submit Product.',
							);
							echo json_encode( $product_update );
							wp_die();
						}
					}
				}
			} elseif ( 'ced_fmcw_remove_from_facebook' == $action ) {
				$product_data_to_be_removed = array();
				$cms_settings               = get_option( 'ced_fmcw_fb_cms_settings', array() );
				$catalog_and_page_id        = get_option( 'ced_fmcw_catalog_and_page_id', true );
				$bussiness_id               = array_keys( $catalog_and_page_id )[0];
				$connected_catalog_id       = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
				$call_for_get_shop_details  = get_option( 'ced_fmcw_stord_whole_store_data' );
				$connected_page             = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';
				if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
					$remove_count = 0;
					foreach ( $post_ids as $key => $product_id ) {
						if ( get_post_meta( $product_id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id, true ) == 'true' || get_post_meta( $product_id, 'ced_fmcw_product_update_submitted', true ) == 'yes' ) {
							$to_be_removed_product_ids[] = $product_id;
							$_product                    = wc_get_product( $product_id );

							if ( $_product->get_type() == 'variable' ) {
								foreach ( $_product->get_children() as $key => $Id ) {
									$product_data_to_be_removed[ "data[$remove_count][source_id]" ] = "$Id";
									$remove_count = ++$remove_count;
								}
							} else {
								$product_data_to_be_removed[ "data[$remove_count][source_id]" ] = "$product_id";
								$remove_count = ++$remove_count;
							}
						}
					}
				}
				if ( ! empty( $product_data_to_be_removed ) ) {
					if ( ! empty( $connected_page ) ) {
						$active_pages                                 = get_option( 'ced_fmcw_active_merchant_pages', array() );
							$product_data_to_be_removed['page_id']    = $connected_page;
							$product_data_to_be_removed['catalog_id'] = $connected_catalog_id;
							$action                                   = 'webapi/rest/v1/product';

							$fileNmae = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
							include_once $fileNmae;
							$ced_fmcw_send_request = new Class_Ced_Fmcw_Send_Http_Request();
							$delete_response       = $ced_fmcw_send_request->delete_product( $product_data_to_be_removed, $action );
						if ( is_array( $delete_response ) && ! empty( $delete_response ) ) {
							if ( isset( $delete_response['success'] ) && $delete_response['success'] ) {
								if ( isset( $delete_response['data']['handles'] ) && ! empty( $delete_response['data']['handles'] ) ) {
									$delete_feed_handles                  = array();
									$delete_feed_handles                  = get_option( 'ced_fmcw_delete_feed_handles', array() );
									$delete_handles                       = isset( $delete_response['data']['handles'][0] ) ? $delete_response['data']['handles'][0] : $delete_response['data']['handles'];
									$ced_fmcw_deleted_feed                = array();
									$ced_fmcw_deleted_feed['feed_id']     = $delete_handles;
									$ced_fmcw_deleted_feed['feed_status'] = '00';
									$ced_fmcw_deleted_feed['feed_time']   = gmdate( 'l jS \of F Y h:i:s A' );
									$ced_fmcw_deleted_feed['feed_type']   = 'Products Removed';
									global $wpdb;
									$prefix                     = $wpdb->prefix;
									$ced_fmcw_feed_status_table = $prefix . 'ced_fb_feeds_status';
									$good_market_insert_id      = $this->insert_in_db( $ced_fmcw_deleted_feed, $ced_fmcw_feed_status_table );
									if ( ! empty( $to_be_removed_product_ids ) ) {
										$products_in_feed                    = array();
										$products_in_feed                    = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
										$products_in_feed[ $delete_handles ] = $to_be_removed_product_ids;
										update_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, $products_in_feed );

										foreach ( $to_be_removed_product_ids as $key1 => $deleted_product_id ) {
											delete_post_meta( $deleted_product_id, 'ced_fmcw_product_update_submitted_' . $connected_catalog_id );
											delete_post_meta( $deleted_product_id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id );
										}
										$product_deleted = array(
											'status'  => '200',
											'message' => 'Product Deleted successfully.',
										);
										echo json_encode( $product_deleted );
										wp_die();
									}
								}
							}
							$product_deleted = array(
								'status'  => '400',
								'message' => 'Some issues while deleting products.',
							);
							echo json_encode( $product_deleted );
							wp_die();
						}
					}
				}
			}
		}
	}

	public function insert_in_db( $details, $tableName ) {
		global $wpdb;
		$wpdb->insert( $tableName, $details );
		$id = $wpdb->insert_id;
		return $id;
	}
	public function ced_fmcw_product_update( $post_ids = array() ) {
		if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
			$cms_settings = get_option( 'ced_fmcw_fb_cms_settings', array() );

			$catalog_and_page_id = get_option( 'ced_fmcw_catalog_and_page_id', array() );

			$authenticated_pages       = get_option( 'ced_fmcw_merchant_page_authenticated', array() );
			$call_for_get_shop_details = get_option( 'ced_fmcw_stord_whole_store_data' );
			$connected_page            = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';

			if ( empty( $connected_page ) ) {
				return;
			}
			$to_be_updated_product_ids     = array();
			$product_data_to_be_updated    = array();
			$variable_update_product_array = array();

			foreach ( $post_ids as $key => $product_id ) {
				$profile_data = $this->ced_fb_get_profile_assigned_data( $product_id );
				$_product     = wc_get_product( $product_id );

				$is_variable = 0;
				if ( $_product->get_type() == 'variable' ) {
					$is_variable                                  = 1;
					$variable_update_product_array[ $product_id ] = $_product->get_children();
					continue;

				}
				$product = $_product->get_data();

				$to_be_updated_product_ids[] = $product_id;

				$cat_ids = isset( $product['category_ids'] ) ? $product['category_ids'] : array();

				if ( ! empty( $cat_ids ) ) {
					foreach ( $cat_ids as $key1 => $value1 ) {
						$category     = get_term_meta( $value1, 'ced_fmcw_category_mapped', true );
						$categoryName = get_term_meta( $value1, 'ced_fmcw_category_mapped_name', true );

						if ( '' != $category ) {
							break;
						}
					}
				}

				$product_data_to_be_updated['data'][ $key ]['name']      = $product['name'];
				$product_data_to_be_updated['data'][ $key ]['brand']     = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_brand' );
				$product_data_to_be_updated['data'][ $key ]['mpn']       = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_mpn' );
				$product_data_to_be_updated['data'][ $key ]['gtin']      = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_gtin' );
				$product_data_to_be_updated['data'][ $key ]['gender']    = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_gender' );
				$product_data_to_be_updated['data'][ $key ]['age_group'] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_agegrp' );
				// $product_data_to_be_updated['data'][$key]["color"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_color' );
				// $product_data_to_be_updated['data'][$key]["material"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_material' );
				// $product_data_to_be_updated['data'][$key]["size"] = $this->fetch_meta_value_of_product( $product_id, 'ced_fmcw_size' );
				$product_data_to_be_updated['data'][ $key ]['url']      = get_the_permalink( $product_id );
				$product_data_to_be_updated['data'][ $key ]['category'] = $categoryName;

				$stock = get_post_meta( $product_id, '_stock', true );
				if ( empty( $stock ) ) {
					$stock = 0;
				}

				$product_data_to_be_updated['data'][ $key ]['inventory'] = $stock;

				$product_data_to_be_updated['data'][ $key ]['price'] = $product['regular_price'];

				$currency = get_option( 'woocommerce_currency', '' );
				if ( empty( $currency ) ) {
					$currency = 'USD';
				}
				$currency = 'USD';
				$product_data_to_be_updated['data'][ $key ]['currency'] = $currency;

				$product_data_to_be_updated['data'][ $key ]['source_id'] = "$product_id";
						// $product_data_to_be_updated['data'][$key]["group_id"] = $product_id;

				$stock_status = get_post_meta( $product_id, '_stock_status', true );
				$product_data_to_be_updated['data'][ $key ]['availability'] = '';
				if ( empty( $product_data_to_be_updated['data'][ $key ]['availability'] ) ) {
					if ( 'instock' == $stock_status || $stock > 0 ) {
						$product_data_to_be_updated['data'][ $key ]['availability'] = 'in stock';
						$product_data_to_be_updated['data'][ $key ]['inventory']    = (int) $stock;
					} else {
						$product_data_to_be_updated['data'][ $key ]['availability'] = 'out of stock';
					}
				}
				$product_data_to_be_updated['data'][ $key ]['condition'] = 'new';
				$description = preg_replace( '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/', '', $product['description'] );
				$product_data_to_be_updated['data'][ $key ]['description'] = $description;

				if ( isset( $product['sale_price'] ) && ! empty( $product['sale_price'] ) ) {
					$product_data_to_be_updated['data'][ $key ]['offer_price'] = $product['sale_price'];

					$sale_date_from = '';
					$sale_date_to   = '';
					if ( isset( $product['date_on_sale_from'] ) ) {
						$product_data_to_be_updated['data'][ $key ]['offer_price_start_date'] = $product['date_on_sale_from']->date;
					}
					if ( isset( $product['date_on_sale_to'] ) ) {
						$product_data_to_be_updated['data'][ $key ]['offer_price_end_date'] = $product['date_on_sale_to']->date;
					}
				}

				$product_data_to_be_updated['data'][ $key ]['main_image'] = get_the_post_thumbnail_url( $product_id );

				$attachment_ids = $product['gallery_image_ids'];
				if ( ! empty( $attachment_ids ) ) {
					$additionalImageLinks = array();
					$count                = 1;
					foreach ( $attachment_ids as $attachment_id ) {
						if ( $count >= 10 ) {
							break;
						}
						$additionalImageLinks[] = wp_get_attachment_url( $attachment_id );
						$count                  = ++$count;
					}

					if ( is_array( $additionalImageLinks ) && ! empty( $additionalImageLinks ) ) {
						$product_data_to_be_updated['data'][ $key ]['images'] = $additionalImageLinks;
					}
				}

				foreach ( $product['attributes'] as $attr_name => $attr_value ) {
					$attr_name = strtolower( str_replace( 'pa_', '', $attr_name ) );
					if ( 'color' == $attr_name || 'size' == $attr_name ) {
						$product_data_to_be_updated['data'][ $key ]['variant_attributes'][] = $attr_name;
					} else {
						$product_data_to_be_updated['data'][ $key ]['additional_variant_attribute'][] = $attr_name;
					}

					$attr_values = $attr_value->get_data();
					$value_arr   = array();
					foreach ( $attr_values['options'] as $key11 => $value11 ) {
						$value_arr[] = ucfirst( get_term( $value11 )->name );
					}
					$product_data_to_be_updated['data'][ $key ][ $attr_name ] = implode( ',', $value_arr );

				}
			}
			if ( ! empty( $variable_update_product_array ) ) {
				 $product_data_to_be_updated['data'] = ! empty( $product_data_to_be_updated['data'] ) ? array_values( $product_data_to_be_updated['data'] ) : array();
				$product_data_to_be_updated['data']  = array_values( $product_data_to_be_updated['data'] );
				$index                               = count( $product_data_to_be_updated['data'] );
				foreach ( $variable_update_product_array as $parent_id => $value ) {
					$parent_data = wc_get_product( $parent_id );
					$parent_data = $parent_data->get_data();
					foreach ( $value as $variation_position => $variation_id ) {
						$variation_product = wc_get_product( $variation_id );
						$variation_attr    = $variation_product->get_variation_attributes();
						$variation_data    = $variation_product->get_data();
						$cat_ids           = isset( $variation_data['category_ids'] ) ? $variation_data['category_ids'] : array();

						if ( ! empty( $cat_ids ) ) {
							foreach ( $cat_ids as $key1 => $value1 ) {
								$category     = get_term_meta( $value1, 'ced_fmcw_category_mapped', true );
								$categoryName = get_term_meta( $value1, 'ced_fmcw_category_mapped_name', true );

								if ( '' != $category ) {
									break;
								}
							}
						}
						$product_data_to_be_updated['data'][ $index ]['name']      = $parent_data['name'];
						$product_data_to_be_updated['data'][ $index ]['brand']     = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_brand' );
						$product_data_to_be_updated['data'][ $index ]['mpn']       = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_mpn' );
						$product_data_to_be_updated['data'][ $index ]['gender']    = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_gender' );
						$product_data_to_be_updated['data'][ $index ]['age_group'] = $this->fetch_meta_value_of_product( $parent_id, 'ced_fmcw_agegrp' );
						$product_data_to_be_updated['data'][ $index ]['url']       = get_the_permalink( $variation_id );
						$product_data_to_be_updated['data'][ $index ]['category']  = $categoryName;

						$stock = get_post_meta( $variation_id, '_stock', true );
						if ( empty( $stock ) ) {
							$stock = 0;
						}

						$product_data_to_be_updated['data'][ $index ]['inventory'] = $stock;

						$product_data_to_be_updated['data'][ $index ]['price'] = $variation_data['regular_price'];

						$currency = get_option( 'woocommerce_currency', '' );
						if ( empty( $currency ) ) {
							$currency = 'USD';
						}
						$currency = 'USD';
						$product_data_to_be_updated['data'][ $index ]['currency'] = $currency;

						$product_data_to_be_updated['data'][ $index ]['source_id'] = "$variation_id";
						$product_data_to_be_updated['data'][ $index ]['group_id']  = "$parent_id";
						$product_data_to_be_updated['data'][ $index ]['position']  = $variation_position + 1;

						$stock_status = get_post_meta( $variation_id, '_stock_status', true );
						$product_data_to_be_updated['data'][ $index ]['availability'] = '';
						if ( empty( $product_data_to_be_updated['data'][ $index ]['availability'] ) ) {
							if ( 'instock' == $stock_status || $stock > 0 ) {
								$product_data_to_be_updated['data'][ $index ]['availability'] = 'in stock';
								$product_data_to_be_updated['data'][ $index ]['inventory']    = (int) $stock;
							} else {
								$product_data_to_be_updated['data'][ $index ]['availability'] = 'out of stock';
							}
						}

						$product_data_to_be_updated['data'][ $index ]['condition'] = 'new';
						$description = preg_replace( '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/', '', $parent_data['description'] );
						$product_data_to_be_updated['data'][ $index ]['description'] = $description;

						if ( isset( $variation_data['sale_price'] ) && ! empty( $variation_data['sale_price'] ) ) {
							$product_data_to_be_updated['data'][ $index ]['offer_price'] = $variation_data['sale_price'];

							$sale_date_from = '';
							$sale_date_to   = '';
							if ( isset( $variation_data['date_on_sale_from'] ) ) {
								$product_data_to_be_updated['data'][ $index ]['offer_price_start_date'] = $variation_data['date_on_sale_from']->date;
							}
							if ( isset( $variation_data['date_on_sale_to'] ) ) {
								$product_data_to_be_updated['data'][ $index ]['offer_price_end_date'] = $variation_data['date_on_sale_to']->date;
							}
						}

						if ( ! empty( get_the_post_thumbnail_url( $variation_id ) ) ) {
							$product_data_to_be_updated['data'][ $index ]['main_image'] = get_the_post_thumbnail_url( $variation_id );
						} else {
							$product_data_to_be_updated['data'][ $index ]['main_image'] = get_the_post_thumbnail_url( $parent_id );
						}

						$attachment_ids = ! empty( $variation_data['gallery_image_ids'] ) ? $variation_data['gallery_image_ids'] : $parent_data['gallery_image_ids'];
						if ( ! empty( $attachment_ids ) ) {
							$additionalImageLinks = array();
							$count                = 1;
							foreach ( $attachment_ids as $attachment_id ) {
								if ( $count >= 10 ) {
									break;
								}
								$additionalImageLinks[] = wp_get_attachment_url( $attachment_id );
								$count                  = ++$count;
							}

							if ( is_array( $additionalImageLinks ) && ! empty( $additionalImageLinks ) ) {
								$product_data_to_be_updated['data'][ $index ]['images'] = $additionalImageLinks;
							}
						}
						// foreach ( $variation_attr as $attr_name => $attr_value ) {
						// $attr_name = ucfirst(str_replace("attribute_pa_","",$attr_name));
						// $attr_name = trim( str_replace( 'attribute_pa_', '', $attr_name ) );

						// if ( 'color' == $attr_name || 'size' == $attr_name ) {
						// $product_data_to_be_updated['data'][ $index ]['variant_attributes'][] = $attr_name;
						// } else {
						// $product_data_to_be_updated['data'][ $index ]['variant_attributes'][] = $attr_name;
						// }

						// $product_data_to_be_updated['data'][ $index ][ $attr_name ] = ucfirst( $attr_value );

						// }
						$index++;
					}
				}
			}
			if ( ! empty( $product_data_to_be_updated ) ) {
				if ( ! empty( $connected_page ) ) {
					$catalog_and_page_id                      = get_option( 'ced_fmcw_catalog_and_page_id', true );
					$bussiness_id                             = array_keys( $catalog_and_page_id )[0];
					$connected_catalog_id                     = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
					$product_data_to_be_updated['page_id']    = $connected_page;
					$product_data_to_be_updated['catalog_id'] = $connected_catalog_id;
					$action                                   = 'webapi/rest/v1/product';
					$fileNmae                                 = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
					include_once $fileNmae;
					$ced_fmcw_send_request              = new Class_Ced_Fmcw_Send_Http_Request();
					$product_data_to_be_updated['data'] = array_values( $product_data_to_be_updated['data'] );
					// print_r($product_data_to_be_updated);
					// die('cedcommerce-3');
					$update_response = $ced_fmcw_send_request->update_product( $product_data_to_be_updated, $action );
					if ( is_array( $update_response ) && ! empty( $update_response ) ) {
						if ( isset( $update_response['success'] ) && $update_response['success'] ) {
							if ( isset( $update_response['data']['handles'] ) && ! empty( $update_response['data']['handles'] ) ) {
								$update_feed_handles = array();
								$update_handles      = isset( $update_response['data']['handles'][0] ) ? $update_response['data']['handles'][0] : $update_response['data']['handles'];

								$ced_fmcw_udated_feed                = array();
								$ced_fmcw_udated_feed['feed_id']     = $update_handles;
								$ced_fmcw_udated_feed['feed_status'] = '0';
								$ced_fmcw_udated_feed['feed_time']   = gmdate( 'l jS \of F Y h:i:s A' );
								$ced_fmcw_udated_feed['feed_type']   = 'Products Synced';
								global $wpdb;
								$prefix                     = $wpdb->prefix;
								$ced_fmcw_feed_status_table = $prefix . 'ced_fb_feeds_status';
								$good_market_insert_id      = $this->insert_in_db( $ced_fmcw_udated_feed, $ced_fmcw_feed_status_table );
								update_option( 'ced_fmcw_all_product_feeds', $all_product_feeds );
								if ( ! empty( $to_be_updated_product_ids ) ) {
									$products_in_feed                    = array();
									$products_in_feed                    = get_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, array() );
									$products_in_feed[ $update_handles ] = $to_be_updated_product_ids;
									update_option( 'ced_fmcw_products_in_feed_' . $connected_catalog_id, $products_in_feed );
									foreach ( $to_be_updated_product_ids as $key1 => $submitted_product_id ) {
										update_post_meta( $submitted_product_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id, 'yes' );
										update_post_meta( $submitted_product_id, 'ced_fmcw_product_upload_submitted_handle_' . $connected_catalog_id, $update_handles );
									}
									$product_updated = array(
										'status'  => '200',
										'message' => 'Product Submitted Successfully.',
									);
									echo json_encode( $product_updated );
									wp_die();
								}
							} elseif ( isset( $update_response['data']['validation_status'] ) && ! empty( $update_response['data']['validation_status'] ) ) {
								$feed_errors = '';
								foreach ( $update_response['data']['validation_status'] as $key => $value ) {
									foreach ( $value['errors'] as $k => $err ) {
										$feed_errors .= '<span>Product : ' . get_the_title( $value['retailer_id'] ) . ' - ' . $err['message'] . '</span><br>';
									}
								}
							}
						} else {
							$product_update = array(
								'status'  => '400',
								'message' => 'Unable to Submit Product.',
							);
							echo json_encode( $product_update );
							wp_die();
						}
					}
					// }
				}
			}
		}

	}
	public function ced_fmcw_register_to_cedcommerce() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$site_email  = isset( $_POST['reg_email'] ) ? sanitize_text_field( $_POST['reg_email'] ) : '';
			$reg_country = isset( $_POST['reg_country'] ) ? sanitize_text_field( $_POST['reg_country'] ) : '';
			$site_url    = site_url();
			$redirect    = site_url() . '/wp-admin/admin.php?page=ced_fb';
			// $url         = 'https://fbdev.cifapps.com/remote/public/apiconnect/user/create';
			$url = 'https://metaapi-backend.cifapps.com/apiconnect/user/create';

			$post_fields = array(
				'username'          => $site_url . 'shbb',
				'name'              => $site_url,
				'marketplace'       => 'meta',
				'email'             => $site_email,
				'password'          => '987987987',
				'domain'            => $redirect,
				'confirmation_link' => $redirect,
				'app_id'            => 4,
				'apps'              => array(
					array(
						'app_id'          => 4,
						'app_name'        => 'Facebook',
						'app_marketplace' => 'meta',
					),
				),
			);
			$curl        = curl_init();
			curl_setopt_array(
				$curl,
				array(
					CURLOPT_URL            => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => '',
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => 'POST',
					CURLOPT_POSTFIELDS     => $post_fields,
					// staging header
					/*
					CURLOPT_HTTPHEADER => array(
					'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c2VyX2lkIjoiNjM4NGI1NzA2ZjcyNWUzMzEwMDI4ZWY5Iiwicm9sZSI6ImFkbWluIiwiZXhwIjoxNzE5NDY5NzE5LCJpc3MiOiJodHRwczpcL1wvYXBwcy5jZWRjb21tZXJjZS5jb20iLCJ0b2tlbl9pZCI6IjY0OWJkMzE3MTc0YjBhNzNjNDAyZTUyMiJ9.Jrl9gRPNjG-rxsBs2Jwtu8FGyzAemMybKrAnGK1lPFP4VHRrMJpq3SBAXj-Ac46NMNNRGtF8Q9CEJKpzjOp3oX-T1w6_slXS5j7FQ0K5KdZcDLCy9NLcFWQ-1VA0_MscBkIodQohmWsmS3Y_CVMtTGtFyDVIXB72e0ILdbOucie4MSBmXezUQpFL1DSAJiNLG2KbuArZfqxx88ry-2587SaHwpdB45UymWvwij52U7sBagY3vezG6w7le-E5HtcqNxAion4HNWBo558fUHhBnLRjJSnUCxUavMcnJRxmIB3cqPDHmSsecoR4i2VzLa72IsAB7cBOZR-LsKTgqyWupg'
					),*/
					// live header
					CURLOPT_HTTPHEADER     => array(
						'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c2VyX2lkIjoiNjQ0MTRmNzdmNWYzYjJjZTMxMDE1OWM5Iiwicm9sZSI6ImFkbWluIiwiZXhwIjoxNzIxMTk1ODM5LCJpc3MiOiJodHRwczpcL1wvYXBwcy5jZWRjb21tZXJjZS5jb20iLCJ0b2tlbl9pZCI6IjY0YjYyOWJmMjA5MzExOTdkZTBhOWQ2MiJ9.LdGxBpJPd84ICDxB3_uoT-lymg4iRVBRlis825d7r559a5gQZ9kflC1PQZQ1H7xPaL0ngc8CO2LMVLfr1Q4B__tjvXyD2nDFDAXz533pr_Q4N-apQxRlr-m5xO2SmCqEIVS1ZXYjgDx1UC0uDh5NVhywBozYtT96RxvFFPMGmE8ru8EDKV16gjzwkeuZai0IF_3SMvzshU41QVxle7bnxx2HWJr5XViseh6qI75BUen3fRjraQoPb1k3mcW45Bw55TNw7n8omYCsG-jlIaz6pOfAiKfJAnDLL7Ld4jEMLky9vfpEyZEJRSuI1kxaKO9Uns6q2qaFTH7HcV14MI4BiA',
					),
				)
			);
			$response = curl_exec( $curl );
			curl_close( $curl );
			$response = json_decode( $response, true );
			// print_r($response); die('pp');
			if ( isset( $response['success'] ) && ( true == $response['success'] || 'true' == $response['success'] ) ) {
				$data   = $response['data'];
				$app_id = $data['apps'][0]['_id'];
				// print_r($data['apps']);
				// die('pp');
				$another_public_key                     = $data['public_key'];
				$secret_key                             = $data['apps'][0]['public_key'];
				$access_token                           = $data['apps'][0]['refresh_token'];
				$registration_data                      = array();
				$registration_data['reg_email']         = isset( $_POST['reg_email'] ) ? sanitize_text_field( $_POST['reg_email'] ) : '';
				$registration_data['reg_app_id']        = $app_id;
				$registration_data['reg_public_key']    = $secret_key;
				$registration_data['reg_refresh_token'] = $access_token;
				update_option( 'ced_fmcw_user_create_response', $data );
				update_option( 'ced_fmcw_registered_with_cedcommerce', $registration_data );
				update_option( 'ced_fmcw_registered_with_cedcommerce_selected_country', $reg_country );
				echo 'success';
				wp_die();
			} else {
				esc_html_e( $response['data']['errors'][0] );
				wp_die();
			}
		}
	}

	public function ced_fmcw_modify_content_on_product_list_page( $column = '', $post_id = '' ) {

		if ( 'ced_fmcw_added_on_facebook' == $column ) {
			$uploaded  = get_post_meta( $post_id, 'ced_fmcw_uploaded_on_facebook', true );
			$errors    = get_post_meta( $post_id, 'ced_fmcw_product_with_errors', true );
			$submitted = get_post_meta( $post_id, 'ced_fmcw_product_upload_submitted', true );
			if ( 'true' == $uploaded ) {
				?>
		   <div class="ced_fmcw_alert-wrap">
			  <div class="ced_fmcw_alert-wrap-contain">
				 <div class="ced_fmcw_alert_text">
					<div class="ced_fmcw_alert_wrap_text_upload">
					   <a href="javascript:void(0)" class="ced_fmcw_display_error" data-product-id="<?php echo esc_attr( $post_id ); ?>">Uploaded</a>
				   </div>
			   </div>
		   </div>
	   </div>
				<?php
			} elseif ( ! empty( $submitted ) ) {
				?>
	   <div class="ced_fmcw_alert-wrap">
		  <div class="ced_fmcw_alert-wrap-contain">
			 <div class="ced_fmcw_alert_text">
				<div class="ced_fmcw_alert_wrap_text_Imported">
				   <a href="javascript:void(0)" class="ced_fmcw_display_error" data-product-id="<?php echo esc_attr( $post_id ); ?>">Submitted</a>
			   </div>
		   </div>
	   </div>
	</div>
				<?php
			} elseif ( ! empty( $errors ) ) {
				$error_arr = get_post_meta( $post_id, 'ced_fmcw_product_errors_warning', true );
				$error     = '';
				if ( is_array( $error_arr ) ) {
					foreach ( $error_arr as $key => $errors ) {
						if ( 'error' == $errors['severity'] ) {
							$error .= $errors['severity'] . ' - ' . $errors['message'] . "\n\n";
						}
					}
				}
				?>
	   <div class="ced_fmcw_alert-wrap" title="<?php echo esc_attr( $error ); ?>">
		  <div class="ced_fmcw_alert-wrap-contain">
			 <div class="ced_fmcw_alert_text">
				<div class="ced_fmcw_alert_wrap_text">
				   <a href="javascript:void(0)" class="ced_fmcw_display_error" data-product-id="<?php echo esc_attr( $post_id ); ?>">Error</a>
			   </div>
		   </div>
	   </div>
   </div>
				<?php
			} else {
				?>
   <div class="ced_fmcw_alert-wrap">
	  <div class="ced_fmcw_alert-wrap-contain">
		 <div class="ced_fmcw_alert_text">
			<div class="ced_fmcw_alert_wrap_text_Imported">
			   <a href="javascript:void(0)">Not Uploaded</a>
		   </div>
	   </div>
   </div>
</div>
				<?php
			}
		}
	}

	public function ced_fmcw_map_categories_to_store() {

		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$user_data       = isset( $sanitized_array['user_data'] ) ? $sanitized_array['user_data'] : false;

			$fb_category_array    = isset( $sanitized_array['fb_category_array'] ) ? $sanitized_array['fb_category_array'] : array();
			$store_category_array = isset( $sanitized_array['store_category_array'] ) ? $sanitized_array['store_category_array'] : array();
			$fb_category_name     = isset( $sanitized_array['fb_category_name'] ) ? $sanitized_array['fb_category_name'] : array();
			$fb_saved_category    = get_option( 'ced_fmcw_category', array() );
			if ( empty( $fb_saved_category ) ) {
				$fb_saved_category = array();
			}
			$fb_mapped_categories      = array_combine( $store_category_array, $fb_category_array );
			$fb_mapped_categories      = array_filter( $fb_mapped_categories );
			$fb_mapped_categories_name = array_combine( $fb_category_array, $fb_category_name );
			$fb_mapped_categories_name = array_filter( $fb_mapped_categories_name );
			foreach ( $store_category_array as $key => $value ) {
				update_term_meta( $value, 'ced_fmcw_category_mapped', $fb_category_array[ $key ] );
				$fb_saved_category[ $fb_category_name[ $key ] ] = $fb_category_array[ $key ];
				update_term_meta( $value, 'ced_fmcw_category_mapped_name', $fb_category_name[ $key ] );

				// $this->cedWGEI_CreateAutoProfiles( $fb_mapped_categories, $fb_mapped_categories_name );
			}
			update_option( 'ced_fmcw_category', $fb_saved_category );
			wp_die();
		}
	}
	public function cedWGEI_CreateAutoProfiles( $fb_mapped_categories = array(), $fb_mapped_categories_name = array() ) {
		global $wpdb;
		if ( ! empty( $fb_mapped_categories ) ) {
			foreach ( $fb_mapped_categories as $key => $value ) {
				$profile_already_created = get_term_meta( $key, 'ced_fb_profile_created', true );
				$created_profile_id      = get_term_meta( $key, 'ced_fb_profile_id', true );
				if ( 'yes' == $profile_already_created && ! empty( $created_profile_id ) ) {
					$new_profile_need_to_be_created = $this->check_if_new_profile_need_to_be_created( $key, $value );

					if ( ! $new_profile_need_to_be_created ) {
						 continue;
					} else {
						$this->reset_mapped_category_data( $key, $value );
					}
				}

				$woo_categories      = array();
				$category_attributes = array();

				$profile_name = isset( $fb_mapped_categories_name[ $value ] ) ? $fb_mapped_categories_name[ $value ] : 'Profile for Shopee - Category Id : ' . $value;

				$profile_id = $wpdb->get_results( $wpdb->prepare( 'SELECT `id` FROM wp_ced_fb_profiles WHERE `profile_name` = %s ', $profile_name ), 'ARRAY_A' );
				if ( ! isset( $profile_id[0]['id'] ) && empty( $profile_id[0]['id'] ) ) {
					$is_active = 1;

					$marketplace_name = 'Shopee';

					foreach ( $fb_mapped_categories as $key1 => $value1 ) {
						if ( $value1 == $value ) {
							$woo_categories[] = $key1;
						}
					}

					$profile_data = array();
					$profile_data = $this->ced_fb_prepare_profile_data( $value, $profile_name );

					$profile_details = array(
						'profile_name'   => $profile_name,
						'profile_status' => 'active',
						'profile_data'   => json_encode( $profile_data ),
						'woo_categories' => json_encode( $woo_categories ),
					);
					$profile_id      = $this->insert_fb_profile( $profile_details );
				} else {
					$woo_categories     = array();
					$profile_id         = $profile_id[0]['id'];
					$profile_categories = $wpdb->get_results( $wpdb->prepare( 'SELECT `woo_categories` FROM wp_ced_fb_profiles WHERE `id` = %d ', $profile_id ), 'ARRAY_A' );
					$woo_categories     = json_decode( $profile_categories[0]['woo_categories'], true );
					$woo_categories[]   = $key;
					$table_name         = 'wp_ced_fb_profiles';
					$wpdb->update(
						$table_name,
						array(
							'woo_categories' => json_encode( $woo_categories ),
						),
						array( 'id' => $profile_id )
					);
				}
				foreach ( $woo_categories as $key12 => $value12 ) {
					update_term_meta( $value12, 'ced_fb_profile_created', 'yes' );
					update_term_meta( $value12, 'ced_fb_profile_id', $profile_id );
					update_term_meta( $value12, 'ced_fb_mapped_category', $value );
				}
			}
		}
	}
	public function ced_fb_get_profile_assigned_data( $pro_ids ) {
		global $wpdb;
		$product_data = wc_get_product( $pro_ids );
		$product      = $product_data->get_data();
		$category_id  = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
		$profile_id   = get_post_meta( $pro_ids, 'ced_fb_profile_assigned', true );
		if ( ! empty( $profile_id ) ) {
			$profile_id = $profile_id;
		} else {
			foreach ( $category_id as $key => $value ) {
				$profile_id = get_term_meta( $value, 'ced_fb_profile_id', true );

				if ( ! empty( $profile_id ) ) {
					break;
				}
			}
		}

		if ( isset( $profile_id ) && ! empty( $profile_id ) ) {
			$this->is_profile_assigned_to_product = true;
			$profile_data                         = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM wp_ced_fb_profiles WHERE `id` = %d ', $profile_id ), 'ARRAY_A' );
			if ( is_array( $profile_data ) ) {
				$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$profile_data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();
			}
		} else {
			$this->is_profile_assigned_to_product = false;
		}
		$this->profile_data = isset( $profile_data ) ? $profile_data : '';
	}
	public function insert_fb_profile( $profile_details ) {
		global $wpdb;
		$profile_table_name = 'wp_ced_fb_profiles';

		$wpdb->insert( $profile_table_name, $profile_details );

		$profile_id = $wpdb->insert_id;
		return $profile_id;
	}
	public function ced_fb_prepare_profile_data( $value, $profile_name ) {
		$profile_data['ced_fmcw_profile_name']['value']      = $profile_name;
		$profile_data['ced_fmcw_availability']['value']      = 'in stock';
		$profile_data['ced_fmcw_available_date']['value']    = gmdate( 'd/m/Y' );
		$profile_data['ced_fmcw_expiration_date']['value']   = gmdate( 'd/m/Y', strtotime( '+30 days', strtotime( gmdate( 'd/m/Y' ) ) ) );
		$profile_data['ced_fmcw_max_handling_time']['value'] = 5;
		$profile_data['ced_fmcw_min_handling_time']['value'] = 2;
		$profile_data['ced_fmcw_condition']['value']         = 'new';
		$profile_data['ced_fmcw_adult']['value']             = '';
		$profile_data['ced_fmcw_bundle']['value']            = '';
		$profile_data['ced_fmcw_multipack']['value']         = '';
		$profile_data['ced_fmcw_agegrp']['value']            = 'all ages';
		$profile_data['ced_fmcw_gender']['value']            = 'unisex';
		$profile_data['ced_fmcw_color']['value']             = '';
		$profile_data['ced_fmcw_material']['value']          = '';
		$profile_data['ced_fmcw_size']['value']              = '';
		return $profile_data;
	}
	public function check_if_new_profile_need_to_be_created( $woo_category_id = '', $fb_category_id = '' ) {
		$old_fb_category_mapped = get_term_meta( $woo_category_id, 'ced_fb_mapped_category', true );
		if ( $old_fb_category_mapped == $fb_category_id ) {
			return false;
		} else {
			return true;
		}
	}
	public function reset_mapped_category_data( $woo_category_id = '', $fb_category_id = '' ) {
		update_term_meta( $woo_category_id, 'ced_fb_mapped_category', $fb_category_id );
		delete_term_meta( $woo_category_id, 'ced_fb_profile_created' );
		$created_profile_id = get_term_meta( $woo_category_id, 'ced_fb_profile_id', true );
		delete_term_meta( $woo_category_id, 'ced_fb_profile_id' );
		$this->remove_category_mapping_from_profile( $created_profile_id, $woo_category_id );
	}
	public function remove_category_mapping_from_profile( $created_profile_id = '', $woo_category_id = '' ) {
		global $wpdb;
		$profile_table_name = 'wp_ced_fb_profiles';
		$profile_data       = $wpdb->get_results( $wpdb->prepare( 'SELECT `woo_categories` FROM %s WHERE `id`= %d ', $table_name, $created_profile_id ), 'ARRAY_A' );

		if ( is_array( $profile_data ) ) {
			$profile_data   = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
			$woo_categories = isset( $profile_data['woo_categories'] ) ? json_decode( $profile_data['woo_categories'], true ) : array();
			if ( is_array( $woo_categories ) && ! empty( $woo_categories ) ) {
				$categories = array();
				foreach ( $woo_categories as $key => $value ) {
					if ( $value != $woo_category_id ) {
						 $categories[] = $value;
					}
				}
				$categories = json_encode( $categories );
				$wpdb->update( $profile_table_name, array( 'woo_categories' => $categories ), array( 'id' => $created_profile_id ) );
			}
		}
	}
	public function ced_fmcw_fetch_next_level_category() {

		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$store_category_id         = isset( $_POST['store_id'] ) ? sanitize_text_field( $_POST['store_id'] ) : '';
			$selected_fb_category_id   = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
			$selected_fb_category_name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$category_level            = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '1';
			$select_html               = '';
			$next_level                = intval( $category_level ) + 1;
			$fb_categories             = file_get_contents( CED_FMCW_DIRPATH . 'admin/lib/json/category.json' );
			$fb_categories             = json_decode( $fb_categories, true );
			foreach ( $fb_categories as $key => $value ) {

				if ( isset( $value[ 'level' . $category_level ] ) && $value[ 'level' . $category_level ] == $selected_fb_category_name ) {
					$filtered_categories[ $value['ID'] ] = isset( $value[ 'level' . $next_level ] ) ? $value[ 'level' . $next_level ] : '';
				}
			}

			$filtered_categories = array_unique( $filtered_categories );
			$filtered_categories = array_filter( $filtered_categories );
			if ( is_array( $filtered_categories ) && ! empty( $filtered_categories ) ) {
				$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_fmcw_level' . $next_level . '_category ced_fmcw_select_category" name="ced_fmcw_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '">';

				$select_html .= '<option value=""> --' . __( 'Select', 'facebook-marketplace-connector-for-woocommerce' ) . '-- </option>';
				foreach ( $filtered_categories as $key => $value ) {
					if ( '' != $value ) {
						$select_html .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}
				$select_html .= '</select></td>';
				print_r( $select_html );
				die;
			} else {
					 echo 'No-Sublevel';
				die;
			}
		}
	}
	public function fetch_meta_value_of_product( $pro_ids, $meta_key ) {
		$_product = wc_get_product( $pro_ids );
		if ( $_product->get_type() == 'variation' ) {
			$parent_id = $_product->get_parent_id();
		} else {
			$parent_id = '0';
		}
		$global_settings_data = get_option( 'ced_fmcw_global_settings', array() );
		if ( isset( $global_settings_data[ $meta_key ] ) ) {
			if ( ! empty( $global_settings_data[ $meta_key ]['value'] ) ) {
				return $global_settings_data[ $meta_key ]['value'];
			} elseif ( ! empty( $global_settings_data[ $meta_key ]['metakey'] ) ) {
				if ( strpos( $global_settings_data[ $meta_key ]['metakey'], 'umb_pattr_' ) !== false ) {
					$woo_attribute = explode( 'umb_pattr_', $global_settings_data[ $meta_key ]['metakey'] );
					$woo_attribute = end( $woo_attribute );
					if ( $_product->get_type() == 'variation' ) {
						$var_product = wc_get_product( $parent_id );
						$attributes  = $var_product->get_variation_attributes();
						if ( isset( $attributes[ 'attribute_pa_' . $woo_attribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $woo_attribute ] ) ) {
							$woo_attribute_value = $attributes[ 'attribute_pa_' . $woo_attribute ];
							if ( '0' != $parent_id ) {
								$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
							} else {
								$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
							}
						} else {
							$woo_attribute_value = $var_product->get_attribute( 'pa_' . $woo_attribute );
							$woo_attribute_value = explode( ',', $woo_attribute_value );
							$woo_attribute_value = $woo_attribute_value[0];

							if ( '0' != $parent_id ) {
								$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
							} else {
								$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
							}
						}
						if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
							foreach ( $product_terms as $tempkey => $tempvalue ) {
								if ( $tempvalue->slug == $woo_attribute_value ) {
									$woo_attribute_value = $tempvalue->name;
									break;
								}
							}
							if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
								$value = $woo_attribute_value;
							} else {
								$value = get_post_meta( $pro_ids, $meta_key, true );
							}
						} else {
							$value = get_post_meta( $pro_ids, $meta_key, true );
						}
					} else {
						$woo_attribute_value = $_product->get_attribute( 'pa_' . $woo_attribute );
						$product_terms       = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
						if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
							foreach ( $product_terms as $tempkey => $tempvalue ) {
								if ( $tempvalue->slug == $woo_attribute_value ) {
									$woo_attribute_value = $tempvalue->name;
									break;
								}
							}
							if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
								$value = $woo_attribute_value;
							} else {
								$value = get_post_meta( $pro_ids, $meta_key, true );
							}
						} else {
							$value = get_post_meta( $pro_ids, $meta_key, true );
						}
					}
					return $value;
				} else {
					$value = get_post_meta( $pro_ids, $global_settings_data[ $meta_key ]['metakey'], true );
					return $value;
				}
			}
		}

		if ( isset( $this->is_profile_assigned_to_product ) && $this->is_profile_assigned_to_product ) {

			if ( ! empty( $this->profile_data ) && isset( $this->profile_data[ $meta_key ] ) ) {
				$profile_data      = $this->profile_data[ $meta_key ];
				$temp_profile_data = $profile_data;
				if ( isset( $temp_profile_data['value'] ) && ! empty( $temp_profile_data['value'] ) && ! is_null( $temp_profile_data['value'] ) ) {
					$value = $temp_profile_data['value'];
				} elseif ( isset( $temp_profile_data['metakey'] ) && ! empty( $temp_profile_data['metakey'] ) && 'null' != $temp_profile_data['metakey'] ) {
					if ( strpos( $temp_profile_data['metakey'], 'umb_pattr_' ) !== false ) {
						$woo_attribute = explode( 'umb_pattr_', $temp_profile_data['metakey'] );
						$woo_attribute = end( $woo_attribute );

						if ( $_product->get_type() == 'variation' ) {
							 $var_product = wc_get_product( $parent_id );
							 $attributes  = $var_product->get_variation_attributes();
							if ( isset( $attributes[ 'attribute_pa_' . $woo_attribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $woo_attribute ] ) ) {
								$woo_attribute_value = $attributes[ 'attribute_pa_' . $woo_attribute ];
								if ( '0' != $parent_id ) {
									  $product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
								} else {
									$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
								}
							} else {
								$woo_attribute_value = $var_product->get_attribute( 'pa_' . $woo_attribute );
								$woo_attribute_value = explode( ',', $woo_attribute_value );
								$woo_attribute_value = $woo_attribute_value[0];

								if ( '0' != $parent_id ) {
									$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
								} else {
									$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
								}
							}
							if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
								foreach ( $product_terms as $tempkey => $tempvalue ) {
									if ( $tempvalue->slug == $woo_attribute_value ) {
										 $woo_attribute_value = $tempvalue->name;
										 break;
									}
								}
								if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
									$value = $woo_attribute_value;
								} else {
									$value = get_post_meta( $pro_ids, $meta_key, true );
								}
							} else {
								$value = get_post_meta( $pro_ids, $meta_key, true );
							}
						} else {
								$woo_attribute_value = $_product->get_attribute( 'pa_' . $woo_attribute );
								$product_terms       = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
							if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
								foreach ( $product_terms as $tempkey => $tempvalue ) {
									if ( $tempvalue->slug == $woo_attribute_value ) {
										$woo_attribute_value = $tempvalue->name;
										break;
									}
								}
								if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
											$value = $woo_attribute_value;
								} else {
										$value = get_post_meta( $pro_ids, $meta_key, true );
								}
							} else {
								$value = get_post_meta( $pro_ids, $meta_key, true );
							}
						}
					} else {
									 $value = get_post_meta( $pro_ids, $temp_profile_data['metakey'], true );
						if ( '_thumbnail_id' == $temp_profile_data['metakey'] ) {
							$value = wp_get_attachment_image_url( get_post_meta( $pro_ids, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $pro_ids, '_thumbnail_id', true ), 'thumbnail' ) : '';
						}
						if ( ! isset( $value ) || empty( $value ) || is_null( $value ) || '0' == $value || 'null' == $value ) {
							if ( '0' == $parent_id ) {
								$value = get_post_meta( $parent_id, $temp_profile_data['metakey'], true );
								if ( '_thumbnail_id' == $temp_profile_data['metakey'] ) {
									$value = wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
								}

								if ( ! isset( $value ) || empty( $value ) || is_null( $value ) ) {
									$value = get_post_meta( $pro_ids, $meta_key, true );
								}
							} else {
								$value = get_post_meta( $pro_ids, $meta_key, true );
							}
						}
					}
				} else {
					$value = get_post_meta( $pro_ids, $meta_key, true );
				}
			} else {
				 $value = get_post_meta( $pro_ids, $meta_key, true );
			}
			return $value;
		} else {
			$value = get_post_meta( $pro_ids, $meta_key, true );
			return $value;
		}
	}
	public function ced_fmcw_setup_completed() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$authenticated_pages = get_option( 'ced_fmcw_merchant_page_authenticated', array() );
			if ( is_array( $authenticated_pages ) && ! empty( $authenticated_pages ) ) {
				update_option( 'ced_fmcw_setup_completed', 'yes' );
				echo json_encode(
					array(
						'status'       => '200',
						'message'      => 'next_step',
						'redirect_url' => admin_url( 'admin.php?page=ced_fb' ),
					)
				);
				wp_die();
			} else {
				 // update_option( 'ced_fmcw_setup_completed', "yes" );
				echo json_encode(
					array(
						'status'  => '201',
						'message' => 'same_step',
					)
				);
				wp_die();
			}
		}
		wp_die();
	}

	public function ced_fmcw_add_tab_on_product_edit_page( $tabs = array() ) {
		$tabs['ced_fmcw_facebook_attributes'] = array(
			'label'  => __( 'Facebook', 'facebook-marketplace-connector-for-woocommerce' ),
			'target' => 'ced_fmcw_facebook_attributes',
			'class'  => array( 'show_if_simple', 'ced_fmcw_facebook_attributes_fields' ),
		);

		return $tabs;
	}

	public function ced_fmcw_add_fields_on_product_edit_page() {
		global $post;

		// $current_term = wp_get_object_terms( $post->ID, 'product_type' );
		// print_r($current_term);
		// die('pp');
		// if ( $current_term == $terms ) {
		// $product_type = sanitize_title( current( $terms )->name );
		// } else {
		// $product_type = apply_filters( 'default_product_type', 'simple' );
		// }
		$product = wc_get_product( $post->ID );
		// check if variable product

		if ( 'simple' === $product->get_type() ) {
			include_once CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-add-product-fields.php';
		}
	}

	public function ced_fmcw_add_fields_on_variation_edit_page( $loop, $variation_data, $variation ) {
		global $post;
		// require CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-add-variation-product-field.php';
		if ( isset( $variation ) && ! empty( $variation ) ) {
			$product_post = $variation;
		}
		?>

<div id="ced_fmcw_facebook_attributes ced_fmcw_facebook_variable_product_tab_layout" class="panel woocommerce_options_panel">
	<div id="ced_fmcw_accordians">
		<div class="ced_fmcw_facebook_panel">
			<div class="ced_fmcw_facebook_panel_heading">
				<h4><?php esc_attr_e( 'Facebook Product Data', 'facebook-marketplace-connector-for-woocommerce' ); ?></h4>
			</div>
			<div class="ced_fmcw_facebook_collapse">
				<div class="options_group ced_fmcw_facebook_label_data">
					<p class="form-field ced_fmcw_availability_field ">
						<?php $ced_fmcw_availability = ''; ?>
						<?php $ced_fmcw_availability = get_post_meta( $product_post->ID, 'ced_fmcw_availability', true ); ?>
						<label><span><?php esc_attr_e( 'Availability', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_availability_[<?php echo esc_attr( $loop ); ?>]" id="ced_fmcw_availability" class="ced_fmcw_data_fields">
							<option><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="in stock" 
							<?php
							if ( 'in stock' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'In Stock', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="out of stock" 
							<?php
							if ( 'out of stock' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Out of Stock', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="preorder" 
							<?php
							if ( 'preorder' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Pre Order', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="available for order" 
							<?php
							if ( 'available for order' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Available for Order', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="discontinued" 
							<?php
							if ( 'discontinued' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Discontinued', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="pending" 
							<?php
							if ( 'pending' == $ced_fmcw_availability ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Pending', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					<?php $ced_fmcw_condition = ''; ?>
					<?php $ced_fmcw_condition = get_post_meta( $product_post->ID, 'ced_fmcw_condition', true ); ?>
					<p class="ced_fmcw_condition_field form-field">
						<label><span><?php esc_attr_e( 'Product Condition', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_condition_[<?php echo esc_attr( $loop ); ?>]" id="ced_fmcw_condition" class="ced_fmcw_data_fields">
							<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="new" 
							<?php
							if ( 'new' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'New', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="refurbished" 
							<?php
							if ( 'refurbished' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Refurbished', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used" 
							<?php
							if ( 'used' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used_like_new" 
							<?php
							if ( 'used_like_new' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used Like New', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used_good" 
							<?php
							if ( 'used_good' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used Good', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="used_fair" 
							<?php
							if ( 'used_fair' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Used Fair', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="cpo" 
							<?php
							if ( 'cpo' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'CPO', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="open_box_new" 
							<?php
							if ( 'open_box_new' == $ced_fmcw_condition ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Open Box New', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					
					<?php $ced_fmcw_agegrp = ''; ?>
					<?php $ced_fmcw_agegrp = get_post_meta( $product_post->ID, 'ced_fmcw_agegrp', true ); ?>
					<p class="ced_fmcw_agegrp_field form-field">
						<label><span><?php esc_attr_e( 'Preferred Age Group', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_agegrp_[<?php esc_attr( $loop ); ?>]" id="ced_fmcw_agegrp" class="ced_fmcw_data_fields">
							<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="newborn" 
							<?php
							if ( 'newborn' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'New Born', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="infant" 
							<?php
							if ( 'infant' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'infant', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="toddler" 
							<?php
							if ( 'toddler' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Toddler', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="kids" 
							<?php
							if ( 'kids' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Kids', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="adult" 
							<?php
							if ( 'adult' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Adult', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="all ages" 
							<?php
							if ( 'all ages' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'All Ages', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="teen" 
							<?php
							if ( 'teen' == $ced_fmcw_agegrp ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Teen', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					<?php $ced_fmcw_gender = ''; ?>
					<?php $ced_fmcw_gender = get_post_meta( $product_post->ID, 'ced_fmcw_gender', true ); ?>
					<p class="ced_fmcw_gender_field form-field">
						<label><span><?php esc_attr_e( 'Select Preferred Audience', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<select name="ced_fmcw_gender_[<?php echo esc_attr( $loop ); ?>]" id="ced_fmcw_gender" class="ced_fmcw_data_fields">
							<option value=""><?php esc_attr_e( '--Select--', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="male" 
							<?php
							if ( 'male' == $ced_fmcw_gender ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Male', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="female" 
							<?php
							if ( 'female' == $ced_fmcw_gender ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Female', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
							<option value="unisex" 
							<?php
							if ( 'unisex' == $ced_fmcw_gender ) {
								echo 'selected';}
							?>
							><?php esc_attr_e( 'Unisex', 'facebook-marketplace-connector-for-woocommerce' ); ?></option>
						</select>
					</p>
					<?php $ced_fmcw_brand = ''; ?>
					<?php $ced_fmcw_brand = get_post_meta( $product_post->ID, 'ced_fmcw_brand', true ); ?>
					<p class="ced_fmcw_color_field form-field">
						<label><span><?php esc_attr_e( 'Brand', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<input type="text" class="ced_fmcw_data_fields" id="ced_fmcw_brand" name="ced_fmcw_brand_[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $ced_fmcw_brand ); ?>"></input>
					</p>
					<?php $ced_fmcw_mpn = ''; ?>
					<?php $ced_fmcw_mpn = get_post_meta( $product_post->ID, 'ced_fmcw_mpn', true ); ?>
					<p class="ced_fmcw_material_field form-field">
						<label><span><?php esc_attr_e( 'MPN', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<input type="text" class="ced_fmcw_data_fields" id="ced_fmcw_mpn" name="ced_fmcw_mpn_[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $ced_fmcw_mpn ); ?>"></input>
					</p>
					<?php $ced_fmcw_gtin = ''; ?>
					<?php $ced_fmcw_gtin = get_post_meta( $product_post->ID, 'ced_fmcw_gtin', true ); ?>
					<p class="ced_fmcw_material_field form-field">
						<label><span><?php esc_attr_e( 'GTIN', 'facebook-marketplace-connector-for-woocommerce' ); ?></span></label>
						<input type="text" class="ced_fmcw_data_fields" id="ced_fmcw_gtin" name="ced_fmcw_gtin_[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $ced_fmcw_gtin ); ?>"></input>
					</p>
					<?php
						wp_nonce_field( 'facebook_product_edit', 'facebook_product_edit_actions' );
					?>
						
				</div>
			</div>
		</div>
		<?php do_action( 'ced_fmcw_add_more_data_field_section' ); ?>
	</div>
</div>
		<?php
	}
	public function ced_fmcw_save_fields_on_edit_page_variation( $variation, $loop ) {
		if ( ! isset( $_POST['facebook_product_edit_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['facebook_product_edit_actions'] ) ), 'facebook_product_edit' ) ) {
			return;
		}
		if ( isset( $_POST['ced_fmcw_availability_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_availability', sanitize_text_field( $_POST['ced_fmcw_availability_'][ $loop ] ) );
		}
		if ( isset( $_POST['ced_fmcw_condition_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_condition', sanitize_text_field( $_POST['ced_fmcw_condition_'][ $loop ] ) );
		}
		if ( isset( $_POST['ced_fmcw_agegrp_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_agegrp', sanitize_text_field( $_POST['ced_fmcw_agegrp_'][ $loop ] ) );
		}
		if ( isset( $_POST['ced_fmcw_gender_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_gender', sanitize_text_field( $_POST['ced_fmcw_gender_'][ $loop ] ) );
		}
		if ( isset( $_POST['ced_fmcw_brand_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_brand', sanitize_text_field( $_POST['ced_fmcw_brand_'][ $loop ] ) );
		}
		if ( isset( $_POST['ced_fmcw_mpn_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_mpn', sanitize_text_field( $_POST['ced_fmcw_mpn_'][ $loop ] ) );
		}
		if ( isset( $_POST['ced_fmcw_gtin_'][ $loop ] ) ) {
			$variation->update_meta_data( 'ced_fmcw_gtin', sanitize_text_field( $_POST['ced_fmcw_gtin_'][ $loop ] ) );
		}
	}
	public function ced_fmcw_save_product_details( $post_id ) {
		if ( ! isset( $_POST['facebook_product_edit_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['facebook_product_edit_actions'] ) ), 'facebook_product_edit' ) ) {
				return;
		}
		if ( is_array( $_POST ) && ! empty( $_POST ) ) {
			foreach ( $_POST as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}

			if ( ! isset( $_POST['ced_wgei_adult'] ) ) {
				update_post_meta( $post_id, 'ced_wgei_adult', '' );
			}
			if ( ! isset( $_POST['ced_wgei_bundle'] ) ) {
				update_post_meta( $post_id, 'ced_wgei_bundle', '' );
			}
			if ( ! isset( $_POST['ced_wgei_multipack'] ) ) {
				update_post_meta( $post_id, 'ced_wgei_multipack', '' );
			}
			if ( ! isset( $_POST['ced_wgei_identifier_exists'] ) ) {
				update_post_meta( $post_id, 'ced_wgei_identifier_exists', '' );
			}
		}
	}

	public function ced_facebook_get_orders() {

		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$file_order = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-order.php';
			if ( file_exists( $file_order ) ) {
				include_once $file_order;
			}

			$order_obj = new Class_CedFacebookOrders();
			$order_obj->ced_facebook_get_the_orders();
			wp_die();
		}
	}

	public function ced_fmcw_complete_dispatch_order() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$page_id              = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';
			$facebook_order_id    = isset( $_POST['facebook_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['facebook_order_id'] ) ) : '';
			$woo_order_id         = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$trackNumber          = isset( $_POST['trackNumber'] ) ? trim( sanitize_text_field( $_POST['trackNumber'] ) ) : false;
			$shipping_provider_id = isset( $_POST['shipping_provider_id'] ) ? sanitize_text_field( $_POST['shipping_provider_id'] ) : false;
			if ( ! empty( $trackNumber ) && ! empty( $shipping_provider_id ) && ! empty( $facebook_order_id ) ) {
				$onbuy_order_details = get_post_meta( $woo_order_id, '_fmcw_order_complete_details', true );
				$product_data        = array();
				if ( isset( $onbuy_order_details['items'] ) && ! empty( $onbuy_order_details['items'] ) ) {
					foreach ( $onbuy_order_details['items'] as $key => $value ) {
						$data['items'][] = array(
							'source_id' => $value['source_id'],
							'quantity'  => $value['quantity'],
						);
					}
				}
				$file_order = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-order.php';
				if ( file_exists( $file_order ) ) {
					include_once $file_order;
				}
				$data['carrier']         = $shipping_provider_id;
				$data['tracking_number'] = $trackNumber;
				$order_obj               = new Class_CedFacebookOrders();
				$response                = $order_obj->ced_facebook_ship_orders( $page_id, $facebook_order_id, $data );
				if ( $response['success'] && ! empty( $response['success'] ) ) {
					if ( ! isset( $response['data']['success'] ) || 1 != $response['data']['success'] ) {
						echo json_encode(
							array(
								'status'  => '200',
								'message' => 'Some Error occured. Please try again',
							)
						);
						die;
					} else {
						update_post_meta(
							$woo_order_id,
							'_facebook_order_details',
							array(
								'trackingNo' => $trackNumber,
								'provider'   => $shipping_provider_id,
							)
						);
						update_post_meta( $woo_order_id, '_fmcw_order_status', 'Shipped' );
						update_post_meta( $woo_order_id, '_facebook_order_status_template', 'complete_dispatch' );
						$order = wc_get_order( $woo_order_id );
						$order->update_status( 'completed' );
						echo json_encode(
							array(
								'status'  => '200',
								'message' => 'Facebook Order Dispatched Successfully',
							)
						);
						die;
					}
				} elseif ( isset( $response['error'] ) && ! empty( $response['error'] ) ) {
					echo json_encode(
						array(
							'status'  => '402',
							'message' => $response['error']['message'],
						)
					);
					die;
				}
			} else {
				echo json_encode( array( 'message' => 'Please fill in all the details' ) );
				die;
			}
		}
	}

	public function ced_facebook_cancel_order() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$page_id             = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';
			$facebook_order_id   = isset( $_POST['facebook_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['facebook_order_id'] ) ) : '';
			$woo_order_id        = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$cancel_reason_id    = isset( $_POST['cancel_reason_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cancel_reason_id'] ) ) : '';
			$cancel_info         = isset( $_POST['cancel_info'] ) ? sanitize_text_field( wp_unslash( $_POST['cancel_info'] ) ) : '';
			$onbuy_order_details = get_post_meta( $woo_order_id, '_fmcw_order_complete_details', true );
				$product_data    = array();
			if ( isset( $onbuy_order_details['items'] ) && ! empty( $onbuy_order_details['items'] ) ) {
				foreach ( $onbuy_order_details['items'] as $key => $value ) {
					$data['items'][] = array(
						'source_id' => $value['source_id'],
						'quantity'  => $value['quantity'],
					);
				}
			}
				$data['code']       = $cancel_reason_id;
			   $data['description'] = $cancel_info;
			   $file_order          = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-order.php';
			if ( file_exists( $file_order ) ) {
				include_once $file_order;
			}
			   $order_obj = new Class_CedFacebookOrders();
			   $response  = $order_obj->ced_facebook_cancel_orders( $page_id, $facebook_order_id, $data );
			if ( isset( $response['msg'] ) && ! empty( $response['msg'] ) ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => $response['msg'],
					)
				);
				wp_die();
			} else {
				update_post_meta( $woo_order_id, '_fmcw_order_status', 'Canceled' );
				update_post_meta( $woo_order_id, '_facebook_order_status_template', 'cancel' );
				$order = wc_get_order( $woo_order_id );
				$order->update_status( 'cancelled' );
				echo json_encode(
					array(
						'status'  => 200,
						'message' => 'Order Successfully Cancelled',
					)
				);
				wp_die();
			}
		}
	}

	public function ced_facebook_refund_order() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$page_id             = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';
			$facebook_order_id   = isset( $_POST['facebook_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['facebook_order_id'] ) ) : '';
			$woo_order_id        = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$refund_reason_id    = isset( $_POST['refund_reason_id'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_reason_id'] ) ) : '';
			$refund_info         = isset( $_POST['refund_info'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_info'] ) ) : '';
			$onbuy_order_details = get_post_meta( $woo_order_id, '_fmcw_order_complete_details', true );
				$product_data    = array();
			if ( isset( $onbuy_order_details['items'] ) && ! empty( $onbuy_order_details['items'] ) ) {
				foreach ( $onbuy_order_details['items'] as $key => $value ) {
					$data['items'][] = array(
						'source_id' => $value['source_id'],
						'quantity'  => $value['quantity'],
					);
				}
			}
				$data['code']       = $refund_reason_id;
			   $data['description'] = $refund_info;
			   $file_order          = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-order.php';
			if ( file_exists( $file_order ) ) {
				include_once $file_order;
			}
			   $order_obj = new Class_CedFacebookOrders();
			   $response  = $order_obj->ced_facebook_refund_orders( $page_id, $facebook_order_id, $data );
			if ( isset( $response['msg'] ) && ! empty( $response['msg'] ) ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => $response['msg'],
					)
				);
				wp_die();
			} elseif ( isset( $response['data']['error'] ) && ! empty( $response['data']['error'] ) ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => $response['data']['error']['error_user_msg'],
					)
				);
				wp_die();
			} else {
				update_post_meta( $woo_order_id, '_fmcw_order_status', 'Refunded' );
				update_post_meta( $woo_order_id, '_facebook_order_status_template', 'refund' );
				$order = wc_get_order( $woo_order_id );
				$order->update_status( 'refunded' );
				echo json_encode(
					array(
						'status'  => 200,
						'message' => 'Order Successfully Refunded',
					)
				);
				wp_die();
			}
		}
	}

	public function ced_fmcw_add_order_metabox() {
		global $post;
		$product = wc_get_product( $post->ID );
		add_meta_box(
			'ced_fmcw_manage_orders_metabox',
			__( 'Manage Marketplace Orders', 'woocommerce-fb-integration' ) . wc_help_tip( __( 'Please send shipping confirmation or order cancellation request.', 'woocommerce-fb-integration' ) ),
			array( $this, 'ced_fmcw_render_orders_metabox' ),
			'shop_order',
			'advanced',
			'high'
		);
	}

	public function ced_fmcw_render_orders_metabox() {
		global $post;
		$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';
		if ( ! is_null( $order_id ) ) {
			$order = wc_get_order( $order_id );

			$order_from  = get_post_meta( $order_id, '_ced_fmcw_order_id', true );
			$marketplace = strtolower( $order_from );

			$template_path = CED_FMCW_DIRPATH . 'admin/partials/order-template.php';
			if ( file_exists( $template_path ) ) {
				include_once $template_path;
			}
		}
	}
	public function ced_save_catalog() {
		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$business_id                                       = isset( $_POST['business_id'] ) ? sanitize_text_field( $_POST['business_id'] ) : '';
			$catalog_id                                        = isset( $_POST['catalog_id'] ) ? sanitize_text_field( $_POST['catalog_id'] ) : '';
			$catalog_and_page_id                               = get_option( 'ced_fmcw_catalog_and_page_id', array() );
			$catalog_and_page_id[ $business_id ]['catalog_id'] = $catalog_id;
			update_option( 'ced_fmcw_catalog_and_page_id', $catalog_and_page_id );
			update_option( 'ced_catalog_saved', 'true' );
			update_option( 'ced_fmcw_setup_completed', 'yes' );
			echo 'success';
		}
		wp_die();
	}

	public function ced_facebook_shopping_list_per_page() {

		$check_ajax = check_ajax_referer( 'ced-fmcw-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$_per_page = isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : '10';
			update_option( 'ced_facebook_shopping_list_per_page', $_per_page );
			wp_die( esc_attr( $_per_page ) );
		}
	}


}
