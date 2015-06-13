<?php

require_once(MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php');

/**
 * The MyStyleHandoffTest class includes tests for testing the MyStyle_Handoff
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleHandoffTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        global $wp_filter;
        
        $mystyle_handoff = new MyStyle_Handoff();
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['wp_loaded'] );
        $this->assertContains( 'override', $function_names );
    }
    
    /**
     * Test the override function for a non matching uri
     */    
    public function test_override_skips_non_matching_uri() {
        $mystyle_handoff = new MyStyle_Handoff();
        
        $_SERVER['REQUEST_URI'] = 'non-matching-uri';
        
        //Assert that override does nothing
        ob_start();
        $mystyle_handoff->override();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertEquals( '', $outbound );
    }
    
    /**
     * Test the override function for a matching uri
     */    
    public function test_override_overrides_matching_uri() {
        //TODO: come up with a better (and faster) way to do this
        /*
        $url = 'http://localhost/wordpress/?mystyle-handoff';
 
        $response = wp_remote_get( $url );
        
        $this->assertContains( '<h2>Access Denied</h2>', $response['body'] );
         */
    }
    
    /**
     * Test the handle function for a GET request
     */    
    public function test_handle_get_request() {
        $mystyle_handoff = new MyStyle_Handoff();
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        $html = $mystyle_handoff->handle();
        
        $this->assertContains( '<h2>Access Denied</h2>', $html );
    }
    
    /**
     * Test the handle function for a POST request
     */    
    public function test_handle_post_request() {
        //TODO
    }
    
}
