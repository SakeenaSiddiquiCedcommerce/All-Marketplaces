<?php
/**
 * Display list of orders
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
 * Ced_Good_Market_Orders_List
 *
 * @since 1.0.0
 */
class Ced_Good_Market_Orders_List extends WP_List_Table {

	/**
	 * Ced_Good_Market_Orders_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Good Market Order', 'good_market-woocommerce-integration' ),
				'plural'   => __( 'Good Market Orders', 'good_market-woocommerce-integration' ),
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
		$per_page = apply_filters( 'ced_good_market_orders_list_per_page', 20 );
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

		$this->items = self::ced_good_market_orders( $per_page, $current_page );
		$count       = self::get_count();
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::ced_good_market_orders( $per_page, $current_page );
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
		$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  group by `post_id` ", '_ced_good_market_order', 1 ), 'ARRAY_A' );
		// print_r($wpdb);die('gh');
		return count( $orders_post_ids );
	}

	/**
	 * Text displayed when no  data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Orders To Display.', 'good_market-woocommerce-integration' );
	}

	/**
	 * Function for id column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_id( $post_data ) {
		$order_id = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		echo '<a href=' . esc_attr( get_edit_post_link( $order_id ) ) . ' target="_blank"># <span class="good_market-id">' . esc_attr( $order_id ) . ' </a></span>';
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
				echo '<p><span class="good_market-orderitem">' . esc_attr( $item_name ) . '</span>( ' . esc_attr( $quantity ) . ' )</p>';
			}
		}
	}

	/**
	 * Function for order Id column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_good_market_order_id( $post_data ) {
		$order_id             = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$good_market_order_id = get_post_meta( $order_id, 'order_detail', true );

		// $good_market_order_id = get_post_meta( $order_id, '_ced_good_market_order_id', true );
		echo '<span class="good_market-orderid">' . esc_attr( $good_market_order_id['order_increment_id'] ) . '</span>';
	}

	/**
	 * Function for order status column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_order_status( $post_data ) {
		$order_id                 = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$good_market_order_status = get_post_meta( $order_id, '_ced_good_market_order_status', true );
		echo '<span class="">' . esc_attr( $good_market_order_status ) . '</span>';
	}

	/**
	 * Function for Edit order column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_action( $post_data ) {
		$order_id        = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$order_edit_link = admin_url( 'admin.php?page=ced_good_market&section=orders&panel=edit&id=' . $order_id );
		echo '<a href="' . esc_url( $order_edit_link ) . '" >' . esc_html( __( 'Ship', 'good_market-woocommerce-integration' ) ) . '</a>';
	}

	/**
	 * Function for customer name column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_customer_name( $post_data ) {
		$order_id            = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$order_details       = get_post_meta( $order_id, 'order_detail', true );
		$account_information = $order_details['order_account_information']['account_information'];
		$customer_name       = isset( $account_information['customer_name'] ) ? $account_information['customer_name'] : '';
		echo '<span class="good_market-customer_name">' . esc_attr( $customer_name ) . '</span>';
	}

	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'id'                   => __( 'WooCommerce Order', 'good_market-woocommerce-integration' ),
			'good_market_order_id' => __( 'Good Market Order ID', 'good_market-woocommerce-integration' ),
			'customer_name'        => __( 'Customer Name', 'good_market-woocommerce-integration' ),
			'items'                => __( 'Order Items', 'good_market-woocommerce-integration' ),
			'order_status'         => __( 'Order Status', 'good_market-woocommerce-integration' ),
			'action'               => __( 'Action', 'good_market-woocommerce-integration' ),
		);
		$columns = apply_filters( 'ced_good_market_orders_columns', $columns );
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
		<div class="ced_good_market_wrap ced_good_market_wrap_extn">
			<div>
				<div class="ced_good_market_heading">
					<?php echo esc_html_e( get_instuction_html() ); ?>
					<div class="ced_good_market_child_element default_modal">
						<ul type="disc">
							<li><?php echo esc_html_e( 'All orders through Good Market will be listed here.' ); ?></li>
							<li><?php echo esc_html_e( 'Orders sync automatically. If you want to trigger an immediate sync, click the Update Orders button.' ); ?></li>
							
						</ul>
					</div>
				</div>
				<div class="ced_good_market_heading_button">
					<button  class="button button-primary" id="ced_good_market_fetch_orders" ><?php esc_html_e( 'Update Orders', 'good_market-woocommerce-integration' ); ?></button>	
				</div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'good_market_profiles', 'good_market_profiles_actions' );
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
			$file = GOOD_MARKET_INTEGRATION_DIRPATH . 'admin/pages/ced-good_market-order-edit.php';
			include_files( $file );
		}
	}

	/**
	 * Function to get all the orders
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function ced_good_market_orders( $per_page, $page_number = 1 ) {
		$offset = ($page_number-1)*$per_page;
		global $wpdb;
		$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  ORDER BY  `post_id` DESC LIMIT %d,%d", '_ced_good_market_order', 1, $offset, $per_page ), 'ARRAY_A' );
		// print_r($wpdb);die;
		return( $orders_post_ids ) ? $orders_post_ids : array();
	}
}

$ced_good_market_orders_obj = new Ced_Good_Market_Orders_List();
$ced_good_market_orders_obj->prepare_items();