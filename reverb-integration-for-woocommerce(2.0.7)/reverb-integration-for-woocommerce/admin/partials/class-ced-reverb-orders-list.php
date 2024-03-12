<?php
/**
 * Display list of orders
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
 * Ced_reverb_Orders_List
 *
 * @since 1.0.0
 */
class Ced_Reverb_Orders_List extends WP_List_Table {

	/**
	 * Ced_reverb_Orders_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'reverb Order', 'reverb-woocommerce-integration' ),
				'plural'   => __( 'reverb Orders', 'reverb-woocommerce-integration' ),
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
		/**
 		* Filter hook for filtering no. of products per page on order page of plugin.
 		* @since 1.0.0
 		*/
		$per_page = apply_filters( 'ced_reverb_orders_list_per_page', 20 );
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

		$this->items = self::ced_reverb_orders( $per_page, $current_page );
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
		$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  group by `post_id` ", '_ced_reverb_order' . rifw_environment(), 1 ), 'ARRAY_A' );
		return count( $orders_post_ids );
	}

	/**
	 * Text displayed when no  data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Orders To Display.', 'reverb-woocommerce-integration' );
	}

	/**
	 * Function for id column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_id( $post_data ) {
		$order_id = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$edit_url = get_edit_post_link( $order_id, '' );
		echo '# <a href="' . esc_url( $edit_url ) . '" target="_blank"><b class="reverb-cool">' . esc_attr( $order_id ) . '</b></a>';
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
				echo '<p><b class="reverb-cool">' . esc_attr( $item_name ) . '</b>( ' . esc_attr( $quantity ) . ' )</p>';
			}
		}
	}

	/**
	 * Function for order Id column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_reverb_order_id( $post_data ) {
		$order_id        = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$reverb_order_id = get_post_meta( $order_id, '_ced_reverb_order_id', true );
		echo '<b class="reverb-success">' . esc_attr( $reverb_order_id ) . '</b>';
	}

	/**
	 * Function for order status column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_order_status( $post_data ) {
		$order_id            = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$reverb_order_status = get_post_meta( $order_id, '_reverb_umb_order_status', true );
		echo '<b class="">' . esc_attr( $reverb_order_status ) . '</b>';
	}


	/**
	 * Function for customer name column
	 *
	 * @since 1.0.0
	 * @param array $post_data Order Id.
	 */
	public function column_customer_name( $post_data ) {
		$order_id      = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
		$order_details = get_post_meta( $order_id, '_reverb_order_details', true );
		$customer_name = isset( $order_details['shipping_address']['name'] ) ? $order_details['shipping_address']['name'] : '';
		echo '<b class="reverb-cool">' . esc_attr( $customer_name ) . '</b>';
	}

	public function column_action( $post_data ) {
			$order_id      = isset( $post_data['post_id'] ) ? esc_attr( $post_data['post_id'] ) : '';
			$woo_order_url = get_edit_post_link( $order_id, '' );
			echo '<a href="' . esc_url( $woo_order_url ) . '" target="#">' . esc_html( __( 'Edit', 'reverb-woocommerce-integration' ) ) . '</a>';
	}

	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'id'              => __( 'WooCommerce Order ID', 'reverb-woocommerce-integration' ),
			'items'           => __( 'Order Items', 'reverb-woocommerce-integration' ),
			'reverb_order_id' => __( 'reverb Order ID', 'reverb-woocommerce-integration' ),
			'customer_name'   => __( 'Customer Name', 'reverb-woocommerce-integration' ),
			'order_status'    => __( 'Order Status', 'reverb-woocommerce-integration' ),
			'action'          => __( 'Action', 'reverb-woocommerce-integration' ),
		);
		/**
 		* Filter hook for filtering columns on order page.
 		* @since 1.0.0
 		*/
		$columns = apply_filters( 'ced_reverb_orders_columns', $columns );
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
		<div>
				<div class="ced_reverb_heading">
					<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
					<div class="ced_reverb_child_element">
						<ul type="disc">
							<li><?php echo esc_html_e( 'Reverb orders will be displayed here.' ); ?></li>
							<li><?php echo esc_html_e( 'You can fetch the reverb orders manually by clicking the fetch order button or also you can enable the auto fetch order feature in Schedulers ' ); ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_reverb&section=global_settings' ) ); ?>">here.</a></li>
							<li><?php echo esc_html_e( 'Make sure you have the skus present in all your products/variations for order syncing.' ); ?></li>
							<li><?php echo esc_html_e( 'You can also submit the tracking details from woocommerce to reverb . You need to go in the order edit section using Edit option in the order table below.Once you go in order edit section you will find the section at the bottom where you can enter tracking info and update them on reverb.' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="ced_reverb_heading ">
					<button  class="button button-primary" id="ced_reverb_fetch_orders" ><?php esc_html_e( 'Fetch Orders', 'reverb-woocommerce-integration' ); ?></button>	
				</div>
		<div class="ced_reverb_wrap ced_reverb_wrap_extn">
			
				<div id="post-body" class="metabox-holder columns-2 ced-reverb-product-list-wrapper">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'reverb_profiles', 'reverb_profiles_actions' );
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
	 * Function to get all the orders
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function ced_reverb_orders( $per_page, $page_number = 1 ) {
		global $wpdb;
		$offset = ( $page_number - 1 ) * $per_page;

			$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  order by `post_id` DESC LIMIT %d OFFSET %d", '_ced_reverb_order' . rifw_environment(), 1, $per_page, $offset ), 'ARRAY_A' );

		return( $orders_post_ids ) ? $orders_post_ids : array();
	}
}

$ced_reverb_orders_obj = new Ced_reverb_Orders_List();
$ced_reverb_orders_obj->prepare_items();
