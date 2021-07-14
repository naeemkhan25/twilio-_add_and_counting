<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'InStock_WPNotifier_Ajax' ) ) {

	class InStock_WPNotifier_Ajax {

		public function __construct() {
			add_action( 'wp_ajax_inwpnotifier_product_subscribe', array(
				$this,
				'InStock_WPNotifier_ajax_subscription'
			) );
			add_action( 'wp_ajax_nopriv_inwpnotifier_product_subscribe', array(
				$this,
				'InStock_WPNotifier_ajax_subscription'
			) );

			add_action( 'inwpnotifier_ajax_data', array( $this, 'InStock_WPNotifier_success_message' ) );
			add_action( 'inwpnotifier_after_insert_subscriber', array(
				$this,
				'InStock_WPNotifier_perform_action_after_insertion'
			), 10, 2 );
			add_action( 'wp_ajax_wp_woocommerce_json_search_tags', array(
				$this,
				'InStock_WPNotifier_json_search_tags'
			) );
		}


		public function InStock_WPNotifier_ajax_subscription() {
			if ( isset( $_POST ) ) {


				$obj               = new InStock_WPNotifier_API();
				$post_data         = $obj->InStock_WPNotifier_post_data_validation( $_POST );
				$product_id        = $post_data['product_id'];



				$check_is_security = isset( $post_data['security'] ) && $post_data['security'] != '' ? 'yes' : 'no';
				if ( $check_is_security == 'no' ) {
					//block ajax request as it may be a bot
					wp_die( - 1, 403 );
				}
				//for success
				do_action( 'inwpnotifier_ajax_data', $post_data );
				$get_messages=get_option("inwpnotifier_success_subscription_message");
				$success_msg = __( 'You have successfully subscribed, we will inform you when this product back in stock', 'inwpnotifier' );
				$success     = isset( $get_messages ) && $get_messages ? $get_messages : $success_msg;
				echo "<div class='inwpnotifiersuccess' style='color:green;'>$success</div>";
			}
			die();
		}

		public function InStock_WPNotifier_success_message( $post_data ) {
			$get_phone         = $post_data['user_phone'];

                $str_trim_phone = trim($get_phone);
			$get_user_id  = $post_data['user_id'];
			$product_id   = $post_data['product_id'];
			$variation_id = $post_data['variation_id'];
            $author_object=get_post($product_id);
            $author_id=$author_object->post_author;

			$obj = new InStock_WPNotifier_API( $product_id, $variation_id, $str_trim_phone, $get_user_id,$author_id);

			$check_is_already_subscribed = $obj->InStock_WPNotifier_is_already_subscribed();
			$subscriber_count            = get_transient( "subscriber_count" ) ? get_transient( "subscriber_count" ) : 0;
			if ( ! $check_is_already_subscribed ) {
				$id = $obj->InStock_WPNotifier_insert_subscriber();
				$subscriber_count ++;
				set_transient( 'subscriber_count', $subscriber_count, 0 );

				if ( $id ) {
					$obj->InStock_WPNotifier_insert_data( $id );
					$get_count = $obj->InStock_WPNotifier_get_subscribers_count( $product_id, 'iwg_subscribed' );
					update_post_meta( $product_id, 'inwpnotifier_total_subscribers', $get_count );
                    $get_popular_product=get_option("inwpnotifier_popular_product_$product_id")?get_option("inwpnotifier_popular_product_$product_id"):0;
                    $get_popular_product++;
                    update_option( "inwpnotifier_popular_product_$product_id", $get_popular_product );
					do_action( 'inwpnotifier_after_insert_subscriber', $id, $post_data );
					//logger
					$logger = new InStock_WPNotifier_Logger( 'success', "Subscriber #$get_phone successfully subscribed - #$id" );
					$logger->InStock_WPNotifier_record_log();
				}
			} else {

				$get_already_message=get_option("inwpnotifier_already_subscribed_message");
				$already_sub_msg = __( 'Seems like you have already subscribed to this product', 'inwpnotifier' );
				$error           = isset( $get_already_message ) && $get_already_message ? $get_already_message : $already_sub_msg;
				echo "<div class='inwpnotifirerror' style='color:coral;'>$error</div>";
				die();
			}
		}

		public function InStock_WPNotifier_perform_action_after_insertion( $id, $post_data ) {
			$get_subscribe_enabled=get_option("inwpnotifier_enable_success_subscription");
			$is_enabled = isset( $get_subscribe_enabled ) ? $get_subscribe_enabled : 0;
			$get_phone  = $post_data['user_phone'];
			if ( $is_enabled == '1' || $is_enabled == 1 ) {

                    $whtsapp_sms = new InStock_WPNotifier_Subscribe_SMS($id);
                    $whtsapp_sms->send_whatsapp_sms();


				$logger = new InStock_WPNotifier_Logger( 'success', "SMS sent to #$get_phone for successful subscription - #$id" );
				$logger->InStock_WPNotifier_record_log();
			}
		}

		public static function InStock_WPNotifier_json_search_tags() {
			ob_start();

			check_ajax_referer( 'search-tags', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				wp_die( - 1 );
			}

			$search_text = isset( $_GET['term'] ) ? wc_clean( wp_unslash( $_GET['term'] ) ) : '';

			if ( ! $search_text ) {
				wp_die();
			}

			$found_tags = array();
			$args       = array(
				'taxonomy'   => array( 'product_tag' ),
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => true,
				'fields'     => 'all',
				'name__like' => $search_text,
			);

			$terms = get_terms( $args );

			if ( $terms ) {
				foreach ( $terms as $term ) {
					$term->formatted_name         = '';
					$term->formatted_name         .= $term->name . ' (' . $term->count . ')';
					$found_tags[ $term->term_id ] = $term;
				}
			}
			wp_send_json( apply_filters( 'woocommerce_json_search_found_tags', $found_tags ) );
		}

	}

	new InStock_WPNotifier_Ajax();
}