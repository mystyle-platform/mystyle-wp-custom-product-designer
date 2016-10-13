<?php

require_once( MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php' );

/**
 * The FrontEndTest class includes tests for testing the MyStyle_FrontEnd class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleFrontEndTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );
    }
    
    /**
     * Test the mystyle_frontend_init function.
     */    
    public function test_mystyle_frontend_init() {
        global $wp_scripts;
        global $wp_styles;
        
        define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
        
        //call the function
        MyStyle_Frontend::get_instance()->init();
        
        //Assert that our scripts are registered
        $this->assertContains( 'swfobject', serialize( $wp_scripts ) );
        
        //Assert that our stylesheets are registered
        $this->assertContains( 'myStyleFrontendStylesheet', serialize( $wp_styles ) );
    }
    
    /**
     * Mock the mystyle_metadata
     * @param type $metadata
     * @param type $object_id
     * @param type $meta_key
     * @param type $single
     * @return string
     */
    function mock_mystyle_metadata( $metadata, $object_id, $meta_key, $single ){
        return 'yes';
    }
    
    /**
     * Test the add_query_vars_filter function.
     */
    public function test_add_query_vars_filter( ) {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        $vars[] = array();
        
        //call the function
        $ret_vars = MyStyle_FrontEnd::get_instance()->add_query_vars_filter( $vars );
        
        $this->assertTrue( in_array( 'design_id', $ret_vars ) );
    }
    
}
