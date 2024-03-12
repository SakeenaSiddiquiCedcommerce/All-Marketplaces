<?php
/**
 * Gettting order related data
 *
 * @package  Facebook_marketplace_connector_for_woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class_CedFacebookOrders
 *
 * @since 1.0.0
 * @param object $_instance Class instance.
 */
class Class_CedFacebookOrders {

	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */

	public static $_instance;

	/**
	 * Class_CedFacebookOrders Instance.
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
	 * Class_CedFacebookOrders construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// $this->load_dependency();
	}

	/**
	 * Class_CedFacebookOrders loading dependency.
	 *
	 * @since 1.0.0
	 */
	public function load_dependency() {
		$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$this->ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
	}

	/**
	 * Function for getting ordres from shopee
	 *
	 * @since 1.0.0
	 * @param int $shop_id Shopee Shop Id.
	 */
	public function ced_facebook_get_the_orders() {
		/*
		$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
		$authenticated_pages  = get_option( 'ced_fmcw_merchant_page_authenticated', array() );
		$catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', array() );

		foreach ( $authenticated_pages as $key => $authenticated_page ) {

			$page_id = isset( $catalog_and_page_id[ $authenticated_page ]['page_id'] ) ? $catalog_and_page_id[ $authenticated_page ]['page_id'] : '';
			$action  = 'webapi/rest/v1/order';
			// print_r($page_id);die;

			$parameters = array(
				'page_id' => $page_id,
				'state'   => 'CREATED,IN_PROGRESS',
			);
			$orders     = $ced_facebook_request->get_request( $action, $parameters );
			echo '<pre>';
			print_r( $orders );
			die( 'dfd' );
			if ( isset( $orders['data'] ) ) {
				$orders = $orders['data'];
				$this->create_local_order( $orders, $page_id, $authenticated_page );
			}
		}*/

		$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
		// $authenticated_pages  = get_option( 'ced_fmcw_merchant_page_authenticated', array() );
		// $catalog_and_page_id  = get_option( 'ced_fmcw_catalog_and_page_id', array() );
		$call_for_get_shop_details = get_option( 'ced_fmcw_stord_whole_store_data' );
		$connected_page            = isset( $call_for_get_shop_details['data']['pages'][0] ) ? $call_for_get_shop_details['data']['pages'][0] : '';
		$catalog_and_page_id       = get_option( 'ced_fmcw_catalog_and_page_id', true );
		$bussiness_id              = array_keys( $catalog_and_page_id )[0];
		$connected_catalog_id      = isset( $catalog_and_page_id[ $bussiness_id ]['catalog_id'] ) ? $catalog_and_page_id[ $bussiness_id ]['catalog_id'] : '';

		// foreach ( $authenticated_pages as $key => $authenticated_page ) {

			// $page_id = isset( $catalog_and_page_id[ $authenticated_page ]['page_id'] ) ? $catalog_and_page_id[ $authenticated_page ]['page_id'] : '';
			$action = 'webapi/rest/v1/order';
			// print_r($page_id);die;

			$parameters = array(
				'page_id' => $connected_page,
				'state'   => 'FB_PROCESSING,CREATED,IN_PROGRESS,COMPLETED',
			);
			$orders     = $ced_facebook_request->get_request( $action, $parameters );
			echo '<pre>';
			print_r( $orders );
			die( 'dfd' );
			if ( isset( $orders['data'] ) ) {
				$orders = $orders['data'];
				$this->create_local_order( $orders, $page_id, $authenticated_page );
			}
			// }
	}

	/**
	 * Function for getting ordres from shopee
	 *
	 * @since 1.0.0
	 * @param int $shop_id Shopee Shop Id.
	 */
	public function ced_facebook_cancel_orders( $page_id = '', $order_id = '', $data = '' ) {
		$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
		$action               = 'webapi/rest/v1/order/cancellation';
			$parameters       = array(
				'page_id'  => $page_id,
				'order_id' => $order_id,
				'data'     => $data,
			);

			$order_cancel = $ced_facebook_request->post_request( $action, $parameters );

			return $order_cancel;
	}

