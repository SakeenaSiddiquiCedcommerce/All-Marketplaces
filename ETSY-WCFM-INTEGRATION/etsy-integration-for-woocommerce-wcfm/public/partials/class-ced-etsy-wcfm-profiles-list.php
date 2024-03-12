<?php
/**
 * Display list of profiles
 *
 * @package  Woocommerce_Jumia_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
ced_etsy_wcfm_get_header();
// if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH.'wp-admin/includes/screen.php' );
	require_once(ABSPATH.'wp-admin/includes/class-wp-screen.php' );
	require_once(ABSPATH.'wp-admin/includes/template.php' );
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
// }
$GLOBALS['hook_suffix'] = '';
/**
 * Ced_Jumia_Profiles_List
 *
 * @since 1.0.0
 */
class Ced_Jumia_Profiles_List extends WP_List_Table {

	/**
	 * Ced_Jumia_Profiles_List construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => __( 'Jumia Profile', 'woocommerce-etsy-integration' ),
				'plural'   => __( 'Jumia Profiles', 'woocommerce-etsy-integration' ),
				'ajax'     => false,
			]
		);

	}

	/**
	 * Function for preparing profile data to be displayed column
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
		if( in_array( 'etsy', $enabled_marketplaces )  ) {

			global $wpdb;
			$per_page = apply_filters( 'ced_etsy_wcfm_profile_list_per_page', 30 );
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

			$this->items = self::ced_etsy_wcfm_get_profiles( $per_page, $current_page );

			$count = self::get_count();

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
	}

	/**
	 * Function for status column
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function ced_etsy_wcfm_get_profiles( $per_page = 10, $page_number = 1 ) {
		$shop_name = isset($_GET['shop_name']) ? sanitize_text_field( $_GET['shop_name'] ) : '';
		$result = get_option( 'ced_etsy_wcfm_profile_details' . $shop_name, array() );
		return isset( $result['ced_etsy_wcfm_profile_details'] ) ? array_reverse( $result['ced_etsy_wcfm_profile_details'] ) : array();
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 */
	public function get_count() {
		$shop_name = isset($_GET['shop_name']) ? sanitize_text_field( $_GET['shop_name'] ) : '';
		$result = get_option( 'ced_etsy_wcfm_profile_details'.$shop_name, array() );
		$result = isset( $result['ced_etsy_wcfm_profile_details'] ) ? $result['ced_etsy_wcfm_profile_details'] : array();
		return count( $result );
	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Profiles Created.', 'woocommerce-etsy-integration' );
	}

	/**
	 * Function for checkboxes
	 *
	 * @since 1.0.0
	 * @param array $ced_etsy_wcfm_profile_details Profile Data.
	 */
	public function column_cb( $ced_etsy_wcfm_profile_details ) {
		$profile_id = isset( $ced_etsy_wcfm_profile_details['profile_id'] ) ? $ced_etsy_wcfm_profile_details['profile_id'] : '';
		return sprintf(
			'<input type="checkbox" class="etsy_wcfm_profile_id" name="etsy_wcfm_profile_ids[]" value="%s" />',
			$profile_id
		);
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $ced_etsy_wcfm_profile_details Profile Data.
	 */
	public function column_profile_name( $ced_etsy_wcfm_profile_details ) {
		$title        = '<strong>' . $ced_etsy_wcfm_profile_details['profile_name'] . '</strong>';
		$shop_name      = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$actions      = [
			'edit' => sprintf( '<a href="?section=%s&shop_name=%s&profile_id=%s&panel=edit">Edit</a>', 'profiles', $shop_name, $ced_etsy_wcfm_profile_details['profile_id'] ),
		];
		return $title . $this->row_actions( $actions );
		return $title;
	}

	/**
	 * Function for category column
	 *
	 * @since 1.0.0
	 * @param array $ced_etsy_wcfm_profile_details Profile Data.
	 */
	public function column_woo_categories( $ced_etsy_wcfm_profile_details ) {
		$woo_categories = json_decode( $ced_etsy_wcfm_profile_details['woo_categories'], true );

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
		$columns = [
			'cb'             => '<input type="checkbox">',
			'profile_name'   => __( 'Profile Name', 'woocommerce-etsy-integration' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'woocommerce-etsy-integration' ),
		];
		$columns = apply_filters( 'ced_etsy_wcfm_alter_profiles_table_columns', $columns );
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
	 * Returns an associative array containing the bulk action
	 *
	 * @since 1.0.0
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => __( 'Delete', 'woocommerce-etsy-integration' ),
		];

		return $actions;
	}

	/**
	 * Function to get changes in html
	 *
	 * @since 1.0.0
	 */
	public function render_html() {
		$shop_name = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		?>
		<div class="ced_etsy_wcfm_wrap ced_etsy_wcfm_wrap_extn ced_etsy_wcfm_profiles_wrapper">		
			<div>

				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">

								<?php
								wp_nonce_field( 'etsy_wcfm_profiles', 'etsy_wcfm_profiles_actions' );
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

			if ( ! isset( $_POST['etsy_wcfm_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['etsy_wcfm_profiles_actions'] ) ), 'etsy_wcfm_profiles' ) ) {
				return;
			}

			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action2'] ) ) {

			if ( ! isset( $_POST['etsy_wcfm_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['etsy_wcfm_profiles_actions'] ) ), 'etsy_wcfm_profiles' ) ) {
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
		if ( isset( $_POST['action'] ) && 'bulk-delete' == $_POST['action'] || isset( $_POST['action2'] ) && 'bulk-delete' == $_POST['action2'] ) {
			$shop_name                    = isset( $_GET['shop_name'] ) ? $_GET['shop_name'] : '';
			$profile_ids                = isset( $_POST['etsy_wcfm_profile_ids'] ) ? $_POST['etsy_wcfm_profile_ids'] : array();
			$ced_etsy_wcfm_profile_details = get_option( 'ced_etsy_wcfm_profile_details' . $shop_name, array() );
			foreach ( $profile_ids as $index => $profile_id ) {
				$term_ids = isset( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ]['woo_categories'] ) ? json_decode( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ]['woo_categories'], true ) : array();
				if ( is_array( $term_ids ) ) {
					foreach ( $term_ids as $index => $term_id ) {
						delete_term_meta( $term_id, 'ced_etsy_wcfm_profile_created_' . $shop_name );
						delete_term_meta( $term_id, 'ced_etsy_wcfm_profile_id_' . $shop_name );
						delete_term_meta( $term_id, 'ced_etsy_wcfm_mapped_category_' . $shop_name );
					}
				}
				unset( $ced_etsy_wcfm_profile_details['ced_etsy_wcfm_profile_details'][ $profile_id ] );
				update_option( 'ced_etsy_wcfm_profile_details' .$shop_name, $ced_etsy_wcfm_profile_details );
			}
			$redirect_url = get_wcfm_url() . 'ced-etsy?section=profiles&shop_name=' . $shop_name;
			wp_redirect( $redirect_url );
			exit;
		} elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {
			$file = CED_ETSY_WCFM_DIRPATH . 'public/partials/ced-etsy-wcfm-profile-edit.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
		}
	}
}

$ced_etsy_wcfm_profile_obj = new Ced_Jumia_Profiles_List();
$ced_etsy_wcfm_profile_obj->prepare_items();
