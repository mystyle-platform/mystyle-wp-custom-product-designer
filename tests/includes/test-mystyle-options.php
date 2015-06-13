<?php

/**
 * The MyStyleOptionsTest class includes tests for testing the MyStyle_Options
 * class.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyleOptionsTest extends WP_UnitTestCase {

    /**
     * Assert that are_keys_installed() correctly returns whether
     * or not a MyStyle API Key and Secret have been installed.
     */    
    function test_are_keys_installed() {
        //Clear out any options
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        //Assert function correctly determines api_key and secret are not installed
        $this->assertFalse( MyStyle_Options::are_keys_installed() );

        //Install the api_key and secret
        $options['api_key'] = 'test_key';
        $options['secret'] = 'test_secret';
        update_option( MYSTYLE_OPTIONS_NAME, $options );

        //Assert function correctly determines that keys are installed
        $this->assertTrue( MyStyle_Options::are_keys_installed() );
    }
    
    /**
     * Assert that get_api_key() returns the expected API Key.
     */    
    function test_get_api_key() {
        //Install the api_key
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['api_key'] = 'test';
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        $api_key = MyStyle_Options::get_api_key();

        if( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
            $this->assertContains( MYSTYLE_OVERRIDE_API_KEY, $api_key );
        } else {
            $this->assertEquals( 'test', $api_key );
        }
    }
    
    /**
     * Assert that get_secret() returns the expected secret.
     */    
    function test_get_secret() {
        //Install the secret
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['secret'] = 'test';
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        $secret = MyStyle_Options::get_secret();

        if( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
            $this->assertContains( MYSTYLE_OVERRIDE_SECRET, $secret );
        } else {
            $this->assertEquals( 'test', $secret );
        }
    }
    
    /**
     * Assert that is_demo_mode() returns true if api key is a demo key.
     */    
    function test_is_demo_mode_for_demo_key() {
        $demo_key = 74;

        //Install the api_key
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['api_key'] = $demo_key;
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        $api_key = MyStyle_Options::get_api_key();

        if( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
            echo 'Error, can\'t test.';
        } else {
            $this->assertTrue( MyStyle_Options::is_demo_mode() );
        }
    }
    
    /**
     * Assert that is_demo_mode() returns false if api key is a not a demo key.
     */    
    function test_is_demo_mode_for_non_demo_key() {
        $demo_key = 72;

        //Install the api_key
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['api_key'] = $demo_key;
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        $api_key = MyStyle_Options::get_api_key();

        if( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
            echo 'Error, can\'t test.';
        } else {
            $this->assertFalse( MyStyle_Options::is_demo_mode() );
        }
    }

}
