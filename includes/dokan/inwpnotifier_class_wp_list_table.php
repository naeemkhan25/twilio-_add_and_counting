<?php
/**
 * WP_list_table show all subscriber
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'WP_List_Table' ) ) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
    require_once(ABSPATH . 'wp-admin/includes/template.php');
}


class Inwpnotifier_all_subscriber_Table extends WP_List_Table{
    private $_items;
    function set_data($data){
        $this->_items=$data;

    }
    function get_columns()
    {
        return [
            'cb'        => '<input type="checkbox" />',
            'phone'=>__("Number",'inwpnotifier'),
            'status'=>__("Post Status","inwpnotifier"),
            'product'=>__("Product",'inwpnotifier'),
            'date'=>__("Date",'inwpnotifier')
        ];
    }
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="inwp[]" value="%s"/>',
            $item['id']
        );
    }

    public function column_product($item){
        $post_id=$item['product'];
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
            $permalink = " <p>#{$pid } {$product_name}</p>";
            echo $permalink;
        }
    }
    public function column_phone($item){
        $post_id=$item['id'];

            $action = 'inwpnotifier-whatsapp';


        $nonce     = wp_create_nonce( 'inwpnotifier_nonce');
        $actions=[
            'instock_sms'=>sprintf('<a href="?post_id=%s&inwp_nonce=%s&action=%s">%s</a>',$post_id,$nonce,$action,__("instock sms",'inwpnotifier')),
            'delete'=>sprintf('<a href="?delete_id=%s&inwp_nonce=%s&action=%s">%s</a>',$post_id,$nonce,'inwpnotifier_delete',__("Delete",'inwpnotifier')),

        ];
        return sprintf("%s %s",$item['phone'],$this->row_actions($actions));
    }
    public function column_status($item){
        $get_post_status=$item['status'];
        $inwpnotfier_api=new InStock_WPNotifier_API();
        $inwpnotfier_api->inwpnotifier_display_status($get_post_status);
    }
    public function column_date($item)
    {
        $date=$item['date'];
      echo $date;
    }

    function column_default($item,$column_name)
    {
        return $item[$column_name];
    }
//    function get_bulk_actions() {
//        $actions = array(
//            'delete'    => 'Delete'
//        );
//        return $actions;
//    }

    function prepare_items()
    {


        if(!isset($_REQUEST['paged'])) {
            $_REQUEST['paged'] = explode('/page/', $_SERVER['REQUEST_URI'], 2);
            if(isset($_REQUEST['paged'][1])) list($_REQUEST['paged'],) = explode('/', $_REQUEST['paged'][1], 2);
            if(isset($_REQUEST['paged']) and $_REQUEST['paged'] != '') {
                $_REQUEST['paged'] = intval($_REQUEST['paged']);
                if($_REQUEST['paged'] < 2) $_REQUEST['paged'] = 1;
            } else {
                $_REQUEST['paged'] =1;
            }
        }

        $paged=$_REQUEST["paged"]??1;
        $total_items=count($this->_items);
        $per_page=get_option('inwpnotifir_dokan_subsciber_per_pages');
//        if($this->_items)
        if($total_items!=0) {
            $data_chunk = array_chunk($this->_items, $per_page);

            $this->items = $data_chunk[$paged - 1];
            $primary = 'cb';
            $this->_column_headers = array($this->get_columns(), [], [], $primary);
            if($per_page<$total_items) {
                $this->set_pagination_args([
                    'total_items' => $total_items,
                    'per_page' => $per_page,
                    'total_pages' => ceil(count($this->_items) / $per_page)
                ]);
            }
        }

    }


}
