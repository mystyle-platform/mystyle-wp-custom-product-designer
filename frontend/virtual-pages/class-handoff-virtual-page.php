<?php

/**
 * Class for creating a virtual (non-persisted) page for the mystyle handoff.
 * @todo Create unit tests
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_Handoff_Virtual_Page {
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    function __construct() {
        add_action('init', array(&$this, 'catch-url'));
    }
    
    /**
     * Function to catch the url.
     */
    public static function catch_url() {
        //TODO
    }
    
}