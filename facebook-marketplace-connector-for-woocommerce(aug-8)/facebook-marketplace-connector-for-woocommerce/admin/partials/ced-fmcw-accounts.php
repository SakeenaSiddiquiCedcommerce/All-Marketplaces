<?php
/**
 * Facebook Account Connection Section
 *
 * @package  Facebook_Marketplace_Connector_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$fb_account_details = get_option( 'ced_fmcw_fb_account_details', array() );
$fb_shop_id         = get_option( 'ced_fmcw_fb_shop_id', '' );

$fileName = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-store-api-handle.php';
require_once $fileName;

$store_api_handler = Class_Ced_Fmcw_Store_Api_Handle::get_instance();

$cms_settings = $store_api_handler->get_cms_settings();
update_option( 'ced_fmcw_fb_cms_settings', $cms_settings );
$uploadDir = wp_upload_dir()['basedir'] . '/ced-facebook-logs';
if ( ! is_dir( $uploadDir ) ) {
	mkdir( $uploadDir, 0777, true );
}
$uploadDirPath = wp_upload_dir()['basedir'] . '/ced-facebook-logs/cmsError.txt';
$file          = fopen( $uploadDirPath, 'w' );
fwrite( $file, json_encode( $cms_settings ) );
fclose( $file );
// print_r( $cms_settings );
// $page_data = get_option('ced_fmcw_auth_post_data','');
// //print_r($page_data);
// $jwt = $page_data['data'];
// $registration_data = get_option('ced_fmcw_registered_with_cedcommerce', array());
// $publicKey    = isset($registration_data['reg_public_key']) ? $registration_data['reg_public_key'] : '';
// require CED_FMCW_DIRPATH . 'admin/lib/jwt/vendor/autoload.php';
// use \Firebase\JWT\JWT;
// $publicKey = base64_decode($publicKey);
// $decoded = JWT::decode($jwt, $publicKey, array('RS256'));
// //print_r($decoded);
// //if($decoded->fbe_data)
// update_option('ced_fmcw_fbe_data',$decoded);
$fbe_data = get_option( 'ced_fmcw_fbe_data' );
// print_r($fbe_data->data);
// $decoded = JWT::decode($jwt, $publicKey, array('RS256'));
// print_r($decoded);die("44");
$true_data = $fbe_data->data->fbe_data;
print_r( $true_data );
$configure_cms_requried = true;
if ( isset( $cms_settings['success'] ) && $cms_settings['success'] ) {
	if ( isset( $cms_settings['data'] ) && empty( $cms_settings['data'] ) ) {
		$configure_cms_requried = true;
	} else {
		$configure_cms_requried = false;
	}
}

$authenticated_pages  = get_option( 'ced_fmcw_merchant_page_authenticated', array() );
$alreadyAuthenticated = 0;
$instagram_channel    = false;
if ( empty( $authenticated_pages ) ) {
	$authenticated_pages = array();
}
?>
<div class="ced-fmcw-wrapper">
	<div class="ced-fmcw-commerce-page-settings-wrapper">
		<div class="ced-fmcw-heading-wrapper">
			<h2><?php esc_attr_e( 'Commerce Merchant Pages', 'facebook-marketplace-connector-for-woocommerce' ); ?></h2>
			<a href="#" class="ced-fmcw-button ced-fmcw-resync-page-button"><?php esc_attr_e( 'Re-Sync Pages', 'facebook-marketplace-connector-for-woocommerce' ); ?></a>
		</div>
		<div class="ced-fmcw-commerce-pages-wrapper">
			<div class="ced-fmcw-create-commerce-page-wrapper">
				<label>
					<?php esc_attr_e( 'Create your CMS Page from here', 'facebook-marketplace-connector-for-woocommerce' ); ?>
				</label>
				<a href="https://facebook.com/commerce_manager" target="_blank" class="ced-fmcw-button ced-fmcw-create-cms-page-button" id="ced-fmcw-create-cms-page" ><?php esc_attr_e( 'Open Facebook CMS Page', 'facebook-marketplace-connector-for-woocommerce' ); ?></a>

			</div>
			<?php
			if ( ! empty( $cms_settings['data'] ) ) {
				?>
				<div class="ced-fmcw-configure-commerce-page-wrapper">
					<?php
					foreach ( $cms_settings['data'] as $key => $data ) {
						$found_error          = 0;
						$merchant_page_errors = isset( $data['errors'] ) ? $data['errors'] : array();
						?>
						<div class="ced-fmcw-fb-page-wrapper">
							<div class="ced-fmcw-fb-page-wrapper-wrap">
								<label><?php echo esc_attr( $data['merchant_page']['name'] ); ?></label>
								<?php
								if ( isset( $data['order_management_apps'] ) && 'CedCommerce' == $data['order_management_apps'][0]['name'] ) {
									$alreadyAuthenticated = 1;
									?>
									<em class="ced-fmcw-authenticated-success-msg"><?php esc_attr_e( 'The Page is Authenticated by: CedCommerce', 'facebook-marketplace-connector-for-wocommerce' ); ?></em>
									<?php
								}
								if ( ! empty( $merchant_page_errors ) && isset( $merchant_page_errors[0] ) ) {
									foreach ( $merchant_page_errors as $key => $page_error ) {
										if ( isset( $page_error['action'] ) && 'setup_shop' == $page_error['action'] ) {
											$found_error = 1;
											?>
											<em class="ced-fmcw-cms-page-error-msg"><?php echo esc_attr( $page_error['display_message'] ); ?> </em>
											<?php
											break;
										}
									}
								}
								?>
							</div>
							<input type="hidden" value="<?php echo esc_attr( $data['merchant_page']['id'] ); ?>">
							<?php
							if ( in_array( $data['id'], $authenticated_pages ) || ( isset( $data['order_management_apps'] ) && 'CedCommerce' == $data['order_management_apps'][0]['name'] ) ) {
								if ( ! $found_error ) {
									$authenticated_pages[]              = $data['id'];
									$catalog_and_page_id                = get_option( 'ced_fmcw_catalog_and_page_id', array() );
									$catalog_and_page_id[ $data['id'] ] = array(
										'page_id'    => $data['merchant_page']['id'],
										'catalog_id' => $data['product_catalogs'][0]['id'],
									);
									update_option( 'ced_fmcw_catalog_and_page_id', $catalog_and_page_id );
									update_option( 'ced_fmcw_merchant_page_authenticated', array_unique( $authenticated_pages ) );
								}

								?>
								<input type="button" disabled value="<?php esc_attr_e( 'Authenticated', 'facebook-marketplace-connector-for-woocommerce' ); ?>" class="ced-fmcw-button ced-fmcw-fb-authenticate-button" data-page_id="<?php echo esc_attr( $data['merchant_page']['id'] ); ?>" data-catalog_id="<?php echo esc_attr( $data['product_catalogs'][0]['id'] ); ?>" data-merchant_page_id="<?php echo esc_attr( $data['id'] ); ?>">
								<?php
							} else {
								if ( empty( $merchant_page_errors ) ) {
									?>
									<input type="button" value="<?php esc_attr_e( 'Authenticate', 'facebook-marketplace-connector-for-woocommerce' ); ?>" class="ced-fmcw-button ced-fmcw-fb-authenticate-button" data-page_id="<?php echo esc_attr( $data['merchant_page']['id'] ); ?>" data-catalog_id="<?php echo esc_attr( $data['product_catalogs'][0]['id'] ); ?>" data-merchant_page_id="<?php echo esc_attr( $data['id'] ); ?>">
									<?php
								} else {
									?>
									<a href="https://www.facebook.com/commerce_manager/onboarding/" class="ced-fmcw-button ced-fmcw-fb-configure-button" data-merchant_page_id="<?php echo esc_attr( $data['id'] ); ?>"><?php esc_attr_e( 'Configure', 'facebook-marketplace-connector-for-woocommerce' ); ?></a>
									<?php
								}
							}
							// if( isset($data['instagram_channel']))
							// {
							// $instagram_channel = true;
							// }
							// else
							// {
							// echo "<span>Instagram Marketplace not activated. Please click here to activate your instagram account as well.</span>";
							// }
							?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			// var_dump($authenticated_pages);
			// var_dump($alreadyAuthenticated);

			?>
		</div>
		<?php
		if ( /*$instagram_channel &&*/ ( ! empty( $authenticated_pages ) || $alreadyAuthenticated ) ) {
			?>
				<div class="ced-fmcw-fb-page-wrapper ced-fmcw-move-to-next-step-wrapper">
					<input type="button" class="ced-fmcw-button ced-fmcw-next-step-button" id="ced-fmcw-next-step-button" value="<?php esc_attr_e( 'Next Step', 'facebook-marketplace-connector-for-woocommerce' ); ?>">
				</div>
				<?php
		}
			?>
			<a href="#" class="ced-fmcw-button ced-fmcw-configure-cms-button"><?php esc_attr_e( 'Configure', "facebook-marketplace-connector-for-woocommerce" ); ?></a>
			<?php
		?>
	</div>
</div>