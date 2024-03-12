<?php
/**
 * Product Filters in manage products
 *
 * @package  CedCommerce_Integration_for_Good_Market
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Ced_Good_Market_Products_Filter
 *
 * @since 1.0.0
 */
class Ced_Good_Market_Products_Filter {


	/**
	 * Function for filtering products
	 *
	 * @since 1.0.0
	 */
	public function ced_good_market_filters_on_products() {
		if ( ( isset( $_POST['status_sorting'] ) && ! empty( $_POST['status_sorting'] ) ) ||
			( isset( $_POST['pro_status_sorting'] ) && ! empty( $_POST['pro_status_sorting'] ) )
			|| ( isset( $_POST['pro_cat_sorting'] ) && ! empty( $_POST['pro_cat_sorting'] ) ) || ( isset( $_POST['pro_type_sorting'] ) && ! empty( $_POST['pro_type_sorting'] ) ) ) {

			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}

			$status_sorting     = isset( $_POST['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['status_sorting'] ) ) : '';
			$pro_cat_sorting    = isset( $_POST['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_cat_sorting'] ) ) : '';
			$pro_type_sorting   = isset( $_POST['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_type_sorting'] ) ) : '';
			$pro_status_sorting = isset( $_POST['pro_status_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_status_sorting'] ) ) : '';
			$current_url        = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			wp_redirect( $current_url . '&status_sorting=' . $status_sorting . '&pro_cat_sorting=' . $pro_cat_sorting . '&pro_type_sorting=' . $pro_type_sorting . ' &pro_status_sorting=' . $pro_status_sorting );
			exit;
		} else {
			$url = admin_url( 'admin.php?page=ced_good_market&section=products' );
			wp_redirect( $url );
			exit;
		}
	}

	/**
	 * Function for searching a product
	 *
	 * @since 1.0.0
	 */
	public function product_search_box() {

		if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
			return;
		}

		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
			$current_url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$searchdata  = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
			$searchdata  = str_replace( ' ', '+', $searchdata );
			wp_redirect( $current_url . '&s=' . ( $searchdata ) );
			exit;
		} else {
			$url = admin_url( 'admin.php?page=ced_good_market&section=class-ced-good_market-products-list&' );
			wp_redirect( $url );
			exit;
		}
	}
}
