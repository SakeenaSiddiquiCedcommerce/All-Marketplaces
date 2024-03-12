<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/header.php';
class TokopediaListProducts extends WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'woocommerce-tokopedia-integration' ),
				'plural'   => __( 'Products', 'woocommerce-tokopedia-integration' ),
				'ajax'     => true,
			)
		);
	}

	public function prepare_items() {

		global $wpdb;

		$per_page              = apply_filters( 'ced_tokopedia_products_per_page', 10 );
		$post_type             = 'product';
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();

		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::get_product_details( $per_page, $current_page, $post_type );

		$count = self::get_count( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {

			$this->items = self::get_product_details( $per_page, $current_page, $post_type );
			$this->renderHTML();

		} else {

			$this->process_bulk_action();
		}
	}

	public function get_product_details( $per_page = '', $page_number = 1, $post_type ) {

		$filterFile = CED_TOKOPEDIA_DIRPATH . 'admin/partials/products-filters.php';
		if ( file_exists( $filterFile ) ) {
			require_once $filterFile;
		}

		$instanceOf_FilterClass = new FilterClass();
		$shop_name              = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$args                   = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) || isset( $args['s'] ) ) {
			$args = $args;
		} else {
			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
			);
		}

		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;
		$wooProducts  = array();

		foreach ( $product_data as $key => $value ) {

			$prodID        = $value->ID;
			$productDATA   = wc_get_product( $prodID );
			$productDATA   = $productDATA->get_data();
			$wooProducts[] = $productDATA;
		}
		if ( isset( $_POST['filter_button'] ) ) {

			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$wooProducts = $instanceOf_FilterClass->ced_tokopedia_filters_on_products( $wooProducts, $shop_name );
		} elseif ( isset( $_POST['s'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$instanceOf_FilterClass->productSearch_box( $wooProducts, $shop_name );
		}

		return $wooProducts;
	}


	public function GetFilteredData( $per_page, $page_number ) {

		if ( isset( $_GET['status_sorting'] ) || isset( $_GET['pro_cat_sorting'] ) || isset( $_GET['pro_type_sorting'] ) || isset( $_GET['searchBy'] ) || isset( $_GET['stock_status'] ) ) {

			if ( ! empty( $_REQUEST['pro_cat_sorting'] ) ) {
				$pro_cat_sorting = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';

				if ( ! empty( $pro_cat_sorting ) ) {

					$selected_cat          = array( $pro_cat_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_cat';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_cat;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_type_sorting'] ) ) {

				$pro_type_sorting = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_sorting'] ) ) : '';
				if ( ! empty( $pro_type_sorting ) ) {
					$selected_type         = array( $pro_type_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_type';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_type;
					$args['tax_query'][]   = $tax_query;
				}
			}
			if ( ! empty( $_REQUEST['status_sorting'] ) ) {

				$status_sorting = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
				$shop_name      = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
				if ( ! empty( $status_sorting ) ) {
					$meta_query = array();

					if ( 'Uploaded' == $status_sorting ) {

						$args['orderby'] = 'meta_value_num';
						$args['order']   = 'ASC';

						$meta_query[] = array(

							'key'     => '_ced_tokopedia_upload_id_' . $shop_name,
							'compare' => 'EXISTS',
						);
					} elseif ( 'NotUploaded' === $status_sorting ) {
						$meta_query[] = array(

							'key'     => '_ced_tokopedia_upload_id_' . $shop_name,
							'compare' => 'NOT EXISTS',
						);
					}
				}
			}
			if ( ! empty( $_REQUEST['searchBy'] ) ) {
				$searchBy = isset( $_GET['searchBy'] ) ? sanitize_text_field( wp_unslash( $_GET['searchBy'] ) ) : '';
				if ( ! empty( $searchBy ) ) {
					$args['s'] = $searchBy;
				}
			}

			if ( ! empty( $_REQUEST['stock_status'] ) ) {
				$stock_status = isset( $_GET['stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['stock_status'] ) ) : '';

				$meta_query[] = array(
					'key'     => '_stock_status',
					'compare' => '=',
					'value'   => $stock_status,
				);

			}

			if ( ! empty( $_GET['stock_status'] ) && ! empty( $_GET['status_sorting'] ) ) {
				$meta_query['relation'] = 'AND';
			}
			$args['meta_query'] = $meta_query;

			$args['post_type']      = 'product';
			$args['posts_per_page'] = $per_page;
			$args['paged']          = $page_number;
			return $args;
		}

	}

	public function no_items() {
		esc_html_e( 'No Products To Show.', 'woocommerce-tokopedia-integration' );
	}

	/**
	 *
	 * Function to count number of responses in result
	 */
	public function get_count( $per_page, $page_number ) {

		$args = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) ) {
			$args = $args;
		} else {
			$args = array( 'post_type' => 'product' );
		}
		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;
		$product_data = $loop->found_posts;

		return $product_data;
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

		return sprintf(
			'<input type="checkbox" name="tokopedia_product_ids[]" class="tokopedia_products_id" value="%s" />',
			$item['id']
		);
	}

	public function column_name( $item ) {

		$shop_name     = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$editUrl       = get_edit_post_link( $item['id'], '' );
		$actions['id'] = 'ID:' . $item['id'];
		echo '<b><a class="ced_tokopedia_prod_name" href="' . esc_attr( $editUrl ) . '" >' . esc_attr( substr( $item['name'], 0, 144 ) ) . '</a></b>';
		return $this->row_actions( $actions );

	}

	public function column_profile( $item ) {
		$shop_name                    = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$get_profile_id_of_prod_level = get_post_meta( $item['id'], 'ced_tokopedia_profile_assigned' . $shop_name, true );

		if ( ! empty( $get_profile_id_of_prod_level ) ) {
			global $wpdb;
			$profile_name = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_profiles WHERE `id`=%s ", $get_profile_id_of_prod_level ), 'ARRAY_A' );

			echo '<b>' . esc_attr( $profile_name[0]['profile_name'] ) . '</b>';
			$profile_id = $get_profile_id_of_prod_level;
		} else {
			$get_tokopedia_category_id = '';
			$category_ids              = isset( $item['category_ids'] ) ? $item['category_ids'] : array();
			foreach ( $category_ids as $index => $data ) {
				$get_tokopedia_category_id_data = get_term_meta( $data );
				$get_tokopedia_category_id      = isset( $get_tokopedia_category_id_data[ 'ced_tokopedia_mapped_category_' . $shop_name ] ) ? $get_tokopedia_category_id_data[ 'ced_tokopedia_mapped_category_' . $shop_name ] : '';
				if ( ! empty( $get_tokopedia_category_id ) ) {
					break;
				}
			}
			if ( ! empty( $get_tokopedia_category_id ) ) {
				foreach ( $get_tokopedia_category_id as $key => $tokopedia_id ) {
					$get_tokopedia_profile_assigned = get_option( 'ced_woo_tokopedia_mapped_categories_name' );
					$get_tokopedia_profile_assigned = isset( $get_tokopedia_profile_assigned[ $shop_name ][ $tokopedia_id ] ) ? $get_tokopedia_profile_assigned[ $shop_name ][ $tokopedia_id ] : '';
				}

				if ( isset( $get_tokopedia_profile_assigned ) && ! empty( $get_tokopedia_profile_assigned ) ) {
					echo '<b>' . esc_attr( $get_tokopedia_profile_assigned ) . '</b>';
				}
			} else {
				echo '<b class="not_completed">' . esc_html( __( 'Profile Not Assigned', 'woocommerce-tokopedia-integration' ) ) . '</b>';
// 				$actions['edit'] = '<a href="javascript:void(0)" class="ced_tokopedia_profiles_on_pop_up" data-shopid="' . $shop_name . '" data-product_id="' . esc_attr( $item['id'] ) . '">' . esc_html( __( 'Assign s', 'woocommerce-tokopedia-integration' ) ) . '</a>';
// 				return $this->row_actions( $actions );
			}
			$profile_id = isset( $get_tokopedia_category_id_data[ 'ced_tokopedia_profile_id_' . $shop_name ] ) ? $get_tokopedia_category_id_data[ 'ced_tokopedia_profile_id_' . $shop_name ] : '';
			$profile_id = isset( $profile_id[0] ) ? $profile_id[0] : '';

		}

		if ( ! empty( $profile_id ) ) {
			$edit_profile_url  = admin_url( 'admin.php?page=ced_tokopedia&profileID=' . $profile_id . '&section=profiles-view&panel=edit&shop_name=' . $shop_name );
			$actions['edit']   = '<a href="' . $edit_profile_url . '">' . __( 'Edit', 'woocommerce-tokopedia-integration' ) . '</a>';
// 			$actions['change'] = '<a href="javascript:void(0)" class="ced_tokopedia_profiles_on_pop_up" data-shopid="' . $shop_name . '" data-product_id="' . $item['id'] . '">' . __( 'Change', 'woocommerce-tokopedia-integration' ) . '</a>';
			return $this->row_actions( $actions );
		}
	}

	public function column_stock( $item ) {
		$product      = wc_get_product( $item['id'] );
		$product_type = $product->get_type();
		$stock        = get_post_meta( $item['id'], '_stock', true );

		if ( 'variable' == $product_type ) {
			$variation   = count( $product->get_available_variations() );
			$stock_count = 0;
			$variations  = $product->get_available_variations();
			foreach ( $variations as $key => $value ) {
				$variation_stock = (int) get_post_meta( $value['variation_id'], '_stock', true );
				$stock_count     = $stock_count + $variation_stock;
			}
			if ( empty( $stock_count ) && 'instock' == get_post_meta( $item['id'], '_stock_status', true ) ) {
				$stock_count = '';
			}
			return '<b>' . $stock_count . '</b> in stock for ' . $variation . ' variants';
		} else {
			if ( empty( $stock ) && 'instock' == get_post_meta( $item['id'], '_stock_status', true ) ) {
				$stock = '';
			}
			return '<b>' . $stock . '</b> in stock for 1 variant';
		}
	}
	public function column_category( $item ) {

		foreach ( $item['category_ids'] as $key => $value ) {

			$wooCategory = get_term_by( 'id', $value, 'product_cat', 'ARRAY_A' );
			echo '<b>' . esc_attr( $wooCategory['name'] ) . '</b></br>';
			break;
		}
	}
	public function column_price( $item ) {
		$price           = isset( $item['price'] ) ? $item['price'] : '';
		$currency_symbol = get_woocommerce_currency_symbol();
		echo esc_attr( $currency_symbol ) . '&nbsp<b class="success">' . esc_attr( $price ) . '</b>';
	}
	public function column_type( $item ) {

		$product      = wc_get_product( $item['id'] );
		$product_type = $product->get_type();
		return '<b>' . esc_attr( $product_type ) . '</b>';
	}
	public function column_sku( $item ) {

		return '<b>' . $item['sku'] . '</b>';
	}
	public function column_image( $item ) {

		$image = wp_get_attachment_url( $item['image_id'] );
		return '<img height="50" width="50" src="' . $image . '">';
	}
	public function column_status( $item ) {

		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$listingId = get_post_meta( $item['id'], '_ced_tokopedia_upload_id_' . $shop_name, true );
		$view_url  = get_post_meta( $item['id'], '_ced_tokopedia_url_' . $shop_name, true );
		if ( ! empty( $listingId ) ) {
			echo '<b class="success_upload_on_tokopedia" id="' . esc_html( $item['id'] ) . '">Uploaded</b>';
		} else {
			echo '<b class="not_completed" id="' . esc_attr( $item['id'] ) . '">Not Uploaded</b>';
		}
	}


	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'image'    => __( 'Product Image', 'woocommerce-tokopedia-integration' ),
			'name'     => __( 'Product Name', 'woocommerce-tokopedia-integration' ),
			'type'     => __( 'Product Type', 'woocommerce-tokopedia-integration' ),
			'price'    => __( 'Product Price', 'woocommerce-tokopedia-integration' ),
			'profile'  => __( 'Profile Assigned', 'woocommerce-tokopedia-integration' ),
			'sku'      => __( 'Product Sku', 'woocommerce-tokopedia-integration' ),
			'stock'    => __( 'Product Stock', 'woocommerce-tokopedia-integration' ),
			'category' => __( 'Woo Category', 'woocommerce-tokopedia-integration' ),
			'status'   => __( 'Product Status', 'woocommerce-tokopedia-integration' ),
		);
		$columns = apply_filters( 'ced_tokopedia_alter_product_table_columns', $columns );
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

			echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_html( __( 'Select bulk action' ) ) . '</label>';
			echo '<select name="action' . esc_attr( $two ) . '" class="bulk-action-selector ">';
			echo '<option value="-1">' . esc_html( __( 'Bulk Actions' ) ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";
			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => 'ced_tokopedia_bulk_operation' ) );
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
			'upload_product' => 'Upload Products',
			'update_product' => 'Update Products',
			'update_price'   => 'Update Price',
			'update_stock'   => 'Update stock',
			// 'deactivate_product' =>'Deactivate Products',
			'remove_product' => 'Remove Product',
		);
		return $actions;
	}
	public function renderHTML() {
		?>
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
					<?php
					$status_actions = array(
						// setting two key with value upload and NotUploaded.
						'Uploaded'    => __( 'Uploaded', 'woocommerce-tokopedia-integration' ),
						'NotUploaded' => __( 'Not Uploaded', 'woocommerce-tokopedia-integration' ),
					);
					$stock_status   = array(
						'instock'    => __( 'In stock', 'woocommerce-tokopedia-integration' ),
						'outofstock' => __( 'Out of stock', 'woocommerce-tokopedia-integration' ),
					);
					$product_types  = get_terms( 'product_type', array( 'hide_empty' => false ) );
					$temp_array     = array();
					foreach ( $product_types as $key => $value ) {
						if ( 'simple' == $value->name || 'variable' == $value->name ) {
							$temp_array_type[ $value->term_id ] = ucfirst( $value->name );
						}
					}
					$product_types      = $temp_array_type;
					$product_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
					$temp_array         = array();
					foreach ( $product_categories as $key => $value ) {
						$temp_array[ $value->term_id ] = $value->name;
					}
					$product_categories = $temp_array;

					$previous_selected_status       = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
					$previous_selected_cat          = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';
					$previous_selected_type         = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_sorting'] ) ) : '';
					$previous_selected_stock_status = isset( $_GET['stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['stock_status'] ) ) : '';
					echo '<div class="ced_tokopedia_wrap">';
					echo '<form method="post" action="">';
					wp_nonce_field( 'manage_products', 'manage_product_filters' );

					echo '<div class="ced_tokopedia_top_wrapper">';
					echo '<select name="status_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Product Status', 'woocommerce-tokopedia-integration' ) ) . '</option>';
					foreach ( $status_actions as $name => $title ) {

						$selectedStatus = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
						$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedStatus ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';
					echo '<select name="pro_cat_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Product Category', 'woocommerce-tokopedia-integration' ) ) . '</option>';

					foreach ( $product_categories as $name => $title ) {

						$selectedCat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';

						$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedCat ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';
					echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Product Type', 'woocommerce-tokopedia-integration' ) ) . '</option>';

					foreach ( $product_types as $name => $title ) {

						$selectedType = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
						$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedType ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';
					echo '<select name="stock_status" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Stock Status', 'woocommerce-tokopedia-integration' ) ) . '</option>';
					foreach ( $stock_status as $name => $title ) {
						$selectedType = ( $previous_selected_stock_status == $name ) ? 'selected="selected"' : '';
						$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedType ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';
					$this->search_box( 'Search Products', 'search_id', 'search_product' );
					submit_button( __( 'Filter', 'ced-tokopedia' ), 'action', 'filter_button', false, array() );
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
			<div class="ced_tokopedia_preview_product_popup_main_wrapper"></div>
			<?php
	}
}

	$ced_tokopedia_products_obj = new TokopediaListProducts();
	$ced_tokopedia_products_obj->prepare_items();
