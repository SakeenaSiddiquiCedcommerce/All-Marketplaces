<?php
/**
 * Header of the extensiom
 *
 * @package  Woocommerce_VidaXL_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( isset( $_GET['tab'] ) ) {
	$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
}else{
	$tab = '';
}
if ( isset( $_GET['page'] ) ) {
	$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
}
?>
<div class="ced-vidaxl-header">
	<h1 class="cedcommerce-text">
		VidaXL Dropshipping for WooCommerce
	</h1>
	<div class="cedcommerce-logo-div">
		<a href="https://cedcommerce.com/"><img src="<?php echo esc_url( CED_VIDAXL_DROPSHIPPING_URL . '/images/cedcommerce-logo.png' ); ?>" class="cedcommerce-logo" > </a>
	</div>
</div>


  
<div class="ced-vidaxl-navigation-wrapper">
	<ul class="ced-vidaxl-navigation">
		<li>
			<a class="
			<?php
			if ( ( 'configuration-view' == $tab && 'ced_vidaxl_dropshipping' == $page ) || ( '' == $tab && 'ced_vidaxl_dropshipping' == $page ) ) {
				echo 'active';
			}
			?>
			" href="<?php echo esc_url( admin_url( 'admin.php?page=ced_vidaxl_dropshipping&tab=configuration-view' ) ); ?>"><?php esc_html_e( 'Authorization', 'cedcommerce-vidaxl-dropshipping' ); ?></a>
		</li>

		<li>
			<a class="
			<?php
			if ( 'settings-view' == $tab ) {
				echo 'active';
			}
			?>
			" href="<?php echo esc_url( admin_url( 'admin.php?page=ced_vidaxl_dropshipping&tab=settings-view' ) ); ?>"><?php esc_html_e( 'Settings', 'cedcommerce-vidaxl-dropshipping' ); ?></a>
		</li>
		
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_vidaxl_dropshipping&tab=import-products' ) ); ?>" class="
				<?php
				if ( 'import-products' == $tab ) {
					echo 'active';
				}
				?>
			"><?php esc_html_e( 'Import Products', 'cedcommerce-vidaxl-dropshipping' ); ?></a>
		</li>

		
		
	</ul>
	
</div>
<div class="success-admin-notices is-dismissible"></div>