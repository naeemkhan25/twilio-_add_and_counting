let ajax_url=inwpnotifier_api_on_of.ajax_url;
let inwp_nonce=inwpnotifier_api_on_of.security;

jQuery(function () {

    jQuery(document).on('click','#inwpnotifier_whatsapp_toggle',function (){
        if (jQuery(this).val() == 1) {
            jQuery(this).val(0);
            var values=0
        }
        else if (jQuery(this).val() == 0) {
            jQuery(this).val(1);
            var values = 1;
        }
        var data_on_off = {
            action: 'inwpnotifier_api_whatsapp_on_of',
            inwpnotifier_whatsapp_value:values,
            security:inwp_nonce,
        };
        jQuery.ajax({
            url: ajax_url,
            type: "POST",
            data:data_on_off,
            success:function(data){

            },
        });

    });

});

