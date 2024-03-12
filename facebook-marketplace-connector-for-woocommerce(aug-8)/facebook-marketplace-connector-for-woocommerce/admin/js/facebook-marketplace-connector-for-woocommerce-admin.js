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

	var ajaxUrl   = ced_fmcw_admin_obj.ajax_url;
	var ajaxNonce = ced_fmcw_admin_obj.ajax_nonce;
	$( document ).ready(
		function(){
			let arrow = document.querySelectorAll( ".arrow" );
			for (var i = 0; i < arrow.length; i++) {
				arrow[i].addEventListener(
					"click",
					(e) => {
					let arrowParent = e.target.parentElement.parentElement;
					arrowParent.classList.toggle( "showMenu" );
					}
				);
			}
			let sidebar                       = document.querySelector( ".sidebar" );
			let sidebarBtn                    = document.querySelector( ".fa-angle-double-left" );
			let sidebarmenueAndContentWarpper = document.querySelector( ".ced-fmcw-main-pages-wrapper" );
			console.log( sidebarBtn );
			sidebarBtn.addEventListener(
				"click",
				() => {
					sidebar.classList.toggle( "ced-facebook-close-content" );
					sidebarmenueAndContentWarpper.classList.toggle( "ced_facebook_content_menu_width_manager" );
				}
			);
		}
	)
	$( document ).on(
		"click",
		".ced_fmcw_connect_fb_account",
		function(){
			$.ajax(
				{
					url:ajaxUrl,
					data:{
						ajax_nonce:ajaxNonce,
						prodId:product_id,
						shopid:shopid,
						action:'ced_shopee_profiles_on_pop_up'
					},
					type:'POST',
					success:function(response)
				{
						$( ".ced_shopee_preview_product_popup_main_wrapper" ).html( response );
						$( document ).find( '.ced_shopee_preview_product_popup_main_wrapper' ).addClass( 'show' );
					}

				}
			);
		}
	);
	$( document ).on(
		"click",
		"#ced-fmcw-register-to-cedcommerce",
		function(){
			$( '.ced_fmcw_loader' ).show();
			$( '.notice-error' ).remove();
			var reg_email   = $( document ).find( "#ced-fmcw-registration-reg_email-field" ).val();
			var reg_country = $( document ).find( ".ced_facebook_selected_country" ).val();
			if (reg_country == null || reg_country == null || reg_email == '' || reg_email == null ) {
				var response = "Please fill all the details";
				$( '<div class="notice notice-error is-dismissible"><span style="font-size: 15px;">' + response + "</span></div>" ).insertAfter( '.ced_gmcw_show_error' );
				$( '.ced_fmcw_loader' ).hide();
				return false;
			}
			$.ajax(
				{
					url:ajaxUrl,
					data:{
						ajax_nonce:ajaxNonce,
						action:"ced_fmcw_register_to_cedcommerce",
						reg_email : reg_email,
						reg_country : reg_country,
					},
					type:"POST",
					success:function(response){
						if (response == 'success') {
							location.reload( true );
						} else {
							console.log( "44" );
							$( '<div class="notice notice-error is-dismissible"><span style="font-size: 15px;">' + response + "</span></div>" ).insertAfter( '.ced_gmcw_show_error' );
							$( '.ced_fmcw_loader' ).hide();
						}
					}
				}
			);
		}
	);

	$( document ).on(
		"click",
		"#ced-fmcw-fb-account-connect",
		function(){
			$( '.ced_fmcw_loader' ).show();
			var sAppId     = $( this ).attr( "data-sAppId" );
			var identifier = $( this ).attr( "data-identifier" );
			var currency   = $( this ).attr( "data-currency" );
			var timezone   = $( this ).attr( "data-timezone" );
			if ( sAppId !== "" ) {
				// need to share a userid - that should be unique and saved in db, in json encoedd form
				var identifier_json = {
					'external_id': identifier
				}
				identifier_json     = JSON.stringify( identifier_json );
				// var url              = "https://fbdev.cifapps.com/remote/public/apiconnect/request/auth?sAppId=" + sAppId + "&state=" + identifier_json + "&fbe=1&timezone=" + timezone + "&currency=" + currency;
				var url              = "https://metaapi-backend.cifapps.com/apiconnect/request/auth?sAppId=" + sAppId + "&state=" + identifier_json + "&fbe=1&timezone=" + timezone + "&currency=" + currency;
				window.location.href = url;
			}
		}
	);
	$( document ).on(
		"click",
		".ced_copy_to_clipboard",
		function(){
			var url                 = $( this ).attr( "data-url" );
			var textarea            = document.createElement( "textarea" );
			textarea.textContent    = url;
			textarea.style.position = "fixed"; // Prevent scrolling to bottom of page in MS Edge.
			document.body.appendChild( textarea );
			textarea.select();
			document.execCommand( "copy" );
			document.body.removeChild( textarea );
			// if( url !== "" )
			// {

			// }
		}
	);

	$( document ).on(
		"click",
		".ced-fmcw-fb-authenticate-button",
		function(){
			var merchant_page_id = $( this ).attr( "data-merchant_page_id" );
			var page_id          = $( this ).attr( "data-page_id" );
			var catalog_id       = $( this ).attr( "data-catalog_id" );
			var button           = $( this );
			$.ajax(
				{
					url:ajaxUrl,
					data:{
						ajax_nonce:ajaxNonce,
						action:"ced_fmcw_authenticate_cms_page",
						merchant_page_id:merchant_page_id,
						catalog_id:catalog_id,
						page_id:page_id
					},
					type:"POST",
					datatype : 'json',
					success:function(response){
						response = $.parseJSON( response );
						if ( response.status == "200" ) {
							button.val( "Authenticated" );
							button.prop( 'disabled', true );
							$( ".ced-fmcw-move-to-next-step-wrapper" ).show();
						}
					}
				}
			);
		}
	);

	$( document ).on(
		"click",
		".ced_fmcw_display_error",
		function(){
			$( document ).find( '.ced_fmcw_product_status_popup_main_wrapper' ).addClass( 'show' );
			var product_id = $( this ).attr( "data-product-id" );
			$.ajax(
				{
					url:ajaxUrl,
					data:{
						ajax_nonce:ajaxNonce,
						action:"ced_get_errors_for_product",
						product_id:product_id,
					},
					type:"POST",
					success:function(response){
						response = $.parseJSON( response );
						// if( response.status == "200" )
						// {
						   // console.log(response);
						// }
						$( document ).find( '.ced_fmcw_product_title_heading' ).html( response.title );
						$( document ).find( '.ced_fmcw_errors' ).html( response.error );
						$( document ).find( '.ced_fmcw_warnings' ).html( response.warning );
					}
				}
			);
		}
	);
	$( document ).on(
		'click',
		'.ced_fmcw_product_status_popup_close',
		function(){

				$( document ).find( '.ced_fmcw_product_status_popup_main_wrapper' ).removeClass( 'show' );

		}
	);
	$( document ).on(
		'change',
		'.ced_fmcw_select_category',
		function(){

			var store_category_id         = $( this ).attr( 'data-storeCategoryID' );
			var selected_fb_category_id   = $( this ).val();
			var selected_fb_category_name = $( this ).find( "option:selected" ).text();
			var level                     = $( this ).attr( 'data-level' );

			if ( level != '5' ) {
				$( '.ced_fmcw_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_fmcw_fetch_next_level_category',
							level : level,
							name : selected_fb_category_name,
							id : selected_fb_category_id,
							store_id : store_category_id
						},
						type : 'POST',
						success: function(response)
					{
							$( '.ced_fmcw_loader' ).hide();
							if ( response != 'No-Sublevel' ) {
								for (var i = 1; i < 5; i++) {
									$( '#ced_fmcw_categories_' + store_category_id ).find( '.ced_fmcw_level' + (parseInt( level ) + i) + '_category' ).closest( "td" ).remove();
								}
								if (response != 0 && selected_fb_category_id != "" ) {
									$( '#ced_fmcw_categories_' + store_category_id ).append( response );
									// $(response).insertAfter('#ced_fmcw_categories_'+store_category_id);
								}
							} else {
								$( '#ced_fmcw_categories_' + store_category_id ).find( '.ced_fmcw_level' + (parseInt( level ) + 1) + '_category' ).remove();
							}
						}
					}
				);
			}
		}
	);
	$( document ).on(
		"click",
		"#ced-fmcw-save-active-pages",
		function(){
			var active_pages = [];
			jQuery( '.ced-fmcw-active-page-checkbox:checked' ).each(
				function(key) {
					active_pages.push( $( this ).val() );
				}
			);

			$.ajax(
				{
					url:ajaxUrl,
					data:{
						ajax_nonce:ajaxNonce,
						action:"ced_fmcw_save_active_pages",
						active_pages : active_pages,
					},
					type:"POST",
					success:function(response){
						location.reload( true );
					}
				}
			);
		}
	);
	$( document ).on(
		'change',
		'.ced_fmcw_select_store_caegory_checkbox',
		function(){
			var store_category_id = $( this ).attr( 'data-categoryID' );
			if ( $( this ).is( ':checked' ) ) {
				$( '#ced_fmcw_categories_' + store_category_id ).show( 'slow' );
			} else {
				$( '#ced_fmcw_categories_' + store_category_id ).hide( 'slow' );
			}
		}
	);

	$( document ).on(
		'click',
		'.ced_fmcw_save_category_mapping',
		function(){

			var  fb_category_array    = [];
			var  store_category_array = [];
			var  fb_category_name     = [];
			$( '.ced_fmcw_loader' ).show();
			jQuery( '.ced_fmcw_select_store_caegory_checkbox' ).each(
				function(key) {
					if ( jQuery( this ).is( ':checked' ) ) {
						var store_category_id = $( this ).attr( 'data-categoryid' );
						var cat_level         = $( '#ced_fmcw_categories_' + store_category_id ).find( "td:last" ).attr( 'data-catlevel' );

						var selected_fb_category_id = $( '#ced_fmcw_categories_' + store_category_id ).find( '.ced_fmcw_level' + cat_level + '_category' ).val();

						if ( selected_fb_category_id == '' || selected_fb_category_id == null ) {
							selected_fb_category_id = $( '#ced_fmcw_categories_' + store_category_id ).find( '.ced_fmcw_level' + (parseInt( cat_level ) - 1) + '_category' ).val();
						}
						var category_name = '';
						$( '#ced_fmcw_categories_' + store_category_id ).find( 'select' ).each(
							function(key1){
								category_name += $( this ).find( "option:selected" ).text() + ' --> ';
							}
						);
						var name_len = 0;
						if ( selected_fb_category_id != '' && selected_fb_category_id != null ) {
							fb_category_array.push( selected_fb_category_id );
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

							fb_category_name.push( category_name );
						}
					}
				}
			);

			$.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_fmcw_map_categories_to_store',
						fb_category_array : fb_category_array,
						store_category_array : store_category_array,
						fb_category_name : fb_category_name,
					},
					type : 'POST',
					success: function(response)
				{
						$( '.ced_fmcw_loader' ).hide();
						location.reload( true );
					}
				}
			);
		}
	);

	$( document ).on(
		'click',
		'.ced_fmcw_cancel_category_mapping',
		function(){
			location.reload( true );
		}
	);

	$( document ).on(
		"click",
		"#ced-fmcw-next-step-button",
		function(){

			/*if( $( document ).find( '.ced-fmcw-authenticated-page-id' ).length > 0 )
			{
			var merchant_account_id = $(document).find("#ced-fmcw-merchant-account-id").val();
			var page_id = [];
			var catalog_id = [];
			$('.ced-fmcw-authenticated-page-id').each( function(index){
				page_id.push($(this).attr( 'data-page_id' ));
				catalog_id.push($(this).attr( 'data-catalog_id' ));

			} );
			}
			console.log( merchant_account_id );
			console.log( catalog_id );
			console.log( page_id );*/

			$.ajax(
				{
					url:ajaxUrl,
					data:{
						ajax_nonce:ajaxNonce,
						action:"ced_fmcw_setup_completed",
					},
					type:"POST",
					datatype : 'json',
					success:function(response){
						response = $.parseJSON( response );
						if ( response.status == "200" ) {
							window.location.href = response.redirect_url;
						} else if ( response.status == "201" ) {

						}
					}
				}
			);
		}
	);

	$( document ).on(
		'click',
		'#ced_fmcw_accordians .ced_fmcw_facebook_panel_heading',
		function(){
			var k = $( this ).next().slideToggle( 'slow' );
			$( '.ced_fmcw_facebook_collapse' ).not( k ).slideUp( 'slow' );
		}
	);

	$( document ).on(
		'click',
		'#ced_facebook_fetch_orders',
		function(event)
		{
			event.preventDefault();
			$( '.ced_fmcw_loader' ).show();
			$.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_facebook_get_orders',
					},
					type : 'POST',
					success: function(response)
				{
						$( '.ced_fmcw_loader' ).hide();
						location.reload( true );
					}
				}
			);
		}
	);
		$( document ).on(
			'click',
			'.ced_facebook_order_template_sbutton',
			function(){
				var div = $( this ).attr( 'data-id' );
				if (div) {
					if (div == 'ced_facebook_complete_dispatch_template') {
						$( "#" + div ).show();
						$( "#ced_facebook_refund_template" ).hide();
						$( "#ced_facebook_cancel_template" ).hide();
					}
					if (div == 'ced_facebook_refund_template') {
						$( "#" + div ).show();
						$( "#ced_facebook_complete_dispatch_template" ).hide();
						$( "#ced_facebook_cancel_template" ).hide();
					}
					if (div == 'ced_facebook_cancel_template') {
						$( "#" + div ).show();
						$( "#ced_facebook_complete_dispatch_template" ).hide();
						$( "#ced_facebook_refund_template" ).hide();
					}
				} else {
					$( "#" + div ).hide();
				}
			}
		);

		jQuery( document ).on(
			'click',
			'#ced_facebook_shipment_submit',
			function(){
				var type           = $( this ).attr( 'data-order-type' );
				var all_data_array = {};
				var unique_ids     = {};
				var i              = 0;
				var order_id       = jQuery( '#facebook_orderid' ).val();
				var wo_order_id    = jQuery( '#post_ID' ).val();
				var shop_id        = jQuery( '#facebook_shop_id' ).val();
				if (type != 'complete') {
					var shipping_provider_id = jQuery( '#facebook_shipping_providers_partial' ).val();
					var tracking_url         = jQuery( '#facebook_shipping_providers_partial' ).find( ':selected' ).attr( 'data-url' );
					var trackNumber          = jQuery( '#_facebook_tracking_number_partial' ).val();
					jQuery( '#onbuy_order_line_items tr' ).each(
						function(){

							var tr          = jQuery( this ).attr( 'id' );
							unique_ids[i]   = tr;
							var sku         = jQuery( '#sku' + tr ).val();
							var qty_order   = jQuery( '#qty_order' + tr ).val();
							var qty_shipped = jQuery( '#qty_shipped' + tr ).val();
							var pro_id      = jQuery( '#sku' + tr ).attr( 'data-p-id' );

							all_data_array['sku/' + tr]         = sku;
							all_data_array['qty_order/' + tr]   = qty_order;
							all_data_array['qty_shipped/' + tr] = qty_shipped;
							all_data_array['pro_id/' + tr]      = pro_id;
						}
					);
					$( '.ced_onbuy_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'umb_onbuy_partial_dispatch_order',
								onbuy_order_id : order_id,
								woo_order_id: wo_order_id,
								trackNumber : trackNumber,
								shipping_provider_id : shipping_provider_id,
								tracking_url : tracking_url,
								all_data_array : all_data_array,
								shopid : shop_id,
							},
							type : 'POST',
							success: function(response)
						{
									$( '.ced_onbuy_loader' ).hide();
									var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									alert( response.message );
									window.location.reload();
								} else {
									alert( response.message );
								}
							}
						}
					);

				} else {
					var shipping_provider_id = jQuery( '#facebook_shipping_providers_complete' ).val();
					var trackNumber          = jQuery( '#_facebook_tracking_number_complete' ).val();
					$( '.ced_fmcw_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_fmcw_complete_dispatch_order',
								facebook_order_id : order_id,
								woo_order_id: wo_order_id,
								trackNumber : trackNumber,
								shipping_provider_id : shipping_provider_id,
								page_id : shop_id,
							},
							type : 'POST',
							success: function(response)
						{
									$( '.ced_fmcw_loader' ).hide();
									var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									alert( response.message );
									window.location.reload();
								} else {
									alert( response.message );
								}
							}
						}
					);

				}
			}
		);

	$( document ).on(
		'click',
		'#ced_facebook_cancel_submit' ,
		function(){
			var cancel_info      = $( '#cancel_info' ).val();
			var cancel_reason_id = $( '#cancel_reason_id' ).find( ':selected' ).val();
			var order_id         = jQuery( '#facebook_orderid' ).val();
			var shop_id          = jQuery( '#facebook_shop_id' ).val();
			var wo_order_id      = jQuery( '#post_ID' ).val();
			if (cancel_reason_id != "" ) {
				$( '.ced_fmcw_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_facebook_cancel_order',
							cancel_info : cancel_info,
							cancel_reason_id : cancel_reason_id,
							facebook_order_id : order_id,
							page_id : shop_id,
							woo_order_id : wo_order_id,
						},
						type : 'POST',
						success: function(response)
					{
							$( '.ced_fmcw_loader' ).hide();
							var response = jQuery.parseJSON( response );
							if (response.status == '200') {
								alert( response.message );
							} else {
								alert( response.message );
							}
						}
					}
				);

			} else {
				alert( "Cancellation Reason Must Be Selected" );
			}
		}
	);

	$( document ).on(
		'click',
		'#ced_facebook_refund_submit' ,
		function(){
			var cancel_info      = $( '#refund_info' ).val();
			var cancel_reason_id = $( '#refund_reason_id' ).find( ':selected' ).val();
			var order_id         = jQuery( '#facebook_orderid' ).val();
			var shop_id          = jQuery( '#facebook_shop_id' ).val();
			var wo_order_id      = jQuery( '#post_ID' ).val();
			if (cancel_reason_id != "" ) {
				  $( '.ced_fmcw_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_facebook_refund_order',
							refund_info : cancel_info,
							refund_reason_id : cancel_reason_id,
							facebook_order_id : order_id,
							page_id : shop_id,
							woo_order_id : wo_order_id,
						},
						type : 'POST',
						success: function(response)
					{
									  $( '.ced_fmcw_loader' ).hide();
									  var response = jQuery.parseJSON( response );
							if (response.status == '200') {
								alert( response.message );
							} else {
								alert( response.message );
							}
						}
					}
				);

			} else {
				alert( "Cancellation Reason Must Be Selected" );
			}
		}
	);
	jQuery( document ).on(
		'click',
		'#ced_fb_export_error_log',
		function(){
			jQuery( '.ced_fmcw_loader' ).show();
			$( "html, body" ).animate(
				{
					scrollTop: 0
				},
				600
			);
			jQuery.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_fb_write_error_log',
					},
					type : 'POST',
					success: function(response)
				{
								var response = jQuery.parseJSON( response );
						if (response.status == '200') {
							jQuery( '.ced_fmcw_loader' ).hide();
							window.location.href = response.url;
						} else {
							jQuery( '.ced_fmcw_loader' ).hide();
							var notice = "";
							notice    += "<div class='notice notice-error'><p>No logs available to show</p></div>";
							$( ".success-admin-notices" ).append( notice );
						}

					}
				}
			);
		}
	);
	jQuery( document ).on(
		'click',
		'#ced_fb_export_uploaded_log',
		function(){
			jQuery( '.ced_fmcw_loader' ).show();
			$( "html, body" ).animate(
				{
					scrollTop: 0
				},
				600
			);
			jQuery.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_fb_write_uploaded_log',
					},
					type : 'POST',
					success: function(response)
				{
						var response = jQuery.parseJSON( response );
						if (response.status == '200') {
							jQuery( '.ced_fmcw_loader' ).hide();
							window.location.href = response.url;
						} else {
							jQuery( '.ced_fmcw_loader' ).hide();
							var notice = "";
							notice    += "<div class='notice notice-error'><p>No logs available to show</p></div>";
							$( ".success-admin-notices" ).append( notice );
						}

					}
				}
			);
		}
	);
	jQuery( document ).on(
		'click',
		'.ced_save_catalog',
		function(e){
			e.preventDefault();
			jQuery( '.ced_fmcw_loader' ).show();
			var business_id = jQuery( this ).attr( 'data-business_id' );
			var catalog_id  = jQuery( ".ced_selected_catalog_id" ).val();
			jQuery.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_save_catalog',
						business_id : business_id,
						catalog_id : catalog_id,
					},
					type : 'POST',
					success: function(response)
				{
						jQuery( '.ced_fmcw_loader' ).hide();
						window.location.reload();

					}
				}
			);
		}
	);

	$( document ).on(
		'change',
		'#ced_facebook_shopping_list_per_page' ,
		function() {
			var per_page = $( this ).val();
			$( '.ced_fmcw_loader' ).show();
			$.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_facebook_shopping_list_per_page',
						per_page : per_page,
					},
					type : 'POST',
					success: function( response ) {
						window.location.reload();
					}
				}
			);ced_fmcw_loader

		}
	);
	$( document ).on(
		'click',
		'#ced_facebook_shopping_bulk_operation',
		function(e){
			e.preventDefault();
			$( '.notice' ).remove();
			$( '.ced_fmcw_loader' ).show();
			var operation = $( '.bulk-action-selector' ).val();
			if (operation == -1 ) {
				let message = 'Please select any bulk operation.';
				let status  = 400;
				$( '.ced_fmcw_loader' ).hide();
				ced_google_shopping_display_notice( message,status );
				return false;
			} else {
				var operation                      = $( '.bulk-action-selector' ).val();
				var facebook_shopping_products_ids = new Array();
				$( '.facebook_shopping_products_id:checked' ).each(
					function(){
						facebook_shopping_products_ids.push( $( this ).val() );
					}
				);
				perform_bulk_action( facebook_shopping_products_ids,operation );
			}

		}
	);

	function perform_bulk_action(facebook_shopping_products_ids,operation) {
		if (facebook_shopping_products_ids == '') {
			$( '.ced_fmcw_loader' ).hide();
			let message = 'Please select any products.';
			let status  = 400;
			ced_google_shopping_display_notice( message,status );
			return false;
		}
		$.ajax(
			{
				url : ajaxUrl,
				data : {
					ajax_nonce : ajaxNonce,
					action : 'ced_fmcw_manage_product_bulk_action',
					operation : operation,
					facebook_shopping_products_ids : facebook_shopping_products_ids,
				},
				type : 'POST',
				success: function(response)
						{
						console.log( response );
						$( '.ced_fmcw_loader' ).hide();
						var parsed_response = jQuery.parseJSON( response );
							var message     = parsed_response.message;
							var status      = parsed_response.status;
							ced_google_shopping_display_notice( message , status );

				}
					}
		);
	}

	/*-------------------Display Notices-----------------------*/
	function ced_google_shopping_display_notice( message = '' , status = 200){
		if ( status == 400 ) {
			var classes = 'notice-error';
		} else {
			var classes = 'notice-success';
		}
		var notice = '';
		notice    += '<div class="notice ' + classes + ' ced_google_shopping_notices_content">';
		notice    += '<p>' + message + '</p>';
		notice    += '</div>';
		scroll_at_top();
		$( '.ced_facebook_shopping_notice_era' ).after( notice );
		if (status != 400) {

			window.setTimeout( function(){window.location.reload()}, 4000 );
		}
	}

	function scroll_at_top() {
		$( "html, body" ).animate(
			{
				scrollTop: 0
			},
			600
		);
	}
			$( document ).on(
				'click touchstart',
				'.ced_reset_facebook_entire_setup',
				function(je){
					je.preventDefault();
					let confirmAction = confirm( "Are you sure you want to reset the whole setting.." );
					if (confirmAction) {

						$( '.ced_fmcw_loader' ).show();
						$.ajax(
							{
								url: ajaxUrl,
								data:{
									ajax_nonce : ajaxNonce,
									action: 'ced_reset_facebook_entire_setup',
								},
								type : 'POST',
								datatype : 'json',
								success: function(response){
									$( '.ced_fmcw_loader' ).hide();
									response = jQuery.parseJSON( response );
									if (response.status == 'success') {
										var redirect_url = response.redirect_url;
										window.location  = redirect_url;
										console.log( response );
									}
								}
							}
						)
					}

				}
			);

})( jQuery );
