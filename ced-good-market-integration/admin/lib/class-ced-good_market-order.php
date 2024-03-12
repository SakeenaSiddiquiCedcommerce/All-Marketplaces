<?php
/**
 * Gettting order related data
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
 * Ced_Good_Market_Order
 *
 * @since 1.0.0
 * @param object $_instance Class instance.
 */
class Ced_Good_Market_Order {

	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */

	public static $_instance;

	/**
	 * Ced_Good_Market_Order Instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Ced_Good_Market_Order construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependency();
	}

	/**
	 * Ced_Good_Market_Order loading dependency.
	 *
	 * @since 1.0.0
	 */
	public function load_dependency() {
		$this->send_request_order_obj = new Ced_Good_Market_Request();
	}

	/**
	 * Function for creating a local order
	 *
	 * @since 1.0.0
	 * @param array $orders Order Details.
	 */
	public function create_local_order( $orders ) {
		if ( is_array( $orders ) && ! empty( $orders ) ) {

			foreach ( $orders as $order_detail ) {
				$fulfillment_node = '';
				$base_discount_amount = 0;
				$tax_price            = 0;
				$res_order_details    = $this->get_single_order_details( $order_detail );

				$order_number            = isset( $res_order_details['order_id'] ) ? $res_order_details['order_id'] : '';
				$goodmarket_order_status = isset( $res_order_details['order_account_information']['order_information']['order_status'] ) ? $res_order_details['order_account_information']['order_information']['order_status'] : '';
				$final_tax               = 0;
				$address_information     = $res_order_details['address_information'];
				if ( isset( $res_order_details['order_account_information']['account_information']['customer_name'] ) && ! empty( $res_order_details['order_account_information']['account_information']['customer_name'] ) ) {

					$client_to_array = $res_order_details['order_account_information']['account_information']['customer_name'];
				}
				$ship_to_first_name    = isset( $client_to_array ) ? $client_to_array : array();
				$customer_phone_number = $address_information['s_telephone'];

				$ship_to_address1   = isset( $address_information['s_street'] ) ? $address_information['s_street'] : '';
				$ship_to_address2   = '';
				$ship_to_city_name  = isset( $address_information['s_city'] ) ? $address_information['s_city'] : '';
				$ship_to_state_code = 'UP';
				$ship_to_zip_code   = isset( $address_information['s_postcode'] ) ? $address_information['s_postcode'] : '';
				$ship_to_country    = isset( $address_information['s_country'] ) ? $address_information['s_country'] : '';

				$shipping_address = array(
					'first_name' => $ship_to_first_name,
					'phone'      => $customer_phone_number,
					'address_1'  => $ship_to_address1,
					'address_2'  => $ship_to_address2,
					'city'       => $ship_to_city_name,
					// 'state'      => $ship_to_state_code,
					'postcode'   => $ship_to_zip_code,
					'country'    => $ship_to_country,
				);

				$buyer               = explode( '<br />', $address_information['billing_address'] );
				$account_information = $res_order_details['order_account_information']['account_information'];

				$bill_to_first_name = isset( $address_information['b_name'] ) ? $address_information['b_name'] : '';

				$bill_email_address = isset( $account_information['email'] ) ? $account_information['email'] : '';
				$bill_phone_number  = /*isset( $buyer['phone_number'] ) ? $buyer['phone_number'] :*/ $customer_phone_number;
				$bill_to_address1   = isset( $address_information['b_street'] ) ? $address_information['b_street'] : '';
				$bill_to_address2   = '';
				$bill_to_city_name  = isset( $address_information['b_city'] ) ? $address_information['b_city'] : '';
				$bill_to_zip_code   = isset( $address_information['b_postcode'] ) ? $address_information['b_postcode'] : '';
				$bill_to_country    = isset( $address_information['b_country'] ) ? $address_information['b_country'] : '';

				$billing_address = array(
					'first_name' => $bill_to_first_name,
					'email'      => $bill_email_address,
					'phone'      => $bill_phone_number,
					'address_1'  => $bill_to_address1,
					'address_2'  => $bill_to_address2,
					'city'       => $bill_to_city_name,
					// 'state'      => $bill_to_state_code,
					'postcode'   => $bill_to_zip_code,
					'country'    => $bill_to_country,
				);

				$order_items = $res_order_details['items_ordered']['rows'];

				$good_market_order_cancel_item  = array();
				$good_market_order_shipped_item = array();

				$good_market_shipped_status = false;

				$shipping_amount            = isset( $res_order_details['payment_shipping_method']['shipping_method']['shipping_price'] ) ? $res_order_details['payment_shipping_method']['shipping_method']['shipping_price'] : '';
				$stripped_amnt              = strip_tags( $shipping_amount );
				$removed_dollar_from_amount = preg_replace( '/[^A-Za-z0-9\-]/', '', $stripped_amnt );
				$shipping_amount            = (int) $removed_dollar_from_amount / 100;
				if ( count( $order_items ) ) {
					$item_array = array();

					foreach ( $order_items as $order_item ) {
						$good_market_order_item = array();
						$sku                    = isset( $order_item['sku'] ) ? $order_item['sku'] : '';

						$ordered_qty = isset( $order_item['qty_ordered'] ) ? $order_item['qty_ordered'] : '';
						$cancel_qty  = '';

						$tax_price            = $tax_price + $order_item['base_tax_amount'];
						$base_discount_amount = $base_discount_amount + $order_item['base_discount_amount'];

						$base_price = $order_item['base_original_price'];
						if ( get_woocommerce_currency() != 'USD' ) {
							$ced_goodmarket_currency_convert_rate = get_option( 'ced_goodmarket_currency_convert_rate' );
							$tax_conv_price                       = (float) $tax_price / (float) $ced_goodmarket_currency_convert_rate;
							$tax_price                            = !empty($tax_conv_price) ? round( $tax_conv_price, 2 ) : 0;
							$base_conv_discount_amount            = (float) $base_discount_amount / (float) $ced_goodmarket_currency_convert_rate;
							$base_discount_amount                 = !empty($base_conv_discount_amount) ? round( $base_conv_discount_amount, 2 ) : 0;
							$base_conv_price                      = (float) $base_price / (float) $ced_goodmarket_currency_convert_rate;
							$base_price                           = !empty($base_conv_price) ? round( $base_conv_price, 2 ) : 0;
						}
										// $final_price      = $pro_price;
						$final_tax = 0;
						// $shipping_amount += $final_price;

						$order_status             = 'wc-processing';
						$good_market_order_status = 'Created';
						$item                     = array(
							'OrderedQty' => $ordered_qty,
							'CancelQty'  => $cancel_qty,
							'UnitPrice'  => $base_price,
							'Sku'        => $sku,
						);

						$item_array[] = $item;

					}
				}
				$ship_service     = isset( $res_order_details['payment_shipping_method']['shipping_method']['carrier_title'] ) ? $res_order_details['payment_shipping_method']['shipping_method']['carrier_title'] : '';
				$order_items_info = array(
					'OrderNumber'          => $order_number,
					'order_status'         => $goodmarket_order_status,
					'ship_service'         => $ship_service,
					'ShippingAmount'       => $shipping_amount,
					'ItemsArray'           => $item_array,
					'tax'                  => $final_tax,
					'base_discount_amount' => $base_discount_amount,
					'tax_price'            => $tax_price,

				);

				$address = array(
					'shipping' => $shipping_address,
					'billing'  => $billing_address,
				);

				$merchant_order_id = isset( $res_order_details['order_id'] ) ? $res_order_details['order_id'] : '';
				$purchase_order_id = isset( $order_detail['order_increment_id'] ) ? $order_detail['order_increment_id'] : '';
				$order_details     = isset( $res_order_details ) ? $res_order_details : array();

				$good_market_order_meta = array(
					'merchant_order_id'  => $merchant_order_id,
					'purchaseOrderId'    => $purchase_order_id,
					'fulfillment_node'   => $fulfillment_node,
					'order_detail'       => $order_details,
					'order_items'        => $order_items,
					'order_list_details' => $order_detail,
				);
						// CREATE ORDER
				$order_id = $this->create_order( $address, $order_items_info, 'Good_Market', $good_market_order_meta );
				if ( ! empty( $order_id ) ) {

					$order = wc_get_order( $order_id );
					$order->update_status( $order_status );
				}
			}
		}
	}

