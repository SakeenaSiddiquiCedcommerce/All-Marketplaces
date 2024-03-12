<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/header.php';
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Ced_Tokopedia_Profile_Table extends WP_List_Table {

	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Tokopedia Profile', 'woocommerce-tokopedia-integration' ),
				'plural'   => __( 'Tokopedia Profiles', 'woocommerce-tokopedia-integration' ),
				'ajax'     => false,
			)
		);
	}

	public function prepare_items() {

		global $wpdb;
		$per_page              = apply_filters( 'ced_tokopedia_profile_list_per_page', 10 );
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
		$this->items = self::get_profiles( $per_page, $current_page );
		$count       = self::get_count();

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::get_profiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	public function get_profiles( $per_page = 10, $page_number = 1 ) {

		global $wpdb;
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$offset    = ( $page_number - 1 ) * $per_page;
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_tokopedia_profiles WHERE `shop_name`= %s ORDER BY `id` DESC LIMIT %d OFFSET %d", $shop_name, $per_page, $offset ), 'ARRAY_A' );

		return $result;

	}

	/**
	 * Function to count number of responses in result
	 */
	public function get_count() {

		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		global $wpdb;
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_tokopedia_profiles WHERE `shop_name`= %s ", $shop_name ), 'ARRAY_A' );
		return count( $result );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No Profiles Created.', 'woocommerce-tokopedia-integration' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="tokopedia_profile_ids[]" value="%s" />',
			$item['id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_profile_name( $item ) {

		$shop_name       = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$title           = '<strong>' . $item['profile_name'] . '</strong>';
		$url             = admin_url( 'admin.php?page=ced_tokopedia&profileID=' . $item['id'] . '&section=profiles-view&panel=edit&shop_name=' . $shop_name );
		$actions['edit'] = '<a href=' . $url . '>Edit</a>';
		print_r( $title );
		return $this->row_actions( $actions );
	}


	public function column_profile_status( $item ) {

		if ( 'inactive' == $item['profile_status'] ) {
			return 'InActive';
		} else {
			return 'Active';
		}
	}

	public function column_woo_categories( $item ) {

		$woo_categories = json_decode( $item['woo_categories'], true );

		if ( ! empty( $woo_categories ) ) {
			foreach ( $woo_categories as $key => $value ) {
				$term = get_term_by( 'id', $value, 'product_cat' );
				if ( $term ) {
					echo '<p>' . esc_attr( $term->name ) . '</p>';
				}
			}
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'profile_name'   => __( 'Profile Name', 'woocommerce-tokopedia-integration' ),
			'profile_status' => __( 'Profile Status', 'woocommerce-tokopedia-integration' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'woocommerce-tokopedia-integration' ),
			'edit_profiles'  => __( ' Edit Profiles' , 'woocommerce-tokopedia-integration' ),

		);
		$columns = apply_filters( 'ced_tokopedia_alter_profiles_table_columns', $columns );
		return $columns;
	}




	public function column_edit_profiles($item ){
		$shop_name  = isset($_GET['shop_name']) ?  sanitize_text_field( wp_unslash($_GET['shop_name'])) : '' ;
		$edit_url   = admin_url('admin.php?page=ced_tokopedia&profileID=' . $item['id'] . '&section=profiles-view&panel=edit&shop_name=' . $shop_name);
		echo "<a class='button-primary' href='".$edit_url."'>Edit</a>";
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => __('Delete', 'woocommerce-tokopedia-integration' ),
		];
		return $actions;
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
	 *  Function to get changes in html
	 *  Top tokopedia profile section.
	 */
	public function renderHTML() {
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$url       = admin_url( 'admin.php?page=ced_tokopedia&section=profiles-view&panel=edit&shop_name=' . $shop_name );
		?>
		<div class="ced_tokopedia_wrap ced_tokopedia_wrap_extn">
			<div class="ced_tokopedia_setting_header ">
				<b class="manage_labels"><?php esc_html_e( 'TOKOPEDIA PROFILES', 'woocommerce-tokopedia-integration' ); ?></b>
				<a href="<?php echo esc_attr( $url ); ?>"  class="ced_tokopedia_custom_button add_profile_button"><?php esc_html_e( 'Add Profile', 'woocommerce-tokopedia-integration' ); ?></a>
			</div>			
			<div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'tokopedia_profiles', 'tokopedia_profiles_actions' );
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

	public function current_action() {

		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['panel'] ) ? sanitize_text_field( wp_unslash( $_GET['panel'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
			if ( ! isset( $_POST['tokopedia_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tokopedia_profiles_actions'] ) ), 'tokopedia_profiles' ) ) {
				return;
			}
			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		}
	}

	public function process_bulk_action() {

		if ( ! session_id() ) {
			session_start();
		}


		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) ) {

			$profileIds = isset( $_POST['tokopedia_profile_ids'] ) ? $_POST['tokopedia_profile_ids'] : array();
			if( is_array( $profileIds ) && !empty( $profileIds ) ){

				global $wpdb;
				$tableName = $wpdb->prefix.'ced_tokopedia_profiles';

				
				$shop_id = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';

				foreach ($profileIds as $index => $pid) {

					$product_ids_assigned = get_option( 'ced_tokopedia_product_ids_in_profile_' . $pid, array() );
					foreach ( $product_ids_assigned as $index => $ppid ) {
						delete_post_meta( $ppid, 'ced_tokopedia_profile_assigned' . $shop_id );
					}

					$term_id = $wpdb->get_results( $wpdb->prepare( ' SELECT `woo_categories` FROM '.$tableName.' WHERE `id` = %d ', $pid ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
					foreach ( $term_id as $key => $value ) {
						delete_term_meta( $value, 'ced_tokopedia_profile_created_' . $shop_id );
						delete_term_meta( $value, 'ced_tokopedia_profile_id_' . $shop_id );
						delete_term_meta( $value, 'ced_tokopedia_mapped_category_' . $shop_id );
					}
				}
				
				$sql = "DELETE FROM `".$tableName."` WHERE `id` IN (";
				foreach ($profileIds as $id) {
					$sql .= $id.',';
				}
				$sql = rtrim($sql, ",");
				$sql .= ')';
				$deleteStatus = $wpdb->query($sql);

				$redirectURL = get_admin_url()."admin.php?page=ced_tokopedia&section=profiles-view&shop_name=".$_GET['shop_name'];
				wp_redirect($redirectURL);
			}
		}elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {

			require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/profile-edit-view.php';
		}
	}
}
$ced_tokopedia_profile_obj = new Ced_Tokopedia_Profile_Table();
$ced_tokopedia_profile_obj->prepare_items();
