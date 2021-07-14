<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Instock_WPNotifier_frontend_Product' ) ) {

	class Instock_WPNotifier_frontend_Product {

		public function __construct() {
			add_action( 'woocommerce_simple_add_to_cart', array(
				$this,
				'Instock_WPNotifier_display_in_simple_product'
			), 31 );
			add_action( 'woocommerce_bundle_add_to_cart', array(
				$this,
				'Instock_WPNotifier_display_in_simple_product'
			), 31 );
			add_action( 'woocommerce_woosb_add_to_cart', array(
				$this,
				'Instock_WPNotifier_display_in_simple_product'
			), 31 );
			add_action( 'woocommerce_after_variations_form', array(
				$this,
				'Instock_WPNotifier_display_in_no_variation_product'
			) );
			add_action( 'woocommerce_grouped_add_to_cart', array(
				$this,
				'Instock_WPNotifier_display_in_simple_product'
			), 32 );
			add_filter( 'woocommerce_available_variation', array(
				$this,
				'Instock_WPNotifier_display_in_variation'
			), 10, 3 );
			//some theme variation disabled by default if it is out of stock so for that workaround solution
			add_filter( 'woocommerce_variation_is_active', array(
				$this,
				'Instock_WPNotifier_enable_disabled_variation_dropdown'
			), 100, 2 );
			//hide out of stock products from catalog is checked bypass to display variation dropdown instead of hide
			add_filter( 'option_woocommerce_hide_out_of_stock_items', array(
				$this,
				'Instock_WPNotifier_display_out_of_stock_products_in_variable'
			), 999 );
		}

		public function Instock_WPNotifier_display_in_simple_product() {
			global $product;
			echo $this->Instock_WPNotifier_display_subscribe_box( $product );
		}

		public function Instock_WPNotifier_display_in_no_variation_product() {
			global $product;
			$product_type = $product->get_type();
			// Get Available variations?
			if ( $product_type == 'variable' ) {
				$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
				$get_variations = $get_variations ? $product->get_available_variations() : false;
				if ( ! $get_variations ) {
					echo $this->Instock_WPNotifier_display_subscribe_box( $product );
				}
			}
		}

		public function Instock_WPNotifier_display_subscribe_box( $product, $variation = array() ) {
			$get_option_backorder  = get_option( 'inwpnotifier_show_subscribe_on_backorder' );


			$visibility_backorder = isset( $get_option_backorder ) && $get_option_backorder == '1' ? true : false;
			if ( ! $variation && ! $product->is_in_stock() || ( ( ! $variation && ( ( $product->managing_stock() && $product->backorders_allowed() && $product->is_on_backorder( 1 ) ) || $product->is_on_backorder( 1 ) ) && $visibility_backorder ) ) ) {

				return $this->Instock_WPNotifier_html_subscribe_form( $product );
			} elseif ( $variation && ! $variation->is_in_stock() || ( ( $variation && ( ( $variation->managing_stock() && $variation->backorders_allowed() && $variation->is_on_backorder( 1 ) ) || $variation->is_on_backorder( 1 ) ) && $visibility_backorder ) ) ) {

			    return $this->Instock_WPNotifier_html_subscribe_form( $product, $variation );

			}
		}

		public function Instock_WPNotifier_html_subscribe_form( $product, $variation = array() ) {
			$hide_for_gets=get_option("inwpnotifier_hide_sub_non_log");
			$hide_for_member=get_option("inwpnotifier_hide_subscribe_loggedin");
			$check_guest_visibility  = isset( $hide_for_gets ) && !empty($hide_for_gets) && ! is_user_logged_in() ? false : true;
			$check_member_visibility = isset( $hide_for_member ) && !empty($hide_for_member) && is_user_logged_in() ? false : true;
			$product_id              = $product->get_id();
            $porduct_upload_author=get_post($product_id);
            $current_user_id=$porduct_upload_author->post_author;
            $get_dokander_notfier_on_Off=get_option("inwpnotifier_dokan_notifier_on_off_$current_user_id");
			$variation_class         = '';
			if ( $variation ) {
				$variation_id    = $variation->get_id();
				$variation_class = "inwpnotifier-subscribe-form-$variation_id";
			} else {
				$variation_id = 0;
			}
			if ( $check_guest_visibility && $check_member_visibility && ( $this->Instock_WPNotifier_is_viewable( $product_id, $variation_id ) && $this->Instock_WPNotifier_is_viewable_for_category( $product_id ) ) && $this->Instock_WPNotifier_visibility_on_regular_or_sale( $product, $variation ) && $this->Instock_WPNotifier_is_viewable_for_product_tag( $product_id )&& $get_dokander_notfier_on_Off ) {
				//wp_enqueue_script('inwpnotifier_jquery_validation');
				do_action( 'inwpnotifier_instock_before_subscribe_form' );
				$security = wp_create_nonce( 'codewpgeek-product_id-' . $product_id );
				ob_start();
				$get_placeholder=get_option("inwpnotifier_frontent_form_placeholder");
				$get_button_label=get_option("inwpnotifer_frontent_form_button");
				$placeholder  = isset( $get_placeholder ) && $get_placeholder != '' ? $get_placeholder : __( 'Your phone number', 'inwpnotifiernotifier' );
				$button_label = isset( $get_button_label ) && $get_button_label != '' ?$get_button_label : __( 'Subscribe Now', 'inwpnotifiernotifier' );

				$inwp_whatsapp_on_option=get_option("inwpnotifier_whatsapp_toggle");

				if($inwp_whatsapp_on_option=='1'){

				$instock_api  = new InStock_WPNotifier_API();

				if ( is_user_logged_in() ) {
					$whatsapp_number = get_the_author_meta( 'inwpnotifier_whatsapp_number', get_current_user_id() );
				} else {
					$whatsapp_number = '';
				}
				?>
                <section id="inwpnotifier_main_form" style="background-color:#f9f9f9;width:300px; height:280px;border-radius:10px;"
                         class="inwpnotifier-subscribe-form <?php echo $variation_class; ?>">
                    <div class="panel panel-primary inwpnotifier-panel-primary">
                        <div class="panel-heading inwpnotifier-panel-heading">
                            <div class="inwpstock_output" style="font-size: 14px;margin-left: 23px"></div>
                            <span  style="margin-left:25px; font-size:22px;  color:black; font-style:initial;font-family:serif;">
                                <?php
                                $get_form_title=get_option("inwpnotifier_frontent_form_title");

                                $form_title = esc_html__( 'Whatsapp SMS when stock available', 'inwpnotifier' );
                                echo isset( $get_form_title ) && $get_form_title != '' ? $instock_api->sanitize_text_field($get_form_title ) : $form_title;
                                ?>
                            </span>
                        </div>
                        <div style="margin-left:30px;" class="panel-body inwpnotifier-panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="inwpnotifier_intel_bg">

                                            <form id="enter_number">
                                            <div class="form-group center-block">

                                                <input type="hidden" name="country_code" id="country_code"
                                                       class="inwnotifi_country_code">
                                                <span class="inwpnotifier_mobile-text "><?php _e( "Phone Number : ", "inwpnotifier" ); ?></span>
                                                <span style="font-size:13px" id="error-msg" class="hide"></span>
                                                <span style="font-size:13px" id="valid-msg" class="hide">✓ Valid</span>
                                                <br/>

                                                <input style="border-top-style: hidden;
                                                        border-right-style:hidden;
                                                         border-left-style: hidden;
                                                           outline: none !important;
                                                            width:200px;
                                                         height:40px;
                                                         border-radius:5px"
                                                       type="tel" id="inwpnotifier_phone" name="inwpnotifier_phone"
                                                       class="inwpstock_whatsapp_sms"
                                                       value="<?php echo $whatsapp_number; ?>">

                                            </div>
											<?php do_action( 'inwpnotifier_instock_after_whatsapp_field', $product_id, $variation_id ); ?>
                                            <input type="hidden" class="inwpnotif-product-id"
                                                   name="inwpnotif-product-id" value="<?php echo $product_id; ?>"/>
                                            <input type="hidden" class="inwpnotif-variation-id"
                                                   name="inwpnotif-variation-id" value="<?php echo $variation_id; ?>"/>

                                            <input type="submit"
                                                   style="font-size: 9px; width: 80px;border-radius:3px;background-color:#006666; margin-bottom: 210px "
                                                   name="inwpstock_submit"
                                                   class="inwpstock_button" <?php echo apply_filters( 'inwpstock_submit_attr', '', $product_id, $variation_id ); ?> value="<?php echo $instock_api->sanitize_text_field( $button_label ); ?>"/>


                                        </form>

                                    </div>
                                </div>
                            </div>

                            <!-- End ROW -->
                        </div>
                    </div>
                </section>
                <script>
                    var input_ID = document.querySelector("#inwpnotifier_phone"),
                        errorMsg = document.querySelector("#error-msg"),
                        validMsg = document.querySelector("#valid-msg");


                    // here, the index maps to the error code returned from getValidationError - see readme
                    var errorMap = [" Invalid number", " Invalid country code", " Too short", " Too long", "Invalid number"];
                    // initialise plugin
                    var iti = window.intlTelInput(input_ID, {
                        utilsScript: plugin_urls + "/assets/js/utils.js"
                    });

                    var reset = function () {
                        input_ID.classList.remove("error");
                        errorMsg.innerHTML = "";
                        errorMsg.classList.add("hide");
                        validMsg.classList.add("hide");


                    };

                    // on blur: validate
                    input_ID.addEventListener('blur', function () {
                        reset();
                        if (input_ID.value.trim()) {
                            if (iti.isValidNumber()) {
                                validMsg.classList.remove("hide");

                            } else {
                                input_ID.classList.add("error");
                                var errorCode = iti.getValidationError();
                                errorMsg.innerHTML = errorMap[errorCode];
                                errorMsg.classList.remove("hide");
                            }
                        }
                    });

                    // on keyup / change flag: reset
                    input_ID.addEventListener('change', reset);
                    input_ID.addEventListener('keyup', reset);


                </script>

				<?php
                }
				return ob_get_clean();
			} else {
				return '';
			}
		}

		public function Instock_WPNotifier_display_in_variation( $atts, $product, $variation ) {
			$get_stock                 = $atts['availability_html'];
			$atts['availability_html'] = $get_stock . $this->Instock_WPNotifier_display_subscribe_box( $product, $variation );

			return $atts;
		}

		public function Instock_WPNotifier_enable_disabled_variation_dropdown( $active, $variation ) {
//			$option                    = get_option( 'inwpnotifiersettings' );
			$get_disabled_variation=get_option("inwpnotifier_ignore_disabled_variation");
			$ignore_disabled_variation = isset( $get_disabled_variation ) && $get_disabled_variation == '1' ? true : false;
			if ( ! $ignore_disabled_variation ) {
				//if it is false then enable disabled out of stock variation from theme
				$active = true;
			}

			return $active;
		}

		public function Instock_WPNotifier_is_viewable( $product_id, $variation_id = 0 ) {
			$option                  = get_option( 'inwpnotifiersettings' );
			$selected_products       = isset( $option['specific_products'] ) ? $option['specific_products'] : array();
			$product_visibility_mode = isset( $option['specific_products_visibility'] ) ? $option['specific_products_visibility'] : '';
			if ( ( is_array( $selected_products ) && ! empty( $selected_products ) ) && $product_visibility_mode != '' ) {
				if ( $variation_id > 0 ) {
					//$product_visibility_mode 1 is for show and 2 is for hide
					if ( $product_visibility_mode == '1' && ! in_array( $variation_id, $selected_products ) ) {
						return false;
					} elseif ( $product_visibility_mode == '2' && in_array( $variation_id, $selected_products ) ) {
						return false;
					}
				} else {
					if ( $product_visibility_mode == '1' && ! in_array( $product_id, $selected_products ) ) {
						return false;
					} elseif ( $product_visibility_mode == '2' && in_array( $product_id, $selected_products ) ) {
						return false;
					}
				}
			}

			return true;
		}

		public function Instock_WPNotifier_is_viewable_for_category( $product_id ) {
			$option                     = get_option( 'inwpnotifiersettings' );
			$selected_categories        = isset( $option['specific_categories'] ) ? $option['specific_categories'] : array();
			$categories_visibility_mode = isset( $option['specific_categories_visibility'] ) ? $option['specific_categories_visibility'] : '';

			if ( ( is_array( $selected_categories ) && ! empty( $selected_categories ) ) && $categories_visibility_mode != '' ) {
				$terms = wp_get_post_terms( $product_id, array( 'product_cat' ), array( 'fields' => 'slugs' ) );
				if ( $terms ) {
					//if any value matched with settings then it will return matched values if not it will return only empty value
					$intersect = array_intersect( $terms, $selected_categories );
					//$categories_visibility_mode 1 is for show and 2 is for hide
					if ( $categories_visibility_mode == '1' && empty( $intersect ) ) {
						return false;
					} elseif ( $categories_visibility_mode == '2' && ! empty( $intersect ) ) {
						return false;
					}
				}
			}

			return true;
		}

		public function Instock_WPNotifier_is_viewable_for_product_tag( $product_id ) {
			$option               = get_option( 'inwpnotifiersettings' );
			$selected_tags        = isset( $option['specific_tags'] ) ? $option['specific_tags'] : array();
			$tags_visibility_mode = isset( $option['specific_tags_visibility'] ) ? $option['specific_tags_visibility'] : '';

			if ( ( is_array( $selected_tags ) && ! empty( $selected_tags ) ) && $tags_visibility_mode != '' ) {
				$terms = wp_get_post_terms( $product_id, array( 'product_tag' ), array( 'fields' => 'slugs' ) );
				if ( $terms ) {
					//if any value matched with settings then it will return matched values if not it will return only empty value
					$intersect = array_intersect( $terms, $selected_tags );
					//$categories_visibility_mode 1 is for show and 2 is for hide
					if ( $tags_visibility_mode == '1' && empty( $intersect ) ) {
						return false;
					} elseif ( $tags_visibility_mode == '2' && ! empty( $intersect ) ) {
						return false;
					}
				} elseif ( empty( $terms ) && $tags_visibility_mode == '1' ) {
					//somewhere settings configured and set the visibility to show then hide it in current product
					return false;
				}
			}

			return true;
		}

		public function Instock_WPNotifier_visibility_on_regular_or_sale( $product, $variation ) {
//			$option           = get_option( 'inwpnotifiersettings' );
			$get_on_sale=get_option("inwpnotifier_hide_subscribe_sale_product");
			$get_on_regular=get_option("inwpnotifier_hide_subscribe_regular_product");
			$hide_on_regular  = isset( $get_on_regular) && $get_on_regular == '1' ? true : false;
			$hide_on_sale     = isset( $get_on_sale ) && $get_on_sale == '1' ? true : false;
			$check_is_on_sale = $variation ? $variation->is_on_sale() : $product->is_on_sale();
			$visibility       = ( ( $hide_on_regular && ! $check_is_on_sale ) || ( $hide_on_sale && $check_is_on_sale ) ) ? false : true;

			return $visibility;
		}

		public function Instock_WPNotifier_display_out_of_stock_products_in_variable( $value ) {
//			$option               = get_option( 'inwpnotifiersettings' );
			$get_wc_visibility=get_option("inwpnotifier_ignore_wc_visibility");
//			print_r($get_wc_visibility);
			$ignore_wc_visibility = isset( $get_wc_visibility ) && $get_wc_visibility == '1' ? true : false;
			if ( ! class_exists( 'WooCommerce' ) ) {
				//to avoid fatal error is_product conflict with other plugins like boost sales etc
				return $value;
			}
			if ( is_product() && $ignore_wc_visibility ) {
				//remove restriction only on single product page and followed by our settings page
				return 'no';
			}

			return $value;
		}

	}

	new Instock_WPNotifier_frontend_Product();
}