	/**
	 * Function for getting ordres from shopee
	 *
	 * @since 1.0.0
	 * @param int $shop_id Shopee Shop Id.
	 */
	public function ced_facebook_refund_orders( $page_id = '', $order_id = '', $data = '' ) {
		$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
		$action               = 'webapi/rest/v1/order/refund';
			$parameters       = array(
				'page_id'  => $page_id,
				'order_id' => $order_id,
				'data'     => $data,
			);
			$order_cancel     = $ced_facebook_request->post_request( $action, $parameters );
			// print_r($order_cancel);
			return $order_cancel;
	}


	/**
	 * Function for getting ordres from shopee
	 *
	 * @since 1.0.0
	 * @param int $shop_id Shopee Shop Id.
	 */
	public function ced_facebook_ship_orders( $page_id = '', $order_id = '', $data = '' ) {
		$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
		$action               = 'webapi/rest/v1/order/shipment';
			$parameters       = array(
				'page_id'  => $page_id,
				'order_id' => $order_id,
				'data'     => $data,
			);
			$order_shipment   = $ced_facebook_request->post_request( $action, $parameters );
			return $order_shipment;
	}

	/**
	 * Function for creating a local order
	 *
	 * @since 1.0.0
	 * @param array $orders Order Details.
	 * @param int   $shop_id Shopee Shop Id.
	 */
	public function create_local_order( $orders, $page_id, $authenticated_page ) {
		if ( is_array( $orders ) && ! empty( $orders ) ) {
			$OrderItemsInfo = array();
			$neworder       = array();
			foreach ( $orders as $order ) {
				$ShipToFirstName      = isset( $order['billing_details']['name'] ) ? $order['billing_details']['name'] : '';
				$ShipToAddress1       = isset( $order['shipping_address']['street1'] ) ? $order['shipping_address']['street1'] : '';
				$ShipToAddress2       = isset( $order['shipping_address']['street2'] ) ? $order['shipping_address']['street2'] : '';
				$ShipToCityName       = isset( $order['shipping_address']['city'] ) ? $order['shipping_address']['city'] : '';
				$ShipToStateCode      = isset( $order['shipping_address']['state'] ) ? $order['shipping_address']['state'] : '';
				$ShipToZipCode        = isset( $order['shipping_address']['postal_code'] ) ? $order['shipping_address']['postal_code'] : '';
				$ShipToCountry        = isset( $order['shipping_address']['country'] ) ? $order['shipping_address']['country'] : '';
				$CustomerPhoneNumber  = isset( $order['buyer']['phone'] ) ? $order['buyer']['phone'] : '';
				$customerEmailaddress = isset( $order['billing_details']['email'] ) ? $order['billing_details']['email'] : '';

				$ShipToAddress1  = $ShipToAddress1;
				$ShippingAddress = array(
					'first_name' => $ShipToFirstName,
					'phone'      => $CustomerPhoneNumber,
					'address_1'  => $ShipToAddress1,
					'address_2'  => $ShipToAddress2,
					'city'       => $ShipToCityName,
					'state'      => $ShipToStateCode,
					'postcode'   => $ShipToZipCode,
					'email'      => $customerEmailaddress,
					'country'    => $ShipToCountry,
				);

				$address      = array(
					'shipping' => $ShippingAddress,
					'billing'  => $ShippingAddress,
				);
				$OrderNumber  = $order['id'];
				$OrderStatus  = $order['status'];
				$ordertotal   = isset( $order['total'] ) ? $order['total'] : '';
				$transactions = $order['items'];
				if ( is_array( $transactions ) && ! empty( $transactions ) ) {
					$ItemArray = array();
					foreach ( $transactions as $transaction ) {
						$item        = array();
						$ID          = isset( $transaction['source_id'] ) ? $transaction['source_id'] : '';
						$OrderedQty  = $transaction['quantity'];
						$CancelQty   = 0;
						$basePrice   = $transaction['price'];
						$sku         = isset( $transaction['sku'] ) ? $transaction['sku'] : false;
						$item        = array(
							'OrderedQty' => $OrderedQty,
							'CancelQty'  => $CancelQty,
							'UnitPrice'  => $basePrice,
							'Sku'        => $sku,
							'ID'         => $ID,
						);
						$ItemArray[] = $item;
					}
				}
				$finalTax       = isset( $order['payment_details']['tax'] ) ? $order['payment_details']['tax'] : '';
				$OrderItemsInfo = array(
					'OrderNumber' => $OrderNumber,
					'ItemsArray'  => $ItemArray,
					'tax'         => $finalTax,
				);
				$orderItems     = $transactions;

				$merchantOrderId = $OrderNumber;
				$purchaseOrderId = $OrderNumber;
				$fulfillmentNode = '';
				$orderDetail     = isset( $order ) ? $order : array();
				$OnbuyOrderMeta  = array(
					'merchant_order_id' => $merchantOrderId,
					'purchaseOrderId'   => $purchaseOrderId,
					'fulfillment_node'  => $fulfillmentNode,
					'order_detail'      => $orderDetail,
					'order_items'       => $orderItems,
				);
				$woo_order_id    = $this->create_order( $address, $OrderItemsInfo, 'fmcw', $OnbuyOrderMeta, $page_id, $authenticated_page );
				if ( isset( $woo_order_id ) && ! empty( $woo_order_id ) ) {
					update_post_meta( $woo_order_id, '_onbuy_order_status', $OrderStatus );
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
	 * @param string $framework_name Framework name.
	 * @param array  $order_meta Order meta details.
	 * @param string $creation_date Order creation date.
	 * @param int    $shop_id Shopee Shop Id.
	 */
	public function create_order( $address = array(), $OrderItemsInfo = array(), $frameworkName = 'fmcw', $orderMeta = array(), $page_id ) {
		global $cedonbuyhelper;
		$order_id      = '';
		$order_created = false;
		if ( count( $OrderItemsInfo ) ) {
			$OrderNumber = isset( $OrderItemsInfo['OrderNumber'] ) ? $OrderItemsInfo['OrderNumber'] : 0;
			$order_id    = $this->is_onbuy_order_exists( $OrderNumber );
			if ( $order_id ) {
				return $order_id;
			}
			if ( count( $OrderItemsInfo ) ) {
				$ItemsArray = isset( $OrderItemsInfo['ItemsArray'] ) ? $OrderItemsInfo['ItemsArray'] : array();
				if ( is_array( $ItemsArray ) ) {
					foreach ( $ItemsArray as $ItemInfo ) {
						$ProID         = isset( $ItemInfo['ID'] ) ? intval( $ItemInfo['ID'] ) : '';
						$Sku           = isset( $ItemInfo['Sku'] ) ? $ItemInfo['Sku'] : '';
						$MfrPartNumber = isset( $ItemInfo['MfrPartNumber'] ) ? $ItemInfo['MfrPartNumber'] : '';
						if ( ! $ProID ) {
							$ProID = $Sku;
						}
						$Qty       = isset( $ItemInfo['OrderedQty'] ) ? intval( $ItemInfo['OrderedQty'] ) : 0;
						$UnitPrice = isset( $ItemInfo['UnitPrice'] ) ? floatval( $ItemInfo['UnitPrice'] ) : 0;
						$_product  = wc_get_product( $ProID );
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
									'customer_note' => __( 'Order from ', 'ced-umb-ebay' ) . 'Facebook',
									'created_via'   => $frameworkName,
								);

								/* ORDER CREATED IN WOOCOMMERCE */
								$order = wc_create_order( $order_data );

								/* ORDER CREATED IN WOOCOMMERCE */

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
				$ShippingAmount = isset( $orderMeta['order_detail']['shipping_details']['price'] ) ? $orderMeta['order_detail']['shipping_details']['price'] : 0;
				$ShipService    = isset( $orderMeta['order_detail']['shipping_details']['method'] ) ? $orderMeta['order_detail']['shipping_details']['method'] : '';
				if ( ! empty( $ShippingAmount ) ) {
					$Ship_params = array(
						'ShippingCost' => $ShippingAmount,
						'ShipService'  => $ShipService,
					);
					$this->add_shipping_charge( $order, $Ship_params );
				}
				$onbuy_order_total = $order->get_shipping_total() + $order->get_total();
				$order->set_total( $onbuy_order_total );
				$order->save();

				$ShippingAddress = isset( $address['shipping'] ) ? $address['shipping'] : '';
				if ( is_array( $ShippingAddress ) && ! empty( $ShippingAddress ) ) {
					if ( WC()->version < '3.0.0' ) {
						$order->set_address( $ShippingAddress, 'shipping' );
					} else {
						$type = 'shipping';
						foreach ( $ShippingAddress as $key => $value ) {
							if ( '' != $value && null != $value && ! empty( $value ) ) {
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}

				if ( isset( $OrderItemsInfo['tax'] ) && ! empty( $OrderItemsInfo['tax'] ) ) {
					$new_fee = new WC_Order_Item_Fee();
					$new_fee->set_name( 'Tax' );
					$new_fee->set_total( $OrderItemsInfo['tax'] );
					$new_fee->set_tax_class( '' );
					$new_fee->set_tax_status( 'none' );
					$new_fee->set_total_tax( $OrderItemsInfo['tax'] );
					$new_fee->save();
					$item_id = $order->add_item( $new_fee );
					$order->save();
				}

				$BillingAddress = isset( $address['billing'] ) ? $address['billing'] : '';
				if ( is_array( $BillingAddress ) && ! empty( $BillingAddress ) ) {
					if ( WC()->version < '3.0.0' ) {
						$order->set_address( $ShippingAddress, 'billing' );
					} else {
						$type = 'billing';
						foreach ( $BillingAddress as $key => $value ) {
							if ( '' != $value && null != $value && ! empty( $value ) ) {
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}
				$order->calculate_totals();
				$order->set_payment_method( 'check' );
				$order->update_status( 'processing' );
				update_post_meta( $order_id, '_ced_fmcw_order_id', $OrderNumber );
				update_post_meta( $order_id, '_fmcw_order_itemdata', $OrderItemsInfo );
				update_post_meta( $order_id, '_fmcw_order_complete_details', $orderMeta['order_detail'] );
				update_post_meta( $order_id, '_ced_fmcw_order_', 'fmcw' );
				update_post_meta( $order_id, '_ced_fmcw_order_status', 'Fetched' );
				update_post_meta( $order_id, '_ced_fmcw_order_page_id_', $page_id );
				update_post_meta( $order_id, '_ced_fmcw_merchant_page_id', $authenticated_page );
				update_post_meta( $order_id, '_fmcw_order_status', 'Pending Acknowledgement' );
				$this->ced_fmcw_acknowledge_order( $OrderNumber, $order_id, $page_id );
			}
			return $order_id;
		}
		return false;
	}

	public function ced_fmcw_acknowledge_order( $OrderNumber, $order_id, $page_id ) {
		if ( empty( $OrderNumber ) ) {
			return;
		}
			$file_request = CED_FMCW_DIRPATH . 'admin/lib/class-ced-fmcw-sendHttpRequest.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
			$ced_facebook_request = new Class_Ced_Fmcw_Send_Http_Request();
			$action               = 'webapi/rest/v1/order/acknowledge';
			$parameters           = array(
				'page_id' => $page_id,
				'orders'  => array( array( 'id' => $OrderNumber ) ),
			);

			$order_ack = $ced_facebook_request->post_request( $action, $parameters );
			if ( isset( $order_ack['success'] ) && ! empty( $order_ack['success'] ) ) {
				$status = $order_ack['data']['orders'][0]['state'];
				update_post_meta( $order_id, '_fmcw_order_status', 'Ready To Ship' );
				update_post_meta( $order_id, '_ced_fmcw_order_status', 'Acknowledged' );
			}
	}

	/**
	 * Get conditional product id.
	 *
	 * @since 1.0.0
	 * @param array $params Parameters to find product in woocommerce.
	 */
	public function umb_get_product_by( $params ) {
		global $wpdb;

		$where = '';
		if ( count( $params ) ) {
			$flag = false;
			foreach ( $params as $meta_key => $meta_value ) {
				if ( ! empty( $meta_value ) && ! empty( $meta_key ) ) {
					if ( ! $flag ) {
						$where .= 'meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
						$flag   = true;
					} else {
						$where .= ' OR meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
					}
				}
			}
			if ( $flag ) {
				$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE %s LIMIT 1", $where ) );
				if ( $product_id ) {
					return $product_id;
				}
			}
		}
		return false;
	}

	/**
	 * Function to check  if order already exists
	 *
	 * @since 1.0.0
	 * @param int $order_number Shopee Order Id.
	 */
	public function is_onbuy_order_exists( $order_number = 0 ) {
		global $wpdb;
		if ( $order_number ) {
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_ced_fmcw_order_id' AND meta_value=%s LIMIT 1", $order_number ) );
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

		$item = new WC_Order_Item_Shipping();

		$item->set_method_title( $ship_name );
		$item->set_method_id( $ship_name );
		$item->set_total( $ship_cost );
		$order->add_item( $item );

		$order->calculate_totals();
		$order->save();
	}
}