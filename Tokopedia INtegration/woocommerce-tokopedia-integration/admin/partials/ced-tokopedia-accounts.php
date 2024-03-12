<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! session_id() && ! headers_sent() ) {
	session_start();

}
class Ced_Tokopedia_Account_Table extends WP_List_Table {
	/**
	 * Account table constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'tokopedia Account', 'woocommerce-tokopedia-integration' ),
				'plural'   => __( 'tokopedia Accounts', 'woocommerce-tokopedia-integration' ),
				'ajax'     => false,
			)
		);
	}

	public function prepare_items() {
		global $wpdb;
		
		$per_page = apply_filters( 'ced_tokopedia_account_list_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		$count = self::get_count();
		if ( ! $this->current_action() ) {

			$this->set_pagination_args(
				array(
					'total_items' => $count,
					'per_page'    => $per_page,
					'total_pages' => ceil( $count / $per_page ),
				)
			);

			$accounts = array();
			$accounts = self::get_accounts( $per_page, $current_page );

			if ( ! empty( $accounts ) ) {
				$this->items = $accounts;
			}
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}
	/**
	 * To get account information from Database.
	 *
	 * @param integer $per_page for offset .
	 * @param integer $page_number this is current page no.
	 * @return array
	 */
	public function get_accounts( $per_page = 10, $page_number = 1 ) {
		global $wpdb;
		$table      = $wpdb->prefix . 'ced_tokopedia_accounts';
		$table_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '. $table ), 'ARRAY_A' );
		return $table_data;
	}

	/**
	 * Function to count number of responses in result
	 */
	public function get_count() {

		global $wpdb;
		$table      = $wpdb->prefix . 'ced_tokopedia_accounts';
		$table_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '. $table ), 'ARRAY_A' );
		return count( $table_data );

	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No Accounts Linked.', 'woocommerce-tokopedia-integration' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
			echo ' <input type="checkbox" value="' . esc_attr( $item['name'] ) . '" name=tokopedia_account_name[]" > ';
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		echo '<b>' . esc_attr( $item['name'] ) . '</b>';
	}
	public function column_userid( $item ) {
		$shopData = json_decode( $item['shop_data'], true );
		echo esc_attr( $shopData['user_id'] );
	}
	public function column_fsid( $item ) {
		$shopData = json_decode( $item['shop_data'], true );
		echo esc_attr( $shopData['fsid'] );

	}
	public function column_account_status( $item ) {
		echo esc_attr( $item['account_status'] );
	}
	public function column_configure( $item ) {

		$configure = '<a class="button" href="' . admin_url( 'admin.php?page=ced_tokopedia&section=global-settings-view&shop_name=' . $item['shop_id'] . '' ) . '">' . __( 'Configure', 'woocommerce-tokopedia-integration' ) . '</a>';
		return $configure;
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'name'           => __( 'Shop Name', 'woocommerce-tokopedia-integration' ),
			'userid'         => __( 'Shop User ID', 'woocommerce-tokopedia-integration' ),
			'fsid'           => __( 'FSID', 'woocommerce-tokopedia-integration' ),
			'account_status' => __( 'Account Status', 'woocommerce-tokopedia-integration' ),
			'configure'      => __( 'Configure', 'woocommerce_tokopedia-integration' ),
		);
		$columns = apply_filters( 'ced_tokopedia_alter_feed_table_columns', $columns );
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

