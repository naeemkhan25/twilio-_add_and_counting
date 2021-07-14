<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('InStock_WPNotifier_API')) {

    class InStock_WPNotifier_API
    {

        public function __construct($product_id = 0, $variation_id = 0, $user_phone = '', $user_id = 0,$author_id=0,$language = 'en_US')
        {

            $this->product_id = $product_id;
            $this->variation_id = $variation_id;
            $this->subscriber_phone = $user_phone;
            $this->user_id = $user_id;
            $this->author_id=$author_id;
            $this->language = $language;
        }

        public function InStock_WPNotifier_post_data_validation($post) {
            $post_data = array();
            if (is_array($post) && !empty($post)) {
                foreach ($post as $key => $value) {
                    if (is_array($value) && !empty($value)) {
                        foreach ($value as $newkey => $newvalue) {
                            $post_data[$key][$newkey] = $this->InStock_WPNotifier_format_field($newkey, $newvalue);
                        }
                    } else {
                        $post_data[$key] = $this->InStock_WPNotifier_format_field($key, $value);
                    }
                }
            }
            return $post_data;
        }

        public function InStock_WPNotifier_insert_data($id) {
            $default_data = array(
                'inwpnotifier_product_id' => $this->product_id,
                'inwpnotifier_variation_id' => $this->variation_id,
                'inwpnotifier_subscriber_phone' => $this->subscriber_phone,
                'inwpnotifier_product_upload_author'=>$this->author_id,
                'inwpnotifier_user_id' => $this->user_id,
                'inwpnotifier_language' => $this->language,
                'inwpnotifier_pid' => $this->variation_id > '0' || $this->variation_id > 0 ? $this->variation_id : $this->product_id,
            );
            foreach ($default_data as $key => $value) {
                update_post_meta($id, $key, $value);
            }
        }
        public function InStock_WPNotifier_is_already_subscribed() {
            $args = array(
                'post_type' => 'inwpnotifier',
                'fields' => 'ids',
                'posts_per_page' => -1,
                'post_status' => 'iwg_subscribed',
            );
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'inwpnotifier_pid',
                    'value' => $this->variation_id > '0' || $this->variation_id > 0 ? $this->variation_id : $this->product_id,
                ),
                array(
                    'key' => 'inwpnotifier_subscriber_phone',
                    'value' => $this->subscriber_phone,
                ),
            );
            $args['meta_query'] = $meta_query;
            $get_posts = get_posts($args);
            return $get_posts;
        }

        public function InStock_WPNotifier_insert_subscriber() {

            $args = array(
                'post_title' => $this->subscriber_phone,
                'post_type' => 'inwpnotifier',
                'post_status' => 'iwg_subscribed',

            );

            $id = wp_insert_post($args);
            if (!is_wp_error($id)) {
                return $id;
            } else {
                return false;
            }
        }

        public function InStock_WPNotifier_get_subscribers_count($product_id, $status = 'any') {
            $args = array(
                'post_type' => 'inwpnotifier',
                'post_status' => $status,
                'meta_query' => array(
                    array(
                        'key' => 'inwpnotifier_product_id',
                        'value' => array($product_id),
                        'compare' => 'IN',
                    )),
                'numberposts' => -1,
            );
            $query = get_posts($args);
            return count($query);
        }


        public function InStock_WPNotifier_format_field($key, $value) {
            $list_of_fields = array(
                'product_id' => intval(sanitize_text_field($value)),
                'variation_id' => intval(sanitize_text_field($value)),
                'user_id' => intval(sanitize_text_field($value)),
                'user_phone' => $this->sanitize_text_field($value),
            );
            if (isset($list_of_fields[$key])) {
                return $list_of_fields[$key];
            } else {
                return sanitize_text_field($value);
            }
        }
        public function InStock_WPNotifier_display_product_name($id) {
            $variation_id = get_post_meta($id, 'inwpnotifier_variation_id', true);
            $product_id = get_post_meta($id, 'inwpnotifier_product_id', true);
            if ($product_id) {
                $val = intval($variation_id);
                if ($val > 0) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        $formatted_name = $variation->get_formatted_name();
                        return $formatted_name;
                    }
                } else {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        return $product->get_formatted_name();
                    }
                }
                return false;
            }
        }

        public function InStock_WPNotifier_display_product_link($id) {
            $variation_id = get_post_meta($id, 'inwpnotifier_variation_id', true);
            $product_id = get_post_meta($id, 'inwpnotifier_product_id', true);
            if ($product_id) {
                $val = intval($variation_id);
                if ($val > 0) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        $link = $variation->get_permalink();
                        return $link;
                    }
                } else {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        return $product->get_permalink();
                    }
                }
            }
            return '';
        }

        public function InStock_WPNotifier_display_only_product_name($id) {
            $variation_id = get_post_meta($id, 'inwpnotifier_variation_id', true);
            $product_id = get_post_meta($id, 'inwpnotifier_product_id', true);
            if ($product_id) {
                $val = intval($variation_id);
                if ($val > 0) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        $formatted_name = $variation->get_name();
                        return $formatted_name;
                    }
                } else {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        return $product->get_name();
                    }
                }
                return false;
            }
        }
        public function InStock_WPNotifier_get_product_sku($id) {
            $variation_id = get_post_meta($id, 'inwpnotifier_variation_id', true);
            $product_id = get_post_meta($id, 'inwpnotifier_product_id', true);
            if ($product_id) {
                $val = intval($variation_id);
                if ($val > 0) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        $formatted_name = $variation->get_sku();
                        return $formatted_name;
                    }
                } else {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        return $product->get_sku();
                    }
                }
                return false;
            }
        }

        public function InStock_WPNotifier_get_product_image($id, $size = 'woocommerce_thumbnail') {
            $variation_id = get_post_meta($id, 'inwpnotifier_variation_id', true);
            $product_id = get_post_meta($id, 'inwpnotifier_product_id', true);
            if ($product_id) {
                $val = intval($variation_id);
                if ($val > 0) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        return $variation->get_image($size);
                    }
                } else {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        return $product->get_image($size);
                    }
                }
                return false;
            }
        }
        public function InStock_WPNotifier_sms_sent_status($subscribe_id) {
            $args = array(
                'ID' => $subscribe_id,
                'post_type' => 'inwpnotifier',
                'post_status' => 'iwg_smssent',
            );
            $id = wp_update_post($args);
            return $id;
        }
        public function InStock_WPNotifier_sms_not_sent_status($subscribe_id) {
            $args = array(
                'ID' => $subscribe_id,
                'post_type' => 'inwpnotifier',
                'post_status' => 'iwg_smsnotsent',
            );
            $id = wp_update_post($args);
            return $id;
        }
        public function InStock_WPNotifier_subscriber_subscribed($subscribe_id) {
            $args = array(
                'ID' => $subscribe_id,
                'post_type' => 'inwpnotifier',
                'post_status' => 'iwg_subscribed',
            );
            $id = wp_update_post($args);
            return $id;
        }
        public function InStock_WPNotifier_subscriber_unsubscribed($subscribe_id) {
            $args = array(
                'ID' => $subscribe_id,
                'post_type' => 'inwpnotifier',
                'post_status' => 'iwg_unsubscribed',
            );
            $id = wp_update_post($args);
            return $id;
        }
        public function InStock_WPNotifier_get_list_of_subscribers() {
            $args = array(
                'post_type' => 'inwpnotifier',
                'fields' => 'ids',
                'posts_per_page' => -1,
                'post_status' => 'iwg_subscribed',
            );
            $meta_query = array(
                    'realtion'=>'AND',
                array(
                'relation' => 'OR',
                array(
                    'key' => 'inwpnotifier_product_id',
                    'value' => ($this->product_id > '0' || $this->product_id) ? $this->product_id : 'no_data_found',
                ),
                array(
                    'key' => 'inwpnotifier_variation_id',
                    'value' => ($this->variation_id > '0' || $this->variation_id > 0) ? $this->variation_id : 'no_data_found',
                ),
                ),

            );

            $args['meta_query'] = apply_filters("inwpnotifier_instock_metaquery", $meta_query);
            $get_posts = get_posts($args);

            return $get_posts;
        }

        public function sanitize_text_field($value) {
            return sanitize_text_field($value);
        }

        public function sanitize_textarea_field($value) {
            $value = wp_kses($value, array(
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                    'target' => array(),
                ),
                'br' => array(),
                'em' => array(),
                'strong' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'h1' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'h2' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'h3' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'h4' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'h5' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'h6' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                ),
                'img' => array(
                    'class' => array(),
                    'id' => array(),
                    'style' => array(),
                    'src' => array(),
                    'alt' => array(),
                    'height' => array(),
                    'width' => array(),
                ),
                'label' => array(
                    'for' => array(),
                ),
                'ul' => array(
                    'id' => array(),
                    'class' => array(),
                    'style' => array(),
                ),
                'li' => array(
                    'id' => array(),
                    'class' => array(),
                    'style' => array(),
                ),
                'ol' => array(
                    'id' => array(),
                    'class' => array(),
                    'style' => array(),
                ),
                'p' => array(
                    'id' => array(),
                    'class' => array(),
                    'style' => array(),
                ),
                'b' => array(
                    'id' => array(),
                    'class' => array(),
                    'style' => array(),
                ),
                'table' => array(
                    'align' => array(),
                    'bgcolor' => array(),
                    'border' => array(),
                    'cellpadding' => array(),
                    'cellspacing' => array(),
                    'class' => array(),
                    'dir' => array(),
                    'frame' => array(),
                    'id' => array(),
                    'rules' => array(),
                    'style' => array(),
                    'width' => array(),
                ),
                'td' => array(
                    'abbr' => array(),
                    'align' => array(),
                    'bgcolor' => array(),
                    'class' => array(),
                    'colspan' => array(),
                    'dir' => array(),
                    'height' => array(),
                    'id' => array(),
                    'lang' => array(),
                    'rowspan' => array(),
                    'scope' => array(),
                    'style' => array(),
                    'valign' => array(),
                    'width' => array(),
                ),
                'th' => array(
                    'abbr' => array(),
                    'align' => array(),
                    'background' => array(),
                    'bgcolor' => array(),
                    'class' => array(),
                    'colspan' => array(),
                    'dir' => array(),
                    'height' => array(),
                    'id' => array(),
                    'lang' => array(),
                    'scope' => array(),
                    'style' => array(),
                    'valign' => array(),
                    'width' => array(),
                ),
                'tr' => array(
                    'align' => array(),
                    'bgcolor' => array(),
                    'class' => array(),
                    'dir' => array(),
                    'id' => array(),
                    'style' => array(),
                    'valign' => array(),
                ),
                'div' => array(
                    'id' => array(),
                    'class' => array(),
                    'style' => array(),
                ),
            ));
            return $value;
        }
        public function inwpnotifier_delete_subscribe($id){
            global $wpdb;
            $table_name=$wpdb->prefix. 'posts';
           return $delete=$wpdb->delete( $table_name, [ 'ID' => sanitize_key($id) ], [ '%d' ] );

        }
        public function inwpnotifier_manual_whatsapp_sms($post_id,$dokan=0){
            $get_number     = get_post_meta( $post_id, 'inwpnotifier_subscriber_phone', true );

            $whatsapp       = new InStock_WPNotifier_Instock_Subscribe_SMS( $post_id );
            $pid            = get_post_meta( $post_id, 'inwpnotifier_pid', true );

            $product_exists = wc_get_product( $pid );
            if ( $product_exists ) {
                $send_SMS = $whatsapp->send_whatsapp_sms();

                if ( $send_SMS ) {
                    $message    = __( "Instock SMS sent to {whatsapp_number} successfully", 'inwpnotifier' );
                    $replace    = str_replace( '{whatsapp_number}', $get_number, $message );
                    $SMS_status = $this->InStock_WPNotifier_sms_sent_status( $post_id );
                    $logger     = new InStock_WPNotifier_Logger( 'success', "Manual Instock SMS sent to #$get_number - #$post_id" );
                    $logger->InStock_WPNotifier_record_log();
                    instock_WPNotfier_add_persistent_notice( array(
                        'type'    => 'success',
                        'message' => $replace,
                    ) );
                } else {
                    $error_msg     = __( 'Unable to send Instock SMS to this {whatsapp_number}', 'inwpnotifier' );
                    $error_replace = str_replace( '{whatsapp_number}', $get_number, $error_msg );
                    $SMS_status    = $this->InStock_WPNotifier_sms_not_sent_status( $post_id );
                    $logger        = new InStock_WPNotifier_Logger( 'error', "$error_replace" . " #$post_id" );
                    $logger->InStock_WPNotifier_record_log();
                    instock_WPNotfier_add_persistent_notice( array(
                        'type'    => 'error',
                        'message' => $error_replace,
                    ) );
                }
            } else {
                $error_msg     = __( 'Unable to send Instock SMS to this {whatsapp_number} as stock product does not exists/deleted !!!', 'inwpnotifier' );
                $error_replace = str_replace( '{whatsapp_number}', $get_number, $error_msg );
                $logger        = new InStock_WPNotifier_Logger( 'error', "$error_replace" . " #$post_id" );
                $logger->InStock_WPNotifier_record_log();
                instock_WPNotfier_add_persistent_notice( array(
                    'type'    => 'error',
                    'message' => $error_replace,
                ) );
            }
            if($dokan==1|| $dokan=='1') {
                wp_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
        }

        public function inwpnotifier_display_status($get_post_status){
            switch ( $get_post_status ) {
                case 'iwg_subscribed':
                    $subscribed = __( 'Subscribed', 'inwpnotifier' );
                    echo "<mark class='iwgmark iwgsubscribed'>$subscribed</mark>";
                    break;
                case 'iwg_smssent':
                    $smssent = __( 'SMS Sent', 'inwpnotifier' );
                    echo "<mark class='iwgmark iwgsmssent'>$smssent</mark>";
                    break;
                case 'iwg_unsubscribed':
                    $unsubscribed = __( 'Unsubscribed', 'inwpnotifier' );
                    echo "<mark class='iwgmark iwgunsubscribed'>$unsubscribed</mark>";
                    break;
                case 'iwg_converted':
                    $converted = __( 'Purchased', 'inwpnotifier' );
                    echo "<mark class='iwgmark iwgpurchased'>$converted</mark>";
                    break;
                case 'iwg_smsnotsent':
                    $notsent = __( 'Failed', 'inwpnotifier' );
                    echo "<mark class='iwgmark iwgfailed'>$notsent</mark>";
                    break;
                default:
                    $otherstatus = $get_post_status;
                    echo "<mark class='iwgmark'>$otherstatus</mark>";
                    break;
            }
        }
    }
}