<?php

/**
 * The FunctionsTest class includes tests for the functions in functions.php
 *
 * @package MyStyle
 * @since 0.1.0
 */
class FunctionsTest extends WP_UnitTestCase {

    /**
     * Assert that mystyle_is_api_key_installed() correctly returns whether
     * or not an MyStyle api key has been installed.
     */    
    function test_mystyle_is_api_key_installed() {
        //Clear out any options
        $options = array();
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        //Assert function correctly determines api_key not installed
        $this->assertFalse( mystyle_is_api_key_installed() );

        //Install the api_key
        $options['api_key'] = 'test';
        update_option(MYSTYLE_OPTIONS_NAME, $options);

        //Assert function correctly determines api_key installed
        $this->assertTrue( mystyle_is_api_key_installed() );
    }
    
    /**
     * Assert that mystyle_get_active_api_key() returns the expected access
     * code.
     */    
    function test_mystyle_get_active_api_key() {
        //Install the api_key
        $options = array();
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        $options['api_key'] = 'test';
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        $api_key = mystyle_get_active_api_key();

        if(defined('MYSTYLE_OVERRIDE_API_KEY')) {
            $this->assertContains(MYSTYLE_OVERRIDE_API_KEY, $api_key);
        } else {
            $this->assertEquals('test', $api_key);
        }
    }
    
    /**
     * Assert that mystyle_is_widget_active() correctly returns whether or not
     * the mystyle widget is active.
     */    
    function test_mystyle_is_widget_active() {
        //Clear out any options
        $options = array();
        update_option(MYSTYLE_WIDGET_OPTIONS_NAME, $options);
        
        //Assert function correctly determines widget is not active
        $this->assertFalse( mystyle_is_widget_active() );

        //Activate the widget
        $options[2]['zone'] = '';
        $options['_multiwidget'] = 1;
        update_option(MYSTYLE_WIDGET_OPTIONS_NAME, $options);

        //Assert function correctly determines widget is active
        $this->assertTrue( mystyle_is_widget_active() );
    }
    
}