			'bulk-enable'  => 'Enable',
			'bulk-disable' => 'Disable',
			'bulk-delete'  => 'Delete',
		);
		return $actions;
	}

	/**
	 * Function to get changes in html
	 */
	public function renderHTML() {

		?>
		<div class="ced_tokopedia_wrap ced_tokopedia_wrap_extn">
			<div class="ced_tokopedia_setting_header">
				<label class="manage_labels"><b><?php esc_html_e( 'TOKOPEDIA ACCOUNT', 'woocommerce-tokopedia-integration' ); ?></b></label>
				<?php
				$count = self::get_count();
				if ( $count < 1 ) {
					echo '<a href="javascript:void(0)" class="ced_tokopedia_add_account_button ced_tokopedia_add_button">Add Account</a>';
				}
				?>
			</div>
			<?php esc_attr( display_tokopedia_support_html() ); ?>
			<div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'tokopedia_accounts', 'tokopedia_accounts_actions' );
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
		<div class="ced_tokopedia_add_account_popup_main_wrapper">
			<div class="ced_tokopedia_add_account_popup_content">
				<div class="ced_tokopedia_add_account_popup_header">
					<h5><?php esc_html_e( 'Authorise your Tokopedia Account', 'woocommerce-tokopedia-integration' ); ?></h5>
					<span class="ced_tokopedia_add_account_popup_close">X</span>
				</div>
				<div class="ced_tokopedia_add_account_popup_body">
					<h6>Steps to authorise your account:</h6>
					<ul>
						<li>Enter Shop name and Username.</li>
						<li>On the tokopedia authorization page you have to log in with your developer login details.</li>
						<li>You have to then click on "Allow Access" button to enable access to API.</li>
					</ul>
					<form action="" method="post">
						<?php
						wp_nonce_field( 'tokopedia_accounts', 'tokopedia_accounts_actions' );
						?>
						<div class="ced_tokopedia_popup_wrap">
							<div class="ced_tokopedia_popup_container">
								<div class="ced_tokopedia_popup_label"><label>Enter Tokopedia User Id </label></br><span><a class="get_tokopedia_sop_name" href="https://www.tokopedia.com/login" target="#">[ Get User Id -> ]</a></span></div>
								<div class="ced_tokopedia_popup_input"><input id="ced_tokopedia_user_id" type="text" name="ced_tokopedia_user_id" placeholder="Enter tokopedia User ID or Email " required=""></div>
							</div>
							<div class="ced_tokopedia_popup_container">
								<div class="ced_tokopedia_popup_label"><label>Enter Tokopedia Shop Id</label></br><span><a class="get_tokopedia_sop_name" href="https://www.tokopedia.com/login" target="#">[ Get Shop Id -> ]</a></span></div>
								<div class="ced_tokopedia_popup_input"><input id="ced_tokopedia_shop_id" type="text" name="ced_tokopedia_shop_id" placeholder="Enter shop id (Number) " required=""></div>
							</div>
							<div class="ced_tokopedia_popup_container">
								<div class="ced_tokopedia_popup_label"><label>Enter Tokopedia Shop name</label></br><span><a class="get_tokopedia_sop_name" href="https://www.tokopedia.com/login" target="#">[ Get Shop name -> ]</a></span></div>
								<div class="ced_tokopedia_popup_input"><input id="ced_tokopedia_shop_name" type="text" name="ced_tokopedia_shop_name" placeholder="Enter Tokopedia Shop Name " required=""></div>
							</div>
								<div class="ced_tokopedia_popup_container">
								<div class="ced_tokopedia_popup_label"><label>Enter Tokopedia FSID</label></br><span><a class="get_tokopedia_sop_name" href="https://www.tokopedia.com/login" target="#">[ Get FSID -> ]</a></span></div>
								<div class="ced_tokopedia_popup_input"><input id="ced_tokopedia_shop_fs_id" type="text" name="ced_tokopedia_shop_fs_id" placeholder="Enter FSID ( App id )" required=""></div>
							</div>
								<div class="ced_tokopedia_popup_container">
								<div class="ced_tokopedia_popup_label"><label>Enter Tokopedia Client Id</label></br><span><a class="get_tokopedia_sop_name" href="https://www.tokopedia.com/login" target="#">[ Get Client Id -> ]</a></span></div>
								<div class="ced_tokopedia_popup_input"><input id="ced_tokopedia_client_id" type="text" name="ced_tokopedia_client_id" placeholder="Enter Client ID ( App Client ID )" required=""></div>
							</div>
								<div class="ced_tokopedia_popup_container">
								<div class="ced_tokopedia_popup_label"><label>Enter Tokopedia Client Secret </label></br><span><a class="get_tokopedia_sop_name" href="https://www.tokopedia.com/login" target="#">[ Get Client Secrete -> ]</a></span></div>
								<div class="ced_tokopedia_popup_input"><input id="ced_tokopedia_client_scret" type="text" name="ced_tokopedia_client_scret" placeholder="Enter Client Secret ( App Secrete ID )"  required=""></div>
							</div>
						</div>
						<div class="ced_tokopedia_add_account_button_wrapper">
							<input type="submit" value="Authorise" id="ced_tokopedia_authorise_account_button" name="ced_tokopedia_authorise_account_button" class="ced_tokopedia_add_button">
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
	public function current_action() {

		if ( isset( $_GET['section'] ) ) {
			$action = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
			if ( ! isset( $_POST['tokopedia_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tokopedia_accounts_actions'] ) ), 'tokopedia_accounts' ) ) {
				return;
			}

			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		}
	}

	public function process_bulk_action() {
		global $wpdb;
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) ) {
			if ( ! isset( $_POST['tokopedia_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tokopedia_accounts_actions'] ) ), 'tokopedia_accounts' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$accountNames    = isset( $sanitized_array['tokopedia_account_name'] ) ? $sanitized_array['tokopedia_account_name'] : array();
			foreach ( $accountNames as $key => $value ) {
				$wpdb->query( $wpdb->prepare( ' DELETE FROM wp_ced_tokopedia_accounts WHERE name = %s ', $value ) );
			}
			$redirectURL = get_admin_url() . 'admin.php?page=ced_tokopedia';
			wp_redirect( $redirectURL );

		} elseif ( 'bulk-enable' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-enable' === $_GET['action'] ) ) {
			if ( ! isset( $_POST['tokopedia_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tokopedia_accounts_actions'] ) ), 'tokopedia_accounts' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

			$accountNames = isset( $sanitized_array['tokopedia_account_name'] ) ? $sanitized_array['tokopedia_account_name'] : array();

			foreach ( $accountNames as $key => $value ) {
				$table = $wpdb->prefix . 'ced_tokopedia_accounts';
				$wpdb->update(
					$table,
					array(
						'account_status' => 'Active',
					),
					array( 'name' => $value ),
					array(
						'%s',
					),
					array( '%d' )
				);
			}
			$redirectURL = get_admin_url() . 'admin.php?page=ced_tokopedia';
			wp_redirect( $redirectURL );
		} elseif ( 'bulk-disable' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-disable' === $_GET['action'] ) ) {
			if ( ! isset( $_POST['tokopedia_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tokopedia_accounts_actions'] ) ), 'tokopedia_accounts' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

			$accountNames = isset( $sanitized_array['tokopedia_account_name'] ) ? $sanitized_array['tokopedia_account_name'] : array();

			foreach ( $accountNames as $key => $value ) {
				$table = $wpdb->prefix . 'ced_tokopedia_accounts';
				$wpdb->update(
					$table,
					array(
						'account_status' => 'InActive',
					),
					array( 'name' => $value ),
					array(
						'%s',
					),
					array( '%d' )
				);
			}
			$redirectURL = get_admin_url() . 'admin.php?page=ced_tokopedia';
			wp_redirect( $redirectURL );

		} elseif ( isset( $_GET['section'] ) ) {

			require_once CED_TOKOPEDIA_DIRPATH . 'admin/partials/' . $this->current_action() . '.php';
		}
	}
}

