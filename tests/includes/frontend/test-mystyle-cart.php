<?php

require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_INCLUDES . 'frontend/class-mystyle-cart.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-wc-product-variable.php' );

/**
 * The MyStyleCartTest class includes tests for testing the MyStyle_Cart class.
 *
 * @package MyStyle
 * @since 1.4.10
 */
class MyStyleCartTest extends WP_UnitTestCase {
    
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
        $mystyle_cart = new MyStyle_Cart();
        
        global $wp_filter;
        
        //Assert that the filter_cart_button_text function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_product_single_add_to_cart_text'] );
        $this->assertContains( 'filter_cart_button_text', $function_names );
        
        //Assert that the filter_add_to_cart_handler function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_to_cart_handler'] );
        $this->assertContains( 'filter_add_to_cart_handler', $function_names );
        
        //Assert that the filter_cart_item_product function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_cart_item_product'] );
        $this->assertContains( 'filter_cart_item_product', $function_names );
        
        //Assert that the mystyle_add_to_cart_handler_customize function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_to_cart_handler_mystyle_customizer'] );
        $this->assertContains( 'mystyle_add_to_cart_handler_customize', $function_names );
        
        //Assert that the mystyle_add_to_cart_handler function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_to_cart_handler_mystyle_add_to_cart'] );
        $this->assertContains( 'mystyle_add_to_cart_handler', $function_names );
        
        //Assert that the loop_add_to_cart_link function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_loop_add_to_cart_link'] );
        $this->assertContains( 'loop_add_to_cart_link', $function_names );
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
     * Test the filter_cart_button_text function when product isn't mystyle
     * enabled.
     */    
    public function test_filter_cart_button_text_doesnt_modify_button_text_when_not_mystyle_enabled() {
        global $product;
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //call the function
        $text = MyStyle_Cart::get_instance()->filter_cart_button_text( 'Add to Cart' );
        
        //Assert that the expected text is returned
        $this->assertContains( 'Add to Cart', $text );
    }
    
    /**
     * Test the filter_cart_button_text function when product is mystyle enabled.
     */    
    public function test_filter_cart_button_text_modifies_button_text_when_mystyle_enabled() {
        global $product;
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        $text = MyStyle_Cart::get_instance()->filter_cart_button_text( 'Add to Cart' );
        
        //Assert that the expected text is returned
        $this->assertContains( 'Customize', $text );
    }
    
    /**
     * Test the filter_add_to_cart_handler function when product isn't mystyle
     * enabled.
     */    
    public function test_filter_add_to_cart_handler_doesnt_modify_handler_when_not_mystyle_enabled() {
        global $product;
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        $text = MyStyle_Cart::get_instance()->filter_add_to_cart_handler( 'test_handler', $product );
        
        //Assert that the expected text is returned
        $this->assertContains( 'test_handler', $text );
    }
    
    /**
     * Test the filter_add_to_cart_handler function when product is mystyle enabled.
     */    
    public function test_filter_add_to_cart_handler_modifies_handler_when_mystyle_enabled() {
        global $product;
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        if(WC_VERSION >= 2.3) { //we intercept the filter and call the the handler in old versions of WC, so this test always fails
            
            //call the function
            $text = MyStyle_Cart::get_instance()->filter_add_to_cart_handler( 'test_handler', $product );
        
            //Assert that the expected text is returned
            $this->assertContains( 'mystyle_customizer', $text );
        }
    }
    
    /**
     * Test the loop_add_to_cart_link function for a regular (uncustomizable) 
     * product.
     */    
    public function test_loop_add_to_cart_link_for_uncustomizable_product() {
        //Create a mock link
        $link = '<a href="">link</a>';
        
         //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        $html = MyStyle_Cart::get_instance()->loop_add_to_cart_link( $link, $product );
        
        $this->assertContains( $link, $html );
    }
    
