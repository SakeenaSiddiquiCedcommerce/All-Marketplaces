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
	
	//Disable the next button for import
	$(window).load(function(){
		$('.ced-vidaxl-next-btn1').prop("disabled", true);
		$('#ced_vidaxl_use_draft').val('publish');
	});
	
	//Success Notice
	$('.notice-dismiss').click( function(event){
		$('.notice-success').fadeTo( 90, 0, function() {
			$('.notice-success').remove();
		});	
	});

	//Import products step-by-step logic
	$(function() { 
		$(".ced-vidaxl-next-btn1").click(function() {		 
			$(".ced-vidaxl-display-tab").hide();
			$("#step2").fadeIn(1000);
			$('.ced-vidaxl-progress-circle').removeClass('active');
			$('.ced-vidaxl-progress-circle:nth-child(2)').addClass('active');	
			$("#snackbar").removeClass("show");	
		});
		$(".next-btn2").click(function() {
			
				$(".ced-vidaxl-display-tab").hide();
				$("#step3").fadeIn(1000);
				$('.ced-vidaxl-progress-circle').removeClass('active');
				$('.ced-vidaxl-progress-circle:nth-child(3)').addClass('active');
			
		});		
		$(".submit-btn").click(function() {		
			$("#loader").show();
			setTimeout(function(){
				$("#booking-form").html("<h2>Your message was sent successfully. Thanks! We'll be in touch as soon as we can, which is usually like lightning (Unless we're sailing or eating tacos!).</h2>");
			}, 1000);
			return false;		
		});
		$('#ced_vidaxl_use_draft').change(function(){
			if(this.checked)
				$('#ced_vidaxl_use_draft').val('draft');
			   else
				$('#ced_vidaxl_use_draft').val('publish');
		});
		//Click on language feed dropdown
		$('#ced_vidaxl_feed_language').change( function(){
			$('#ced_vidaxl_feed_language').prop('disabled', true);
			$('.ced-vidaxl-next-btn1').prop('disabled', true);
			var feed_language = $(this).val();
			if(feed_language == ''){
				$('#snackbar').text('Please select the Feed Language');
				$("#snackbar").addClass("show");
				$('#ced_vidaxl_feed_language').prop('disabled', false);
			}else{
				var data = {
					'action':'ced_vidaxl_check_temp_db_table_status',
					'ajax_nonce' : ced_vidaxl_obj.ajax_nonce,
					'feed_language' : feed_language
				};
				$(".vidaxl-loader-div span").addClass("vidaxl-loader-circle");
				$('#snackbar').text('CSV is Fetching from VidaXL Server. Please Wait...');
				$("#snackbar").addClass("show");
				jQuery.post(
					ced_vidaxl_obj.ajax_url,
					data,
					function(response){
						response = JSON.parse(response);
						if(response.status != feed_language)
						{	
							$('.ced-vidaxl-next-btn1').prop('disabled', true);
							var data = {
								'action':'ced_vidaxl_download_csv',
								'ajax_nonce' : ced_vidaxl_obj.ajax_nonce,
								'feed_language' : feed_language
							};
							jQuery.post(
								ced_vidaxl_obj.ajax_url,
								data,
								function(response){
									response = JSON.parse(response);
									if(response.message == 'Downloaded'){
										$('#snackbar').text('CSV has been downloaded. Now it is being processed...');
										$("#snackbar").addClass("show");																					
										var data = {
											'action':'ced_vidaxl_process_csv',
											'ajax_nonce' : ced_vidaxl_obj.ajax_nonce,
											'feed_language' : feed_language
										};
										jQuery.post(
											ced_vidaxl_obj.ajax_url,
											data,
											function(response){
												response = JSON.parse(response);
												if(response.message == 'Processed'){
													var data = {
														'action':'ced_vidaxl_fetch_categories',
														'ajax_nonce' : ced_vidaxl_obj.ajax_nonce,
													};
													jQuery.post(
														ced_vidaxl_obj.ajax_url,
														data,
														function(response){
															response = JSON.parse(response);
															if(response.status == '200'){
																$('#ced_vidaxl_feed_category').html(response.data);
																$('#ced_vidaxl_feed_language').prop('disabled', false);
																$(".vidaxl-loader-div span").removeClass("vidaxl-loader-circle");
																$('.ced-vidaxl-next-btn1').prop("disabled", false);
																$('#snackbar').text('CSV has been Processed. Click Next to Import Products');
																$("#snackbar").addClass("show");	
															}									
														}
													);
												}		
											}	
										);		
									}
									
								}
							);
						}else{
							var data = {
								'action':'ced_vidaxl_fetch_categories',
								'ajax_nonce' : ced_vidaxl_obj.ajax_nonce,
							};
							jQuery.post(
								ced_vidaxl_obj.ajax_url,
								data,
								function(response){
									response = JSON.parse(response);
									if(response.status == '200'){
										$('#ced_vidaxl_feed_category').html(response.data);
										$('#ced_vidaxl_feed_language').prop('disabled', false);
										$(".vidaxl-loader-div span").removeClass("vidaxl-loader-circle");
										$('.ced-vidaxl-next-btn1').prop("disabled", false);
										$('#snackbar').text('Click Next to Import Products');
										$("#snackbar").addClass("show");
									}									
								}
							);
						}
					}
				);	
			}
		});

		$('#ced_vidaxl_import_product_button').click( function(){
			var feed_category = [];
			var min_price 		= $(document).find('#ced_vidaxl_min_price').val();
			var max_price 		= $(document).find('#ced_vidaxl_max_price').val();
			var imprt_draft 	= $(document).find('#ced_vidaxl_use_draft').val();
			var update_data 	= $(document).find('#ced_vidaxl_update_option').val();
			feed_category 	= $(document).find('#ced_vidaxl_feed_category').val();
			
			if(feed_category.length === 0){
				$('#snackbar').text('Please select category');
				$("#snackbar").addClass("show");
				setTimeout(function(){ $('#snackbar').attr("class", ""); }, 3000);
			}else{

				$('#ced_vidaxl_import_product_button').prop("disabled", true);
				$(".vidaxl-loader-div span").addClass("vidaxl-loader-circle");
				var data = {
					'action':'ced_vidaxl_start_import_process',
					'ajax_nonce' : ced_vidaxl_obj.ajax_nonce,
					'feed_category' : feed_category,
					'updated_data' : update_data,
					'imprt_draft' : imprt_draft,
					'min_price' : min_price,
					'max_price' : max_price,
				};
				jQuery.post(
					ced_vidaxl_obj.ajax_url,
					data,
					function(response){
						response = JSON.parse(response);
						if(response.message == 'product_created'){
							$(".vidaxl-loader-div span").removeClass("vidaxl-loader-circle");
							$('#ced_vidaxl_import_product_button').prop("disabled", false);
							var display_text = response.products_created+ ' Products Created Out of '+ response.total_products + ' and '+response.products_updated+' Product(s) Updated';
							$('#snackbar').text(display_text);
							$("#snackbar").addClass("show");						
							setTimeout(function(){ $('#snackbar').attr("class", ""); }, 8000);
						}
					}
				);
			}
		
		
		});
	
	});

})( jQuery );