if ( isset( $_POST['ced_tokopedia_authorise_account_button'] ) && 'Authorise' == $_POST['ced_tokopedia_authorise_account_button'] ) {
	if ( ! isset( $_POST['tokopedia_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tokopedia_accounts_actions'] ) ), 'tokopedia_accounts' ) ) {
		return;
	}

	$requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
	if ( file_exists( $requrest_tokopedia_file ) ) {
		require_once $requrest_tokopedia_file;
	}
	$obj_request = new tokopediaRequest();
	global $wpdb;

	$UserId       = isset( $_POST['ced_tokopedia_user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_user_id'] ) ) : '';
	$ShopId       = isset( $_POST['ced_tokopedia_shop_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_shop_id'] ) ) : '';
	$ShopName     = isset( $_POST['ced_tokopedia_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_shop_name'] ) ) : '';
	$ShopFsid     = isset( $_POST['ced_tokopedia_shop_fs_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_shop_fs_id'] ) ) : '';
	$ClientId     = isset( $_POST['ced_tokopedia_client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_client_id'] ) ) : '';
	$ClientSecret = isset( $_POST['ced_tokopedia_client_scret'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_tokopedia_client_scret'] ) ) : '';
	$access_token = $obj_request->sendCurlGetMethodForAcesssToken( 'ced_tokopedia_get_access_token', $ClientId, $ClientSecret );
	
	if (!empty( $access_token ) ) {
		$access_token = json_decode( $access_token , true );
		$token        = isset( $access_token['access_token'] ) ? $access_token['access_token'] :'';
	}

	$etalase      = $obj_request->sendCurlGetMethod( 'get_etalase_ids', $ShopId );
	$etalase_data = isset( $etalase['data']['etalase'] ) ? $etalase['data']['etalase'] : '';

	if ( !empty( $token ) ) {
		   $shop_data  = array();
		   $shop_data  = array(
			   'user_id'       => $UserId,
			   'fsid'          => $ShopFsid,
			   'client_id'     => $ClientId,
			   'client_secret' => $ClientSecret,
			   'etalase_data'  => $etalase_data,
		   );
		   $shop_data  = json_encode( $shop_data );
		   $table      = $wpdb->prefix . 'ced_tokopedia_accounts';
		   $table_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '. $table ), 'ARRAY_A' );
		   $table_data = json_decode( json_encode( $table_data, true ) );
		   $data = array(
			   'name'           => $ShopName,
			   'account_status' => 'Active',
			   'shop_id'        => $ShopId,
			   'shop_data'      => $shop_data,
			   'access_token'   => $token,
		   );
		   $wpdb->insert( $table, $data );
		   $my_id = $wpdb->insert_id;
	}
}
$ced_tokopedia_account_obj = new Ced_Tokopedia_Account_Table();
$ced_tokopedia_account_obj->prepare_items();
