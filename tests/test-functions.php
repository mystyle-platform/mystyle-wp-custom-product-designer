<?php

/**
 * The FunctionsTest class includes tests for the functions in functions.php
 *
 * @package MyStyle
 * @since 0.1.0
 */
class FunctionsTest extends WP_UnitTestCase {

    /**
     * Assert that mystyle_are_keys_installed() correctly returns whether
     * or not a MyStyle API Key and Secret have been installed.
     */    
    function test_mystyle_are_keys_installed() {
        //Clear out any options
        $options = array();
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        //Assert function correctly determines api_key and secret are not installed
        $this->assertFalse( mystyle_are_keys_installed() );

        //Install the api_key and secret
        $options['api_key'] = 'test_key';
        $options['secret'] = 'test_secret';
        update_option(MYSTYLE_OPTIONS_NAME, $options);

        //Assert function correctly determines that keys are installed
        $this->assertTrue( mystyle_are_keys_installed() );
    }
    
    /**
     * Assert that mystyle_get_active_api_key() returns the expected API Key.
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
     * Assert that mystyle_get_active_secret() returns the expected secret.
     */    
    function test_mystyle_get_active_secret() {
        //Install the secret
        $options = array();
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        $options['secret'] = 'test';
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        $secret = mystyle_get_active_secret();

        if(defined('MYSTYLE_OVERRIDE_API_KEY')) {
            $this->assertContains(MYSTYLE_OVERRIDE_SECRET, $secret);
        } else {
            $this->assertEquals('test', $secret);
        }
    }
    
}

