<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cedcommerce_Vidaxl_Dropshipping
 * @subpackage Cedcommerce_Vidaxl_Dropshipping/admin
 * @author     cedcommerce <support@cedcommerce.com>
 */
class Cedcommerce_Vidaxl_Dropshipping_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_action( 'init', array( $this, 'plugins_loaded' ) );

	}
	public function plugins_loaded() {
		add_action( 'wp_ajax_ced_vidaXl_run_auto_image_creation', array( $this, 'ced_vidaxl_product_image_creation' ) );
		add_action( 'wp_ajax_nopriv_ced_vidaXl_run_auto_image_creation', array( $this, 'ced_vidaxl_product_image_creation' ) );
		add_action( 'wp_ajax_product_title', array( $this, 'ced_product_title' ) );
		add_action( 'wp_ajax_nopriv_product_title', array( $this, 'ced_product_title' ) );
		add_action( 'wp_ajax_set_attributes_from_vida', array( $this, 'ced_vidaxl_callback_of_set_attributes_to_products' ) );
		add_action( 'wp_ajax_ced_vidaXl_run_auto_product_syncing', array( $this, 'ced_vidaxl_enable_inventory_and_price_sync_scheduler_job' ) );
		add_action( 'wp_ajax_nopriv_ced_vidaXl_run_auto_product_syncing', array( $this, 'ced_vidaxl_enable_inventory_and_price_sync_scheduler_job' ) );
		add_action( 'wp_ajax_nopriv_set_attributes_from_vida', array( $this, 'ced_vidaxl_callback_of_set_attributes_to_products' ) );
// 		add_action('admin_init', array($this, 'ced_admin_init'));
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-vidaxl-dropshipping-process-for-import-background-images.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wp-background-process.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wp-async-request.php';
		global $process;
		$process = new Vidaxl_dropshipping_process_for_import_background_images();
	}
	/**
	 *  VidaXL ced_vidaxl_enable_inventory_and_price_sync_scheduler_job.
	 *  wp-admin/admin-ajax.php?action=ced_vidaXl_run_auto_product_syncing
	 *
	 * @since 1.0.0
	 */
	public function ced_vidaxl_enable_inventory_and_price_sync_scheduler_job() {
		$products_to_sync_price_and_inventory = get_option( 'ced_vidaXl_image_price_syncings', array() );
		if ( empty( $products_to_sync_price_and_inventory ) ) {
			$products                             = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'fields'      => 'ids',
				)
			);
			$products_to_sync_price_and_inventory = array_chunk( $products, 100 );
		}
		if ( is_array( $products_to_sync_price_and_inventory[0] ) && ! empty( $products_to_sync_price_and_inventory[0] ) ) {
			$this->ced_vidaXL_auto_sync_price_and_inventory( $products_to_sync_price_and_inventory[0] );
			unset( $products_to_sync_price_and_inventory[0] );
			$products_to_sync_price_and_inventory = array_values( $products_to_sync_price_and_inventory );
			update_option( 'ced_vidaXl_image_price_syncings', $products_to_sync_price_and_inventory );
		}
	}
	public function ced_vidaXL_auto_sync_price_and_inventory( $items ) {
	    
		if ( ! empty( $items ) ) {
			print_r($items);
			$render_authorization_data = get_option( 'ced_vidaxl_authorization_data', false );
			$api_token                 = isset( $render_authorization_data['ced_vidaxl_api_token'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_api_token'] ) : '';
			$email                     = isset( $render_authorization_data['ced_vidaxl_email'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_email'] ) : '';
			$render_settings_data      = get_option( 'ced_vidaxl_settings_data', false );
			$use_price_in_wc           = isset( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) ? sanitize_text_field( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) : '';
			$selected_region           = isset( $render_authorization_data['ced_vidaxl_order_region'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_order_region'] ) : '';
			foreach ( $items as $item_key => $product_id ) {
				$products_sku = get_post_meta( $product_id, '_sku', true );
				if ( ! empty( $products_sku ) ) {
					$curl = curl_init();
					$url  = 'https://vidaxl.cedcommerce.com/vidaxl/public/vidaxlimporter/request/getCountryData?country=' . $selected_region . '&sku=' . $products_sku . '';
					$curl = curl_init();

					curl_setopt_array(
						$curl,
						array(
							CURLOPT_URL            => $url,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING       => '',
							CURLOPT_MAXREDIRS      => 10,
							CURLOPT_TIMEOUT        => 0,
							CURLOPT_FOLLOWLOCATION => true,
							CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST  => 'GET',
							CURLOPT_HTTPHEADER     => array(
								'Cookie: PHPSESSID=uof3eid5c9eebhd3i2f3jgia35',
							),
						)
					);
					$response = curl_exec( $curl );
					curl_close( $curl );
					$response      = json_decode( $response, ARRAY_A );
					$recieved_data = isset( $response['data'] ) ? $response['data'] : '';
					if(empty($recieved_data)) {
						update_post_meta( $product_id, '_stock_status', 'outofstock' );
					} else {
						if ( ! empty( $recieved_data[0] ) && is_array( $recieved_data[0] ) ) {
							$quantity = $recieved_data[0]['quantity'];
							if ( isset( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) && ! empty( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) ) {
								if ( 'webshop' == $render_settings_data['ced_vidaxl_use_price_in_wc'] ) {
									$price = (float) $recieved_data[0]['webprice'];
								} elseif ( 'b2b' == $render_settings_data['ced_vidaxl_use_price_in_wc'] ) {
									$price = (float) $recieved_data[0]['b2b_price'];
								}
							}

							if ( isset( $render_settings_data['ced_vidaxl_markup_type'] ) && ! empty( $render_settings_data['ced_vidaxl_markup_type'] ) ) {

								if ( isset( $render_settings_data['ced_vidaxl_markup_value'] ) && ! empty( $render_settings_data['ced_vidaxl_markup_value'] ) ) {
									if ( 'fixed' == $render_settings_data['ced_vidaxl_markup_type'] ) {
										$price = $price + $render_settings_data['ced_vidaxl_markup_value'];
									} elseif ( 'percentage' == $render_settings_data['ced_vidaxl_markup_type'] ) {
										$price = ( $price + ( ( $render_settings_data['ced_vidaxl_markup_value'] / 100 ) * $price ) );
									}
								}
							}
							update_post_meta( $product_id, '_price', $price );
							update_post_meta( $product_id, '_stock', $quantity );
							update_post_meta( $product_id, '_stock_status', 'in stock' );

						}
					}
				}
			}
		}
	
	}
	
	
	/**
	 *  VidaXL ced_vidaxl_callback_of_set_attributes_to_products. remove the vidaxl from prifix
	 *  wp-admin/admin-ajax.php?action=set_attributes_from_vida
	 *  ced_vidaXL_product_attributes_chunks
	 *
	 * @since 1.0.0
	 */
	public function ced_vidaxl_callback_of_set_attributes_to_products() {
		$product_attributes_chunks_data = get_option( 'ced_vidaXL_product_attributes_chunkkk', array() );
		if ( empty( $product_attributes_chunks_data ) ) {
			$products               = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'fields'      => 'ids',
				)
			);
			$product_attributes_chunks_data = array_chunk( $products, 10 );
		}
		if ( is_array( $product_attributes_chunks_data[0] ) && ! empty( $product_attributes_chunks_data[0] ) ) {
			$this->ced_vidaXL_create_attributes_by_API( $product_attributes_chunks_data[0] );
			unset( $product_attributes_chunks_data[0] );
			$product_attributes_chunks_data = array_values( $product_attributes_chunks_data );
			update_option( 'ced_vidaXL_product_attributes_chunkkk', $product_attributes_chunks_data );
		}
	}

	public function ced_vidaXL_create_attributes_by_API($items) {
		print_r($items);
	   // die('ppp');
		if ( ! empty( $items ) ) {
			ini_set('max_execution_time',-1);
			ini_set('memory_limit',-1);
			ini_set('upload_max_filesize', '500M');
			ini_set('post_max_size', '550M');
			$render_authorization_data = get_option( 'ced_vidaxl_authorization_data', false );
			$api_token                 = isset( $render_authorization_data['ced_vidaxl_api_token'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_api_token'] ) : '';
			$email                     = isset( $render_authorization_data['ced_vidaxl_email'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_email'] ) : '';
			$render_settings_data      = get_option( 'ced_vidaxl_settings_data', false );
			$use_price_in_wc           = isset( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) ? sanitize_text_field( $render_settings_data['ced_vidaxl_use_price_in_wc'] ) : '';
			$selected_region           = isset( $render_authorization_data['ced_vidaxl_order_region'] ) ? sanitize_text_field( $render_authorization_data['ced_vidaxl_order_region'] ) : 'DE';
			foreach ( $items as $item_key => $product_id ) {
				$products_sku = get_post_meta( $product_id, '_sku', true );
				if ( ! empty( $products_sku ) ) {
					$curl = curl_init();
					$url  = 'https://vidaxl.cedcommerce.com/vidaxl/public/vidaxlimporter/request/getCountryData?country=' . $selected_region . '&sku=' . $products_sku . '';
					$curl = curl_init();

					curl_setopt_array(
						$curl,
						array(
							CURLOPT_URL            => $url,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING       => '',
							CURLOPT_MAXREDIRS      => 10,
							CURLOPT_TIMEOUT        => 0,
							CURLOPT_FOLLOWLOCATION => true,
							CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST  => 'GET',
							CURLOPT_HTTPHEADER     => array(
								'Cookie: PHPSESSID=uof3eid5c9eebhd3i2f3jgia35',
							),
						)
					);
					$response = curl_exec( $curl );
					curl_close( $curl );
					$response      = json_decode( $response, ARRAY_A );
					$recieved_data = isset( $response['data'] ) ? $response['data'] : '';
					if ( ! empty( $recieved_data[0] ) && is_array( $recieved_data[0] ) ) {
				// 		print_r($recieved_data[0]);	
						$arrayList_of_attributes_by_CSV = array('Number_of_packages', 'Parcel_or_pallet', 'Gender','Diameter','Size','Brand', 'Product_volume', 'Weight', 'Color');
						$slug_and_val = array();

						
						echo "<pre>";
// print_r($recieved_data[0]);
// Columns you want to extract
					$columnsToExtract = array('Number_of_packages', 'Parcel_or_pallet', 'Gender','Diameter','Size','Brand', 'Product_volume', 'Weight', 'Color', 'number_of_packages', 'parcel_or_pallet', 'gender','diameter','size','brand', 'product_volume', 'weight', 'color');
// Extract specific columns from the array
						$extractedData = $this->extractColumns($recieved_data, $columnsToExtract);

// Output the extracted data
						// print_r($extractedData);
						$this->ced_create_attributes( $product_id,  $extractedData); 
						// die("lpo");

						// foreach($recieved_data[0] as $key => $val) {
						// 	if(in_array($key, $arrayList_of_attributes_by_CSV) || in_array(ucfirst($key), $arrayList_of_attributes_by_CSV)) {
						// 		$this->ced_create_attributes( $product_id,  $recieved_data[0][$key], strtolower($key)); 
						// 	}
						// }
					} else {
						update_post_meta( $product_id, '_stock_status', 'outofstock' );
					}
				}
			}
		}

	}
function extractColumns($dataArray, $columnsToExtract) {
							return array_map(function ($row) use ($columnsToExtract) {
								return array_intersect_key($row, array_flip($columnsToExtract));
							}, $dataArray);
						}

	public function ced_create_attributes( $post_id, $attributeName = array(), $attribute_slug = '') {
		if (!empty($attributeName)) {
			$count = 1;
			$attributes_array              = array();
			foreach ( $attributeName[0] as $attribute => $value ) {
				$value = trim( $value );
				$value = str_replace( '_', ' ', $value );
				$value = trim( $value );
				$label = $attribute;
				$attribute = sanitize_title( $attribute );
				$attr_label  = trim( sanitize_title( 'pa_'.$attribute ) );
				$attr_label  = substr( $attr_label, 0, 29 );
							//update_post_meta( $variation_post_id, 'attribute_' . $attr, $value );

				$term = wp_insert_term(
					$value,
					strtolower($attr_label),
					array(
						'description' =>ucwords($value),
						'parent'      => '',
					)
				);
				// print_r($term);
				if ( is_object($term) && ( $term->errors ) ) {
					//do nothing.
				}elseif ( isset( $term->error_data['term_exists'] ) ) {
					$term_id = $term->error_data['term_exists'];
					wp_set_object_terms($post_id, $term_id, strtolower($attr_label),true);
				} elseif ( isset( $term['term_id'] ) ) {
					$term_id = $term['term_id'];
					wp_set_object_terms($post_id, $term_id, strtolower($attr_label),true);
				} 


				$attributes = wc_get_attribute_taxonomies();

				$slugs = wp_list_pluck( $attributes, 'attribute_name' );

				$id = 0;  
				if (  in_array( strtolower($attribute), $slugs ) ) {
					$id = (int)str_replace("id:", "", array_search(strtolower($attribute), $slugs));
				}
				// var_dump($id);

				$args = array(
					'slug'    =>  strtolower( $attr_label ),
					'name'   =>  ucwords($label),
					'type'    => 'select',
					'orderby' => 'menu_order',
					'has_archives'  => false,
					'id'  => $id,
				);
				$result = wc_create_attribute( $args );
				// print_r($result);

				$productAttributes[strtolower($attr_label)] = array(
					'name'         => strtolower($attr_label),
					'value'        => $term_id,
					'position'     => 1,
					'is_visible'   => 1,
					'is_variation' => 0,
							    'is_taxonomy'  => 1, // for some reason, this is really important       
							);
				update_post_meta( $post_id, '_product_attributes', $productAttributes ); 

				$attribute_names         = strtolower($attr_label);
				$attribute_values        = $value;
				$attribute_visibility    = 1;
				$attribute_variation     = 0;
				$attribute_position      = $count;
				

				$attribute_id   = 0;
				$attribute_name = $attribute_names;
				if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
					$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
				}
				$options = $attribute_values;
				if ( is_array( $options ) ) {
						// Term ids sent as array.
					$options = wp_parse_id_list( $options );
				} else {
					$options = wc_get_text_attributes( $options );
				}

				if ( empty( $options ) ) {
					continue;
				}
				$attributeobj = new WC_Product_Attribute();
				$attributeobj->set_id( $attribute_id );
				$attributeobj->set_name( $attribute_name );
				$attributeobj->set_options( $options );
				$attributeobj->set_position( $attribute_position );
				$attributeobj->set_visible( isset( $attribute_visibility ) );
				$attributeobj->set_variation( ! empty( $attribute_variation ) );
				$attributes_array[] = $attributeobj;
				
				$count ++;
				// break;
			}
// 			var_dump($post_id);
// 			var_dump($attributes_array);
			$product_type = 'simple';
			$classname = WC_Product_Factory::get_product_classname( $post_id, $product_type );
			$product   = new $classname( $post_id );
			if ( ! empty( $attributes_array ) ) {
				$product->set_attributes( $attributes_array );
			}
			$product->save();

			return ;
			ini_set('max_execution_time',-1);
			ini_set('memory_limit',-1);
			ini_set('upload_max_filesize', '500M');
			ini_set('post_max_size', '550M');
			// wp_insert_term( $termvalue, 'pa_'.$attr_name );

			$attributeName = ucfirst($attribute_slug);
			$attributeSlug = $attribute_slug;
			$attributeLabels = wp_list_pluck(wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name');
			$attributeWCName = array_search($attributeSlug, $attributeLabels, TRUE);
			if (! $attributeWCName) {
				$attributeWCName = wc_sanitize_taxonomy_name($attributeSlug);
			}
			
			$attributeId = wc_attribute_taxonomy_id_by_name($attributeWCName);
			if ( ! $attributeId ) {
				$taxonomyName = wc_attribute_taxonomy_name($attributeWCName);
				
				$attributeId = wc_create_attribute(array(
					'name' => $attributeName,
					'slug' => $attributeSlug,
					'type' => 'select',
					'order_by' => 'menu_order',
				));
				register_taxonomy($taxonomyName, apply_filters('woocommerce_taxonomy_objects_' . $taxonomyName, array(
					'product'
				)), apply_filters('woocommerce_taxonomy_args_' . $taxonomyName, array(
					'labels' => array(
						'name' => $attributeSlug,
					),
					'hierarchical' => FALSE,
					'show_ui' => FALSE,
					'query_var' => TRUE,
					'rewrite' => FALSE,
				)));
				$taxonomyName = wc_attribute_taxonomy_name($attributeWCName);
				
				$attributeId = wc_create_attribute(array(
					'name' => $attributeName,
					'slug' => $attributeSlug,
					'type' => 'select',
					'order_by' => 'menu_order',
				));
				register_taxonomy($taxonomyName, apply_filters('woocommerce_taxonomy_objects_' . $taxonomyName, array(
					'product'
				)), apply_filters('woocommerce_taxonomy_args_' . $taxonomyName, array(
					'labels' => array(
						'name' => $attributeSlug,
					),
					'hierarchical' => FALSE,
					'show_ui' => FALSE,
					'query_var' => TRUE,
					'rewrite' => FALSE,
				)));
				$taxonomyName = wc_attribute_taxonomy_name($attributeWCName);
				
				$attributeId = wc_create_attribute(array(
					'name' => $attributeName,
					'slug' => $attributeSlug,
					'type' => 'select',
					'order_by' => 'menu_order',
				));
				register_taxonomy($taxonomyName, apply_filters('woocommerce_taxonomy_objects_' . $taxonomyName, array(
					'product'
				)), apply_filters('woocommerce_taxonomy_args_' . $taxonomyName, array(
					'labels' => array(
						'name' => $attributeSlug,
					),
					'hierarchical' => FALSE,
					'show_ui' => FALSE,
					'query_var' => TRUE,
					'rewrite' => FALSE,
				)));
			}
			$term_id = '';
			$termName = $termvalue;
			$termSlug = $termvalue;
			$taxonomy = wc_attribute_taxonomy_name($attributeName);
			if( !empty($termName) && !empty($termSlug) ) {
				$term = get_term_by( 'slug', $termSlug, $taxonomy );
				if ( ! $term ) {
					$term 		= wp_insert_term($termName, $taxonomy, array( 'slug' => $termSlug ));
					$term 		= get_term_by('id', $term['term_id'], $taxonomy);
					$term_ids[] = $term->term_id;
				} else {
					$term_ids[] = $term->term_id;
				}
			}

			$prod_attribute = new WC_Product_Attribute();
			$prod_attribute->set_id( $attributeId );
			$prod_attribute->set_name( $taxonomy );
			$prod_attribute->set_options( $term_ids );
			$prod_attribute->set_visible( true );
			$prod_attribute->set_variation( true );
			$product_type = 'simple';
			$classname    = WC_Product_Factory::get_product_classname( $productId, $product_type );
			$product      = new $classname( $productId );
			$product_attributes = $product->get_attributes();
			$product_attributes[$attribute_slug] = $prod_attribute;
			$product->set_attributes( $product_attributes );
			$product->save();

		}
	}
	/**
	 *  VidaXL ced_vidaxl_product_image_creation.
	 *  wp-admin/admin-ajax.php?action=product_title
	 *
	 * @since 1.0.0
	 */
	function ced_product_title() {
	    $product_modified_title = get_option( 'ced_product_titles', array() );
		if ( empty( $product_modified_title ) ) {
				$products                = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => 'product',
					 'relation' => 'AND',
					 array(
					  	'meta_key'     => '_thumbnail_id',
				    	'meta_compare' => 'EXISTS',
					     ),
					    array(
					    'meta_key'     => '_knawatfibu_wcgallary',
					    'meta_compare' => 'EXISTS',
					   ),
					 'fields'          => 'ids'
				)
			);
			$product_modified_title        = array_chunk( $products, 500 );
		}
		if ( is_array( $product_modified_title[0] ) && ! empty( $product_modified_title[0] ) ) {
		    print_r($product_modified_title[0]);
			global $wpdb;
			$prefix = $wpdb->prefix;
			$table_pro_name = $prefix."posts";
			foreach($product_modified_title[0] as $key => $product_id) {
				$get_title = "SELECT `post_title` FROM $table_pro_name WHERE `ID` = '".$product_id."'";
				$product_title = $wpdb->get_var($get_title);
				$product_title = trim(str_ireplace('vidaXL','',$product_title));
				$update_product_title = "UPDATE $table_pro_name SET `post_title` = '".$product_title."' WHERE `ID` = '".$product_id."' "; 
				$updated_cutom_product_sku_data = $wpdb->query($wpdb->prepare($update_product_title));
			}
			unset( $product_modified_title[0] );
			$product_modified_title = array_values( $product_modified_title );
			update_option( 'ced_product_titles', $product_modified_title );
		}
	}
	public function ced_admin_init() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$table_pro_name = $prefix."posts";
		$update_product_status = "UPDATE $table_pro_name SET `post_status` = 'publish' WHERE `post_type` = 'product' AND `post_status` = 'draft'"; 
		$updated_cutom_product_sku_data = $wpdb->query($wpdb->prepare($update_product_status));
	}

	public function ced_vidaXL_create_image_callback( $items ) {
		global $wpdb;
		foreach($items as $items_key => $items_val) {
			$sku = get_post_meta( $items_val, '_sku', true );
			// echo $sku;
			$table_name = 'wp_ced_vidaxl_temp_product_data';
			$query = "SELECT * FROM $table_name where sku = $sku";
			$products = $wpdb->get_results( $query, ARRAY_A );
			$image_url_array = array(
				urldecode( $products[0]['image1'] ),
				urldecode( $products[0]['image2'] ),
				urldecode( $products[0]['image3'] ),
				urldecode( $products[0]['image4'] ),
				urldecode( $products[0]['image5'] ),
				urldecode( $products[0]['image6'] ),
				urldecode( $products[0]['image7'] ),
				urldecode( $products[0]['image8'] ),
				urldecode( $products[0]['image9'] ),
				urldecode( $products[0]['image10'] ),
				urldecode( $products[0]['image11'] ),
				urldecode( $products[0]['image12'] ),
			);

			$product_id  		= $items_val;
			try {
				$image_ids = array();
				foreach ( $image_url_array as $key1 => $value1 ) {
				$image_url  = $value1;	// Define the image URL here
				$image_name = basename($image_url);
				$upload_dir       = wp_upload_dir(); // Set upload folder
				// $image_data       = file_get_contents($image_url); // Get image data
				$image_url = str_replace('https', 'http', $image_url);
				$connection = curl_init();
				curl_setopt($connection, CURLOPT_URL, $image_url);
				curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
				$image_data = curl_exec($connection);	
				curl_close($connection);
				$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
				$filename         = basename( $unique_file_name ); // Create image file name
				if( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}
				// Create the image  file on the server
				file_put_contents( $file, $image_data );

				// Check image file type
				$wp_filetype = wp_check_filetype( $filename, null );

				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name( $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				// Create the attachment
				$attach_id = wp_insert_attachment( $attachment, $file, $product_id );

				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

				// Assign metadata to attachment
				wp_update_attachment_metadata( $attach_id, $attach_data );

				if ( 0 == $key1 ) {
					set_post_thumbnail( $product_id, $attach_id );
					error_log( 'Thumbnail id for feature image": ' . $attach_id );
				} else {
					$image_ids[] = $attach_id;

				}

			}	
			update_post_meta( $product_id, '_product_image_gallery', implode( ',', $image_ids ) );
			//error_log( 'Thumbnail id for gallery images": ' . $image_ids );
		} catch ( Exception $e ) {
			error_log( "Vidaxl Error log" . $e->getMessage() );
			return false;
		}
	} 
	return false;
}

public function ced_vidaXL_create_image_callback_featured( $items ) {
	print_r($items );
	// die('yui');
	global $wpdb;
	foreach($items as $items_key => $items_val) {
		$sku = get_post_meta( $items_val, '_sku', true );
		// echo $sku;
		$imageArray = array();
		$table_name = 'wp_ced_vidaxl_temp_product_data';
		$query = "SELECT * FROM $table_name where sku = $sku";
		$products = $wpdb->get_results( $query, ARRAY_A );
		$image_url_array = array(
			urldecode( $products[0]['image1'] ),
			urldecode( $products[0]['image2'] ),
			urldecode( $products[0]['image3'] ),
			urldecode( $products[0]['image4'] ),
			urldecode( $products[0]['image5'] ),
			urldecode( $products[0]['image6'] ),
			urldecode( $products[0]['image7'] ),
			urldecode( $products[0]['image8'] ),
			urldecode( $products[0]['image9'] ),
			urldecode( $products[0]['image10'] ),
			urldecode( $products[0]['image11'] ),
			urldecode( $products[0]['image12'] ),
		);
		$productId  		= $items_val;
		try {
// 			print_r($image_url_array);
// 			die('pp');
			$count = 0;
			$imageArray = array();
			foreach ( $image_url_array as $key1 => $value1 ) {
				if($key1 == 0) {
					$mainImageUrl = $value1 ;
					$mainImageArray['img_url'] = $mainImageUrl; 
					update_post_meta( $productId, "_knawatfibu_url", $mainImageArray );
				} else {
					$mainImageUrl = $value1 ;
					if(!empty($mainImageUrl)) {
						$imageArray[$count]['url'] = $mainImageUrl;
						$count++ ;
					}
				}

			}
			if( !empty( $imageArray ) )
    		{
    			update_post_meta( $productId, '_knawatfibu_wcgallary', $imageArray );
    		}
		} catch ( Exception $e ) {
			error_log( "Vidaxl Error log" . $e->getMessage() );
			return false;
		}
	}

}

	/**
	 *  VidaXL ced_google_shopping_auto_existing_product_syncing.
	 *  wp-admin/admin-ajax.php?action=ced_vidaXl_run_auto_image_creation
	 *
	 * @since 1.0.0
	 */
	public function ced_vidaxl_product_image_creation() {
		ini_set( 'max_execution_time', -1 );
		ini_set( 'memory_limit', -1 );
        // update_option('ced_vidaXl_chunk_product_featured_imageses', array());
		$products_to_create_image = get_option( 'ced_vidaXl_chunk_product_featured_image', array() );
		if ( empty( $products_to_create_image ) ) {
		    
			$products                = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => 'product',
					 'relation' => 'AND',
					 array(
					  	'meta_key'     => '_thumbnail_id',
				    	'meta_compare' => 'EXISTS',
					     ),
					    array(
					    'meta_key'     => '_knawatfibu_wcgallary',
					    'meta_compare' => 'EXISTS',
					   ),
					 'fields'          => 'ids'
				)
			);
			$products_to_create_image        = array_chunk( $products, 10 );
		}
// 		print_r($products_to_create_image);
// 		die('pp');
		if ( is_array( $products_to_create_image[0] ) && ! empty( $products_to_create_image[0] ) ) {
		    
			$this->ced_vidaXL_create_image_callback_featured( $products_to_create_image[0] );
		  //  print_r($products_to_create_image[0]);
			unset( $products_to_create_image[0] );
			$products_to_create_image = array_values( $products_to_create_image );
// 			echo '----';
// 			print_r($products_to_create_image);
			update_option( 'ced_vidaXl_chunk_product_featured_image', $products_to_create_image );
		}		
	}

	public function my_vidaXl_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['ced_vidaXl_image_10min'] ) ) {
			$schedules['ced_vidaXl_image_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_vidaXl_image_15min'] ) ) {
			$schedules['ced_vidaXl_image_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_vidaXl_image_20min'] ) ) {
			$schedules['ced_vidaXl_image_20min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 20 minutes' ),
			);
		}
		return $schedules;
	}
	public function ced_vidaxl_admin_menu() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'CedCommerce', 'cedcommerce-vidaxl-dropshipping' ), __( 'CedCommerce', 'cedcommerce-vidaxl-dropshipping' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), plugins_url( 'cedcommerce-vidaxl-dropshipping/images/ced-logo.png' ), 12 );
			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );
				}
			}
		}
	}
	/**
	 * cedcommerce_vidaxl_dropshipping_admin ced_vidaxl_add_marketplace_menus_to_array.
	 *
	 * @since 1.0.0
	 * @param array $menus Marketplace menus.
	 */
	public function ced_vidaxl_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'VidaXL Dropshipping',
			'slug'            => 'cedcommerce-vidaxl-dropshipping',
			'menu_link'       => 'ced_vidaxl_dropshipping',
			'instance'        => $this,
			'function'        => 'ced_vidaxl_main_page',
			'card_image_link' => CED_VIDAXL_DROPSHIPPING_URL . 'images/vidaxllogo.png',
		);
		return $menus;
	}

	/**
	 * cedcommerce_vidaxl_dropshipping_admin ced_marketplace_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_marketplace_listing_page() {

		$active_marketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
			require CED_VIDAXL_DROPSHIPPING_DIRPATH . 'admin/partials/marketplaces.php';
		}
	}
	/**
	 * cedcommerce_vidaxl_dropshipping_admin ced_vidaxl_main_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_vidaxl_main_page() {
		if ( isset( $_GET['tab'] ) ) {
			$file = CED_VIDAXL_DROPSHIPPING_DIRPATH . 'admin/partials/' . $_GET['tab'] . '.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
		} else {
			$active_marketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
				require CED_VIDAXL_DROPSHIPPING_DIRPATH . 'admin/partials/configuration-view.php';
			}
		}
	}
	/**
	 * cedcommerce_vidaxl_dropshipping_admin ced_vidaxl_download_csv.
	 *
	 * @since 1.0.0
	 */
	public function ced_vidaxl_download_csv() {
		global $wpdb;
		$table_name = 'wp_ced_vidaxl_temp_product_data';
		$wpdb->query( 'TRUNCATE TABLE ' . $table_name );

		ini_set( 'max_execution_time', -1 );
		ini_set( 'memory_limit', -1 );
		$list = array(
			'US'     => 'http://transport.productsup.io/d88ebe68f8d10ecf2704/channel/188219/vidaXL_us_dropshipping.csv',
			'FR'     => 'http://transport.productsup.io/48ab79321df6c10b2211/channel/188040/vidaXL_fr_dropshipping.csv',
			'SE'     => 'http://transport.productsup.io/fca2664692a2f654afd0/channel/188042/vidaXL_se_dropshipping.csv',
			'DK'     => 'http://transport.productsup.io/de8254c69e698a08e904/channel/188044/vidaXL_dk_dropshipping.csv',
			'IT'     => 'http://transport.productsup.io/bd06690795dfeb1b0f28/channel/188046/vidaXL_it_dropshipping.csv',
			'AU'     => 'http://transport.productsup.io/b1565d67ca14cebf617c/channel/188048/vidaXL_au_dropshipping.csv',
			'CH(DE)' => 'http://transport.productsup.io/e9ba40e8e3597b1588a0/channel/188050/
			vidaXL_ch_de_dropshipping.csv',
			'CH(FR)' => 'http://transport.productsup.io/e9ba40e8e3597b1588a0/channel/188052/
			vidaXL_ch_fr_dropshipping.csv',
			'NL'     => 'http://transport.productsup.io/cf7871c3eb59dc3fd0e5/channel/188055/vidaXL_nl_dropshipping.csv',
			'DE'     => 'http://transport.productsup.io/8227eda6a7fa10d0d996/channel/188057/vidaXL_de_dropshipping.csv',
			'BE(NL)' => 'http://transport.productsup.io/beb10746e58fc0fa8ef8/channel/188060/
			vidaXL_be_nl_dropshipping.csv',
			'BE(FR)' => 'http://transport.productsup.io/beb10746e58fc0fa8ef8/channel/188062/
			vidaXL_be_fr_dropshipping.csv',
			'ES'     => 'http://transport.productsup.io/7947c7becca20572b9b6/channel/188064/vidaXL_es_dropshipping.csv',
			'HR'     => 'http://transport.productsup.io/77b598618c840f97d89a/channel/188067/vidaXL_hr_dropshipping.csv',
			'RO'     => 'http://transport.productsup.io/b6ba4135929198a068a9/channel/188075/vidaXL_ro_dropshipping.csv',
			'LT'     => 'http://transport.productsup.io/ea90965b409295177e8d/channel/188078/vidaXL_lt_dropshipping.csv',
			'GR'     => 'http://transport.productsup.io/f500c29c85ad4b7d500c/channel/188080/vidaXL_gr_dropshipping.csv',
			'PT'     => 'http://transport.productsup.io/3ae5497075215d3afaf1/channel/188082/vidaXL_pt_dropshipping.csv',
			'AT'     => 'http://transport.productsup.io/9e92912654ad834d7822/channel/188085/vidaXL_at_dropshipping.csv',
			'SI'     => 'http://transport.productsup.io/9116925bbb8c92887cae/channel/188087/vidaXL_si_dropshipping.csv',
			'NO'     => 'http://transport.productsup.io/292c03b1c8daf8e11913/channel/188092/vidaXL_no_dropshipping.csv',
			'Uk'     => 'http://transport.productsup.io/4f373569130aa7bb51c5/channel/188214/vidaXL_uk_dropshipping.csv',
			'BG'     => 'http://transport.productsup.io/460dae360c319251a1f2/channel/188216/vidaXL_bg_dropshipping.csv',
			'LV'     => 'http://transport.productsup.io/f64b98ba93ceeefb8418/channel/188229/vidaXL_lv_dropshipping.csv',
			'CR'     => 'http://transport.productsup.io/5d371734fbb1a91a8253/channel/188232/vidaXL_cz_dropshipping.csv',
			'PL'     => 'http://transport.productsup.io/d2fc87189709ca0590a6/channel/188220/vidaXL_pl_dropshipping.csv',
			'HU'     => 'http://transport.productsup.io/642816fd3f155760666d/channel/188224/vidaXL_hu_dropshipping.csv',
			'IE'     => 'http://transport.productsup.io/f7fb4926fed0c7dc291b/channel/188233/vidaXL_ie_dropshipping.csv',
			'EE'     => 'http://transport.productsup.io/a5ee3ec8cb8b96527314/channel/188234/vidaXL_ee_dropshipping.csv',
			'FI'     => 'http://transport.productsup.io/ca19bd84b792df4ba57a/channel/188225/vidaXL_fi_dropshipping.csv',
			'SK'     => 'http://transport.productsup.io/6651a3e97d339f544a23/channel/188228/vidaXL_sk_dropshipping.csv',
		);

		$feed_language = isset( $_POST['feed_language'] ) ? $_POST['feed_language'] : '';

		if ( ! empty( $list[ $feed_language ] ) ) {
			$wpuploadDir = wp_upload_dir();
			$baseDir     = $wpuploadDir['basedir'];
			$uploadDir   = $baseDir . '/vidaxl_dropship';
			if ( ! is_dir( $uploadDir ) ) {
				mkdir( $uploadDir, 0777, true );
			}
			$file = $uploadDir . '/vidaxl_dropship_feed_' . $feed_language . '.csv';
			if ( ! file_exists( $file ) ) {
				$data = file_get_contents( $list[ $feed_language ] );
				$wH   = fopen( $uploadDir . '/vidaxl_dropship_feed_' . $feed_language . '.csv', 'w+' );
				fwrite( $wH, $data );
				chmod( $uploadDir . '/vidaxl_dropship_feed_' . $feed_language . '.csv', 0777 );
				$file = fopen( $file, 'r' );
			}
			echo json_encode(
				array(
					'status'  => 200,
					'message' => __(
						'Downloaded',
						'cedcommerce-vidaxl-dropshipping'
					),
				)
			);
		}
		wp_die();

	}


	/**
	 * class-cedcommerce-vidaxl-dropshipping-admin ced_vidaxl_process_csv.
	 *
	 * @since    1.0.0
	 */
	public function ced_vidaxl_process_csv() {
		$feed_language = isset( $_POST['feed_language'] ) ? $_POST['feed_language'] : '';
		if ( ! empty( $feed_language ) ) {
			ini_set( 'max_execution_time', -1 );
			ini_set( 'memory_limit', -1 );
			set_time_limit( 0 );
			global $wpdb;
			$feed_category   = isset( $_POST['feed_category'] ) ? $_POST['feed_category'] : array();
			$wpuploadDir     = wp_upload_dir();
			$baseDir         = $wpuploadDir['basedir'];
			$uploadDir       = $baseDir . '/vidaxl_dropship';
			$filePath        = $uploadDir . '/vidaxl_dropship_feed_' . $feed_language . '.csv';
			$file_handle     = fopen( $filePath, 'r' );
			$heading_columns = fgets( $file_handle );
			$heading_array   = str_getcsv( $heading_columns );
			$row             = 0;
			$csv             = array();
			$batchsize       = 100;
			$path_array      = array();
			while ( ( $data = fgets( $file_handle ) ) !== false ) {
				$line = str_getcsv( $data );
				if ( $row % $batchsize == 0 ) :
					$file = fopen( $uploadDir . '/vidaxl_dropship_feed_' . $feed_language . $row . '.csv', 'w' );
				endif;
				$product_data = array();
				foreach ( $heading_array  as $k => $v ) {
					$product_data[ $v ] = $line[ $k ];
				}
				$sku              = urlencode( $product_data['SKU'] );
				$title            = urlencode( $product_data['Title'] );
				$category         = urlencode( $product_data['Category'] );
				$b2b_price        = (float) urlencode( $product_data['B2B price'] );
				$stock            = urlencode( $product_data['Stock'] );
				$description      = urlencode( $product_data['Description'] );
				$properties       = urlencode( $product_data['Properties'] );
				$weight           = urlencode( $product_data['Weight'] );
				$image1           = urlencode( $product_data['Image 1'] );
				$image2           = urlencode( $product_data['Image 2'] );
				$image3           = urlencode( $product_data['Image 3'] );
				$image4           = urlencode( $product_data['Image 4'] );
				$image5           = urlencode( $product_data['Image 5'] );
				$image6           = urlencode( $product_data['Image 6'] );
				$image7           = urlencode( $product_data['Image 7'] );
				$image8           = urlencode( $product_data['Image 8'] );
				$image9           = urlencode( $product_data['Image 9'] );
				$image10          = urlencode( $product_data['Image 10'] );
				$image11          = urlencode( $product_data['Image 11'] );
				$image12          = urlencode( $product_data['Image 12'] );
				$ean              = urlencode( $product_data['EAN'] );
				$html_description = urlencode( $product_data['HTML_description'] );
				$category_id      = urlencode( $product_data['Category_id'] );
				$webshop_price    = (float) urlencode( $product_data['Webshop price'] );

				$json = "'$sku','$title', '$category', '$b2b_price', '$stock', '$description', '$properties', '$weight', '$image1', '$image2', '$image3', '$image4', '$image5', '$image6', '$image7', '$image8', '$image9', '$image10', '$image11', '$image12', '$ean', '$html_description', '$category_id', '$webshop_price'";
				fwrite( $file, $json . PHP_EOL );

				if ( $row % $batchsize == 0 ) {
					$path = $uploadDir . '/vidaxl_dropship_feed_' . $feed_language . $row . '.csv';

					array_push( $path_array, $path );
				}

				$row++;
			}
			fclose( $file );
			fclose( $file_handle );
			$this->ced_vidaxl_insert_csv_to_dbtable( $path_array, $feed_language );
		}
		wp_die();
	}

	/**
	 * class-cedcommerce-vidaxl-dropshipping-admin ced_vidaxl_insert_csv_to_dbtable
	 *
	 * @since    1.0.0
	 */
	public function ced_vidaxl_insert_csv_to_dbtable( $path_arrays, $feed_language ) {
		ini_set( 'max_execution_time', -1 );
		ini_set( 'memory_limit', -1 );
		set_time_limit( 0 );
		global $wpdb;
		foreach ( $path_arrays as $path_array ) {
			$handle_open = fopen( $path_array, 'r' );
				$counter = 0;

				$sql = 'INSERT INTO wp_ced_vidaxl_temp_product_data(sku,title,category,b2b_price,stock,description,properties,weight,image1,image2,image3,image4,image5,image6,image7,image8,image9,image10,image11,image12,ean,html_description,category_id,webshop_price) VALUES ';
			while ( ( $readline = fgets( $handle_open ) ) !== false ) {
				$sql .= "($readline),";
				$counter++;
			}
				$sql = substr( $sql, 0, strlen( $sql ) - 1 );
			if ( $wpdb->query( $sql ) ) {
				unlink( $path_array );
			} else {

			}
			fclose( $handle_open );
		}
		update_option( 'wc_settings_tab_vidaxl_product_temp_data', $feed_language );
		echo json_encode(
			array(
				'status'  => 200,
				'message' => __(
					'Processed',
					'cedcommerce-vidaxl-dropshipping'
				),
			)
		);
		wp_die();
	}

	/**
	 * Function to create the categories.
	 *
	 * @since    1.0.0
	 */
	public function ced_vidaxl_create_category( $category ) {
		$i = 0;
		foreach ( $category as $cat ) {
			if ( $i == 0 ) {
				wp_insert_term(
					$cat,
					'product_cat',
					array(
						'description' => $cat,
						'parent'      => '',
					)
				);
				$term      = term_exists( $cat, 'product_cat' );
				$parent_id = $term['term_id'];
			} else {
				wp_insert_term(
					$cat,
					'product_cat',
					array(
						'description' => $cat,
						'parent'      => $parent_id,
					)
				);
				$term      = term_exists( $cat, 'product_cat' );
				$parent_id = $term['term_id'];
			}
			$i++;
		}
		return $parent_id;
	}
	/**
	 * Starting the Import Product Process.
	 *
	 * @since    1.0.0
	 */
	public function ced_vidaxl_start_import_process() {
		ini_set( 'max_execution_time', -1 );
		ini_set( 'memory_limit', -1 );
		set_time_limit( 0 );
		$feed_category       = isset( $_POST['feed_category'] ) ? $_POST['feed_category'] : array();
		$updated_data_fields = isset( $_POST['updated_data'] ) ? $_POST['updated_data'] : array();
		$imprt_type          = isset( $_POST['imprt_draft'] ) ? $_POST['imprt_draft'] : 'draft';

		$min_price = isset( $_POST['min_price'] ) ? $_POST['min_price'] : 0;
		$max_price = isset( $_POST['max_price'] ) ? $_POST['max_price'] : 0;

		$min_price = (float) $min_price;
		$max_price = (float) $max_price;

		// print_r($min_price);
		// print_r($max_price);

		$feed_category = implode( ',', $feed_category );

		global $wpdb;
		$table_name = 'wp_ced_vidaxl_temp_product_data';

		$query = "SELECT * FROM $table_name where category_id IN($feed_category)";

		if ( $max_price != 0 ) {
			$query .= " AND webshop_price BETWEEN $min_price AND $max_price";
		}
		$products = $wpdb->get_results( $query, ARRAY_A );

		$total_products   = 0;
		$products_created = 0;
		$updated_products = 0;
		global $process;
		foreach ( $products as $product ) {
			if ( ! empty( $product['category'] ) ) {
				$category = array_map( 'trim', explode( '>', urldecode( $product['category'] ) ) );
				$term_id  = $this->ced_vidaxl_create_category( $category );

				// Checking product exist or not
				$get_listing_ids = get_posts(
					array(
						'numberposts'  => -1,
						'post_type'    => array( 'product' ),
						'post_status'  => array( 'publish', 'draft' ),
						'meta_key'     => '_sku',
						'meta_value'   => urldecode( $product['sku'] ),
						'meta_compare' => '=',
					)
				);
				$get_listing_ids = wp_list_pluck( $get_listing_ids, 'ID' );

				if ( empty( $get_listing_ids ) ) {
					$post    = array(
						'post_title'   => urldecode( $product['title'] ),
						'post_content' => urldecode( $product['description'] ) . urldecode( $product['html_description'] ),
						'post_status'  => $imprt_type,
						'post_parent'  => '',
						'post_type'    => 'product',
					);
					$post_id = wp_insert_post( $post );
					if ( ! $post_id ) {
						return false;
					}
					wp_set_post_terms( $post_id, $term_id, 'product_cat' );

					update_post_meta( $post_id, '_sku', urldecode( $product['sku'] ) );
					wp_set_object_terms( $post_id, 'simple', 'product_type' );
					update_post_meta( $post_id, '_visibility', 'visible' );

					update_post_meta( $post_id, '_stock', urldecode( $product['stock'] ) );
					if ( urldecode( $product['stock'] ) > 0 ) {
						update_post_meta( $post_id, '_manage_stock', 'yes' );
						update_post_meta( $post_id, '_stock_status', 'instock' );
					} else {
						update_post_meta( $post_id, '_stock_status', 'outofstock' );
						update_post_meta( $post_id, '_manage_stock', 'yes' );
						update_post_meta( $post_id, '_stock', 0 );
					}
					update_post_meta( $post_id, '_weight', urldecode( $product['weight'] ) );

					$settings_data = get_option( 'ced_vidaxl_settings_data', true ); // Setting page data

					$price = (float) urldecode( $product['webshop_price'] );

					if ( isset( $settings_data['ced_vidaxl_use_price_in_wc'] ) && ! empty( $settings_data['ced_vidaxl_use_price_in_wc'] ) ) {
						if ( 'webshop' == $settings_data['ced_vidaxl_use_price_in_wc'] ) {
							$price = (float) $product['webshop_price'];
						} elseif ( 'b2b' == $settings_data['ced_vidaxl_use_price_in_wc'] ) {
							$price = (float) $product['b2b_price'];
						}
					}

					if ( isset( $settings_data['ced_vidaxl_markup_type'] ) && ! empty( $settings_data['ced_vidaxl_markup_type'] ) ) {

						if ( isset( $settings_data['ced_vidaxl_markup_value'] ) && ! empty( $settings_data['ced_vidaxl_markup_value'] ) ) {
							if ( 'fixed' == $settings_data['ced_vidaxl_markup_type'] ) {
								$price = $price + $settings_data['ced_vidaxl_markup_value'];
							} elseif ( 'percentage' == $settings_data['ced_vidaxl_markup_type'] ) {
								$price = ( $price + ( ( $settings_data['ced_vidaxl_markup_value'] / 100 ) * $price ) );
							}
						}
					}

					update_post_meta( $post_id, '_regular_price', $price );
					update_post_meta( $post_id, '_price', $price );

					// Background processing for images
					$image_url_array = array(
						urldecode( $product['image1'] ),
						urldecode( $product['image2'] ),
						urldecode( $product['image3'] ),
						urldecode( $product['image4'] ),
						urldecode( $product['image5'] ),
						urldecode( $product['image6'] ),
						urldecode( $product['image7'] ),
						urldecode( $product['image8'] ),
						urldecode( $product['image9'] ),
						urldecode( $product['image10'] ),
						urldecode( $product['image11'] ),
						urldecode( $product['image12'] ),
					);
					$image_url_array = array_filter( $image_url_array );


					// custome code for create the images --- 
					$imageArray = array();
					foreach ( $image_url_array as $key1 => $value1 ) {
						if($key1 == 0) {
							$mainImageUrl = $value1 ;
							$mainImageArray['img_url'] = $mainImageUrl; 
							update_post_meta( $post_id, "_knawatfibu_url", $mainImageArray );
						} else {
							$mainImageUrl = $value1 ;
							if(!empty($mainImageUrl)) {
								$imageArray[$count]['url'] = $mainImageUrl;
								$count++ ;
							}
						}

					}
					if( !empty( $imageArray ) )
		    		{
		    			update_post_meta( $post_id, '_knawatfibu_wcgallary', $imageArray );
		    		}
					// ending of custom code for create the images -- 
					$image_data      = array(
						'product_id' => $post_id,
						'images_url' => $image_url_array,
					);

					// $process->push_to_queue( $image_data );

					unset( $image_data );
					$products_created++;
				} else {
					if ( ! empty( $updated_data_fields ) ) {

						foreach ( $updated_data_fields as $field ) {
							if ( $field == 'title' ) {
								$post_update = array(
									'ID'         => $get_listing_ids[0],
									'post_title' => urldecode( $product['title'] ),
								);
								wp_update_post( $post_update );
							}
							if ( $field == 'description' ) {
								$post_update = array(
									'ID'           => $get_listing_ids[0],
									'post_content' => urldecode( $product['description'] ),
								);
								wp_update_post( $post_update );
							}
							if ( $field == 'price' ) {
								update_post_meta( $get_listing_ids[0], '_regular_price', urldecode( $product['webshop_price'] ) );
								update_post_meta( $get_listing_ids[0], '_price', urldecode( $product['webshop_price'] ) );
							}
							if ( $field == 'stock' ) {
								update_post_meta( $get_listing_ids[0], '_stock', urldecode( $product['stock'] ) );
								if ( urldecode( $product['stock'] ) > 0 ) {
									update_post_meta( $get_listing_ids[0], '_manage_stock', 'yes' );
									update_post_meta( $get_listing_ids[0], '_stock_status', 'instock' );
								} else {
									update_post_meta( $get_listing_ids[0], '_stock_status', 'outofstock' );
									update_post_meta( $get_listing_ids[0], '_manage_stock', 'yes' );
									update_post_meta( $get_listing_ids[0], '_stock', 0 );
								}
							}
							if ( $field == 'category' ) {
								if ( ! empty( $product['category'] ) ) {
									$category = array_map( 'trim', explode( '>', urldecode( $product['category'] ) ) );
									$term_id  = $this->ced_vidaxl_create_category( $category );
									wp_set_post_terms( $get_listing_ids[0], $term_id, 'product_cat' );
								}
							}
						}
						$updated_products++;
					}
				}
			}
			$total_products++;
		}
		// $process->save()->dispatch();
		echo json_encode(
			array(
				'status'           => 200,
				'message'          => __(
					'product_created',
					'cedcommerce-vidaxl-dropshipping'
				),
				'products_updated' => $updated_products,
				'products_created' => $products_created,
				'total_products'   => $total_products,
			)
		);
		wp_die();
	}

	// Fetching the categories
	public function ced_vidaxl_fetch_categories() {
		ini_set( 'max_execution_time', -1 );
		ini_set( 'memory_limit', -1 );
		set_time_limit( 0 );
		global $wpdb;
		$table_name = 'wp_ced_vidaxl_temp_product_data';
		$results    = $wpdb->get_results( "SELECT DISTINCT category, category_id FROM $table_name", ARRAY_A );
		if ( ! empty( $results ) ) {
			foreach ( $results as $k => $v ) {
				$option_data .= "<option value='" . urldecode( $v['category_id'] ) . "'>" . urldecode( $v['category'] ) . '</option>';
			}
		}
		echo json_encode(
			array(
				'status'  => 200,
				'message' => __(
					'cat_generated',
					'cedcommerce-vidaxl-dropshipping'
				),
				'data'    => $option_data,
			)
		);
		wp_die();
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cedcommerce_Vidaxl_Dropshipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cedcommerce_Vidaxl_Dropshipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cedcommerce-vidaxl-dropshipping-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cedcommerce_Vidaxl_Dropshipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cedcommerce_Vidaxl_Dropshipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$ajax_nonce     = wp_create_nonce( 'ced-ebay-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,

		);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cedcommerce-vidaxl-dropshipping-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ced_vidaxl_obj', $localize_array );
		wp_enqueue_script( $this->plugin_name . '_select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Function to check data in table avail or not.
	 *
	 * @since    1.0.0
	 */
	public function ced_vidaxl_check_temp_db_table_status() {

		$temp_table_status = get_option( 'wc_settings_tab_vidaxl_product_temp_data', true );
		echo json_encode(
			array(
				'status' => $temp_table_status,
			)
		);
		wp_die();
	}

}
