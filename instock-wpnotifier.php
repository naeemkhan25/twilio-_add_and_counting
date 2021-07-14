<?php
/**
 * Plugin Name: WhatsApp In Stock Notifier for WooCommerce and Multivendor
 * Plugin URI:  https://wppool.dev/
 * Description: Woocommerce plugin using which a customer can subscribe for interest on an out of stock product. When the product becomes available, subscribed customer will get an alert Whatsapp & Phone.
 * Version: 1.0
 * Author: WPPOOL
 * Author URI:http://wppool.dev
 * Text Domain:inwpnotifier
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // avoid direct access to the file
}

if ( ! class_exists( "InStock_WPNotifier" ) ) {

	final class  InStock_WPNotifier {
		/**
		 * @var use for debug console
		 */


		const version = '1.0';

		private function __construct() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			//use for woocommerce dependence active check

			$this->InStock_WPNotifier_avoid_header_sent();

			$this->InStock_WPNotifier_define_constants();
			$this->InStock_WPNotifier_include_files();
			add_action( "admin_enqueue_scripts", [ $this, "admin_enqueue_scripts" ] );
			add_action( "inwpnotifier_settings_admin_enqueue", [
				$this,
				"inwpnotifier_settings_admin_enqueue_scripts"
			] );
			add_action( "wp_enqueue_scripts", [ $this, "frontend_enqueue_script" ] );
			add_filter( 'woocommerce_screen_ids', array( $this, 'InStock_WPNotifier_add_screen_ids_to_woocommerce' ) );
			add_action( 'plugins_loaded', array( $this, 'InStock_WPNotifier_load_plugin_textdomain' ) );
			add_action( 'admin_head', array( $this, "remove_help_tab_context_plugin" ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
				$this,
				'InStock_WPNotifier_settings'
			) );

		}


		function InStock_WPNotifier_settings( $links ) {
			$plugin_links = array( '<a href="' . admin_url( 'edit.php?post_type=inwpnotifier&page=instock-wpNotifier_special' ) . '">' . __( 'Settings', 'inwpnotifier' ) . '</a>' );

			return array_merge( $plugin_links, $links );
		}
		/**
		 * Woocommerce Inactive Notice
		 */
		public function InStock_WPNotifier_woocommerce_inactive_notice() {
			?>
            <div id="message" class="error">
                <p><?php printf( __( '%s InStock WPNotifier  is inactive.%s The %sWooCommerce plugin%s must be active for the InStock WPNotifier to work. Please %sinstall & activate WooCommerce%s', 'inwpnotifier' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
            </div>
			<?php
		}


		/**
		 * register screen ids for use condition check load js or css.
		 *
		 * @param $screen_ids
		 *
		 * @return mixed
		 */
		public function InStock_WPNotifier_add_screen_ids_to_woocommerce( $screen_ids ) {
//          global $screen;
//         print_r(get_current_screen());
//         die();
			$screen_ids[] = 'inwpnotifier_page_instock-wpNotifier';
			$screen_ids[] = 'edit-inwpnotifier';
			$screen_ids[] = 'inwpnotifier_page_instock-wpNotifier_special';

			return $screen_ids;

		}

		/**
		 * initializes singleton instance.
		 */
		public static function init() {
			static $instance = false;
			if ( ! $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * Avoid Header already sent issue
		 */
		public function InStock_WPNotifier_avoid_header_sent() {
			ob_start();
		}

		/**
		 *
		 * Include necessary files to load
		 */
		public function InStock_WPNotifier_include_files()
        {
            require_once "includes/admin/class-cptui-all-subscribers.php";
            require_once "includes/class-instock-wpnotifier-api.php";
            require_once "includes/admin/class-setting-sub-menu.php";
            require_once 'includes/class-instock-wpnotifier-ajax.php';
            require_once "includes/user_profile/class-instock-wpnotifier-whatsapp-number.php";
            require_once "includes/frontend/class-instock-wpnotifier-product.php";
            require_once "includes/class-instok_wpnotifier_logger.php";
            require_once "includes/class-instock-wpnotifier-subscribe-sms.php";
            require_once "includes/class-instock-wpnotifier-instock-sms.php";
            require_once "includes/libary/WPNotifier_Persistent_Notices.php";
            require_once "includes/instock-wpnotifier-core-functions.php";
            require_once "includes/class-instock-wpnotifer-api-ajax.php";

//			if ( is_plugin_active( 'dokan-lite/dokan.php' )|| is_plugin_active_for_network( 'dokan-lite/dokan.php' ) ) {
            require_once "includes/dokan/inwpnotifier_dokan_dashboard_add_menu.php";
            require_once "includes/dokan/inwpnotifier_dokan_ajax.php";

//        }

		}

		/**
		 * loaded plugin text domain for do not conflict another plugin.
		 * @return bool
		 */
		public function InStock_WPNotifier_load_plugin_textdomain() {
			$domain = 'inwpnotifier';
			$dir    = untrailingslashit( WP_LANG_DIR );
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
			if ( $exists = load_textdomain( $domain, $dir . '/plugins/' . $domain . '-' . $locale . '.mo' ) ) {
				return $exists;
			} else {
				load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * define constance for any  position are usages.
		 */
		public function InStock_WPNotifier_define_constants() {
			$this->define( "INSTOCKWPNOTIFIER_VERSION", self::version );
			$this->define( "INSTOCKWPNOTIFIER_DIR_FILE", __FILE__ );
			$this->define( 'INSTOCKWPNOTIFIER_DIRNAME', basename( dirname( __FILE__ ) ) );
			$this->define( "INSTOCKWPNOTIFIER_DIR", __DIR__ );
			$this->define( "INSTOCKWPNOTIFIER_PLUGIN_URL", plugin_dir_url( __FILE__ ) );
			$this->define( "INSTOCKWPNOTIFIER_PLUGIN_PATH", plugin_dir_path( __FILE__ ) );
			$this->define( "INSTOCKWPNOTIFIER_ASSETS", INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets' );
		}

		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		public function check_script_is_already_load( $handle, $list = 'enqueued' ) {
			return wp_script_is( $handle, $list );
		}

		public function frontend_enqueue_script() {
			wp_register_script( 'inwpnotifier_jquery_validation', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/js/jquery.validate.js', array( 'jquery' ), time(), false );
			$check_already_enqueued = $this->check_script_is_already_load( 'jquery-blockui' );
			if ( ! $check_already_enqueued ) {
				wp_register_script( 'jquery-blockui', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/js/jquery.blockUI.js', array( 'jquery' ), time(), false );
			}
			wp_register_script( 'inwpnotifier_js', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/js/frontend.js', array(
				'jquery',
				'jquery-blockui'
			), time(), false );

			wp_register_style( 'inwpnotifier_frontend_css', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/css/frontend.css' );
			wp_register_style( 'inwpnotifier_intlTrlInput_css', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/css/intlTelInput.css' );
			wp_register_style( 'inwpnotifier_isValidNumber', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/css/isValidNumber.css' );
			wp_register_script( 'inwpnotifier_intlTrlInput_js', INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/js/intlTelInput.js', array( 'jquery' ), time(), false );


			$get_hide_form_guests=get_option("inwpnotifier_hide_sub_non_log");
			$check_wp_visibility = isset($get_hide_form_guests ) && !empty($get_hide_form_guests) && ! is_user_logged_in() ? false : true;
			if ( $check_wp_visibility ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-blockui' );
				wp_enqueue_style( 'inwpnotifier_frontend_css' );
//
				wp_enqueue_style( "inwpnotifier_intlTrlInput_css" );
				wp_enqueue_style( "inwpnotifier_isValidNumber" );
				$get_empty_error_message=get_option("inwpnotifier_field_empty_errors");
				$get_wp_empty_msg  = isset( $get_empty_error_message ) && $get_empty_error_message != '' ? $get_empty_error_message : __( 'Whatsapp Number cannot be empty', 'inwpnotifier' );
				$translation_array = apply_filters( 'inwpnotifier_localization_array', array(
					'ajax_url'       => admin_url( 'admin-ajax.php' ),
					'security'       => wp_create_nonce( 'inwpnotifier_subscribe_product' ),
					'security_five'=> wp_create_nonce( 'inwpnotifier_vendor_notifier_on_off'),
					'user_id'        => get_current_user_id(),
					'security_error' => __( "Something went wrong, please try after sometime", 'inwpnotifier' ),
					'empty_phone'    => $get_wp_empty_msg,
					'plugin_urls'    => INSTOCKWPNOTIFIER_PLUGIN_URL
				) );
				wp_enqueue_script( 'inwpnotifier_intlTrlInput_js' );
				wp_localize_script( 'inwpnotifier_js', 'inwpnotifier', $translation_array );
				wp_enqueue_script( 'inwpnotifier_js' );
			}
			wp_enqueue_style('inwpnotifier_dokan_css',INSTOCKWPNOTIFIER_PLUGIN_URL.'/assets/css/dokan.css');

		}

		public function admin_enqueue_scripts( $hook ) {
            wp_enqueue_style( 'inwpnotifier_menuicon_css', INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/css/menu_icon.css' );

            $screen = get_current_screen();
			if ( $screen->id == 'edit-inwpnotifier' || $screen->id == 'inwpnotifier_page_instock-wpNotifier' ) {
				wp_enqueue_style( 'inwpnotifier_admin_css', INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/css/admin.css' );

				wp_register_script( 'inwpnotifier_admin_js', INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/js/admin.js', array(
					'jquery',
					'wc-enhanced-select'
				), INSTOCKWPNOTIFIER_VERSION );
				wp_localize_script( 'inwpnotifier_admin_js', 'incwp_enhanced_selected_params', array( 'search_tags_nonce' => wp_create_nonce( 'search-tags' ) ) );
				wp_enqueue_script( 'inwpnotifier_admin_js' );


			}
			if ( $screen->id == 'inwpnotifier_page_instock-wpNotifier_special' ) {

				do_action( "inwpnotifier_settings_admin_enqueue" );
			}


			wp_register_style( "inwpnotifier_intlTellnput", INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/css/intlTelInput.css' );
			wp_register_style( "inwpnotifier_isValidenumber_css", INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/css/isValidNumber.css' );
			wp_register_script( "inwpnotifier_intlTellnput_js", INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/js/intlTelInput.js', array( "jquery" ), time(), true );
			wp_register_script( "inwpnotifier_isValidNumber_js", INSTOCKWPNOTIFIER_PLUGIN_URL . 'assets/js/isValidNumber.js', array( "jquery" ), time(), true );
			if ( 'profile.php' == $hook || 'user-edit.php'==$hook ) {
				wp_enqueue_style( 'inwpnotifier_intlTellnput' );
				wp_enqueue_style( 'inwpnotifier_isValidenumber_css' );
				wp_enqueue_script( 'inwpnotifier_intlTellnput_js' );
				wp_localize_script( 'inwpnotifier_isValidNumber_js', 'isValidnumber', array( 'plugin_Urls' => INSTOCKWPNOTIFIER_PLUGIN_URL ) );
				wp_enqueue_script( 'inwpnotifier_isValidNumber_js' );
			}


		}

		public function inwpnotifier_settings_admin_enqueue_scripts() {

			wp_enqueue_style( 'inwpnotifier_settings_css', INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/css/settings.css' );

			if ( is_rtl() ) {
				wp_enqueue_style( 'inwpnotifier_rtl_settings_css', INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/css/settings-rtl.css' );
			}
            wp_enqueue_script( 'inwpnotifier_settings_js', INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/js/settings.js', array( "jquery" ), INSTOCKWPNOTIFIER_VERSION, true );
            wp_register_script("inwpnotifier_api_js",INSTOCKWPNOTIFIER_PLUGIN_URL . '/assets/js/api.js',array('jquery'),INSTOCKWPNOTIFIER_VERSION,true);

           $inwpnotfier_api_on_of=array(
               'ajax_url'=> admin_url( 'admin-ajax.php' ),
               'security'=> wp_create_nonce( 'inwpnotifier_api_on_of')
           );
            wp_localize_script("inwpnotifier_api_js","inwpnotifier_api_on_of",$inwpnotfier_api_on_of);
            wp_enqueue_script("inwpnotifier_api_js");
		}


		/**
		 * hide help context tab
		 */

		public function remove_help_tab_context_plugin() {
			$screen = get_current_screen();
			if ( $screen->id == 'edit-inwpnotifier' || $screen->id == 'inwpnotifier_page_instock-wpNotifier' ) {
				$screen->remove_help_tabs();
			}
		}
	}

	/**
	 * initializes the main plugin
	 *
	 * return \Stock_Notifier class.
	 */

	function inwpnotifier_InStock_WPNotifier() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( inwpnotifier_is_woocommerce_activated() ) {
			return InStock_WPNotifier::init();
		}else{
		    return false;
        }

	}

	if ( ! function_exists( 'inwpnotifier_is_woocommerce_activated' ) ) {

		function inwpnotifier_is_woocommerce_activated() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				return true;
			} elseif ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				return true;
			} else {
				return false;
			}
		}

	}

	/**
	 * kick-of the plugin
	 */
	if(inwpnotifier_InStock_WPNotifier()){
	    inwpnotifier_InStock_WPNotifier();
    }else{
	    add_action("admin_notices","inwpnotifier_woocommerce_not_active");
	    function inwpnotifier_woocommerce_not_active(){
	        ?>
	        <div class="notice notice-warning is-dismissible">
                <p><?php printf(__('%sWhatsApp In Stock Notifier for WooCommerce and Multivendor is inactive.%s The %sWooCommerce plugin%s must be active for the %sWhatsApp In Stock Notifier for WooCommerce and Multivendor %s to work . Please %sinstall & activate WooCommerce%s', "inwpnotifier"), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>','<strong>', '</strong>', '<a href="' . admin_url('plugins.php') . '">', '&nbsp;&raquo;</a>'); ?></p>
         </div>
      <?php
    }
}
}