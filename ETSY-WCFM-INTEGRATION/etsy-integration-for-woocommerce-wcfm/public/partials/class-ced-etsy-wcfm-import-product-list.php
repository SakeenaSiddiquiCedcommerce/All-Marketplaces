<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
// if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH.'wp-admin/includes/screen.php' );
	require_once(ABSPATH.'wp-admin/includes/class-wp-screen.php' );
	require_once(ABSPATH.'wp-admin/includes/template.php' );
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
// }

ced_etsy_wcfm_get_header();
$GLOBALS['hook_suffix'] = '';
class EtsyListImportedProducts extends WP_List_Table {

	public $show_reset;
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Product import', 'woocommerce-etsy-integration' ), //singular name of the listed records
			'plural'   => __( 'Products import', 'woocommerce-etsy-integration' ), //plural name of the listed records
			//'ajax'     => true //does this table support ajax?
		) );
	}
	public function get_pagenum() {
		$str = get_query_var('ced-etsy');
		$current_page =  str_replace("page/", "",$str);
		if (str_contains($str, 'page/')) { 
			if(!isset($current_page) || empty($current_page) ) {
				$current_page = 1;
			}
		}else{
			$current_page = 1;
		}
		return $current_page;
	}

	public function prepare_items() {

		$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
		if( in_array( 'etsy', $enabled_marketplaces )  ) {
			global $wpdb;
			$shop_name = isset($_GET['shop_name']) ?  sanitize_text_field( wp_unslash($_GET['shop_name'])) : '' ;
			$per_page  = apply_filters( 'ced_etsy_products_import_per_page', 20 );

			$columns   = $this->get_columns();
			$hidden    = array();
			$sortable  = $this->get_sortable_columns();

			// Column headers
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$current_page = $this->get_pagenum();
			
			if ( 1 < $current_page ) {
				$offset = $per_page * ( $current_page - 1 );
			} else {
				$offset = 0;
			}

			$count       = self::get_count( $per_page ,$current_page ,$shop_name );
			$this->set_pagination_args( array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page )
			) );

			if (!$this->current_action()) {
				$this->items = self::get_product_details( $per_page, $offset , $shop_name  );
				$this->renderHTML();
			} else {
				$this->process_bulk_action();
			}
		}
	}


	public function get_product_details( $per_page = '', $offset = 1 , $shop_name = '' ) {
			// Check clicked button of filter
			if (isset($_POST['filter_button'])) {
				if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
					return;
				}
				$status_sorting   = isset($_POST['status_sorting']) ? sanitize_text_field( wp_unslash($_POST['status_sorting'])) : '' ;
				$current_url      = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field( wp_unslash($_SERVER['REQUEST_URI'])) : '' ;
				$shop_name        = isset($_GET['shop_name']) ? sanitize_text_field( wp_unslash($_GET['shop_name'])) : '' ;
				wp_redirect( $current_url . '&status_sorting=' . $status_sorting  .'&shop_name=' . $shop_name );
			}

			$args = 'active';
			if ( ! empty( $_GET['status_sorting'] ) ) {
				$status_sorting = isset($_GET['status_sorting']) ? sanitize_text_field( wp_unslash($_GET['status_sorting'])) : '';
				$args = $status_sorting;
			}

			$product_to_show         = array();
			$shop_name               = isset($_GET['shop_name']) ?  sanitize_text_field( wp_unslash($_GET['shop_name'])) : '' ;
			if( empty( $offset ) ) {
				$offset = 0;
			}
			$params   = array(
				'offset'   => $offset,
				'limit'    => $per_page,
				'state'  => $args,
			);

			$shop_id = ced_etsy_wcfm_get_shop_id( $shop_name );
			$action  = "application/shops/{$shop_id}/listings";
			/** Refresh token
			 *
			 * @since 2.0.0
			 */
			do_action( 'ced_etsy_wcfm_refresh_token', $shop_name );
			$response = Ced_Etsy_WCFM_API_Request( $shop_name )->get( $action, $shop_name, $params );
			$total_count=isset($response['count'])?$response['count']:0;
		
			if ($total_count==0) {
				update_option( 'ced_etsy_wcfm_total_import_product_' . $shop_name, array() );
				
				return array();
			}

			// Update total Avaiable Items
			update_option( 'ced_etsy_wcfm_total_import_product_' . $shop_name, $response['count'] );
			if ( isset( $response['results'][0] ) ) {
				foreach ( $response['results'] as $key => $value ) {
					$products_to_list['name']       = $value['title'];
					$products_to_list['price']      = (float) $value['price']['amount'] / $value['price']['divisor'];
					$products_to_list['stock']      = $value['quantity'];
					$products_to_list['status']     = $value['state'];
					$products_to_list['url']        = $value['url'];
					$products_to_list['listing_id'] = $value['listing_id'];
					$products_to_list['shop_name']  = $shop_name;
					$listing_id                     = $value['listing_id'];
					$action_images                  = "application/listings/{$listing_id}/images";
					$image_details                  = Ced_Etsy_WCFM_API_Request( $shop_name )->get( $action_images, $shop_name );
						$products_to_list['image']  = isset( $image_details['results'][0]['url_170x135'] ) ? $image_details['results'][0]['url_170x135'] : '';
					$product_to_show[]              = $products_to_list;
					$post_status_type               = $value['state']=="active"?"publish":'draft';
					$if_product_exists              = get_product_id_by_shopname_and_listing_id( $shop_name, $value['listing_id'], $post_status_type );
					if ( ! empty( $if_product_exists ) ) {
						$count[] = isset( $if_product_exists ) ? $if_product_exists : '';
							// Counted imported Items
						update_option( 'ced_etsy_total_created_product_' . $shop_name, $count );
					}
				}
				return $product_to_show;

			}
		
	}


	public function no_items() {
		esc_html_e( 'No Products To Show.', 'woocommerce-etsy-integration' );
	}

	/**
	* 
	* Function to count number of responses in result
	*
	*/
	public function get_count( $per_page = '', $page_number = '', $shop_name = '' ) {

		$total_items = get_option( 'ced_etsy_wcfm_total_import_product_' . $shop_name , array() );
		if( !empty( $total_items )){
			
			return $total_items;
		} else {
			return 0;
		}
		
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
		$post_status_type  = $item['status']=="active"?"publish":'draft';
		$if_product_exists = get_product_id_by_shopname_and_listing_id(  $item['shop_name'], $item['listing_id'], $post_status_type );
	 	if (!empty( $if_product_exists ) ) {
	 		update_option( 'ced_product_is_availabe_in_woo_' . $item['listing_id'] , $item['listing_id'] );
	 		$image_path =  CED_ETSY_WCFM_URL . 'public/images/check.png';
	 		return sprintf( '<img class="check_image" src="' .$image_path . '" alt="Done">' );
	 	} else {
	 		return sprintf(
	 			'<input type="checkbox" name="ced_wcfm_imp_listing_id[]" class="ced_wcfm_imp_listing_id" value="%s" />' , $item['listing_id']
	 		);
	 	}	
	}

	public function column_name($item) {
		$post_status_type  = $item['status']=="active"?"publish":'draft';
		$product_id = get_product_id_by_shopname_and_listing_id(  $item['shop_name'], $item['listing_id'],$post_status_type );
		$product_id = isset( $product_id ) ? $product_id : '';
		$editUrl    = 'javascript:void(0)';

		$actions['import'] = '<a href="' .$editUrl . '" class="import_single_product" data-listing-id="'.$item['listing_id'].'"> Import</a>';
		echo '<b><a class="ced_etsy_prod_name" href="' . esc_attr($editUrl) . '" >' . esc_attr( $item['name'] ) . '</a></b>';
		return $this->row_actions( $actions , true );

	}

	public function column_stock( $item) {
		return $item['stock'];
	}

	public function column_price( $item) {
		$price = isset($item['price']) ? $item['price'] : '' ;
		echo wc_price($price);
	}
	public function column_status( $item) {	
		$status = $item['status'];
		if ( !empty($status) ) {
			echo $item['status'];
		}
	}

	public function column_view_url( $item ){
		echo "<a href='".$item['url']."'>View</a>";
	}

	public function get_columns() {
		$columns = array(
			'cb'                           =>   '<input type="checkbox" />',
			'name'                         => __( 'Name', 'woocommerce-etsy-integration' ),
			'price'                        => __( 'Price', 'woocommerce-etsy-integration' ),	
			'stock'                        => __( 'Stock', 'woocommerce-etsy-integration' ),
			'status'                       => __( 'Status', 'woocommerce-etsy-integration' ),
			'view_url'                     => __(' View Link' , 'woocommerce-etsy-integration' )
		);
		$columns = apply_filters( 'ced_etsy_alter_product_table_columns', $columns );
		return $columns;
	}

	protected function bulk_actions( $which = '' ) {
		if ('top' == $which ) :
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

			echo '<label for="bulk-import-action-selector' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_html(__( 'Select bulk action' )) . '</label>';
			echo '<select name="action' . esc_attr($two) . '" class="bulk-import-action-selectorf">';
			echo '<option value="-1">' . esc_html(__( 'Bulk Actions' )) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr($name) . '"' . esc_attr($class) . '>' . esc_attr($title) . "</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => 'ced_esty_wcfm_import_product_bulk_optration' ) );
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
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<?php
						$shop_name = isset($_GET['shop_name']) ?  sanitize_text_field( wp_unslash($_GET['shop_name'])) : '' ;
						$status_actions = array(
							'draft'     => __( 'Draft' ,'woocommerce-etsy-integration' ),
							'active'    => __( 'Active' ,'woocommerce-etsy-integration' ),
							'inacitve'  => __( 'Inactive' ,'woocommerce-etsy-integration' ),
							'expired'   => __( 'Expired' ,'woocommerce-etsy-integration' ),
						);
						$previous_selected_status = isset($_GET['status_sorting']) ? sanitize_text_field( wp_unslash($_GET['status_sorting'])) : '';
						$count =$this->get_count('','',$shop_name);
						 // print_r($count);
						echo '<input type="hidden" name="count_number" id="ced_count_number" value="'.$count.'">';
						echo '<div class="ced_etsy_wrap">';
						echo '<form method="post" action="">';
						wp_nonce_field( 'manage_products', 'manage_product_filters' );
						echo '<div class="ced_etsy_top_wrapper">';
						
						echo '<select name="status_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html(__( 'Import By Status', 'woocommerce-etsy-integration' )) . '</option>';
							foreach ( $status_actions as $name => $title ) {
								$selectedStatus = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
								$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
								echo '<option ' . esc_attr($selectedStatus) . ' value="' . esc_attr($name) . '"' . esc_attr($class) . '>' . esc_attr($title) . '</option>';
							}
						echo '</select>';
						submit_button( __( ' Filter', 'ced-etsy' ), 'action', 'filter_button', false, array() );
						submit_button( __( 'Refresh' ), 'action', '', false, array( 'id' => 'ced_esty_wcfm_refresh_page' ) );
						echo '</div>';
						echo '</form>';
						echo '</div>';
						?>
						<form method="post">
							<?php
							$this->display();
							?>
						</form>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="ced_etsy_preview_product_popup_main_wrapper"></div>
			<?php
		}
	}

	$ced_etsy_import_products_obj = new EtsyListImportedProducts();
	$ced_etsy_import_products_obj->prepare_items();
