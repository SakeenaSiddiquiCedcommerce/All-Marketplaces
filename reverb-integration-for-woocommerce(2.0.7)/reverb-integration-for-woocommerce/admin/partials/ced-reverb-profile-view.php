<?php
/**
 * Category Mapping
 *
 * @package  reverb_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
get_reverb_header();
$reverb_profiles = get_option( 'ced_reverb_profiles_list', array() );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Ced_Reverb_Profile_Table extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'reverb Profile', 'woocommerce-catch-integration' ), // singular name of the listed records
				'plural'   => __( 'reverb Profiles', 'woocommerce-catch-integration' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	/**
	 *
	 * Function for preparing profile data to be displayed column
	 */
	public function prepare_items() {

		global $wpdb;
		/**
 		* Filter hook for filtering the no. of profiles on profile page of the plugin.
 		* @since 1.0.0
 		*/
		$per_page = apply_filters( 'ced_reverb_profile_list_per_page', 10 );
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

		// Set the pagination
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
	/**
	 *
	 * Function for status column
	 */
	public function get_profiles( $per_page = 10, $page_number = 1 ) {
		$reverb_profiles = get_option( 'ced_reverb_profiles_list', true );
		if ( is_array( $reverb_profiles ) && ! empty( $reverb_profiles ) ) {
			$reverb_profiles = array_reverse( $reverb_profiles );
			return $reverb_profiles;
		}
	}

	/*
	*
	* Text displayed when no customer data is available
	*
	*/
	public function no_items() {
		esc_attr_e( 'No Profiles Created.', 'woocommerce-catch-integration' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		$reverb_profiles = get_option( 'ced_reverb_profiles_list', true );
		if ( is_array( $reverb_profiles ) && ! empty( $reverb_profiles ) ) {
			foreach ( $reverb_profiles as $key => $profile_data ) {
				if ( isset( $profile_data ) && $item == $profile_data ) {
					return sprintf(
						'<input type="checkbox" name="reverb_profile_ids[]" value="%s" />',
						$key
					);
				}
			}
		}
	}


	/**
	 * Function for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_reverb_cat_name( $item ) {

		$title           = '<strong>' . $item['reverb_cat_name'] . '</strong>';
		$reverb_profiles = get_option( 'ced_reverb_profiles_list', true );
		$page            = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( is_array( $reverb_profiles ) && ! empty( $reverb_profiles ) ) {
			foreach ( $reverb_profiles as $key => $profile_data ) {
				if ( isset( $profile_data ) && $item == $profile_data ) {
					 $actions = array(
						 'edit' => sprintf( '<a href="?page=%s&section=%s&profileID=%s&panel=edit">Edit</a>', esc_attr( $page ), 'profile_view', $key ),
					 );
				}
			}
		}
		return $title . $this->row_actions( $actions, true );
		return $title;
	}

	/**
	 *
	 * Function for category column
	 */
	public function column_woo_categories( $item ) {
		$woo_categories = $item['woo_categories'];
		$woo_cat        = '';
		if ( ! empty( $woo_categories ) ) {
			foreach ( $woo_categories as $key => $value ) {
				$term = get_term_by( 'id', $value, 'product_cat' );
				if ( $term ) {
					$woo_cat .= $term->name . ', ';
				}
			}
			$woo_cat = rtrim( $woo_cat, ', ' );
			echo esc_attr( $woo_cat );
		}
	}


	public function column_auto_upload( $ced_reverb_profile_details ) {
		$woo_categories = $ced_reverb_profile_details['woo_categories'];

		if ( ! empty( $woo_categories ) ) {

			$ced_reverb_auto_upload_categories = get_option( 'ced_reverb_auto_upload_categories', array() );
			$woo_category_ids                  = array();
			foreach ( $woo_categories as $key => $value ) {
				$woo_category_ids[] = $value;
			}

			$checked = '';
			if ( isset( $woo_category_ids[0] ) && in_array( $woo_category_ids[0], $ced_reverb_auto_upload_categories ) ) {
				$checked = 'checked';
			}
			$woo_category_ids = json_encode( $woo_category_ids );

				echo '<label class="switch"><input type="checkbox" value=' . esc_attr( $woo_category_ids ) . ' id="ced_reverb_auto_upload_categories" ' . esc_attr( $checked ) . '><span class="slider round"></span>
			</label>';
		}
	}

	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'reverb_cat_name' => __( 'Profile Name', 'woocommerce-reverb-integration' ),
			'woo_categories'  => __( 'Mapped WooCommerce Categories', 'woocommerce-reverb-integration' ),
			'auto_upload'     => __( 'Auto Upload Products', 'reverb-woocommerce-integration' ),
		);
		/**
 		* Filter hook for filtering columns on profile page of plugin.
 		* @since 1.0.0
 		*/
		$columns = apply_filters( 'ced_reverb_alter_profiles_table_columns', $columns );
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
		$actions = array(
			'bulk-delete' => __( 'Delete', 'woocommerce-reverb-integration' ),
		);
		return $actions;
	}


	/**
	 * Function to get changes in html
	 */
	public function renderHTML() {
		?>
		<div class="ced_reverb_heading">
					<?php echo esc_html_e( get_reverb_instuctions_html() ); ?>
					<div class="ced_reverb_child_element">
						<ul type="disc">
							<li><?php echo esc_html_e( 'In this section you will see all the profiles created after category mapping.' ); ?></li>
							<li><?php echo esc_html_e( 'You can use the Profiles in order to override the settings of Product Export Settigs in Global Settings at category level.For overriding the details edit the required profile using the edit option under profile name.' ); ?></li>
							<li><?php echo esc_html_e( 'Also there are miscellaneous attributes which you can fill.' ); ?></li>
						</ul>
					</div>
				</div>
		<div class="ced_reverb_wrap ced_reverb_wrap_extn">
				
			<div>
				<?php
				if ( ! session_id() ) {
					session_start();
				}

				?>
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
				<br class="clear">
			</div>
		</div>
		<?php
	}
	/**
	 *
	 * Function for getting current status
	 */
	public function current_action() {

		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['panel'] ) ? sanitize_text_field( $_GET['panel'] ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
				$nonce    = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$nonce_action = 'bulk-' . $this->_args['plural'];
			if ( wp_verify_nonce( $nonce, $nonce_action ) ) {
				$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
				return $action;
			}
		}
	}

	/**
	 *
	 * Function for processing bulk actions
	 */
	public function process_bulk_action() {

		if ( ! session_id() ) {
			session_start();
		}

		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) ) {
			$nonce        = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$nonce_action = 'bulk-' . $this->_args['plural'];
			if ( wp_verify_nonce( $nonce, $nonce_action ) ) {

				$reverb_profiles = get_option( 'ced_reverb_profiles_list', true );

				$profileIds = isset( $_POST['reverb_profile_ids'] ) ? array_map( 'sanitize_text_field', $_POST['reverb_profile_ids'] ) : array();

				if ( is_array( $profileIds ) && ! empty( $profileIds ) && is_array( $reverb_profiles ) && ! empty( $reverb_profiles ) ) {

					$profileData = get_option( 'ced_reverb_profile_data', array() );
					foreach ( $profileIds as $index => $pid ) {
						foreach ( $reverb_profiles as $key => $profile_data ) {
							if ( $key == $pid ) {
								foreach ( $profileData as $profile_key => $profile_value ) {
									if ( $profile_key == $profile_data['reverb_cat_id'] ) {
										unset( $profileData[ $profile_key ] );
									}
								}
								foreach ( $profile_data['woo_categories'] as $woo_key => $woo_cat ) {
									delete_term_meta( $woo_cat, 'ced_reverb_mapped_category', $profile_data['reverb_cat_id'] );
								}
								unset( $reverb_profiles[ $key ] );
								update_option( 'ced_reverb_profile_data', $profileData );
							}
						}
					}
					update_option( 'ced_reverb_profiles_list', $reverb_profiles );
					$redirectURL = get_admin_url() . 'admin.php?page=ced_reverb&section=profile_view';
					wp_redirect( $redirectURL );
				}
			}
		} elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {
			$file = plugin_dir_path( __FILE__ ) . '/ced-reverb-profile-edit-view.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}

$ced_reverb_profile_obj = new Ced_Reverb_Profile_Table();
$ced_reverb_profile_obj->prepare_items();
?>
