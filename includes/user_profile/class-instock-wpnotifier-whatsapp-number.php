<?php

if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists("InStock_WPNotifier_Whatsapp_number")){

    class InStock_WPNotifier_Whatsapp_number {
        public function __construct()
        {
            add_action( 'show_user_profile', array($this,'InStock_WPNotifie_extra_profile_fields'), 10 );
            add_action( 'edit_user_profile', array($this,'InStock_WPNotifie_extra_profile_fields'), 10 );
            add_action( 'personal_options_update', array($this,'InStock_WPNotifie_save_extra_profile_fields') );
            add_action( 'edit_user_profile_update', array($this,'InStock_WPNotifie_save_extra_profile_fields') );
        }

        public function InStock_WPNotifie_extra_profile_fields($user){

            ?>

            <h3><?php _e('Whatsapp Number ( Instock WPNotif )',"inwpnotifier"); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label for="whatsapp_number"><?php _e('Whatsapp Number ',"inwpnotifier");?></label></th>
                    <td>
                        <input type="hidden" name="country_code" id="country_code" class="inwnotifi_country_code">
                        <input style="border-top-style: hidden;
                                        border-right-style:hidden;
                                        border-left-style: hidden;
                                        border-butt-style: hidden;
                                        outline: none !important;
                                        "type="tel"  id="inwpnotifier_phone" name="inwpnotifier_phone" class="inwpstock_whatsapp_sms" value="<?php echo esc_attr( get_the_author_meta( 'inwpnotifier_whatsapp_number', $user->ID ) ); ?>">
                        <span id="valid-msg" class="hide">✓ Valid</span>
                        <span style="size:10px" id="error-msg" class="hide"></span>

                    </td>
                </tr>
            </table>
            <?php
        }
        public function InStock_WPNotifie_save_extra_profile_fields($user_id)
        {
            if ( !current_user_can( 'read', $user_id ) )
                return false;
            $_code=isset($_POST['country_code'])?sanitize_text_field($_POST['country_code']):"+1";

            $phone_code=isset($_POST['inwpnotifier_phone'])?sanitize_text_field($_POST['inwpnotifier_phone']):'';
            $phone=$_code.$phone_code;
            $phone_number=trim($phone,' ');
            $phone_number_length=strlen($phone_number);
          if($phone_number_length>4){
            /* Edit the following lines according to your set fields */
            update_user_meta( $user_id, 'inwpnotifier_whatsapp_number',$phone_number);
           }

    }
    }
    new InStock_WPNotifier_Whatsapp_number();
}

