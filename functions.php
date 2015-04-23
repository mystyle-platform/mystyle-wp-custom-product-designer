<?php
/**
 * Miscellaneous functions used by the plugin.
 * @package MyStyle
 * @since 0.1.0
 */
    
/**
 * Function that looks to see if an mystyle API Key and secret have been 
 * installed.
 * @return boolean Returns true if an API Key and Secret are installed,
 * otherwise returns false.
 */
function mystyle_are_keys_installed() {
    $options = get_option(MYSTYLE_OPTIONS_NAME, array() );
    
    if( (!empty($options['api_key'])) && (!empty($options['secret'])) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function that gets the active api_key
 * @return string Returns the active api key.
 */
function mystyle_get_active_api_key() {
    $api_key = null;
    $options = get_option(MYSTYLE_OPTIONS_NAME, array() );
    if(!empty($options['api_key'])) {
        $api_key = $options['api_key'];
    }
    if(defined('MYSTYLE_OVERRIDE_API_KEY')) {
        $api_key = MYSTYLE_OVERRIDE_API_KEY;
    }
    
    return $api_key;
}

/**
 * Function that gets the active secret
 * @return string Returns the active secret.
 */
function mystyle_get_active_secret() {
    $secret = null;
    $options = get_option(MYSTYLE_OPTIONS_NAME, array() );
    if(!empty($options['secret'])) {
        $secret = $options['secret'];
    }
    if(defined('MYSTYLE_OVERRIDE_SECRET')) {
        $secret = MYSTYLE_OVERRIDE_SECRET;
    }
    
    return $secret;
}
