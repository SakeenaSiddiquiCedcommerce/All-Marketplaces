(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$( document ).on(
		'click',
		'.ced_reverb_parent_element',
		function(){
			if ($( this ).find( '.ced_reverb_instruction_icon' ).hasClass( "dashicons-arrow-down-alt2" )) {
				$( this ).find( '.ced_reverb_instruction_icon' ).removeClass( "dashicons-arrow-down-alt2" );
				$( this ).find( '.ced_reverb_instruction_icon' ).addClass( "dashicons-arrow-up-alt2" );
			} else if ($( this ).find( '.ced_reverb_instruction_icon' ).hasClass( "dashicons-arrow-up-alt2" )) {
				$( this ).find( '.ced_reverb_instruction_icon' ).addClass( "dashicons-arrow-down-alt2" );
				$( this ).find( '.ced_reverb_instruction_icon' ).removeClass( "dashicons-arrow-up-alt2" );
			}
			$( this ).next( '.ced_reverb_child_element' ).toggle();
		}
	);

	$( document ).on(
		'click',
		'#ced_reverb_update_api_keys',
		function(){
			ced_reverb_process_configuration_details( true , 'validate' );
		}
	);

	function ced_reverb_process_configuration_details(can_ajax,operation) {
		if ( can_ajax ) {
			$( '.ced_reverb_loader' ).show();
			var client_id , environment;
			var parsed_response = '';
			var message         = '';
			var status          = '';

			client_id   = $( '#ced_reverb_client_id' ).val();
			environment = $( '#ced_reverb_environment' ).val();

			$.ajax(
				{
					url : ajaxurl,
					data :{
						ajaxnonce : ced_reverb_admin_obj.ajaxnonce,
						client_id : client_id,
						action : 'ced_reverb_process_api_keys',
						operation : operation,
						environment : environment
					},
					type : 'POST',
					success: function( response ) {
						$( '.ced_reverb_loader' ).hide();
						parsed_response = jQuery.parseJSON( response );
						message         = parsed_response.message;
						status          = parsed_response.status;
						ced_reverb_display_notice( message , status );
					}
				}
			);
		}
	}

	$( document ).on(
		'change',
		'#ced_reverb_auto_upload_categories' ,
		function() {
			var categories = $( this ).val();
			var operation  = 'remove';
			if ( $( this ).is( ':checked' ) ) {
				operation = 'save';
			}
			$( '.ced_reverb_loader' ).show();
			categories = JSON.parse( categories );
			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_auto_upload_categories',
						categories : categories,
						operation:operation,
					},
					type : 'POST',
					dataType : 'JSON',
					success: function( response ) {
						$( '.ced_reverb_loader' ).hide();
						console.log( response );
						ced_reverb_display_notice( response.message ,response.status );
					}
				}
			);

		}
	);

	jQuery( document ).on(
		'click' ,
		'.ced_reverb_single_product' ,
		function(e) {
			e.preventDefault();
			let listing_id       = jQuery( this ).data( 'listing-id' );
			let listing_id_array = new Array();
			listing_id_array.push( listing_id );
			imrportProducts( listing_id_array );
		}
	);

	/**
	 * To Perforn Bulk action of the ced_reverb_import product section / tab
	 */
	jQuery( document ).on(
		'click',
		'#ced_reverb_import_product_bulk_optration' ,
		function(e){
			e.preventDefault();
			let operation = jQuery( '.ced_reverb_bulk-import-action-selector' ).val();
			if ( operation <= 0 ) {
				  var notice = '';
				  notice    += '<div class="notice notice-error"><p>Please Select Operation To Be Performed</p></div>';
				  jQuery( '#ced_reverb_notices' ).html( notice );
			} else {

				let operation                = jQuery( '.ced_reverb_bulk-import-action-selector' ).val();
				let reverb_import_listing_id = new Array();
				// Get all checked ids in the array from input box
				jQuery( '.reverb_import_listing_id:checked' ).each(
					function(){
						reverb_import_listing_id.push( jQuery( this ).val() );
					}
				);
				// import Product by Bulk action
				imrportProducts( reverb_import_listing_id , operation );
			}
		}
	);

	function imrportProducts( reverb_import_listing_id , operation ) {
		if ( reverb_import_listing_id == '') {
			let notice = '';
			notice    += '<div class="notice notice-error"><p>No Products Selected To Import</p></div>';
			jQuery( ".success-admin-notices" ).append( notice );
		}
		jQuery( '.ced_reverb_loader' ).show();
		jQuery.post(
			ced_reverb_admin_obj.ajaxurl,
			{
				'action'                    : 'ced_reverb_import_product_by_wp_list',
				ajax_nonce:ced_reverb_admin_obj.ajaxnonce,
				'operation_to_be_performed' : operation,
				'listing_ids'               : reverb_import_listing_id,
			},
			function( response ) {

				let sliced_array_value = reverb_import_listing_id.slice( 1 );
				if ( sliced_array_value != 0 ) {
					imrportProducts( sliced_array_value ,operation );
				} else {
					jQuery( '.ced_reverb_loader' ).hide();
					var successHtml = '<div class="notice notice-success umb_current_cat_prof ced_reverb_current_notice"><p>Product Impoted Successfully !</p></div>';
					jQuery( '#ced_reverb_notices' ).html( successHtml );
					$( 'html, body' ).animate(
						{
							scrollTop: parseInt( $( "body" ).offset().top )
						},
						2000
					);
					setTimeout( function(){ jQuery( '.ced_reverb_current_notice' ).remove(); }, 4000 );
				}
			}
		);
	}

	jQuery( document ).on(
		'change',
		'#ced_reverb_map_order_status',
		function(e) {

			var reverb_order_status = jQuery( this ).attr( 'data-reverb-order-status' );
			var woo_order_status    = jQuery( this ).val();

			$( ".ced_reverb_loader" ).show();
			$.ajax(
				{
					url : ajaxurl,
					type : 'post',
					data : {
						ajax_nonce:ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_map_order_status',
						woo_order_status:woo_order_status,
						reverb_order_status:reverb_order_status,
					},
					success : function(response) {
						$( '.ced_reverb_loader' ).hide();
						ced_reverb_display_notice( response );
					}
				}
			);
		}
	);

	$( document ).on(
		'keyup' ,
		'#ced_reverb_search_product_name' ,
		function() {
			var keyword = $( this ).val();
			if ( keyword.length < 3 ) {
				var html = '';
				html    += '<li>Please enter 3 or more characters.</li>';
				$( document ).find( '.ced-reverb-search-product-list' ).html( html );
				$( document ).find( '.ced-reverb-search-product-list' ).show();
				return;
			}
			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce :ced_reverb_admin_obj.ajaxnonce,
						keyword : keyword,
						action : 'ced_reverb_search_product_name',
					},
					type:'POST',
					dataType : 'JSON',
					success : function( response ) {
						// parsed_response = $.parseJSON( response );
						$( document ).find( '.ced-reverb-search-product-list' ).html( response.html );
						$( document ).find( '.ced-reverb-search-product-list' ).show();
					}
				}
			);
		}
	);

		$( document ).on(
			'click' ,
			'.ced_reverb_searched_product' ,
			function() {
				$( '.ced_reverb_loader' ).show();
				var post_id = $( this ).data( 'post-id' );
				$.ajax(
					{
						url : ajaxurl,
						data : {
							ajax_nonce :ced_reverb_admin_obj.ajaxnonce,
							post_id : post_id,
							action : 'ced_reverb_get_product_metakeys',
						},
						type:'POST',
						dataType : 'JSON',
						success : function( response ) {
							$( '.ced_reverb_loader' ).hide();
							// parsed_response = jQuery.parseJSON( response );
							$( document ).find( '.ced-reverb-search-product-list' ).hide();
							$( ".ced_reverb_render_meta_keys_content" ).html( response.html );
							$( ".ced_reverb_render_meta_keys_content" ).show();
						}
					}
				);
			}
		);

	$( document ).on(
		'change',
		'.ced_reverb_meta_key',
		function(){
			$( '.ced_reverb_loader' ).show();
			var metakey = $( this ).val();
			var operation;
			if ( $( this ).is( ':checked' ) ) {
				operation = 'store';
			} else {
				operation = 'remove';
			}

			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce :ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_process_metakeys',
						metakey : metakey ,
						operation : operation,
					},
					type : 'POST',
					success: function(response)
				{
						$( '.ced_reverb_loader' ).hide();
					}
				}
			);
		}
	);

	$( document ).on(
		'click' ,
		'.ced_reverb_navigation' ,
		function() {
			$( '.ced_reverb_loader' ).show();
			var page_no = $( this ).data( 'page' );
			$( '.ced_reverb_metakey_body' ).hide();
			window.setTimeout( function() {$( '.ced_reverb_loader' ).hide()},500 );
			$( document ).find( '.ced_reverb_metakey_list_' + page_no ).show();
		}
	);

	function ced_reverb_display_notice( message = '' , status = 200){
		var classes = '';
		var notice  = '';
		if ( status == 400 ) {
			classes = 'notice-error';
		} else {
			classes = 'notice-success';
		}
		notice += '<div class="notice ' + classes + ' ced_reverb_notices_content">';
		notice += '<p>' + message + '</p>';
		notice += '</div>';
		$( '#ced_reverb_notices' ).html( notice );
		$( 'html, body' ).animate(
			{
				scrollTop: parseInt( $( "body" ).offset().top )
			},
			2000
		);
	}

	$( document ).on(
		'change',
		'.ced_reverb_select_store_category_checkbox',
		function(){
			var store_category_id = $( this ).attr( 'data-categoryID' );
			if ( $( this ).is( ':checked' ) ) {
				$( '#ced_reverb_' + store_category_id ).show( 'slow' );
			} else {
				$( '#ced_reverb_' + store_category_id ).hide( 'slow' );
			}
		}
	);

	$( document ).on(
		'change',
		'.ced_reverb_select_category',
		function(){

			var store_category_id             = $( this ).attr( 'data-storeCategoryID' );
			var reverb_store_id               = $( this ).attr( 'data-reverbStoreId' );
			var selected_reverb_category_id   = $( this ).val();
			var selected_reverb_category_name = $( this ).find( "option:selected" ).text();
			var level                         = $( this ).attr( 'data-level' );
			if (selected_reverb_category_name != '--Select--') {
				$( this ).css( 'border-color',"green" );
			}
			// console.log( selected_reverb_category_name );

			if ( level != '8' ) {
				$( '.ced_reverb_loader' ).show();
				$.ajax(
					{
						url : ajaxurl,
						data : {
							ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
							action : 'ced_reverb_fetch_next_level_category',
							level : level,
							name : selected_reverb_category_name,
							id : selected_reverb_category_id,
							store_id : store_category_id,
							reverb_store_id : reverb_store_id,
						},
						type : 'POST',
						success: function(response)
					{
							$( '.ced_reverb_loader' ).hide();
							if ( response != 'No-Sublevel' ) {
								for (var i = 1; i < 8; i++) {
									$( '#ced_reverb_' + store_category_id ).find( '.ced_reverb_level' + (parseInt( level ) + i) + '_category' ).closest( "td" ).remove();
								}
								if (response != 0 && selected_reverb_category_id != "" ) {
									$( '#ced_reverb_' + store_category_id ).append( response );
								}
							} else {
								$( '#ced_reverb_' + store_category_id ).find( '.ced_reverb_level' + (parseInt( level ) + 1) + '_category' ).remove();
							}
						}
					}
				);
			}

		}
	);

	$( document ).on(
		'change',
		'.ced_reverb_select_category_on_add_profile',
		function(){

			var reverb_store_id                       = $( this ).attr( 'data-reverbStoreId' );
			var selected_reverb_category_id           = $( this ).val();
					var selected_reverb_category_name = $( this ).find( "option:selected" ).text();
					var level                         = $( this ).attr( 'data-level' );

			if ( level != '8' ) {
				$( '.ced_reverb_loader' ).show();
				$.ajax(
					{
						url : ajaxurl,
						data : {
							ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
							action : 'ced_reverb_fetch_next_level_category_add_profile',
							level : level,
							name : selected_reverb_category_name,
							id : selected_reverb_category_id,
							reverb_store_id : reverb_store_id
						},
						type : 'POST',
						success: function(response)
							{
							$( '.ced_reverb_loader' ).hide();
							if ( response != 'No-Sublevel' ) {
								for (var i = 1; i < 10; i++) {
									$( '#ced_reverb_categories_in_profile' ).find( '.ced_reverb_level' + (parseInt( level ) + i) + '_category' ).remove();
								}
								if (response != 0 && selected_reverb_category_id != "") {
									$( '#ced_reverb_categories_in_profile' ).append( response );
								}
							} else {
								$( '#ced_reverb_categories_in_profile' ).find( '.ced_reverb_level' + (parseInt( level ) + 1) + '_category' ).remove();
							}
						}
							}
				);
			}

		}
	);

	$( document ).on(
		'click',
		'.ced_reverb_save_category_mapping',
		function(){

			var  reverb_category_array = [];
			var  store_category_array  = [];
			var  reverb_category_name  = [];
			var reverb_store_id        = $( this ).attr( 'data-reverbStoreID' );

			var level = [];

			jQuery( '.ced_reverb_select_store_category_checkbox' ).each(
				function(key) {
					if ( jQuery( this ).is( ':checked' ) ) {
						var store_category_id           = $( this ).attr( 'data-categoryID' );
						var cat_level                   = $( '#ced_reverb_' + store_category_id ).find( "td:last" ).attr( 'data-catlevel' );
						var level                       = $().data( 'level' );
						var selected_reverb_category_id = $( '#ced_reverb_' + store_category_id ).find( '.ced_reverb_level' + cat_level + '_category' ).val();

						if ( selected_reverb_category_id == '--select--' || selected_reverb_category_id == null ) {
							selected_reverb_category_id = $( '#ced_reverb_' + store_category_id ).find( '.ced_reverb_level' + (parseInt( cat_level ) - 1) + '_category' ).val();
						}
						var category_name = '';
						$( '#ced_reverb_' + store_category_id ).find( 'select' ).each(
							function(key1){
								category_name += $( this ).find( "option:selected" ).text() + ' --> ';
							}
						);

						var name_len = 0;
						if ( selected_reverb_category_id != '' && selected_reverb_category_id != null ) {
							reverb_category_array.push( selected_reverb_category_id );
							store_category_array.push( store_category_id );

							name_len      = category_name.length;
							category_name = category_name.substring( 0, name_len - 5 );
							category_name = category_name.trim();
							name_len      = category_name.length;
							if ( category_name.lastIndexOf( '--select--' ) > 0 ) {
								category_name = category_name.trim();
								category_name = category_name.replace( '--select--', '' );
								name_len      = category_name.length;
								category_name = category_name.substring( 0, name_len - 5 );
							}
							name_len = category_name.length;

							reverb_category_name.push( category_name );
						}
					}
				}
			);

			$( '.ced_reverb_loader' ).show();

				$.ajax(
					{
						url : ajaxurl,
						data : {
							ajax_nonce :ced_reverb_admin_obj.ajaxnonce,
							action : 'ced_reverb_map_categories_to_store',
							reverb_category_array : reverb_category_array,
							store_category_array : store_category_array,
							reverb_category_name : reverb_category_name,
						},
						type : 'POST',
						success: function(response)
					{
							$( '.ced_reverb_loader' ).hide();
							var html = "Profile Created Successfully";

							ced_reverb_display_notice( html );
							$( 'html, body' ).animate(
								{
									scrollTop: parseInt( $( "body" ).offset().top )
								},
								2000
							);
							window.setTimeout( function(){window.location.reload()}, 2000 );
						}
					}
				);
		}
	);

	$( document ).on(
		'click',
		'#ced_reverb_bulk_operation',
		function(e){
			e.preventDefault();
			var operation = $( ".bulk-action-selector" ).val();
			if (operation <= 0 ) {
				var notice = "";
				notice    += "<div class='notice notice-error'><p>Please Select Operation To Be Performed</p></div>";
				$( ".success-admin-notices" ).append( notice );
			} else {
				var operation          = $( ".bulk-action-selector" ).val();
				var reverb_products_id = new Array();
				$( '.reverb_products_id:checked' ).each(
					function(){
						reverb_products_id.push( $( this ).val() );
					}
				);
				performBulkAction( reverb_products_id,operation );
			}
		}
	);

	function performBulkAction(reverb_products_id,operation)
	 {
		if (reverb_products_id == "") {
			var notice = "";
			notice    += "<div class='notice notice-error'><p>No Products Selected</p></div>";
			$( ".success-admin-notices" ).append( notice );
		}

		$( '.ced_reverb_loader' ).show();
		$.ajax(
			{
				url : ajaxurl,
				data : {
					ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
					action : 'ced_reverb_process_bulk_action',
					operation_to_be_performed : operation,
					id : reverb_products_id,
				},
				type : 'POST',
				dataType : 'JSON',
				success: function(response)
			{
					var message = '';
					var status  = '';
					var classes = '';
					var notice  = '';
					$( '.ced_reverb_loader' ).hide();
					var response1 = jQuery.trim( response.message );
					if (response1 == "Shop is Not Active") {
						var notice = "";
						notice    += "<div class='notice notice-error'><p>Currently Shop is not Active . Please activate your Shop in order to perform operations.</p></div>";
						$( ".success-admin-notices" ).append( notice );
						return;
					} else if (response.status == 200) {
						message = response.message;
						status  = response.status;
						if ( status == 400 ) {
							classes = 'notice-error';
						} else {
							classes = response.classes;
						}

						notice += '<div class="notice ' + classes + ' ced_reverb_notices_content">';
						notice += '<p>' + message + '</p>';
						notice += '</div>';
						$( 'html, body' ).animate(
							{
								scrollTop: parseInt( $( "body" ).offset().top )
							},
							2000
						);
						$( '#ced_reverb_notices' ).html( notice );

					} else if (response.status == 400) {
						var notice = "";
						notice    += "<div class='notice notice-error'><p>" + response.message + "</p></div>";
						$( '#ced_reverb_notices' ).html( notice );
					}
				}
			}
		);
	}

	$( document ).on(
		'click' ,
		'#ced_reverb_fetch_orders' ,
		function() {
			var message = '';
			var status  = '';
			$( '.ced_reverb_loader' ).show();
			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_get_orders_manual',
					},
					type : 'POST',
					dataType :'JSON',
					success: function(response)
				{
						$( '.ced_reverb_loader' ).hide();
						message = response.message;
						status  = response.status;
						ced_reverb_display_notice( message , status );
						window.setTimeout( function() { location.reload(); } , 3000 );
					}

				}
			);
		}
	);

	$( document ).on(
		'click' ,
		'#ced_reverb_update_categories' ,
		function() {
			$( '.ced_reverb_loader' ).show();
			var message = '';
			var status  = '';
			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_update_categories',
					},
					type : 'POST',
					dataType : 'JSON',

					success: function(response)
					{
						$( '.ced_reverb_loader' ).hide();

						message = response.message;
						status  = response.status;
						ced_reverb_display_notice( message , status );
					}
				}
			);
		}
	);

	$( document ).on(
		'click' ,
		'#ced_reverb_shipment_submit' ,
		function() {
			var order_id           = $( this ).data( 'order_id' );
			var trackNumber        = $( '#umb_reverb_tracking_number' ).val();
			var shipping_providers = $( '#reverb_shipping_providers' ).val();
			$( '.ced_reverb_loader' ).show();
			var message = '';
			var status  = '';
			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_ship_order',
						'order_id' : order_id,
						'trackNumber' : trackNumber,
						'shipping_providers' : shipping_providers
					},
					type : 'POST',
					dataType : 'JSON',

					success: function(response)
					{
						$( '.ced_reverb_loader' ).hide();
							window.location.reload();

					}
				}
			);
		}
	);

	$( document ).on(
		'click' ,
		'.glob' ,
		function(e) {
			e.preventDefault();
			$( '.ced_reverb_loader' ).show();
			var profileid = $( this ).attr( 'profile-id' );

			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_copy_global',
						'profileid' : profileid,
						//'trackNumber' : trackNumber,
						//'shipping_providers' : shipping_providers
					},
					type : 'POST',
					//dataType : 'JSON',

					success: function(response)
					{
						console.log(response);
						$( '.ced_reverb_loader' ).hide();
						window.location.reload();

					}
				}
			);


		}	
	);

	$(document).on(
		'click',
		'.ced_reverb_product_prepared_data',

		function(e){

			e.preventDefault();

			var productID = $(this).attr('pro-id');

			$( document ).find( '.ced_reverb_add_account_popup_main_wrapper' ).addClass( 'show' );


			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_test_prepared_data',
						productID : productID,
						//'profileid' : profileid,
						//'trackNumber' : trackNumber,
						//'shipping_providers' : shipping_providers
					},
					type : 'POST',
					//dataType : 'JSON',

					success: function(response)
					{
						
						$(document).find('.ced_reverb_add_account_popup_body').text("");
						$(document).find('.ced_reverb_add_account_popup_body').append(response);
						// console.log(response);
						// $( '.ced_reverb_loader' ).hide();
						// window.location.reload();

					}
				}
			);



		});

	$(document).on(
		'click',
		'.ced_reverb_inventory_prepared_data',
		function(e){

			e.preventDefault();

			var productID = $(this).attr('pro-id');

			$(document).find('.ced_reverb_add_account_popup_main_wrapper').addClass('show');

			$.ajax(
				{
					url : ajaxurl,
					data : {
						ajax_nonce : ced_reverb_admin_obj.ajaxnonce,
						action : 'ced_reverb_test_prepared_data_update_inventory',
						productID : productID,
						//'profileid' : profileid,
						//'trackNumber' : trackNumber,
						//'shipping_providers' : shipping_providers
					},
					type : 'POST',
					//dataType : 'JSON',

					success: function(response)
					{
						$(document).find('.ced_reverb_add_account_popup_body').text("");
						$(document).find('.ced_reverb_add_account_popup_body').append(response);
						// console.log(response);
						// $( '.ced_reverb_loader' ).hide();
						// window.location.reload();

					}
				}
			);

		});

	$( document ).on(
		'click',
		'.ced_reverb_add_account_popup_close',
		function(){

			$( document ).find( '.ced_reverb_add_account_popup_main_wrapper' ).removeClass( 'show' );

		}
	);

})( jQuery );
