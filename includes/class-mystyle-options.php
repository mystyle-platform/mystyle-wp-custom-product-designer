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
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());

        if ( ( ! empty($options['api_key'] ) ) && ( ! empty($options['secret'] ) ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function that gets the active api_key.
     * @return string Returns the active api key.
     */
    static function get_api_key() {
        $api_key = null;
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        if ( ! empty($options['api_key'] ) ) {
            $api_key = $options['api_key'];
        }
        if ( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
            $api_key = MYSTYLE_OVERRIDE_API_KEY;
        }

        return $api_key;
    }

    /**
     * Function that gets the active secret.
     * @return string Returns the active secret.
     */
    static function get_secret() {
        $secret = null;
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        if ( ! empty( $options['secret'] ) ) {
            $secret = $options['secret'];
        }
        if ( defined( 'MYSTYLE_OVERRIDE_SECRET' ) ) {
            $secret = MYSTYLE_OVERRIDE_SECRET;
        }

        return $secret;
    }

    /**
     * Function that gets the value of enable_flash setting.
     * @return boolean Returns 1 if the enable_flash setting is enabled,
     * otherwise returns false.
     */
    static function get_enable_flash() {
        $enable_flash = 0;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array());
        if ( ! empty( $options['enable_flash'] ) ) {
            $enable_flash = $options['enable_flash'];
        }

        return $enable_flash;
    }

    /**
     * Function that gets the value of the customize_page_title_hide setting.
     * @return boolean Returns 1 if the customize_page_title_hide setting is enabled,
     * otherwise returns false.
     */
    static function get_customize_page_title_hide() {
        $customize_page_title_hide = 0;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if ( ! empty( $options['customize_page_title_hide'] ) ) {
            $customize_page_title_hide = $options['customize_page_title_hide'];
        }
        
        return $customize_page_title_hide;
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
    
    /**
     * Function that gets the enable_alternate_design_complete_redirect option.
     * @return boolean Returns true if the 
     * enable_alternate_design_complete_redirect setting is enabled, otherwise
     * returns false.
     */
    static function enable_alternate_design_complete_redirect() {
        $val = 0;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if ( ! empty( $options['enable_alternate_design_complete_redirect'] ) ) {
            $val = $options['enable_alternate_design_complete_redirect'];
        }
        
        //convert to true boolean
        if($val == 1 ) {
            $ret = true;
        } else {
            $ret = false;
        }
        
        return $ret;
    }
    
    /**
     * Function that gets the Alternate Design Complete Redirect URL.
     * @return string|null Returns the Alternate Design Complete Redirect URL if one
     * is set, otherwise returns null.
     */
    static function get_alternate_design_complete_redirect_url() {
        $val = null;
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        if ( ! empty($options['alternate_design_complete_redirect_url'] ) ) {
            $val = $options['alternate_design_complete_redirect_url'];
        }

        return $val;
    }

}
