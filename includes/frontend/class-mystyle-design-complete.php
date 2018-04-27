<?php

/**
 * MyStyle_Design_Complete class.
 * The MyStyle_Design_Complete class has hooks for working with what happens
 * after a design is completed.
 *
 * @package MyStyle
 * @since 3.4.0
 */
class MyStyle_Design_Complete {
    
    /**
     * Singleton class instance
     * @var MyStyle_Design_Complete
     */
    private static $instance;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_filter( 'query_vars', array( &$this, 'add_query_vars_filter' ), 10, 1 );
        
        // Hook template_redirect instead of init to wait for query vars.
        add_action( 'template_redirect', array( &$this, 'init' ), 10, 0 );
    }
    
    /**
     * Init hooks.
     * 
     * This function is being hooked into "template_redirect" instead of "init"
     * because we want to wait until the query vars have been loaded.
     */
    public function init() {
        $design_complete = ( intval( get_query_var( 'design_complete', '0' ) ) == 1 ) ? true : false;
        
        if( $design_complete ) {
            wp_register_script( 'mystyle-design-complete', MYSTYLE_ASSETS_URL . 'js/design-complete.js' );
            wp_enqueue_script( 'mystyle-design-complete' );
        }
    }
    
    /**
     * Add design_complete as a custom query var.
     * @param array $vars
     * @return string
     */
    public function add_query_vars_filter( $vars ){
        $vars[] = 'design_complete';
        
        return $vars;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Cart Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle_Design_Complete Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}

