<?php
/**
 * The admin-orders related functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommmerce_Tokopedia_Integration
 * @subpackage Woocommmerce_Tokopedia_Integration/admin
 */
class Class_Ced_Tokopedia_Orders {

	public static $_instance;
		/**
		 * Ced_Tokopedia_Config Instance.
		 *
		 * Ensures only one instance of Ced_Tokopedia_Config is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 */

	public function __construct() {

		$requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
		if ( file_exists( $requrest_tokopedia_file ) ) {
			require_once $requrest_tokopedia_file;
		}
		$this->obj_request = new tokopediaRequest();
	}


	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/*
	*
	* function for getting ordres  from Tokopedia
	*
	*
	*/
	public function getOrders( $shop_name ) {
		$result = $this->obj_request->sendCurlGetMethod( 'get_all_orders', $shop_name );	
// 		echo "<pre>";
// 		print_r( $result );
// 		die();		
		if ( isset( $result['data'] ) && $result['data'] > 0 ) {
			update_option( 'ced_tokopedia_orders_fetched', true );
		}
		if ( isset( $result['data'] ) && ! empty( $result['data'] ) ) {
			$this->createLocalOrder( $result['data'] , $shop_name );
// 			die;
		}
	}

	/*
	*
	* function for creating a local order
	*
	*
	*/
	public function createLocalOrder( $orders, $shopId = '' ) {

		if ( is_array( $orders ) && ! empty( $orders ) ) {

			$address        = array();
			$OrderItemsInfo = array();

			foreach ( $orders as $key => $order ) {
				//print_r( $order );
				//die();
				$order_fsid   = isset( $order['fs_id'] ) ? $order['fs_id'] : '';
				$order_status = isset( $order['order_status'] ) ? $order['order_status'] : '';
				$receipt_data = isset( $order['recipient'] ) ? $order['recipient'] : '';
				$product_data = isset( $order['products'] ) ? $order['products'] : '';
				$buyer_data   = isset( $order['buyer'] ) ? $order['buyer'] : '';
				
				if ( ! empty( $receipt_data ) ) {

					$ShipToName         = isset( $receipt_data['name'] ) ? $receipt_data['name'] : '';
					$ShipToPhone        = isset( $receipt_data['phone'] ) ? $receipt_data['phone'] : '';
					$ShipToAddressfull  = isset( $receipt_data['address']['address_full'] ) ? $receipt_data['address']['address_full'] : '';
					$ShipToDistrict     = isset( $receipt_data['address']['district'] ) ? $receipt_data['address']['district'] : '';
					$ShipToCityName     = isset( $receipt_data['address']['city'] ) ? $receipt_data['address']['city'] : '';
					$ShipToCityId       = isset( $receipt_data['address']['city_id'] ) ? $receipt_data['address']['city_id'] : '';
					$ShipToprovince     = isset( $receipt_data['address']['province'] ) ? $receipt_data['address']['province'] : '';
					$ShipToprovinceId   = isset( $receipt_data['address']['province_id'] ) ? $receipt_data['address']['province_id'] : '';
					$ShipToPostalCode   = isset( $receipt_data['address']['postal_code'] ) ? $receipt_data['address']['postal_code'] : '';
					$ShipToDistrictCode = isset( $receipt_data['address']['district_id'] ) ? $receipt_data['address']['district_id'] : '';
					$shiptocountry      = isset( $receipt_data['address']['country'] ) ? $receipt_data['address']['country'] : '';
					$ShipToCurrency     = isset( $receipt_data['address']['currency'] ) ? $receipt_data['address']['currency'] : '';

					$ShippingAddress = array(
						'first_name' => $ShipToName,
						'address_1'  => $ShipToAddressfull,
						'address_2'  => $ShipToAddressfull,
						'city'       => $ShipToCityName,
						'state'      => $ShipToprovince,
						'postcode'   => $ShipToPostalCode,
						'country'    => $shiptocountry,
					);
					$BillToName      = $ShipToName;

					if ( ! empty( $buyer_data ) ) {
						$buyer_id    = isset( $buyer_data['id'] ) ? $buyer_data['id'] : '';
						$buyer_name  = isset( $buyer_data['name'] ) ? $buyer_data['name'] : '';
						$buyer_phone = isset( $buyer_data['phone'] ) ? $buyer_data['phone'] : '';
						$buyer_email = isset( $buyer_data['email'] ) ? $buyer_data['email'] : '';
					}
					$BillEmailAddress = isset( $buyer_email ) ? $buyer_email : '';

					$BillingAddress = array(
						'first_name' => $buyer_name,
						'last_name'  => $buyer_name,
						'email'      => $buyer_email,
						'address_1'  => $ShipToAddressfull,
						'address_2'  => $ShipToAddressfull,
						'city'       => $ShipToCityName,
						'state'      => $ShipToprovince,
						'postcode'   => $ShipToPostalCode,
						'country'    => $shiptocountry,
					);

					$address['shipping'] = $BillingAddress;
					$address['billing']  = $BillingAddress;

					$OrderNumber = isset( $order['order_id'] ) ? $order['order_id'] : '';

					if ( isset( $product_data ) && ! empty( $product_data ) ) {

						$ItemArray = array();

						foreach ( $product_data as $transaction ) {
							$ID           = false;
							$listing_id   = isset( $transaction['id'] ) ? $transaction['id'] : false;
							$listing_name = isset( $transaction['name'] ) ? $transaction['name'] : false;
							$OrderedQty   = isset( $transaction['quantity'] ) ? $transaction['quantity'] : 1;
							$basePrice    = isset( $transaction['total_price'] ) ? $transaction['total_price'] : '';
							$CancelQty    = 0;
							$sku          = isset( $transaction['sku'] ) ? $transaction['sku'] : '';
						
							$ID          = $this->get_product_id_by_params( '_sku', $sku );
// 							update_post_meta( 519 ,'_ced_tokopedia_upload_id_11426500',1863501355 );
// 							print_r( '_ced_tokopedia_upload_id_' . $shopId);
							if ( ! $ID || empty($sku) ) {
								$ID = $this->get_product_id_by_params( '_ced_tokopedia_upload_id_' . $shopId, $listing_id );
							}
							
							$item        = array(
								'Name'       => $ShipToName,
								'OrderedQty' => $OrderedQty,
								'CancelQty'  => $CancelQty,
								'UnitPrice'  => $basePrice,
								'Sku'        => $sku,
								'ID'         => $ID,
							);
							$ItemArray[] = $item;

						}
					}
				}

				$amt = isset( $order['amt'] ) ? $order['amt'] : '';

				$promo_order_detail = isset( $order['promo_order_detail'] ) ? $order['promo_order_detail'] : 0;
				$wherehouse_id      = isset( $order['warehouse_id'] ) ? $order['warehouse_id'] : 0;
				$custom_fields      = isset( $order['custom_fields']['awb'] ) ? $order['custom_fields']['awb'] : 0;

				$ShippingAmount  = isset( $amt['shipping_cost'] ) ? $amt['shipping_cost'] : 0;
				$ShipService     = isset( $promo_order_detail['shipping_details']['shipping_method'] ) ? $promo_order_detail['shipping_details']['shipping_method'] : '';
				$ProductDiscount = isset( $promo_order_detail['total_discount_product'] ) ? $promo_order_detail['total_discount_product'] : 0;
				$DiscountDetails = isset( $promo_order_detail['total_discount_details'] ) ? $promo_order_detail['saved_tokopedia_details'] : array();

				$DiscountedAmount = isset( $promo_order_detail['total_discount_shipping'] ) ? $promo_order_detail['total_discount_shipping'] : 0;
				$finalTax         = 1;

				$OrderItemsInfo = array(

					'OrderNumber'      => isset( $OrderNumber ) ? $OrderNumber : '',
					'ItemsArray'       => isset( $ItemArray ) ? $ItemArray : '',
					'tax'              => isset( $finalTax ) ? $finalTax : '',
					'ShippingAmount'   => isset( $ShippingAmount ) ? $ShippingAmount : '',
					'ShipService'      => isset( $ShipService ) ? $ShipService : '',
					'DiscountedAmount' => isset( $DiscountedAmount ) ? $DiscountedAmount : '',

				);

				$orderItems      = isset( $transactions_per_reciept ) ? $transactions_per_reciept : '';
				$merchantOrderId = isset( $OrderNumber ) ? $OrderNumber : '';
				$purchaseOrderId = isset( $OrderNumber ) ? $OrderNumber : '';
				$fulfillmentNode = '';
				$orderDetail     = isset( $order ) ? $order : array();

				$tokopediaOrderMeta = array(
					'merchant_order_id' => isset( $merchantOrderId ) ? $merchantOrderId : '',
					'purchaseOrderId'   => isset( $purchaseOrderId ) ? $purchaseOrderId : '',
					'fulfillment_node'  => isset( $fulfillmentNode ) ? $fulfillmentNode : '',
					'order_detail'      => isset( $orderDetail ) ? $orderDetail : '',
					'order_items'       => isset( $orderItems ) ? $orderItems : '',
				);

				$creation_date = $order['create_time'];
				$order_id      = $this->create_order( $address, $OrderItemsInfo, 'Tokopedia', $tokopediaOrderMeta, $creation_date, $shopId );
				$shipping_ref_num = isset( $order['custom_fields']['awb'] ) ? $order['custom_fields']['awb'] : 0;
				if( $order_id ) {
					update_post_meta( $order_id , 'ced_tokopedia_order_state', $order_status );
					update_post_meta( $order_id , '_tokopedia_umb_order_srn', $shipping_ref_num );
					if( 220 == $order_status ) {
						$requrest_tokopedia_file = CED_TOKOPEDIA_DIRPATH . 'admin/tokopedia/lib/RequestToko/tokopediaRequest.php';
						$shop_data    = ced_topedia_get_account_details_by_shop_name( $shopId );
						$fsid         = $shop_data['fsid'];
						require_once $requrest_tokopedia_file;
						$obj_request = new tokopediaRequest();
						$action = 'https://fs.tokopedia.net/v1/order/'.$OrderNumber.'/fs/'.$fsid.'/ack';
						$response = $obj_request->sendshipmentCurlPostMethod( $action , '' , $shopId );
						if( isset($response['data']) && strtolower($response['data']) == "success" ) {
							update_post_meta( $order_id , 'ced_tokopedia_order_state', 400 );
						}
					} 
					
					if ( 700 == $order_status ) {
						$_order = wc_get_order( $order_id );
						$_order->update_status( 'wc-completed' );
					}
					
					$_order = wc_get_order( $order_id );
					if( is_object( $_order ) ) {
						$tokoorderstatus = array(
							'10'=>'wc-cancelled',
						);
						
						if(isset($tokoorderstatus[$order_status])) {
							$woo_order_state = $tokoorderstatus[$order_status];
							$_order->update_status($woo_order_state);
						}
					}
				}
			}
		}
	}


public function get_product_id_by_params( $meta_key = '', $meta_value = '' ) {
		if ( ! empty( $meta_value ) ) {
			$posts = get_posts(
				array(

					'numberposts' => -1,
					'post_type'   => array( 'product', 'product_variation' ),
					'meta_query'  => array(
						array(
							'key'     => $meta_key,
							'value'   => trim( $meta_value ),
							'compare' => '=',
						),
					),
					'fields'      => 'ids',

				)
			);
			if ( ! empty( $posts ) ) {
				return $posts[0];
			}
			return false;
		}
		return false;
	}

