<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('InStock_WPNotifier_Instock_Subscribe_SMS')) {

    class InStock_WPNotifier_Instock_Subscribe_SMS {

        public function __construct($subscriber_id) {
            $this->subscriber_id = $subscriber_id;
            $this->whatsapp_number = get_post_meta($subscriber_id, 'inwpnotifier_subscriber_phone', true);
            do_action('inwpnotifier_before_instock_sms', $this->whatsapp_number, $this->subscriber_id);

            $get_instock_subject=get_option("inwpnotifir_instock_sub_subject");
            $get_instock_message=get_option("inwpnotifier_instock_sub_message");
            $this->get_wp_subject = apply_filters('iwginstock_raw_subject', $get_instock_subject, $subscriber_id);
            $this->get_wp_message = apply_filters('iwginstock_raw_message', nl2br($get_instock_message), $subscriber_id);
        }

        public function from_name() {
            $from_name = get_bloginfo('name');
            return apply_filters('iwginstock_from_name',$from_name);
        }

        public function InStock_WPNotifier_format_data($message) {
            $replace = html_entity_decode($message);
            return $replace;
        }

        public function InStock_WPNotifier_get_subject() {
            return apply_filters('iwginstock_subject', $this->InStock_WPNotifier_format_data(do_shortcode($this->InStock_WPNotifier_replace_shortcode($this->get_wp_subject))), $this->subscriber_id);
        }

        public function InStock_WPNotifier_get_message() {
            return apply_filters('iwginstock_message', do_shortcode($this->InStock_WPNotifier_replace_shortcode($this->get_wp_message)), $this->subscriber_id);
        }

        private function InStock_WPNotifier_replace_shortcode($content) {
            $obj = new InStock_WPNotifier_API();
            $pid = get_post_meta($this->subscriber_id, 'inwpnotifier_pid', true);
            $product_name = $obj->InStock_WPNotifier_display_product_name($this->subscriber_id);
            $only_product_name = $obj->InStock_WPNotifier_display_only_product_name($this->subscriber_id);
            $product_link = $obj->InStock_WPNotifier_display_product_link($this->subscriber_id);
            $only_product_sku = $obj->InStock_WPNotifier_get_product_sku($this->subscriber_id);
            $product_image = $obj->InStock_WPNotifier_get_product_image($this->subscriber_id);
            $cart_url = esc_url_raw(add_query_arg('add-to-cart', $pid, get_permalink(wc_get_page_id('cart'))));
            $blogname = get_bloginfo('name');
            $find_array = array('{product_name}', '{product_id}', '{product_link}', '{shopname}', '{whatsapp_number}', '{subscriber_number}', '{cart_link}', '{only_product_name}', '{only_product_sku}', '{product_image}');
            $replace_array = array(strip_tags($product_name), $pid, $product_link, $blogname, $this->whatsapp_number, $this->whatsapp_number, $cart_url, $only_product_name, $only_product_sku,$product_image);
            $formatted_content = str_replace($find_array, $replace_array, $content);
            return apply_filters('iwginstock_InStock_WPNotifier_replace_shortcode', $formatted_content, $this->subscriber_id);
        }
        public function send_whatsapp_sms() {
            $w_Number = $this->whatsapp_number;
//            $whatsappNumber = str_replace('+', '', $w_Number);
            $subject_sms=$this->InStock_WPNotifier_get_subject();
            $get_message_sms=$this->InStock_WPNotifier_get_message();

            $message=$subject_sms .' '.$get_message_sms;
            $sid=get_option("inwpnotfier_twilio_SID");
            $token=get_option("inwpnotifier_twilio_token");
            $form=get_option("inwpnotifier_twilio_senderNumber");
            $curl_urls="https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
            $data=array(
                'To'=>"whatsapp:$w_Number",
                'From'=>"whatsapp:$form",
                'Body'=>$message,
            );
            $post=http_build_query($data);
            $x_curl= curl_init ( $curl_urls );
            curl_setopt($x_curl,CURLOPT_POST,true);
            curl_setopt($x_curl,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($x_curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($x_curl,CURLOPT_USERPWD,"$sid:$token");
            curl_setopt($x_curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
            curl_setopt($x_curl,CURLOPT_POSTFIELDS, $post);
            $y_curl=curl_exec($x_curl);

            $z_curl=json_decode($y_curl);
            curl_close($x_curl);
            do_action('iwg_instock_after_instock_SMS', $w_Number, $this->subscriber_id);
            if(is_object($z_curl)) {
                $f_curl = $z_curl->status;

                if ($f_curl == 'queued') {
                    do_action('iwg_instock_sms_sent_success', $this->subscriber_id);
                    return true;
                } else {
                    do_action('iwg_instock_sms_sent_failure', $this->subscriber_id);
                    return false;
                }

            }else{
                do_action('iwg_instock_sms_sent_failure', $this->subscriber_id);
                return false;
            }
        }

    }

}
