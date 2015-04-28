<?php

/**
 * Class for creating an endpoint the mystyle handoff.
 * @todo Create unit tests
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_Handoff {
    
    private static $SLUG = "mystyle-handoff";
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    function __construct() {
        add_action('wp_loaded', array(&$this, 'override'));
    }
    
    public static function override() {
        //$url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $url = $_SERVER['REQUEST_URI'];
        //echo $url;
        if(strpos($url, self::$SLUG) !== FALSE) {
            ob_start(array('MyStyle_Handoff', 'render'));
        }
    }
    
    public static function render() {
        $html = "<!DOCTYPE html><html><head></head><body><h1>MyStyle!</h1></body></head>";
        
        return $html;
    }
    
}