;(function ($) {
    $(document).ready(function () {


        var $mainNav = jQuery(".inwpnotifier-tab-ul");
        $mainNav.append("<li id='inwpnotifier-tab-magic-line' style='display:none;'></li>");
        var $magicLine = jQuery("#inwpnotifier-tab-magic-line");
        setTimeout(function () {
            $magicLine.show();
            update_tb_line();
        })

        function update_tb_line() {
            var inwpnotifier_active_tab = jQuery(".inwpnotifier-nav-tab-active");

            if (!inwpnotifier_active_tab.length) {
                $magicLine.hide();
                return;
            }

            var inwpnotifier_active_tab_par_pos = inwpnotifier_active_tab.parent().position();
            $magicLine
                .width(inwpnotifier_active_tab.parent().width())
                .css({
                    "left": inwpnotifier_active_tab_par_pos.left,
                    "top": inwpnotifier_active_tab_par_pos.top + 21
                })
                .data("origLeft", $magicLine.position().left)
                .data("origWidth", $magicLine.width());
            if (inwpnotifier_active_tab.hasClass("inwpnotifier_ngmc") && !inwpnotifier_active_tab.hasClass("customfieldsNavTab")) {
                $magicLine.hide().css({'top': 45});
            }
        }

        jQuery(".inpwpnotifier_admin_conf .updatetabview").on('click', function () {


            var c = jQuery(this).attr('tab');

            var acr = jQuery(this).attr('acr');

            var refresh = jQuery(this).attr('refresh');

            if (typeof refresh !== typeof undefined && refresh !== false) {
                location.reload();
                return true;
            }

            if (typeof acr !== typeof undefined && acr !== false) {
                var inv = 0;

            }

            var tab = jQuery("." + c);

            if (tab.hasClass('digcurrentactive')) return false;

            var $this = jQuery(this);

            if (tab.data('attach')) {
                $this = jQuery('.' + tab.data('attach'));
            }

            if (!$this.hasClass("inwpnotifier_ngmc")) {
                $magicLine.show();
                $el = $this.parent();
                leftPos = $el.position().left;
                newWidth = $el.width();
                $magicLine.stop().animate({
                    left: leftPos,
                    width: newWidth,
                    top: $el.position().top + 21
                }, 'fast');
            } else {
                $magicLine.hide();
            }

            jQuery(".digcurrentactive").removeClass("digcurrentactive").hide();

            tab.fadeIn(150).addClass("digcurrentactive");


            if (jQuery(".inwpnotifier-tab-wrapper-fixed").length)
                jQuery('in.html, body').animate({scrollTop: tab.offset().top - 90}, 220);


            jQuery(".inwpnotifier-nav-tab-active").removeClass("inwpnotifier-nav-tab-active");
            jQuery(this).addClass("inwpnotifier-nav-tab-active");


            updateURL("tab", c.slice(0, -3));

            return false;
        });

        function updateURL(key, val) {
            var url = window.location.href;
            var reExp = new RegExp("[\?|\&]" + key + "=[0-9a-zA-Z\_\+\-\|\.\,\;]*");

            if (reExp.test(url)) {
                // update
                var reExp = new RegExp("[\?&]" + key + "=([^&#]*)");
                var delimiter = reExp.exec(url)[0].charAt(0);
                url = url.replace(reExp, delimiter + key + "=" + val);
            } else {
                // add
                var newParam = key + "=" + val;
                if (!url.indexOf('?')) {
                    url += '?';
                }

                if (url.indexOf('#') > -1) {
                    var urlparts = url.split('#');
                    url = urlparts[0] + "&" + newParam + (urlparts[1] ? "#" + urlparts[1] : '');
                } else {
                    url += "&" + newParam;
                }
            }
            window.history.pushState(null, document.title, url);
        }

        $whatsapp_toggle_value = $("#inwpnotifier_whatsapp_toggle").val();
        if ($whatsapp_toggle_value != 1) {
            $(".inwpnotifier_TO_whatsapp_container,.inwpnotifier_TO_whatsapp_gateway").css("display", "none");
        }

        $("#inwpnotifier_whatsapp_toggle").on("click", function () {

            $(".inwpnotifier_TO_whatsapp_container,.inwpnotifier_TO_whatsapp_gateway").fadeToggle().css({
                "background-color": "#FAFDFC",
                "height": "230px",
                "margin-top": '2px'
            });
        });

        $("#inwpnotifier_frontend_form_box").on("click", function () {
            $(".inwpnotifier_frontend_form_shows,.inwpnotifier_frontend_form_show").fadeToggle().css({
                "background-color": "#FAFDFC",
                "height": "230px",
                "margin-top": '2px'
            });
        });
        $("#inwpnotifier_message_settings_box").on("click", function () {
            $(".inwpnotifier_message_setting_shows,.inwpnotifier_message_setting_show").fadeToggle().css({
                "background-color": "#FAFDFC",
                "height": "230px",
                "margin-top": '2px'
            });
        });

        $('#inwpnotifier_hide_sub_non_log').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_hide_subscribe_loggedin').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_show_subscribe_on_backorder').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_hide_subscribe_regular_product').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_hide_subscribe_sale_product').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_ignore_disabled_variation').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_ignore_wc_visibility').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_enable_success_subscription').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });
        $('#inwpnotifier_enable_instock_sms').click(function () {
            if ($(this).val() == 1)
                $(this).val(0);
            else if ($(this).val() == 0)
                $(this).val(1);
        });

    });
    jQuery("#inwpnotifier_loading_container").fadeOut('fast');
})(jQuery);

