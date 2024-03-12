<?php
/**
 * Display list of orders
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
 * Ced_Walmart_Orders_List
 *
 * @since 1.0.0
 */
class Ced_Walmart_Orders_List extends WP_List_Table {

	/**
	 * Ced_Walmart_Orders_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Walmart Order', 'walmart-woocommerce-integration' ),
				'plural'   => __( 'Walmart Orders', 'walmart-woocommerce-integration' ),
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
		/** Get per page number for listing orders
		 *
		 * @since 1.0.0
		 */
		$per_page = apply_filters( 'ced_walmart_orders_list_per_page', 20 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::ced_walmart_orders( $per_page, $current_page );
		$count       = self::get_count();

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->render_html();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 */
	public function get_count() {
		global $wpdb;
		$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  order by `post_id` ", '_ced_walmart_order' . wifw_environment(), 1 ), 'ARRAY_A' );
		return count( $orders_post_ids );
	}

	/**
	 * Text displayed when no  data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Orders To Display.', 'walmart-woocommerce-integration' );
	}

	/**
	 * Function for id column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_id( $post_data ) {
		$order_id = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		echo '<a href=' . esc_attr( get_edit_post_link( $order_id ) ) . ' target="_blank"># <b class="walmart-cool">' . esc_attr( $order_id ) . ' </a></b>';
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_items( $post_data ) {
		$order_id    = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$_order      = wc_get_order( $order_id );
		$order_items = $_order->get_items();
		if ( is_array( $order_items ) && ! empty( $order_items ) ) {
			foreach ( $order_items as $index => $_item ) {
				$line_items = $_item->get_data();
				$quantity   = isset( $line_items['quantity'] ) ? $line_items['quantity'] : 0;
				$item_name  = isset( $line_items['name'] ) ? $line_items['name'] : '';
				echo '<p><b class="walmart-cool">' . esc_attr( $item_name ) . '</b>( ' . esc_attr( $quantity ) . ' )</p>';
			}
		}
	}

	/**
	 * Function for order Id column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_walmart_order_id( $post_data ) {
		$order_id         = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$walmart_order_id = get_post_meta( $order_id, '_ced_walmart_order_id', true );
		echo '<b class="walmart-success">' . esc_attr( $walmart_order_id ) . '</b>';
	}

	/**
	 * Function for order status column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_order_status( $post_data ) {
		$order_id             = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$walmart_order_status = get_post_meta( $order_id, '_ced_walmart_order_status', true );
		echo '<b class="">' . esc_attr( $walmart_order_status ) . '</b>';
	}

	/**
	 * Function for Edit order column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_action( $post_data ) {
		$order_id        = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$order_edit_link = admin_url( 'admin.php?page=ced_walmart&section=orders&panel=edit&id=' . $order_id );
		echo '<a href="' . esc_url( $order_edit_link ) . '" >' . esc_html( __( 'Edit', 'walmart-woocommerce-integration' ) ) . '</a>';
	}

	/**
	 * Function for customer name column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_customer_name( $post_data ) {
		$order_id      = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$order_details = get_post_meta( $order_id, 'order_detail', true );
		$customer_name = isset( $order_details['shippingInfo']['postalAddress']['name'] ) ? $order_details['shippingInfo']['postalAddress']['name'] : '';
		echo '<b class="walmart-cool">' . esc_attr( $customer_name ) . '</b>';
	}

	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'id'               => __( 'WooCommerce Order', 'walmart-woocommerce-integration' ),
			'items'            => __( 'Order Items', 'walmart-woocommerce-integration' ),
			'walmart_order_id' => __( 'Walmart Order ID', 'walmart-woocommerce-integration' ),
			'customer_name'    => __( 'Customer Name', 'walmart-woocommerce-integration' ),
			'order_status'     => __( 'Order Status', 'walmart-woocommerce-integration' ),
			'action'           => __( 'Action', 'walmart-woocommerce-integration' ),
		);
		/** Get columns for orders
		 *
		 * @since 1.0.0
		 */
		$columns = apply_filters( 'ced_walmart_orders_columns', $columns );
		return $columns;
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
	 * Render html content
	 *
	 * @since 1.0.0
	 */
	public function render_html() {
		?>
		<div class="ced_walmart_wrap ced_walmart_wrap_extn">
			<div>
				<div class="ced_walmart_heading">
					<?php echo esc_html_e( get_instuctions_html() ); ?>
					<div class="ced_walmart_child_element default_modal">
						<ul type="disc">
							<li><?php echo esc_html_e( 'All the Walmart orders will be listed here.' ); ?></li>
							<li><?php echo esc_html_e( 'Click on Fetch Order to get Walmart orders to WooCommerce' ); ?></li>
							<li><?php echo esc_html_e( 'You can perform different operation on Walmart order using Edit option.' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="ced_walmart_heading ">
					<button  class="button button-primary" id="ced_walmart_fetch_orders" ><?php esc_html_e( 'Fetch Orders', 'walmart-woocommerce-integration' ); ?></button>	
				</div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'walmart_profiles', 'walmart_profiles_actions' );
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Function for getting current status
	 *
	 * @since 1.0.0
	 */
	public function current_action() {
		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['panel'] ) ? sanitize_text_field( wp_unslash( $_GET['panel'] ) ) : '';
			return $action;
		}
	}


		/**
		 * Function for processing bulk actions
		 *
		 * @since 1.0.0
		 */
	public function process_bulk_action() {
		if ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {
			$file = CED_WALMART_DIRPATH . 'admin/pages/ced-walmart-order-edit.php';
			include_file( $file );
		}
	}

	/**
	 * Function to get all the orders
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function ced_walmart_orders( $per_page, $page_number = 1 ) {
		global $wpdb;
		$offset          = ( $page_number - 1 ) * $per_page;
		$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  order by `post_id` DESC LIMIT %d OFFSET %d", '_ced_walmart_order' . wifw_environment(), 1, $per_page, $offset ), 'ARRAY_A' );

		return( $orders_post_ids ) ? $orders_post_ids : array();
	}
}

$ced_walmart_orders_obj = new Ced_Walmart_Orders_List();
$ced_walmart_orders_obj->prepare_items();
