<?php

/**
 * MyStyle_User_Interface class. 
 * 
 * The MyStyle_User_Interface class has hooks for controlling the WordPress and
 * WooCommerce user interface.
 *
 * @package MyStyle
 * @since 1.5.0
 */
class MyStyle_User_Interface {
    
    /**
     * Singleton class instance
     * @var MyStyle_User_Interface
     */
    private static $instance;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        // Register hooks
        add_action( 'wp_login', array( &$this, 'on_wp_login' ), 10, 2 );
        add_action( 'user_register', array( &$this, 'on_user_register' ), 10, 1 );
        add_action( 'woocommerce_created_customer', array( &$this, 'on_woocommerce_created_customer' ), 10, 3 );
    }
    
    /**
     * Called when a user logs in.
     * @param string $user_login
     * @param WP_User $user
     */
    public function on_wp_login( $user_login, $user ) {
        $session = MyStyle_SessionHandler::get();
        MyStyle_DesignManager::set_user_id( $user, $session );
    }
    
    /**
     * Called when a user registers.
     * @param integer $user_id
     */
    public function on_user_register( $user_id ) {
        $session = MyStyle_SessionHandler::get();
        $user = get_user_by( 'id', $user_id );
        MyStyle_DesignManager::set_user_id( $user, $session );
    }
    
    /**
     * Called when woocommerce creates a user (for instance when the user checks
     * out).
     * @param integer $user_id
     */
    public function on_woocommerce_created_customer( $customer_id, $new_customer_data, $password_generated ) {
        $session = MyStyle_SessionHandler::get();
        $user = get_user_by( 'id', $customer_id );
        MyStyle_DesignManager::set_user_id( $user, $session );
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_User_Interface Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}
