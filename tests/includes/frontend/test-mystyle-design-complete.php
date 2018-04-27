<?php

require_once( MYSTYLE_INCLUDES . 'frontend/class-mystyle-design-complete.php' );

/**
 * The MyStyleDesignComplete class includes tests for testing the
 * MyStyle_Design_Complete class.
 *
 * @package MyStyle
 * @since 1.5.0
 */
class MyStyleDesignCompleteTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor.
     * @global $wp_filter
     */    
    public function test_constructor() {
        global $wp_filter;
        
        //Call the constructor.
        $mystyle_design_complete = new MyStyle_Design_Complete();
        
        //Assert that the filter_cart_button_text function is registered.
        $function_names = get_function_names( $wp_filter['query_vars'] );
        $this->assertContains( 'add_query_vars_filter', $function_names );
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['wp_enqueue_scripts'] );
        $this->assertContains( 'enqueue_scripts', $function_names );
    }
    
    /**
     * Test the enqueue_scripts function.
     * @global $wp_scripts
     */    
    public function test_enqueue_scripts() {
        global $wp_scripts;
        
        //Instantiate the SUT (System Under Test) class.
        $mystyle_design_complete = new MyStyle_Design_Complete();
        
        //Mock the query var
        set_query_var( 'design_complete', 1 );
        
        //Call the method
        $mystyle_design_complete->enqueue_scripts();
        
        //Assert that the design-complete.js script is registered
        $this->assertContains( 
                'mystyle-design-complete', 
                serialize( $wp_scripts ) 
            );
    }
    
    /**
     * Test the add_query_vars_filter function.
     */
    public function test_add_query_vars_filter( ) {
        
        $vars[] = array();
        
        //call the function
        $ret_vars = MyStyle_Design_Complete::get_instance()->add_query_vars_filter( $vars );
        
        $this->assertTrue( in_array( 'design_complete', $ret_vars ) );
    }
    
}
