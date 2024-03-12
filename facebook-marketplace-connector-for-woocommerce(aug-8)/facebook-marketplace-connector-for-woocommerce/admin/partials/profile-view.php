<?php
/**
 * Display list of profiles
 *
 * @package  Woocommerce_Facebook_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_FMCW_DIRPATH . 'admin/partials/ced-fmcw-header.php';
if ( file_exists( $file ) ) {
	include_once $file;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_Facebook_Profile_Table
 *
 * @since 1.0.0
 */
class Ced_Facebook_Profile_Table extends WP_List_Table {

	/**
	 * Ced_Facebook_Profile_Table construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Facebook Profile', 'woocommerce-fb-integration' ),
				'plural'   => __( 'Facebook Profiles', 'woocommerce-fb-integration' ),
				'ajax'     => false,
			)
		);

	}

	/**
	 * Function for preparing profile data to be displayed column
	 *
	 * @since 1.0.0
	 */
	public function prepareItems() {
		global $wpdb;

		$per_page = apply_filters( 'ced_fb_profile_list_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->getSortableColumns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::cedFacebookGetProfiles( $per_page, $current_page );

		$count = self::getCount();

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::cedFacebookGetProfiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 * Function for status column
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function cedFacebookGetProfiles( $per_page = 10, $page_number = 1 ) {
		global $wpdb;
		$offset = ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM wp_ced_fb_profiles ORDER BY `id` DESC LIMIT %d OFFSET %d', $per_page, $offset ), 'ARRAY_A' );
		return $result;
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 */
	public function getCount() {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM wp_ced_fb_profiles' ), 'ARRAY_A' );
		return count( $result );
	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Profiles Created.', 'woocommerce-fb-integration' );
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $item Profile Data.
	 */
	public function column_profile_name( $item ) {
		$title        = '<strong>' . $item['profile_name'] . '</strong>';
		$request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$actions      = array(
			'edit'   => sprintf( '<a href="?page=%s&section=%s&profileID=%s&panel=edit">Edit</a>', $request_page, 'profile-view', $item['id'] ),
			'delete' => sprintf( '<a href="?page=%s&section=%s&profileID=%s&panel=delete">Delete</a>', $request_page, 'profile-view', $item['id'], 'bulk-delete' ),
		);
		return $title . $this->row_actions( $actions );
		return $title;
	}

	/**
	 * Function for category column
	 *
	 * @since 1.0.0
	 * @param array $item Profile Data.
	 */
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
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'profile_name'   => __( 'Profile Name', 'woocommerce-fb-integration' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'woocommerce-fb-integration' ),
		);
		$columns = apply_filters( 'ced_fb_alter_profiles_table_columns', $columns );
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @since 1.0.0
	 */
	public function getSortableColumns() {
		 $sortable_columns = array();
		 return $sortable_columns;
	}

	/**
	 * Function to get changes in html
	 *
	 * @since 1.0.0
	 */
	public function renderHTML() {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		?>
		<div class="ced_fb_wrap ced_fb_wrap_extn">
				<div class="ced_fb_setting_header ">
					<b class="manage_labels"><?php esc_html_e( 'FACEBOOK PROFILES', 'woocommerce-fb-integration' ); ?></b>
				</div>          
			<div>

				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">

								<?php
								wp_nonce_field( 'fb_profiles', 'fb_profiles_actions' );
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

			if ( ! isset( $_POST['fb_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fb_profiles_actions'] ) ), 'fb_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action2'] ) ) {

			if ( ! isset( $_POST['fb_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fb_profiles_actions'] ) ), 'fb_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';
			return $action;
		}
	}

	/**
	 * Function for processing bulk actions
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_action() {
		if ( isset( $_GET['panel'] ) && 'delete' == $_GET['panel'] ) {
			$profile_id = isset( $_GET['profileID'] ) ? sanitize_text_field( wp_unslash( $_GET['profileID'] ) ) : '';
				global $wpdb;

				$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';

					$product_ids_assigned = get_option( 'ced_fb_product_ids_in_profile_' . $profile_id, array() );
			foreach ( $product_ids_assigned as $index => $id ) {
				delete_post_meta( $id, 'ced_fb_profile_assigned' . $shop_id );
			}

					$term_id = $wpdb->get_results( $wpdb->prepare( ' SELECT `woo_categories` FROM wp_ced_fb_profiles WHERE `id` = %d ', $profile_id ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
			foreach ( $term_id as $key => $value ) {
				delete_term_meta( $value, 'ced_fb_profile_created_' . $shop_id );
				delete_term_meta( $value, 'ced_fb_profile_id_' . $shop_id );
				delete_term_meta( $value, 'ced_fb_mapped_category_' . $shop_id );
			}
					$delete_status = $wpdb->query( $wpdb->prepare( 'DELETE FROM wp_ced_fb_profiles WHERE `id`= %d ', $profile_id ) );

				$redirect_url = get_admin_url() . 'admin.php?page=ced_fb&section=profile-view&shop_id=' . $shop_id;
				wp_redirect( $redirect_url );
				exit;
		} elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {
			$file = CED_FMCW_DIRPATH . 'admin/partials/profile-edit-view.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
		}
	}
}

$ced_fb_profile_obj = new Ced_Facebook_Profile_Table();
$ced_fb_profile_obj->prepareItems();
