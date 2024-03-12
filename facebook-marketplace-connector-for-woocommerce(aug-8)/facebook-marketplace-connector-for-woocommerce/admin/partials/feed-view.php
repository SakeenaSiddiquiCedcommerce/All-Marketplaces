<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Ced_FMCW_Feeds_List extends WP_List_Table {

	/**
	 * Ced_FMCW_Feeds_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'FMCW Feed', 'facebook-marketplace-connector-for-woocommerce' ), // singular name of the listed records
				'plural'   => __( 'FMCW Feeds', 'facebook-marketplace-connector-for-woocommerce' ), // plural name of the listed records
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

		$per_page = apply_filters( 'ced_fmcw_import_status_per_page', 10 );
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

		$this->items = self::ced_fmcw_get_import_ids( $per_page );

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
	public function ced_fmcw_get_import_ids( $per_page ) {
		// $ced_fmcw_update_feeds_data                = get_option( 'ced_fmcw_update_feeds_data', array() );
		global $wpdb;
		$ced_fmcw_update_feeds_data          = $wpdb->get_results( "SELECT * from {$wpdb->prefix}ced_fb_feeds_status", 'ARRAY_A' );
		$ced_fmcw_update_feeds_data          = array_reverse( $ced_fmcw_update_feeds_data );
		$current_page                        = $this->get_pagenum();
		$count                               = 0;
		$total_count                         = ( $current_page - 1 ) * $per_page;
		$ced_fmcw_import_ids_to_be_displayed = array();
		foreach ( $ced_fmcw_update_feeds_data as $key => $value ) {
			if ( 1 == $current_page && $count < $per_page ) {
				$count++;

					$ced_fmcw_import_ids_to_be_displayed[ $value['feed_id'] ]['feed_id'] = isset( $value['feed_id'] ) ? $value['feed_id'] : '';
					$ced_fmcw_import_ids_to_be_displayed[ $value['feed_id'] ]['type']    = $value['feed_type'];
					$ced_fmcw_import_ids_to_be_displayed[ $value['feed_id'] ]['time']    = $value['feed_time'];

			} elseif ( $current_page > 1 ) {
				if ( $key < $total_count ) {
					continue;
				} elseif ( $count < $per_page ) {
					$count++;
					$ced_fmcw_import_ids_to_be_displayed[ $value['feed_id'] ]['feed_id'] = $value['feed_id'];
					$ced_fmcw_import_ids_to_be_displayed[ $value['feed_id'] ]['type']    = $value['feed_type'];
					$ced_fmcw_import_ids_to_be_displayed[ $value['feed_id'] ]['time']    = $value['feed_time'];
				}
			}
		}
		return $ced_fmcw_import_ids_to_be_displayed;
	}

	/**
	 * Function to get number of responses
	 *
	 * @since 1.0.0
	 */
	public function get_count() {
		global $wpdb;
		$ced_fmcw_update_feeds_data = $wpdb->get_results( "SELECT * from {$wpdb->prefix}ced_fb_feeds_status", 'ARRAY_A' );

		return count( $ced_fmcw_update_feeds_data );
	}

	/**
	 * Function to display text when no data availbale
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No feeds to show.', 'facebook-marketplace-connector-for-woocommerce' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since 1.0.0
	 * @param array $ced_fmcw_feed_details Account Data.
	 */
	public function column_cb( $ced_fmcw_feed_data ) {
		if ( isset( $ced_fmcw_feed_data['feed_id'] ) && ! empty( $ced_fmcw_feed_data['feed_id'] ) ) {
			echo "<input type='checkbox' value=" . esc_attr( $ced_fmcw_feed_data['feed_id'] ) . " name='ced_fmcw_feed_ids[]'>";
		}
	}


	public function column_feed_action( $ced_fmcw_feed_data ) {
		$request_page              = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$call_for_get_shop_details = get_option( 'ced_fmcw_stord_whole_store_data' );
		$connected_page            = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';
		$actions                   = array(
			'edit' => sprintf( '<a class="ced_facebook_view_feed_details" href="?page=%s&section=%s&feedID=%s&auth_page=%s&panel=edit">View Details</a>', $request_page, 'feed-view', $ced_fmcw_feed_data['feed_id'], $connected_page ),
		);
		return $this->row_actions( $actions );
	}

	/**
	 * Function for import id column
	 *
	 * @since 1.0.0
	 * @param array $ced_fmcw_feed_details Account Data.
	 */
	public function column_import_id( $ced_fmcw_feed_data ) {
		if ( isset( $ced_fmcw_feed_data['feed_id'] ) && ! empty( $ced_fmcw_feed_data['feed_id'] ) ) {
			$request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			echo '<b>Feed Id : <a>' . esc_attr( $ced_fmcw_feed_data['feed_id'] ) . '</a></b>';

		}
	}



	/**
	 * Function for feed type column
	 *
	 * @since 1.0.0
	 * @param array $ced_fmcw_feed_details Account Data.
	 */
	public function column_type( $ced_fmcw_feed_data ) {
		if ( isset( $ced_fmcw_feed_data['feed_id'] ) && ! empty( $ced_fmcw_feed_data['feed_id'] ) ) {
			echo '<b class="fmcw-success">' . esc_attr( strtoupper( str_replace( '_', ' ', $ced_fmcw_feed_data['type'] ) ) ) . '</b>';
		}
	}




	/**
	 * Function for feed time column
	 *
	 * @since 1.0.0
	 * @param array $ced_fmcw_feed_details Account Data.
	 */
	public function column_time( $ced_fmcw_feed_data ) {
		if ( isset( $ced_fmcw_feed_data['feed_id'] ) && ! empty( $ced_fmcw_feed_data['feed_id'] ) ) {
			echo '<b>' . esc_attr( $ced_fmcw_feed_data['time'] ) . '</b>';
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
			'import_id'   => __( 'Meta Feed ID', 'facebook-marketplace-connector-for-woocommerce' ),
			'time'        => __( 'Meta Feed Time', 'facebook-marketplace-connector-for-woocommerce' ),
			'type'        => __( 'Meta Feed Type', 'facebook-marketplace-connector-for-woocommerce' ),
			'feed_action' => __( 'Action', 'facebook-marketplace-connector-for-woocommerce' ),
		);
		$columns = apply_filters( 'ced_fmcw_alter_import_status_table_columns', $columns );
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
		<div class="ced_fb_wrap ced_fb_wrap_extn">
			<div class="ced_fb_setting_header ">
				<h2 class="manage_labels"><?php esc_html_e( "Meta's Products Feeds", 'woocommerce-fb-integration' ); ?></h2>
			</div>          
			<div>
				<div id="post-body" class="metabox-holder columns-2 ced-facebook-feed-wrapper ced-facebook-back">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'facebook_profiles', 'fmcw_profiles_actions' );
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
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

			if ( ! isset( $_POST['fmcw_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fmcw_profiles_actions'] ) ), 'facebook_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action2'] ) ) {

			if ( ! isset( $_POST['fmcw_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fmcw_profiles_actions'] ) ), 'facebook_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';
			return $action;
		}
	}


	/**
	 * Function to process bulk actions.
	 */
	public function process_bulk_action() {
		if ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {
			$file = CED_FMCW_DIRPATH . 'admin/partials/single-feed-view.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
		}
	}


}

$ced_fmcw_feed_obj = new Ced_FMCW_Feeds_List();
$ced_fmcw_feed_obj->prepare_items();