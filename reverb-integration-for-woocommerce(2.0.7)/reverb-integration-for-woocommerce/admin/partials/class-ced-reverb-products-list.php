<?php
/**
 * Product listing in manage products
 *
 * @package  Woocommerce_reverb_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_reverb_header();
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_reverb_Products_List
 *
 * @since 1.0.0
 */
class Ced_Reverb_Products_List extends WP_List_Table {


	/**
	 * Ced_reverb_Products_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'reverb-woocommerce-integration' ),
				'plural'   => __( 'Products', 'reverb-woocommerce-integration' ),
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
 		* Filter hook for filtering no. of product per page.
 		* @since 1.0.0
 		*/
		$per_page  = apply_filters( 'ced_reverb_products_per_page', 20 );
		$_per_page = get_option( 'ced_reverb_list_per_page', '' );
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
		$this->items = self::ced_reverb_get_product_details( $per_page, $current_page, $post_type );
		$count       = self::get_count( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::ced_reverb_get_product_details( $per_page, $current_page, $post_type );
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
	public function ced_reverb_get_product_details( $per_page = '', $page_number = '', $post_type = '' ) {
		$filter_file = CED_REVERB_DIRPATH . 'admin/partials/class-ced-reverb-products-filter.php';
		if ( file_exists( $filter_file ) ) {
			include_once $filter_file;
		}

		$instance_of_filter_class = new Ced_Reverb_Products_Filter();

		$args = $this->ced_reverb_get_filtered_data( $per_page, $page_number );
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

		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;

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
			foreach ( $woo_categories as $key1 => $value1 ) {
				if ( isset( $get_product_data['category_ids'][0] ) ) {
					if ( $value1->term_id == $get_product_data['category_ids'][0] ) {
						$woo_products[ $key ]['category'] = $value1->name;
					}
				}
			}
		}

		if ( isset( $_POST['filter_button'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->ced_reverb_filters_on_products();
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
		esc_html_e( 'No Products To Show.', 'reverb-woocommerce-integration' );
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
			'<input type="checkbox" name="reverb_product_ids[]" class="product-id reverb_products_id" value="%s" />',
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
		// $url             = get_edit_post_link( $item['id'], '' );
		// $actions['id']   = '<b>ID : ' . $item['id'] . '</b>';
		// $actions['edit'] = '<a href="' . esc_url( $url ) . '" target="_blank">Edit</a>';
		// echo '<b class="product-title reverb-cool">' . esc_attr( $item['name'] ) . '</b>';
		// return $this->row_actions( $actions, true );

		$product         = wc_get_product( $item['id'] );
		$product_type    = $product->get_type();
		$url             = get_edit_post_link( $item['id'], '' );
		$actions['id']   = '<b>ID : ' . $item['id'] . '</b>';
		$actions['edit'] = '<a href="' . esc_url( $url ) . '" target="_blank">Edit</a>';
		$actions['type'] = '<strong>' . ucwords( $product_type ) . '</strong>';

		echo '<b class="product-title reverb-cool">' . esc_attr( $item['name'] ) . '</b>';
		return $this->row_actions( $actions, true );
	}

	/**
	 * Function for profile column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_profile( $item ) {
		$is_profile_assigned      = false;
		$actions                  = array();
		$ced_reverb_category_name = '';
		$category_ids             = isset( $item['category_ids'] ) ? $item['category_ids'] : array();
		$reverb_profiles          = get_option( 'ced_reverb_profiles_list', true );
		$page                     = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		foreach ( $category_ids as $key => $value ) {
			$term_meta = get_term_meta( $value, 'ced_reverb_mapped_category', true );
			if ( is_array( $reverb_profiles ) && ! empty( $reverb_profiles ) ) {
				foreach ( $reverb_profiles as $key => $profile_data ) {
					if ( isset( $profile_data['reverb_cat_id'] ) && $term_meta == $profile_data['reverb_cat_id'] ) {
						$ced_reverb_category_name = $profile_data['reverb_cat_name'];
						 $actions                 = array(
							 'edit' => sprintf( '<a href="?page=%s&section=%s&profileID=%s&panel=edit">Edit</a>', esc_attr( $page ), 'profile_view', $key ),
						 );
					}
				}
			}
			if ( ! empty( $ced_reverb_category_name ) ) {
				echo '<b class="reverb-success">' . esc_attr( $ced_reverb_category_name ) . '</b>';
				return $this->row_actions( $actions, true );
				break;
			}
		}
		if ( empty( $term_meta ) ) {
			echo '<b class="reverb-error">Category not mapped</b>';
		}
	}

	/**
	 * Function for stock column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_stock( $item ) {
		if ( 'instock' == $item['stock_status'] ) {
			$stock_html = '<b class="reverb-success">' . __( 'In stock', 'woocommerce' ) . '</b>';
		} elseif ( 'outofstock' == $item['stock_status'] ) {
			$stock_html = '<b class="reverb-error">' . __( 'Out of stock', 'woocommerce' ) . '</b>';
		}
		if ( ! empty( $item['manage_stock'] ) ) {
			$stock_html .= ' (' . wc_stock_amount( $item['stock'] ) . ')';
		}

		echo wp_kses_post( $stock_html );
	}

	/**
	 * Function for stock column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_details( $item ) {
		$selling_price = get_post_meta( $item['id'], '_sale_price', true );
		echo '<p>';
		echo '<strong>Regular price: </strong>' . esc_attr( $item['price'] ) . '</br>';
		echo '<strong>Selling price: </strong>' . esc_attr( $selling_price ) . '</br>';
		echo '<strong>SKU : </strong>' . esc_attr( $item['sku'] ) . '</br>';
		echo "<strong>Stock status: </strong><span class='" . esc_attr( $item['stock_status'] ) . "'>" . esc_attr( ucwords( $item['stock_status'] ) ) . '</span></br>';
		echo '<strong>Stock qty: </strong>' . esc_attr( $item['stock'] ) . '</br>';

		echo '</p>';
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

		$config_details = get_option( 'ced_reverb_configuration_details', array() );
		$account_type   = '';
		
		$is_uploaded    = get_post_meta( $item['id'], 'ced_reverb_listing_id' . $account_type, true );
		if ( ! empty( $is_uploaded ) ) {
			$reverb_url   = get_post_meta( $item['id'], 'ced_reverb_listing_url' . $account_type, true );
			$reverb_stage = get_post_meta( $item['id'], 'ced_reverb_state' . $account_type, true );
			$reverb_icon  = CED_REVERB_URL . 'admin/images/reverb.png';
			//echo "<b class='reverb-success'>" . esc_attr( strtoupper( $reverb_stage ) ) . '</b>';

			if($reverb_stage == "Live"){

			?>
			<strong style="color: tomato;">[Live]</strong>
			<?php

			}elseif( isset($reverb_stage) && !empty($reverb_stage)){

				?>
			<strong style="color: black;"><?php echo '['.$reverb_stage.']';?></strong>
			<?php

			}
			return $this->row_actions( array( 'view' => '<a href="' . esc_url( $reverb_url ) . '" target="_blank"><img class="ced_reverb_status" src="' . esc_url( $reverb_icon ) . '" height="" width="50"></a>' ), true );
		} else {
			echo "<b class='reverb-error'>Not on Reverb</b>";
		}
	}

	public function column_get_prepared_data($item){

		

		?>
		<div class="ced_reverb_product_prepared_data_wrapper">
			<div class="ced_reverb_product_prepared_data ced_reverb_prep_data" pro-id = "<?php echo $item['id']; ?>"> Upload or Update	</div>
			<div class="ced_reverb_inventory_prepared_data ced_reverb_prep_data" pro-id = "<?php echo $item['id']; ?>"> Inventory Update	</div>
		</div>

		<!-- <button class="button-33" role="button" pro-id = "<?php echo $item['id']; ?>">At upload/update</button>

		<button class="button-34" role="button" pro-id = "<?php echo $item['id']; ?>">At update inventory</button> -->

		<?php
	}

	/**
	 *
	 * Function for category column
	 */
	public function column_category( $item ) {
		if ( isset( $item['category'] ) ) {
			return '<b>' . $item['category'] . '</b>';
		}
	}

	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'image'    => __( 'Image', 'reverb-woocommerce-integration' ),
			'name'     => __( 'Title', 'reverb-woocommerce-integration' ),
			'details'    => __( 'Details', 'reverb-woocommerce-integration' ),
			'profile'  => __( 'Profile', 'reverb-woocommerce-integration' ),
			//'price'    => __( 'Price', 'reverb-woocommerce-integration' ),
			//'sku'      => __( 'Sku', 'reverb-woocommerce-integration' ),
			'category' => __( 'Category', 'reverb-woocommerce-integration' ),
			//'stock'    => __( 'Stock', 'reverb-woocommerce-integration' ),
			'status'   => __( 'Reverb Status', 'reverb-woocommerce-integration' ),
			'get_prepared_data' => __('Get Prepared Data', 'reverb-woocommerce-integration'),
		);
		/**
 		* Filter hook for filtering columns on product page of plugin.
 		* @since 1.0.0
 		*/
		$columns = apply_filters( 'ced_reverb_alter_product_table_columns', $columns );
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
		$args = $this->ced_reverb_get_filtered_data( $per_page, $page_number );
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
	public function ced_reverb_get_filtered_data( $per_page, $page_number ) {
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
					$meta_query = array();
					if ( 'Uploaded' == $status_sorting ) {
						$args['orderby'] = 'meta_value_num';
						$args['order']   = 'ASC';

						$meta_query[] = array(
							'key'     => 'ced_reverb_listing_id',
							'compare' => 'EXISTS',
						);
					} elseif ( 'NotUploaded' == $status_sorting ) {
						$meta_query[] = array(
							'key'     => 'ced_reverb_listing_id',
							'compare' => 'NOT EXISTS',
						);
					}
					$args['meta_query'] = $meta_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_tag_sorting'] ) ) {
				$pro_tag_sorting = isset( $_GET['pro_tag_sorting'] ) ? sanitize_text_field( $_GET['pro_tag_sorting'] ) : '';
				if ( '' != $pro_tag_sorting ) {
					$selected_tag          = array( $pro_tag_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_tag';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_tag;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_stock_sorting'] ) ) {
				$pro_stock_sorting     = isset( $_GET['pro_stock_sorting'] ) ? sanitize_text_field( $_GET['pro_stock_sorting'] ) : '';
				$selected_stock_status = array( $pro_stock_sorting );
				$meta_query[]          = array(
					'key'   => '_stock_status',
					'value' => $selected_stock_status,
				);
				$args['meta_query']    = $meta_query;
			}

			if ( ! empty( $_REQUEST['pro_attribute_sorting'] ) ) {
				$pro_attribute_sorting = isset( $_GET['pro_attribute_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_attribute_sorting'] ) ) : '';
				$options               = array( 'hide_empty' => false );
				$terms                 = get_terms( $pro_attribute_sorting, $options );

				foreach ( $terms as $key => $value ) {
					foreach ( $value as $key => $term ) {
						if ( 'slug' == $key ) {
							$term_array[] = $term;
						}
					}
				}

				if ( ! empty( $pro_attribute_sorting ) ) {
					$selected_type         = array( $pro_attribute_sorting );
					$tax_query             = array();
					$tax_query['taxonomy'] = $pro_attribute_sorting;
					$tax_query['field']    = 'slug';
					$tax_query['terms']    = $term_array;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( ! empty( $_REQUEST['s'] ) ) {
				$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
				if ( ! empty( $s ) ) {
					$meta_query = array();

					$meta_query[] = array(
						'key'     => '_sku',
						'value'   => $s,
						'compare' => 'LIKE',
					);

					$args['meta_query'] = $meta_query;
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
			echo "<input type='button' class='button' value='Apply' id='ced_reverb_bulk_operation'>";
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
			'upload_product'   => __( 'Upload/Update Products', 'woocommerce-catch-integration' ),
			'update_inventory' => __( 'Update Inventory', 'woocommerce-catch-integration' ),
			'remove'           => __( 'Remove', 'woocommerce-catch-integration' ),
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
		<div class="ced_reverb_heading">
				<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
				<div class="ced_reverb_child_element">
					<ul type="disc">
						<li><?php echo esc_html_e( 'This section lets you perform multiple operation such as Upload/Update product from woocommerce to reverb.In order to perform any operation from the Bulk Actions dropdown you need to select the product using the checkbox on the left side in the product list column and hit Apply button.You will get the notification for each performed operation.' ); ?></li>
						<li><?php echo esc_html_e( 'You can also filter out the product on the basis of category , type, stock, tag and reverb status.' ); ?></li>
						<li>
						<?php
						echo esc_html_e(
							'The Search Product option lets you find product using product sku.
						'
						);
						?>
							</li>
						<li>
						<?php
						echo esc_html_e(
							'Once the product is successfuly uploaded on reverb you will have the product view link on the right [ The reverb Logo ].
						'
						);
						?>
							</li>
					</ul>
				</div>
			</div>
		<div class="ced_reverb_wrap ced_reverb_wrap_extn ">
			
			<div id="post-body" class="metabox-holder columns-2 ced-reverb-product-list-wrapper">

				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<?php
						$status_actions = array(
							'Uploaded'    => __( 'On Reverb', 'reverb-woocommerce-integration' ),
							'NotUploaded' => __( 'Not on Reverb', 'reverb-woocommerce-integration' ),
						);

						$stock_actions = array(
							'instock'    => __( 'instock', 'woocommerce-reverb-integration' ),
							'outofstock' => __( 'outofstock', 'woocommerce-reverb-integration' ),
						);

						$attribute_taxonomies = wc_get_attribute_taxonomies();
						$taxonomy_terms       = array();
						$temp_attribute_array = array();

						if ( $attribute_taxonomies ) :
							foreach ( $attribute_taxonomies as $tax ) :
								if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) :
									$taxonomy_terms[ $tax->attribute_name ] = wc_attribute_taxonomy_name( $tax->attribute_name );
							endif;
						endforeach;
						endif;

						foreach ( $taxonomy_terms as $key => $value ) {
							$temp_attribute_array[ $value ] = $key;
						}

						$product_tag = get_terms( 'product_tag', array( 'hide_empty' => false ) );

						$temp_array_tag = array();
						foreach ( $product_tag as $key => $value ) {
							$temp_array_tag[ $value->term_id ] = $value->name;
						}
						$product_tag = $temp_array_tag;

						$product_types = get_terms( 'product_type', array( 'hide_empty' => false ) );
						$temp_array    = array();
						foreach ( $product_types as $key => $value ) {
								$temp_array_type[ $value->term_id ] = ucfirst( $value->name );
						}
						$product_types = $temp_array_type;

						$product_categories = get_terms(
							'product_cat',
							array(
								'hide_empty' => false,
								'order'      => 'ASC',
								'orderby'    => 'title',
							)
						);

						$temp_array = array();
						foreach ( $product_categories as $key => $value ) {
							$temp_array[ $value->term_id ] = $value->name;
						}
						$product_categories = $temp_array;

						$previous_selected_status = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
						$previous_selected_cat    = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';
						$previous_selected_type   = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_sorting'] ) ) : '';
						$previous_selected_stock  = isset( $_GET['pro_stock_sorting'] ) ? sanitize_text_field( $_GET['pro_stock_sorting'] ) : '';
						$previous_selected_tag    = isset( $_GET['pro_tag_sorting'] ) ? sanitize_text_field( $_GET['pro_tag_sorting'] ) : '';
						$previous_selected_attr   = isset( $_GET['pro_attribute_sorting'] ) ? sanitize_text_field( $_GET['pro_attribute_sorting'] ) : '';

						echo '<div class="ced_reverb_wrap">';
						echo '<form method="post" action="">';
						wp_nonce_field( 'manage_products', 'manage_product_filters' );
						echo '<div class="ced_reverb_top_wrapper">';
						echo '<select name="status_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Reverb status', 'reverb-woocommerce-integration' ) ) . '</option>';
						foreach ( $status_actions as $name => $title ) {
							$selected_status = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
							$class           = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_status ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						echo '<select name="pro_cat_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Category', 'reverb-woocommerce-integration' ) ) . '</option>';

						foreach ( $product_categories as $name => $title ) {
							$selected_cat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';
							$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_cat ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_html( __( 'Product type', 'reverb-woocommerce-integration' ) ) . '</option>';
						foreach ( $product_types as $name => $title ) {
							$selected_type = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
							$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';

						echo '<select name="pro_stock_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_attr( __( 'Stock status', 'woocommerce-reverb-integration' ) ) . '</option>';
						foreach ( $stock_actions as $name => $title ) {
							$selected_status = ( $previous_selected_stock == $name ) ? 'selected="selected"' : '';
							$class           = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_status ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						echo '<select name="pro_tag_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_attr( __( 'Product tag', 'woocommerce-reverb-integration' ) ) . '</option>';
						foreach ( $product_tag as $name => $title ) {
							$selected_tag = ( $previous_selected_tag == $name ) ? 'selected="selected"' : '';
							$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_tag ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';
						echo '<select name="pro_attribute_sorting" class="select_boxes_product_page">';
						echo '<option value="">' . esc_attr( __( 'Product Attribute', 'woocommerce-reverb-integration' ) ) . '</option>';
						foreach ( $temp_attribute_array as $name => $title ) {
							$selected_attr = ( $previous_selected_attr == $name ) ? 'selected="selected"' : '';
							$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option ' . esc_attr( $selected_attr ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
						}
						echo '</select>';

						$this->search_box( 'Search Products', 'search_id', 'search_product' );
						submit_button( __( 'Filter', 'reverb-woocommerce-integration' ), 'action', 'filter_button', false, array() );
						echo '</div>';
						echo '</form>';
						echo '<div id="ced_reverb_per_page">';
						$_per_page = get_option( 'ced_reverb_list_per_page', '' );
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
		<div class="ced_reverb_preview_product_popup_main_wrapper"></div>
		<?php
	}
}

$ced_reverb_products_obj = new Ced_Reverb_Products_List();
$ced_reverb_products_obj->prepare_items();
