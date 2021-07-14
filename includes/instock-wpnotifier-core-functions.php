<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
if ( ! class_exists( "InStock_WPNotifier_Core" ) ) {
	class InStock_WPNotifier_Core {
		public function __construct() {
			add_action( 'woocommerce_product_set_stock_status', array(
				$this,
				'InStock_WPNotifier_action_based_on_stock_status'
			), 999, 3 );
			add_action( 'woocommerce_variation_set_stock_status', array(
				$this,
				'InStock_WPNotifier_action_based_on_stock_status'
			), 999, 3 );
			add_action( "inwpnotifier_trigger_status", array(
				$this,
				"InStock_WPNotifier_trigger_instock_status"
			), 999, 3 );
			add_action( 'iwg_instock_sms_sent_success', array(
				$this,
				'InStock_WPNotifier_recount_subscribers_upon_instock'
			), 99 );
			add_action( 'iwg_instock_bulk_status_action', array(
				$this,
				'InStock_WPNotifier_recount_subscribers_upon_bulk_action'
			), 99, 2 );
			add_filter( 'inwpnotifier_trigger_status_product', array(
				$this,
				'InStock_WPNotifier_get_product_bundle_subscribers'
			), 10, 2 );
			add_filter( 'inwpnotifier_trigger_status_variation', array(
				$this,
				'InStock_WPNotifier_get_product_bundle_subscribers'
			), 10, 2 );
            add_filter( 'inwpnotifier_trigger_status_phone_product', array(
                $this,
                'InStock_WPNotifier_get_phone_product_bundle_subscribers'
            ), 10, 2 );
            add_filter( 'inwpnotifier_trigger_status_phone_variation', array(
                $this,
                'InStock_WPNotifier_get_phone_product_bundle_subscribers'
            ), 10, 2 );


		}

		public function InStock_WPNotifier_action_based_on_stock_status( $id, $stockstatus, $obj = '' ) {
			if ( $stockstatus == 'instock' ) {
				do_action( 'inwpnotifier_trigger_status', $id, $stockstatus, $obj );
			}
		}

		public function InStock_WPNotifier_trigger_instock_status( $id, $stockstatus, $obj ) {
			if ( ! $obj ) {
				$obj = wc_get_product( $id );
			}



			if ( $obj->is_type( 'variation' ) || $obj->is_type( 'variable' ) ) {
				$main_obj  = $obj->is_type( 'variable' ) ? new InStock_WPNotifier_API( $id, 0 ) : new InStock_WPNotifier_API( 0, $id, '', 0 );

                    $get_posts = apply_filters('inwpnotifier_trigger_status_variation', $main_obj->InStock_WPNotifier_get_list_of_subscribers(), $id);

				$this->inwpnotifier_background_process_core( $get_posts, true, $id );
			} else {

                $main_obj  = new InStock_WPNotifier_API( $id, 0 );
                    $get_posts = apply_filters('inwpnotifier_trigger_status_product', $main_obj->InStock_WPNotifier_get_list_of_subscribers(), $id);
				$this->inwpnotifier_background_process_core( $get_posts, false, $id );
			}
		}

		private function inwpnotifier_background_process_core( $get_posts, $is_variation, $id ) {
			foreach ( $get_posts as $post_id ) {
				$this->InStock_WPNotifier_task( $post_id );
			}

		}

		protected function InStock_WPNotifier_task( $each_id ) {
			$get_post_status = get_post_status( $each_id );
			if ( $get_post_status == 'iwg_subscribed' ) {
				$get_enable_instock=get_option("inwpnotifier_enable_instock_sms");
				$is_enabled =$get_enable_instock;
				if ( $is_enabled == '1' || $is_enabled == 1 ) {
				    $ge_phone=get_post_meta($each_id,"inwpnotifier_subscriber_phone",true);
                        $Send_sm = new InStock_WPNotifier_Instock_Subscribe_SMS($each_id);
                        $send_sms = $Send_sm->send_whatsapp_sms(); // sms sent
					if ( $send_sms ) {
						$api        = new InStock_WPNotifier_API();
						$sms_status = $api->InStock_WPNotifier_sms_sent_status( $each_id ); // update sms sent status
						$logger     = new InStock_WPNotifier_Logger( 'info', "Automatic Instock SMS Triggered for ID #$each_id with #$ge_phone" );
						$logger->InStock_WPNotifier_record_log();
					} else {
						$api        = new InStock_WPNotifier_API();
						$sms_status = $api->InStock_WPNotifier_sms_not_sent_status( $each_id );
						$logger     = new InStock_WPNotifier_Logger( 'error', "Failed to send Automatic Instock sms for ID #$each_id with #$ge_phone" );
						$logger->InStock_WPNotifier_record_log();
					}
				}
			}

			return false;
		}

		public function InStock_WPNotifier_recount_subscribers_upon_instock( $subscriber_id ) {
			$obj            = new InStock_WPNotifier_API();
			$get_product_id = get_post_meta( $subscriber_id, 'inwpnotifier_product_id', true );
			if ( $get_product_id ) {
				$get_count = $obj->InStock_WPNotifier_get_subscribers_count( $get_product_id, 'iwg_subscribed' );
				update_post_meta( $get_product_id, 'inwpnotifier_total_subscribers', $get_count );
			}
		}

		public function InStock_WPNotifier_recount_subscribers_upon_bulk_action( $subscriber_id, $status ) {
			$this->InStock_WPNotifier_recount_subscribers_upon_instock( $subscriber_id );
		}

		public function InStock_WPNotifier_get_product_bundle_subscribers( $subscribers, $product_id ) {
			$product = wc_get_product( $product_id ); //if this

			if ( ! $product->is_type( 'bundle' ) ) { // this product type to be excluded because the below code applicable on other product types
				//now check this product exists in bundle product or not
				if ( function_exists( 'wc_pb_is_bundled_cart_item' ) ) {
					$product_ids = array( $product_id );
					$results     = WC_PB_DB::query_bundled_items( array(
						'return'     => 'id=>bundle_id',
						'product_id' => $product_ids,
					) );
					if ( is_array( $results ) && ! empty( $results ) ) {
						//now we matched with bundle data
						foreach ( $results as $each_item_key => $bundle_id ) {
							//bundle id is parent id upon send instock email check the bundle product is instock
							$bundle = wc_get_product( $bundle_id );
							if ( $bundle->is_in_stock() ) { //if it is true it check stock for bundled items as well
								//fetch subscribers only when the bundle product items back in stock
								$main_obj                = new InStock_WPNotifier_API( $bundle_id, 0 );
								$get_list_of_subscribers = $main_obj->InStock_WPNotifier_get_list_of_subscribers();
								if ( is_array( $get_list_of_subscribers ) && ! empty( $get_list_of_subscribers ) ) {
									$subscribers = array_merge( $subscribers, $get_list_of_subscribers );
								}
							}
						}
					}
				}
			}

			return $subscribers;
		}

        public function InStock_WPNotifier_get_phone_product_bundle_subscribers( $subscribers, $product_id ) {
            $product = wc_get_product( $product_id ); //if this

            if ( ! $product->is_type( 'bundle' ) ) { // this product type to be excluded because the below code applicable on other product types
                //now check this product exists in bundle product or not
                if ( function_exists( 'wc_pb_is_bundled_cart_item' ) ) {
                    $product_ids = array( $product_id );
                    $results     = WC_PB_DB::query_bundled_items( array(
                        'return'     => 'id=>bundle_id',
                        'product_id' => $product_ids,
                    ) );
                    if ( is_array( $results ) && ! empty( $results ) ) {
                        //now we matched with bundle data
                        foreach ( $results as $each_item_key => $bundle_id ) {
                            //bundle id is parent id upon send instock sms check the bundle product is instock
                            $bundle = wc_get_product( $bundle_id );
                            if ( $bundle->is_in_stock() ) { //if it is true it check stock for bundled items as well
                                //fetch subscribers only when the bundle product items back in stock
                                $main_obj                = new InStock_WPNotifier_API( $bundle_id, 0 );
                                $get_list_of_subscribers = $main_obj->InStock_WPNotifier_get_list_of_phone_subscribers();
                                if ( is_array( $get_list_of_subscribers ) && ! empty( $get_list_of_subscribers ) ) {
                                    $subscribers = array_merge( $subscribers, $get_list_of_subscribers );
                                }
                            }
                        }
                    }
                }
            }

            return $subscribers;
        }
	}

	new InStock_WPNotifier_Core();
}