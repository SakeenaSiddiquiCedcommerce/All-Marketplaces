<?php
/**
 * Product listing in manage products
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
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_Good Market_Products_List
 *
 * @since 1.0.0
 */
class Ced_Good_Market_Products_List extends WP_List_Table {


	/**
	 * Ced_Good Market_Products_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'good_market-woocommerce-integration' ),
				'plural'   => __( 'Products', 'good_market-woocommerce-integration' ),
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

		$per_page  = apply_filters( 'ced_good_market_products_per_page', 20 );
		$_per_page = get_option( 'ced_good_market_list_per_page', '' );
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
		$this->items = self::ced_good_market_get_product_details( $per_page, $current_page, $post_type );

		$count = self::get_count( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::ced_good_market_get_product_details( $per_page, $current_page, $post_type );
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
	public function ced_good_market_get_product_details( $per_page = '', $page_number = '', $post_type = '' ) {
		$filter_file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/partials/class-ced-good_market-products-filter.php';
		if ( file_exists( $filter_file ) ) {
			include_once $filter_file;
		}

		$instance_of_filter_class = new Ced_Good_Market_Products_Filter();

		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';

		$args = $this->ced_good_market_get_filtered_data( $per_page, $page_number );
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
		$args['tax_query'][] = array(
			'taxonomy' => 'product_type',
			'field'    => 'name',
			'terms'    => array( 'simple', 'variable' ),
		);
		$loop                = new WP_Query( $args );

		$product_data             = $loop->posts;
		$woo_categories           = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products             = array();
		$get_woocommerce_currency = get_woocommerce_currency_symbol();
		foreach ( $product_data as $key => $value ) {
			$get_product_data_                    = wc_get_product( $value->ID );
			$get_product_data                     = $get_product_data_->get_data();
			$woo_products[ $key ]['category_ids'] = isset( $get_product_data['category_ids'] ) ? $get_product_data['category_ids'] : array();
			$woo_products[ $key ]['id']           = $value->ID;
			$woo_products[ $key ]['name']         = isset( $get_product_data['name'] ) ? $get_product_data['name'] : '';
			$woo_products[ $key ]['stock_status'] = ! empty( $get_product_data['stock_status'] ) ? $get_product_data['stock_status'] : '';
			$woo_products[ $key ]['manage_stock'] = ! empty( $get_product_data['manage_stock'] ) ? $get_product_data['manage_stock'] : '';
			$woo_products[ $key ]['sku']          = ! empty( $get_product_data['sku'] ) ? $get_product_data['sku'] : '';
			if ( ! $get_product_data_->is_type( 'variable' ) ) {
				$woo_products[ $key ]['stock']         = ! empty( $get_product_data['stock_quantity'] ) ? $get_product_data['stock_quantity'] : 0;
				$woo_products[ $key ]['price']         = $get_woocommerce_currency . ' ' . $get_product_data['price'];
				$woo_products[ $key ]['regular_price'] = $get_woocommerce_currency . ' ' . $get_product_data['regular_price'];

			} else {
				$min_price                     = $get_product_data_->get_variation_price( 'min' );
				$max_price                     = $get_product_data_->get_variation_price( 'max' );
				$woo_products[ $key ]['price'] = $get_woocommerce_currency . ' ' . $min_price . ' - ' . $get_woocommerce_currency . ' ' . $max_price;
				$variations                    = $get_product_data_->get_children();
				$vari_all_stock                = ! empty( $get_product_data['stock_quantity'] ) ? (int) $get_product_data['stock_quantity'] : 0;
				foreach ( $variations as $index => $variation_id ) {
					$stock          = (int) get_post_meta( $variation_id, '_stock', true );
					$vari_all_stock = $vari_all_stock + $stock;
				}
				$woo_products[ $key ]['stock']         = ! empty( $vari_all_stock ) ? $vari_all_stock : 0;
				$min_regular_price                     = $get_product_data_->get_variation_regular_price( 'min' );
				$max_regular_price                     = $get_product_data_->get_variation_regular_price( 'max' );
				$woo_products[ $key ]['regular_price'] = $get_woocommerce_currency . ' ' . $min_regular_price . ' - ' . $get_woocommerce_currency . ' ' . $max_regular_price;

			}
			// $woo_products[ $key ]['regular_price']= $get_woocommerce_currency. ' '.$get_product_data['regular_price'];
			$image_url_id                  = $get_product_data['image_id'];
			$woo_products[ $key ]['image'] = wp_get_attachment_url( $image_url_id );

		}

		if ( isset( $_POST['filter_button'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->ced_good_market_filters_on_products();
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
		esc_html_e( 'No Products To Show.', 'good_market-woocommerce-integration' );
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
			'<input type="checkbox" name="good_market_product_ids[]" class="product-id good_market_products_id" value="%s" />',
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
		$product         = wc_get_product( $item['id'] );
		$product_type    = $product->get_type();
		$url             = get_edit_post_link( $item['id'], '' );
		$actions['id']   = '<b>ID : ' . $item['id'] . '</b>';
		$actions['edit'] = '<a href="' . esc_url( $url ) . '" target="_blank">Edit</a>';
		$actions['type'] = '<strong>' . ucwords( $product_type ) . '</strong>';

		echo '<b class="product-title good_market-cool">' . esc_attr( $item['name'] ) . '</b>';
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
		$mapped_cat          = get_option( 'good_market_mapped_cat' );
		$mapped_cat          = json_decode( $mapped_cat, 1 );
		$category            = '';
		if ( ! empty( $category_ids ) && ! empty( $mapped_cat ) ) {

			foreach ( $category_ids as $index => $term_id ) {
				foreach ( $mapped_cat['profile'] as $key => $value ) {
					if ( in_array( $term_id, $value['woo_cat'] ) ) {
						$category       = $key;
						$profile_name[] = $value['profile_name'];
						// print_r($value['profile_name']);
						// break;
					}
				}
			}
		}
		if ( $category ) {
			echo '<span class="profile-name good_market-success">' . esc_attr( $profile_name[0] ) . '</span>';
			$format_cat_for_url = str_replace( ' & ', ' and ', $category );
			$edit_profile_url   = admin_url( 'admin.php?page=ced_good_market&section=profile-list&profile_id=' . ( $format_cat_for_url ) . '&panel=edit' );
			// $actions['edit']    = '<a href="' . esc_url( $edit_profile_url ) . '">' . __( 'Edit', 'good_market-woocommerce-integration' ) . '</a>';

		} else {
			$cat_mapping_section = admin_url( 'admin.php?page=ced_good_market&section=mapping' );
			echo '<b class="good_market-error">Category not mapped</b><p>Please map category <a href="' . esc_url( $cat_mapping_section ) . '" target="_blank"><i>here</i></a></p>';
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
			$stock_html = '<b class="good_market-success">' . __( 'In stock', 'woocommerce' ) . '</b>';
		} elseif ( 'outofstock' == $item['stock_status'] ) {
			$stock_html = '<b class="good_market-error">' . __( 'Out of stock', 'woocommerce' ) . '</b>';
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
	 * Function for status column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_status( $item ) {

		$saved_good_market_product_id = get_post_meta( $item['id'], 'saved_good_market_product_id', 1 );
		if ( ! empty( $saved_good_market_product_id ) && is_numeric( $saved_good_market_product_id ) ) {
			echo 'On Good Market';
		} else {
			echo 'Not on Good Market';
		}
	}


	public function column_details( $item ) {
		$price      = isset( $item['price'] ) ? $item['price'] : '';
		$product_id = isset( $item['id'] ) ? $item['id'] : '';
		echo '<p>';
		echo '<strong>Regular price: </strong>' . esc_attr( $item['regular_price'] ) . '</br>';
		echo '<strong>Selling price: </strong>' . esc_attr( $price ) . '</br>';
		echo '<strong>SKU : </strong>' . esc_attr( $item['sku'] ) . '</br>';
		$manage_stock = get_post_meta( $product_id, '_manage_stock', true );
		$stock        = 0;
		if ( 'yes' == $manage_stock ) {
			$stock = get_post_meta( $product_id, '_stock', true );
		} else {
			$stock = 1;
		}
		if ( $stock < 1 ) {
			$item['stock_status'] = 'Outofstock';
			echo "<strong>Stock status: </strong><span class='outofstock'>" . esc_attr( ucwords( $item['stock_status'] ) ) . '</span></br>';
		} else {

			echo "<strong>Stock status: </strong><span class= '" . esc_attr( $item['stock_status'] ) . "'>" . esc_attr( ucwords( $item['stock_status'] ) ) . '</span></br>';
		}
		echo '<strong>Stock qty: </strong>' . esc_attr( $item['stock'] ) . '</br>';
		echo '</p>';
	}




	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {

		$ced_good_market_configuration_details = get_option( 'ced_good_market_configuration_details', array() );

		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'image'             => __( 'Image', 'good_market-woocommerce-integration' ),
			'name'              => __( 'Title', 'good_market-woocommerce-integration' ),
			'details'           => __( 'Details', 'good_market-woocommerce-integration' ),
			'profile'           => __( 'Good Market Category', 'good_market-woocommerce-integration' ),
			'woo_category_name' => __( 'Woo Category', 'good_market-woocommerce-integration' ),
			'status'            => __( 'Good Market Status', 'good_market-woocommerce-integration' ),

		);

		if ( isset( $wfs_coloumn ) ) {
			$columns = array_merge( $columns, $wfs_coloumn );
		}
		$columns = apply_filters( 'ced_good_market_alter_product_table_columns', $columns );
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
		$args = $this->ced_good_market_get_filtered_data( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) ) {
			$args = $args;
		} else {
			$args = array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			);

		}
		$args['tax_query'][] = array(
			'taxonomy' => 'product_type',
			'field'    => 'name',
			'terms'    => array( 'simple', 'variable' ),
		);
		$loop                = new WP_Query( $args );
		$product_data        = $loop->posts;
		$product_data        = $loop->found_posts;

		return $product_data;
	}

	/**
	 * Function to get the filtered data
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function ced_good_market_get_filtered_data( $per_page, $page_number ) {

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
							'key'     => 'saved_good_market_product_id',
							'compare' => 'EXISTS',
						);
					} elseif ( 'NotUploaded' == $status_sorting ) {
						$meta_query[] = array(
							'key'     => 'saved_good_market_product_id',
							'compare' => 'NOT EXISTS',
						);
					}
				}
			}

			if ( ! empty( $_REQUEST['pro_status_sorting'] ) ) {
				$status_sorting = isset( $_GET['pro_status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_status_sorting'] ) ) : '';
				if ( ! empty( $status_sorting ) && 'outofstock' == $status_sorting ) {

					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => '_stock_status',
							'value'   => $status_sorting,
							'compare' => '=',
						),
						array(
							'key'     => '_stock',
							'type'    => 'numeric',
							'value'   => 1,
							'compare' => '<',
						),
					);

				} else {
					$meta_query[] = array(
						'relation' => 'OR',
						/*
						array(
							'key'     => '_stock_status',
							'value'   => $status_sorting,
							'compare' => '=',
						),*/
						 array(
							 'key'     => '_stock',
							 'type'    => 'numeric',
							 'value'   => 0,
							 'compare' => '>',
						 ),
						array(
							'key'     => '_stock_status',
							'value'   => $status_sorting,
							'compare' => 'NOTEXISTS',
						),
					);
				}
			}
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
			echo "<input type='button' class='button' value='Apply' id='ced_good_market_bulk_operation'>";
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
			// 'upload'                   => __( 'Upload', 'good_market-woocommerce-integration' ),
			'save_Bulk_Product' => __( 'Add to Good Market', 'good_market-woocommerce-integration' ),
			'delete'            => __( 'Remove from Good Market', 'good_market-woocommerce-integration' ),

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
		<div class="ced_good_market_wrap ced_good_market_wrap_extn ">
			<div class="ced_good_market_heading">
				<?php echo esc_html( get_instuction_html() ); ?>
				<div class="ced_good_market_child_element default_modal">
					<ul type="disc" style="margin: unset;
	font-size: 14px;
	margin: 5px auto;
	padding:5px 20px;">
						<li><?php echo esc_html_e( 'In this section, you can sync WooCommerce products to Good Market.' ); ?></li>
						<li><?php echo esc_html_e( 'You can filter products by Good Market Status, Category, Stock, and Product Type.' ); ?></li>
						<li><?php echo esc_html_e( 'Use Search Products to find products by product name or keyword.' ); ?></li>
					</ul>
				</div>
			</div>
			<div id="post-body" class="metabox-holder columns-2 ced-good_market-product-list-wrapper">

				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<?php
						$status_actions = array(
							'Uploaded'    => __( 'On Good Market', 'good_market-woocommerce-integration' ),
							'NotUploaded' => __( 'Not on Good Market', 'good_market-woocommerce-integration' ),
						);
						$list_options   = array(
							'10'  => __( '10 Products per page', 'good_market-woocommerce-integration' ),
							'20'  => __( '20 Products per page', 'good_market-woocommerce-integration' ),
							'50'  => __( '50 Products per page', 'good_market-woocommerce-integration' ),
							'100' => __( '100 Products per page', 'good_market-woocommerce-integration' ),
						);

						$stock_status_filter = array(
							'instock'    => __( 'In Stock', 'good_market-woocommerce-integration' ),
							'outofstock' => __( 'Out of Stock', 'good_market-woocommerce-integration' ),
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
						echo '<div class="ced_good_market_wrap">';
						echo '<form method="post" action="">';
						wp_nonce_field( 'manage_products', 'manage_product_filters' );
						echo '<div class="ced_good_market_top_wrapper">';
						echo '<select name="status_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Good Market Status', 'good_market-woocommerce-integration' ) ) . '</option>';
						foreach ( $status_actions as $name => $title ) {
							$selected_status = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
							$class           = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_status ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						echo '<select id="pro_cat_filter_sorting" name="pro_cat_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Category', 'good_market-woocommerce-integration' ) ) . '</option>';
						foreach ( $product_categories as $name => $title ) {
							$selected_cat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';
							$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_cat ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';

						echo '<select name="pro_status_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Stock Status', 'good_market-woocommerce-integration' ) ) . '</option>';
						foreach ( $stock_status_filter as $index => $value ) {
							$selected_status = ( $previous_selected_sort_status == $index ) ? 'selected="selected"' : '';
							echo '<option value="' . esc_attr( $index ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $value ) . '</option>';
						}
						echo '</select>';

						echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Product Type', 'good_market-woocommerce-integration' ) ) . '</option>';
						foreach ( $product_types as $name => $title ) {
							$selected_type = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
							$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						$this->search_box( 'Search Products', 'search_id', 'search_product' );
						submit_button( __( 'Filter', 'good_market-woocommerce-integration' ), 'action', 'filter_button', false, array() );
						echo '<a class="ced_good_remove_filter_url" href="'.admin_url().'admin.php?page=ced_good_market&section=products"><span title="Clear Filters" class="ced_good_market_remove_filter_icon"><img src="'. esc_url( GOOD_MARKET_INTEGRATION_URL . 'admin/images/filter.png' ).'" height="30" width="30"></span></a>';
                        echo '</div>';
						echo '</form>';
						echo '<div id="ced_good_market_per_page">';
						$_per_page = get_option( 'ced_good_market_list_per_page', '' );
						echo '<select id="ced_good_market_list_per_page">';
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
		<div class="ced_good_market_preview_product_popup_main_wrapper"></div>

		<?php

	}
}

$ced_good_market_products_obj = new Ced_Good_Market_Products_List();
$ced_good_market_products_obj->prepare_items();