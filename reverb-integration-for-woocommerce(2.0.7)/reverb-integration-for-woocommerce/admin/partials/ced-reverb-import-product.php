<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
require_once CED_REVERB_DIRPATH . 'admin/partials/ced-reverb-header.php';?>

<?php
// here usign WP_list table to create status of all the product imported or not

/*
 *
 * Step to create wp list table in the WordPress
 *
 */

 // If WP-List table is not included then include

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp/admin/includes/class-wp-list-table.php';
}


	/**
	 * WP_List_table to show all imopted product
	 */
class ReverProductImoporter extends WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product Importer', 'woocommerce-reverb-integration' ),
				'plural'   => __( 'Products Importer', 'woocommerce-reverb-integration' ),
				'ajax'     => true,
			)
		);
	}

	// preparing items data

	public function prepare_items() {
		/**
 		* Per page filter.
 		* @since 1.0.0
 		*/
		$per_page = apply_filters( 'ced_rever_per_page_import_product', 10 );
		// Get column function
		$column = $this->get_columns();
		// hidden would be blank array
		$hidden = array();
		// get sortable column fuction
		$sortable = $this->get_sortable_columns();
		// column_headers
		$this->_column_headers = array( $column, $hidden, $sortable );
		// current page no
		$current_page = $this->get_pagenum();
		// make pagination
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		// get product details and store in the item value
		$this->items = self::get_product_details( $per_page, $offset );
		// get  count the no of items in the array by the api call
		$count = (int) self::get_count( $per_page, $current_page );
		// set pagination to the page
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		// make condtion to execute this whole thing by the action
		if ( ! $this->current_action() ) {
			$this->items = self::get_product_details( $per_page, $offset );
			$this->renderHTMl();
		} else {
			$this->process_bulk_action();
		}
	}


	public function get_product_details( $per_page = '', $offset = 1 ) {
		if ( ! session_id() ) {
			session_start();
		}

		if ( isset( $_POST['filter_button'] ) ) {
			$nonce_verification_filter = isset( $_POST['manage_product_filters'] ) ? sanitize_text_field( $_POST['manage_product_filters'] ) : '';

			if ( wp_verify_nonce( $nonce_verification_filter, 'manage_products' ) ) {
				$status_sorting = isset( $_POST['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['status_sorting'] ) ) : '';
				$current_url    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				wp_redirect( $current_url . '&status_sorting=' . $status_sorting );
			}
		}

		if ( ! empty( $_GET['status_sorting'] ) ) {
			$status_sorting = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
			$status         = $status_sorting;
		} else {
			$status = 'live';
		}

		require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		$reverbRequest   = new Ced_Reverb_Curl_Request();
		$resultArray     = array();
		$resultJsonArray = $reverbRequest->ced_reverb_get_request( 'my/listings?per_page=' . $per_page . '&offset=' . $offset . '&state=all&state=' . $status );

		$response       = $resultJsonArray;
		$total_products = ! empty( $response['total'] ) ? $response['total'] : 0;

		// Update all items in the option table
		update_option( 'ced_reverb_total_product_on_shop', $total_products );

		$saved_reverb_details = get_option( 'ced_reverb_details', array() );
		$products_detail      = ! empty( $response['listings'] ) ? $response['listings'] : array();
		$product_to_show      = array();
		$reverb_price         = get_option( 'ced_reverb_product_prices_api', array() );
		if ( ! empty( $products_detail ) ) {
			// print_r($products_detail);
			foreach ( $products_detail as $key => $value ) {
				$reverb_price[ $value['id'] ]  = $value['seller_price']['amount'];
				$product_to_show['listing_id'] = $value['id'];
				if ( is_array( $value['photos'] ) && ! empty( $value['photos'] ) ) {
					$product_to_show['image_url'] = $value['photos'][0]['_links']['thumbnail'];
				} else {
					$product_to_show['image_url'] = '';
				}
				$product_to_show['name']     = $value['title'];
				$product_to_show['sku']      = $value['sku'];
				$product_to_show['price']    = $value['seller_price']['amount'];
				$product_to_show['view_url'] = $value['_links']['web'];
				// $product_to_show['shop_id']    = $value['shop_id'];
				$product_to_show['shop_name'] = $value['shop_name'];
				$product_to_show['stock']     = $value['inventory'];
				$product_to_show['status']    = $value['state']['description'];
				$product_to_show['categories']    = $value['categories'][0]['full_name'];
				$product_list_in_wp_list[]    = $product_to_show;
				$config_details               = get_option( 'ced_reverb_configuration_details', array() );
				$account_type                 = '';

				$if_product_exists = get_posts(
					array(
						'numberposts' => -1,
						'post_type'   => 'product',
						'meta_query'  => array(
							array(
								'key'     => 'ced_reverb_listing_id' . $account_type,
								'value'   => $value['id'],
								'compare' => '=',
							),
						),
						'fields'      => 'ids',
					)
				);
				if ( ! empty( $if_product_exists ) ) {
					$count[] = $if_product_exists[0];
					update_option( 'ced_reveb_total_imoported_data', count( $count ) );
				}
			}
			update_option( 'ced_reverb_product_prices_api', $reverb_price );
			return $product_list_in_wp_list;
		}
	}

	public function no_items() {
		esc_html_e( 'No Products To Show.', 'woocommerce-reverb-integration' );
	}

	/**
	 *
	 * Function to count number of responses in result
	 */
	public function get_count( $per_page = '', $page_number = '', $shop_name = '' ) {
		$total_available_items = get_option( 'ced_reverb_total_product_on_shop', array() );
		$total_available_items = isset( $total_available_items ) ? $total_available_items : '0';
		return $total_available_items;

	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */

	public function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	public function column_cb( $item ) {

		$config_details    = get_option( 'ced_reverb_configuration_details', array() );
		$account_type      = '';
		$if_product_exists = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'product',
				'meta_query'  => array(
					array(
						'key'     => 'ced_reverb_listing_id' . $account_type,
						'value'   => $item['listing_id'],
						'compare' => '=',
					),
				),
				'fields'      => 'ids',
			)
		);
		if ( ! empty( $if_product_exists ) ) {
			$image_path = CED_REVERB_URL . 'admin/images/check.png';
			return sprintf( '<img class="check_image" src="' . $image_path . '" alt="Done">' );

		} else {

			return sprintf(
				'<input type="checkbox" name="reverb_import_listing_id[]" class="reverb_import_listing_id" value="%s" />',
				$item['listing_id']
			);
		}
	}

	public function column_name( $item ) {

		$actions['id']     = 'ID:' . $item['listing_id'];
		echo '<b><a class="ced_reverb_prod_name" href="" >' . esc_attr( $item['name'] ) . '</a></b>';
		return $this->row_actions( $actions, true );

	}
	public function column_product_action($item) {
		// $actions['id']     = 'ID:' . $item['listing_id'];
		$actions['import'] = '<a href="javascript:void(0)" class="ced_reverb_single_product" data-listing-id="' . $item['listing_id'] . '"> Import</a>';
		return $this->row_actions( $actions, true );

	}
	public function column_view_url( $item ) {
		$reverb_icon  = CED_REVERB_URL . 'admin/images/reverb.png';
		echo '<a href="' . esc_url(  $item['view_url']['href'] ) . '" target="_blank"><img class="ced_reverb_status" src="' . esc_url( $reverb_icon ) . '" height="" width="50"></a>';
	}
	public function column_image( $item ) {
		if ( is_array( $item['image_url'] ) && ! empty( $item['image_url'] ) ) {
			$image = $item['image_url']['href'];
		} else {
			$image = '';
		}

		return '<img height="50" width="50" src="' . esc_url( $image ) . '">';
	}

	public function column_category($item) {
		echo esc_attr($item['categories']);
	}
	public function column_details( $item ) {
		echo '<p>';
		echo '<strong>Regular price: </strong>' . esc_attr( $item['price'] ) . '</br>';
		echo '<strong>SKU : </strong>' . esc_attr( $item['sku'] ) . '</br>';
		echo "<strong>Stock status: </strong><span class='" . esc_attr( $item['status'] ) . "'>" . esc_attr( ucwords( $item['status'] ) ) . '</span></br>';
		echo '<strong>Stock qty: </strong>' . esc_attr( $item['stock'] ) . '</br>';

		echo '</p>';
	}

	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'image'    => __( 'Product Image', 'woocommerce-reverb-integration' ),
			'name'     => __( 'Product Name', 'woocommerce-reverb-integration' ),
			'category'     => __( 'Product Category', 'woocommerce-reverb-integration' ),
			'details'     => __( 'Product Details', 'woocommerce-reverb-integration' ),
			'view_url' => __( 'View on Reverb', 'woocommerce-reverb-integration' ),
			'product_action' => __( 'Action', 'woocommerce-reverb-integration' ),
		);
		/**
 		* Filter hook for filtering columns on product page of plugin.
 		* @since 1.0.0
 		*/
		$columns = apply_filters( 'ced_reverb_alter_product_table_columns', $columns );
		return $columns;
	}

	protected function bulk_actions( $which = '' ) {
		if ( 'top' == $which ) :
			if ( is_null( $this->_actions ) ) {
				$this->_actions = $this->get_bulk_actions();
				/**
				 * Filters the list table Bulk Actions drop-down.
				 *
				 * The dynamic portion of the hook name, `$this->screen->id`, refers
				 * to the ID of the current screen, usually a string.
				 *
				 * This filter can currently only be used to remove bulk actions.
				 *
				 * @since 3.5.0
				 *
				 * @param array $actions An array of the available bulk actions.
				 */
				$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
				$two            = '';
			} else {
				$two = '2';
			}

			if ( empty( $this->_actions ) ) {
				return;
			}

			echo '<label for="bulk-import-action-selector' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_html( __( 'Select bulk action' ) ) . '</label>';
			echo '<select name="action' . esc_attr( $two ) . '" class="ced_reverb_bulk-import-action-selector">';
			echo '<option value="-1">' . esc_html( __( 'Bulk Actions' ) ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => 'ced_reverb_import_product_bulk_optration' ) );
			echo "\n";
			endif;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'import_product' => 'Import Product',
		);
		return $actions;
	}
	public function renderHTML() {
		?>
				<div class="ced_reverb_heading">
					<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
					<div class="ced_reverb_child_element">
						<ul type="disc">
							<li><?php echo esc_html_e( 'Reverb products will be displayed here.By default active products are displayed.' ); ?></li>
							<li><?php echo esc_html_e( 'You can fetch the reverb product manually by selecting it using the checkbox on the left side in the product list table and using import operation from the Bulk Actions dropdown and then Apply.' ); ?></li>
							<li><?php echo esc_html_e( 'You can filter out the reverb products on the basis of the status using Import By Status dropdown.' ); ?></li>
						</ul>
					</div>
				</div>
				<div id="post-body" class="metabox-holder columns-2 productsimporter_wrap">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
					<?php
					$shop_name                = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
					$status_actions           = array(
						'live'  => __( 'Live', 'woocommerce-reverb-integration' ),
						'draft' => __( 'Draft', 'woocommerce-reverb-integration' ),
						'ended' => __( 'Ended', 'woocommerce-reverb-integration' ),
					);
					$previous_selected_status = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';

					echo '<div class="ced_reverb_wrap">';
					echo '<form method="post" action="">';
						wp_nonce_field( 'manage_products', 'manage_product_filters' );
						$total_created_product      = get_option( 'ced_reveb_total_imoported_data', true );
						$total_reverb_total_product = get_option( 'ced_reverb_total_product_on_shop' );
							echo '<div class="ced_reverb_top_wrapper">';
								echo '<select name="status_sorting" class="select_boxes_product_page">';
									echo '<option value="">' . esc_html( __( 'Import By Status', 'woocommerce-reverb-integration' ) ) . '</option>';
					foreach ( $status_actions as $name => $title ) {
						$selectedStatus = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
						$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedStatus ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
									echo '</select>';
							submit_button( __( ' Filter', 'ced-reverb' ), 'action', 'filter_button', false, array() );
						echo '</div>';
						echo '</form>';
						echo '</div>';
					?>

						<form method="post">
							<input type="hidden" id="nonce_verification" name="nonce_verification" value="<?php echo esc_attr( wp_create_nonce( 'nonce_verification' ) ); ?>">
							<?php
							$this->display();
							?>
						</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="ced_reverb_preview_product_popup_main_wrapper"></div>
				<?php
	}

}

$ReverProductImoporter_obj = new ReverProductImoporter();
$ReverProductImoporter_obj->prepare_items();
