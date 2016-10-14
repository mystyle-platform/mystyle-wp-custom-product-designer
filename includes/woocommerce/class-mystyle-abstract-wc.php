<?php

/**
 * MyStyle_AbstractWC class. 
 * 
 * Abstract class for facilitating interactions with WooCommerce.
 * 
 * The abstract class, interface, etc is primarily used for testing purposes.
 *
 * @package MyStyle
 * @since 1.5.0
 */
abstract class MyStyle_AbstractWC {
    
    /**
     * Singleton class instance
     */
    private static $instance;
    
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}
