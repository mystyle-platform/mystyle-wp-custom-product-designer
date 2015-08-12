<?php

require_once(MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php');
require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-api.php');
require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php');
require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php');

/**
 * The MyStyleHandoffTest class includes tests for testing the MyStyle_Handoff
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleHandoffTest extends WP_UnitTestCase {
    
    /**
     * Overrwrite the setUp function so that our custom tables will be persisted
     * to the test database.
     */
    function setUp() {
        // Perform the actual task according to parent class.
        parent::setUp();
        // Remove filters that will create temporary tables. So that permanent tables will be created.
        remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
        remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
        
        //Create the tables
        MyStyle_Install::create_tables();
    }
    
    /**
     * Overrwrite the tearDown function to remove our custom tables.
     */
    function tearDown() {
        global $wpdb;
        // Perform the actual task according to parent class.
        parent::tearDown();
        
        //Drop the tables that we created
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Design::get_table_name());
    }
    
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
     * Test the get_url function
     */    
    public function test_get_url() {
        $expected_url = 'http://example.org/?mystyle-handoff';
        
        $url = MyStyle_Handoff::get_url();
        
        //Assert that the expected url is returned
        $this->assertContains( $expected_url, $url );
    }
    
    /**
     * Test the override function for a non matching uri
     */    
    public function test_override_skips_non_matching_uri() {
        $GLOBALS['skip_ob_start'] = true;
        
        $_SERVER['REQUEST_URI'] = 'non-matching-uri';
        
        
        //Call the function
        $ret = MyStyle_Handoff::override();
        
        $this->assertFalse( $ret );
    }
    
    /**
     * Test the override function for a matching uri
     */    
    public function test_override_overrides_matching_uri() {
        
        $GLOBALS['skip_ob_start'] = true;
        
        $_SERVER['REQUEST_URI'] = 'http://localhost/wordpress/?mystyle-handoff';
        
        //Call the function
        $ret = MyStyle_Handoff::override();
        
        $this->assertTrue( $ret );
        
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
        global $post;
        global $woocommerce;
        
        //Mock the API response
        add_filter( 'pre_http_request', array( 'MyStyleMockAPI', 'mock_api_call' ), 10, 3 );
        
        //Mock woocommerce
        $woocommerce = new MyStyle_MockWooCommerce();
        
        $mystyle_handoff = new MyStyle_Handoff();
        
        //Mock the POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = 1;
        $post['product_id'] = 0;
        $post['h'] = base64_encode( 
                        json_encode( 
                            array( 
                                'local_product_id' => 0, 
                                'quantity' => 1
                            ) 
                        ) 
                    );
        $post['user_id'] = 0;
        $post['price'] = 0;
        $_POST = $post;
        
        $html = $mystyle_handoff->handle();
        
        $this->assertContains( 'Product added to cart', $html );
    }
    
}
