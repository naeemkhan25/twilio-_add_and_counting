
"use strict";
var ajaxurl = inwpnotifier.ajax_url;
var security = inwpnotifier.security;
var security_error = inwpnotifier.security_error;
var security_five = inwpnotifier.security_five;
var userid = inwpnotifier.user_id;
var emptyphone = inwpnotifier.empty_phone;
var plugin_urls=inwpnotifier.plugin_urls;

jQuery(function () {
//    jQuery(".variations_form").on("woocommerce_variation_select_change", function () {
//        // Fires whenever variation selects are changed
//        // onloadCallback();
//    });

    jQuery(".single_variation_wrap").on("show_variation", function (event, variation) {
        // Fired when the user selects all the required dropdowns / attributes
        // and a final variation is selected / shown
        var vid = variation.variation_id;
        jQuery('.inwpnotifier-subscribe-form').hide(); //remove existing form
        jQuery('.inwpnotifier-subscribe-form-' + vid).show(); //add subscribe form to show

    });



    jQuery(document).on('click', '.inwpstock_button', function () {
        if (input_ID.value.trim()) {
            if (iti.isValidNumber()) {

        var submit_button_obj = jQuery(this);

         var phone_id = jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpstock_whatsapp_sms').val();
            var country_code = jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwnotifi_country_code').val();
            var phone_number = country_code + phone_id;


            var product_id = jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpnotif-product-id').val();
            var var_id = jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpnotif-variation-id').val();
            if (phone_id == '') {
                jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').fadeIn();
                jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').html("<div class='inwpnotifiererror' style='color:coral;'>" + emptyphone + "</div>");
                return false;
            } else if (country_code == '') {
                jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').fadeIn();
                jQuery(this).closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').html("<div class='inwpnotifiererror' style='color:coral;'>please seletct country code</div>")
            } else {
                var data = {
                    action: 'inwpnotifier_product_subscribe',
                    product_id: product_id,
                    variation_id: var_id,
                    user_phone: phone_number,
                    user_id: userid,
                    security: security,
                    dataobj: inwpnotifier,
                };

                //jQuery.blockUI({message: null});
                if (jQuery.fn.block) {
                    submit_button_obj.closest('.inwpnotifier-subscribe-form').block({message: null});
                } else {
                    var overlay = jQuery('<div id="inwpnotif-bis-overlay"> </div>');
                    overlay.appendTo(submit_button_obj.closest('.inwpnotifier-subscribe-form'));
                }
                jQuery.ajax({
                    type: "post",
                    url: ajaxurl,
                    data: data,
                    success: function (msg) {
                        submit_button_obj.closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').fadeIn(2000);
                        submit_button_obj.closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').html(msg);
                        //jQuery.unblockUI();
                        if (jQuery.fn.block) {
                            submit_button_obj.closest('.inwpnotifier-subscribe-form').unblock();
                        } else {
                            submit_button_obj.closest('.inwpnotifier-subscribe-form').find('#inwpnotif-bis-overlay').fadeOut(400, function () {
                                submit_button_obj.closest('.inwpnotifier-subscribe-form').find('#inwpnotif-bis-overlay').remove();
                            });
                        }
                    },
                    error: function (request, status, error) {
                        if (request.responseText === '-1' || request.responseText === -1) {
                            submit_button_obj.closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').fadeIn(2000);
                            submit_button_obj.closest('.inwpnotifier-subscribe-form').find('.inwpstock_output').html("<div class='inwpnotifiererror' style='color:red;'>" + security_error + "</div>");
                        }
                        //jQuery.unblockUI();
                        if (jQuery.fn.block) {
                            submit_button_obj.closest('.inwpnotifier-subscribe-form').unblock();
                        } else {
                            submit_button_obj.closest('.inwpnotifier-subscribe-form').find('#inwpnotif-bis-overlay').fadeOut(400, function () {
                                submit_button_obj.closest('.inwpnotifier-subscribe-form').find('#inwpnotif-bis-overlay').remove();
                            });
                        }
                    }
                });
            }

                return false;
            }
        }
            return false;


    });

    jQuery(document).on('click','#inwpnotifier_ON_OFF_Setting',function (){
        var current_user_id=jQuery("#inwpnotifier_shop_user_id").val();
        if (jQuery(this).val() == 1) {
            jQuery(this).val(0);
            var values=0
        }
        else if (jQuery(this).val() == 0) {
            jQuery(this).val(1);
            var values=1;
        }
        var data_on_off = {
            action: 'inwpnotifier_vendor_notifier_on_off',
            current_user_id: current_user_id,
            notifier_value:values,
            security:security_five,
        };
        jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data:data_on_off,
                success:function(data){


                },
            });

    });

    return false;
});
