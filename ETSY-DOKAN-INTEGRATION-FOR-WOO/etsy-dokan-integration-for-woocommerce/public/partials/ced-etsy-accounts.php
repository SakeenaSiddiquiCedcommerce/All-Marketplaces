<?php
if ( isset( $_POST['ced_etsy_dokan_authorise_account_button'] ) && 'Save & Authorize' == $_POST['ced_etsy_dokan_authorise_account_button'] ) {
	if ( ! isset( $_POST['dokan_etsy_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dokan_etsy_accounts_actions'] ) ), 'ced_dokan_etsy_accounts' ) ) {
		return;
	}

	$shop_name      = isset( $_POST['ced_etsy_dokan_de_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_etsy_dokan_de_shop_name'] ) ) : '';
	update_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id(), $shop_name );
	$scopes = array(
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
	$string         = bin2hex( random_bytes( 32 ) );
	$verifier       = base64_encode( dokan_get_navigation_url( 'ced_etsy' ) );
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
	$auth_url = "https://www.etsy.com/oauth/connect?response_type=code&redirect_uri=$redirect_uri&scope=$scopes&client_id=$client_id&state=$verifier&code_challenge=$code_challenge&code_challenge_method=S256";
	wp_redirect( $auth_url );

}

if ( str_contains( $_SERVER['REQUEST_URI'], 'state' ) || str_contains( $_SERVER['REQUEST_URI'], 'code' ) ) {
	$valid_get_url = str_replace('/&', '&', $_SERVER['REQUEST_URI'] );
	$code_state    = explode( '&', $valid_get_url);
	$auth_state    = str_replace('state=', '', $code_state[1] );
	$auth_code     = substr( str_replace('code=', '', $code_state[2] ), 0, -1);
	$verifier      = base64_encode( dokan_get_navigation_url( 'ced_etsy' ) );
	if (!empty($auth_state) && !empty($auth_code)) {
		$query_args = array(
			'grant_type'    => 'authorization_code',
			'client_id'     => 'ghvcvauxf2taqidkdx2sw4g4',
			'redirect_uri'  => 'https://woodemo.cedcommerce.com/woocommerce/authorize/etsy/authorize.php',
			'code'          => $auth_code,
			'code_verifier' => $verifier,
		);
		$parameters = $query_args;
		$shop_name  = get_option( 'ced_etsy_dokan_de_shop_name_' . get_current_user_id(), '' );
		$response   = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( $shop_name, 'public/oauth/token', 'POST', $parameters, $query_args );
		if ( isset( $response['access_token'] ) && ! empty( $response['access_token'] ) ) {
			$query_args = array(
				'shop_name' => $shop_name,
			);
			$shop       = ced_etsy_dokan_request()->ced_etsy_dokan_remote_request( '', 'application/shops', 'GET', $query_args );
			if ( isset( $shop['results'][0] ) ) {

				set_transient( 'ced_etsy_dokan_token_' . $shop_name . get_current_user_id(), $response, (int) $response['expires_in'] );
				$user_details               = get_option( 'ced_etsy_dokan_details', array() );
				$user_id                    = isset( $shop['results'][0]['user_id'] ) ? $shop['results'][0]['user_id'] : '';
				$user_name                  = isset( $shop['results'][0]['login_name'] ) ? $shop['results'][0]['login_name'] : '';
				$shop_id                    = isset( $shop['results'][0]['shop_id'] ) ? $shop['results'][0]['shop_id'] : '';
				$info                       = array(
					'details' => array(
						'ced_etsy_shop_name'      => $shop_name,
						'user_id'                 => $user_id,
						'user_name'               => $user_name,
						'shop_id'                 => $shop_id,
						'ced_etsy_keystring'      => ced_etsy_dokan_request()->client_id,
						'ced_etsy_shared_string'  => ced_etsy_dokan_request()->client_secret,
						'ced_shop_account_status' => 'Active',
						'token'                   => $response,
						'shop_info'               => $shop,
					),
				);
				$user_details[get_current_user_id()][ $shop_name ] = $info;

				if ( count( $user_details ) < 2 ) {
					update_option( 'ced_etsy_dokan_details', $user_details );
				}
			}
		}
	}
	wp_redirect( dokan_get_navigation_url( 'ced_etsy' ) );
}
$dokan_account = get_option('ced_etsy_dokan_details' , array() );
if( @count($dokan_account[get_current_user_id()]) < 1 ) {
	?>
<div class="ced-etsy-dokan-wrapper">
	<tbody>
		<tr>
			<td>
				<input type="button" name="" value="Add Account" class="button-primary ced-dokan-btn" id="ced_etsy_dokan_add_account_button">
			</td>
		</tr>
	</tbody>
</div>
<?php
}
?>
<form method="post" action="">
	<?php wp_nonce_field( 'ced_dokan_etsy_accounts', 'dokan_etsy_accounts_actions' ); ?>
<div class="ced-etsy-add-account-wrapper">	
	<table class="ced-etsy-table wp-list-table widefat stripped">
		<tr>
			<td><?php esc_html_e( 'Enter etsy shop name' , 'etsy-integration-for-woocommerce-dokan' ); ?></br><span><a class="get_etsy_sop_name" href="https://www.etsy.com/your/shops/me?ref=seller-platform-mcnav" target="#">[ Get Shop Name -> ]</a></span></td>
			<td><input type="text" name="ced_etsy_dokan_de_shop_name" id="ced_etsy_dokan_de_shop_name" required=""></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" class="ced-dokan-btn" name="ced_etsy_dokan_authorise_account_button" value="Save & Authorize"></td>
		</tr>
	</table>
</div>
</form>
<div class="ced-etsy-account-list">
	<table class="ced-etsy-table wp-list-table widefat stripped">
		<?php 
		echo "<div class='ced-etsy-account-header'>Etsy Account</div>";
		if( isset($dokan_account[get_current_user_id()]) ) {
			echo "<tr>";
			echo "<th>Etsy shop name</th>";
			echo "<th>User ID</th>";
			echo "<th>User name</th>";
			echo "<th>Configure</th>";
			echo "<th>Delete</th>";
			echo "</tr>";
			foreach ($dokan_account[get_current_user_id()] as $shop_name => $shop_data) {
			echo "<tr>";
			echo "<td>$shop_name</td>";
			echo "<td>".$shop_data['details']['user_name']."</td>";
			echo "<td>".$shop_data['details']['user_id']."</td>";
			echo "<td><button class='ced-dokan-btn'><a href='".dokan_get_navigation_url() . 'ced_etsy/ced-etsy-settings?de_shop_name=' . $shop_name."' class='button-primary'>Configure</a></button></td>";
			echo "<td><a id='ced_etsy_dokan_delete_account' href='javascript:void(0)' data-shop='".$shop_name."'>Delete</a></td>";
			echo "</tr>";
		}
		}
		?>
	</table>
</div>