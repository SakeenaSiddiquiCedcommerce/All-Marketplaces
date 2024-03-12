<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 *Used to render the Product Fields
 *
 * @since      1.0.0
 *
 * @package    Woocommerce etsy Integration
 * @subpackage Woocommerce etsy Integration/admin/helper
 */

if ( ! class_exists( 'Ced_Etsy_Wcfm_Product_Fields' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce etsy Integration
	 * @subpackage Woocommerce etsy Integration/admin/helper
	 */
	class Ced_Etsy_Wcfm_Product_Fields {

		/**
		 * The Instace of CED_etsy_wcfm_product_fields.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_etsy_wcfm_product_fields class.
		 */
		private static $_instance;

		/**
		 * CED_etsy_wcfm_product_fields Instance.
		 *
		 * Ensures only one instance of CED_etsy_wcfm_product_fields is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_etsy_wcfm_product_fields instance.
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
		public static function get_etsy_wcfm_custom_products_fields() {
			$active_shop 	   = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
			$vendor_id   	   = !empty( ced_etsy_wcfm_get_vendor_id() ) ? ced_etsy_wcfm_get_vendor_id() : 0;
			$sections          = array();
			$shippingTemplates = array();
			$shop_id           = ced_etsy_wcfm_get_shop_id( $active_shop, $vendor_id );
			if ( ! empty( $shop_id ) ) {
				/**
				 **************************************************
				 * ETSY REFRESH TOKEN CALL BEDORE MAKING API CALL
				 * ***********************************************
				 */
				do_action( 'ced_etsy_wcfm_refresh_token', $active_shop, $vendor_id );
				$shop_sections = Ced_Etsy_WCFM_API_Request( $active_shop, $vendor_id )->get( "application/shops/{$shop_id}/sections", $active_shop );
				if ( isset( $shop_sections['count'] ) && $shop_sections['count'] >= 1 ) {
					$shop_sections = $shop_sections['results'];
					foreach ( $shop_sections as $key => $value ) {
						$sections[ $value['shop_section_id'] ] = $value['title'];
					}
				}
				$e_shpng_tmplts = Ced_Etsy_WCFM_API_Request( $active_shop, $vendor_id )->get( "application/shops/{$shop_id}/shipping-profiles", $active_shop );
				if ( isset( $e_shpng_tmplts['count'] ) && $e_shpng_tmplts['count'] >= 1 ) {
					foreach ( $e_shpng_tmplts['results'] as $key => $value ) {
						$shippingTemplates[ $value['shipping_profile_id'] ] = $value['title'];
					}
				}
			}			

			$required_fields = array(
				array(
					'type'   => '_hidden',
					'id'     => '_umb_etsy_wcfm_category',
					'fields' => array(
						'id'          => '_umb_etsy_wcfm_category',
						'label'       => __( 'Etsy Category', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Etsy category.', 'woocommerce-etsy-integration' ),
						'type'        => 'hidden',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'     => '_select',
					'id'       => '_ced_etsy_wcfm_product_list_type',
					'fields'   => array(
						'id'          => '_ced_etsy_wcfm_product_list_type',
						'label'       => __( 'Product list type', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Markup Price.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'draft'      => __( 'Draft' ),
							'active' => __( 'Active' ),
						),
						'class'       => 'wc_input_price',
					),
					'required' => false,
				),
				array(
					'type'     => '_select',
					'id'       => '_ced_etsy_wcfm_language',
					'fields'   => array(
						'id'          => '_ced_etsy_wcfm_language',
						'label'       => __( 'Etsy shop language', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Markup Price.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'en'      => __( 'English' ),
							'de'      => __( 'German' ),
							'es'      => __( 'Spanish' ),
							'fr'      => __( 'French' ),
							'it'      => __( 'Italian' ),
							'ja'      => __( 'Japanese' ),
							'nl'      => __( 'Dutch' ),
							'pl'      => __( 'Polish' ),
							'pt'      => __( 'Portuguese' ),
							'ru'      => __( 'Russian' ),
						),
						'class'       => 'wc_input_price',
					),
					'required' => false,
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_etsy_wcfm_title',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_title',
						'label'       => __( 'Title', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Title.', 'woocommerce-etsy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_etsy_wcfm_desription',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_desription',
						'label'       => __( 'Description', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Description.', 'woocommerce-etsy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_etsy_wcfm_manufacturer',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_manufacturer',
						'label'       => __( 'Manufacturer', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the manufacturer.', 'woocommerce-etsy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_shipping_profile',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_shipping_profile',
						'label'       => __( 'Shipping Profile', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Shipping profile to be used for uploading products on Etsy', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => $shippingTemplates,
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_shop_section',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_shop_section',
						'label'       => __( 'Shop Section', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the shop section.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => $sections,
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_who_made',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_who_made',
						'label'       => __( 'Who Made', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies Who made the Product.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'i_did'        => 'I did',
							'collective'   => 'Collective',
							'someone_else' => 'Someone Else',
						),
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				
				array(
					'type'   => '_text_input',
					'id'     => '_ced_etsy_wcfm_tags',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_tags',
						'label'       => __( 'Product Tags', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Product Related Tags. Enter multiple tags comma ( , ) seperated', 'woocommerce-etsy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_etsy_wcfm_materials',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_materials',
						'label'       => __( 'Product Materials', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Product Related Materials. Enter multiple tags comma ( , ) seperated', 'woocommerce-etsy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_recipient',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_recipient',
						'label'       => __( 'Preferred Audience (Recipient)', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Preferred Audience or Recipient to use the product.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'men'           => 'Men',
							'women'         => 'Women',
							'unisex_adults' => 'Unisex Adults',
							'teen_boys'     => 'Teen Boys',
							'teen_girls'    => 'Teen Girls',
							'teens'         => 'Teens',
							'boys'          => 'Boys',
							'girls'         => 'Girls',
							'children'      => 'Children',
							'baby_boys'     => 'Baby Boys',
							'baby_girls'    => 'Baby Girls',
							'babies'        => 'Babies',
							'birds'         => 'Birds',
							'cats'          => 'Cats',
							'dogs'          => 'Dogs',
							'pets'          => 'Pets',
							'not_specified' => 'Not Specified',
						),
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_occasion',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_occasion',
						'label'       => __( 'Occasion', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Occasion.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'anniversary'        => 'Anniversary',
							'baptism'            => 'Baptism',
							'bar_or_bat_mitzvah' => 'Bar or Bat Mitzvah',
							'birthday'           => 'Birthday',
							'canada_day'         => 'Canada Day',
							'chinese_new_year'   => 'Chinese New Year',
							'cinco_de_mayo'      => 'Cinco De Mayo',
							'confirmation'       => 'Confirmation',
							'christmas'          => 'Christmas',
							'day_of_the_dead'    => 'Day of the Dead',
							'easter'             => 'Easter',
							'eid'                => 'Eid',
							'engagement'         => 'Engagement',
							'fathers_day'        => 'Fathers Day',
							'get_well'           => 'Get Well',
							'graduation'         => 'Graduation',
							'halloween'          => 'Halloween',
							'hanukkah'           => 'Hanukkah',
							'housewarming'       => 'Housewarming',
							'kwanzaa'            => 'Kwanzaa',
							'prom'               => 'Prom',
							'july_4th'           => 'July 4th',
							'mothers_day'        => 'Mothers Day',
							'new_baby'           => 'New Baby',
							'new_years'          => 'New Years',
							'quinceanera'        => 'Quinceanera',
							'retirement'         => 'Retirement',
							'st_patricks_day'    => 'St. Patricks Day',
							'sweet_16'           => 'Sweet 16',
							'sympathy'           => 'Sympathy',
							'thanksgiving'       => 'Thanks Giving',
							'valentines'         => 'Valentines',
							'wedding'            => 'Wedding',
						),
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_is_supply',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_is_supply',
						'label'       => __( 'Product Supply', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Supply available', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'true'  => 'A supply or tool to make things',
							'false' => 'A finished product',
						),
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_etsy_wcfm_when_made',
					'fields' => array(
						'id'          => '_ced_etsy_wcfm_when_made',
						'label'       => __( 'Manufacturing Year', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Select manufacture time for the product', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'made_to_order' => 'Made to Order',
							'2020_2021'     => '2020-2021',
							'2010_2019'     => '2010-2019',
							'2002_2009'     => '2002-2009',
							'before_2002'     => 'Before 2002',
							'2000_2001'     => '2000-2001',
							'1990s'         => '1990s',
							'1980s'         => '1980s',
							'1970s'         => '1970s',
							'1960s'         => '1960s',
							'1950s'         => '1950s',
							'1940s'         => '1940s',
							'1930s'         => '1930s',
							'1920s'         => '1920s',
							'1910s'         => '1910s',
							'1900s'         => '1900s',
							'1800s'         => '1800s',
							'1700s'         => '1700s',
							'before_1700'   => 'Before 1700',
						),
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'     => '_select',
					'id'       => '_ced_etsy_wcfm_markup_type',
					'fields'   => array(
						'id'          => '_ced_etsy_wcfm_markup_type',
						'label'       => __( 'Markup Type', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Markup Price.', 'woocommerce-etsy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'Fixed_Increased'      => __( 'Fixed_Increased' ),
							'Percentage_Increased' => __( 'Percentage_Increased' ),
						),
						'class'       => 'wc_input_price',
					),
					'required' => false,
				),
				array(
					'type'     => '_text_input',
					'id'       => '_ced_etsy_wcfm_markup_price',
					'fields'   => array(
						'id'          => '_ced_etsy_wcfm_markup_price',
						'label'       => __( 'Markup Price', 'woocommerce-etsy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Markup Price.', 'woocommerce-etsy-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
					'required' => false,
				),
				//This is a input field for run Scheduler
				// array(
				// 	'type'     => '_select',
				// 	'id'       => '_ced_etsy_wcfm_isScheduler',
				// 	'fields'   => array(
				// 		'id'          => '_ced_etsy_wcfm_isScheduler',
				// 		'label'       => __( 'Enable scheduler', 'woocommerce-etsy-integration' ),
				// 		'desc_tip'    => true,
				// 		// 'description' => __( 'Specify the Markup Price.', 'woocommerce-etsy-integration' ),
				// 		'type'        => 'select',
				// 		'options'     => array(
				// 			'draft'      => __( 'Draft' ),
				// 			'active' => __( 'Active' ),
				// 		),
				// 		'class'       => 'wc_input_isScheduler',
				// 	),
				// 	'required' => false,
				// ),
			);
		return $required_fields;
}

		/*
		* Function to render input text html
		*/
		public function renderInputTextHTML( $attribute_id='', $attribute_name='', $categoryID='', $productID='', $marketPlace='', $attribute_description = null, $indexToUse='', $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?>
			<!-- <p class="form-field _umb_brand_field "> -->
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
					echo wc_help_tip( __( $attribute_description, 'woocommerce-etsy-integration' ) );
				}
				if ( $conditionally_required ) {
					echo wc_help_tip( __( $conditionally_required_text, 'woocommerce-etsy-integration' ) );
				}
				?>
				<!-- </p> -->
				<?php
			}

		/*
		* Function to render input text html
		*/
		public function rendercheckboxHTML( $attribute_id='', $attribute_name='', $categoryID='', $productID='', $marketPlace='', $attribute_description = null, $indexToUse='', $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {

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
			echo wc_help_tip( __( $attribute_description, 'woocommerce-etsy-integration' ) );
		}
		if ( $conditionally_required ) {
			echo wc_help_tip( __( $conditionally_required_text, 'woocommerce-etsy-integration' ) );
		}
		?>
		<!-- </p> -->
		<?php
	}

		/*
		* Function to render dropdown html
		*/
		public function renderDropdownHTML( $attribute_id='', $attribute_name='', $values='', $categoryID='', $productID='', $marketPlace='', $attribute_description = null, $indexToUse='', $additionalInfo = array( 'case' => 'product' ), $is_required = false ) {
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}
			?>
			<!-- <p class="form-field _umb_id_type_field "> -->
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
						foreach ( $values as $key => $value ) {
							if ( $previousValue == $key ) {
								echo '<option value="' . esc_attr( $key ) . '" selected>' . esc_attr( $value ) . '</option>';
							} else {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
							}
						}
						?>
					</select>
				</td>
				<?php
				if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
					echo wc_help_tip( __( $attribute_description, 'woocommerce-etsy-integration' ) );
				}
				?>
				<!-- </p> -->
				<?php
			}

			public function renderInputTextHTMLhidden( $attribute_id='', $attribute_name='', $categoryID='', $productID='', $marketPlace='', $attribute_description = null, $indexToUse='', $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
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
				echo wc_help_tip( __( $attribute_description, 'woocommerce-etsy-integration' ) );
			}
			if ( $conditionally_required ) {
				echo wc_help_tip( __( $conditionally_required_text, 'woocommerce-etsy-integration' ) );
			}
			?>

			<?php
		}

		public function get_taxonomy_node_properties( $getTaxonomyNodeProperties='' ) {

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
							'id'     => '_ced_etsy_wcfm_taxonomy_id_' . $getTaxonomyNodeProperties_value['property_id'],
							'fields' => array(
								'id'          => '_ced_etsy_wcfm_property_id_' . $getTaxonomyNodeProperties_value['property_id'],
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
							'id'     => '_ced_etsy_wcfm_taxonomy_id_' . $getTaxonomyNodeProperties_value['property_id'],
							'fields' => array(
								'id'          => '_ced_etsy_wcfm_property_id_' . $getTaxonomyNodeProperties_value['property_id'],
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

		public function get_variation_attribute_property( $variation_category_attribute_property='' ) {
			$attributesList = array();
			if ( isset( $variation_category_attribute_property ) ) {
				foreach ( $variation_category_attribute_property as $variation_category_attribute_property_key => $variation_category_attribute_property_value ) {

					$attributesList[] = array(
						'type'   => '_text_input',
						'id'     => '_ced_etsy_wcfm_variation_property_id_' . $variation_category_attribute_property_value['property_id'],
						'fields' => array(
							'id'          => '_ced_etsy_wcfm_variation_property_id_' . $variation_category_attribute_property_value['property_id'],
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
