<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once CED_ETSY_DOKAN_DIRPATH . 'public/partials/header.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/template.php';
$GLOBALS['hook_suffix'] = '';

class Ced_Etsy_Profile_Table extends WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Etsy Profile', 'woocommerce-etsy-integration' ), // singular name of the listed records
				'plural'   => __( 'Etsy Profiles', 'woocommerce-etsy-integration' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	public function prepare_items() {

		global $wpdb;
		// $per_page = apply_filters( 'ced_etsy_profile_list_per_page', 10 );
		$per_page = 10;
		// print_r( get_included_files() );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::get_profiles( $per_page, $current_page );
		$count = self::get_count();
		if ( ! $this->current_action() ) {
			$this->items = self::get_profiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	public function get_profiles( $per_page = 10, $page_number = 1 ) {

		global $wpdb;
		$vendor_id = get_current_user_id();
		$de_shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash(  ced_etsy_filter($_GET['de_shop_name'] ) ) ) : '';
		$offset    = ( $page_number - 1 ) * $per_page;
		$tableName = $wpdb->prefix . 'ced_etsy_dokan_profiles';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `de_shop_name`= %s AND `vendor_id` = %d ORDER BY `id` DESC LIMIT %d OFFSET %d", $de_shop_name, $vendor_id , $per_page, $offset ), 'ARRAY_A' );
		return $result;

	}

	/**
	 * Function to count number of responses in result
	 */
	public function get_count() {
		$de_shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( ced_etsy_filter($_GET['de_shop_name'] ) ) ) : '';
		$vendor_id    = get_current_user_id();
		global $wpdb;
		$tableName = $wpdb->prefix . 'ced_etsy_dokan_profiles';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `de_shop_name`= %s AND `vendor_id`=%d", $de_shop_name, $vendor_id ), 'ARRAY_A' );
		return count( $result );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No Profiles Created.', 'woocommerce-etsy-integration' );
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
			'<input type="checkbox" name="etsy_profile_ids[]" value="%s" />',
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
		$de_shop_name       = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( ced_etsy_filter($_GET['de_shop_name'] ) ) ) : '';
		$title           = '<strong>' . $item['profile_name'] . '</strong>';
		$url             = admin_url( 'admin.php?page=ced_etsy&profileID=' . $item['id'] . '&section=profiles-view&panel=edit&de_shop_name=' . $de_shop_name );
		$actions      = [
			'edit' => '<a href="' . dokan_get_navigation_url( 'ced_etsy/profile-edit-view' ) . '?panel=edit&profileID=' . $item['id'] . '&de_shop_name=' . $de_shop_name . '">Edit</a>',
		];

		// $actions['delete'] = '<a href="javascript:void(0)" class="ced_etsy_delete_mapped_e_profiles" data-profileid="' . $item['id'] . '">Delete</a>';
		print_r( $title );
		return $this->row_actions( $actions, true );
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

	public function column_edit_profiles( $item ) {
		$de_shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( ced_etsy_filter($_GET['de_shop_name'] ) ) ) : '';
		$edit_url  = admin_url( 'admin.php?page=ced_etsy&profileID=' . $item['id'] . '&section=profiles-view&panel=edit&de_shop_name=' . $de_shop_name );
		echo "<a class='button-primary' href='" . esc_url( $edit_url ) . "'>Edit</a>";
	}

	public function column_auto_upload( $ced_etsy_profile_details ) {
		$woo_categories = json_decode( $ced_etsy_profile_details['woo_categories'], true );
		$de_shop_name      = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['de_shop_name'] ) ) : '';
		if ( ! empty( $woo_categories ) ) {

			$ced_etsy_dokan_auto_upload_categories = get_option( 'ced_etsy_dokan_auto_upload_categories_' . $de_shop_name, array() );
			$woo_category_ids                = array();
			foreach ( $woo_categories as $key => $value ) {
				$woo_category_ids[] = $value;
			}
			$checked = '';
			if ( isset( $woo_category_ids[0] ) && in_array( $woo_category_ids[0], $ced_etsy_dokan_auto_upload_categories ) ) {
				$checked = 'checked';
			}

				echo '<label class="switch"><input type="checkbox" value="' . json_encode( $woo_category_ids ) . '" id="ced_etsy_dokan_auto_upload_categories" ' . esc_attr( $checked ) . ' data-shop-name="' . esc_attr( $de_shop_name ) . '"><span class="slider round"></span>
			</label>';
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
			'profile_name'   => __( 'Profile Name', 'woocommerce-etsy-integration' ),
			'profile_status' => __( 'Profile Status', 'woocommerce-etsy-integration' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'woocommerce-etsy-integration' ),
			// 'auto_upload'    => __( 'Auto Upload Products', 'etsy-woocommerce-integration' ),
		);
		$columns = apply_filters( 'ced_etsy_alter_profiles_table_columns', $columns );
		return $columns;
	}

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
			echo '<option value="-1">' . esc_html( __( 'Bulk Actions' ) ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply' ), 'ced-dokan-btn', '', false, );
			echo "\n";
		endif;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'woocommerce-etsy-integration' ),
		);
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
	 * Function to get changes in html
	 */
	public function renderHTML() {
		$de_shop_name = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( ced_etsy_filter($_GET['de_shop_name'] ) ) ) : '';
		$url       = admin_url( 'admin.php?page=ced_etsy&section=profiles-view&panel=edit&de_shop_name=' . $de_shop_name );
		?>
		<div class="ced_etsy_heading">
		<?php echo esc_html_e( ced_etsy_dokan_get_etsy_instuctions_html() ); ?>
