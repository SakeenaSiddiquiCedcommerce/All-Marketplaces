<?php
/**
 * Gettting order related data
 *
 * @package  reverb_Integration_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Ced_reverb_Order
 *
 * @since 1.0.0
 * @param object $_instance Class instance.
 */
class Ced_Reverb_Order {

	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */

	public static $_instance;

	/**
	 * Ced_reverb_Order Instance.
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
	 * Ced_reverb_Order construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependency();
	}

	/**
	 * Ced_reverb_Order loading dependency.
	 *
	 * @since 1.0.0
	 */
	public function load_dependency() {
		$ced_reverb_curl_file = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
		reverb_include_file( $ced_reverb_curl_file );
		$this->ced_reverb_curl_instance = Ced_Reverb_Curl_Request::get_instance();
	}

	/**
	 * Function for creating a local order
	 *
	 * @since 1.0.0
	 * @param array $orders Order Details.
	 */
	public function createLocalOrder( $orders ) {
		
		if ( is_array( $orders ) && ! empty( $orders ) ) {
			$OrderItemsInfo = array();
			$neworder       = array();
			foreach ( $orders as $order ) {

				if('pending_review' == $order['status']){

					continue;
				}
				
				$ShipToFirstName     = isset( $order['shipping_address']['name'] ) ? $order['shipping_address']['name'] : '';
				$CustomerPhoneNumber = isset( $order['shipping_address']['phone'] ) ? $order['shipping_address']['phone'] : '';
				$ShipToAddress1      = isset( $order['shipping_address']['street_address'] ) ? $order['shipping_address']['street_address'] : '';
				$ShipToAddress2      = isset( $order['shipping_address']['extended_address'] ) ? $order['shipping_address']['extended_address'] : '';

				$ShipToCityName = isset( $order['shipping_address']['locality'] ) ? $order['shipping_address']['locality'] : '';

				$ShipToStateCode   = isset( $order['shipping_address']['region'] ) ? $order['shipping_address']['region'] : '';
				$ShipToZipCode     = isset( $order['shipping_address']['postal_code'] ) ? $order['shipping_address']['postal_code'] : '';
				$ShipToCountryCode = isset( $order['shipping_address']['country_code'] ) ? $order['shipping_address']['country_code'] : '';

				$buyerFirstName = isset( $order['buyer_first_name'] ) ? $order['buyer_first_name'] : '';
				$buyerLastName  = isset( $order['buyer_last_name'] ) ? $order['buyer_last_name'] : '';

				if(empty($buyerFirstName) || "" == $buyerFirstName || empty($ShipToFirstName) || "" == $ShipToFirstName ){

					continue;
				}

				$ShippingAddress  = array(
					'first_name' => $ShipToFirstName,
					'phone'      => $CustomerPhoneNumber,
					'address_1'  => $ShipToAddress1,
					'address_2'  => $ShipToAddress2,
					'city'       => $ShipToCityName,
					'state'      => $ShipToStateCode,
					'postcode'   => $ShipToZipCode,
					'country'    => $ShipToCountryCode,
				);
				$BillToFirstName  = $buyerFirstName;
				$BillToLastName   = $buyerLastName;
				$BillEmailAddress = '';
				$BillPhoneNumber  = $CustomerPhoneNumber;

				$BillingAddress = array(
					'first_name' => $BillToFirstName,
					'last_name'  => $BillToLastName,
					'phone'      => $CustomerPhoneNumber,
					'address_1'  => $ShipToAddress1,
					'address_2'  => $ShipToAddress2,
					'city'       => $ShipToCityName,
					'state'      => $ShipToStateCode,
					'postcode'   => $ShipToZipCode,
					'country'    => $ShipToCountryCode,
				);
				$address        = array(
					'shipping' => $ShippingAddress,
					'billing'  => $BillingAddress,
				);
				$OrderNumber    = $order['order_number'];
				$ordertotal     = isset( $order['total']['amount'] ) ? $order['total']['amount'] : '';
				$ordersubtotal  = isset( $order['amount_product_subtotal']['amount'] ) ? $order['amount_product_subtotal']['amount'] : '';
				$ordershipping  = isset( $order['shipping']['amount'] ) ? $order['shipping']['amount'] : '';
				$amount_tax     = isset( $order['amount_tax']['amount'] ) ? $order['amount_tax']['amount'] : '';
				$productsku     = isset( $order['sku'] ) ? $order['sku'] : '';
				$product_id     = isset( $order['product_id'] ) ? $order['product_id'] : '';
				$quantity       = isset( $order['quantity'] ) ? $order['quantity'] : '';
				$order_details  = array(
					'id'         => $OrderNumber,
					'total'      => $ordertotal,
					'subtotal'   => $ordersubtotal,
					'shipping'   => $ordershipping,
					'amount_tax' => $amount_tax,

				);
				$product_data = array(
					'sku'        => $productsku,
					'product_id' => $product_id,
					'quantity'   => $quantity,
				);

				$merchant_order_id = $OrderNumber;
				$purchase_order_id = $OrderNumber;
				$fulfillment_node  = '';
				$order_detail      = isset( $order ) ? $order : array();

				$reverb_order_meta = array(
					'merchant_order_id' => $merchant_order_id,
					'purchaseOrderId'   => $purchase_order_id,
					'fulfillment_node'  => $fulfillment_node,
					'order_detail'      => $order_detail,
				);

				$order_data = array(
					'address'         => $address,
					'order_details'   => $order_details,
					'product_details' => $product_data,
				);

				$order_id = $this->create_order( $order_data, 'Reverb', $reverb_order_meta, $order );

				


				if ( ! $order_id ) {
					continue;
				}

				$woocomerce_order = wc_get_order( $order_id );

				$ced_reverb_default_order_statuses = array(
					'unpaid'          => 'wc-pending',
					'payment_pending' => 'wc-pending',
					'pending_review'  => 'wc-processing',
					'paid'            => 'wc-processing',
					'blocked'         => 'wc-failed',
					'received'        => 'wc-completed',
					'picked_up'       => 'wc-completed',
					'shipped'         => 'wc-completed',
					'refunded'        => 'wc-refunded',
					'cancelled'       => 'wc-cancelled',
				);

				$ced_reverb_plugin_order_statuses = array(
					'unpaid'          => 'Fetched',
					'payment_pending' => 'Fetched',
					'pending_review'  => 'Fetched',
					'paid'            => 'Fetched',
					'blocked'         => 'Fetched',
					'received'        => 'Completed',
					'picked_up'       => 'Completed',
					'shipped'         => 'Completed',
					'refunded'        => 'Returned',
					'cancelled'       => 'Cancelled',
				);

				$order_status = isset( $order['status'] ) ? $order['status'] : '';

				$ced_reverb_mapped_order_statuses = get_option( 'ced_reverb_mapped_order_statuses', array() );
				if ( ! empty( $woocomerce_order ) && is_object( $woocomerce_order ) ) {
					$woo_order_status = isset( $ced_reverb_mapped_order_statuses[ $order_status ] ) ? $ced_reverb_mapped_order_statuses[ $order_status ] : $ced_reverb_default_order_statuses[ $order_status ];

					update_post_meta( $order_id, '_reverb_umb_order_status', $ced_reverb_plugin_order_statuses[ $order_status ] );
					$woocomerce_order->update_status( $woo_order_status );
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
	public function create_order( $order_data = array(), $frameworkName = 'Reverb', $orderMeta = array(), $reverb_order_details = '' ) {
		global $cedreverbhelper;
		$order_data_temp = $order_data;
		$OrderNumber     = isset( $order_data['order_details']['id'] ) ? $order_data['order_details']['id'] : 0;
		$order_id        = $this->is_umb_order_exists( $OrderNumber );

		if ( $order_id ) {
			return $order_id;
		}
		$order_id         = '';
		$order_created    = false;
		$productsToUpdate = array();

		$Sku = isset( $order_data['product_details']['sku'] ) ? $order_data['product_details']['sku'] : '';

		$listing_id = $order_data['product_details']['product_id'];

		if ( empty( $Sku ) ) {
			
    		$orders_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%s  group by `post_id` ", 'ced_reverb_listing_id', $listing_id ), 'ARRAY_A' );
            
    	    if(empty($orders_post_ids)){
    	       
    	       return;
    	       
    	    }
    	    
    	    $ProID = $orders_post_ids[0]['post_id'];
		}

		$UnitPrice     = isset( $order_data['order_details']['subtotal'] ) ? $order_data['order_details']['subtotal'] : '';
		$ShippingCost  = isset( $order_data['order_details']['shipping'] ) ? $order_data['order_details']['shipping'] : '';
		$OrderItemstax = isset( $order_data['order_details']['amount_tax'] ) ? $order_data['order_details']['amount_tax'] : '';
		$OrderItemstax = (float) $OrderItemstax;

		if(!empty($Sku)){

			$params = array( '_sku' => $Sku );

			$ProID = wc_get_product_id_by_sku( $Sku );
			
		}

		if ( ! $ProID ) {
			return;
		}
		$productsToUpdate[] = $ProID;
		$Qty                = isset( $order_data['product_details']['quantity'] ) ? intval( $order_data['product_details']['quantity'] ) : 0;

		$_product = wc_get_product( $ProID );

		if ( is_wp_error( $_product ) ) {
			return false;
		} elseif ( is_null( $_product ) ) {
			return false;
		} elseif ( ! $_product ) {
			return false;
		} else {

			if ( ! $order_created ) {
				$order_data = array(
					/**
 					* Filter hook for filtering ORDER status.
 					* @since 1.0.0
 					*/
					'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
					'customer_note' => __( 'Order from ', 'ced-umb' ) . $frameworkName,
					'created_via'   => $frameworkName,
				);

				/* ORDER CREATED IN WOOCOMMERCE */
				$order = wc_create_order( $order_data );

				/* ORDER CREATED IN WOOCOMMERCE */

				if ( is_wp_error( $order ) ) {
					return false;
				} elseif ( false === $order ) {
					return false;
				} else {
					$order_id      = $order->get_id();
					$order_created = true;
				}
			}
			$_product->set_price( $UnitPrice );
			$order->add_product( $_product, $Qty );
			$order->calculate_totals( false );
			$order->save();

			foreach ( $order->get_items() as $item_id => $item ) {
				if ( $item['product_id'] == $ProID ) {
					// code...
					$new_product_price = $UnitPrice; // A static replacement product price
					$product_quantity  = (int) $item->get_quantity(); // product Quantity
					// The new line item price
					$new_line_item_price = $new_product_price * $product_quantity;
					// Set the new price
					$item->set_subtotal( $new_line_item_price );
					$item->set_total( $new_line_item_price );
					// Make new taxes calculations
					$item->save(); // Save line item data
				}
			}
		}
		$order->save();
		$ShippingAddress = isset( $order_data_temp['address']['shipping'] ) ? $order_data_temp['address']['shipping'] : '';
		if ( is_array( $ShippingAddress ) ) {

			$order->set_address( $ShippingAddress, 'shipping' );
		}

		$BillingAddress = isset( $order_data_temp['address']['billing'] ) ? $order_data_temp['address']['billing'] : '';
		if ( is_array( $BillingAddress ) ) {
			$order->set_address( $BillingAddress, 'billing' );
		}
		$order->calculate_totals( false );
		$order->save();

		if ( ! $order_created ) {
			return false;
		}

		if ( isset( $ShippingCost ) ) {
			$Ship_params = array(
				'ShippingCost' => $ShippingCost,
				'ShipService'  => 'standard',
			);
			$this->addShippingCharge( $order, $Ship_params );
		}

		$this->add_order_tax( $order, $OrderItemstax );

		$reverb_order_total = $order->get_shipping_total() + $order->get_total();
		$order->set_total( $reverb_order_total );
		$order->save();

		update_post_meta( $order_id, '_reverb_order_details', $reverb_order_details );
		update_post_meta( $order_id, '_ced_reverb_order_id', $OrderNumber );

		update_post_meta( $order_id, '_ced_reverb_order' . rifw_environment(), 1 );
		update_post_meta( $order_id, '_reverb_umb_order_status', 'Fetched' );
		update_post_meta( $order_id, 'ced_reverb_order_marketplace', $frameworkName );

		if ( count( $orderMeta ) ) {
			foreach ( $orderMeta as $oKey => $oValue ) {
				update_post_meta( $order_id, $oKey, $oValue );
			}
		}

		if ( $order_id ) {
			return $order_id;
		} else {
			return false;
		}

	}

	/**
	 * Check if order already imported or not.
	 *
	 * @since 1.0.0
	 */
	public function is_umb_order_exists( $order_number = 0 ) {
		global $wpdb;
		if ( $order_number ) {
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_ced_reverb_order_id' AND meta_value=%s LIMIT 1", $order_number ) );
			if ( $order_id ) {
				return $order_id;
			}
		}
		return false;
	}

	/**
	 * Get conditional product id.
	 *
	 * @since 1.0.0
	 */
	public function umb_get_product_by( $params ) {
		global $wpdb;

		$where = '';
		if ( count( $params ) ) {
			$Flag = false;
			foreach ( $params as $meta_key => $meta_value ) {
				if ( ! empty( $meta_value ) && ! empty( $meta_key ) ) {
					if ( ! $Flag ) {
						$where .= 'meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
						$Flag   = true;
					} else {
						$where .= ' OR meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
					}
				}
			}
			if ( $Flag ) {
				$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE %s LIMIT 1", $where ) );

				if ( $product_id ) {
					return $product_id;
				}
			}
		}
		return false;
	}


	/**
	 * Get product id by.
	 *
	 * @since 1.0.0
	 */
	public function get_product_id_by( $params = array() ) {
		global $wpdb;

		$where = '';
		if ( count( $params ) ) {
			$Flag = false;
			foreach ( $params as $meta_key => $meta_value ) {
				if ( ! empty( $meta_value ) && ! empty( $meta_key ) ) {
					if ( ! $Flag ) {
						$where .= 'meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
						$Flag   = true;
					} else {
						$where .= ' OR meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
					}
				}
			}
			if ( $Flag ) {
				$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE %s LIMIT 1", $where ) );
				if ( $product_id ) {
					return $product_id;
				}
			}
		}
		return false;
	}

	/**
	 * Add shipping charge
	 *
	 * @since 1.0.0
	 */
	public static function addShippingCharge( $order, $ShipParams = array() ) {
		$ShipName = isset( $ShipParams['ShipService'] ) ? esc_attr( $ShipParams['ShipService'] ) : 'UMB Default Shipping';
		$ShipCost = isset( $ShipParams['ShippingCost'] ) ? floatval( $ShipParams['ShippingCost'] ) : 0;
		$ShipTax  = isset( $ShipParams['ShippingTax'] ) ? floatval( $ShipParams['ShippingTax'] ) : 0;

		if ( WC()->version < '3.0.0' ) {
			$item_id = wc_add_order_item(
				$order->id,
				array(
					'order_item_name' => $ShipName,
					'order_item_type' => 'shipping',
				)
			);
		} else {
			$item_id = wc_add_order_item(
				$order->get_id(),
				array(
					'order_item_name' => $ShipName,
					'order_item_type' => 'shipping',
				)
			);
		}

		if ( ! $item_id ) {
			return false;
		}

		wc_add_order_item_meta( $item_id, 'method_id', $ShipName );
		wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $ShipCost ) );

		if ( WC()->version < '3.0.0' ) {
			// Update total
			$order->set_total( $order->order_shipping + wc_format_decimal( $ShipCost ), 'shipping' );
		} else {
			// Update total
			$order_id       = $order->get_id();
			$order_shipping = get_post_meta( $order_id, '_order_shipping', true );
			$order->set_shipping_total( $order_shipping + wc_format_decimal( $ShipCost ) );
			$order->save();
		}
		return $item_id;
	}

	public function add_order_tax( $order, $extradiscounted ) {

		$item_fee = new WC_Order_Item_Fee();
						$item_fee->set_name( 'Tax' );
						$fee_amount = (float) $extradiscounted;

						$item_fee->set_total( $fee_amount );
						$order->add_item( $item_fee );
						$order->calculate_totals( false );
						$order->save();
	}
}
