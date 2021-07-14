<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 *  Dokan Dashboard Template
 *
 *  Dokan Main Dahsboard template for Fron-end
 *
 *  @since 2.4
 *
 *  @package dokan
 */
require_once "inwpnotifier_class_wp_list_table.php";
?>
<div class="dokan-dashboard-wrap" xmlns="http://www.w3.org/1999/html">
    <?php
        /**
         *  dokan_dashboard_content_before hook
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
    ?>

    <div class="dokan-dashboard-content">

        <?php
            /**
             *  dokan_dashboard_content_before hook
             *
             *  @hooked show_seller_dashboard_notice
             *
             *  @since 2.4
             */

            do_action( 'inwpnotfier_dokan_all_subscriber_content_inside_before' );
        $role = get_role( 'shop_manager' );
        if( '' != $role ) {
            $role->add_cap( 'manage_options' );
        }

        if(current_user_can('manage_options') || current_user_can("dokandar")){
            $current_user_id=get_current_user_id();
        }else{
            return false;
        }

        $get_inwpnotifier_on_off=get_option("inwpnotifier_dokan_notifier_on_off_$current_user_id");

        if($get_inwpnotifier_on_off==1 || $get_inwpnotifier_on_off=='1'){
                $checked='checked';
            }else{
                $checked='';
            }
        ?>

        <article class="help-content-area">
            <div class="inwpnotfier_ON_OFF_from">
            <form class="inwpnotifier_Active_form">
            <span style="color: #f05025"><?php _e("OFF","inwwpnotifier");?></span>/<span style="color: #4CAF50"><?php _e('ON',"inwpnotifier");?></span>
            <input type="hidden" name="inwpnotifier_shop_user_id" id="inwpnotifier_shop_user_id" value="<?php echo esc_attr($current_user_id);?>">
            <span class="inwpnotifier_ON_OFF_Settings" id="inwpnotifier_ON_OFF_Settings">
                            <label class="inwpnotifier_email_switch">
                             <input type="checkbox" name="inwpnotifier_ON_OFF_Setting"  class="inwpnotifier_email_toggle" id="inwpnotifier_ON_OFF_Setting" value="<?php echo esc_attr($get_inwpnotifier_on_off);?>" <?php echo $checked;?>>
                              <span class="inwpnotifier_slider inwpnotifier_slider_round"></span>
                             </label>
                          </span>

            <div class="inwpnotifier_line_break"></div>
            </form>
            </div>
            <?php

            if(isset($_GET['post_id']) || isset($_GET['delete_id'])){
                $delete_id=isset($_GET['delete_id'])?sanitize_text_field($_GET['delete_id']):0;
                $post_id=isset($_GET['post_id'])?sanitize_text_field($_GET['post_id']):0;

                if(!isset($_GET['inwp_nonce'])|| !wp_verify_nonce($_GET['inwp_nonce'],"inwpnotifier_nonce")){
                    wp_die("sorry you are not authorizid");
                }
                if(isset($_GET['action'])){
                    $action=$_GET['action'];
                   if ($action=='inwpnotifier-whatsapp'){
                        if($post_id) {
                            $inwpnotifier_api = new InStock_WPNotifier_API();
                            $inwpnotifier_api->inwpnotifier_manual_whatsapp_sms($post_id,$dokan=1);
                        }
                    }elseif ($action=='inwpnotifier_delete'){
                        if($delete_id) {
                            $inwpnotifier_api = new InStock_WPNotifier_API();
                            $inwp_success = $inwpnotifier_api->inwpnotifier_delete_subscribe($delete_id);

                        if($inwp_success){
                            ?>
                            <p class="inwpnotifier_delete"><?php echo "Delete Successfully!";?></p>
                                <?php
                        }
                    }
                    }
                }

            }


            $args = array(
                'post_type' => 'inwpnotifier',
                'post_status'=>'iwg_subscribed,iwg_smssent,iwg_smsnotsent,',
                'meta_key'  => 'inwpnotifier_product_upload_author',
                'meta_value'=>get_current_user_id()
            );
            $query = new WP_Query( $args );
//            print_r($query);
            $post_data=array();
            $i=0;
            while ($query->have_posts()){
                $query->the_post();

                $post_data[$i]['id']=get_the_ID();
                $post_data[$i]['phone']=get_the_title();
                $post_data[$i]['product']=get_the_ID();
                $post_data[$i]['status']=get_post_status();
                $post_data[$i]['date']=get_the_date().' at '.get_the_time();
                $i++;
            }


            wp_reset_query();

            $table_name=new Inwpnotifier_all_subscriber_Table();
            $table_name->set_data($post_data);

            $table_name->prepare_items();

            ?>
            <div class="wrap">
                <h6 class="inwpnotfier-all-subscriber"><?php _e('All subscriber',"inwpnotifier");?></h6>
                <form method="GET">

                        <?php

                         $table_name->display();
            ?>
                    <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
                    </form>
            </div>

        </article><!-- .dashboard-content-area -->

         <?php
            /**
             *  dokan_dashboard_content_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'inwpnotifier_dokan_dashboard_content_inside_after' );
        ?>


    </div><!-- .dokan-dashboard-content -->

    <?php
        /**
         *  dokan_dashboard_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'inwpnotifier_dokan_dashboard_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->
<?php
