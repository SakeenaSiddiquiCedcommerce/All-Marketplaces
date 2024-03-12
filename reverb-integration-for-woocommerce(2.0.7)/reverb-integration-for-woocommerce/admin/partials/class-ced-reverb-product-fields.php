<?php
/**
 * Product Fields file
 *
 * @package  reverb_Integration_For_Woocommerce
 * @version  1.0.0
 * @link     https://woocommerce.com/vendor/cedcommerce/
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


if ( ! class_exists( 'Ced_reverb_Product_Fields' ) ) {

	/**
	 * Ced_reverb_Product_Fields.
	 *
	 * @since    1.0.0
	 */
	class Ced_Reverb_Product_Fields {

		/**
		 * Ced_reverb_Product_Fields Instance Variable.
		 *
		 * @var $_instance
		 */
		private static $_instance;

		/**
		 * Ced_reverb_Product_Fields Instance.
		 *
		 * @since    1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function ced_reverb_get_custom_category_fields( $categorie_id ) {
			$ced_reverb_curl_file = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
			reverb_include_file( $ced_reverb_curl_file );
			$ced_reverb_curl_instance = Ced_Reverb_Curl_Request::get_instance();
			$action                   = 'categories/' . $categorie_id . '/params';

			$reverb_product_list = @file_get_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/reverb-category-list.json' );
			if ( false == $reverb_product_list ) {
				$response = $ced_reverb_curl_instance->ced_reverb_get_request( $action );
				$response = json_encode( $response );
				file_put_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/reverb-category-list.json', $response );
			} else {
				$response = $reverb_product_list;
			}
			$response = json_decode( $response, true );
			return $response;
		}

		public function ced_reverb_get_category_parameter_values( $categorie_id, $param_id ) {
			$ced_reverb_curl_file = CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
			reverb_include_file( $ced_reverb_curl_file );
			$ced_reverb_curl_instance = Ced_Reverb_Curl_Request::get_instance();
			$action                   = 'categories/' . $categorie_id . '/params/' . $param_id;

			$reverb_product_listattribute = @file_get_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/reverb-category-listattribute.json' );
			if ( false == $reverb_product_listattribute ) {
				$response = $ced_reverb_curl_instance->ced_reverb_get_request( $action );
				$response = json_encode( $response );
				file_put_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/reverb-category-listattribute.json', $response );
			} else {
				$response = $reverb_product_listattribute;
			}

			$response = json_decode( $response, true );
			return $response;
		}

		public function ced_reverb_renderDropdownHTML( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $is_required = '',$attribute_description = null ) {
			$fieldName = '_ced_reverb_' . $attribute_id;

			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?><input type="hidden" name="<?php echo esc_attr( $marketPlace ) . '[]'; ?>" value="<?php echo esc_attr( $fieldName ); ?>" />

			<td>
				<label for=""><?php print_r( $attribute_name ); ?>
				<?php
				if ( 'required' == $is_required ) {
					?>
					<span class="ced_reverb_wal_required">
						<?php
						esc_attr_e( '[Required]' )
						?>
					</span>
					<?php
				}
				?>
			</label>
			<?php
			echo wc_help_tip( $attribute_description, true );
			?>
		</td>
		<td>
			<select id="" name="<?php echo esc_attr( $fieldName ) . '[' . esc_attr( $indexToUse ) . ']'; ?>" class="select short" style="">
				<?php
				echo '<option value="" selected>' . esc_attr__( '-- Select --' ) . '</option>';
				foreach ( $values as $key => $value ) {
					if ( $previousValue == $key ) {
						if ( '' != $previousValue ) {
							echo '<option value="' . esc_attr( $key ) . '" selected>' . esc_attr( $value ) . '</option>';
						}
					} else {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
					}
				}
				?>
			</select>
		</td>
		
			<?php
		}

		/**
		 * Ced_reverb_get_custom_products_fields
		 *
		 * @param  mixed $shop_id
		 * @return void
		 */
		public static function ced_reverb_get_shipping_regions() {
			require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';

			$reverbRequest = new Ced_Reverb_Curl_Request();

			$shipping_regions = @file_get_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/reverb-shipping-regions.json' );
			if ( false == $shipping_regions ) {
				$response = $reverbRequest->ced_reverb_get_request( 'shipping/regions' );
				$response = json_encode( $response );
				file_put_contents( CED_REVERB_DIRPATH . 'admin/reverb/lib/json/reverb-shipping-regions.json', $response );

			} else {
				$response = $shipping_regions;
			}
			return $response;
		}


		public static function ced_reverb_get_custom_miscellaneous_fields() {
			$required_miscellaneousfields = array(

				array(
					'type'   => '_text_input',
					'id'     => 'title_prefix',
					'fields' => array(
						'id'          => 'title_prefix',
						'label'       => __( 'Title Prefix', 'woocommerce-reverb-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Title Prefix.', 'woocommerce-reverb-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'default_stock',
					'fields' => array(
						'id'          => 'default_stock',
						'label'       => __( 'Default Stock', 'woocommerce-reverb-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Default Stock.', 'woocommerce-reverb-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'title_suffix',
					'fields' => array(
						'id'          => 'title_suffix',
						'label'       => __( 'Title Suffix', 'woocommerce-reverb-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Title Suffix.', 'woocommerce-reverb-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
			);

			return $required_miscellaneousfields;
		}



		/**
		 * Get product custom fields for preparing
		 * product data information to send on different
		 * marketplaces accoding to there requirement.
		 *
		 * @since 1.0.0
		 */
		public function ced_reverb_get_custom_products_fields() {

			global $post;
			$fields         = array();
			$price_currency = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
			$price_currency = $price_currency . 'currencies.json';
			if ( file_exists( $price_currency ) ) {
				$price_currency = file_get_contents( $price_currency );
				$price_currency = json_decode( $price_currency, true );

			}
			foreach ( $price_currency['currencies'] as $key => $value ) {
				$price_currencytoselect[ $value ] = $value;
			}

			$saved_reverb_details = get_option( 'ced_reverb_details', array() );

			$account_type = isset( $saved_reverb_details['details']['account_type'] ) ? $saved_reverb_details['details']['account_type'] : '';

			$shipping_options = array();

			require_once CED_REVERB_DIRPATH . 'admin/reverb/lib/class-ced-reverb-curl-request.php';
			$reverbRequest = new Ced_Reverb_Curl_Request();

			$price_currency = array();
			$price_currency = $price_currencytoselect;

			$shipping_regions_to_show = array();

			$shipping_regions = $this->ced_reverb_get_shipping_regions();
			$shipping_regions = json_decode( $shipping_regions, true );

			if ( isset( $shipping_regions['shipping_regions'] ) && is_array( $shipping_regions ) && ! empty( $shipping_regions ) ) {
				foreach ( $shipping_regions['shipping_regions'] as $key => $regions ) {
					if ( isset( $regions['children'] ) && is_array( $regions['children'] ) && ! empty( $regions['children'] ) ) {
						foreach ( $regions['children'] as $key => $children ) {
							if ( isset( $children['children'] ) && is_array( $children['children'] ) && $children['children'] ) {
								foreach ( $children['children'] as $key => $subchild ) {

									$shipping_regions_to_show[ $subchild['code'] ] = $regions['name'] . '->' . $children['name'] . '->' . $subchild['name'];
								}
							} else {
								$shipping_regions_to_show[ $children['code'] ] = $regions['name'] . '->' . $children['name'];
							}
						}
					} else {
						$shipping_regions_to_show[ $regions['code'] ] = $regions['name'];
					}
				}
			}
			// to get currency information
			$folderName      = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
			$productCondtion = $folderName . 'listing_condition.json';
			if ( file_exists( $productCondtion ) ) {
				$productCondtion = file_get_contents( $productCondtion );
				$productCondtion = json_decode( $productCondtion, true );

			}
			foreach ( $productCondtion['conditions'] as $key => $value ) {
				if ( ! is_array( $value['display_name'] ) ) {
					$productCondtiontoselect[ $value['uuid'] ] = $value['display_name'];
				}
			}
			$productCondtion = array();
			$productCondtion = $productCondtiontoselect;

			// to get countory code information
			$folderName    = CED_REVERB_DIRPATH . 'admin/reverb/lib/json/';
			$countory_code = $folderName . 'countries.json';
			if ( file_exists( $countory_code ) ) {
				$countory_code = file_get_contents( $countory_code );
				$countory_code = json_decode( $countory_code, true );

			}
			$countory_codetoselect = array();
			foreach ( $countory_code['countries'] as $key => $countory_code1 ) {

				$countory_codetoselect[ $countory_code1['country_code'] ] = $countory_code1['name'];

			}
			$shipping_options  = array();

			$shipping_profiles = $reverbRequest->ced_reverb_get_request( 'shop' );

			

			if ( isset( $shipping_profiles['shipping_profiles'] ) && is_array( $shipping_profiles ) && ! empty( $shipping_profiles['shipping_profiles'] ) ) {
				$shipping_prof = $shipping_profiles['shipping_profiles'];
			}

			if ( isset( $shipping_prof ) && is_array( $shipping_prof ) && ! empty( $shipping_prof ) ) {
				foreach ( $shipping_prof as $key => $value ) {
					$shipping_options[ $value['id'] ] = $value['name'];
				}
			}

			$required_fields = array(
				array(
					'type'   => '_hidden',
					'id'     => '_umb_reverb_category',
					'fields' => array(
						'id'          => '_umb_reverb_category',
						'label'       => __( 'Category Name', 'woocommerce-reverb-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the category name.', 'woocommerce-reverb-integration' ),
						'type'        => 'hidden',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'shipping_profile_id',
					'fields' => array(
						'id'          => 'shipping_profile_id',
						'label'       => __( 'shipping profile id', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Condition', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => $shipping_options,
						'class'       => '',
					),
				),

				array(
					'type'   => '_text_input',
					'id'     => 'sku',
					'fields' => array(
						'id'          => 'sku',
						'label'       => __( 'SKU', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Unique identifier for product.', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'title',
					'fields' => array(
						'id'          => 'title',
						'label'       => __( 'TITLE', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Reverb Product Title', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'description',
					'fields' => array(
						'id'          => 'description',
						'label'       => __( 'DESCRIPTION', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Reverb Description', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'make',
					'fields' => array(
						'id'          => 'make',
						'label'       => __( 'Make', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Make.', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'model',
					'fields' => array(
						'id'          => 'model',
						'label'       => __( 'Model', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Model.', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'condition',
					'fields' => array(
						'id'          => 'condition',
						'label'       => __( 'Condition', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Condition', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => $productCondtion,
						'class'       => '',
					),
				),

				array(
					'type'   => '_select',
					'id'     => 'markup_type',
					'fields' => array(
						'id'          => 'markup_type',
						'label'       => __( 'Markup Type', 'woocommerce-reverb-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Markup Price.', 'woocommerce-reverb-integration' ),
						'type'        => 'select',
						'options'     => array(
							'Fixed_Increased'      => 'Fixed_Increased',
							'Fixed_Decreased'      => 'Fixed_Decreased',
							'Percentage_Increased' => 'Percentage_Increased',
							'Percentage_Decreased' => 'Percentage_Decreased',
						),
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'markup_price',
					'fields' => array(
						'id'          => 'markup_price',
						'label'       => __( 'Markup Price', 'woocommerce-reverb-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Markup Price.', 'woocommerce-reverb-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),

				array(
					'type'   => '_select',
					'id'     => 'is_sold_as_is',
					'fields' => array(
						'id'          => 'sold_as_is',
						'label'       => __( 'Sold As Is', 'ced-reverb' ),
						'desc_tip'    => true,
						'description' => __( 'This item is sold As-Is and cannot be returned', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'true',
							'false' => 'false',
						),
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'handmade',
					'fields' => array(
						'id'          => 'handmade',
						'label'       => __( 'Handmade', 'ced-reverb' ),
						'desc_tip'    => true,
						'description' => __( 'Handmade', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'true',
							'false' => 'false',
						),
						'class'       => '',
					),
				),

				array(
					'type'   => '_select',
					'id'     => 'publish',
					'fields' => array(
						'id'          => 'publish',
						'label'       => __( 'Publish', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Publish your listing if draft', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'true',
							'false' => 'false',
						),
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'tax_exempt',
					'fields' => array(
						'id'          => 'tax_exempt',
						'label'       => __( 'tax Exempt', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Publish your listing if draft', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'true',
							'false' => 'false',
						),
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'upc_does_not_apply',
					'fields' => array(
						'id'          => 'upc_does_not_apply',
						'label'       => __( 'UPC does not apply', 'ced-reverb' ),
						'desc_tip'    => true,
						'description' => __( 'True if a brand new product has no UPC code, ie for a handmade or custom item', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'true',
							'false' => 'false',
						),
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'offers_enabled',
					'fields' => array(
						'id'          => 'offers_enabled',
						'label'       => __( 'Offer Enable', 'ced-reverb' ),
						'desc_tip'    => true,
						'description' => __( 'Enable Offer For Buyers On Reverb', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'true',
							'false' => 'false',
						),
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'upc',
					'fields' => array(
						'id'          => 'upc',
						'label'       => __( 'UPC', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Valid UPC code', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),

				array(
					'type'   => '_text_input',
					'id'     => 'finish',
					'fields' => array(
						'id'          => 'finish',
						'label'       => __( 'Finish', 'ced-reverb' ) . '<span style= "color:green" class="ced_reverb_required"> [ ' . __( 'Guesser', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Finish', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),

				array(
					'type'   => '_select',
					'id'     => 'price_currency',
					'fields' => array(
						'id'          => 'price_currency',
						'label'       => __( 'Price Currency', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'The currency the money will be expressed in', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => $price_currency,
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'origin_country_code',
					'fields' => array(
						'id'          => 'origin_country_code',
						'label'       => __( 'Origin Country Code', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Country of origin/manufacture, ISO code (e.g: US)', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => $countory_codetoselect,
						'class'       => '',
					),
				),
				array(
					'type'   => '_select',
					'id'     => 'shipping_regions',
					'fields' => array(
						'id'          => 'shipping_regions',
						'label'       => __( 'Shipping Regions', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [ ' . __( 'Required', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Shipping Regions For Shipping Data, ISO code (e.g: US)', 'ced-reverb' ),
						'type'        => 'select',
						'options'     => $shipping_regions_to_show,
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'year',
					'fields' => array(
						'id'          => 'year',
						'label'       => __( 'Year', 'ced-reverb' ) . '<span style= "color:green" class="ced_reverb_required"> [ ' . __( 'Guesser', 'ced-reverb' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Supports many formats. Ex: 1979, mid-70s, late 90s', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'shipping_us_con',
					'fields' => array(
						'id'          => 'shipping_us_con',
						'label'       => __( 'Shipping Cost for Continental United States', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [Required]</span>',
						'desc_tip'    => true,
						'description' => __( 'Continental US Shipping Cost', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => 'shipping_other_then_us_con',
					'fields' => array(
						'id'          => 'shipping_other_then_us_con',
						'label'       => __( 'Shipping Cost for Everywhere Else', 'ced-reverb' ) . '<span style= "color:red" class="ced_reverb_required"> [Required]</span>',
						'desc_tip'    => true,
						'description' => __( 'Everywhere Else Shipping Cost', 'ced-reverb' ),
						'type'        => 'text',
						'class'       => '',
					),
				),
			);

			return $required_fields;
		}

		/**
		 * Render dropdown html in the profile edit section
		 *
		 * @since 1.0.0
		 * @param int    $attribute_id Attribute Id.
		 * @param string $attribute_name Attribute name.
		 * @param array  $values Option values.
		 * @param int    $category_id Category Id.
		 * @param int    $product_id Product Id.
		 * @param string $market_place Marketplace.
		 * @param string $attribute_description Attribute Description.
		 * @param int    $index_to_use Index to be used.
		 * @param array  $additional_info Additional data.
		 * @param bool   $is_required Whether required or not.
		 */
		public function render_dropdown_html( $attribute_id, $attribute_name, $values, $category_id, $product_id, $market_place, $index_to_use, $additional_info = array( 'case' => 'product' ), $is_required = '',$attribute_description = null ) {
			$field_name = '_ced_reverb_' . $attribute_id;

			if ( 'product' == $additional_info['case'] ) {
				$previous_value = get_post_meta( $product_id, $field_name, true );
			} else {
				$previous_value = $additional_info['value'];
			}
			?>
			<input type="hidden" name="<?php echo esc_attr( $market_place . '[]' ); ?>" value="<?php echo esc_attr( $field_name ); ?>" />

			<td>
				<label for=""><?php print_r( ucwords( strtolower( $attribute_name ) ) ); ?>
				<?php
				if ( 'required' == $is_required ) {
					?>
					<span class="ced_reverb_wal_required"><?php esc_html_e( '[Required]', 'woocommerce-reverb-integration' ); ?></span>
					<?php
				}
				?>
			</label>
			<?php
			echo wc_help_tip( $attribute_description, true );
			?>
		</td>
		<td>
			<select id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ) . '[' . esc_attr( $index_to_use ) . ']'; ?>" class="select short select2" style="">
				<?php
				$count = 0;
				echo '<option value="">' . esc_html( __( '-- Select --', 'woocommerce-reverb-integration' ) ) . '</option>';
				foreach ( $values as $key => $value ) {
					if ( $previous_value == $key ) {
						echo '<option value="' . esc_attr( $key ) . '" selected>' . esc_attr( $value ) . '</option>';
					} else {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
					}
					$count++;
				}
				?>
			</select>
		</td>

			<?php
		}

		/**
		 * Render text html in the profile edit section
		 *
		 * @since 1.0.0
		 * @param int    $attribute_id Attribute Id.
		 * @param string $attribute_name Attribute name.
		 * @param int    $category_id Category Id.
		 * @param int    $product_id Product Id.
		 * @param string $market_place Marketplace.
		 * @param string $attribute_description Attribute Description.
		 * @param int    $index_to_use Index to be used.
		 * @param array  $additional_info Additional data.
		 * @param bool   $conditionally_required Whether required or not.
		 * @param string $conditionally_required_text Conditionally required data.
		 */
		public function render_input_text_html( $attribute_id, $attribute_name, $category_id, $product_id, $market_place, $index_to_use, $additional_info = array( 'case' => 'product' ), $conditionally_required = false,$attribute_description = null ) {
			global $post,$product,$loop;
			$field_name = '_ced_reverb_' . $attribute_id;
			if ( 'product' == $additional_info['case'] ) {
				$previous_value = get_post_meta( $product_id, $field_name, true );
			} else {
				$previous_value = $additional_info['value'];
			}
			?>

			<input type="hidden" name="<?php echo esc_attr( $market_place . '[]' ); ?>" value="<?php echo esc_attr( $field_name ); ?>" />
			<td>
				<label for=""><?php print_r( ucwords( strtolower( $attribute_name ) ) ); ?>
				<?php
				if ( 'required' == $conditionally_required ) {
					?>
					<span class="ced_reverb_wal_required"><?php esc_html_e( '[ Required ]', 'woocommerce-reverb-integration' ); ?></span>
					<?php
				}
				echo wc_help_tip( $attribute_description, true );
				?>
			</label>
		</td>
		<td>
			<input class="short" style="" name="<?php echo esc_attr( $field_name . '[' . $index_to_use . ']' ); ?>" id="" value="<?php echo esc_attr( $previous_value ); ?>" placeholder="" type="text" /> 
		</td>

			<?php
		}

		/**
		 * Render text html for hidden fields in the profile edit section
		 *
		 * @since 1.0.0
		 * @param int    $attribute_id Attribute Id.
		 * @param string $attribute_name Attribute name.
		 * @param int    $category_id Category Id.
		 * @param int    $product_id Product Id.
		 * @param string $market_place Marketplace.
		 * @param string $attribute_description Attribute Description.
		 * @param int    $index_to_use Index to be used.
		 * @param array  $additional_info Additional data.
		 * @param bool   $conditionally_required Whether required or not.
		 * @param string $conditionally_required_text Conditionally required data.
		 */
		public function render_input_text_html_hidden( $attribute_id, $attribute_name, $category_id, $product_id, $market_place, $index_to_use, $additional_info = array( 'case' => 'product' ), $conditionally_required = false, $attribute_description = null ) {
			global $post,$product,$loop;
			$fieldName = $category_id . '_' . $attribute_id;

			if ( 'product' == $additional_info['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additional_info['value'];
			}

			?>

			<input type="hidden" name="<?php echo esc_attr( $market_place ) . '[]'; ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				
			</td>
			<td>
				<label></label>
				<input class="short" style="" name="<?php echo esc_attr( $fieldName ) . '[' . esc_attr( $index_to_use ) . ']'; ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="hidden" /> 
			</td>
			<?php

		}
	}
}
