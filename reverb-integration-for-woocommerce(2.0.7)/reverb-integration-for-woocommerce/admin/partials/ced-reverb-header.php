<?php
/**
 * Header of the extensiom
 *
 * @package  reverb_Integration_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

	$section = get_reverb_active_section();

?>
<div id="ced_reverb_notices"></div>
<div class="ced_reverb_loader">

	<img src="<?php echo esc_url( CED_REVERB_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_reverb_loading_img" >
</div>

<div class="ced_reverb_add_account_popup_main_wrapper">
<div class="ced_reverb_add_account_popup_content">
				<div class="ced_reverb_add_account_popup_header">
					<h5><?php esc_html_e( 'Prepared Data', 'woocommerce-lazada-integration' ); ?></h5>
					<span class="ced_reverb_add_account_popup_close">X</span>
				</div>
				<div class="ced_reverb_add_account_popup_body">
						
					</div>
				</div>
			</div>


<div class="navigation-wrapper">
	<ul class="navigation">
		<?php
		$navigation_menus = get_reverb_navigation_menus();
		foreach ( $navigation_menus as $label => $href ) {
			$class = '';
			if ( $label == $section ) {
				$class = 'active';
			}
			$label = str_replace( '_', ' ', $label );
			echo '<li>';
			echo "<a href='" . esc_url( $href ) . "' class='" . esc_attr( $class ) . "'>" . esc_html( __( $label, 'reverb-woocommerce-integration' ) ) . '</a>';
			echo '</li>';
		}
		?>
	</ul>
		<div class="ced_reverb_document"><span><a href="https://woocommerce.com/document/reverb-integration-for-woocommerce/" target="_blank" class="ced_reverb_document_link" name="" value="">View documentation</a></span></div>
</div>

<?php

// $ced = get_option('luck_test', "");

// print_r($ced);

// delete_transient('ced_utk_123');

// //set_transient('ced_utk_123', "asdf");

// $edc = get_transient('ced_utk_123');

// print_r($edc);
// $wsx = wc_get_product_id_by_sku('woo-vneck-tee-blue');
// echo $wsx;
// echo "hhhh";
?>
