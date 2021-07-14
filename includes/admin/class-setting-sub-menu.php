<?php
/**
 * Class Stock_Notifier_Setting_Menu
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists("InStock_WPNotifier_Setting_Sub_Menu")) {
    
    class InStock_WPNotifier_Setting_Sub_Menu
    {
        public $active_tab;
        public function __construct()
        {
            add_action("admin_menu", array($this, "inwpnotifier_add_setting_subMenu"));
            add_action("admin_init",array($this,"inwpnotifier_Register_manage_settings"));
            add_action("admin_init",array($this,"default_value"));
            $this->api=new InStock_WPNotifier_API();
            add_action("admin_post_inwpnotifier_save_settings",array($this,"inwpnotifer_save_option_data"));

        }



        public function inwpnotifier_add_setting_subMenu()
        {

             add_submenu_page("edit.php?post_type=inwpnotifier", __("Settings", "inwpnotifier"), __("Settings", "inwpnotifier"), "manage_woocommerce", "instock-wpNotifier_special", array($this, "inwpnotifier_Manage_setting_subMenu"));
             add_submenu_page("edit.php?post_type=inwpnotifier", __("Settings", "inwpnotifier"), __("Sp Settings", "inwpnotifier"), "manage_woocommerce", "instock-wpNotifier", array($this, "inwpnotifier_Manage_setting_special"));

        }

        public  function inwpnotifer_save_option_data(){

                if(!check_admin_referer("inwpnotifer_form")){
                    wp_die("you are  not authorized");
                }
//                    $whatsapp_toogles=isset($_POST['inwpnotifier_whatsapp_toggle'])?$_POST["inwpnotifier_whatsapp_toggle"]:0;
                    $whatsapp_account_sids=isset($_POST['inwpnotfier_twilio_SID'])?$_POST["inwpnotfier_twilio_SID"]:'';
                    $whatsapp_auth_tokens=isset($_POST['inwpnotifier_twilio_token'])?$_POST["inwpnotifier_twilio_token"]:'';
                    $whatsapp_sender_numbers=isset($_POST['inwpnotifier_twilio_senderNumber'])?$_POST["inwpnotifier_twilio_senderNumber"]:'';

//                    $whatsapp_toogle=sanitize_text_field($whatsapp_toogles);
                    $whatsapp_account_sid=trim(sanitize_text_field($whatsapp_account_sids),' ');
                    $whatsapp_auth_token=trim(sanitize_text_field($whatsapp_auth_tokens),' ');
                    $whatsapp_sender_number=trim(sanitize_text_field($whatsapp_sender_numbers),' ');

//                    update_option("inwpnotifier_whatsapp_toggle",$whatsapp_toogle);
                    update_option("inwpnotfier_twilio_SID",$whatsapp_account_sid);
                    update_option("inwpnotifier_twilio_token",$whatsapp_auth_token);
                    update_option("inwpnotifier_twilio_senderNumber",$whatsapp_sender_number);

                    //end gateway settings
                    //start user consent settings
                     $non_logdins_user=isset($_POST['inwpnotifier_hide_sub_non_log'])?$_POST['inwpnotifier_hide_sub_non_log']:0;

                     $subscribe_logdins=isset($_POST['inwpnotifier_hide_subscribe_loggedin'])?$_POST['inwpnotifier_hide_subscribe_loggedin']:0;
                     $show_backorders=isset($_POST['inwpnotifier_show_subscribe_on_backorder'])?$_POST['inwpnotifier_show_subscribe_on_backorder']:0;
                     $hide_regular_products=isset($_POST['inwpnotifier_hide_subscribe_regular_product'])?$_POST['inwpnotifier_hide_subscribe_regular_product']:0;
                     $hide_regular_sale_products=isset($_POST['inwpnotifier_hide_subscribe_sale_product'])?$_POST['inwpnotifier_hide_subscribe_sale_product']:0;
                     $ignore_disabled_variations=isset($_POST['inwpnotifier_ignore_disabled_variation'])?$_POST['inwpnotifier_ignore_disabled_variation']:0;
                     $ignore_wc_visibilitys=isset($_POST['inwpnotifier_ignore_wc_visibility'])?$_POST['inwpnotifier_ignore_wc_visibility']:0;
                     $dokan_per_page=isset($_POST['inwpnotifir_dokan_subsciber_per_page'])?$_POST['inwpnotifir_dokan_subsciber_per_page']:0;

                     $non_logdins_users=sanitize_text_field($non_logdins_user);

                     $subscribe_logdin=sanitize_text_field($subscribe_logdins);
                     $show_backorder=sanitize_text_field($show_backorders);
                     $hide_regular_product=sanitize_text_field($hide_regular_products);
                     $hide_regular_sale_product=sanitize_text_field($hide_regular_sale_products);
                     $ignore_disabled_variation=sanitize_text_field($ignore_disabled_variations);
                     $ignore_wc_visibility=sanitize_text_field($ignore_wc_visibilitys);
                     $dokan_per_pages=sanitize_text_field($dokan_per_page);

                     update_option("inwpnotifier_hide_sub_non_log",$non_logdins_users);

                     update_option("inwpnotifier_hide_subscribe_loggedin",$subscribe_logdin);
                     update_option("inwpnotifier_show_subscribe_on_backorder",$show_backorder);
                     update_option("inwpnotifier_hide_subscribe_regular_product",$hide_regular_product);
                     update_option("inwpnotifier_hide_subscribe_sale_product",$hide_regular_sale_product);
                     update_option("inwpnotifier_ignore_disabled_variation",$ignore_disabled_variation);
                     update_option("inwpnotifier_ignore_wc_visibility",$ignore_wc_visibility);
                     update_option("inwpnotifir_dokan_subsciber_per_pages",$dokan_per_pages);
                    //end notification settings
                    //start general settings
                    $this->inwpnotifer_general_option_data_save();

                    //end general setting
                    //start sms settings
                    $this->inwwpnotifer_sms_settings_option_data_save();
                    //end sms settings

                    wp_redirect("edit.php?post_type=inwpnotifier&page=instock-wpNotifier_special");


        }
        private function inwwpnotifer_sms_settings_option_data_save(){
            //default value
             $success_sub_subject = 'You subscribed to {product_name} at {shopname}';
              $instock_sms_subject = 'Product {product_name} has back in stock';
            $success_subscribe_message = "Dear {subscriber_number}, <br/>"
                . "Thank you for subscribing to the #{product_name}. We will sms you once product back in stock";
            $instock_message = "Hello {whatsapp_number}, <br/>"
                . "Thanks for your patience and finally the wait is over!
                <br/> Your Subscribed Product {product_name} is now back in stock! We only have a limited amount of stock,
             and this sms is not a guarantee you will get one, so hurry to be one of the lucky shoppers who do
             <br/> Add this product {product_name} directly to your cart <a href='{cart_link}'>{cart_link}</a>";
            //end default value.

            $inwp_enable_success=isset($_POST['inwpnotifier_enable_success_subscription'])?$_POST["inwpnotifier_enable_success_subscription"]:0;
            //trim the value
                $inwp_subject_post=trim($_POST['inwpnotifier_success_sub_subject'],' ');
               $inwp_success_sub_post=trim($_POST["inwpnotifier_success_sub_message"],' ');
               $inwp_enable_instock_subject_post=trim($_POST["inwpnotifir_instock_sub_subject"],' ');
               $inwp_enable_instock_message_post=trim($_POST["inwpnotifier_instock_sub_message"],' ');

            //trim

               $inwp_success_sub_subjects=isset($inwp_subject_post)&& !empty($inwp_subject_post)?$inwp_subject_post:$success_sub_subject;
               $inwp_success_sub_message=isset($inwp_success_sub_post)&& !empty($inwp_success_sub_post)?$inwp_success_sub_post:$success_subscribe_message;
               $inwp_enable_instock_subject=isset($inwp_enable_instock_subject_post)&& !empty($inwp_enable_instock_subject_post)?$inwp_enable_instock_subject_post:$instock_sms_subject;
               $inwp_enable_instock_message=isset($inwp_enable_instock_message_post)&& !empty($inwp_enable_instock_message_post)?$inwp_enable_instock_message_post:$instock_message;
                $inwp_enable_instock_success=isset($_POST['inwpnotifier_enable_instock_sms'])?$_POST["inwpnotifier_enable_instock_sms"]:0;

             $inwp_enable_successes=sanitize_text_field($inwp_enable_success);
              $inwp_success_sub_subject=sanitize_text_field($inwp_success_sub_subjects);
              $inwp_success_sub_messages=sanitize_text_field($inwp_success_sub_message);
              $inwp_enable_instock_successes=sanitize_text_field($inwp_enable_instock_success);
              $inwp_enable_instock_subjects=sanitize_text_field($inwp_enable_instock_subject);
              $inwp_enable_instock_messages=sanitize_text_field($inwp_enable_instock_message);

               update_option("inwpnotifier_enable_success_subscription",$inwp_enable_successes);
               update_option("inwpnotifier_success_sub_subject",$inwp_success_sub_subject);
               update_option("inwpnotifier_success_sub_message",$inwp_success_sub_messages);
               update_option("inwpnotifier_enable_instock_sms",$inwp_enable_instock_successes);
               update_option("inwpnotifir_instock_sub_subject",$inwp_enable_instock_subjects);
               update_option("inwpnotifier_instock_sub_message",$inwp_enable_instock_messages);

        }


        private function inwpnotifer_general_option_data_save(){
            //default value
             $inwp_frontent_form_title = 'sms when stock available';
             $inwp_frontent_form_placeholder = 'your phone number';
             $inwp_frontent_button_lable = 'Subscribe Now';
             $inwp_frontent_success_subscription = 'You have successfully subscribed';
             $inwp_frontent_already_subscription = 'you have already subscribed';
             $inwp_frontent_empty_error_message = 'phone number cannot be empty';

            //end default
            //trim value
             $inwp_frontent_post_title=trim($_POST['inwpnotifier_frontent_form_title'],' ');
             $inwp_frontent_post_placeholder=trim($_POST['inwpnotifier_frontent_form_placeholder'],' ');
             $inwp_frontent_post_button=trim($_POST['inwpnotifer_frontent_form_button'],' ');
             $inwp_frontent_post_success=trim($_POST['inwpnotifier_success_subscription_message'],' ');
             $inwp_frontent_post_already=trim($_POST['inwpnotifier_already_subscribed_message'],' ');
             $inwp_frontent_post_empty=trim($_POST['inwpnotifier_field_empty_errors'],' ');
            //end trim

                 $frontent_form_titles=isset($inwp_frontent_post_title)&& !empty($inwp_frontent_post_title)?$inwp_frontent_post_title:$inwp_frontent_form_title;
                 $frontent_form_placeholders=isset($inwp_frontent_post_placeholder)&& !empty($inwp_frontent_post_placeholder)?$inwp_frontent_post_placeholder:$inwp_frontent_form_placeholder;
                 $frontent_form_buttons=isset($inwp_frontent_post_button)&& !empty($inwp_frontent_post_button)?$inwp_frontent_post_button:$inwp_frontent_button_lable;
                 $success_subscription_messages=isset($inwp_frontent_post_success)&& !empty($inwp_frontent_post_success)?$inwp_frontent_post_success:$inwp_frontent_success_subscription;
                 $already_subscribed_messages=isset($inwp_frontent_post_already)&& !empty($inwp_frontent_post_already)?$inwp_frontent_post_already:$inwp_frontent_already_subscription;
                 $field_empty_errors=isset($inwp_frontent_post_empty)&& !empty($inwp_frontent_post_empty)?$inwp_frontent_post_empty:$inwp_frontent_empty_error_message;

                $frontent_form_title=sanitize_text_field($frontent_form_titles);
                $frontent_form_placeholder=sanitize_text_field($frontent_form_placeholders);
                $frontent_form_button=sanitize_text_field($frontent_form_buttons);
                $success_subscription_message=sanitize_text_field($success_subscription_messages);
                $already_subscribed_message=sanitize_text_field($already_subscribed_messages);
                $field_empty_error=sanitize_text_field($field_empty_errors);

                update_option("inwpnotifier_frontent_form_title",$frontent_form_title);
                update_option("inwpnotifier_frontent_form_placeholder",$frontent_form_placeholder);
                update_option("inwpnotifer_frontent_form_button",$frontent_form_button);
                update_option("inwpnotifier_success_subscription_message",$success_subscription_message);
                update_option("inwpnotifier_already_subscribed_message",$already_subscribed_message);
                update_option("inwpnotifier_field_empty_errors",$field_empty_error);
        }
        public function inwpnotifier_Manage_setting_special(){
             echo "<div class='wrap'>";
             ?>
             <div class="inwp_special_container" style="width: 850px;background-color: #BCDDEC">
                <div class="inwp_special_heading" style="margin-left: 20px;margin-right: 20px;margin-top: 20px">
                    <div class="inwp_heding">

                    <h1 style="margin-top: 20px"><?php _e('Visibility Taxonomy Settings ', 'inwpnotifier'); ?></h1>
                <hr/>
                </div>
            <?php
             settings_errors();
            ?>

            <form action='options.php' method='post' id="inwpnotifier_spsettings">
                <?php
                settings_fields('inwpnotifier_settings');
                do_action('inwpnotifier_before_section');
                do_settings_sections('inwpnotifier_settings');
                submit_button();
                ?>
            </form>
            </div>
            </div>
            <?php
            echo "</div>";
           }

        public function inwpnotifier_Manage_setting_subMenu()
        {
            $active_tab = 'apisettings';
            if (isset($_GET['tab'])) {
            $active_tab = !empty($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $active_tab;
        }
            ?>
           <div class="inpwpnotifier_admin_conf inwpnotifier_admin_fields" xmlns="http://www.w3.org/1999/html">

            <div class="inwpnotifier_preview_overlay">
                <div class="inwpnotifier-preview_wrapper">
                    <img src="" draggable="false"/>

                </div>
            </div>

            <div class="inwpnotifier_load_overlay_gs">
                <div class="inwpnotifier_load_content">

                    <div class="inwpnotifier_circle-loader">
                        <div class="inwpnotifier_checkmark inwpnotifier_draw"></div>

                    </div>

                </div>
            </div>

            <div class="inwpnotifier_log_setge">
                <div class="inwpnotifier_admin_left_side">
                    <div class="inwpnotifier_admin_left_side_content">

                        <div class="inwpnotifier_sts_logo">
                            <?php $this->inwwpnotifer_admin_header_logo();?>
                            <ul class="inwpnotifier_gs_log_men">
                                <li><a class="inwpnotifier_ngmc"
                                       href="#"
                                       target="_blank"><b><?php esc_html_e('Documentation', 'inwpnotifier'); ?></b></a>
                                </li>
                                <li><a class="inwpnotifier_ngmc" href="#"
                                       target="_blank"><b><?php esc_html_e('Support', 'inwpnotifier'); ?></b></a>
                                </li>

                                <li><button class="inwpnotifer_pro_bttton"><a  class="inwpnotifier_ngmc inwpnotifer_pro_bttton" href="#" ><b><?php esc_html_e('GET PRO', 'inwpnotifier'); ?></b></a></button>
                                </li>


                            </ul>
                        </div>
                         <input type="hidden" id="inwpnotifier_activated" value="1">

                           <div class="inwpnotifier-tab-wrapper">
                            <ul class="inwpnotifier-tab-ul">
                                <li><a href="?post_type=inwpnotifier&page=instock-wpNotifier&amp;tab=apisettings"
                                       class="updatetabview inwpnotifier-nav-tab  <?php echo $active_tab == 'apisettings' ? 'inwpnotifier-nav-tab-active' : ''; ?>"
                                       tab="apisettingstab" id="gateway"><?php esc_html_e('Gateway', 'inwpnotifier'); ?></a></li>

                                <li><a href="?post_type=inwpnotifier&page=instock-wpNotifier&amp;tab=general"
                                       class="updatetabview inwpnotifier-nav-tab <?php echo $active_tab == 'general' ? 'inwpnotifier-nav-tab-active' : ''; ?>"
                                       tab="generalTab"><?php esc_html_e('General', 'inwpnotifier'); ?></a>
                                </li>

                                <li><a href="?post_type=inwpnotifier&page=instock-wpNotifier&amp;tab=notification"
                                       class="updatetabview inwpnotifier-nav-tab  <?php echo $active_tab == 'notification' ? 'inwpnotifier-nav-tab-active' : ''; ?>"
                                       tab="notificationTab"><?php esc_html_e('Notification', 'inwpnotifier'); ?></a>
                                </li>

                                <li><a href="?post_type=inwpnotifier&page=instock-wpNotifier&amp;tab=multivendor"
                                       class="updatetabview inwpnotifier-nav-tab  <?php echo $active_tab == 'multivendor' ? 'inwpnotifier-nav-tab-active' : ''; ?>"
                                       tab="multivendorTab"><?php esc_html_e('Mutivendor Settings', 'inwpnotifier'); ?></a>
                                </li>

                            </ul>

                        </div>
                        <form method="post"  id="inwpnotifier_settings" action="<?php echo admin_url("admin-post.php");?>"
                              class="inwpnotifier_activation_form"
                              enctype="multipart/form-data">
                                <?php
                                wp_nonce_field("inwpnotifer_form");
                                    $this->inwpnotifier_skeleton_loader();
                                   ?>
                              <div data-tab="apisettingstab"
                                 class="inwpnotifier_admin_in_pt apisettingstab digtabview <?php echo $active_tab == 'apisettings' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                <?php  $this->inwwpnotifer_api_settings(); ?>
                            </div>
                             <div data-tab="generalTab"
                                 class="inwpnotifier_admin_in_pt generalTab digtabview <?php echo $active_tab == 'general' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                <?php $this->inwwpnotifer_user_consent_settings();?>
                                 <div class="inwpnotifier_admin_head"><span><?php esc_html_e('Some Important Notice', 'inwpnotifier'); ?></span></div>
                                      <label><?php _e("*Don't overwrite disabled out of stock variations from theme configuration","inwwpnotifer"); ?></label>
                                    <div class="notice notice-info"><?php _e("Some themes disable variation out of stock by default and some by an option, when activate our plugin it overwrite theme configuration(disabled variation become selectable), so by enable this option our plugin settings will not overwrite theme configuration","inwpnotifier")?></div>
                                    <br/>
                                    <hr/>
                                     <label><?php _e("*Ignore WooCommerce Out of Stock Visibility Settings for Variation","inwwpnotifer"); ?></label>
                                  <div class="notice notice-info" ><?php _e("WooCommerce has an option to hide out of stock products from catalog
                                    (WooCommerce->Products->Inventory->Out of stock visibililty),when you enable/enabled this option will hide out of stock products from shop page/category page,
                                   but this also hide out of stock variations from variation dropdown, for that we provide option to ignore that woocommerce 
                                   out of stock visibility settings only for variable products.
                                     ","inwpnotifier");?></div>
                                 </div>

                              <div data-tab="notificationTab"
                                 class="inwpnotifier_admin_in_pt notificationTab digtabview <?php echo $active_tab == 'notification' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                <?php  $this->inwwpnotifer_genarel_settings(); ?>
                               <?php $this->inwwpnotifer_message_settings_tab();?>
                               </div>
                                 <div data-tab="multivendorTab"
                                 class="inwpnotifier_admin_in_pt multivendorTab digtabview <?php echo $active_tab == 'multivendor' ? 'digcurrentactive' : '" style="display:none;'; ?>">

                                <?php  $this->inwpnotifier_dokan_subscribers_perpage();?>
                               </div>

                            <input type="hidden" name="action" value="inwpnotifier_save_settings">
                             <?php
                                submit_button("Save Changes","inwp_custom-css");
                               ?>

                            </form>

                              </div>

                        <?php

         }

            public function inwpnotifier_skeleton_loader()
            {
        ?>
        <div id="inwpnotifier_loading_container">
            <div id="inwpnotifier_loading_anim">

                <div class="wpn-skeleton-loader">
                    <div class="skltn-shine">
                    </div>
                    <div class="skltn-container">
                        <div class="skltn-sec">
                            <div class="skltn-line"></div>
                            <div class="skltn-setting"></div>
                        </div>

                        <div class="skltn-sec skltn-one">
                            <div class="skltn-line"></div>
                            <div class="skltn-setting"></div>
                        </div>

                        <div class="skltn-sec skltn-two">
                            <div class="skltn-line"></div>
                            <div class="skltn-setting"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    public function inwwpnotifer_message_settings_tab(){

            $enable_success_checked="";
            $enabl_success_active_value=get_option("inwpnotifier_enable_success_subscription");
            if($enabl_success_active_value==1){
                $enable_success_checked="checked";
            }
            $enable_success_subject=get_option("inwpnotifier_success_sub_subject");
            $enable_success_message=get_option("inwpnotifier_success_sub_message");
      ?>

       <div class="inwpnotifier_admin_head"><span><?php esc_html_e('SMS Settings', 'inwpnotifier'); ?></span></div>
        <div class="notice notice-info" ><?php _e("Available Shortcodes to be used for subject and message
         {product_name}, {product_id}, {product_link}, {shopname}, {whatsapp_number}, {subscriber_number}, {cart_link}, {only_product_name}, {only_product_sku}.","inwpnotifier");?></div>
                <hr/>
                 <br/>
                   <div class="inwpnotifier_success_subscription_container">
                    <div class="form-table inwpnotifier_success_subscription_head gateway_table">
                     <div class="twiliocred gateway_conf">
                    <span scope="row"  style="margin-left: 30px"><label for="inwp_twiliosid"><?php esc_html_e('Enable Success Subscription SMS', 'inwpnotifier'); ?> </label>
                    </span>
                    <span scope="row"  style="margin-left: 53px;"><label for="inwpnotifier_enable_success_sub">

                         <span class="inwpnotifier_enable_success_sub" id="inwpnotifier_enable_success_sub">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_enable_success_subscription"  class="inwpnotifier_email_toggle" id="inwpnotifier_enable_success_subscription" value="<?php echo $enabl_success_active_value; ?>" <?php echo $enable_success_checked; ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>
                          </label>
                         </div>
                         <br/>
                    <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row"  style="width: 30px;margin-left: 30px"><label for="inwpnotifier_success_sub_subject"><?php esc_html_e('Success Subscription SMS Subject', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 45px;">
                        <input  type="text" id="inwpnotifier_success_sub_subject" name="inwpnotifier_success_sub_subject" class="regular-text"
                               value="<?php echo $enable_success_subject; ?>"
                               placeholder="<?php esc_html_e('You subscribed to {product_name} at {shopname}', 'inwpnotifier'); ?>"
                               autocomplete="off"/>
                    </span>
                </div>
                 <span scope="row" style="width: 30px; margin-left: 30px;margin-top: 240px"><label for="inwpnotifier_success_sub_message"><?php esc_html_e('Success Subscription Message', 'inwpnotifier'); ?> </label>
                    </span>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">

                    <span style="margin-left: 329px">
                        <textarea rows="5" type="text" id="inwpnotifier_success_sub_message" name="inwpnotifier_success_sub_message" class="regular-text"><?php echo $enable_success_message; ?></textarea>

                    </span>
                </div>

                <br/>
                <hr/>
            </div>
            </div>

      <?php
      $this->inwwpnotifer_enabel_Instock_sms();

    }
    public function inwwpnotifer_enabel_Instock_sms(){
           $enable_success_instock_checked="";
            $enable_instock_active_value=get_option("inwpnotifier_enable_instock_sms");
            if($enable_instock_active_value==1){
                $enable_success_instock_checked="checked";
            }
            $enable_instock_subject=get_option("inwpnotifir_instock_sub_subject");
            $enable_instock_message=get_option("inwpnotifier_instock_sub_message");
            ?>
            <br/>
                  <div class="inwpnotifier_instock_sms_container">
                    <div class="form-table inwpnotifier_instock_sms_header gateway_table">
                     <div class="twiliocred gateway_conf">
                    <span scope="row"  style="margin-left: 30px"><label for="inwp_twiliosid"><?php esc_html_e('Enable Instock SMS', 'inwpnotifier'); ?> </label>
                    </span>
                    <span scope="row"  style="margin-left: 157px;"><label for="inwp_twiliosid">

                         <span class="inwpnotifier_enable_instock_sm" id="inwpnotifier_enable_instock_sm">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_enable_instock_sms"  class="inwpnotifier_email_toggle" id="inwpnotifier_enable_instock_sms" value="<?php echo $enable_instock_active_value;?>" <?php echo $enable_success_instock_checked; ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>
                          </label>
                         </div>
                         <br/>
                    <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row"  style="width: 30px;margin-left: 30px"><label for="inwpnotifir_instock_sub_subject"><?php esc_html_e('Instock SMS  Subject', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 67px;">
                        <input  type="text" id="inwpnotifir_instock_sub_subject" name="inwpnotifir_instock_sub_subject" class="regular-text"
                               value="<?php echo $enable_instock_subject; ?>"
                               placeholder="<?php esc_html_e('Product {product_name} has back in stock', 'inwpnotifier'); ?>"
                               autocomplete="off"/>
                    </span>
                </div>
                 <span scope="row" style="width: 30px; margin-left: 30px;margin-top: 240px"><label for="inwpnotifier_instock_sub_message"><?php esc_html_e('Instock SMS  Message', 'inwpnotifier'); ?> </label>
                    </span>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">

                    <span style="margin-left: 329px">
                        <textarea rows="6" type="text" id="inwpnotifier_instock_sub_message" name="inwpnotifier_instock_sub_message" class="regular-text"><?php echo $enable_instock_message; ?></textarea>


                    </span>
                </div>

                <br/>
                <hr/>
            </div>
            </div>
            <?php

    }

    public function inwwpnotifer_genarel_settings(){

            $frontent_form_title=get_option("inwpnotifier_frontent_form_title");
            $frontent_form_placeholder=get_option("inwpnotifier_frontent_form_placeholder");
            $frontent_form_button=get_option("inwpnotifer_frontent_form_button");

            ?>

            <div class="inwpnotifier_frontend_form_box" id="inwpnotifier_frontend_form_box" style="display: block">
                <div class="inwpnotifier_frontend_form_target_head"><?php esc_html_e('Frontend Form', 'inwpnotifier'); ?></div>
                <div class="inwpnotifier_gateway_box_close"></div>

                <div class="inwpnotifier_gateway_collapse_box">
                    <div class="inwpnotifier_gateway_for_list"><span
                                class="inwpnotifier_gate_for"><?php esc_html_e('Settings', 'inwpnotifier'); ?> </span><span
                                class="inwpnotifier_ctr_list"></span></div>
                             <div  class="icon-gear icon-gear-dims inwpnotifier_gateway_configure_gateway inwpnotifier_gateay_conf_expand"></div>
                </div>

               </div>


              <div class="inwpnotifier_frontend_form_shows">
              <div class="form-table inwpnotifier_frontend_form_show gateway_table">
                    <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row"  style="width: 30px;margin-left: 30px"><label for="inwpnotifier_frontent_form_title"><?php esc_html_e('Title Subscribe Form', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 150px;">
                        <input  type="text" id="inwpnotifier_frontent_form_title" name="inwpnotifier_frontent_form_title" class="regular-text"
                               value="<?php echo $frontent_form_title;?>"
                               placeholder="<?php esc_html_e('Title Subscribe Form', 'inwpnotifier'); ?>"
                               autocomplete="off"/>
                    </span>
                </div>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row" style="width: 30px; margin-left: 30px;"><label for="inwpnotifier_frontent_form_placeholder"><?php esc_html_e('Placeholder Subscribe Field', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 96px;">
                        <input type="text" id="inwpnotifier_frontent_form_placeholder" name="inwpnotifier_frontent_form_placeholder" class="regular-text"
                               value="<?php echo $frontent_form_placeholder;?>" autocomplete="off"
                               placeholder="<?php esc_html_e('Placeholder Subscribe Field', 'inwpnotifier'); ?>"/>
                    </span>
                </div>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row" style="width: 30px;margin-left: 30px">
                    <label for="inwpnotifer_frontent_form_button"><?php esc_html_e('Button Label', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 207px;">
                        <input type="text" id="inwpnotifer_frontent_form_button" name="inwpnotifer_frontent_form_button" class="regular-text"
                               value="<?php echo $frontent_form_button;?>" autocomplete="off"
                               placeholder="<?php esc_html_e('Button Label', 'inwpnotifier'); ?>"/>
                    </span>
                </div>
                <br/>
                <hr/>
            </div>
            </div>
    <?php
            $this->inwwpnotifer_message_settings();
    }
     public function inwwpnotifer_message_settings(){
            $inwpnotifier_success_subscription_message=get_option("inwpnotifier_success_subscription_message");
            $inwpnotifier_already_subscribed_message=get_option("inwpnotifier_already_subscribed_message");
            $inwpnotifier_field_empty_errors=get_option("inwpnotifier_field_empty_errors");


            ?>
             <div class="inwpnotifier_message_settings_box" id="inwpnotifier_message_settings_box" style="display: block">
                <div class="inwpnotifier_message_settings_target_head"><?php esc_html_e('Message Settings', 'inwpnotifier'); ?></div>
                <div class="inwpnotifier_gateway_box_close"></div>

                <div class="inwpnotifier_gateway_collapse_box">
                    <div class="inwpnotifier_gateway_for_list"><span
                                class="inwpnotifier_gate_for"><?php esc_html_e('Settings', 'inwpnotifier'); ?> </span><span
                                class="inwpnotifier_ctr_list"></span></div>
                             <div  class="icon-gear icon-gear-dims inwpnotifier_gateway_configure_gateway inwpnotifier_gateay_conf_expand"></div>
                        </div>

                     </div>


              <div class="inwpnotifier_message_setting_shows">
              <div class="form-table inwpnotifier_message_setting_show gateway_table">
                    <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row"  style="width: 30px;margin-left: 30px"><label for="inwpnotifier_success_subscription_message"><?php esc_html_e('Success Subscription Message', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 75px;">
                        <input  type="text" id="inwpnotifier_success_subscription_message" name="inwpnotifier_success_subscription_message" class="regular-text"
                               value="<?php echo $inwpnotifier_success_subscription_message; ?>"
                               placeholder="<?php esc_html_e('Success Subscription Message', 'inwpnotifier'); ?>"
                               autocomplete="off"/>
                    </span>
                </div>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row" style="width: 30px; margin-left: 30px;"><label for="inwpnotifier_already_subscribed_message"><?php esc_html_e('Already Subscribed Message', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 90px;">
                        <input type="text" id="inwpnotifier_already_subscribed_message" name="inwpnotifier_already_subscribed_message" class="regular-text"
                               value="<?php echo $inwpnotifier_already_subscribed_message;?>" autocomplete="off"
                               placeholder="<?php esc_html_e('Already Subscribed Message', 'inwpnotifier'); ?>"/>
                    </span>
                </div>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row" style="width: 30px;margin-left: 30px">
                    <label for="inwpnotifier_field_empty_errors"><?php esc_html_e('Field Empty Error Message', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 105px;">
                        <input type="text" id="inwpnotifier_field_empty_errors" name="inwpnotifier_field_empty_errors" class="regular-text"
                               value="<?php echo $inwpnotifier_field_empty_errors;?>" autocomplete="off"
                               placeholder="<?php esc_html_e('Field Empty Error Message', 'inwpnotifier'); ?>"/>
                    </span>
                </div>
                <br/>
                <hr/>
            </div>
            </div>
            <?php
    }
    public function inwwpnotifer_user_consent_settings(){
            ?>
               <div class="inwpnotifier_admin_head"><span><?php esc_html_e('Visibility Settings', 'inwpnotifier'); ?></span></div>
           <?php

           $this->inwwpnotifer_hide_subscrib_for_guests();
           $this->inwwpnotifer_hide_subscrib_for_members();
           $this->inwwpnotifer_Show_Subscribe_Form_on_Backorders();
           $this->inwwpnotifer_hide_Regular_Products_out_of_stock();
           $this->inwwpnotifer_hide_sale_product_ou_of_stock();
           $this->inwwpnotifer_ignore_disabled_variation();
           $this->inwwpnotifer_ignore_wc_out_stack_settings();

           ?>
           <?php
      }
      public function inwpnotifier_dokan_subscribers_perpage(){
            ?>
              <div class="inwpnotifier_admin_head"><span><?php esc_html_e('Dokan Settings', 'inwpnotifier'); ?></span></div>
           <?php
            $dokan_subsciber_per_page=get_option("inwpnotifir_dokan_subsciber_per_pages",5);

            ?>
                <label><?php _e("Dokan subscribers per page","inwwpnotifer"); ?></label>

                              <span style="margin-left: 280px;">
        <input  style="width: 140px" type="number" id="inwpnotifir_dokan_subsciber_per_page" name="inwpnotifir_dokan_subsciber_per_page" class="regular-text" value="<?php echo esc_attr($dokan_subsciber_per_page); ?>"/>

          </span>
                         <hr/>

        <?php
        }

      public function inwwpnotifer_ignore_wc_out_stack_settings(){
                            $ignore_wc_visibility_checked="";
                            $ignore_wc_visibility=get_option("inwpnotifier_ignore_wc_visibility");
                            if($ignore_wc_visibility==1){
                            $ignore_wc_visibility_checked="checked";
                            }
            ?>
                        <label><?php _e("Ignore WooCommerce Out of Stock Visibility Settings for Variation","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_ignore_wc_out_stack_settings" id="inwpnotifier_ignore_wc_out_stack_settings">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_ignore_wc_visibility"  class="inwpnotifier_email_toggle" id="inwpnotifier_ignore_wc_visibility" value="<?php echo $ignore_wc_visibility;?>" <?php echo $ignore_wc_visibility_checked; ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>

                          </span>
                         <hr/>
                         <?php
                    }
                    public function inwwpnotifer_ignore_disabled_variation(){
                           $ignore_disabled_variation_checked="";
                            $ignore_disabled_variation=get_option("inwpnotifier_ignore_disabled_variation");
                            if($ignore_disabled_variation==1){
                            $ignore_disabled_variation_checked="checked";
                            }

                     ?>
                         <label><?php _e("Don't overwrite disabled out of stock variations from theme configuration","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_form_theme_configurations" id="inwpnotifier_ignore_disabled_variations">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_ignore_disabled_variation"  class="inwpnotifier_email_toggle" id="inwpnotifier_ignore_disabled_variation" value="<?php echo $ignore_disabled_variation;?>" <?php echo $ignore_disabled_variation_checked; ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>

                         <hr/>
                         <?php
            }
         public function inwwpnotifer_hide_sale_product_ou_of_stock(){
                 $hide_sale_product_checked="";
                $hide_sale_product=get_option("inwpnotifier_hide_subscribe_sale_product");
                if($hide_sale_product==1){
                $hide_sale_product_checked="checked";
                }
            ?>
            <label><?php _e("Hide Subscribe Form on Sale Products out of stock","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_hide_subscribe_sale_products" id="inwpnotifier_hide_subscribe_sale_products">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_hide_subscribe_sale_product"  class="inwpnotifier_email_toggle" id="inwpnotifier_hide_subscribe_sale_product" value="<?php echo $hide_sale_product;?>" <?php echo $hide_sale_product_checked; ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>


                          </span>
                         <hr/>
                         <?php
                     }
                 public function inwwpnotifer_hide_Regular_Products_out_of_stock(){
                $hide_regular_product_checked="";
                $hide_regular_product=get_option("inwpnotifier_hide_subscribe_regular_product");
                if($hide_regular_product==1){
                $hide_regular_product_checked="checked";
            }
            ?>
            <label><?php _e("Hide Subscribe Form on Regular Products out of stock","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_hide_subscribe_regular_products" id="inwpnotifier_hide_subscribe_regular_products">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_hide_subscribe_regular_product"  class="inwpnotifier_email_toggle" id="inwpnotifier_hide_subscribe_regular_product" value="<?php echo $hide_regular_product;?>" <?php echo $hide_regular_product_checked ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>
                         <hr/>
                         <?php
    }
    public function inwwpnotifer_hide_subscrib_for_guests(){

            $hide_gu_checked= " ";
             $hide_guests_value=get_option("inwpnotifier_hide_sub_non_log");
             if($hide_guests_value == 1){
                 $hide_gu_checked= "checked";
             }

            ?>
                   <label><?php _e("Hide Subscribe Form for Guests ","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_hide_subscribe_guest" id="inwpnotifier_hide_subscribe_guest">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_hide_sub_non_log" <?php echo $hide_gu_checked; ?>  class="inwpnotifier_email_toggle" id="inwpnotifier_hide_sub_non_log" value="<?php echo $hide_guests_value;?>" >
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>
                         <hr/>

            <?php
    }
     public function inwwpnotifer_hide_subscrib_for_members(){
                $hide_member_checked='';
                $hide_member=get_option("inwpnotifier_hide_subscribe_loggedin");
                if($hide_member==1){
                    $hide_member_checked="checked";
                }
            ?>
                   <label><?php _e("Hide Subscribe Form for Members","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_hide_subscribe_members" id="inwpnotifier_hide_subscribe_members">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_hide_subscribe_loggedin"  class="inwpnotifier_email_toggle" id="inwpnotifier_hide_subscribe_loggedin" value="<?php echo $hide_member;?>" <?php echo $hide_member_checked; ?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>
                          <hr/>


            <?php
    }
           public function inwwpnotifer_Show_Subscribe_Form_on_Backorders(){
            $show_backorder_checked="";
            $show_backorder=get_option("inwpnotifier_show_subscribe_on_backorder");
            if($show_backorder==1){
                $show_backorder_checked="checked";
            }
            ?>
                   <label><?php _e("Show Subscribe Form on Backorders","inwwpnotifer"); ?></label>
                         <span class="inwpnotifier_show_subscribe_backorder" id="inwpnotifier_show_subscribe_backorder">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_show_subscribe_on_backorder"  class="inwpnotifier_email_toggle" id="inwpnotifier_show_subscribe_on_backorder" value="<?php echo $show_backorder;?>" <?php echo $show_backorder_checked;?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>
                         <hr/>
            <?php
    }


        public function inwwpnotifer_api_settings(){

            $this->inwpnotifier_whatsapp_settings();


    }

        public function inwpnotifier_whatsapp_settings(){
                $whatsapp_checked='';
                $whatsapp_instance_id=get_option("inwpnotfier_twilio_SID");
                $whatsapp_token=get_option("inwpnotifier_twilio_token");
                $whatsapp_senderNumber=get_option("inwpnotifier_twilio_senderNumber");
                $whatsapp_active=get_option("inwpnotifier_whatsapp_toggle");
                if($whatsapp_active==1){
                    $whatsapp_checked='checked';
                }
            ?>
             <div class="inwpnotifier_gateway_box" id="inwpnotifier_gateway_box" >
                <div class="inwpnotifier_gateway_target_head"><?php esc_html_e('Whatsapp', 'inwpnotifier'); ?></div>
                <div class="inwpnotifier_gateway_box_close"></div>

                <div class="inwpnotifier_gateway_collapse_box">
                    <div class="inwpnotifier_gateway_for_list"><span
                                class="inwpnotifier_gate_for"><?php esc_html_e('Twilio API', 'inwpnotifier'); ?> </span><span
                                class="inwpnotifier_ctr_list"></span></div>
                                 <div  class="icon-gear icon-gear-dims inwpnotifier_gateway_configure_gateway inwpnotifier_gateay_conf_expand"></div>
                </div>

                    <div class="inwpnotifier_whatsapp_minitoggle" id="inwpnotifier_whatsapp_minitoggle">
                    <label class="inwpnotifier_email_switch">
                 <input type="checkbox" name="inwpnotifier_whatsapp_toggle"  class="inwpnotifier_email_toggle" id="inwpnotifier_whatsapp_toggle" value="<?php echo $whatsapp_active;?>" <?php echo $whatsapp_checked;?>>
                <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                    </label>
                    </div>
             </div>


             <div class="inwpnotifier_TO_whatsapp_container">
            <div class="form-table inwpnotifier_TO_whatsapp_gateway gateway_table">
                    <br/>
                     <div class="chatapi gateway_conf">
                    <span scope="row"  style="margin-left: 30px"><label for="chatapi"><?php esc_html_e('Whatsapp API', 'inwpnotifier'); ?> </label>
                    </span>
                    <span scope="row"  style="margin-left: 270px;"><label for="chatapi" style="font-size: 18px"><?php esc_html_e('Twilio API', 'inwpnotifier'); ?> </label>
                </div>
                    <br/>
                <hr/>

                    <div class="chatapi gateway_conf" style="margin-top: 20px">
                    <span scope="row"  style="width: 30px;margin-left: 30px"><label for="inwp_twilio_SID"><?php esc_html_e('ACCOUNT SID', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 190px;">
                        <input  type="text" id="inwp_twilio_SID" name="inwpnotfier_twilio_SID" class="regular-text"
                               value="<?php echo $whatsapp_instance_id; ?>"
                               placeholder="<?php esc_html_e('ACCOUNT SID', 'inwpnotifier'); ?>"
                               autocomplete="off"/>
                    </span>
                </div>
                <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row" style="width: 30px; margin-left: 30px;"><label for="inwpnotifier_twilio_token"><?php esc_html_e('AUTH TOKEN', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 196px;">
                        <input type="text" id="inwpnotifier_twilio_token" name="inwpnotifier_twilio_token" class="regular-text"
                               value="<?php echo $whatsapp_token; ?>" autocomplete="off"
                               placeholder="<?php esc_html_e('AUTH TOKEN', 'inwpnotifier'); ?>"/>
                    </span>
                </div>
                 <div class="twiliocred gateway_conf" style="margin-top: 20px">
                    <span scope="row" style="width: 30px; margin-left: 30px;"><label for="inwpnotifier_senderNumber"><?php esc_html_e('SENDER NUMBER', 'inwpnotifier'); ?> </label>
                    </span>
                    <span style="margin-left: 158px;">
                        <input type="text" id="inwpnotifier_senderNumber" name="inwpnotifier_twilio_senderNumber" class="regular-text"
                               value="<?php echo $whatsapp_senderNumber; ?>" autocomplete="off"
                               placeholder="<?php esc_html_e('SENDER NUMBER', 'inwpnotifier'); ?>"/>
                    </span>
                </div>

                <br/>
                <hr/>
            </div>
            </div>

             <?php
        }


         public function inwwpnotifer_admin_header_logo()
         {
        ?>
        <a href="https://wppool.dev/" target="_blank">
            <img  height="10px" width="30px" src="<?php echo INSTOCKWPNOTIFIER_PLUGIN_URL.'assets/img/WPNotif.svg'?>"
                 alt=""/>
                 </a>
        <?php
         }
        public function inwpnotifier_Register_manage_settings() {
             register_setting('inwpnotifier_settings', 'inwpnotifiersettings', array($this, 'sanitize_data'));

            add_settings_section('inwpnotifier_section_visibility', __('Visibility  SP Settings', 'inwpnotifier'), array($this, 'visibility_section_heading'), 'inwpnotifier_settings');

            add_settings_field('inwpnotifier_visibility_products', __('Show/Hide Subscribe Form for specific products', 'inwpnotifier'), array($this, 'visibility_for_specific_products'), 'inwpnotifier_settings', 'inwpnotifier_section_visibility');
            add_settings_field('inwpnotifier_visibility_categories', __('Show/Hide Subscribe Form for specific categories', 'inwpnotifier'), array($this, 'visibility_for_specific_categories'), 'inwpnotifier_settings', 'inwpnotifier_section_visibility');
            add_settings_field('inwpnotifier_visibility_tags', __('Show/Hide Subscribe Form for specific tags', 'inwpnotifier'), array($this, 'visibility_for_specific_tags'), 'inwpnotifier_settings', 'inwpnotifier_section_visibility');

           
            do_action('inwpnotifier_register_settings');
            
        }
        public function visibility_section_heading() {
            _e("Visibility SP Settings for Subscriber Form Frontend", 'inwpnotifier');
           echo  "<hr/>";
        }

        public function visibility_for_specific_products() {
            $options = get_option('inwpnotifiersettings');
            ?>
            <select style="width:320px;" data-placeholder="<?php _e("Select Products", 'inwpnotifier'); ?>" data-allow_clear="true" tabindex="-1" aria-hidden="true" name="inwpnotifiersettings[specific_products][]" multiple="multiple" class="wc-product-search">
                <?php
                $current_v = isset($options['specific_products']) ? $options['specific_products'] : '';
                if (is_array($current_v) && !empty($current_v)) {
                    foreach ($current_v as $each_id) {
                        $product = wc_get_product($each_id);
                        if ($product) {
                            printf('<option value="%s"%s>%s</option>', $each_id, ' selected="selected"', wp_kses_post($product->get_formatted_name()));
                        }
                    }
                }
                ?>
            </select>
            <label><input type="radio" name="inwpnotifiersettings[specific_products_visibility]" <?php isset($options['specific_products_visibility']) ? checked($options['specific_products_visibility'], 1) : ''; ?> value="1"/> <?php _e('Show', 'inwpnotifier'); ?></label>
            <label><input type="radio" name="inwpnotifiersettings[specific_products_visibility]" <?php isset($options['specific_products_visibility']) ? checked($options['specific_products_visibility'], 2) : ''; ?> value="2"/> <?php _e('Hide', 'inwpnotifier'); ?></label>
            <p><i><?php _e("By Default this field will empty means subscribe form will shown to all out of stock products by default", 'inwpnotifier'); ?></i></p>
            <?php
        }

        public function visibility_for_specific_categories() {
            $options = get_option('inwpnotifiersettings');
            ?>
            <select style="width:320px;" data-placeholder="<?php _e("Select Categories", 'inwpnotifier'); ?>" data-allow_clear="true" name="inwpnotifiersettings[specific_categories][]" multiple="multiple" class="wc-category-search">
                <?php
                $current_v = isset($options['specific_categories']) ? $options['specific_categories'] : '';
                if (is_array($current_v) && !empty($current_v)) {
                    foreach ($current_v as $each_slug) {
                        $current_category = $each_slug ? get_term_by('slug', $each_slug, 'product_cat') : false;
                        if ($current_category) {
                            printf('<option value="%s"%s>%s</option>', $each_slug, ' selected="selected"', esc_html($current_category->name . "(" . $current_category->count . ")"));
                        }
                    }
                }
                ?>
            </select>
            <label><input type="radio" name="inwpnotifiersettings[specific_categories_visibility]" <?php isset($options['specific_categories_visibility']) ? checked($options['specific_categories_visibility'], 1) : ''; ?> value="1"/> <?php _e('Show', 'inwpnotifier'); ?></label>
            <label><input type="radio" name="inwpnotifiersettings[specific_categories_visibility]" <?php isset($options['specific_categories_visibility']) ? checked($options['specific_categories_visibility'], 2) : ''; ?> value="2"/> <?php _e('Hide', 'inwpnotifier'); ?></label>
            <p><i><?php _e("By Default this field will empty means subscribe form will shown to all out of stock products by default", 'inwpnotifier'); ?></i></p>
            <?php
        }

        public function visibility_for_specific_tags() {
            $options = get_option('inwpnotifiersettings');
            ?>
            <select style="width:320px;" data-placeholder="<?php _e("Select Product Tags", 'inwpnotifier'); ?>" data-allow_clear="true" name="inwpnotifiersettings[specific_tags][]" multiple="multiple" class="wc-tag-search">
                <?php
                $current_v = isset($options['specific_tags']) ? $options['specific_tags'] : '';
                if (is_array($current_v) && !empty($current_v)) {
                    foreach ($current_v as $each_slug) {
                        $current_category = $each_slug ? get_term_by('slug', $each_slug, 'product_tag') : false;
                        if ($current_category) {
                            printf('<option value="%s"%s>%s</option>', $each_slug, ' selected="selected"', esc_html($current_category->name . "(" . $current_category->count . ")"));
                        }
                    }
                }
                ?>
            </select>
            <label><input type="radio" name="inwpnotifiersettings[specific_tags_visibility]" <?php isset($options['specific_tags_visibility']) ? checked($options['specific_tags_visibility'], 1) : ''; ?> value="1"/> <?php _e('Show', 'inwpnotifier'); ?></label>
            <label><input type="radio" name="inwpnotifiersettings[specific_tags_visibility]" <?php isset($options['specific_tags_visibility']) ? checked($options['specific_tags_visibility'], 2) : ''; ?> value="2"/> <?php _e('Hide', 'inwpnotifier'); ?></label>
            <p><i><?php _e("By Default this field will empty means subscribe form will shown to all out of stock products by default", 'inwpnotifier'); ?></i></p>
            <?php
        }


        public function default_value() {
            //delete_option('cwginstocksettings');
            $success_subscribe_message = "Dear {subscriber_number}, <br/>"
                . "Thank you for subscribing to the #{product_name}. We will sms you once product back in stock";
            $instock_message = "Hello {whatsapp_number}, <br/>"
                . "Thanks for your patience and finally the wait is over! <br/> Your Subscribed Product {product_name} is now back in stock! We only have a limited amount of stock, and this sms is not a guarantee you'll get one, so hurry to be one of the lucky shoppers who do <br/> Add this product {product_name} directly to your cart <a href='{cart_link}'>{cart_link}</a>";
            $data = apply_filters('inwpnotifierstock_default_values', array(
                'form_title' => 'sms when stock available',
                'form_placeholder' => 'Your sms Address',
                'button_label' => 'Subscribe Now',
                'empty_error_message' => 'sms Address cannot be empty',
                'invalid_sms_error' => 'Please enter valid sms Address',
                'enable_success_sub_sms' => '1',
                'success_sub_subject' => 'You subscribed to {product_name} at {shopname}',
                'success_sub_message' => $success_subscribe_message,
                'enable_instock_sms' => '1',
                'instock_sms_subject' => 'Product {product_name} has back in stock',
                'instock_sms_message' => $instock_message,
                'success_subscription' => 'You have successfully subscribed, we will inform you when this product back in stock',
                'already_subscribed' => 'Seems like you have already subscribed to this product',
            ));

            if (is_array($data) && !empty($data)) {
                add_option('inwpnotifiersettings', $data);
            }
            $get_data = get_option('inwpnotifiersettings');

            if (!isset($get_data['specific_categories_visibility'])) {
                $get_data['specific_categories_visibility'] = '1';
                $get_data['specific_products_visibility'] = '1';
                update_option('inwpnotifiersettings', $get_data);
            }

            $get_data = get_option('inwpnotifiersettings');
            if (!isset($get_data['specific_tags_visibility'])) {
                $get_data['specific_tags_visibility'] = '1';
                update_option('inwpnotifiersettings', $get_data);
            }

            do_action('inwpnotifierinstock_settings_default');
        }
        public function sanitize_data($input) {
            $textarea_field = array('instock_sms_message', 'success_sub_message');
            if (is_array($input) && !empty($input)) {
                foreach ($input as $key => $value) {
                    if (!is_array($value)) {
                        if (in_array($key, $textarea_field)) {
                            $input[$key] = $this->api->sanitize_textarea_field($value);
                        } else {
                            $input[$key] = $this->api->sanitize_text_field($value);
                        }
                    }
                }
            }

            return $input;
        }

    }
    new InStock_WPNotifier_Setting_Sub_Menu();
}