<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Used to render the Product Fields
 *
 * @since      1.0.0
 *
 * @package    Woocommerce Tokopedia Integration
 * @subpackage Woocommerce Tokopedia Integration/admin/helper
 */

if ( ! class_exists( 'Ced_Tokopedia_Product_Fields' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce Tokopedia Integration
	 * @subpackage Woocommerce Tokopedia Integration/admin/helper
	 */
	class Ced_Tokopedia_Product_Fields {

		/**
		 * The Instace of CED_tokopedia_product_fields.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_tokopedia_product_fields class.
		 */
		private static $_instance;

		/**
		 * CED_tokopedia_product_fields Instance.
		 *
		 * Ensures only one instance of CED_tokopedia_product_fields is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_tokopedia_product_fields instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Get product custom fields for preparing
		 * product data information to send on different
		 * marketplaces accoding to there requirement.
		 *
		 * @since 1.0.0
		 * @param string $type  required|framework_specific|common
		 * @param bool   $ids  true|false
		 * @return array  fields array
		 */
		public static function get_custom_products_fields() {
			global $wpdb;
			$active_shop             = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
			$saved_tokopedia_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_tokopedia_accounts WHERE `shop_id`=%d", $active_shop ), 'ARRAY_A' );

			$arr = json_decode( $saved_tokopedia_details [0]['shop_data'] , true );
			if ( is_array( $arr ) && isset( $arr ) && !empty( $arr ) ) {
				foreach ( $arr as $key => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $key1 => $value1 ) {
							 $etalase_id[ $value1->etalase_id ] = $value1->etalase_id;
						}
					}
				}
			}

			$etalase_id = isset( $etalase_id ) ? $etalase_id : array();

			$required_fields = array(
				array(
					'type'   => '_hidden',
					'id'     => '_umb_tokopedia_category',
					'fields' => array(
						'id'          => '_umb_tokopedia_category',
						'label'       => __( 'Tokopedia Category', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Tokopedia category.', 'woocommerce-tokopedia-integration' ),
						'type'        => 'hidden',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_title',
					'fields' => array(
						'id'          => '_ced_tokopedia_title',
						'label'       => __( 'Product Title ', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product title apply on the each product which one is having this profile', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_description',
					'fields' => array(
						'id'          => '_ced_tokopedia_description',
						'label'       => __( 'Product Description', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product Description apply on the each product which one is having this profile', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_markup_price',
					'fields' => array(
						'id'          => '_ced_tokopedia_markup_price',
						'label'       => __( 'Markup Price', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product Price apply on the each product which one is having this profile', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_tokopedia_markup_type',
					'fields' => array(
						'id'          => '_ced_tokopedia_markup_type',
						'label'       => __( 'Markup Type', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product price mark Type', 'woocommerce-tokopedia-integration' ),
						'type'        => 'select',
						'options'     => array(
							'fixed'      => 'Fixed',
							'percentage' => 'Percentage',
						),
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_stock',
					'fields' => array(
						'id'          => '_ced_tokopedia_stock',
						'label'       => __( ' Product Stock ', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product quantity will apply Except woocommerce products Quantity', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_tokopedia_product_status',
					'fields' => array(
						'id'          => '_ced_tokopedia_product_status',
						'label'       => __( 'Product status', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product Status Must be selected', 'woocommerce-tokopedia-integration' ),
						'type'        => 'select',
						'options'     => array(
							'UNLIMITED'        => 'Unlimited',
							'LIMITED'        => 'Limited',
							'EMPTY'        => 'Empty',
						),
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_cc_rate',
					'fields' => array(
						'id'          => '_ced_tokopedia_cc_rate',
						'label'       => __( 'Currency conversion Rate', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Currency conversion Rate is require for convert the price into IDR.', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_weight',
					'fields' => array(
						'id'          => '_ced_tokopedia_weight',
						'label'       => __( 'Weight', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Weight is require for the product while uploading on the tokopedia and this weight unit will be apply on the product .', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_height',
					'fields' => array(
						'id'          => '_ced_tokopedia_height',
						'label'       => __( 'Height', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Height is require for the product while uploading on the tokopedia and this weight unit will be apply on the product .', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_width',
					'fields' => array(
						'id'          => '_ced_tokopedia_width',
						'label'       => __( 'Width', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Width is require for the product while uploading on the tokopedia and this weight unit will be apply on the product .', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_length',
					'fields' => array(
						'id'          => '_ced_tokopedia_length',
						'label'       => __( 'Length', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Length is require for the product while uploading on the tokopedia and this weight unit will be apply on the product .', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_tokopedia_weight_unit',
					'fields' => array(
						'id'          => '_ced_tokopedia_weight_unit',
						'label'       => __( 'Weight Unit', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Weight is require for the product while uploading on the tokopedia and this weight unit will be apply on the product .', 'woocommerce-tokopedia-integration' ),
						'type'        => 'select',
						'options'     => array(
							'GR'        => 'Gram',
							'KG'        => 'kilogram',
						),
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_tokopedia_product_condition',
					'fields' => array(
						'id'          => '_ced_tokopedia_product_condition',
						'label'       => __( 'Product Condition', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product condition for upload on the Tokopedia Shop.', 'woocommerce-tokopedia-integration' ),
						'type'        => 'select',
						'options'     => array(
							'NEW'        => 'New',
							'USED'       => 'Used',
						),
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_tokopedia_min_order',
					'fields' => array(
						'id'          => '_ced_tokopedia_min_order',
						'label'       => __( 'Min orders', 'woocommerce-tokopedia-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Minimum orders to be purchages for the products', 'woocommerce-tokopedia-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
			);

			return $required_fields;
		}

		/*
		* Function to render input text html
		*/
		public function renderInputTextHTML( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?>
			<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				<label for=""><?php echo esc_attr( $attribute_name ); ?></label>
				<?php
				if ( $conditionally_required ) {
					?>
					<span style="color: red; margin-left:5px; ">*</span>
					<?php
				}
				?>
			</td>

			<td>
				<input class="short" style="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="text" /> 
			</td>
			<?php
			if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
				echo wc_help_tip( __( $attribute_description, 'woocommerce-tokopedia-integration' ) );
			}
			if ( $conditionally_required ) {
				echo wc_help_tip( __( $conditionally_required_text, 'woocommerce-tokopedia-integration' ) );
			}
			?>
			<?php
		}

		/*
		* Function to render input text html
		*/
		public function rendercheckboxHTML( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {

			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$checked = ( 'yes' == $additionalInfo['value'] ) ? 'checked="checked"' : '';
			}

			?>
			<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				<label for=""><?php echo esc_attr( $attribute_name ); ?>
				</label>
			</td>
			<td>
				<input class="short" style="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" id="" value="<?php echo esc_attr( 'yes' ); ?>" placeholder="" <?php echo esc_attr( $checked ); ?> type="checkbox" /> 
			</td>
			<?php
			if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
				echo wc_help_tip( __( $attribute_description, 'woocommerce-tokopedia-integration' ) );
			}
			if ( $conditionally_required ) {
				echo wc_help_tip( __( $conditionally_required_text, 'woocommerce-tokopedia-integration' ) );
			}
			?>
			<?php
		}

		/*
		* Function to render dropdown html
		*/
		public function renderDropdownHTML( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $is_required = false ) {
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}
			?>
			<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				<label for=""><?php echo esc_attr( $attribute_name ); ?></label>
				<?php
				if ( $is_required ) {
					?>
					<span style="color: red; margin-left:5px; ">*</span>
					<?php
				}
				?>
			</td>
			<td>
				<select id="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" class="select short" style="">
					<?php
					echo '<option value="">-- Select --</option>';
					if (is_array( $values) && isset( $values ) && !empty( $values ) ) {
						foreach ( $values as $key => $value ) {
							if ( $previousValue == $key ) {
								echo '<option value="' . esc_attr( $key ) . '" selected>' . esc_attr( $value ) . '</option>';
							} else {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
							}
						}
					}
					?>
				</select>
			</td>
			<?php
			if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
				echo wc_help_tip( __( $attribute_description, 'woocommerce-tokopedia-integration' ) );
			}
			?>
			<?php
		}

		public function renderInputTextHTMLhidden( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}
			?>
			<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
			</label>
		</td>
		<td>
			<label></label>
			<input class="short" style="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="hidden" /> 
		</td>
			<?php
			if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
				echo wc_help_tip( __( $attribute_description, 'woocommerce-tokopedia-integration' ) );
			}
			if ( $conditionally_required ) {
				echo wc_help_tip( __( $conditionally_required_text, 'woocommerce-tokopedia-integration' ) );
			}
			?>

			<?php
		}

		public function get_taxonomy_node_properties( $getTaxonomyNodeProperties ) {

			$taxonomyList = array();
			if ( isset( $getTaxonomyNodeProperties ) && is_array( $getTaxonomyNodeProperties ) && ! empty( $getTaxonomyNodeProperties ) ) {
				foreach ( $getTaxonomyNodeProperties as $getTaxonomyNodeProperties_key => $getTaxonomyNodeProperties_value ) {
					$type             = '';
					$taxonomy_options = array();
					if ( isset( $getTaxonomyNodeProperties_value['possible_values'] ) && is_array( $getTaxonomyNodeProperties_value['possible_values'] ) && ! empty( $getTaxonomyNodeProperties_value['possible_values'] ) ) {
						$type = '_select';
						foreach ( $getTaxonomyNodeProperties_value['possible_values'] as $possible_values_key => $possible_value ) {
							$taxonomy_options[ $possible_value['value_id'] ] = $possible_value['name'];
						}
					} else {
						$type = '_text_input';
					}
					if ( isset( $type ) && '_select' != $type ) {
						$taxonomyList[] = array(
							'type'   => $type,
							'id'     => '_ced_tokopedia_taxonomy_id_' . $getTaxonomyNodeProperties_value['property_id'],
							'fields' => array(
								'id'          => '_ced_tokopedia_property_id_' . $getTaxonomyNodeProperties_value['property_id'],
								'label'       => $getTaxonomyNodeProperties_value['name'],
								'desc_tip'    => true,
								'description' => /*$variation_category_attribute_property_value['description']*/ $getTaxonomyNodeProperties_value['name'],
								'type'        => 'text',
								'class'       => 'wc_input_price',
							),
						);
					} else {
						$taxonomyList[] = array(
							'type'   => $type,
							'id'     => '_ced_tokopedia_taxonomy_id_' . $getTaxonomyNodeProperties_value['property_id'],
							'fields' => array(
								'id'          => '_ced_tokopedia_property_id_' . $getTaxonomyNodeProperties_value['property_id'],
								'label'       => $getTaxonomyNodeProperties_value['name'],
								'desc_tip'    => true,
								'description' => /* $variation_category_attribute_property_value['description']*/ $getTaxonomyNodeProperties_value['name'],
								'type'        => 'text',
								'options'     => $taxonomy_options,
								'class'       => 'wc_input_price',
							),
						);
					}
				}
			}
			return $taxonomyList;
		}

		public function get_variation_attribute_property( $variation_category_attribute_property ) {
			$attributesList = array();
			if ( isset( $variation_category_attribute_property ) ) {
				foreach ( $variation_category_attribute_property as $variation_category_attribute_property_key => $variation_category_attribute_property_value ) {

					$attributesList[] = array(
						'type'   => '_text_input',
						'id'     => '_ced_tokopedia_variation_property_id_' . $variation_category_attribute_property_value['property_id'],
						'fields' => array(
							'id'          => '_ced_tokopedia_variation_property_id_' . $variation_category_attribute_property_value['property_id'],
							'label'       => $variation_category_attribute_property_value['name'],
							'desc_tip'    => true,
							'description' => $variation_category_attribute_property_value['description'],
							'type'        => 'text',
							'class'       => 'wc_input_price',
						),
					);
				}
			}
			return $attributesList;
		}

	}
}