	/**
	 * Function for creating order in woocommerce
	 *
	 * @since 1.0.0
	 * @param array  $address Shipping and billing address.
	 * @param array  $order_items_info Order items details.
	 * @param string $marketplace marketplace name.
	 * @param array  $order_meta Order meta details.
	 */
	public function create_order( $address = array(), $order_items_info = array(), $marketplace = 'Good_Market', $order_meta = array() ) {
		$order_id      = '';
		$order_created = false;
		if ( count( $order_items_info ) ) {

			$order_number            = isset( $order_items_info['OrderNumber'] ) ? $order_items_info['OrderNumber'] : 0;
			$goodmarket_order_status = isset( $order_items_info['order_status'] ) ? $order_items_info['order_status'] : '';
			$order_id                = $this->is_good_market_order_exists( $order_number );
			if ( $order_id ) {
				return $order_id;
			}

			if ( count( $order_items_info ) ) {
				$items_array = isset( $order_items_info['ItemsArray'] ) ? $order_items_info['ItemsArray'] : array();
				if ( is_array( $items_array ) ) {
					foreach ( $items_array as $item_info ) {
						$pro_id = isset( $item_info['ID'] ) ? intval( $item_info['ID'] ) : 0;
						$sku    = isset( $item_info['Sku'] ) ? $item_info['Sku'] : '';

						if ( ! $pro_id && ! empty( $sku ) ) {
							$pro_id = wc_get_product_id_by_sku( $sku );
						}
						if ( ! $pro_id ) {
							$sku_exp = explode( '_', $sku );
							if(isset($sku_exp[1])){
								$pro_id  = $sku_exp[1];
							}
						}
						if ( ! $pro_id ) {
							$pro_id = $sku;
						}

						$qty                    = isset( $item_info['OrderedQty'] ) ? intval( $item_info['OrderedQty'] ) : 0;
						$unit_price             = isset( $item_info['UnitPrice'] ) ? floatval( $item_info['UnitPrice'] ) : 0;
						$extend_unit_price      = isset( $item_info['ExtendUnitPrice'] ) ? floatval( $item_info['ExtendUnitPrice'] ) : 0;
						$extend_shipping_charge = isset( $item_info['ExtendShippingCharge'] ) ? floatval( $item_info['ExtendShippingCharge'] ) : 0;
						$_product               = wc_get_product( $pro_id );
						if ( is_wp_error( $_product ) ) {
							continue;
						} elseif ( is_null( $_product ) ) {
							continue;
						} elseif ( ! $_product ) {
							continue;
						} else {
							if ( ! $order_created ) {
								$order_data = array(
									'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
									'customer_note' => __( 'Order from ', 'good_market-woocommmerce-integration' ) . $marketplace,
									'created_via'   => $marketplace,
								);

								/* ORDER CREATED IN WOOCOMMERCE */
								$order = wc_create_order( $order_data );

								/* ORDER CREATED IN WOOCOMMERCE */

								if ( is_wp_error( $order ) ) {
									continue;
								} elseif ( false === $order ) {
									continue;
								} else {
									$order_id      = $order->get_id();
									$order_created = true;
								}
							}
							$_product->set_price( $unit_price );
							$order->add_product( $_product, $qty );
							$order->calculate_totals();
							// $order->save();
						}
					}
				}

				if ( ! $order_created ) {
					return false;
				}

				$shipping_amount = isset( $order_items_info['ShippingAmount'] ) ? $order_items_info['ShippingAmount'] : 0;
				$discount_amount = isset( $order_items_info['DiscountAmount'] ) ? $order_items_info['DiscountAmount'] : 0;
				$ship_service    = isset( $order_items_info['ship_service'] ) ? $order_items_info['ship_service'] : '';

				if ( ! empty( $ship_service ) ) {
					$ship_params = array(
						'ShippingCost' => $shipping_amount,
						'ship_service' => $ship_service,
					);
					$this->add_shipping_charge( $order, $ship_params );
				}

				$shipping_address = isset( $address['shipping'] ) ? $address['shipping'] : '';
				if ( is_array( $shipping_address ) ) {
					$order->set_address( $shipping_address, 'shipping' );
				}

				$new_fee = new WC_Order_Item_Fee();
				$new_fee->set_name( 'Tax' );
				$new_fee->set_amount( esc_attr( $order_items_info['tax_price'] ) );
				$new_fee->set_total( esc_attr( $order_items_info['tax_price'] ) );

				$item_id = $order->add_item( $new_fee );

				$new_discount = new WC_Order_Item_Fee();
				$new_discount->set_name( 'Discount' );
				$new_discount->set_amount( esc_attr( -$order_items_info['base_discount_amount'] ) );
				$new_discount->set_total( esc_attr( -$order_items_info['base_discount_amount'] ) );

				$item_id         = $order->add_item( $new_discount );
				$billing_address = isset( $address['billing'] ) ? $address['billing'] : '';
				if ( is_array( $billing_address ) ) {
					$order->set_address( $billing_address, 'billing' );
				}

				// $order->set_payment_method( 'check' );

				$order->set_total( $discount_amount, '' );

				$order->calculate_totals();

				update_post_meta( $order_id, '_ced_good_market_order_id', $order_number );
				update_post_meta( $order_id, '_ced_good_market_order', 1 );
				update_post_meta( $order_id, '_ced_good_market_order_status', $goodmarket_order_status );
				update_post_meta( $order_id, '_order_marketplace', $marketplace );

				if ( count( $order_meta ) ) {
					foreach ( $order_meta as $o_key => $o_value ) {
						update_post_meta( $order_id, $o_key, $o_value );
					}
				}
			}
			return $order_id;
		}
		return false;

	}

