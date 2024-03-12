(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

	 var ajaxUrl   = ced_etsy_wcfm_admin_obj.ajax_url;
	 var ajaxNonce = ced_etsy_wcfm_admin_obj.ajax_nonce;
	 var shop_name = ced_etsy_wcfm_admin_obj.shop_name;
	 var parsed_response,message,status,classes,notice;


	 $( document ).on(
	 	'click',
	 	'.ced_etsy_wcfm_parent_element',
	 	function(){
	 		if ($( this ).find( '.ced_etsy_wcfm_instruction_icon' ).hasClass( "dashicons-arrow-down-alt2" )) {
	 			$( this ).find( '.ced_etsy_wcfm_instruction_icon' ).removeClass( "dashicons-arrow-down-alt2" );
	 			$( this ).find( '.ced_etsy_wcfm_instruction_icon' ).addClass( "dashicons-arrow-up-alt2" );
	 		} else if ($( this ).find( '.ced_etsy_wcfm_instruction_icon' ).hasClass( "dashicons-arrow-up-alt2" )) {
	 			$( this ).find( '.ced_etsy_wcfm_instruction_icon' ).addClass( "dashicons-arrow-down-alt2" );
	 			$( this ).find( '.ced_etsy_wcfm_instruction_icon' ).removeClass( "dashicons-arrow-up-alt2" );
	 		}
	 		$( this ).next( '.ced_etsy_wcfm_child_element' ).toggle( 200 );
	 	}
	 	);

	 $( document ).on(
	 	'click',
	 	'#ced_etsy_wcfm_add_account',
	 	function(){
	 		$( '.ced-etsy-add-account-wrapper').toggle();
	 	}
	 	);
	 $( document ).on(
	 	'change',
	 	'.ced_etsy_wcfm_select_store_category_checkbox',
	 	function(){
	 		var store_category_id = $( this ).attr( 'data-categoryID' );

	 		if ( $( this ).is( ':checked' ) ) {
	 			$( '#ced_etsy_wcfm_categories_' + store_category_id ).show( 'slow' );
	 		} else {
	 			$( '#ced_etsy_wcfm_categories_' + store_category_id ).hide( 'slow' );
	 		}
	 	}
	 	);
	 $( document ).on(
	 	'change',
	 	'.ced_etsy_wcfm_select_category',
	 	function(){

	 		var store_category_id           = $( this ).attr( 'data-storeCategoryID' );
	 		var selected_etsy_wcfm_category_id   = $( this ).val();
	 		var selected_etsy_wcfm_category_name = $( this ).find( "option:selected" ).text();
	 		var level                       = $( this ).attr( 'data-level' );
	 		if ( level != '10' ) {
	 			$( '.ced_etsy_wcfm_loader' ).show();
	 			$.ajax(
	 			{
	 				url : ajaxUrl,
	 				data : {
	 					ajax_nonce : ajaxNonce,
	 					action : 'ced_etsy_wcfm_fetch_next_level_category',
	 					level : level,
	 					name : selected_etsy_wcfm_category_name,
	 					id : selected_etsy_wcfm_category_id,
	 					store_id : store_category_id,
	 				},
	 				type : 'POST',
	 				success: function(response)
	 				{
	 					response = jQuery.parseJSON( response );
	 					$( '.ced_etsy_wcfm_loader' ).hide();
	 					if ( response != 'No-Sublevel' ) {
	 						for (var i = 1; i < 10; i++) {
	 							$( '#ced_etsy_wcfm_categories_' + store_category_id ).find( '.ced_etsy_wcfm_level' + (parseInt( level ) + i) + '_category' ).closest( "td" ).remove();
	 						}
	 						if (response != 0) {
	 							$( '#ced_etsy_wcfm_categories_' + store_category_id ).append( response );
	 						}
	 					} else {
	 						$( '#ced_etsy_wcfm_categories_' + store_category_id ).find( '.ced_etsy_wcfm_level' + (parseInt( level ) + 1) + '_category' ).remove();
	 					}
	 				}
	 			}
	 			);
	 		}

	 	}
	 	);

	 $( document ).on(
	 	'click',
	 	'#ced_etsy_wcfm_save_category_button',
	 	function(){

	 		var  etsy_wcfm_category_array  = [];
	 		var  store_category_array = [];
	 		var  etsy_wcfm_category_name   = [];
	 		var shopname              = $( this ).attr( 'data-etsyStoreName' );
	 		var shop_name             = jQuery.trim( shopname );
	 		jQuery( '.ced_etsy_wcfm_select_store_category_checkbox' ).each(
	 			function(key) {

	 				if ( jQuery( this ).is( ':checked' ) ) {
	 					var store_category_id = $( this ).attr( 'data-categoryid' );
	 					var cat_level         = $( '#ced_etsy_wcfm_categories_' + store_category_id ).find( "td:last" ).attr( 'data-catlevel' );

	 					var selected_etsy_wcfm_category_id = $( '#ced_etsy_wcfm_categories_' + store_category_id ).find( '.ced_etsy_wcfm_level' + cat_level + '_category' ).val();

	 					if ( selected_etsy_wcfm_category_id == '' || selected_etsy_wcfm_category_id == null ) {
	 						selected_etsy_wcfm_category_id = $( '#ced_etsy_wcfm_categories_' + store_category_id ).find( '.ced_etsy_wcfm_level' + (parseInt( cat_level ) - 1) + '_category' ).val();
	 					}
	 					var category_name = '';
	 					$( '#ced_etsy_wcfm_categories_' + store_category_id ).find( 'select' ).each(
	 						function(key1){
	 							category_name += $( this ).find( "option:selected" ).text() + ' --> ';
	 						}
	 						);
	 					var name_len = 0;
	 					if ( selected_etsy_wcfm_category_id != '' && selected_etsy_wcfm_category_id != null ) {
	 						etsy_wcfm_category_array.push( selected_etsy_wcfm_category_id );
	 						store_category_array.push( store_category_id );

	 						name_len      = category_name.length;
	 						category_name = category_name.substring( 0, name_len - 5 );
	 						category_name = category_name.trim();
	 						name_len      = category_name.length;
	 						if ( category_name.lastIndexOf( '--Select--' ) > 0 ) {
	 							category_name = category_name.trim();
	 							category_name = category_name.replace( '--Select--', '' );
	 							name_len      = category_name.length;
	 							category_name = category_name.substring( 0, name_len - 5 );
	 						}
	 						name_len = category_name.length;
	 						etsy_wcfm_category_name.push( category_name );
	 					}
	 				}
	 			}
	 			);
	 		$( '.ced_etsy_wcfm_loader' ).show();
	 		$.ajax(
	 		{
	 			url : ajaxUrl,
	 			data : {
	 				ajax_nonce : ajaxNonce,
	 				action : 'ced_etsy_wcfm_map_categories_to_store',
	 				etsy_wcfm_category_array : etsy_wcfm_category_array,
	 				store_category_array : store_category_array,
	 				etsy_wcfm_category_name : etsy_wcfm_category_name,
	 				storeName : shop_name
	 			},
	 			type : 'POST',
	 			success: function(response)
	 			{
	 				$( '.ced_etsy_wcfm_loader' ).hide();
	 				window.location.reload();

	 			}
	 		}
	 		);

	 	}
	 	);

	 $( document ).on(
	 	'click',
	 	'#ced_etsy_wcfm_bulk_operation',
	 	function(e){
	 		e.preventDefault();
	 		var operation = $( ".bulk-action-selector" ).val();
	 		if (operation <= 0 ) {
	 			message = 'Please select any bulk operation.';
	 			status  = 400;
	 			$( '.ced_etsy_wcfm_loader' ).hide();
	 			ced_etsy_wcfm_display_notice( message,status );
	 			return false;
	 		} else {
	 			var operation        = $( ".bulk-action-selector" ).val();
	 			var etsy_products_ids = new Array();
	 			$( '.etsy_wcfm_products_id:checked' ).each(
	 				function(){
	 					etsy_products_ids.push( $( this ).val() );
	 				}
	 				);
	 			performBulkAction( etsy_products_ids,operation );
	 		}

	 	}
	 	);


	 function ced_etsy_wcfm_display_notice( message = '' , status = 200){
	 	if ( status == 400 ) {
	 		classes = 'notice-error';
	 	} else {
	 		classes = 'notice-success';
	 	}
	 	notice  = '';
	 	notice += '<div class="notice ' + classes + ' ced_etsy_wcfm_notices_content">';
	 	notice += message;
	 	notice += '</div>';
		// scroll_at_top();
		$( '#ced_etsy_wcfm_notices' ).html( notice );
	}

	function performBulkAction(etsy_products_ids,operation,notice='')
	{
		if (etsy_products_ids == "") {
			message = 'Please select any products.';
			status  = 400;
			$( '.ced_etsy_wcfm_loader' ).hide();
			ced_etsy_wcfm_display_notice( message,status );
			return false;
		}
		$( '.ced_etsy_wcfm_loader' ).show();
		var etsy_products_id_to_perform = etsy_products_ids[0];

		$.ajax(
		{
			url : ajaxUrl,
			data : {
				ajax_nonce : ajaxNonce,
				action : 'ced_etsy_wcfm_process_bulk_action',
				operation_to_be_performed : operation,
				id : etsy_products_id_to_perform,
				shopname:shop_name
			},
			type : 'POST',
			success: function(response)
			{
				$( '.ced_etsy_wcfm_loader' ).hide();
				parsed_response = jQuery.parseJSON( response );
				message         = parsed_response.message;
				status          = parsed_response.status;
				if ( status == 400 ) {
					classes = 'notice-error';
				} else {
					classes = 'notice-success';
				}

				notice += '<div class="notice ' + classes + ' ced_etsy_wcfm_notices_content">';
				notice += message;
				notice += '</div>';
					// scroll_at_top();
					$( '#ced_etsy_wcfm_notices' ).html( notice );
					etsy_products_ids = etsy_products_ids.splice( 1 );
					if (etsy_products_ids == "") {
						window.setTimeout(function(){window.location.reload()}, 2500);
					} else {
						performBulkAction( etsy_products_ids,operation,notice );
					}
				}
			}
			);
	}

	function scroll_at_top() {
		$( "html, body" ).animate(
		{
			scrollTop: 0
		},
		
		);
	}

	$( document ).on(
		'click',
		'#ced_etsy_fetch_orders',
		function(event)
		{
			event.preventDefault();
			var store_id = $( this ).attr( 'data-id' );
			$( '.ced_etsy_wcfm_loader' ).show();
			$.ajax(
			{
				url : ajaxUrl,
				data : {
					ajax_nonce : ajaxNonce,
					action : 'ced_etsy_wcfm_get_orders',
					shopid:store_id
				},
				type : 'POST',
				success: function(response)
				{
					location.reload( true );
				}
			}
			);
		}
		);

	$( document ).on(
		'click',
		'#ced_etsy_wcfm_delete_account',
		function(event)
		{
			var store_id = $( this ).attr( 'data-shop' );
			$( '.ced_etsy_wcfm_loader' ).show();
			$.ajax(
			{
				url : ajaxUrl,
				data : {
					ajax_nonce : ajaxNonce,
					action : 'ced_etsy_wcfm_delete_account',
					shopid:store_id
				},
				type : 'POST',
				success: function(response)
				{
					location.reload( true );
				}
			}
			);
		}
		);


	/**
	 ***************************************************************************
	 * IMPORT PRODUCT FROM ETSY TO WOOCOMMERCE BY BULK ACTION IMPORT NAVE MENU
	 ***************************************************************************
	 */
	 $(document).on( 'click', '#ced_esty_wcfm_import_product_bulk_optration' , function(e){
	 	e.preventDefault();
	
	 	let operation = $( '.bulk-import-action-selectorf').val();
	 	if ( operation <= 0 ) {
	 		var notice = '';
	 		notice +='<div class="notice notice-error"><p>Please Select Operation To Be Performed</p></div>';
	 		$('.success-admin-notices').append(notice);
	 	} else {

	 		let operation = $( '.bulk-import-action-selectorf').val();
	 		let ced_wcfm_imp_listing_id = new Array();
			// Get all checked ids in the array from input box
			$('.ced_wcfm_imp_listing_id:checked').each(function(){

				ced_wcfm_imp_listing_id.push($(this).val());

				console.log(ced_wcfm_imp_listing_id);
			});
			// import Product by Bulk action
			CedWCFMimportProducts( ced_wcfm_imp_listing_id , operation );
		}
	});

	 $(document).on('click' , '.import_single_product' , function(e) {
	 	e.preventDefault();
	 	let listing_id = $(this).data('listing-id');
	 	const listing_id_array = new Array();
	 	listing_id_array.push(listing_id);
	 	CedWCFMimportProducts( listing_id_array );
	 });


	/**
	 * To perform Bulk action of the import product tab
	 * 
	 * @param products_id  array for all check id
	 * @param operation string to be perfor by Bulk action
	 */
	 
	 function CedWCFMimportProducts( ced_wcfm_imp_listing_id , operation ) {
	 	if ( ced_wcfm_imp_listing_id == '') {
	 		let notice = '';
	 		notice +='<div class="notice notice-error"><p>No Products Selected To Import</p></div>';
	 		$(".success-admin-notices").append(notice);
	 	}

	 	$( '.ced_etsy_wcfm_loader' ).show();
	 	$.ajax({
	 		url : ajaxUrl,
	 		data : {
	 			ajax_nonce                : ajaxNonce,
	 			action                    : 'ced_etsy_import_product_bulk_action',
	 			operation_to_be_performed : operation,
	 			listing_id                : ced_wcfm_imp_listing_id,
	 			shop_name                 : shop_name 
	 		},
	 		type: 'POST',
	 		success : function (response) {
	 		
	 			let sliced_listing_id = ced_wcfm_imp_listing_id.slice(1);
	 			if ( sliced_listing_id != 0 ) {
	 				
	 				CedWCFMimportProducts( sliced_listing_id ,operation );
	 			} else {

	 				$( '.ced_etsy_wcfm_loader' ).hide();
	 				var parsed_response = jQuery.parseJSON( response );
	 				message         = parsed_response.message;
	 				status          = parsed_response.status;
	 				// console.log(response.status);
	 				if ( status ==200 ) {
	 					let notice = '';
	 					// if ( status == 400 ) {
	 					// 	classes = 'notice-error';
	 					// } else {
	 					// 	classes = 'notice-success';
	 					// }
                        // console.log(message);
	 					notice += '<div class="notice ' + classes + ' ced_etsy_wcfm_notices_content">';
	 					notice += message;
	 					notice += '</div>';
							// scroll_at_top();
							$( '#ced_etsy_wcfm_notices' ).html( notice );
							window.setTimeout(function(){window.location.reload()}, 2500);

						}
						// window.location.reload();
					}

				}
			});
	 }

	 // $('#cb-select-all-1').click(function() {
	 // 	if( $(this).is(':checked') ) {
	 // 		$('#cb-select-all-1').attr( 'checked', true );
	 // 		$('.ced_bulk_action_checkbox_single').attr( 'checked', true );
	 // 	}	else {
	 // 		$('#cb-select-all-1').attr( 'checked', false );
	 // 		$('.ced_bulk_action_checkbox_single').attr( 'checked', false );
	 // 	}
	 // });

	 jQuery(document).on('click','#cb-select-all-1',function(){
	 	var checked = 0;
	 	if(jQuery(this).prop("checked") == true){
	 		checked = 1;
	 	}
	 	else
	 	{
	 		checked = 0;
	 	}
	 	if(checked == 1)
	 	{
	 		if(jQuery('.etsy_wcfm_products_id:checkbox').length)
	 		{
	 			jQuery('.etsy_wcfm_products_id:checkbox').each(function(){
	 				jQuery(this).prop('checked',true);
	 			});
	 		}
	 		if(jQuery('.ced_etsy_wcfm_imp_listing_id:checkbox').length)
	 		{
	 			jQuery('.ced_etsy_wcfm_imp_listing_id:checkbox').each(function(){
	 				jQuery(this).prop('checked',true);
	 			});
	 		}
	 		
	 	}
	 	else
	 	{
	 		if(jQuery('.etsy_wcfm_products_id:checkbox').length)
	 		{
	 			jQuery('.etsy_wcfm_products_id:checkbox').each(function(){
	 				jQuery(this).prop('checked',false);
	 			});
	 		}
	 		if(jQuery('.ced_etsy_wcfm_imp_listing_id:checkbox').length)
	 		{
	 			jQuery('.ced_etsy_wcfm_imp_listing_id:checkbox').each(function(){
	 				jQuery(this).prop('checked',false);
	 			});
	 		}
	 	}
	 });


	 /**
	  * To add refresh button on product import page
	  * 
	  * @param
	  */

	 $(document).ready(function(){
            $('#ced_esty_wcfm_refresh_page').on('click',function() {
                   window.setTimeout(function(){window.location.reload()}, 2500);
            });
	 });
	 
	})( jQuery );
