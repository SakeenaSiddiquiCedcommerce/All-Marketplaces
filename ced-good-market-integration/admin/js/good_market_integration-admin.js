(function( $ ) {
	'use strict';
	var ajax_url   = ced_good_market_admin_obj.ajax_url;
	var ajax_nonce = ced_good_market_admin_obj.ajax_nonce;
	var parsed_response,message,status,classes,notice;

	$( document ).on(
		'click' ,
		'#ced_good_market_fetch_orders' ,
		function() {
			$( '.ced_good_market_loader' ).show();
			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						action : 'ced_good_market_get_orders_manual',
					},
					type : 'POST',
					success: function(response)
				{
						$( '.ced_good_market_loader' ).hide();
						parsed_response = jQuery.parseJSON( response );
						message         = parsed_response.message;
						status          = parsed_response.status;
						ced_good_market_display_notice( message , status );
					}

				}
			);
		}
	);

	$( document ).on(
		'keyup' ,
		'#ced_good_market_search_product_name' ,
		function() {
			var keyword = $( this ).val();
			if ( keyword.length < 3 ) {
				var html = '';
				html    += '<li>Please enter 3 or more characters.</li>';
				$( document ).find( '.ced-good_market-search-product-list' ).html( html );
				$( document ).find( '.ced-good_market-search-product-list' ).show();
				return;
			}
			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						keyword : keyword,
						action : 'ced_good_market_search_product_name',
					},
					type:'POST',
					success : function( response ) {
						parsed_response = jQuery.parseJSON( response );
						$( document ).find( '.ced-good_market-search-product-list' ).html( parsed_response.html );
						$( document ).find( '.ced-good_market-search-product-list' ).show();
					}
				}
			);
		}
	);

	$( document ).on(
		'click' ,
		'.ced_good_market_searched_product' ,
		function() {
			$( '.ced_good_market_loader' ).show();
			var post_id = $( this ).data( 'post-id' );
			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						post_id : post_id,
						action : 'ced_good_market_get_product_metakeys',
					},
					type:'POST',
					success : function( response ) {
						$( '.ced_good_market_loader' ).hide();
						parsed_response = jQuery.parseJSON( response );
						$( document ).find( '.ced-good_market-search-product-list' ).hide();
						$( ".ced_good_market_render_meta_keys_content" ).html( parsed_response.html );
						$( ".ced_good_market_render_meta_keys_content" ).show();
					}
				}
			);
		}
	);
	$( document ).on(
		'change',
		'.ced_good_market_meta_key',
		function(){
			$( '.ced_good_market_loader' ).show();
			var metakey = $( this ).val();
			var operation;
			if ( $( this ).is( ':checked' ) ) {
				operation = 'store';
			} else {
				operation = 'remove';
			}

			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						action : 'ced_good_market_process_metakeys',
						metakey : metakey ,
						operation : operation,
					},
					type : 'POST',
					success: function(response)
				{
						$( '.ced_good_market_loader' ).hide();
					}
				}
			);
		}
	);

	$( document ).on(
		'click' ,
		'.ced_good_market_navigation' ,
		function() {
			$( '.ced_good_market_loader' ).show();
			var page_no = $( this ).data( 'page' );
			$( '.ced_good_market_metakey_body' ).hide();
			window.setTimeout( function() {$( '.ced_good_market_loader' ).hide()},500 );
			$( document ).find( '.ced_good_market_metakey_list_' + page_no ).show();
		}
	);
	/*-------------------Toggle Chile Elements-----------------------*/
	$( document ).on(
		'click',
		'.ced_good_market_parent_element',
		function(){
			if ($( this ).find( '.ced_good_market_instruction_icon' ).hasClass( "dashicons-arrow-down-alt2" )) {
				$( this ).find( '.ced_good_market_instruction_icon' ).removeClass( "dashicons-arrow-down-alt2" );
				$( this ).find( '.ced_good_market_instruction_icon' ).addClass( "dashicons-arrow-up-alt2" );
			} else if ($( this ).find( '.ced_good_market_instruction_icon' ).hasClass( "dashicons-arrow-up-alt2" )) {
				$( this ).find( '.ced_good_market_instruction_icon' ).addClass( "dashicons-arrow-down-alt2" );
				$( this ).find( '.ced_good_market_instruction_icon' ).removeClass( "dashicons-arrow-up-alt2" );
			}
			$( this ).next( '.ced_good_market_child_element' ).slideToggle( 'slow' );
		}
	);

	$( document ).on(
		'click',
		'.ced_good_market_global_tab_label',
		function(){
			var tab = $( this ).data( 'tab' );
			$( '.ced_good_market_global_tab_label' ).removeClass( 'active' );
			$( this ).addClass( 'active' );
			$( '.ced_tab_content div' ).hide();
			$( '#' + tab + '' ).show();
		}
	);

	/*-------------------Process API keys-----------------------*/

	$( document ).on(
		'click',
		'[name="ced_good_market_save_currency_convert_rate"]',
		function(je){
			$( '.ced_goodmarket_validation_notice' ).text( '' );
			var currency_value = jQuery( '[name="ced_good_market_currency_convert_rate"]' ).val();
			if (isNaN( currency_value ) || currency_value <= 0 || currency_value == '') {
				var error_notice = '<p class="ced_goodmarket_validation_notice">Currency should be numeric. </p>';
				je.preventDefault();
				$( '[name="ced_good_market_currency_convert_rate"]' ).css( 'border','1px solid red' );
				$( error_notice ).insertAfter( '[name="ced_good_market_currency_convert_rate"]' );
			}

		}
	);
	$( document ).on(
		'click',
		'#ced_good_market_update_api_keys',
		function(){
			$( '.ced_goodmarket_validation_notice' ).text( '' );

			var can_ajax = true;
			/*$( '.ced_good_market_required_data' ).each(
				function() {

					if ( $( this ).val() == '' ) {
						$( this ).css( 'border' , '1px solid red' );
						message = 'Please fill the required details !!';
						status  = 400;
						ced_good_market_display_notice( message , status );
						can_ajax = false;
						return false;
					} else {
						$( this ).removeAttr( 'style' );
					}
				}
			);*/

			var client_email , client_pass, vendor_id;
			client_email = $( '#ced_good_market_client_id' ).val();
			vendor_id    = $( '#vendor_id' ).val();
			if (isNaN( vendor_id ) || vendor_id == '') {
				var error_notice = '<p class="ced_goodmarket_validation_notice">Vendor ID should be Numeric</p>';
				can_ajax         = false;
				$( '#vendor_id' ).css( 'border','1px solid red' );
				$( error_notice ).insertAfter( "#vendor_id" );
			}
			var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

			if ( ! client_email.match( validRegex ) || client_email == '') {

				var error_notice = '<p class="ced_goodmarket_validation_notice">Invalid E-Mail Id</p>';
				can_ajax         = false;
				$( '#ced_good_market_client_id' ).css( 'border','1px solid red' );
				$( error_notice ).insertAfter( "#ced_good_market_client_id" );
			}
			if ( can_ajax ) {
				$( '.ced_good_market_loader' ).show();
				$.ajax(
					{
						url : ajax_url,
						data :{
							ajax_nonce : ajax_nonce,
							client_email : client_email,
							// client_pass : client_pass,
							vendor_id : vendor_id,
							action : 'ced_good_market_process_api_keys',
						},
						type : 'POST',
						dataType :'json',
						success: function( response ) {
							console.log( response );
							$( '.ced_good_market_loader' ).hide();
							let parsed_response = ( response.data );
							if (response.success == true) {
								$( '.msg_dis' ).removeClass( 'msg_dis_error' );
								$( '.msg_dis' ).html( parsed_response );
								$( '.msg_dis' ).addClass( 'msg_dis_sucess' );
							} else {
								$( '.msg_dis' ).removeClass( 'msg_dis_sucess' );
								$( '.msg_dis' ).html( parsed_response );
								$( '.msg_dis' ).addClass( 'msg_dis_error' );
							}
							window.setTimeout( function(){window.location.reload()}, 2000 );
						}
					}
				);
			}
		}
	);

	$( document ).on(
		'click',
		'#ced_good_market_bulk_operation',
		function(e){
			e.preventDefault();
			$( '.ced_good_market_loader' ).show();
			var operation = $( '.bulk-action-selector' ).val();
			if (operation <= 0 ) {
				message = 'Please select any bulk operation.';
				status  = 400;
				$( '.ced_good_market_loader' ).hide();
				ced_good_market_display_notice( message,status );
				return false;
			} else {
				var operation                = $( '.bulk-action-selector' ).val();
				var good_market_products_ids = new Array();
				$( '.good_market_products_id:checked' ).each(
					function(){
						good_market_products_ids.push( $( this ).val() );
					}
				);
				perform_bulk_action( good_market_products_ids,operation );
			}

		}
	);

	function perform_bulk_action(good_market_products_ids,operation) {
		if (good_market_products_ids == '') {
			$( '.ced_good_market_loader' ).hide();
			message = 'Please select any products.';
			status  = 400;
			ced_good_market_display_notice( message,status );
			return false;
		}
		$.ajax(
			{
				url : ajax_url,
				data : {
					ajax_nonce : ajax_nonce,
					action : 'ced_good_market_process_bulk_action',
					operation : operation,
					good_market_products_ids : good_market_products_ids,
				},
				type : 'POST',
				success: function(response)
			{
					$( '.ced_good_market_loader' ).hide();
					parsed_response = jQuery.parseJSON( response );
					console.log( parsed_response.error );
					var reload_condition = '';
					if(operation != 'delete') {
						var message = '';
						if (parsed_response.job_id != '' && parsed_response.job_id != null) {
							message += " Products added in queue of feeds, for feed details <a href="+parsed_response.job_detail_url+">click here</a> .";
						} 
						if (parsed_response.not_mapped_ids != '' && parsed_response.not_mapped_ids != null) {
							message += ' Category Not Mapped" so - some products has been not added to queue.';
						}
						if (parsed_response.error == 'Attribute not mapped in some products' && parsed_response.error != '') {
							var message = " Products added in queue of feeds";
							message += ', ' + parsed_response.error;
						}
						status = 200;
						reload_condition = 'disabled';
					} else {
						status = 200;
						var message = 'Products added in queue of feeds for delete.';
						reload_condition = '';
					}
					ced_good_market_display_notice( message , status , reload_condition );
				}
			}
		);
	}

	/*-------------------Display Notices-----------------------*/
	function ced_good_market_display_notice( message = '' , status = 200, reload_condition=''){
		var reload_condition = reload_condition;
		if ( status == 400 ) {
			classes = 'notice-error';
		} else {
			classes = 'notice-success';
		}
		notice  = '';
		notice += '<div class="notice ' + classes + ' ced_good_market_notices_content">';
		notice += '<p>' + message + '</p>';
		notice += '<span class= "ced_goodmarket_remove_notice">x</span></div>';
		scroll_at_top();
		$( '.ced_good_market_heading' ).after( notice );
		if (status != 400 && reload_condition == '') {

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
		'click',
		'.ced_goodmarket_remove_notice',
		function(){
			jQuery(this).parent( ".notice" ).remove();
	});

	// For good_market  category Selection

	$( document ).on(
		'change',
		'.ced_good_market_category',
		function(){
        	var element_id = $(this).attr('id');

			let value   = $( this ).val();
			let catId   = $( this ).attr( "data-store-category-id" );
			let catname = $( this ).find( ":selected" ).text();
			$( '.ced_good_market_loader' ).show();
			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						action : 'ced_good_market_save_cat',
						value:value,
						catId:catId,
						catname:catname,
					},
					type : 'POST',
					success: function(response) {
						$( '.ced_good_market_loader' ).hide();
                        var aria_labelledby = 'select2-'+element_id+'-container';
                        $("[aria-labelledby="+aria_labelledby+"]").css("border", "2.5px solid #2271b1");
                        setTimeout(function(){
                            $("[aria-labelledby="+aria_labelledby+"]").css("border", "");
                        },4000);
					}
				}
			);

		}
	);

	$( document ).on(
		'click',
		'#ced_good_market_shipment_submit',
		function(){

			var can_ajax = true;
			$( '.ced_good_market_required_data' ).each(
				function() {
					if ( $( this ).val() == '' || $( this ).val() == null || $( this ).val() == undefined) {
						$( this ).css( 'border' , '1px solid red' );
						message = 'Please fill the required details !!';
						status  = 400;
						ced_good_market_display_notice( message , status );
						can_ajax = false;
						return false;
					} else {
						$( this ).removeAttr( 'style' );
					}
				}
			);
			if (can_ajax) {

				let order_id                         = $( '#woocommerce_orderid' ).val();
				let good_market_orderid              = $( '#good_market_orderid' ).val();
				let ced_good_market_tracking_num     = $( '#ced_good_market_tracking_num' ).val();
				let ced_good_market_tracking_title   = $( '#ced_good_market_tracking_title' ).val();
				let ced_good_market_tracking_carrier = $( '#ced_good_market_tracking_carrier' ).val();
				let ced_good_market_tracking_comment = $( '#ced_good_market_tracking_comment' ).val();

				$( '.ced_good_market_loader' ).show();
				$.ajax(
					{
						url : ajax_url,
						data : {
							ajax_nonce : ajax_nonce,
							action : 'ced_good_market_ship_order',
							order_id:order_id,
							good_market_orderid:good_market_orderid,
							ced_good_market_tracking_num:ced_good_market_tracking_num,
							ced_good_market_tracking_title:ced_good_market_tracking_title,
							ced_good_market_tracking_carrier:ced_good_market_tracking_carrier,
							ced_good_market_tracking_comment:ced_good_market_tracking_comment,
						},
						type : 'POST',
						success: function(response) {
							$( '.ced_good_market_loader' ).hide();
							window.location.reload();
						}
					}
				);
			}

		}
	);

	// For good_market  category Selection

	$( document ).on(
		'click',
		'#ced_good_market_update_categories',
		function(){

			$( '.ced_good_market_loader' ).show();
			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						action : 'ced_good_market_update_categories',
					},
					type : 'POST',
					success: function(response) {
						$( '.ced_good_market_loader' ).hide();
						window.location.reload();

					}
				}
			);

		}
	);

	$( document ).on(
		'change',
		'#ced_good_market_list_per_page' ,
		function() {
			var per_page = $( this ).val();
			$( '.ced_good_market_loader' ).show();
			$.ajax(
				{
					url : ajax_url,
					data : {
						ajax_nonce : ajax_nonce,
						action : 'ced_good_market_list_per_page',
						per_page : per_page,
					},
					type : 'POST',
					success: function( response ) {
						window.location.reload();
					}
				}
			);

		}
	);
	$( document ).on(
		'change',
		'.ced_good_market_attribie_mapping',
		function(){
			var selected_value = $( this ).val();
			var current_id     = $( this ).attr( 'id' );

			$( ".ced_good_market_attribie_mapping" ).each(
				function() {

					if ($( this ).attr( 'id' ) != current_id) {

						$( this ).find( "option" ).each(
							function(){
								var value = $( this ).val();
								if (jQuery.inArray( value,selected_value ) != -1) {

									$( this ).remove();
								}
							}
						);
					}
				}
			);
		}
	);

	jQuery( document ).ready(
		function(){

			jQuery( '#_color_attribute_meta' ).prop( 'multiple','multiple' );
			// jQuery('#_color_attribute_meta').select2();
			jQuery( "#_color_attribute_meta" ).select2(
				{
					width: 'resolve'
				}
			);
			jQuery( '#_size_attribute_meta' ).prop( 'multiple','multiple' );
			// jQuery('#_size_attribute_meta').select2();
			jQuery( "#_size_attribute_meta" ).select2(
				{
					width: 'resolve'
				}
			);
			jQuery( '#_type_attribute_meta' ).prop( 'multiple','multiple' );
			// jQuery('#_type_attribute_meta').select2();
			jQuery( "#_type_attribute_meta" ).select2(
				{
					width: 'resolve'
				}
			);
			$('#pro_cat_filter_sorting').select2({
			    maximumSelectionSize: 1
			}).on('select2-opening', function(e) {
			    if ($(this).select2('val').length > 0) {
			        e.preventDefault();
			    }
			});

			$('.ced_good_market_category').each(function(index, element) {
				jQuery(element).select2();
				jQuery( element ).select2(
				{
					width: 'resolve'
				}
				);
			});
		}
	);

})( jQuery );