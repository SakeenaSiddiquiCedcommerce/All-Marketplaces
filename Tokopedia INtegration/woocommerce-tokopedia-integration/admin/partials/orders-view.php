<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_TOKOPEDIA_DIRPATH . 'admin/partials/header.php';

if ( file_exists( $file ) ) {
	require_once $file;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Ced_Tokopedia_List_Orders extends WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Tokopedia Order', 'woocommerce-tokopedia-integration' ),
				'plural'   => __( 'Tokopedia Orders', 'woocommerce-tokopedia-integration' ),
				'ajax'     => true,
			)
		);
	}
	/**
	 *
	 * Function for preparing data to be displayed
	 */
	public function prepare_items() {

		$per_page              = apply_filters( 'ced_tokopedia_orders_list_per_page', 10 );
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

		$this->items = self::get_orders( $per_page, $current_page );
		$count       = self::get_count();
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);
		if ( ! $this->current_action() ) {
			$this->renderHTML();
		}

	}
	/*
	*
	* Text displayed when no  data is available
	*
	*/
	public function no_items() {
		esc_html_e( 'No Orders To Display.', 'woocommerce-tokopedia-integration' );
	}
	/**
	 *
	 * Function for id column
	 */
	public function column_id( $items ) {
		foreach ( $items as $key => $value ) {
			$displayOrders = $value->get_data();
			echo '<b>' . esc_attr( $displayOrders['order_id'] ) . '</b>';
			break;
		}
	}
	/**
	 *
	 * Function for name column
	 */
	public function column_name( $items ) {
		foreach ( $items as $key => $value ) {
			$displayOrders = $value->get_data();
			$productId     = $displayOrders['product_id'];
			$url           = get_edit_post_link( $productId, '' );
			echo '<b><a class="ced_tokopedia_prod_name" href="' . esc_attr( $url ) . '" target="#">' . esc_attr( $displayOrders['name'] ) . '</a></b></br>';

		}
	}
	/**
	 *
	 * Function for order Id column
	 */
	public function column_tokopedia_order_id( $items ) {
		foreach ( $items as $key => $value ) {
			$displayOrders   = $value->get_data();
			$orderID         = $displayOrders['order_id'];
			$details         = wc_get_order( $orderID );
			$details         = $details->get_data();
			$order_meta_data = $details['meta_data'];
			foreach ( $order_meta_data as $key1 => $value1 ) {
				$order_id = $value1->get_data();
				if ( 'merchant_order_id' == $order_id['key'] ) {
					echo '<b>' . esc_attr( $order_id['value'] ) . '</b>';

				}
			}
			break;
		}
	}
	/**
	 *
	 * Function for order status column
	 */
	public function column_order_status( $items ) {
		foreach ( $items as $key => $value ) {
			$displayOrders   = $value->get_data();
			$orderID         = $displayOrders['order_id'];
			$details         = wc_get_order( $orderID );
			$details         = $details->get_data();
			$order_meta_data = $details['meta_data'];
			foreach ( $order_meta_data as $key1 => $value1 ) {
				$order_status = $value1->get_data();
				if ( '_tokopedia_umb_order_status' == $order_status['key'] ) {
					echo '<b>' . esc_attr( $order_status['value'] ) . '</b>';
				}
			}
			break;
		}
	}
	/**
	 *
	 * Function for Edit order column
	 */
	public function column_action( $items ) {
		foreach ( $items as $key => $value ) {
			$displayOrders = $value->get_data();
			$woo_order_url = get_edit_post_link( $displayOrders['order_id'], '' );
			echo '<a href="' . esc_url( $woo_order_url ) . '" target="#">Edit</a>';
			break;
		}
	}
	/**
	 *
	 * Function for customer name column
	 */
	public function column_customer_name( $items ) {
		foreach ( $items as $key => $value ) {
			$displayOrders = $value->get_data();
			$orderID       = $displayOrders['order_id'];
			$details       = wc_get_order( $orderID );
			$details       = $details->get_data();
			echo '<b>' . esc_attr( $details['billing']['first_name'] ) . '</b>';
			break;
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */

	public function get_columns() {
		$columns = array(
			'id'                 => __( 'WooCommerce Order', 'woocommerce-tokopedia-integration' ),
			'name'               => __( 'Product Name', 'woocommerce-tokopedia-integration' ),
			'tokopedia_order_id' => __( 'Tokopedia Order ID', 'woocommerce-tokopedia-integration' ),
			// 'customer_name'      => __( 'Customer Name', 'woocommerce-tokopedia-integration' ),
			'order_status'       => __( 'Order Status', 'woocommerce-tokopedia-integration' ),
			'action'             => __( 'Action', 'woocommerce-tokopedia-integration' ),
		);
		$columns = apply_filters( 'ced_tokopedia_orders_columns', $columns );
		return $columns;
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
	public function renderHTML() {
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		?>
		<div class="ced_tokopedia_wrap ced_tokopedia_wrap_extn">
			<div class="ced_tokopedia_setting_header ">
				<label class="manage_labels"><b><?php esc_html_e( 'TOKOPEDIA ORDERS', 'woocommerce-tokopedia-integration' ); ?></b></label>
				<?php echo '<button  class="ced_tokopedia_custom_button" id="ced_tokopedia_fetch_orders" data-id="' . esc_attr( $shop_name ) . '" >' . esc_html( __( 'Fetch Orders', 'woocommerce-tokopedia-integration' ) ) . '</button>'; ?>
			</div>
			<div id="post-body" class="metabox-holder columns-2">
				<div id="">
					<div class="meta-box-sortables ui-sortable">
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
		<?php
	}

	public function get_count() {
		global $wpdb;
		$shop_name      = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$orders_post_id = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d", 'ced_tokopedia_order_shop_id', $shop_name ), 'ARRAY_A' );
		return count( $orders_post_id );
	}

	/*
	 *
	 *  Function to get all the orders
	 *
	 */
	public function get_orders( $per_page, $current_page ) {

		global $wpdb;
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$offset    = ( $current_page - 1 ) * $per_page;

		$orders_post_id = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%s  order by `post_id` DESC LIMIT %d OFFSET %d", 'ced_tokopedia_order_shop_id', $shop_name, $per_page, $offset ), 'ARRAY_A' );
		
		foreach ( $orders_post_id as $key => $value ) {
			$post_id        = isset( $value['post_id'] ) ? $value['post_id'] : '';
			$post_details   = wc_get_order( $post_id );
			$order_detail[] = $post_details->get_items();
		}
		$order_detail = isset( $order_detail ) ? $order_detail: array();
		return( $order_detail );
	}
}

$ced_tokopedia_orders_obj = new Ced_Tokopedia_List_Orders();
$ced_tokopedia_orders_obj->prepare_items();

