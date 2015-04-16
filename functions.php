<?php
/**
 * Miscellaneous functions used by the plugin.
 * @package MyStyle
 * @since 0.1.0
 */
    
/**
 * Function that looks to see if an mystyle api key has been installed.
 * @return boolean Returns true if an api key is installed, otherwise
 * returns false.
 */
function mystyle_is_api_key_installed() {
    $options = get_option(MYSTYLE_OPTIONS_NAME, array() );
    
    if(!empty($options['api_key'])) {
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
