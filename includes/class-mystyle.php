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
    private $wc;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        // Register hooks
        add_action( 'init', array( &$this, 'init' ) );
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
                //Run the upgrader
                MyStyle_Install::upgrade( MYSTYLE_VERSION, $data_version );
            }
        }
    }
    
    /**
     * Function that looks to see if any products are mystyle enabled.
     * @return boolean Returns true if at least one product is customizable.
     */
    public static function site_has_customizable_products() {
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
    public static function product_is_customizable( $product_id ) {
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
     * @return MyStyle Returns the singleton instance of
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
