<?php

/**
* Plugin Name: Woo Nehabi
* Plugin URI: https://nehabi.com/
* Description: Woo Nehabi Is a Addons for woocomerce that Help the user to generate apointment date with QR Code by Nehabi Teams Version One Plugin Development.
* Version: 1.0
* Requires at least: 3.6
* Requires PHP: 7.5
* Author: Ashewa Technology
* Author URI: https://ashewatechnology.com
* Text Domain: nehabi-homes
* Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;  
}


if( !class_exists( 'Woo_Nehabi' ) ){

    class Woo_Nehabi{

        public function __construct() {

           
            $this->define_constants(); 

         
            require_once( WOO_NEHABI_PATH . 'woopostype/class.woo-nehabi-cpt.php' );
            $woo_nehabi = New Woo_Nehabi_Post_Type();
 
            add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 999);
            register_activation_hook(__FILE__, array($this, 'create_appointments_table'));
             
           
            
            require_once( WOO_NEHABI_PATH  . 'views/class.woo-nehabi-page.php' );
            $nehabi_woo_order = new Woo_Nehabi_Appointment();;
 
        }

        public function register_scripts(){
           
            wp_register_script( 'bootstrap-js', WOO_NEHABI_URL. 'inc/bootstrap.min.js', array('jquery'), '4.3.1', true );
            wp_register_style( 'bootstrap-css', WOO_NEHABI_URL. 'inc/bootstrap.min.css', array(), '4.3.1', 'all' );
            wp_register_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
        }

       
        
 
           public  function create_appointments_table() {
                global $wpdb;
                $table_name = $wpdb->prefix . 'appointments';  

           
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE $table_name (
                    id INT NOT NULL AUTO_INCREMENT,
                    order_id INT NOT NULL,
                    customer_id INT NOT NULL,
                    customer_name VARCHAR(255) NOT NULL,
                    customer_email VARCHAR(255) NOT NULL,
                    appointment_date DATE NOT NULL,
                    qr_code_url VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);  
            }

        

          
        public function define_constants(){
             
            define ( 'WOO_NEHABI_PATH', plugin_dir_path( __FILE__ ) );
            define ( 'WOO_NEHABI_URL', plugin_dir_url( __FILE__ ) );
            define ( 'WOO_NEHABI_VERSION', '1.0.0' );     
        }

 
        
        public static function activate(){
            update_option('rewrite_rules', '' );
        }

                public static function deactivate(){
            unregister_post_type( 'woo-nehabi' );
            flush_rewrite_rules();
        }

        
        public static function uninstall(){

            delete_option('widget_ashewa-properties');

            $posts = get_posts(
                array(
                    'post_type' => 'woo-nehabi',
                    'number_posts' => -1,
                    'post_status' => 'any'
                )               
            );

            foreach( $posts as $post ){
                wp_delete_post( $post->ID, true );
            }

        }

  
}

if( class_exists( 'Woo_Nehabi' ) ){
    
    register_activation_hook( __FILE__, array( 'Woo_Nehabi', 'activate'));
    register_deactivation_hook( __FILE__, array( 'Woo_Nehabi', 'deactivate'));
    register_uninstall_hook( __FILE__, array( 'Woo_Nehabi', 'uninstall' ) );

    $ashewa_homes= new Woo_Nehabi();
} 
}