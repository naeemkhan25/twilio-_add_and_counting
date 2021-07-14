<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * seller on And OFF notification setting.
 */
if(!class_exists('Inwpnotifier_api_sms_ajax_save')){
    class Inwpnotifier_api_sms_ajax_save{
        public function __construct(){
            add_action("wp_ajax_inwpnotifier_api_whatsapp_on_of",array($this,"inwpnotifier_api_whatsapp_on_of"));
            add_action("wp_ajax_nopriv_inwpnotifier_api_whatsapp_on_of",array($this,"inwpnotifier_api_whatsapp_on_of"));
        }
        public function inwpnotifier_api_whatsapp_on_of(){
            $inwpnotifer_api_whatsapp=$_POST;
            $check_is_security = isset( $inwpnotifer_api_whatsapp['security'] ) && $inwpnotifer_api_whatsapp['security'] != '' ? 'yes' : 'no';
            if ( $check_is_security == 'no' ) {
                wp_die( - 1, 403 );
            }
                update_option("inwpnotifier_whatsapp_toggle",intval($inwpnotifer_api_whatsapp['inwpnotifier_whatsapp_value']));

            die;
        }

    }
    new Inwpnotifier_api_sms_ajax_save();
}