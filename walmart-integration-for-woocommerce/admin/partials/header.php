<?php
/**
 * Header of the extensiom
 *
 * @package  Walmart_Woocommerce_Integration
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

	$section = get_active_section();


?>
<div id="ced_walmart_notices"></div>
<div class="ced_walmart_loader">
	<img src="<?php echo esc_url( CED_WALMART_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_walmart_loading_img" >
</div>
<div class="navigation-wrapper">
	<ul class="navigation">
		<?php
		$navigation_menus = get_navigation_menus();
		foreach ( $navigation_menus as $label => $href ) {
			$class = '';
			if ( $label == $section ) {
					$class = 'active';
			}
			$label = str_replace( '_', ' ', $label );
			echo '<li>';
			echo "<a href='" . esc_url( $href ) . "' class='" . esc_attr( $class ) . "'>" . esc_html( __( $label, 'walmart-woocommerce-integration' ) ) . '</a>';
			echo '</li>';
		}
		?>
	</ul>
	<div class="ced_walmart_document"><span><a href="https://woocommerce.com/document/walmart-integration-for-woocommerce/" target="_blank" class="ced_walmart_document_link" name="" value="">View documentation</a></span></div>
</div>
