<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * seller on And OFF notification setting.
 */
if(!class_exists('Inwpnotifier_dokan_ajax_save')){
    class Inwpnotifier_dokan_ajax_save{
        public function __construct(){
            add_action("wp_ajax_inwpnotifier_vendor_notifier_on_off",array($this,"inwpnotifier_vendor_shop_notfier_on_off"));
            add_action("wp_ajax_nopriv_inwpnotifier_vendor_notifier_on_off",array($this,"inwpnotifier_vender_shop_notfier_on_off"));
        }
        public function inwpnotifier_vendor_shop_notfier_on_off(){
            $shope_notification_data=$_POST;
            $check_is_security = isset( $shope_notification_data['security'] ) && $shope_notification_data['security'] != '' ? 'yes' : 'no';
            if ( $check_is_security == 'no' ) {
                wp_die( - 1, 403 );
            }
            $current_user_id=intval(sanitize_text_field($shope_notification_data['current_user_id']));
            $shop_notifier_on_off=intval(sanitize_text_field($shope_notification_data['notifier_value']));
          $inwp_option_name="inwpnotifier_dokan_notifier_on_off_$current_user_id";
          update_option($inwp_option_name,$shop_notifier_on_off);
          die;

        }

    }
    new Inwpnotifier_dokan_ajax_save();
}