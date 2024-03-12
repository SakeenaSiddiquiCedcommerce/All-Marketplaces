<?php
/**
 * Product listing in manage products
 *
 * @package  facebook_shopping_woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_Facebook Shopping_Products_List
 *
 * @since 1.0.0
 */
class Ced_Facebook_Shopping_Products_List extends WP_List_Table {


	/**
	 * Ced_Facebook Shopping_Products_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'facebook-marketplace-connector-for-woocommerce' ),
				'plural'   => __( 'Products', 'facebook-marketplace-connector-for-woocommerce' ),
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
		/**
		 * A filter used for create product per page ced_facebook_shopping_products_per_page.
		 *
		 * A filter used for create product per page.
		 *
		 * @since 1.0.0
		 * @filter ced_facebook_shopping_products_per_page
		 */
		$per_page  = apply_filters( 'ced_facebook_shopping_products_per_page', 20 );
		$_per_page = get_option( 'ced_facebook_shopping_list_per_page', '' );
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
		$this->items = self::ced_facebook_shopping_get_product_details( $per_page, $current_page, $post_type );

		$count = self::get_count( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::ced_facebook_shopping_get_product_details( $per_page, $current_page, $post_type );
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
	public function ced_facebook_shopping_get_product_details( $per_page = '', $page_number = '', $post_type = '' ) {
		$filter_file = CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-product_filter.php';
		if ( file_exists( $filter_file ) ) {
			include_once $filter_file;
		}

		$instance_of_filter_class = new Ced_Facebook_Shopping_Products_Filter();

		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';

		$args = $this->ced_facebook_shopping_get_filtered_data( $per_page, $page_number );
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

		$product_data   = $loop->posts;
		$woo_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products   = array();
		foreach ( $product_data as $key => $value ) {
			$get_product_data                      = wc_get_product( $value->ID );
			$get_product_data                      = $get_product_data->get_data();
			$woo_products[ $key ]['category_ids']  = isset( $get_product_data['category_ids'] ) ? $get_product_data['category_ids'] : array();
			$woo_products[ $key ]['id']            = $value->ID;
			$woo_products[ $key ]['name']          = isset( $get_product_data['name'] ) ? $get_product_data['name'] : '';
			$woo_products[ $key ]['stock']         = ! empty( $get_product_data['stock_quantity'] ) ? $get_product_data['stock_quantity'] : 0;
			$woo_products[ $key ]['stock_status']  = ! empty( $get_product_data['stock_status'] ) ? $get_product_data['stock_status'] : '';
			$woo_products[ $key ]['manage_stock']  = ! empty( $get_product_data['manage_stock'] ) ? $get_product_data['manage_stock'] : '';
			$woo_products[ $key ]['sku']           = ! empty( $get_product_data['sku'] ) ? $get_product_data['sku'] : '';
			$woo_products[ $key ]['price']         = $get_product_data['price'];
			$woo_products[ $key ]['regular_price'] = $get_product_data['regular_price'];
			$image_url_id                          = $get_product_data['image_id'];
			$woo_products[ $key ]['image']         = wp_get_attachment_url( $image_url_id );

		}

		if ( isset( $_POST['filter_button'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->ced_facebook_shopping_filters_on_products();
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
		esc_html_e( 'No Products To Show.', 'facebook-marketplace-connector-for-woocommerce' );
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
			'<input type="checkbox" name="facebook_shopping_product_ids[]" class="product-id facebook_shopping_products_id" value="%s" />',
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

		echo '<b class="product-title facebook_shopping-cool">' . esc_attr( $item['name'] ) . '</b>';
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
	 * Function for stock column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_stock( $item ) {
		if ( 'instock' == $item['stock_status'] ) {
			$stock_html = '<b class="facebook_shopping-success">' . __( 'In stock', 'woocommerce' ) . '</b>';
		} elseif ( 'outofstock' == $item['stock_status'] ) {
			$stock_html = '<b class="facebook_shopping-error">' . __( 'Out of stock', 'woocommerce' ) . '</b>';
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
		return '<img height="80" width="80" src="' . esc_url( $item['image'] ) . '">';
	}


	/**
	 * Function for status column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_status( $item ) {
		$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', true );
		$bussiness_id         = array_keys( $catalog_and_page_id )[0];
		$connected_catalog_id = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
		$post_id              = isset( $item['id'] ) ? $item['id'] : '';
		$uploaded             = get_post_meta( $post_id, 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id, true );
		$errors               = get_post_meta( $post_id, 'ced_fmcw_product_with_errors_' . $connected_catalog_id, true );
		$submitted            = get_post_meta( $post_id, 'ced_fmcw_product_upload_submitted_' . $connected_catalog_id, true );
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
			$error_arr = get_post_meta( $post_id, 'ced_fmcw_product_errors_warning_' . $connected_catalog_id, true );
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


	public function column_details( $item ) {
		$selling_price = get_post_meta( $item['id'], '_sale_price', true );
		echo '<p>';
		echo '<strong>Regular price: </strong>' . esc_attr( $item['regular_price'] ) . '</br>';
		echo '<strong>Selling price: </strong>' . esc_attr( $selling_price ) . '</br>';
		echo '<strong>SKU : </strong>' . esc_attr( $item['sku'] ) . '</br>';
		echo "<strong>Stock status: </strong><span class='" . esc_attr( $item['stock_status'] ) . "'>" . esc_attr( ucwords( $item['stock_status'] ) ) . '</span></br>';
		echo '<strong>Stock qty: </strong>' . esc_attr( $item['stock'] ) . '</br>';
		echo '</p>';
	}




	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {

		$ced_facebook_shopping_configuration_details = get_option( 'ced_facebook_shopping_configuration_details', array() );

		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'image'             => __( 'Image', 'facebook-marketplace-connector-for-woocommerce' ),
			'name'              => __( 'Title', 'facebook-marketplace-connector-for-woocommerce' ),
			'Stock'             => __( 'Stock', 'facebook-marketplace-connector-for-woocommerce' ),
			'details'           => __( 'Details', 'woocommerce-etsy-integration' ),
			'woo_category_name' => __( 'Woo Category', 'facebook-marketplace-connector-for-woocommerce' ),
			'status'            => __( 'Meta Status', 'facebook-marketplace-connector-for-woocommerce' ),

		);

		if ( isset( $wfs_coloumn ) ) {
			$columns = array_merge( $columns, $wfs_coloumn );
		}
		/**
		 * A filter used to alter product table column ced_facebook_shopping_alter_product_table_columns.
		 *
		 * A filter used to alter product table column.
		 *
		 * @since 1.0.0
		 * @filter ced_facebook_shopping_alter_product_table_columns
		 */
		$columns = apply_filters( 'ced_facebook_shopping_alter_product_table_columns', $columns );
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
		$args = $this->ced_facebook_shopping_get_filtered_data( $per_page, $page_number );
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
	public function ced_facebook_shopping_get_filtered_data( $per_page, $page_number ) {
		$merchant_details      = get_option( 'ced_save_merchant_details', true );
		$connected_merchant_id = isset( $merchant_details['merchant_id'] ) ? $merchant_details['merchant_id'] : '';
		if ( isset( $_GET['status_sorting'] ) || isset( $_GET['pro_cat_sorting'] ) || isset( $_GET['pro_type_sorting'] ) || isset( $_GET['s'] ) ) {
			if ( ! empty( $_REQUEST['pro_cat_sorting'] ) ) {
				$pro_cat_sorting = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';
				if ( ! empty( $pro_cat_sorting ) ) {
					$selected_cat                  = array( $pro_cat_sorting );
					$tax_query                     = array();
					$tax_queries                   = array();
					$tax_query['taxonomy']         = 'product_cat';
					$tax_query['field']            = 'term_id';
					$tax_query['terms']            = $selected_cat;
					$tax_query['operator']         = 'IN';
					$tax_query['include_children'] = false;
					$args['tax_query'][]           = $tax_query;
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
					$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', true );
					$bussiness_id         = array_keys( $catalog_and_page_id )[0];
					$connected_catalog_id = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';
					if ( 'Uploaded' == $status_sorting ) {
						$args['orderby'] = 'meta_value_num';
						$args['order']   = 'ASC';

						$meta_query[] = array(
							'key'     => 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id,
							'compare' => 'EXISTS',
						);
					} elseif ( 'NotUploaded' == $status_sorting ) {
						$meta_query[] = array(
							'key'     => 'ced_fmcw_uploaded_on_facebook_' . $connected_catalog_id,
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
			echo "<input type='button' class='button' value='Apply' id='ced_facebook_shopping_bulk_operation'>";
			?>
			<?php
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
			'ced_fmcw_upload_to_facebook'   => __( 'Upload on Meta', 'facebook-marketplace-connector-for-woocommerce' ),
			'ced_fmcw_update_on_facebook'   => __( 'Update on Meta', 'facebook-marketplace-connector-for-woocommerce' ),
			'ced_fmcw_remove_from_facebook' => __( 'Remove from Meta', 'facebook-marketplace-connector-for-woocommerce' ),
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
		<div class="ced_facebook_shopping_wrap ced_facebook_shopping_wrap_extn ">
			
			<div class="ced_facebook_shopping_notice_era"></div>
			<div id="post-body" class="metabox-holder columns-2 ced-facebook_shopping-product-list-wrapper">

				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<?php
						$status_actions = array(
							'Uploaded'    => __( 'On Meta', 'facebook-marketplace-connector-for-woocommerce' ),
							'NotUploaded' => __( 'Not on Meta', 'facebook-marketplace-connector-for-woocommerce' ),
						);
						$list_options   = array(
							'10' => __( '10  Per Page', 'facebook-marketplace-connector-for-woocommerce' ),
							'25' => __( '25  Per Page', 'facebook-marketplace-connector-for-woocommerce' ),
							'50' => __( '50  Per Page', 'facebook-marketplace-connector-for-woocommerce' ),

						);

						$stock_status_filter = array(
							'instock'    => __( 'In Stock', 'facebook-marketplace-connector-for-woocommerce' ),
							'outofstock' => __( 'Out of Stock', 'facebook-marketplace-connector-for-woocommerce' ),
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
						echo '<div class="ced_facebook_shopping_wrap">';
						echo '<form method="post" action="">';
						wp_nonce_field( 'manage_products', 'manage_product_filters' );
						echo '<div class="ced_facebook_shopping_top_wrapper">';
						echo '<select name="status_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Meta Status', 'facebook-marketplace-connector-for-woocommerce' ) ) . '</option>';
						foreach ( $status_actions as $name => $title ) {
							$selected_status = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
							$class           = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_status ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						echo '<select name="pro_cat_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Category', 'facebook-marketplace-connector-for-woocommerce' ) ) . '</option>';
						foreach ( $product_categories as $name => $title ) {
							$selected_cat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';
							$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_cat ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';

						echo '<select name="pro_status_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Stock Status', 'facebook-marketplace-connector-for-woocommerce' ) ) . '</option>';
						foreach ( $stock_status_filter as $index => $value ) {
							$selected_status = ( $previous_selected_sort_status == $index ) ? 'selected="selected"' : '';
							echo '<option value="' . esc_attr( $index ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $value ) . '</option>';
						}
						echo '</select>';

						echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Filter By Product Type', 'facebook-marketplace-connector-for-woocommerce' ) ) . '</option>';
						foreach ( $product_types as $name => $title ) {
							$selected_type = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
							$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						$this->search_box( 'Search Products', 'search_id', 'search_product' );
						submit_button( __( 'Filter', 'facebook-marketplace-connector-for-woocommerce' ), 'action', 'filter_button', false, array() );
						echo '</div>';
						echo '</form>';
						echo '<div id="ced_facebook_shopping_per_page">';
						$_per_page = get_option( 'ced_facebook_shopping_list_per_page' );
						echo '<select id="ced_facebook_shopping_list_per_page">';
						foreach ( $list_options as $index => $list_per_page ) {
							$selected_status = ( $_per_page == $index ) ? 'selected="selected"' : '';
							echo '<option value="' . esc_attr( $index ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $list_per_page ) . '</option>';
						}
						echo '</select>';
						echo '</div>';
						echo '</div>';
						?>
						<div class="ced_fmcw_product_status_popup_main_wrapper">
							<div class="ced_fmcw_product_status_popup_content">
								<div class="ced_fmcw_product_status_popup_header">
									<h5><?php esc_html_e( 'Product Feed Status', 'facebook-marketplace-connector-for-woocommerce' ); ?></h5>
									<span class="ced_fmcw_product_status_popup_close"><i class="fas fa-times-circle" aria-hidden="true"></i>
									</span>
								</div>
								<div class="ced_fmcw_product_status_popup_body">
									<h6 class="ced_fmcw_product_title_heading">Product 1</h6>
									<label class="ced_fmcw_error_heading">Product Errors</label>
									<ul class="ced_fmcw_errors">
										<li>
											No errors to Show
										</li>
									</ul>
									<label class="ced_fmcw_warning_heading">Product Warnings</label>
									<ul class="ced_fmcw_warnings">
										<li>
											No Warnings to Show
										</li>
									</ul>
								</div>
							</div>
						</div>
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
		<div class="ced_facebook_shopping_preview_product_popup_main_wrapper"></div>

		<?php

	}
}

$ced_facebook_shopping_products_obj = new Ced_Facebook_Shopping_Products_List();
$ced_facebook_shopping_products_obj->prepare_items();