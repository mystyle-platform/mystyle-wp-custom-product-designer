<?php

/**
 * MyStyle FrontEnd class.
 * The MyStyle FrontEnd class sets up and controls the MyStyle front end
 * interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_FrontEnd {
    
    /**
     * Singleton class instance
     * @var MyStyle_Frontend
     */
    private static $instance;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_filter( 'query_vars', array( &$this, 'add_query_vars_filter' ), 10, 1 );
        
        add_action( 'init', array( &$this, 'init' ) );
    }
    
    /**
     * Init the MyStyle front end.
     */
    public function init() {
        //Add the MyStyle frontend stylesheet to the WP frontend head
        wp_register_style( 'myStyleFrontendStylesheet', MYSTYLE_ASSETS_URL . 'css/frontend.css' );
        wp_enqueue_style( 'myStyleFrontendStylesheet' );

        //Add the WordPress Dashicons icon font to the frontend.
        wp_enqueue_style( 'dashicons' );
        
        //Add the swfobject.js file to the WP head
        wp_register_script( 'swfobject', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js' );
        wp_enqueue_script( 'swfobject' );
    }
    
    /**
     * Add design_id as a custom query var.
     * @param array $vars
     * @return string
     */
    public function add_query_vars_filter( $vars ){
        $vars[] = 'design_id';
        
        return $vars;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Design_Profile_Page Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle_Design_Profile_Page Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}

