<?php
/**
 * Product listing in manage products
 *
 * @package  Woocommerce_Walmart_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_walmart_header();
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_Walmart_Products_List
 *
 * @since 1.0.0
 */
class Ced_Walmart_Products_List extends WP_List_Table {


	/**
	 * Ced_Walmart_Products_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'walmart-woocommerce-integration' ),
				'plural'   => __( 'Products', 'walmart-woocommerce-integration' ),
				'ajax'     => true,
			)
		);

	}

	/**
	 * Function for preparing data to be displayed
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		global $wpdb;
		/** Get per page number to list product
		 *
		 * @since 1.0.0
		 */
		$per_page  = apply_filters( 'ced_walmart_products_per_page', 20 );
		$_per_page = get_option( 'ced_walmart_list_per_page', '' );
		if ( ! empty( $_per_page ) ) {
			$per_page = $_per_page;
		}
		$post_type = 'product';
		$columns   = $this->get_columns();
		$hidden    = array();
		$sortable  = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		$this->items = self::ced_walmart_get_product_details( $per_page, $current_page, $post_type );

		$count = self::get_count( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::ced_walmart_get_product_details( $per_page, $current_page, $post_type );
			$this->render_html();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 * Function for get product data
	 *
	 * @since 1.0.0
	 * @param      int    $per_page    Results per page.
	 * @param      int    $page_number   Page number.
	 * @param      string $post_type   Post type.
	 */
	public function ced_walmart_get_product_details( $per_page = '', $page_number = '', $post_type = '' ) {
		$filter_file = CED_WALMART_DIRPATH . 'admin/partials/class-ced-walmart-products-filter.php';
		if ( file_exists( $filter_file ) ) {
			include_once $filter_file;
		}

		$instance_of_filter_class = new Ced_Walmart_Products_Filter();

		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';

		$args = $this->ced_walmart_get_filtered_data( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) || isset( $args['s'] ) ) {
			$args = $args;
		} else {
			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
				'post_status'    => 'publish',

			);
		}

		$loop = new WP_Query( $args );

		$product_data   = $loop->posts;
		$woo_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products   = array();
		foreach ( $product_data as $key => $value ) {
			$get_product_data                     = wc_get_product( $value->ID );
			$get_product_data                     = $get_product_data->get_data();
			$woo_products[ $key ]['category_ids'] = isset( $get_product_data['category_ids'] ) ? $get_product_data['category_ids'] : array();
			$woo_products[ $key ]['id']           = $value->ID;
			$woo_products[ $key ]['name']         = isset( $get_product_data['name'] ) ? $get_product_data['name'] : '';
			$woo_products[ $key ]['stock']        = ! empty( $get_product_data['stock_quantity'] ) ? $get_product_data['stock_quantity'] : 0;
			$woo_products[ $key ]['stock_status'] = ! empty( $get_product_data['stock_status'] ) ? $get_product_data['stock_status'] : '';
			$woo_products[ $key ]['manage_stock'] = ! empty( $get_product_data['manage_stock'] ) ? $get_product_data['manage_stock'] : '';
			$woo_products[ $key ]['sku']          = ! empty( $get_product_data['sku'] ) ? $get_product_data['sku'] : '';
			$woo_products[ $key ]['price']        = $get_product_data['price'];
			$image_url_id                         = $get_product_data['image_id'];
			$woo_products[ $key ]['image']        = wp_get_attachment_url( $image_url_id );

		}

		if ( isset( $_POST['filter_button'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->ced_walmart_filters_on_products();
		} elseif ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->product_search_box();
		}

		return $woo_products;
	}

