<?php

require_once(MYSTYLE_INCLUDES . 'frontend/class-frontend.php');

/**
 * The FrontEndTest class includes tests for testing the MyStyle_FrontEnd class.
 *
 * @package MyStyle
 * @since 0.2.1
 * @todo Add the tests for the rest of the class functions/methods.
 */
class FrontEndTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names($wp_filter['init']);
        $this->assertContains('mystyle_frontend_init', $function_names);  
    }
    
    /**
     * Test the mystyle_frontend_init function.
     */    
    public function test_mystyle_frontend_init() {
        $mystyle_admin = new MyStyle_Admin();
        
        //Assert that the frontend stylesheet is registered
        global $wp_styles;
        $this->assertContains('myStyleFrontEndStylesheet', serialize($wp_styles));
    }
    
    
}

