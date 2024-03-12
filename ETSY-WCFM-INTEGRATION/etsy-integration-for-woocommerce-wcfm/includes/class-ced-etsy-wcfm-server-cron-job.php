<?php
require_once '../../../../wp-blog-header.php';

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/**
 * Cron to fetch order and update inventory
 *
 * @class    Ced_Etsy_Cron
 * @version  1.0.0
 * @category Class
 * @author   CedCommerce
 */

class Ced_Etsy_WCFM_Cron {

	public function __construct() {
		$etsy_wcfm_account_list = get_option( 'ced_etsy_wcfm_accounts' ,array() );
		if( !empty($etsy_wcfm_account_list) ) {
			$etsy_wcfm_account_list = json_decode($etsy_wcfm_account_list,true);	
		}
		foreach ( $etsy_wcfm_account_list as $vendor_id => $shop_name ) {
			do_action( 'ced_etsy_wcfm_update_inventory_cron_job_' . $shop_name . '_' . $vendor_id, $shop_name, $vendor_id );
			do_action( 'ced_etsy_wcfm_fetch_order_cron_job_' . $shop_name . '_' . $vendor_id, $shop_name, $vendor_id );
		}
	}
}
$ced_toko_cron_obj = new Ced_Etsy_WCFM_Cron();