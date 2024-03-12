<?php
$etsy_wcfm_account_list = get_option( 'ced_etsy_wcfm_accounts' ,array() );
if( !empty($etsy_wcfm_account_list) ) {
	$etsy_wcfm_account_list = json_decode($etsy_wcfm_account_list,true);	
	// $etsy_wcfm_account_list = array();
}
/**
 *******************************************************
 *  Get state and code from back to Etsy authorisation.
 * *****************************************************
 */
if ( isset( $_GET['state'] ) && ! empty( $_GET['code'] ) ) {
	$code       = isset( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';
	$verifier   = isset( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';
	$action     = 'public/oauth/token';
	$query_args = array(
		'grant_type'    => 'authorization_code',
		'client_id'     => 'ghvcvauxf2taqidkdx2sw4g4',
		'redirect_uri'  => 'https://woodemo.cedcommerce.com/woocommerce/authorize/etsy/authorize.php',
		'code'          => $code,
		'code_verifier' => $verifier,
	);
	$parameters = $query_args;
	$shop_name  = get_option( 'ced_etsy_wcfm_shop_name_' .ced_etsy_wcfm_get_vendor_id(), '' );
	$response   = Ced_Etsy_WCFM_API_Request()->post( $action, $parameters, $shop_name, $query_args );

	if ( isset( $response['access_token'] ) && ! empty( $response['access_token'] ) ) {
		$action = 'application/shops';
		$query_args = array(
			'shop_name' => $shop_name,
		);
		$shop = Ced_Etsy_WCFM_API_Request( $shop_name, ced_etsy_wcfm_get_vendor_id() )->get( $action, '', $query_args );
		if ( isset( $shop['results'][0] ) ) {
			
			// Delete notice token Expired.
			delete_option( 'ced_etsy_wcfm_had_token_expired_' . ced_etsy_wcfm_get_vendor_id() . $shop_name );

			// Get Token expired transient
			set_transient( 'ced_etsy_wcfm_token_'. ced_etsy_wcfm_get_vendor_id() . $shop_name, $response, (int) $response['expires_in'] );
			$user_id                    = isset( $shop['results'][0]['user_id'] ) ? $shop['results'][0]['user_id'] : '';
			$user_name                  = isset( $shop['results'][0]['login_name'] ) ? $shop['results'][0]['login_name'] : '';
			$shop_id                    = isset( $shop['results'][0]['shop_id'] ) ? $shop['results'][0]['shop_id'] : '';
			$info                       = array(
				'details' => array(
					'ced_etsy_wcfm_shop_name' 	 => $shop_name,
					'user_id'                 	 => $user_id,
					'user_name'               	 => $user_name,
					'shop_id'                 	 => $shop_id,
					'ced_etsy_wcfm_keystring'      => Ced_Etsy_WCFM_API_Request()->client_id,
					'ced_etsy_wcfm_shared_string'  => Ced_Etsy_WCFM_API_Request()->client_secret,
					'ced_shop_account_status' => 'Active',
					'token'                   => $response,
					'shop_info'               => $shop['results'][0],
				),
			);

			require( $_SERVER['DOCUMENT_ROOT'] .'/wp-load.php' );
			$etsy_wcfm_account_list[ced_etsy_wcfm_get_vendor_id()][ $shop_name ] = $info;
			update_option( 'ced_etsy_wcfm_accounts', json_encode($etsy_wcfm_account_list) );		}
	}
	wp_redirect(get_wcfm_url() .'ced-etsy?&shop_name=' . $shop_name );
}

if ( isset( $_POST['ced_etsy_wcfm_authorize_account'] )  ) {
	$shop_name = isset( $_POST['ced_etsy_wcfm_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_wcfm_shop_name'] ) ) : '';
	$scopes    = array(
		'address_r',
		'address_w',
		'billing_r',
		'cart_r',
		'cart_w',
		'email_r',
		'favorites_r',
		'favorites_w',
		'feedback_r',
		'listings_d',
		'listings_r',
		'listings_w',
		'profile_r',
		'profile_w',
		'recommend_r',
		'recommend_w',
		'shops_r',
		'shops_w',
		'transactions_r',
		'transactions_w',
	);
	$verifier = base64_encode( get_wcfm_url() .'ced-etsy?' );
	$code_challenge = strtr(
		trim(
			base64_encode( pack( 'H*', hash( 'sha256', $verifier ) ) ),
			'='
		),
		'+/',
		'-_'
	);
	$scopes       = urlencode( implode( ' ', $scopes ) );
	$client_id    = 'ghvcvauxf2taqidkdx2sw4g4';
	$redirect_uri = 'https://woodemo.cedcommerce.com/woocommerce/authorize/etsy/authorize.php';
	update_option( 'ced_etsy_wcfm_shop_name_'. ced_etsy_wcfm_get_vendor_id(), $shop_name );
	$auth_url = "https://www.etsy.com/oauth/connect?response_type=code&redirect_uri=$redirect_uri&scope=$scopes&client_id=$client_id&state=$verifier&code_challenge=$code_challenge&code_challenge_method=S256";
	wp_redirect( $auth_url );
	exit;
}

if( !isset( $etsy_wcfm_account_list[ced_etsy_wcfm_get_vendor_id()] ) ) {
	?>
<div class="ced-etsy-wcfm-wrapper">
	<tbody>
		<tr>
			<td>
				<input type="button" name="" value="Add Account" class="button-primary ced-wcfm-btn" id="ced_etsy_wcfm_add_account">
			</td>
		</tr>
	</tbody>
</div>
<?php
}
?>
<form method="post" action="">
<div class="ced-etsy-add-account-wrapper">	
	<table class="ced-etsy-table wp-list-table widefat stripped">
		<tr>
			<td><?php esc_html_e( 'Enter etsy shop name' , 'etsy-integration-for-woocommerce-wcfm' ); ?></br><span><a class="get_etsy_sop_name" href="https://www.etsy.com/your/shops/me?ref=seller-platform-mcnav" target="#">[ Get Shop Name -> ]</a></span></td>
			<td><input type="text" name="ced_etsy_wcfm_shop_name" id="ced_etsy_wcfm_shop_name" required=""></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" class="ced-wcfm-btn" name="ced_etsy_wcfm_authorize_account" value="Save & Authorize"></td>
		</tr>
	</table>
</div>
</form>
<div class="ced-etsy-account-list">
	<table class="ced-etsy-table wp-list-table widefat stripped">
		<?php 
		echo "<div class='ced-etsy-account-header'>Etsy Account</div>";
		if(isset($etsy_wcfm_account_list[ced_etsy_wcfm_get_vendor_id()])) {
			echo "<tr>";
			echo "<th>Etsy shop name</th>";
			echo "<th>User ID</th>";
			echo "<th>User name</th>";
			echo "<th>Configure</th>";
			echo "<th>Delete</th>";
			echo "</tr>";
			foreach ($etsy_wcfm_account_list[ced_etsy_wcfm_get_vendor_id()] as $shop_name => $shop_data) {
				echo "<tr>";
				echo "<td>$shop_name</td>";
				echo "<td>".$shop_data['details']['user_name']."</td>";
				echo "<td>".$shop_data['details']['user_id']."</td>";
				echo "<td><button class='ced-wcfm-btn'><a href='".esc_url(get_wcfm_url() . 'ced-etsy?section=global-settings&shop_name=' . $shop_name)."' class='button-primary'>Configure</a></button></td>";
				echo "<td><a id='ced_etsy_wcfm_delete_account' href='javascript:void(0)' data-shop='".$shop_name."'>Delete</a></td>";
				echo "</tr>";
			}
		}

		?>
	</table>
</div>