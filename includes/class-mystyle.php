<?php

/**
 * MyStyle Main class. 
 * 
 * The MyStyle class sets up and controls the MyStyle plugin for features used
 * by both the frontend and the admin.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle {
    
    public static $STANDARD_DATE_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * Singleton class instance
     * @var MyStyle
     */
    private static $instance;
    
    /**
     * Our WooCommerce interface.
     * @var MyStyle_WC_Interface
     */
    private static $wc;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        // Register hooks
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'wp_login', array( &$this, 'on_wp_login' ), 10, 2 );
        add_action( 'user_register', array( &$this, 'on_user_register' ), 10, 1 );
        add_action( 'woocommerce_created_customer', array( &$this, 'on_woocommerce_created_customer' ), 10, 3 );
    }
    
    /**
     * Init the MyStyle interface.
     * @todo Add unit testing for this function.
     */
    public function init() {
        // Set the current version and handle any updates
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $data_version = ( array_key_exists( 'version', $options ) ) ? $options['version'] : null;
        if( $data_version != MYSTYLE_VERSION ) {
            $options['version'] = MYSTYLE_VERSION;
            update_option( MYSTYLE_OPTIONS_NAME, $options );
            if( ! is_null( $data_version ) ) {  //skip if not an upgrade
                
                //Delta the database tables
                MyStyle_Install::delta_tables();

                //Add the Design page if upgrading from less than 1.4.0 (versions that were before this page existed)
                //Changed to v1.4.1 (with exists check) because 1.4.0 wasn't working properly
                if( version_compare( $data_version, '1.4.1', '<' ) ) {
                    if( ! MyStyle_Design_Profile_Page::exists() ) {
                        MyStyle_Design_Profile_Page::create();
                    }
                }
                
                $upgrade_notice = MyStyle_Notice::create( 'notify_upgrade', 'Upgraded version from ' . $data_version . ' to ' . MYSTYLE_VERSION . '.' );
                mystyle_notice_add_to_queue( $upgrade_notice );
            }
        }
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
     * Function that looks to see if any products are mystyle enabled.
     * @return boolean Returns true if at least one product is customizable.
     */
    public function site_has_customizable_products() {
        $args = array(
                    'post_type'      => 'product',
                    'numberposts' => 1,
                    'meta_key'       => '_mystyle_enabled',
                    'meta_value'     => 'yes',
                );

        $customizable_products = get_posts( $args );
        
        if( ! empty( $customizable_products ) ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Function that looks to see if passed products is mystyle enabled.
     * @param integer $product_id The id of the product to check.
     * @return boolean Returns true if the product is customizable, otherwise,
     * returns false.
     */
    public function product_is_customizable( $product_id ) {
        $mystyle_enabled = get_post_meta( $product_id, '_mystyle_enabled', true );
        
        if( $mystyle_enabled == 'yes' ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Sets the WooCommerce interface.
     * @param MyStyle_WC_Interface $mystyle_wc_interface The WooCommerce 
     * interface.
     */
    public function set_WC( MyStyle_WC_Interface $mystyle_wc_interface ) {
        $this->wc = $mystyle_wc_interface;
    }
    
    /**
     * Gets the WooCommerce interface.
     * @return MyStyle_WC_Interface Returns the value of template_id.
     */
    public function get_WC() {
        return $this->wc;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Customize_Page Returns the singleton instance of
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
