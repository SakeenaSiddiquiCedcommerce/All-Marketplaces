(function($) {
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
    var ajaxUrl = ced_tokopedia_admin_obj.ajax_url;
    var ajaxNonce = ced_tokopedia_admin_obj.ajax_nonce;
    var shop_name = ced_tokopedia_admin_obj.shop_name;
    var parsed_response;

    $(document).on(
        'click',
        '.ced_tokopedia_parent_element',
        function() {

            if ($(this).find('.ced_tokopedia_instruction_icon').hasClass("dashicons-arrow-down-alt2")) {

                $(this).find('.ced_tokopedia_instruction_icon').removeClass("dashicons-arrow-down-alt2");

                $(this).find('.ced_tokopedia_instruction_icon').addClass("dashicons-arrow-up-alt2");

            } else if ($(this).find('.ced_tokopedia_instruction_icon').hasClass("dashicons-arrow-up-alt2")) {

                $(this).find('.ced_tokopedia_instruction_icon').addClass("dashicons-arrow-down-alt2");

                $(this).find('.ced_tokopedia_instruction_icon').removeClass("dashicons-arrow-up-alt2");
            }

            $(this).next('.ced_tokopedia_child_element').toggle(200);
        }
    );

    $(document).on(
        'hover',
        ".ced_tokopedia_thumbnail li img",
        function() {
            $('#preview-image img').attr('src', $(this).attr('src').replace(' ', ''));
            var cedimgSwap = [];
            var imgUrl = "";
            $(".ced_tokopedia_thumbnail li img").each(
                function() {
                    imgUrl = this.src.replace(' ', '');
                    cedimgSwap.push(imgUrl);
                }
            );
        }
    );

    $(document).on(
        'keyup',
        '#ced_tokopedia_search_product_name',
        function() {

            var keyword = $(this).val();

            if (keyword.length < 3) {

                var html = '';
                html += '<li>Please enter 3 or more characters.</li>';
                $(document).find('.ced-tokopedia-search-product-list').html(html);
                $(document).find('.ced-tokopedia-search-product-list').show();
                return;
            }
            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    keyword: keyword,
                    action: 'ced_tokopedia_search_product_name',
                },
                type: 'POST',
                success: function(response) {

                    parsed_response = jQuery.parseJSON(response);
                    $(document).find('.ced-tokopedia-search-product-list').html(parsed_response.html);
                    $(document).find('.ced-tokopedia-search-product-list').show();
                }
            });
        }
    );

    $(document).on(
        'click',
        '.ced_tokopedia_searched_product',
        function() {
            $('.ced_tokopedia_loader').show();
            var post_id = $(this).data('post-id');
            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    post_id: post_id,
                    action: 'ced_tokopedia_get_product_metakeys',
                },
                type: 'POST',
                success: function(response) {
                    $('.ced_tokopedia_loader').hide();
                    parsed_response = jQuery.parseJSON(response);
                    $(document).find('.ced-tokopedia-search-product-list').hide();
                    $(".ced_tokopedia_render_meta_keys_content").html(parsed_response.html);
                    $(".ced_tokopedia_render_meta_keys_content").show();
                }
            });
        }
    );

    $(document).on(
        'change',
        '.ced_tokopedia_meta_key',
        function() {
            $('.ced_tokopedia_loader').show();
            var metakey = $(this).val();
            var operation;
            if ($(this).is(':checked')) {
                operation = 'store';
            } else {
                operation = 'remove';
            }

            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    action: 'ced_tokopedia_process_metakeys',
                    metakey: metakey,
                    operation: operation,
                },
                type: 'POST',
                success: function(response) {
                    $('.ced_tokopedia_loader').hide();
                }
            });
        }
    );

    $(document).on(
        'click',
        '.ced_tokopedia_navigation',
        function() {
            $('.ced_tokopedia_loader').show();
            var page_no = $(this).data('page');
            $('.ced_tokopedia_metakey_body').hide();
            window.setTimeout(function() { $('.ced_tokopedia_loader').hide() }, 500);
            $(document).find('.ced_tokopedia_metakey_list_' + page_no).show();
        }
    );

    $(document).on(
        "click",
        ".ced_tokopedia_add_account_button",
        function() {

            $(document).find('.ced_tokopedia_add_account_popup_main_wrapper').addClass('show');

        }
    )

    $(document).on(
        "click",
        ".ced_tokopedia_add_account_popup_close",
        function() {

            $(document).find('.ced_tokopedia_add_account_popup_main_wrapper').removeClass('show');

        }
    )

    $(document).on(
        'click',
        '#ced_tokopedia_submit_shipment',
        function() {

            var can_ajax = true;
            $('.ced_tokopedia_required_data').each(
                function() {
                    if ($(this).val() == '') {
                        $(this).css('border', '1px solid red');
                        can_ajax = false;
                        return false;
                    } else {
                        $(this).removeAttr('style');
                    }
                }
            );

            if (can_ajax) {
                $(this).addClass('disabled');
                $('.ced_spinner').css('visibility', 'visible');
                var ced_tokopedia_tracking_code = $('#ced_tokopedia_tracking_code').val();
                var order_id = $(this).data('order-id');

                $.ajax({
                    url: ajaxUrl,
                    data: {
                        ajax_nonce: ajaxNonce,
                        action: 'ced_tokopedia_submit_shipment',
                        ced_tokopedia_tracking_code: ced_tokopedia_tracking_code,
                        order_id: order_id,
                    },
                    type: 'POST',
                    success: function(response) {
                        $("#ced_tokopedia_submit_shipment").removeClass('disabled');
                        $('.ced_spinner').css('visibility', 'hidden');
                        parsed_response = jQuery.parseJSON(response);
                        var classes = classes = 'notice notice-success';
                        if (parsed_response.status == 400) {
                            classes = 'notice notice-error';
                        }
                        var html = '<div class="' + classes + '"><p>' + parsed_response.message + '</p></div>';
                        $('.ced_tokopedia_error').html(html);
                        window.setTimeout(function() { window.location.reload(); }, 5000);
                    }
                });
            }
        }
    );

    $(document).on(
        'click',
        '.ced_tokopedia_add_more_shop_section',
        function() {
            var repeatable = $(this).parents('tr').clone();
            $(repeatable).find(".ced_tokopedia_shopsection_inputs").val("");
            $(repeatable).insertAfter($(this).parents('tr'));
            $(this).parent('td').remove();
        }
    );

    $(document).on(
        'change',
        '.ced_tokopedia_select_store_category_checkbox',
        function() {

            var store_category_id = $(this).attr('data-categoryID');

            if ($(this).is(':checked')) {
                $('#ced_tokopedia_categories_' + store_category_id).show('slow');
            } else {
                $('#ced_tokopedia_categories_' + store_category_id).hide('slow');
            }
        }
    );

    $(document).on(
        'click',
        '#ced_tokopedia_category_refresh_button',
        function() {
            shop_name = $(this).data('shopname');
            $('.ced_tokopedia_loader').show();
            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    action: 'ced_tokopedia_category_refresh',
                    shop_name: shop_name
                },
                type: 'POST',
                success: function(response) {
                    $('.ced_tokopedia_loader').hide();
                    var response = jQuery.parseJSON(response);
                    var response1 = jQuery.trim(response.message);
                    if (response1 == "Shop is Not Active") {
                        var notice = "";
                        notice += "<div class='notice notice-error'><p>Currently Shop is not Active . Please activate your Shop in order to refresh categories.</p></div>";
                        $(".success-admin-notices").append(notice);
                        return;
                    } else if (response.status == 200) {
                        var notice = "";
                        notice += "<div class='notice notice-success'><p>Categories Updated Successfully</p></div>";
                        $(".success-admin-notices").append(notice);
                        window.setTimeout(function() { window.location.reload() }, 2000);
                    } else {
                        var notice = "";
                        notice += "<div class='notice notice-error'><p>Unable To Fetch Categories</p></div>";
                        $(".success-admin-notices").append(notice);
                        window.setTimeout(function() { window.location.reload() }, 2000);
                    }
                }
            });
        }
    );

    $(document).on(
        'change',
        '.ced_tokopedia_select_category_on_add_profile',
        function() {

            var selected_tokopedia_category_id = $(this).val();
            var selected_tokopedia_category_name = $(this).find("option:selected").text();

            var level = $(this).attr('data-level');

            if (level != '8') {

                $('.ced_tokopedia_loader').show();

                $.ajax({
                    url: ajaxUrl,
                    data: {
                        ajax_nonce: ajaxNonce,
                        action: 'ced_tokopedia_fetch_next_level_category_add_profile',
                        level: level,
                        name: selected_tokopedia_category_name,
                        id: selected_tokopedia_category_id,
                        tokopedia_store_id: shop_name
                    },
                    type: 'POST',
                    success: function(response) {

                        response = jQuery.parseJSON(response);
                        $('.ced_tokopedia_loader').hide();
                        if (response != 'No-Sublevel') {
                            for (var i = 1; i < 10; i++) {

                                $('#ced_tokopedia_categories_in_profile').find('.ced_tokopedia_level' + (parseInt(level) + i) + '_category').remove();
                            }
                            if (response != 0 && selected_tokopedia_category_id != "") {

                                $('#ced_tokopedia_categories_in_profile').append(response);
                            }
                        } else {
                            $('#ced_tokopedia_categories_in_profile').find('.ced_tokopedia_level' + (parseInt(level) + 1) + '_category').remove();
                        }
                    }
                });
            }

        }
    );

    $(document).on(
        'change',
        '.ced_tokopedia_select_category',
        function() {

            var store_category_id = $(this).attr('data-storeCategoryID');
            var selected_tokopedia_category_id = $(this).val();
            var selected_tokopedia_category_name = $(this).find("option:selected").text();
            var level = $(this).attr('data-level');
            if (level != '10') {
                $('.ced_tokopedia_loader').show();
                $.ajax({
                    url: ajaxUrl,
                    data: {
                        ajax_nonce: ajaxNonce,
                        action: 'ced_tokopedia_fetch_next_level_category',
                        level: level,
                        name: selected_tokopedia_category_name,
                        id: selected_tokopedia_category_id,
                        store_id: store_category_id,
                    },
                    type: 'POST',
                    success: function(response) {
                        response = jQuery.parseJSON(response);
                        $('.ced_tokopedia_loader').hide();
                        if (response != 'No-Sublevel') {
                            for (var i = 1; i < 10; i++) {
                                $('#ced_tokopedia_categories_' + store_category_id).find('.ced_tokopedia_level' + (parseInt(level) + i) + '_category').closest("td").remove();
                            }
                            if (response != 0) {
                                $('#ced_tokopedia_categories_' + store_category_id).append(response);
                            }
                        } else {
                            $('#ced_tokopedia_categories_' + store_category_id).find('.ced_tokopedia_level' + (parseInt(level) + 1) + '_category').remove();
                        }
                    }
                });
            }

        }
    );

    $(document).on(
        'click',
        '#ced_tokopedia_save_category_button',
        function() {

            var tokopedia_category_array = [];
            var store_category_array = [];
            var tokopedia_category_name = [];
            var shopname = $(this).attr('data-tokopediaStoreName');
            var shop_name = jQuery.trim(shopname);
            jQuery('.ced_tokopedia_select_store_category_checkbox').each(
                function(key) {

                    if (jQuery(this).is(':checked')) {

                        var store_category_id = $(this).attr('data-categoryid');
                        var cat_level = $('#ced_tokopedia_categories_' + store_category_id).find("td:last").attr('data-catlevel');
                        var selected_tokopedia_category_id = $('#ced_tokopedia_categories_' + store_category_id).find('.ced_tokopedia_level' + cat_level + '_category').val();
                        if (selected_tokopedia_category_id == '' || selected_tokopedia_category_id == null) {
                            selected_tokopedia_category_id = $('#ced_tokopedia_categories_' + store_category_id).find('.ced_tokopedia_level' + (parseInt(cat_level) - 1) + '_category').val();
                        }

                        var category_name = '';

                        $('#ced_tokopedia_categories_' + store_category_id).find('select').each(
                            function(key1) {
                                category_name += $(this).find("option:selected").text() + ' --> ';
                            }
                        );

                        var name_len = 0;
                        if (selected_tokopedia_category_id != '' && selected_tokopedia_category_id != null) {
                            tokopedia_category_array.push(selected_tokopedia_category_id);
                            store_category_array.push(store_category_id);

                            name_len = category_name.length;
                            category_name = category_name.substring(0, name_len - 5);
                            category_name = category_name.trim();
                            name_len = category_name.length;

                            if (category_name.lastIndexOf('--Select--') > 0) {
                                category_name = category_name.trim();
                                category_name = category_name.replace('--Select--', '');
                                name_len = category_name.length;
                                category_name = category_name.substring(0, name_len - 5);
                            }
                            name_len = category_name.length;
                            tokopedia_category_name.push(category_name);

                        }
                    }
                }
            );
            $('.ced_tokopedia_loader').show();
            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    action: 'ced_tokopedia_map_categories_to_store',
                    tokopedia_category_array: tokopedia_category_array,
                    store_category_array: store_category_array,
                    tokopedia_category_name: tokopedia_category_name,
                    storeName: shop_name
                },
                type: 'POST',
                success: function(response) {
                    $('.ced_tokopedia_loader').hide();
                    var html = "<div class='notice notice-success'><p>Profile Created Successfully</p></div>";
                    $("#profile_create_message").html(html);
                    /* window.setTimeout(function(){*/ // window.location.reload()/*}, 3000)*/;
                }
            });

        }
    );

    $(document).on(
        'click',
        '#ced_tokopedia_bulk_operation',
        function(e) {

            e.preventDefault();
            var operation = $(".bulk-action-selector").val();
            if (operation <= 0) {
                var notice = "";
                notice += "<div class='notice notice-error'><p>Please Select Operation To Be Performed</p></div>";
                $(".success-admin-notices").append(notice);
            } else {

                var operation = $(".bulk-action-selector").val();
                var tokopedia_products_id = new Array();
                $('.tokopedia_products_id:checked').each(
                    function() {
                        tokopedia_products_id.push($(this).val());
                    }
                );
                performBulkAction(tokopedia_products_id, operation);
            }

        }
    );

    function performBulkAction(tokopedia_products_id, operation) {

        if (tokopedia_products_id == "") {
            var notice = "";
            notice += "<div class='notice notice-error'><p>No Products Selected</p></div>";
            $(".success-admin-notices").append(notice);
            return false;
        }

        $('.ced_tokopedia_loader').show();
        var tokopedia_products_id_to_perform = tokopedia_products_id[0];
        $.ajax({
            url: ajaxUrl,
            data: {
                ajax_nonce: ajaxNonce,
                action: 'ced_tokopedia_process_bulk_action',
                operation_to_be_performed: operation,
                id: tokopedia_products_id_to_perform,
                shopname: shop_name
            },
            type: 'POST',
            success: function(response) {
                $('.ced_tokopedia_loader').hide();
                var response = jQuery.parseJSON(response);
                var response1 = jQuery.trim(response.message);
                if (response1 == "Shop is Not Active") {
                    var notice = "";
                    notice += "<div class='notice notice-error'><p>Currently Shop is not Active . Please activate your Shop in order to perform operations.</p></div>";
                    $(".success-admin-notices").append(notice);
                    return;
                } else if (response.status == 200) {
                    var id = response.prodid;
                    var Response_message = jQuery.trim(response.message);
                    var notice = "";
                    notice += "<div class='notice notice-success'><p>" + response.message + "</p></div>";
                    $(".success-admin-notices").append(notice);
                    if (Response_message == 'Product ' + id + ' Deleted Successfully') {
                        $("#" + id + "").html('<b class="not_completed">Not Uploaded</b>');
                        $("." + id + "").remove();
                    } else {
                        $("#" + id + "").html('<b class="success_upload_on_tokopedia">Uploaded</b>');
                    }

                    var remainig_products_id = tokopedia_products_id.splice(1);
                    if (remainig_products_id == "") {
                        return;
                    } else {
                        performBulkAction(remainig_products_id, operation);
                    }

                } else if (response.status == 400) {
                    var notice = "";
                    notice += "<div class='notice notice-error'><p>" + response.message + "</p></div>";
                    $(".success-admin-notices").append(notice);
                    var remainig_products_id = tokopedia_products_id.splice(1);
                    if (remainig_products_id == "") {
                        return;
                    } else {
                        performBulkAction(remainig_products_id, operation);
                    }

                }
            }
        });
    }

    $(document).on(
        'click',
        '.ced_tokopedia_profiles_on_pop_up',
        function() {

            var product_id = $(this).attr("data-product_id");
            var shopid = $(this).attr("data-shopid");
            $.ajax({

                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    prodId: product_id,
                    shopid: shopid,
                    action: 'ced_tokopedia_profiles_on_pop_up'
                },
                type: 'POST',
                success: function(response) {
                    $(".ced_tokopedia_preview_product_popup_main_wrapper").html(response);
                    $(document).find('.ced_tokopedia_preview_product_popup_main_wrapper').addClass('show');
                }

            });
        }
    );

    $(document).on(
        'click',
        '#save_tokopedia_profile_through_popup',
        function() {
            $('.ced_tokopedia_loader').show();
            var product_id = $(this).attr("data-prodId");
            var shopid = $(this).attr("data-shopid");
            var profile_id = $(".ced_tokopedia_profile_selected_on_popup").val();
            $.ajax({

                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    prodId: product_id,
                    shopid: shopid,
                    profile_id: profile_id,
                    action: 'save_tokopedia_profile_through_popup'
                },
                type: 'POST',
                success: function(response) {
                    response = jQuery.trim(response);
                    if (response == "null") {
                        $('.ced_tokopedia_loader').hide();
                        $(document).find('.ced_tokopedia_preview_product_popup_main_wrapper').removeClass('show');
                        var notice = "";
                        notice += "<div class='notice notice-error'><p>No Profile Selected.</p></div>";
                        $(".success-admin-notices").append(notice);
                        window.setTimeout(function() { window.location.reload() }, 2500);

                    } else {
                        $('.ced_tokopedia_loader').hide();
                        location.reload(true);
                    }
                }

            });

        }
    );

    $(document).on(
        'click',
        '#ced_tokopedia_preview',
        function() {

            var product_id = $(this).attr("data");
            var shopid = $(this).attr("data-shopid");
            $.ajax({

                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    prodId: product_id,
                    shopid: shopid,
                    action: 'ced_tokopedia_preview_product_detail'
                },
                type: 'POST',
                success: function(response) { /*alert(response);*/
                    $(".ced_tokopedia_preview_product_popup_main_wrapper").html(response);
                    $(document).find('.ced_tokopedia_preview_product_popup_main_wrapper').addClass('show');
                }

            });
        }
    );

    $(document).on(
        'click',
        '#ced_tokopedia_fetch_orders',
        function(event) {

            event.preventDefault();
            var store_id = $(this).attr('data-id');
            $('.ced_tokopedia_loader').show();
            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    action: 'ced_tokopedia_get_orders',
                    shopid: store_id
                },
                type: 'POST',
                success: function(response) {

                    $('.ced_tokopedia_loader').hide();
                    var response = jQuery.parseJSON(response);
                    var response1 = jQuery.trim(response.message);
                    if (response1 == "Shop is Not Active") {
                        var notice = "";
                        notice += "<div class='notice notice-error'><p>Currently Shop is not Active . Please activate your Shop in order to fetch orders.</p></div>";
                        $(".success-admin-notices").append(notice);
                        return;
                    } else {
                        location.reload(true);
                    }
                }
            });
        }
    );
    $(document).on(
        'click',
        "#ced_tokopedia_update_account_status",
        function() {
            var status = $("#ced_tokopedia_account_status").val();
            var shop_name = $(this).attr("data-id");
            var url = window.location.href;
            $.ajax({
                url: ajaxUrl,
                data: {
                    ajax_nonce: ajaxNonce,
                    action: 'ced_tokopedia_change_account_status',
                    status: status,
                    shop_name: shop_name
                },
                type: 'POST',
                success: function(response) {
                    var response = jQuery.parseJSON(response);
                    window.location.href = url;
                }

            });

        }
    );

    $(document).on(
        'click',
        '.ced_tokopedia_profile_popup_close',
        function() {

            $(document).find('.ced_tokopedia_preview_product_popup_main_wrapper').removeClass('show');

        }
    );

    $(document).on(
        'click',
        '.ced_tokopedia_preview_product_popup_close',
        function() {

            $(document).find('.ced_tokopedia_preview_product_popup_main_wrapper').removeClass('show');

        }
    );
    $(document).on(
        'change',
        '#ced_tokopedia_scheduler_info',
        function() {

            if (this.checked) {
                $(".ced_tokopedia_scheduler_info").css('display', 'contents');
            } else {
                $(".ced_tokopedia_scheduler_info").css('display', 'none');
            }
        }
    );

})(jQuery);