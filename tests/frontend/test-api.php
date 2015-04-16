<?php

require_once(MYSTYLE_PATH . 'frontend/api.php');

/**
 * The ApiTest class includes tests for testing the functions in the
 * frontend/api.php file.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class ApiTest extends WP_UnitTestCase {
    
    /**
     * Test that the mystyle_add_api function doesn't output anything if the
     * api_key is not set. Note that if the override is set, the API will
     * be output.
     * TODO: Figure out a way to disable the override for this test.
     */    
    public function test_mystyle_add_api_doesnt_serve_without_api_key() {
        
        //Assert that nothing was output
        ob_start();
        mystyle_add_api();
        $outbound = ob_get_contents();
        ob_end_clean();
        
        if(defined('MYSTYLE_OVERRIDE_API_KEY')) {
            $this->assertContains(MYSTYLE_OVERRIDE_API_KEY, $outbound);
        } else {
            $this->assertEquals('', $outbound);
        }
    }
    
    /**
     * Test that the mystyle_add_api function outputs the api when the 
     * api_key is set.
     */    
    public function test_mystyle_add_api_outputs_api() {
        
        //Set the api_key
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $options['api_key'] = 'A0000';
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        //Output the API
        ob_start();
        mystyle_add_api();
        $outbound = ob_get_contents();
        ob_end_clean();
        
        //Assert that the API is output.
        $this->assertContains('<script', $outbound);
        
    }
    
    /**
     * Test that the mystyle_add_api function outputs the qunit interface when
     * MYSTYLE_LOAD_QUNIT is set to TRUE.
     */    
    public function test_mystyle_add_api_outputs_qunit() {
        //Set the MYSTYLE_LOAD_QUNIT constant
        if(!defined('MYSTYLE_LOAD_QUNIT')) { define('MYSTYLE_LOAD_QUNIT', true); }
        
        //Set the api_key (API won't render without it)
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $options['api_key'] = 'A0000';
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        //Assert that the QUnit interface is output.
        ob_start();
        mystyle_add_api();
        $outbound = ob_get_contents();
        ob_end_clean();
        
        $this->assertContains('qunit', $outbound);
    }
    
    
}

