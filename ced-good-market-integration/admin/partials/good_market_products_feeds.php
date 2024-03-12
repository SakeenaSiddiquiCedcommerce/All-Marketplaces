<?php
/**
 * Product feeds listing
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_good_market_header();
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Ced_Good_Market_Feeds_List extends WP_List_Table {

	/**
	 * Ced_Good_Market_Feeds_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Good Market Feed', 'good_market-woocommerce-integration' ), // singular name of the listed records
				'plural'   => __( 'Good Market Feeds', 'good_market-woocommerce-integration' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	/**
	 * Function to prepare feed data to be displayed
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		global $wpdb;

		$per_page = apply_filters( 'ced_goodmarket_import_status_per_page', 10 );
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

		$this->items = self::ced_goodmarket_get_import_ids( $per_page );

		$count = self::get_count();

		if ( ! $this->current_action() ) {
			$this->set_pagination_args(
				array(
					'total_items' => $count,
					'per_page'    => $per_page,
					'total_pages' => ceil( $count / $per_page ),
				)
			);
			$this->render_html();
		} else {
			$this->process_bulk_action();
		}

	}

	/**
	 * Function to get import ids
	 *
	 * @since 1.0.0
	 */
	public function ced_goodmarket_get_import_ids( $per_page = 10 ) {
		// $ced_goodmarket_update_feeds_data                = get_option( 'ced_goodmarket_update_feeds_data', array() );
		global $wpdb;
		$ced_goodmarket_update_feeds_data          = $wpdb->get_results( "SELECT * from {$wpdb->prefix}good_market_upload_status", 'ARRAY_A' );
		$ced_goodmarket_update_feeds_data          = array_reverse( $ced_goodmarket_update_feeds_data );
		$current_page                              = $this->get_pagenum();
		$count                                     = 0;
		$total_count                               = ( $current_page - 1 ) * $per_page;
		$ced_goodmarket_import_ids_to_be_displayed = array();
		foreach ( $ced_goodmarket_update_feeds_data as $key => $value ) {
			if ( 1 == $current_page && $count < $per_page ) {
				$count++;

					$ced_goodmarket_import_ids_to_be_displayed[ $value['feed_id'] ]['feed_id'] = isset( $value['feed_id'] ) ? $value['feed_id'] : '';
					$ced_goodmarket_import_ids_to_be_displayed[ $value['feed_id'] ]['type']    = 'Product Upload';
					$ced_goodmarket_import_ids_to_be_displayed[ $value['feed_id'] ]['time']    = $value['feed_time'];

			} elseif ( $current_page > 1 ) {
				if ( $key < $total_count ) {
					continue;
				} elseif ( $count < $per_page ) {
					$count++;
					$ced_goodmarket_import_ids_to_be_displayed[ $value['feed_id'] ]['feed_id'] = $value['feed_id'];
					$ced_goodmarket_import_ids_to_be_displayed[ $value['feed_id'] ]['type']    = 'Product Upload';
					$ced_goodmarket_import_ids_to_be_displayed[ $value['feed_id'] ]['time']    = $value['feed_time'];
				}
			}
		}
		return $ced_goodmarket_import_ids_to_be_displayed;
	}

	/**
	 * Function to get number of responses
	 *
	 * @since 1.0.0
	 */
	public function get_count() {
		global $wpdb;
		$ced_goodmarket_update_feeds_data = $wpdb->get_results( "SELECT * from {$wpdb->prefix}good_market_upload_status", 'ARRAY_A' );

		return count( $ced_goodmarket_update_feeds_data );
	}

	/**
	 * Function to display text when no data availbale
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No feeds to show.', 'good_market-woocommerce-integration' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since 1.0.0
	 * @param array $ced_good_market_users_detail Account Data.
	 */
	public function column_cb( $ced_goodmarket_feed_data ) {
		if ( isset( $ced_goodmarket_feed_data['feed_id'] ) && ! empty( $ced_goodmarket_feed_data['feed_id'] ) ) {
			echo "<input type='checkbox' value=" . esc_attr( $ced_goodmarket_feed_data['feed_id'] ) . " name='ced_goodmarket_import_ids[]'>";
		}
	}

	/**
	 * Function for import id column
	 *
	 * @since 1.0.0
	 * @param array $ced_good_market_users_detail Account Data.
	 */
	public function column_import_id( $ced_goodmarket_feed_data ) {
		if ( isset( $ced_goodmarket_feed_data['feed_id'] ) && ! empty( $ced_goodmarket_feed_data['feed_id'] ) ) {
			$request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			echo '<b>Feed Id : <a>' . esc_attr( $ced_goodmarket_feed_data['feed_id'] ) . '</a></b>';
			$url             = admin_url( 'admin.php?page=ced_good_market&section=feeds&panel' );
			$actions['edit'] = sprintf( '<a href="?page=%s&section=%s&feed_id=%s&panel=edit">View Details</a>', $request_page, 'products_feeds_details', $ced_goodmarket_feed_data['feed_id'] );
			return $this->row_actions( $actions, true );
		}
	}



	/**
	 * Function for feed type column
	 *
	 * @since 1.0.0
	 * @param array $ced_good_market_users_detail Account Data.
	 */
	public function column_type( $ced_goodmarket_feed_data ) {
		if ( isset( $ced_goodmarket_feed_data['feed_id'] ) && ! empty( $ced_goodmarket_feed_data['feed_id'] ) ) {
			echo '<b class="goodmarket-success">' . esc_attr( strtoupper( str_replace( '_', ' ', $ced_goodmarket_feed_data['type'] ) ) ) . '</b>';
		}
	}




	/**
	 * Function for feed time column
	 *
	 * @since 1.0.0
	 * @param array $ced_good_market_users_detail Account Data.
	 */
	public function column_time( $ced_goodmarket_feed_data ) {
		if ( isset( $ced_goodmarket_feed_data['feed_id'] ) && ! empty( $ced_goodmarket_feed_data['feed_id'] ) ) {
			echo '<b>' . esc_attr( $ced_goodmarket_feed_data['time'] ) . '</b>';
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			// 'cb'        => '<input type="checkbox">',
			'import_id' => __( 'Good Market Feed ID', 'good_market-woocommerce-integration' ),
			'time'      => __( 'Good Market Feed Time', 'good_market-woocommerce-integration' ),
			'type'      => __( 'Good Market Feed Type', 'good_market-woocommerce-integration' ),
		);
		$columns = apply_filters( 'ced_good_market_alter_import_status_table_columns', $columns );
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

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
	}

	/**
	 * Function to get changes in html
	 */
	public function render_html() {
		?>
		<div class="ced_good_market_wrap ced_good_market_wrap_extn">
			<div>
				<div class="ced_good_market_heading">
					<?php echo esc_html_e( get_instuction_html() ); ?>
					<div class="ced_good_market_child_element default_modal">
						<ul type="disc">
							<li><?php echo esc_html_e( 'Good Market syncing activity will be listed here.' ); ?></li>
							<li><?php echo esc_html_e( 'To see the details of a syncing activity click View Details.' ); ?></li>
						</ul>
					</div>
				</div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'goodmarket_profiles', 'goodmarket_profiles_actions' );
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
		} elseif ( isset( $_POST['action'] ) ) {

			if ( ! isset( $_POST['goodmarket_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['goodmarket_profiles_actions'] ) ), 'goodmarket_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action2'] ) ) {

			if ( ! isset( $_POST['goodmarket_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['goodmarket_profiles_actions'] ) ), 'goodmarket_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';
			return $action;
		}
	}


	/**
	 * Function to process bulk actions.
	 */
	public function process_bulk_action() {}


}

$ced_goodmarket_feed_obj = new Ced_Good_Market_Feeds_List();
$ced_goodmarket_feed_obj->prepare_items();
