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
     * Function that gets the value of the design_profile_page_show_add_to_cart
     * setting.
     * @return boolean Returns 1 if the design_profile_page_show_add_to_cart is
     * enabled, otherwise returns false. Defaults to enabled (1).
     */
    static function get_design_profile_page_show_add_to_cart() {
        $design_profile_page_show_add_to_cart = 1;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if ( isset( $options['design_profile_page_show_add_to_cart'] ) ) {
            $design_profile_page_show_add_to_cart = $options['design_profile_page_show_add_to_cart'];
        }
        
        return $design_profile_page_show_add_to_cart;
    }
    
    /**
     * Function that gets the value of the 
     * customize_page_disable_viewport_rewrite setting.
     * @return boolean Returns 1 if the customize_page_title_hide setting is 
     * enabled, otherwise returns false.
     */
    static function get_customize_page_disable_viewport_rewrite() {
        $customize_page_disable_viewport_rewrite = 0;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if ( ! empty( $options['customize_page_disable_viewport_rewrite'] ) ) {
            $customize_page_disable_viewport_rewrite = $options['customize_page_disable_viewport_rewrite'];
        }
        
        return $customize_page_disable_viewport_rewrite;
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
        if ( ! empty( $options['alternate_design_complete_redirect_url'] ) ) {
            $val = $options['alternate_design_complete_redirect_url'];
        }

        return $val;
    }
    
    /**
     * Function that builds the Alternate Design Complete Redirect URL.
     * @return string|null Returns the built Alternate Design Complete Redirect
     * URL if one is set, otherwise returns null.
     */
    static function build_alternate_design_complete_redirect_url( 
                                                        MyStyle_Design $design 
                ) {
        $url = self::get_alternate_design_complete_redirect_url();
        
        if ( ! empty( $url ) ) {
            if ( strpos( $url, '?' ) == false ) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= 'design_id=' . $design->get_design_id();
            $url .= '&design_complete=1';
        }

        return $url;
    }
    
    /**
     * Function that gets the Redirect URL Whitelist.
     * @return array|null Returns the Redirect URL Whitelist as an array (if one
     * is set), otherwise returns null.
     */
    static function get_redirect_url_whitelist() {
        $val = null;
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        if ( ! empty($options['redirect_url_whitelist'] ) ) {
            $val = explode("\n", $options['redirect_url_whitelist']);
        }

        return $val;
    }
    
    /**
     * Function that determines whether or not the passed redirect url is
     * permitted by the redirect_url_whitelist.
     * @param $redirect_url The url that you want to check.
     * @return boolean Returns true if the redirect_url is permitted, otherwise
     * returns false.
     */
    static function is_redirect_url_permitted( $redirect_url ) {
        $allowed = false;
        $redirect_domain = parse_url( $redirect_url, PHP_URL_HOST );
        $whitelist_array = self::get_redirect_url_whitelist();
        
        if( ! empty( $whitelist_array ) ) {
            $allowed = in_array( $redirect_domain , $whitelist_array );
        }
        
        return $allowed;
    }

}