	/**
	 * Text displayed when no data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Products To Show.', 'walmart-woocommerce-integration' );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @since 1.0.0
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="walmart_product_ids[]" class="product-id walmart_products_id" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_name( $item ) {
		$url             = get_edit_post_link( $item['id'], '' );
		$actions['id']   = '<b>ID : ' . $item['id'] . '</b>';
		$actions['edit'] = '<a href="' . esc_url( $url ) . '" target="_blank">Edit</a>';

		$lifecycleStatus = get_post_meta( $item['id'], 'ced_walmart_product_lifecycle' . wifw_environment(), true );
		if ( 'RETIRED' == $lifecycleStatus ) {
			$actions['delete'] = '<a>RETIRED </a>';
		}

		echo '<b class="product-title walmart-cool">' . esc_attr( $item['name'] ) . '</b>';
		return $this->row_actions( $actions, true );
	}

	/**
	 * Function for Category Name column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_woo_category_name( $item ) {
		$term_list     = wp_get_post_terms( $item['id'], 'product_cat', array( 'fields' => 'ids' ) );
		$cat_id        = (int) $term_list[0];
		$category_name = get_term_by( 'id', $cat_id, 'product_cat' );
		return '<b class="product-category-name">' . $category_name->name . '</b>';

	}


	/**
	 * Function for profile column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_profile( $item ) {
		$is_profile_assigned = false;
		$actions             = array();
		$category_ids        = isset( $item['category_ids'] ) ? $item['category_ids'] : array();
		$mapped_cat          = get_option( 'ced_mapped_cat' );
		if ( empty( $mapped_cat ) ) {
			$mapped_cat = array();
		} else {
			$mapped_cat = json_decode( $mapped_cat, 1 );
		}
		$category = '';
		if ( ! empty( $mapped_cat ) ) {
			foreach ( $category_ids as $index => $term_id ) {
				foreach ( $mapped_cat['profile'] as $key => $value ) {
					if ( in_array( $term_id, $value['woo_cat'] ) ) {
						$category = $key;

					}
				}
			}
		}
		if ( $category ) {
			echo '<b class="profile-name walmart-success">' . esc_attr( $category ) . '</b>';
			$format_cat_for_url = str_replace( ' & ', ' and ', $category );
			$edit_profile_url   = admin_url( 'admin.php?page=ced_walmart&section=profiles&profile_id=' . ( $format_cat_for_url ) . '&panel=edit' );
			$actions['edit']    = '<a href="' . esc_url( $edit_profile_url ) . '">' . __( 'Edit', 'walmart-woocommerce-integration' ) . '</a>';

		} else {
			$cat_mapping_section = admin_url( 'admin.php?page=ced_walmart&section=category_mapping' );
			echo '<b class="walmart-error">Category not mapped</b><p>Please map category <a href="' . esc_url( $cat_mapping_section ) . '" target="_blank"><i>here</i></a></p>';
		}

		return $this->row_actions( $actions, true );
	}

	/**
	 * Function for stock column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_stock( $item ) {
		if ( 'instock' == $item['stock_status'] ) {
			$stock_html = '<b class="walmart-success">' . __( 'In stock', 'woocommerce' ) . '</b>';
		} elseif ( 'outofstock' == $item['stock_status'] ) {
			$stock_html = '<b class="walmart-error">' . __( 'Out of stock', 'woocommerce' ) . '</b>';
		}
		if ( ! empty( $item['manage_stock'] ) ) {
			$stock_html .= ' (' . wc_stock_amount( $item['stock'] ) . ')';
		}

		echo wp_kses_post( $stock_html );
	}

	/**
	 * Function for price column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_price( $item ) {
		return '<b class="product-price">' . wc_price( $item['price'] ) . '</b>';
	}


	/**
	 * Function for sku column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_sku( $item ) {
		return '<b class="product-sku">' . ( $item['sku'] ) . '</b>';
	}

	/**
	 * Function for image column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_image( $item ) {
		return '<img height="50" width="50" src="' . esc_url( $item['image'] ) . '">';
	}


	/**
	 * Function for wfs status column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_wfs_status( $item ) {
		$errors = get_post_meta( $item['id'], '_validation_erros_wfs', true );
		if ( is_array( $errors ) && ! empty( $errors ) ) {
			$error_icon = CED_WALMART_URL . 'admin/images/error.png';
			echo '<img class="walmart-error ced_walmart_product_error"  id="ced_walmart_wfs_product_error" data-id="' . esc_attr( $item['id'] ) . '" src="' . esc_url( $error_icon ) . '" height="20" width="20" >';
			echo '<div class="ced_walmart_error_data walmart-hidden" id="ced_walmart_error_data_wfs' . esc_attr( $item['id'] ) . '"><ul>';
			foreach ( $errors as $attribute => $message ) {
				echo '<li class="walmart-error">' . esc_attr( $attribute ) . ' : ' . esc_attr( $message ) . '</li>';
			}
			echo '</ul><div>';
		} else {
			echo '<b class="walmart-error" id="' . esc_attr( $item['id'] ) . '">' . esc_html( __( 'Seller', 'walmart-woocommerce-integration' ) ) . '</b>';
		}
	}

	/**
	 * Function for status column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_status( $item ) {
		$lifecycleStatus = get_post_meta( $item['id'], 'ced_walmart_product_lifecycle' . wifw_environment(), true );
		if ( 'RETIRED' == $lifecycleStatus ) {
			echo '<b class="walmart-error">' . esc_html( __( 'RETIRED', 'walmart-woocommerce-integration' ) ) . '</b>';
		} else {
			$status = get_post_meta( $item['id'], 'ced_walmart_product_uploaded' . wifw_environment(), true );
			$errors = get_post_meta( $item['id'], '_validation_erros', true );
			if ( isset( $status ) && ! empty( $status ) ) {
				$product_state = get_post_meta( $item['id'], 'ced_walmart_product_status' . wifw_environment(), true );

				if ( 'PUBLISHED' == $product_state ) {
					echo '<b class="' . esc_attr( $product_state ) . '" id="' . esc_attr( $item['id'] ) . '">' . esc_attr( $product_state ) . '</b>';
					echo '<p><a class="pro-insight btn btn-success btn-sm" data-bs-toggle="modal" data-id="' . esc_attr( $item['id'] ) . '" data-bs-target="#insight">View Insight</a></p>';

				} else {
					echo '<b class="' . esc_attr( $product_state ) . '" id="' . esc_attr( $item['id'] ) . '">' . esc_attr( $product_state ) . '</b>';

				}
			} elseif ( is_array( $errors ) && ! empty( $errors ) ) {
				$error_icon = CED_WALMART_URL . 'admin/images/error.png';
				echo '<img class="walmart-error ced_walmart_product_error"  id="ced_walmart_product_error" data-id="' . esc_attr( $item['id'] ) . '" src="' . esc_url( $error_icon ) . '" height="20" width="20" >';
				echo '<div class="ced_walmart_error_data walmart-hidden" id="ced_walmart_error_data' . esc_attr( $item['id'] ) . '"><ul>';
				foreach ( $errors as $attribute => $message ) {
					echo '<li class="walmart-error">' . esc_attr( $attribute ) . ' : ' . esc_attr( $message ) . '</li>';
				}
				echo '</ul><div>';
			} else {
				echo '<b class="walmart-error" id="' . esc_attr( $item['id'] ) . '">' . esc_html( __( 'UNPUBLISHED', 'walmart-woocommerce-integration' ) ) . '</b>';
			}
		}

	}




	public function column_details( $item ) {
		$price          = isset( $item['price'] ) ? $item['price'] : '';
		$regular_price  = isset( $item['regular_price'] ) ? $item['regular_price'] : '';
		$sku            = isset( $item['sku'] ) ? $item['sku'] : '';
		$stock_status   = isset( $item['stock_status'] ) ? $item['stock_status'] : '';
		$stock_quantity = isset( $item['stock_quantity'] ) ? $item['stock_quantity'] : '';

		echo '<p>';
		echo '<strong>Regular price: </strong>' . esc_attr( $regular_price ) . '</br>';
		echo '<strong>Selling price: </strong>' . esc_attr( $price ) . '</br>';
		echo '<strong>SKU : </strong>' . esc_attr( $sku ) . '</br>';
		echo "<strong>Stock status: </strong><span class='" . esc_attr( $stock_status ) . "'>" . esc_attr( ucwords( $stock_status ) ) . '</span></br>';
		echo '<strong>Stock qty: </strong>' . esc_attr( $stock_quantity ) . '</br>';
		echo '</p>';
	}




	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {

		$ced_walmart_configuration_details = get_option( 'ced_walmart_configuration_details', array() );
		$wfs                               = isset( $ced_walmart_configuration_details['wfs'] ) ? $ced_walmart_configuration_details['wfs'] : '';

		if ( 'on' == $wfs ) {
			$wfs_coloumn = array( 'wfs_status' => __( 'Fulfilled By', 'walmart-woocommerce-integration' ) );
		}
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'image'             => __( 'Image', 'walmart-woocommerce-integration' ),
			'name'              => __( 'Title', 'walmart-woocommerce-integration' ),
			'profile'           => __( 'Profile', 'walmart-woocommerce-integration' ),
			'details'           => __( 'Details', 'woocommerce-etsy-integration' ),
			'woo_category_name' => __( 'Woo Category', 'walmart-woocommerce-integration' ),
			'status'            => __( 'Walmart Status', 'walmart-woocommerce-integration' ),

		);

		if ( isset( $wfs_coloumn ) ) {
			$columns = array_merge( $columns, $wfs_coloumn );
		}
		/** Get columns to list product
		 *
		 * @since 1.0.0
		 */
		$columns = apply_filters( 'ced_walmart_alter_product_table_columns', $columns );
		return $columns;
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function get_count( $per_page, $page_number ) {
		$args = $this->ced_walmart_get_filtered_data( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) ) {
			$args = $args;
		} else {
			$args = array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			);
		}
		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;
		$product_data = $loop->found_posts;

		return $product_data;
	}

	/**
	 * Function to get the filtered data
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function ced_walmart_get_filtered_data( $per_page, $page_number ) {
		if ( isset( $_GET['status_sorting'] ) || isset( $_GET['pro_cat_sorting'] ) || isset( $_GET['pro_type_sorting'] ) || isset( $_GET['s'] ) ) {
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
				if ( ! empty( $status_sorting ) ) {
					if ( 'Uploaded' == $status_sorting ) {
						$args['orderby'] = 'meta_value_num';
						$args['order']   = 'ASC';

						$meta_query[] = array(
							'key'     => 'ced_walmart_product_uploaded' . wifw_environment(),
							'compare' => 'EXISTS',
						);
					} elseif ( 'NotUploaded' == $status_sorting ) {
						$meta_query[] = array(
							'key'     => 'ced_walmart_product_uploaded' . wifw_environment(),
							'compare' => 'NOT EXISTS',
						);
					}
				}
			}

			if ( ! empty( $_REQUEST['pro_status_sorting'] ) ) {
				$status_sorting = isset( $_GET['pro_status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_status_sorting'] ) ) : '';
				if ( ! empty( $status_sorting ) ) {

					$meta_query[] = array(
						'key'     => '_stock_status',
						'value'   => $status_sorting,
						'compare' => '=',
					);

				}
			}

			// if(! empty( $_REQUEST['orderby'])) {
			// $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : '';
			// $order = !empty($_GET["order"]) ? $_GET["order"] : 'ASC';

			// if('price'==$orderby) {
			// $key='_price';
			// } else {
			// $key='_stock_status';
			// }

			// $meta_query[] = array(
			// 'key'     => $key,
			// 'orderby' => $orderby,
			// 'order'    =>$order
			// );

			// }

			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			if ( ! empty( $_REQUEST['s'] ) ) {
				$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
				if ( ! empty( $s ) ) {
					$args['s'] = $s;
				}
			}

			$args['post_type']      = 'product';
			$args['posts_per_page'] = $per_page;
			$args['paged']          = $page_number;
			$args['post_status']    = 'publish';
			return $args;
		}
	}

	/**
	 * Render bulk actions
	 *
	 * @since 1.0.0
	 * @param      string $which    Where the apply button is placed.
	 */
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
			echo '<option value="-1">' . esc_html( __( 'Bulk Operations' ) ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";
			echo "<input type='button' class='button' value='Apply' id='ced_walmart_bulk_operation'>";
			echo "\n";
		endif;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since 1.0.0
	 */
	public function get_bulk_actions() {
		$actions = array(
			'upload'                   => __( 'Upload / Update', 'walmart-woocommerce-integration' ),
			'upload_wfs_item'          => __( 'Upload As WFS', 'walmart-woocommerce-integration' ),
			'update_price'             => __( 'Update Price', 'walmart-woocommerce-integration' ),
			'update_stock'             => __( 'Update Stock', 'walmart-woocommerce-integration' ),
			'convert_to_wfs'           => __( 'Convert To WFS', 'walmart-woocommerce-integration' ),
			'update_shipping_template' => __( 'Update Shipping Template', 'walmart-woocommerce-integration' ),
			'remove_shipping_template' => __( 'Remove Shipping Template', 'walmart-woocommerce-integration' ),
			'retire_bulk_item'         => __( 'Retire Items', 'walmart-woocommerce-integration' ),
		);
		return $actions;
	}

	/**
	 * Function for rendering html
	 *
	 * @since 1.0.0
	 */
	public function render_html() {
		?>
		<div class="ced_walmart_wrap ced_walmart_wrap_extn ">
			<div class="ced_walmart_heading">
				<?php echo esc_html_e( get_instuctions_html() ); ?>
				<div class="ced_walmart_child_element default_modal">
					<ul type="disc" style="margin: unset;
					font-size: 14px;
					margin: 5px auto;
					padding:5px 20px;">
					<li><?php echo esc_html_e( 'In this section you can upload or update WooCommerce products to Walmart.' ); ?></li>
					<li><?php echo esc_html_e( 'You can also filter out the product on the basis of category , type, stock, and walmart status.' ); ?></li>
					<li><?php echo esc_html_e( 'The Search Product option lets you find product using product name/keywords..' ); ?></li>
				</ul>
			</div>
		</div>
		<div id="post-body" class="metabox-holder columns-2 ced-walmart-product-list-wrapper">

			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<?php
					$status_actions = array(
						'Uploaded'    => __( 'On Walmart', 'walmart-woocommerce-integration' ),
						'NotUploaded' => __( 'Not on Walmart', 'walmart-woocommerce-integration' ),
					);
					$list_options   = array(
						'10'  => __( '10 Products per page', 'walmart-woocommerce-integration' ),
						'20'  => __( '20 Products per page', 'walmart-woocommerce-integration' ),
						'50'  => __( '50 Products per page', 'walmart-woocommerce-integration' ),
						'100' => __( '100 Products per page', 'walmart-woocommerce-integration' ),
					);

					$stock_status_filter = array(
						'instock'    => __( 'In Stock', 'walmart-woocommerce-integration' ),
						'outofstock' => __( 'Out of Stock', 'walmart-woocommerce-integration' ),
					);

					$product_types = get_terms( 'product_type', array( 'hide_empty' => false ) );
					$temp_array    = array();
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

					$previous_selected_status      = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
					$previous_selected_cat         = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';
					$previous_selected_type        = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_sorting'] ) ) : '';
					$previous_selected_sort_status = isset( $_GET['pro_status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_status_sorting'] ) ) : '';
					echo '<div class="ced_walmart_wrap">';
					echo '<form method="post" action="">';
					wp_nonce_field( 'manage_products', 'manage_product_filters' );
					echo '<div class="ced_walmart_top_wrapper">';
					echo '<select name="status_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Filter By Walmart Status', 'walmart-woocommerce-integration' ) ) . '</option>';
					foreach ( $status_actions as $name => $title ) {
						$selected_status = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
						$class           = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_status ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';
					echo '<select name="pro_cat_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Filter By Category', 'walmart-woocommerce-integration' ) ) . '</option>';
					foreach ( $product_categories as $name => $title ) {
						$selected_cat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';
						$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_cat ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					echo '<select name="pro_status_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Filter By Stock Status', 'walmart-woocommerce-integration' ) ) . '</option>';
					foreach ( $stock_status_filter as $index => $value ) {
						$selected_status = ( $previous_selected_sort_status == $index ) ? 'selected="selected"' : '';
						echo '<option value="' . esc_attr( $index ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $value ) . '</option>';
					}
					echo '</select>';

					echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Filter By Product Type', 'walmart-woocommerce-integration' ) ) . '</option>';
					foreach ( $product_types as $name => $title ) {
						$selected_type = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
						$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';
					$this->search_box( 'Search Products', 'search_id', 'search_product' );
					submit_button( __( 'Filter', 'walmart-woocommerce-integration' ), 'action', 'filter_button', false, array() );
					echo '</div>';
					echo '</form>';
					echo '<div id="ced_walmart_per_page">';
					$_per_page = get_option( 'ced_walmart_list_per_page', '' );
					echo '<select id="ced_walmart_list_per_page">';
					foreach ( $list_options as $index => $list_per_page ) {
						$selected_status = ( $_per_page == $index ) ? 'selected="selected"' : '';
						echo '<option value="' . esc_attr( $index ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $list_per_page ) . '</option>';
					}
					echo '</select>';
					echo '</div>';
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
	</div>
	<div class="ced_walmart_preview_product_popup_main_wrapper"></div>

		<?php

		require_once CED_WALMART_DIRPATH . 'admin/partials/ced-walmart-modal-product-insight.php';
	}
}

$ced_walmart_products_obj = new Ced_Walmart_Products_List();
$ced_walmart_products_obj->prepare_items();
