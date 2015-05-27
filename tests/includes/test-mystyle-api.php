<?php

require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-api.php');

/**
 * The MyStyleAPITest class includes tests for testing the MyStyle_API class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleAPITest extends WP_UnitTestCase {

    /**
     * Test the add_api_data_to_design function
     */    
    function test_add_api_data_to_design() {
        
        //Mock the API response
        add_filter( 'pre_http_request', array( 'MyStyleMockAPI', 'mock_api_call' ), 10, 3 );
        
        //Install the api_key
        $options = array();
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        $options['api_key'] = '72';
        $options['secret'] = 'SqXHiNTaD5TC0Y908tC9nEqP6';
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        //Create a design
        $design = new MyStyle_Design();
        $design->set_description( 'test description' );
        $design->set_design_id( 1 );
        $design->set_template_id( 1 );
        $design->set_product_id( 1 );
        $design->set_user_id( 1 );
        $design->set_price( 1 );
        
        $design = MyStyle_API::add_api_data_to_design($design);
        
        //Assert print_url is set
        $expected_print_url = 'http://testhost/test_print_url.jpg';
        $this->assertEquals( $expected_print_url, $design->get_print_url() );
    }

}