	/**
	 * Function to check  if order already exists
	 *
	 * @since 1.0.0
	 * @param int $order_number Good_Market Order Id.
	 */
	public function is_good_market_order_exists( $order_number = 0 ) {
		global $wpdb;
		if ( $order_number ) {
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_ced_good_market_order_id' AND meta_value=%s LIMIT 1", $order_number ) );
			if ( $order_id ) {
				return $order_id;
			}
		}
		return false;
	}

	/**
	 * Function to acknowledge_order
	 *
	 * @since 1.0.0
	 * @param int $order_id Woo Order Id.
	 */
	public function acknowledge_order( $order_id = 0 ) {
		$order_details     = get_post_meta( $order_id, 'order_detail', true );
		$purchase_order_id = isset( $order_details['purchaseOrderId'] ) ? $order_details['purchaseOrderId'] : '';
		$action            = 'orders/' . esc_attr( $purchase_order_id ) . '/acknowledge';
		do_action( 'ced_good_market_refresh_token' );
		// $response = $this->ced_good_market_curl_instance->ced_good_market_post_request( $action );
		return $response;
	}

	/**
	 * Function to add shipping data
	 *
	 * @since 1.0.0
	 * @param object $order Order details.
	 * @param array  $ship_params Shipping details.
	 */
	public function add_shipping_charge( $order, $ship_params = array() ) {
		$ship_name = isset( $ship_params['ship_service'] ) ? ( $ship_params['ship_service'] ) : 'Default Shipping';
		$ship_cost = isset( $ship_params['ShippingCost'] ) ? $ship_params['ShippingCost'] : 0;
		$ship_tax  = isset( $ship_params['ShippingTax'] ) ? $ship_params['ShippingTax'] : 0;

		$item = new WC_Order_Item_Shipping();

		$item->set_method_title( $ship_name );
		$item->set_method_id( $ship_name );
		$item->set_total( $ship_cost );
		$order->add_item( $item );

		$order->calculate_totals();
		$order->save();
	}