    /**
     * Test the loop_add_to_cart_link function for a customizable product.
     */    
    public function test_loop_add_to_cart_link_for_customizable_product() {
        //Create a mock link
        $link = '<a href="">link</a>';
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        //Create the MyStyle Customize page (needed by the function)
        MyStyle_Customize_Page::create();
        
        //Run the function
        $html = MyStyle_Cart::get_instance()->loop_add_to_cart_link( $link, $product );
        
        //var_dump($html);
        
        $cust_pid = MyStyle_Customize_Page::get_id();
        $h = base64_encode( json_encode( array( 'post' => array( 'quantity' => 1, 'add-to-cart' => 1 ) ) ) );
        
        $expectedUrl = 'http://example.org/?page_id=' . $cust_pid . '&#038;product_id=1&#038;h=' . $h;
        
        $expectedHtml = '<a href="'.$expectedUrl.'" rel="nofollow" class="button  product_type_simple" >Customize</a>';
        
        $this->assertEquals( $expectedHtml, $html );
    }
    
    /**
     * Test the loop_add_to_cart_link function for a customizable but variable
     * product.  It should leave the button "Select Options" unchanged.
     */    
    public function test_loop_add_to_cart_link_for_variable_product() {
        $mystyle_cart = new MyStyle_Cart();
        
        //Create a mock link
        $link = '<a href="">link</a>';
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        //Create a mock VARIABLE product using the mock Post
        $product = new WC_Product_Variable( $GLOBALS['post'] );
        
        $html = $mystyle_cart->loop_add_to_cart_link( $link, $product );
        
        //assert that the link is returned unmodified
        $this->assertContains( $link, $html );
    }
    
    /**
     * Test the mystyle_add_to_cart_handler function.
     */    
    public function test_mystyle_add_to_cart_handler() {
        global $woocommerce;
        
        $mystyle_cart = MyStyle_Cart::get_instance();
        
        //Create the MyStyle Customize page
        MyStyle_Customize_Page::create();
        
        //Mock woocommerce
        $woocommerce = new MyStyle_MockWooCommerce();
        $woocommerce->cart = new MyStyle_MockWooCommerceCart();
        
        //Mock the request
        $_REQUEST['add-to-cart'] = 1;
        $_REQUEST['design_id'] = 2;
        
        //call the function
        $mystyle_cart->mystyle_add_to_cart_handler( 'http://www.example.com' );
        
        //Assert that the mock add_to_cart function was called.
        $this->assertEquals( 1, $woocommerce->cart->add_to_cart_call_count );
    }
    
    /**
     * Test the filter_cart_item_product function.
     */
    public function test_filter_cart_item_product( ) {
        //Create a design
        $result_object = new MyStyle_MockDesignQueryResult( 1 );
        $design = MyStyle_Design::create_from_result_object( $result_object );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Create a mock product (this class is mocked above).
        $product = new WC_Product_Variable();
        
        $cart_item_key = 'test_cart_item_key';
        
        //create the cart item data
        $cart_item = array(
            'mystyle_data' => array(
                'design_id' => $design->get_design_id()
            )
        );
        
        //call the function
        $ret_product = MyStyle_Cart::get_instance()->filter_cart_item_product( 
                                        $product, 
                                        $cart_item, 
                                        $cart_item_key 
                                    );
        
        $this->assertEquals( 'MyStyle_Product', get_class( $ret_product ) );
    }
    
    /**
     * Disable the wp_redirect function so that it returns false and doesn't
     * perform the redirect. This is used by the 
     * test_mystyle_add_to_cart_handler function below.
     * @param string $location
     * @param integer $status
     * @return type
     */
    function filter_wp_redirect( $location, $status ){
        global $filter_wp_redirect_called;
        
        $filter_wp_redirect_called = true;
    }
    
    /**
     * Test the mystyle_add_to_cart_handler_customize function.
     */    
    public function test_mystyle_add_to_cart_handler_customize() {
        global $product;
        global $filter_wp_redirect_called;
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Set the expected request variables
        $_REQUEST['add-to-cart'] = $product->id;
        $_REQUEST['quantity'] = 1;
        
        //Create the MyStyle Customize page (needed by the function)
        MyStyle_Customize_Page::create();
        
        //Disable the redirect
        add_filter('wp_redirect', array( &$this, 'filter_wp_redirect' ), 10, 2);
        
        //call the function
        MyStyle_Cart::get_instance()->mystyle_add_to_cart_handler_customize( '' );
        
        //Assert that the function called the filter_wp_redirect function (see above)
        $this->assertTrue( $filter_wp_redirect_called );
    }
}
