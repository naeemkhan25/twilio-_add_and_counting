<?php

/**
 * manage post type
 * Class InStock_WPNotifier_Post_Type
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'InStock_WPNotifier_Post_Type' ) ) {
	class InStock_WPNotifier_Post_Type {

		public function __construct() {
			add_action( "init", array( $this, "InStock_WPNotifier_Register_custom_post_type" ) );
			add_action( "init", array( $this, "InStock_WPNotifier__Register_post_status" ) );
			add_filter( 'manage_inwpnotifier_posts_columns', array( $this, 'InStock_WPNotifier_add_columns' ) );
			add_action( 'manage_inwpnotifier_posts_custom_column', array(
				$this,
				'InStock_WPNotifier_manage_columns'
			), 10, 2 );

			add_filter( 'list_table_primary_column', array(
				$this,
				'InStock_WPNotifier_list_table_primary_column'
			), 10, 2 );
			add_filter( 'manage_edit-inwpnotifier_sortable_columns', array(
				$this,
				'InStock_WPNotifier_sortable_columns'
			) );
			add_filter( 'post_row_actions', array( $this, 'InStock_WPNotifier_manage_row_actions' ), 10, 2 );
			add_action( 'admin_action_inwpnotifier-whatsapp', array(
				$this,
				'InStock_WPNotifier_send_manual_Whatsapp_sms'
			) );
			//instock_wpnotifier_manual_phone_message

//         Bulk action unset edit
			add_filter( 'bulk_actions-edit-inwpnotifier', array(
				$this,
				'InStock_WPNotifier_remove_from_bulk_actions'
			) );
			add_filter( 'handle_bulk_actions-edit-inwpnotifier', array(
				$this,
				'InStock_WPNotifier_handle_bulk_actions'
			), 10, 3 );
			//mark status to sms sent
			add_action( 'inwpnotifier_handle_action_mark_status_sent', array(
				$this,
				'InStock_WPNotifier_bulk_mark_status_sent'
			) );
			//mark status to subscribed
			add_action( 'inwpnotifier_handle_action_mark_status_subscribed', array(
				$this,
				'InStock_WPNotifier_bulk_mark_status_subscribed'
			) );
			//mark status to unsubscribed
			add_action( 'inwpnotifier_handle_action_mark_status_unsubscribed', array(
				$this,
				'InStock_WPNotifier_bulk_mark_status_unsubscribed'
			) );
			//send SMS in bulk
			add_action( 'inwpnotifier_handle_action_send_sms', array(
				$this,
				'InStock_WPNotifier_bulk_send_manual_sms'
			) );

			// add filter option in custom post type
			add_action( 'restrict_manage_posts', array( $this, 'InStock_WPNotifier_filter_by_subscribed_products' ) );
			add_filter( 'parse_query', array( $this, 'InStock_WPNotifier_parse_query' ) );
			// add columns in product list table
			// manage columns
			add_filter( 'manage_product_posts_columns', array(
				$this,
				'InStock_WPNotifier_add_subscribers_count_column'
			), 999 );
			add_action( 'manage_product_posts_custom_column', array(
				$this,
				'InStock_WPNotifier_show_subscribers_count'
			), 999, 2 );
			add_filter( 'manage_edit-product_sortable_columns', array(
				$this,
				'InStock_WPNotifier_subscribers_sortable_columns'
			), 999 );
			add_action( 'pre_get_posts', array( $this, 'InStock_WPNotifier_sort_total_subscribers' ), 999 );
			add_filter( "add_menu_classes", array( $this, "InStock_WPNotifier_set_transient" ) );
			add_action( "admin_enqueue_scripts", array( $this, "InStock_WPNotifier_delete_transient" ) );
		}

		public function InStock_WPNotifier_Register_custom_post_type() {
			$labels = array(
				'name'               => _x( 'Subscribers', 'All Subscribers', 'inwpnotifier' ),
				'singular_name'      => _x( 'All Subscribers', 'All Subscribers', 'inwpnotifier' ),
				'menu_name'          => _x( 'Instock WPNotif ', 'Instock Notifier', 'inwpnotifier' ),
				'name_admin_bar'     => _x( 'Instock WPNotif', 'Name in Admin Bar', 'inwpnotifier' ),
				'add_new'            => _x( 'Add New Subscriber', 'add new in menu', 'inwpnotifier' ),
				'add_new_item'       => __( 'Add New Subscriber', 'inwpnotifier' ),
				'new_item'           => __( 'New Subscriber', 'inwpnotifier' ),
				'edit_item'          => __( 'Edit Subscriber', 'inwpnotifier' ),
				'view_item'          => __( 'View Subscriber', 'inwpnotifier' ),
				'all_items'          => __( 'All Subscribers', 'inwpnotifier' ),
				'search_items'       => __( 'Search Subscribers', 'inwpnotifier' ),
				'parent_item_colon'  => __( 'Parent:', 'inwpnotifier' ),
				'not_found'          => __( 'No Subscriber Found', 'inwpnotifier' ),
				'not_found_in_trash' => __( 'No Subscriber found in Trash', 'inwpnotifier' ),
			);

			$args = array(
				'labels'          => $labels,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => INSTOCKWPNOTIFIER_PLUGIN_URL.'assets/img/svg.svg',
				'capability_type' => 'post',
				'capabilities'    => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'    => true,
			);

			do_action( 'inwpnotifier_register_post_type' );
			register_post_type( 'inwpnotifier', $args );
		}

		public function InStock_WPNotifier__Register_post_status() {

			register_post_status( 'iwg_smssent', array(
				'label'                     => _x( 'SMS Sent', 'post', 'inwpnotifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'SMS Sent <span class="count">(%s)</span>', 'SMS Sent <span class="count">(%s)</span>' ),
			) );

			register_post_status( 'iwg_smsnotsent', array(
				'label'                     => _x( 'Failed', 'post', 'inwpnotifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>' ),
			) );

			register_post_status( 'iwg_subscribed', array(
				'label'                     => _x( 'Subscribed', 'post', 'inwpnotifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Subscribed <span class="count">(%s)</span>', 'Subscribed <span class="count">(%s)</span>' ),
			) );

			register_post_status( 'iwg_unsubscribed', array(
				'label'                     => _x( 'Unsubscribed', 'post', 'inwpnotifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Unsubscribed <span class="count">(%s)</span>', 'Unsubscribed <span class="count">(%s)</span>' ),
			) );

			register_post_status( 'iwg_converted', array(
				'label'                     => _x( 'Purchased', 'post', 'inwpnotifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Purchased <span class="count">(%s)</span>', 'Purchased <span class="count">(%s)</span>' ),
			) );
		}

		public function InStock_WPNotifier_add_columns( $columns ) {
			$newcolumns['cb'] = $columns['cb'];

			$newcolumns['whatsapp'] = __( 'Whatsapp Number', 'inwpnotifier' );
			$newcolumns['status']   = __( 'Status', 'inwpnotifier' );
			$newcolumns['product']  = __( 'Product', 'inwpnotifier' );
			$newcolumns['author_id']=__("Shop Name",'inwpnotifier');
			$newcolumns['popular_product']=__("Subscriber Count",'inwpnotifier');
			$newcolumns['date']     = __( 'Subscribed on', 'inwpnotifier' );

			return apply_filters( 'iwginstocknotifier_columns', $newcolumns );
		}

		public function InStock_WPNotifier_manage_columns( $column, $post_id ) {
			return $this->_InStock_WPNotifier_manage_columns( $column, $post_id );
		}

		protected function _InStock_WPNotifier_manage_columns( $columns, $post_id ) {

			$whatsapp_number = get_post_meta( $post_id, 'inwpnotifier_subscriber_phone', true );

            $product_upload_author_id=get_post_meta($post_id,'inwpnotifier_product_upload_author',true);
            $product_id   = get_post_meta( $post_id, 'inwpnotifier_product_id', true );
            $inpnotifier_popular_product=get_option("inwpnotifier_popular_product_$product_id");
			$obj = new InStock_WPNotifier_API( 0, 0, $whatsapp_number );

			switch ( $columns ) {
				case 'whatsapp':
					echo $whatsapp_number;
					break;

				case 'status':
					$this->InStock_WPNotifier_display_status( $post_id );
					break;
				case 'product':
					$obj          = new InStock_WPNotifier_API();
					$product_name = $obj->InStock_WPNotifier_display_product_name( $post_id );
					$product_id   = get_post_meta( $post_id, 'inwpnotifier_product_id', true );
					$variation_id = get_post_meta( $post_id, 'inwpnotifier_variation_id', true );
					$pid          = get_post_meta( $post_id, 'inwpnotifier_pid', true );
					$intvariation = intval( $variation_id );

					$image = '';
					if ( $intvariation > 0 ) {
						$var_obj = wc_get_product( $intvariation );
						// $image = $var_obj->get_image(array(40, 40));
						$pid = $product_id;
					} else {
						$product_obj = wc_get_product( $product_id );
					}
					if ( $product_id ) {
						$permalink = esc_url_raw( admin_url( "post.php?post=$product_id&action=edit" ) );
						$permalink = " <a href='$permalink'>#{$pid } {$product_name}</a>";
						echo $permalink;
					}
					break;
                case 'author_id':
                    echo $this->InStock_WPNotifier_display_product_author_name($product_upload_author_id);
                    break;
                case 'popular_product':
                    $permalink_two = esc_url_raw( admin_url( "post.php?post=$product_id&action=edit" ) );
                    $permalink_two = " <a href='$permalink_two'>$inpnotifier_popular_product</a>";
                    echo $permalink_two;
                   break;
				case 'date':
					echo date( 'y-m-d h:i:s' );
					break;
			}
		}
		public function InStock_WPNotifier_display_product_author_name($product_upload_author_id){
		   $wp_user=new WP_User($product_upload_author_id);
//		   print_r($wp_user);
		  return $user_niceName=$wp_user->user_nicename;
        }


		public function InStock_WPNotifier_remove_from_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			$newactions      = array();
			$list_of_actions = array(
				'mark_status_sent'         => __( 'Change status to SMS Sent', 'inwpnotifier' ),
				'mark_status_subscribed'   => __( 'Change status to Subscribed', 'inwpnotifier' ),
				'mark_status_unsubscribed' => __( 'Change status to Unsubscribed', 'inwpnotifier' ),
				'send_sms'                 => __( 'Send SMS', 'inwpnotifier' )
			);
			foreach ( $list_of_actions as $key => $each_action ) {
				$newactions[ $key ] = $each_action;
			}
			$merge_actions = array_merge( $newactions, $actions );

			return apply_filters( 'inwpnotifier_bulk_actions', $merge_actions );
		}

		public function InStock_WPNotifier_handle_bulk_actions( $redirect_to, $action, $post_ids ) {
			print_r( $action . $post_ids );
			do_action( 'inwpnotifier_handle_action_' . $action, $post_ids );

			return $redirect_to;

		}

		public function InStock_WPNotifier_display_status( $id ) {
			$get_post_status = get_post_status( $id );
		    $inwpnotfier_api=new InStock_WPNotifier_API();
		    $inwpnotfier_api->inwpnotifier_display_status($get_post_status);
		}

		public function InStock_WPNotifier_manage_row_actions( $actions, $post ) {
			$post_status = get_post_status( $post->ID );
			$newactions  = array();
			$post_id     = intval( $post->ID );

			if ( $post->post_type == 'inwpnotifier' && $post_status != 'trash' ) {


				$newactions['id'] = "<span class='id' style='color:#a0a0a0;'>" . __( 'ID:', 'inwpnotifier' ) . " $post_id" . "</span>";
				/*
				 * ****************************************************
				 */
				$edit_list = admin_url( 'edit.php?post_type=inwpnotifier' );
                    $action = 'inwpnotifier-whatsapp';
                    $nonce     = wp_create_nonce( 'inwpnotifier-whatsapp-' . $post_id );

				$query_arg = esc_url_raw( add_query_arg( array(
					'action'  => $action,
					'post_id' => $post_id,
					'nonce'   => $nonce
				), $edit_list ) );
				$caption   = __( 'Send Instock SMS', 'inwpnotifier' );
				$sendSMS   = "<a href='$query_arg'>$caption</a>";
				/*
				 * ******************************************************
				 */

				$newactions['sendsms'] = $sendSMS;

				$newactions['trash'] = $actions['trash'];
				$actions             = $newactions;

				return apply_filters( 'inwpnotifier_row_actions', $actions );
			} elseif ( $post->post_type == 'product' ) {
				$edit_list                        = admin_url( 'edit.php?post_type=inwpnotifier&iwg_filter_by_products[0]=' . $post_id . '&filter_action=Filter' );
				$query_arg                        = esc_url_raw( $edit_list );
				$api                              = new InStock_WPNotifier_API();
				$subscribers_count                = $api->InStock_WPNotifier_get_subscribers_count( $post_id, 'any' );
				$actions['iwg_subscribers_count'] = "<a href='$query_arg'>" . __( 'View Subscribers', 'inwpnotifier' ) . '(' . $subscribers_count . ')' . "</a>";
			}

			return $actions;
		}

		public function InStock_WPNotifier_list_table_primary_column( $default, $screen ) {
			if ( 'edit-inwpnotifier' === $screen ) {
				$default = 'whatsapp';
			}

			return $default;
		}

		public function InStock_WPNotifier_sortable_columns( $columns ) {
			$columns['whatsapp'] = 'title';
			$columns['product']  = 'product';
			$columns['popular_product']='subscriber';

			return $columns;
		}

		public function InStock_WPNotifier_send_manual_Whatsapp_sms() {
			$nonce   = $_REQUEST['nonce'];
			$post_id = intval( $_REQUEST['post_id'] );
			if ( wp_verify_nonce( $nonce, 'inwpnotifier-whatsapp-' . $post_id ) ) {
				//send sms
                $inwpnotifier_api = new InStock_WPNotifier_API();
                $inwpnotifier_api->inwpnotifier_manual_whatsapp_sms($post_id);
			} else {
				instock_WPNotfier_add_persistent_notice( array(
					'type'    => 'error',
					'message' => __( "Security Check Failed, Please try later", 'inwpnotifier' ),
				) );
			}
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();
		}


		//instock manual phone sms


		public function InStock_WPNotifier_bulk_mark_status_sent( $post_ids ) {
			$count     = count( $post_ids );
			$stock_api = new InStock_WPNotifier_API();
			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $each_id ) {
					$stock_api->InStock_WPNotifier_sms_sent_status( $each_id );
					do_action( 'iwg_instock_bulk_status_action', $each_id, 'iwg_smssent' );
					$logger = new InStock_WPNotifier_Logger( 'success', "Manual changed status to SMS Sent - $each_id" );
					$logger->InStock_WPNotifier_record_log();
				}

				instock_WPNotfier_add_persistent_notice( array(
					'type'    => 'success',
					'message' => __( "$count - Data(s) Manually marked status to SMS Sent", 'inwpnotifier' ),
				) );
			}
		}

		public function InStock_WPNotifier_bulk_mark_status_subscribed( $post_ids ) {
			$count     = count( $post_ids );
			$stock_api = new InStock_WPNotifier_API();
			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $each_id ) {
					$stock_api->InStock_WPNotifier_subscriber_subscribed( $each_id );
					do_action( 'iwg_instock_bulk_status_action', $each_id, 'iwg_subscribed' );
					$logger = new InStock_WPNotifier_Logger( 'success', "Manual changed status to Subscribe - $each_id" );
					$logger->InStock_WPNotifier_record_log();
				}

				instock_WPNotfier_add_persistent_notice( array(
					'type'    => 'success',
					'message' => __( "$count - Data(s) Manually marked status to Subscribe", 'inwpnotifier' ),
				) );
			}
		}

		public function InStock_WPNotifier_bulk_mark_status_unsubscribed( $post_ids ) {
			$count     = count( $post_ids );
			$stock_api = new InStock_WPNotifier_API();
			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $each_id ) {
					$stock_api->InStock_WPNotifier_subscriber_unsubscribed( $each_id );
					do_action( 'iwg_instock_bulk_status_action', $each_id, 'iwg_unsubscribed' );
					$logger = new InStock_WPNotifier_Logger( 'success', "Manual changed status to Unsubscribe - $each_id" );
					$logger->InStock_WPNotifier_record_log();
				}

				instock_WPNotfier_add_persistent_notice( array(
					'type'    => 'success',
					'message' => __( "$count - Data(s) Manually marked status to Unsubscribe", 'inwpnotifier' ),
				) );
			}
		}

		public function InStock_WPNotifier_bulk_send_manual_sms( $post_ids ) {
			$sent       = 0;
			$failed     = 0;
			$not_exists = 0;
			$count      = count( $post_ids );
			$stock_api  = new InStock_WPNotifier_API();
			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				$logger = new InStock_WPNotifier_Logger( 'success', "Bulk SMS process started for data #$count" );
				$logger->InStock_WPNotifier_record_log();
				foreach ( $post_ids as $post_id ) {
					$get_phone      = get_post_meta( $post_id, 'inwpnotifier_subscriber_phone', true );


                        $send_SMSler = new InStock_WPNotifier_Instock_Subscribe_SMS($post_id);

					$pid            = get_post_meta( $post_id, 'inwpnotifier_pid', true );
					$api            = new InStock_WPNotifier_API();
					$product_exists = wc_get_product( $pid );
					if ( $product_exists ) {

                            $send_sms = $send_SMSler->send_whatsapp_sms();

						if ( $send_sms ) {
							$message    = __( "Instock SMS sent to {whatsapp_number} successfully", 'inwpnotifier' );
							$replace    = str_replace( '{whatsapp_number}', $get_phone, $message );
							$sms_status = $api->InStock_WPNotifier_sms_sent_status( $post_id );
							$logger     = new InStock_WPNotifier_Logger( 'success', "Bulk mail sent to #$get_phone - #$post_id" );
							$logger->InStock_WPNotifier_record_log();
							$sent ++;
						} else {
							$error_msg     = __( 'Unable to send Instock SMS to this {whatsapp_number}', 'inwpnotifier' );
							$error_replace = str_replace( '{whatsapp_number}', $get_phone, $error_msg );
							$sms_status    = $api->InStock_WPNotifier_sms_not_sent_status( $post_id );
							$logger        = new InStock_WPNotifier_Logger( 'error', "$error_replace" . " #$post_id" );
							$logger->InStock_WPNotifier_record_log();
							$failed ++;
						}
					} else {
						$error_msg     = __( 'Unable to send Instock SMS to this {whatsapp_number} as stock product does not exists/deleted !!!', 'inwpnotifier' );
						$error_replace = str_replace( '{whatsapp_number}', $get_phone, $error_msg );
						$logger        = new InStock_WPNotifier_Logger( 'error', "$error_replace" . " #$post_id" );
						$logger->InStock_WPNotifier_record_log();
						$not_exists ++;
					}
				}

				$final_notice = __( "Bulk SMS: ", 'inwpnotifier' );
				$final_notice .= $count > 0 ? "Total = $count" : '';
				$final_notice .= $sent > 0 ? " Sent = $sent" : '';
				$final_notice .= $failed > 0 ? " Failed = $failed" : '';
				$final_notice .= $not_exists > 0 ? " Product not Exists = $not_exists" : '';

				$logger = new InStock_WPNotifier_Logger( 'info', $final_notice );
				$logger->InStock_WPNotifier_record_log();

				instock_WPNotfier_add_persistent_notice( array(
					'type'    => 'success',
					'message' => $final_notice,
				) );
			}
		}

		public function InStock_WPNotifier_set_transient( $menu ) {
			$get_subscriber_count = get_transient( "subscriber_count" ) ? get_transient( "subscriber_count" ) : 0;
			if ( $get_subscriber_count > 0 ) {
				$menu[6][0] = sprintf( '%s<span class="awaiting-mod">%s</span>', __( "Instock WPNotif", "inwpnotifier" ), $get_subscriber_count );
			}

			return $menu;
		}

		public function InStock_WPNotifier_delete_transient( $screen ) {
			$current_screen = get_current_screen();
			if ( $screen == "edit.php" && 'inwpnotifier' == $current_screen->post_type ) {
				delete_transient( "subscriber_count" );
			}

		}

		public function InStock_WPNotifier_filter_by_subscribed_products() {
			if ( isset( $_GET['post_type'] ) ) {
				$type = $_GET['post_type'];
				if ( 'inwpnotifier' == $type ) {
					?>
                    <select style="width:320px;" data-placeholder="<?php _e( "Filter by products", 'inwpnotifier' ); ?>"
                            data-allow_clear="true" tabindex="-1" aria-hidden="true" name="iwg_filter_by_products[]"
                            multiple="multiple" class="wc-product-search">
						<?php
						$current_v = isset( $_GET['iwg_filter_by_products'] ) ? $_GET['iwg_filter_by_products'] : array();
						if ( is_array( $current_v ) && ! empty( $current_v ) ) {
							foreach ( $current_v as $each_id ) {
								$product = wc_get_product( $each_id );
								if ( $product ) {
									printf( '<option value="%s"%s>%s</option>', $each_id, ' selected="selected"', wp_kses_post( $product->get_formatted_name() ) );
								}
							}
						}
						?>
                    </select>
					<?php
				}
			}
		}

		public function InStock_WPNotifier_parse_query( $query ) {
			global $pagenow;
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby' );
			if ( isset( $_GET['post_type'] ) ) {
				$type = $_GET['post_type'];
				if ( 'inwpnotifier' == $type && is_admin() && $pagenow == 'edit.php' && isset( $_GET['iwg_filter_by_products'] ) && ! empty( $_GET['iwg_filter_by_products'] ) && is_array( $_GET['iwg_filter_by_products'] ) ) {

					$meta_query                      = array(
						'relation' => 'OR',
						array(
							'key'     => 'inwpnotifier_pid',
							'value'   => $_GET['iwg_filter_by_products'],
							'compare' => 'IN',
						),
						array(
							'key'     => 'inwpnotifier_product_id',
							'value'   => $_GET['iwg_filter_by_products'],
							'compare' => 'IN',
						)
					);
					$query->query_vars['meta_query'] = $meta_query;
				}

				if ( 'inwpnotifier' == $type && is_admin() && $pagenow == 'edit.php' && 'product' == $orderby ) {
					// for orderby just order based on product id
					$query->set( 'meta_key', 'inwpnotifier_pid' );
					$query->set( 'orderby', 'meta_value_num' );
				}
			}
		}

		public function InStock_WPNotifier_add_subscribers_count_column( $columns ) {
			$date_columns = $columns['date'];
			unset( $columns['date'] );
			$columns['total_subscrib'] = __( 'Subscribers', 'inwpnotifier' );
			$columns['date']           = $date_columns;

			return $columns;
		}

		public function InStock_WPNotifier_show_subscribers_count( $columns, $post_id ) {
			if ( $columns == 'total_subscrib' ) {
				$edit_list         = admin_url( 'edit.php?post_type=inwpnotifier&iwg_filter_by_products[0]=' . $post_id . '&post_status=iwg_subscribed&filter_action=Filter' );
				$query_arg         = esc_url_raw( $edit_list );
				$api               = new InStock_WPNotifier_API();
				$subscribers_count = intval( $api->InStock_WPNotifier_get_subscribers_count( $post_id, 'iwg_subscribed' ) );
				$get_data          = intval( get_post_meta( $post_id, 'inwpnotifier_total_subscribers', true ) );
				if ( $get_data != $subscribers_count ) {
					update_post_meta( $post_id, 'inwpnotifier_total_subscribers', $subscribers_count );
				} else {
					add_post_meta( $post_id, 'inwpnotifier_total_subscribers', $subscribers_count, true );
				}
				if ( $subscribers_count > 0 ) {
					echo "<a href='$query_arg'> $subscribers_count </a>";
				} else {
					echo $subscribers_count;
				}
			}
		}

		public function InStock_WPNotifier_subscribers_sortable_columns( $columns ) {
			$columns['total_subscrib'] = 'total_subscrib';

			return $columns;
		}

		public function InStock_WPNotifier_sort_total_subscribers( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby' );
			if ( 'total_subscrib' == $orderby ) {
				$query->set( 'meta_key', 'inwpnotifier_total_subscribers' );
				$query->set( 'orderby', 'meta_value_num' );
			}
		}

	}

	new InStock_WPNotifier_Post_Type();
}
?>

