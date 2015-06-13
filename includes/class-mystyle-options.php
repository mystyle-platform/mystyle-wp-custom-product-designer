<?php

/**
 * MyStyle Options class. 
 * 
 * The MyStyle Options class includes functions for setting and getting MyStyle
 * Options.
 *
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_Options {
    
   /**
    * Function that looks to see if an mystyle API Key and secret have been 
    * installed.
    * @return boolean Returns true if an API Key and Secret are installed,
    * otherwise returns false.
    */
    static function are_keys_installed() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );

        if( ( ! empty( $options['api_key'] ) ) && ( ! empty( $options['secret'] ) ) ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Function that gets the active api_key
     * @return string Returns the active api key.
     */
    static function get_api_key() {
        $api_key = null;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( ! empty( $options['api_key'] ) ) {
            $api_key = $options['api_key'];
        }
        if( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
            $api_key = MYSTYLE_OVERRIDE_API_KEY;
        }

        return $api_key;
    }

    /**
     * Function that gets the active secret
     * @return string Returns the active secret.
     */
    static function get_secret() {
        $secret = null;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( ! empty( $options['secret'] ) ) {
            $secret = $options['secret'];
        }
        if( defined( 'MYSTYLE_OVERRIDE_SECRET' ) ) {
            $secret = MYSTYLE_OVERRIDE_SECRET;
        }

        return $secret;
    }
    
    /**
     * Function that determines if the plugin is in demo mode.
     * @return string Returns the active secret.
     */
    static function is_demo_mode() {
        $api_key = self::get_api_key();
        $demo_key = 74;
        
        $ret = ($api_key == $demo_key);

        return $ret;
    }

}