<div class="ced_etsy_child_element dokan-alert-warning">
		<?php
				$activeShop   = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( $_GET['de_shop_name'] ) : '';
				$profile_url  = admin_url( 'admin.php?page=ced_etsy&section=profiles-view&de_shop_name=' . $activeShop );
				$instructions = array(
					'In this section you will see all the profiles created after category mapping.',
					'You can use the <a>Profiles</a> in order to override the settings of <a>Product Export Settigs</a> in Global Settings at category level.' .
					'For overriding the details edit the required profile using the edit option under profile name.',
					'Also there are category specific attributes which you can fill.',
				);

				echo '<ul class="ced_etsy_instruction_list" type="disc">';
				foreach ( $instructions as $instruction ) {
					print_r( "<li>$instruction</li>" );
				}
				echo '</ul>';

				?>
</div>
</div>
		<div class="ced_etsy_wrap ced_etsy_wrap_extn">
					
			<div>
				
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'etsy_profiles', 'etsy_profiles_actions' );
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
			// die('2');
			if ( ! isset( $_POST['etsy_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['etsy_profiles_actions'] ) ), 'etsy_profiles' ) ) {
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

			if ( ! isset( $_POST['etsy_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['etsy_profiles_actions'] ) ), 'etsy_profiles' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$profileIds      = isset( $sanitized_array['etsy_profile_ids'] ) ? $sanitized_array['etsy_profile_ids'] : array();
			if ( is_array( $profileIds ) && ! empty( $profileIds ) ) {

				global $wpdb;

				$tableName = $wpdb->prefix . 'ced_etsy_dokan_profiles';

				$shop_id = isset( $_GET['de_shop_name'] ) ? sanitize_text_field( wp_unslash( ced_etsy_filter($_GET['de_shop_name'] ) ) ) : '';

				foreach ( $profileIds as $index => $pid ) {

					$product_ids_assigned = get_option( 'ced_etsy_product_ids_in_profile_' . $pid, array() );
					foreach ( $product_ids_assigned as $index => $ppid ) {
						delete_post_meta( $ppid, 'ced_etsy_dokan_profile_assigned' . $shop_id );
					}

					$term_id = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_etsy_dokan_profiles WHERE `id` = %d", $pid ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
					foreach ( $term_id as $key => $value ) {
						delete_term_meta( $value, 'ced_etsy_dokan_profile_created_' . $shop_id . '_' . get_current_user_id() );
						delete_term_meta( $value, 'ced_etsy_dokan_profile_id_' . strtolower($shop_id) . '_' . get_current_user_id() );
						delete_term_meta( $value, 'ced_etsy_dokan_profile_id_' . $shop_id . '_' . get_current_user_id() );
						delete_term_meta( $value, 'ced_etsy_dokan_mapped_category_' . $shop_id  . '_' . get_current_user_id() );
						// delete_term_meta( $value, 'ced_etsy_dokan_mapped_category_' . strtolower($shop_id) );
					}
				}

				foreach ( $profileIds as $id ) {
					$wpdb->delete( $tableName, array( 'id' => $id ) );
				}

				$redirectURL = dokan_get_navigation_url( 'ced_etsy/profiles-view' ) . '?de_shop_name=' . $shop_id;
				wp_redirect( $redirectURL );
			}
		} elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {

			require_once CED_ETSY_DOKAN_DIRPATH . 'public/partials/profile-edit-view.php';
		}
	}
}

$ced_etsy_profile_obj = new Ced_Etsy_Profile_Table();
$ced_etsy_profile_obj->prepare_items();
