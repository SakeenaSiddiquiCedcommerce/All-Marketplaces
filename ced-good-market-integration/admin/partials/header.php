<?php
/**
 * Header of the extensiom
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

	$section = get_active_sections();


?>
<div id="ced_good_market_notices"></div>
<div class="ced_good_market_loader">
	<img src="<?php echo esc_url( GOOD_MARKET_INTEGRATION_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_good_market_loading_img" >
</div>
<div class="navigation-wrapper">
	<?php // esc_attr( ced_good_market_cedcommerce_logo() ); ?>
	<ul class="navigation">
		<?php
		$navigation_menus = navigation_good_market();
		foreach ( $navigation_menus as $label => $href ) {
			$class = '';
			if ( $href['url_end_point'] == $section ) {
					$class = 'active';
			}
			$label = str_replace( '_', ' ', $label );
			echo '<li>';
			echo "<a href='" . esc_url( $href['href'] ) . "' class='" . esc_attr( $class ) . "'>" . esc_html( __( $label, 'good_market-woocommerce-integration' ) ) . '</a>';
			echo '</li>';
		}
		?>
	</ul>
</div>
