<?php

require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-api.php' );

/**
 * The MyStyleAPITest class includes tests for testing the MyStyle_API class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleAPITest extends WP_UnitTestCase {

    /**
     * tearDown function. Called by phpUnit after each test.
     */
    function tearDown() {
        remove_filter( 'pre_http_request', array( 'MyStyle_MockAPI', 'mock_api_call' ) );
    }
    
    /**
     * Test the add_api_data_to_design function
     */    
    function test_add_api_data_to_design() {
        
        //init the MyStyle_API
        $mystyle_api = new MyStyle_API( "http://localhost" );
        
        //Mock the API response (so that the actual api isn't ever really called).
        add_filter( 'pre_http_request', array( 'MyStyle_MockAPI', 'mock_api_call' ), 10, 3 );
        
        //Install the api_key
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['api_key'] = '0';
        $options['secret'] = 'fake-secret';
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        //Create a design
        $design = new MyStyle_Design();
        $design->set_description( 'test description' );
        $design->set_design_id( 1 );
        $design->set_template_id( 1 );
        $design->set_product_id( 1 );
        $design->set_designer_id( 1 );
        $design->set_price( 1 );
        
        
        
        $design = $mystyle_api->add_api_data_to_design( $design );
        
        //Assert print_url is set
        $expected_print_url = 'http://testhost/test_print_url.jpg';
        $this->assertEquals( $expected_print_url, $design->get_print_url() );
    }
    
    /**
     * Test the get_user function
     */    
    function test_get_user() {
        $designer_id = 2;
        
        //init the MyStyle_API
        $mystyle_api = new MyStyle_API( "http://localhost" );
        
        //Mock the API response
        add_filter( 'pre_http_request', array( 'MyStyle_MockAPI', 'mock_api_call' ), 10, 3 );
        
        //Install the api_key
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['api_key'] = '0';
        $options['secret'] = 'fake-secret';
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        /* @var $user \MyStyle_User */
        $user = $mystyle_api->get_user( $designer_id );
        
        //Assert email is set
        $expected_email= 'someone@example.com';
        $this->assertEquals( $expected_email, $user->get_email() );
    }

}