	/*
	*
	*function for creating order in woocommerce
	*
	*
	*/

	public function create_order( $address = array(), $OrderItemsInfo = array(), $frameworkName = 'tokopedia', $orderMeta = array(), $creation_date = '', $shopId ) {
// print_r($address);
		$order_id      = '';
		$order_created = false;
		if ( count( $OrderItemsInfo ) ) {

			$OrderNumber = isset( $OrderItemsInfo['OrderNumber'] ) ? $OrderItemsInfo['OrderNumber'] : 0;

			$order_id = $this->is_tokopedia_order_exists( $OrderNumber );

			if ( $order_id ) {
				return $order_id;
			}
			if ( count( $OrderItemsInfo ) ) {
				if ( is_array( $OrderItemsInfo ) ) {
					 $ItemArray = $OrderItemsInfo['ItemsArray'];
					foreach ( $ItemArray as $ItemInfo ) {

						$ProID = isset( $ItemInfo['ID'] ) ? intval( $ItemInfo['ID'] ) : 0;
						$Sku   = isset( $ItemInfo['Sku'] ) ? $ItemInfo['Sku'] : '';
						
						$MfrPartNumber = isset( $ItemInfo['MfrPartNumber'] ) ? $ItemInfo['MfrPartNumber'] : '';
						$Upc           = isset( $ItemInfo['UPCCode'] ) ? $ItemInfo['UPCCode'] : '';
						$Asin          = isset( $ItemInfo['ASIN'] ) ? $ItemInfo['ASIN'] : '';
						$params        = array( '_sku' => $Sku );
						if ( ! $ProID ) {
							$ProID = wc_get_product_id_by_sku( $Sku );
						}
						if ( ! $ProID ) {
							$ProID = $Sku;
						}
						$productsToUpdate[]   = $ProID;
						$Qty                  = isset( $ItemInfo['OrderedQty'] ) ? intval( $ItemInfo['OrderedQty'] ) : 0;
						$UnitPrice            = isset( $ItemInfo['UnitPrice'] ) ? floatval( $ItemInfo['UnitPrice'] ) : 0;
						$ExtendUnitPrice      = isset( $ItemInfo['ExtendUnitPrice'] ) ? floatval( $ItemInfo['ExtendUnitPrice'] ) : 0;
						$ExtendShippingCharge = isset( $ItemInfo['ExtendShippingCharge'] ) ? floatval( $ItemInfo['ExtendShippingCharge'] ) : 0;

						$_product = wc_get_product( $ProID );
						if ( is_wp_error( $_product ) ) {
							continue;
						} elseif ( is_null( $_product ) ) {
							continue;
						} elseif ( ! $_product ) {
							continue;
						} else {

							if ( ! $order_created ) {
								$order_data = array(
									'status'        => apply_filters( 'woocommerce_default_order_status', 'processing' ),
									'customer_note' => __( 'Order from ', 'woocommerce-tokopedia-integration' ) . $frameworkName . '[' . $shopId . ']',
									'created_via'   => $frameworkName,
								);

								/* ORDER CREATED IN WOOCOMMERCE */
								$order = wc_create_order( $order_data );
								if ( is_wp_error( $order ) ) {
									continue;
								} elseif ( false === $order ) {
									continue;
								} else {
									if ( WC()->version < '3.0.0' ) {
										$order_id = $order->id;
									} else {
										$order_id = $order->get_id();
									}
									$order_created = true;
								}
							}
							$_product->set_price( $UnitPrice );
							$order->add_product( $_product, $Qty );
							$order->calculate_totals();
						}
					}
				}

				if ( ! $order_created ) {
					return false;
				}
				
				update_post_meta( $order_id, '_ced_tokopedia_order_id', $OrderNumber );
				update_post_meta( $order_id, '_is_ced_tokopedia_order', 1 );
				update_post_meta( $order_id, '_tokopedia_umb_order_status', 'Fetched' );
				update_post_meta( $order_id, '_umb_tokopedia_marketplace', $frameworkName );
				update_post_meta( $order_id, 'ced_tokopedia_order_shop_id', $shopId );
				
				$OrderItemAmount = isset( $OrderItemsInfo['OrderItemAmount'] ) ? $OrderItemsInfo['OrderItemAmount'] : 0;
				$ShippingAmount  = isset( $OrderItemsInfo['ShippingAmount'] ) ? $OrderItemsInfo['ShippingAmount'] : 0;
				$DiscountAmount  = isset( $OrderItemsInfo['DiscountAmount'] ) ? $OrderItemsInfo['DiscountAmount'] : 0;
				$RefundAmount    = isset( $OrderItemsInfo['RefundAmount'] ) ? $OrderItemsInfo['RefundAmount'] : 0;
				$ShipService     = isset( $OrderItemsInfo['ShipService'] ) ? $OrderItemsInfo['ShipService'] : '';
				if ( ! empty( $ShippingAmount ) ) {
					$Ship_params = array(
						'ShippingCost' => $ShippingAmount,
						'ShipService'  => $ShipService,
					);
					$this->add_shipping_charge( $order, $Ship_params );
				}
				$ShippingAddress = isset( $address['shipping'] ) ? $address['shipping'] : '';
				if ( is_array( $ShippingAddress ) && ! empty( $ShippingAddress ) ) {
					if ( WC()->version < '3.0.0' ) {

						$order->set_address( $ShippingAddress, 'shipping' );
					} else {

						$type = 'shipping';

						foreach ( $ShippingAddress as $key => $value ) {

							if ( ! empty( $value ) && null != $value && ! empty( $value ) ) {
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}
				$new_fee            = new stdClass();
				$new_fee->name      = 'Tax';
				$new_fee->amount    = (float) esc_attr( $OrderItemsInfo['tax'] );
				$new_fee->tax_class = '';
				$new_fee->taxable   = 0;
				$new_fee->tax       = '';
				$new_fee->tax_data  = array();
				if ( WC()->version < '3.0.0' ) {
					$item_id = $order->add_fee( $new_fee );
				} else {
					$item_id = $order->add_item( $new_fee );
				}

				$BillingAddress = isset( $address['billing'] ) ? $address['billing'] : '';
				if ( is_array( $BillingAddress ) && ! empty( $BillingAddress ) ) {
					if ( WC()->version < '3.0.0' ) {

						$order->set_address( $ShippingAddress, 'billing' );
					} else {
						$type = 'billing';

						foreach ( $BillingAddress as $key => $value ) {

							if ( null != $value && ! empty( $value ) ) {
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}
				wc_reduce_stock_levels( $order->get_id() );
				$order->set_payment_method( 'check' );

				if ( WC()->version < '3.0.0' ) {
					$order->set_total( $DiscountAmount, 'cart_discount' );
				} else {
					$order->set_total( $DiscountAmount );
				}
				$order->calculate_totals();
				update_option( 'ced_tokopedia_last_order_created_time', $creation_date );
				if ( count( $orderMeta ) ) {
					foreach ( $orderMeta as $oKey => $oValue ) {
						update_post_meta( $order_id, $oKey, $oValue );
					}
				}
			}
			return $order_id;
		}
		return false;
	}

	/**
	 * Tokopedia checking if order already exists
	 *
	 * @since    1.0.0
	 */
	public function is_tokopedia_order_exists( $order_number = 0 ) {

		global $wpdb;
		if ( $order_number ) {
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_ced_tokopedia_order_id' AND meta_value=%s LIMIT 1", $order_number ) );
			if ( $order_id ) {
				return $order_id;
			}
		}
		return false;
	}

	/**
	 * Function to add shipping data
	 *
	 * @since 1.0.0
	 * @param object $order Order details.
	 * @param array  $ship_params Shipping details.
	 */
	public static function add_shipping_charge( $order, $ship_params = array() ) {
		$ship_name = isset( $ship_params['ShipService'] ) ? ( $ship_params['ShipService'] ) : 'UMB Default Shipping';
		$ship_cost = isset( $ship_params['ShippingCost'] ) ? $ship_params['ShippingCost'] : 0;
		$ship_tax  = isset( $ship_params['ShippingTax'] ) ? $ship_params['ShippingTax'] : 0;
		$item      = new WC_Order_Item_Shipping();
		$item->set_method_title( $ship_name );
		$item->set_method_id( $ship_name );
		$item->set_total( $ship_cost );
		$order->add_item( $item );
		$order->calculate_totals();
		$order->save();
	}
}