	public function get_single_order_details( $order_details ) {

		if ( empty( $order_details['increment_id'] ) ) {
			return 'No Record Found.';
		}
		$get_api_related_data = $this->send_request_order_obj->get_api_related_data();
		$post_data            = array();
		$post_data['query']   = 'query getOrderData($vendor_id: Int! , $vorder_id: Int!,$hash_token:String) {
			getOrderDetails(vendor_id: $vendor_id, vorder_id: $vorder_id, hash_token:$hash_token) {
				order_data
				success
			}
		}';
		if ( ! empty( $get_api_related_data['data']['generateVendorToken']['hash_token'] ) ) {

			$post_data['variables']['vendor_id']  = $get_api_related_data['data']['generateVendorToken']['vendor_id'];
			$post_data['variables']['vorder_id']  = $order_details['order_id'];
			$post_data['variables']['hash_token'] = $get_api_related_data['data']['generateVendorToken']['hash_token'];
		}

		$data_to_send_order = json_encode( $post_data );
		$res_order          = $this->send_request_order_obj->good_market_post( $data_to_send_order );
		$decod_res          = json_decode( $res_order, 1 );

		$order_line_data = json_decode( $decod_res['data']['getOrderDetails']['order_data'], 1 );
		return $order_line_data;
	}
}
