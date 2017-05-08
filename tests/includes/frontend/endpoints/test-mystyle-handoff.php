<?php

//bootstrap woocommerce
require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_PATH . '../woocommerce/includes/abstracts/abstract-wc-product.php' );
require_once( MYSTYLE_PATH . '../woocommerce/includes/class-wc-product-variable.php' );

require_once( MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-api.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php' );

/**
 * The MyStyleHandoffTest class includes tests for testing the MyStyle_Handoff
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleHandoffTest extends WP_UnitTestCase {
    
    private $mail;
    
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
        
        //Call the constructor
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
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
        
        //Init the MyStyle_Handoff
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
        //Call the function
        $ret = $mystyle_handoff->override();
        
        $this->assertFalse( $ret );
    }
    
    /**
     * Test the override function for a matching uri
     */    
    public function test_override_overrides_matching_uri() {
        
        $GLOBALS['skip_ob_start'] = true;
        
        $_SERVER['REQUEST_URI'] = 'http://localhost/wordpress/?mystyle-handoff';
        
        //Init the MyStyle_Handoff
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
        //Call the function
        $ret = $mystyle_handoff->override();
        
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
        $GLOBALS['skip_ob_start'] = true;
        
        //Init the MyStyle_Handoff
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        //Call the function
        $mystyle_handoff->handle();
        $html = $mystyle_handoff->get_output();
        
        $this->assertContains( '<h2>Access Denied</h2>', $html );
    }
    
    /**
     * Test the handle function for a POST request
     */    
    public function test_handle_post_request() {
        global $post;
        global $woocommerce;
        global $mail_message;
        $GLOBALS['skip_ob_start'] = true;
        
        //Create the MyStyle Customize page (needed for the link in the email)
        MyStyle_Customize_Page::create();
        
        //Create the MyStyle Design Profile page (needed for the link in the email)
        MyStyle_Design_Profile_Page::create();
        
        //Mock woocommerce
        $woocommerce = new MyStyle_MockWooCommerce();
        
        //Init the MyStyle_Handoff
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
        //Mock the POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = 1;
        $post['product_id'] = 0;
        $post['h'] = base64_encode( 
                        json_encode( 
                            array(
                                'post' => array(
                                    'add-to-cart' => 0, 
                                    'quantity' => 1
                                )
                            ) 
                        ) 
                    );
        $post['user_id'] = 2;
        $post['price'] = 0;
        $_POST = $post;
        
        //Assert that the session is not yet persisted
        $this->assertFalse( MyStyle_SessionHandler::get()->is_persistent() );
        
        //Call the function
        $mystyle_handoff->handle();
        $html = $mystyle_handoff->get_output();
        
        //Assert that the 'product added to cart' message is displayed
        $this->assertContains( 'Product added to cart', $html );
        
        //Assert that the session was persisted
        $this->assertTrue( MyStyle_SessionHandler::get()->is_persistent() );
        
        //Assert that add_to_cart was called.
        $this->assertEquals( 1, $woocommerce->cart->add_to_cart_call_count );
        
        //Assert that the email was sent
        $this->assertEquals( 'Design Created!', $mail_message['subject'] );
        $this->assertContains( 'http://', $mail_message['message'] );
    }
    
    /**
     * Test the handle function for a POST request with a variation
     */
    public function test_handle_post_request_with_variation() {
        global $post;
        global $woocommerce;
        global $mail_message;
        $GLOBALS['skip_ob_start'] = true;
        
        //Create the MyStyle Customize page (needed for the link in the email)
        MyStyle_Customize_Page::create();
        
        //Create the MyStyle Design Profile page (needed for the link in the email)
        MyStyle_Design_Profile_Page::create();
        
        //Mock woocommerce
        $woocommerce = new MyStyle_MockWooCommerce();
        
        //create a variable product
        $product_id = create_wc_test_product(
                'Test Product', //name
                'variable', //type
                array('color' => array('red', 'blue')) //attributes
        );
        
        $parent = get_post( $product_id );
        
        //create a variation
        $variation_id = create_wc_test_product_variation(
                            $parent,
                            1,
                            array('color' => 'red')
        );
        
        //Init the MyStyle_Handoff
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
        //Mock the POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = 1;
        $post['product_id'] = $product_id;
        $post['h'] = base64_encode( 
                        json_encode( 
                            array(
                                'post' => array(
                                    'add-to-cart' => $product_id, 
                                    'product_id' => $product_id,
                                    'quantity' => 1,
                                    'variation_id' => $variation_id,
                                    'attribute_pa_color' => 'blue',
                                )
                            ) 
                        ) 
                    );
        $post['user_id'] = $parent->post_author;
        $post['price'] = 0;
        $_POST = $post;
        
        //Call the function
        $mystyle_handoff->handle();
        $html = $mystyle_handoff->get_output();
        
        //Assert that the expected product variation was added to the cart.
        $added_to_cart = $woocommerce->cart->added_to_cart;
        
        $this->assertEquals( $variation_id, $added_to_cart['variation_id'] );
    }
    
    /**
     * Test the handle function for a POST request with a variation that is
     * altered by the customiser. The post data includes the color red but the
     * variation_id for the blue variation.  The function should catch this
     * and change the variation_id to the id of the red variation.
     */
    public function test_handle_post_request_with_altered_variation() {
        global $post;
        global $woocommerce;
        global $mail_message;
        $GLOBALS['skip_ob_start'] = true;
        
        //Create the MyStyle Customize page (needed for the link in the email)
        MyStyle_Customize_Page::create();
        
        //Create the MyStyle Design Profile page (needed for the link in the email)
        MyStyle_Design_Profile_Page::create();
        
        //Mock woocommerce
        $woocommerce = new MyStyle_MockWooCommerce();
        
        //create a variable product
        $product_id = create_wc_test_product(
                'Test Product', //name
                'variable', //type
                array('color' => array('red', 'blue')) //attributes
        );
        
        $parent = get_post( $product_id );
        
        //create the red variation
        $red_variation_id = create_wc_test_product_variation(
                            $parent,
                            1,
                            array('color' => 'red')
        );
        
        //create the blue variation
        $blue_variation_id = create_wc_test_product_variation(
                            $parent,
                            2,
                            array('color' => 'blue')
        );
        
        //Init the MyStyle_Handoff
        $mystyle_handoff = new MyStyle_Handoff( new MyStyle_MockAPI() );
        
        //Mock the POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = 1;
        $post['product_id'] = $product_id;
        $post['h'] = base64_encode( 
                        json_encode( 
                            array(
                                'post' => array(
                                    'add-to-cart' => $product_id,
                                    'product_id' => $product_id,
                                    'quantity' => 1,
                                    //submit the blue variation_id but the color red
                                    'variation_id' => $blue_variation_id,
                                    'attribute_pa_color' => 'red'
                                )
                            ) 
                        ) 
                    );
        $post['user_id'] = 2;
        $post['price'] = 0;
        $_POST = $post;
        
        //Call the function
        $mystyle_handoff->handle();
        $html = $mystyle_handoff->get_output();
        
        //Assert that the variation_id ws updated to the red one
        $added_to_cart = $woocommerce->cart->added_to_cart;
        $this->assertEquals( $red_variation_id, $added_to_cart['variation_id'] );
    }
    
}
