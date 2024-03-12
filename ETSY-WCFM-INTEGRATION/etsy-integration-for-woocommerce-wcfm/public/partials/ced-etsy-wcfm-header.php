<?php
/**
 * Header of the extensiom
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$section = ced_etsy_wcfm_get_active_section();
$activeShop = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
$activeShops = get_option( 'ced_etsy_active_shops' . ced_etsy_wcfm_get_vendor_id() , array() );
$activeShops[] = $activeShop;
update_option( 'ced_etsy_active_shops' . ced_etsy_wcfm_get_vendor_id(), array_unique( $activeShops ) );
?>
<div id="ced_etsy_wcfm_notices"></div>

<div class="ced_etsy_wcfm_loader">
	<img src="<?php echo esc_url( CED_ETSY_WCFM_URL . 'public/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_etsy_wcfm_loading_img" >
</div>

			<ul class="ced-etsy-wcfm-navigation">
				<?php
				$enabled_marketplaces = get_user_meta( ced_etsy_wcfm_get_vendor_id() , '_ced_allowed_marketplaces' , true );
				if( in_array( 'etsy', $enabled_marketplaces )  ) {
					$navigation_menus = get_etsy_wcfm_navigation_menus();
					foreach ( $navigation_menus as $label => $href ) {
						$class = '';
						if ( $label == $section ) {
							$class = 'ced-etsy-wcfm-active';
						}
						$label = str_replace( '-', ' ', $label );
						echo '<li>';
						echo "<a href='" . esc_url( $href ) . "' class='" . esc_attr( $class ) . "'>" . esc_html( __( strtoupper($label), 'etsy-wcfm-woocommerce-integration' ) ) . '</a>';
						echo '</li>';
					}
				}
				?>
			</ul>
