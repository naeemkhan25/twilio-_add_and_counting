<?php
/**
 * add_submenu in the dokan frontend dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if(!class_exists('inwpnotifier_dokan_all_subscriber_menu')){
    class inwpnotifier_dokan_all_subscriber_menu{
        public function __construct(){
            add_filter('dokan_query_var_filter', array($this,'inwpnotfier_dokan_load_document_menu'));
            add_filter('dokan_get_dashboard_nav', array($this,'inwpnotfier_dokan_add_subscriber_menu'));
            add_action('dokan_load_custom_template', array($this,'inwpnotifer_dokan_load_template'));
        }


        function inwpnotfier_dokan_load_document_menu($query_vars)
        {
            $query_vars['inwpnotifier'] = 'inwpnotifier';
            return $query_vars;
        }


        function inwpnotfier_dokan_add_subscriber_menu($urls)
        {
            $urls['inwpnotifier'] = array(
                'title' => __('Instock Notifer', 'inwpnotifier'),
                'icon' => '<i class="fa fa-whatsapp"></i>',
                'url' => dokan_get_navigation_url('inwpnotifier'),
                'pos' => 51
            );
            return $urls;
        }


        function inwpnotifer_dokan_load_template($query_vars)
        {
            if (isset($query_vars['inwpnotifier'])) {
                require_once dirname(__FILE__) . '/inwpnotifier_all_subscriber_template.php';

            }

        }

    }
    new inwpnotifier_dokan_all_subscriber_menu();
}