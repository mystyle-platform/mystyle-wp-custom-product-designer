<?php

require_once(MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php');

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
        
        //Assert that the filter_cart_button_text function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_product_single_add_to_cart_text'] );
        $this->assertContains( 'filter_cart_button_text', $function_names );
        
        //Assert that the filter_add_to_cart_handler function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_to_cart_handler'] );
        $this->assertContains( 'filter_add_to_cart_handler', $function_names );
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );
        
        //Assert that the mystyle_add_to_cart_handler function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_to_cart_handler_mystyle_customizer'] );
        $this->assertContains( 'mystyle_add_to_cart_handler', $function_names );
        
        //Assert that the loop_add_to_cart_link function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_loop_add_to_cart_link'] );
        $this->assertContains( 'loop_add_to_cart_link', $function_names );
    }
    
    /**
     * Test the mystyle_frontend_init function.
     */    
    public function test_mystyle_frontend_init() {
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Assert that the swfobject script is registered
        global $wp_scripts;
        $this->assertContains( 'swfobject', serialize( $wp_scripts ) );
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
        
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        $text = $mystyle_frontend->filter_cart_button_text( 'Add to Cart' );
        
        //Assert that the expected text is returned
        $this->assertContains( 'Add to Cart', $text );
    }
    
    /**
     * Test the filter_cart_button_text function when product is mystyle enabled.
     */    
    public function test_filter_cart_button_text_modifies_button_text_when_mystyle_enabled() {
        global $product;
        
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        $text = $mystyle_frontend->filter_cart_button_text( 'Add to Cart' );
        
        //Assert that the expected text is returned
        $this->assertContains( 'Customize', $text );
    }
    
    /**
     * Test the filter_add_to_cart_handler function when product isn't mystyle
     * enabled.
     */    
    public function test_filter_add_to_cart_handler_doesnt_modify_handler_when_not_mystyle_enabled() {
        global $product;
        
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        $text = $mystyle_frontend->filter_add_to_cart_handler( 'test_handler', $product );
        
        //Assert that the expected text is returned
        $this->assertContains( 'test_handler', $text );
    }
    
    /**
     * Test the filter_add_to_cart_handler function when product is mystyle enabled.
     */    
    public function test_filter_add_to_cart_handler_modifies_handler_when_mystyle_enabled() {
        global $product;
        
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        $text = $mystyle_frontend->filter_add_to_cart_handler( 'test_handler', $product );
        
        //Assert that the expected text is returned
        $this->assertContains( 'mystyle_customizer', $text );
    }
    
    /**
     * Test the loop_add_to_cart_link function for a regular (uncustomizable) 
     * product.
     */    
    public function test_loop_add_to_cart_link_for_uncustomizable_product() {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        //Create a mock link
        $link = '<a href="">link</a>';
        
         //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        $html = $mystyle_frontend->loop_add_to_cart_link( $link, $product );
        
        $this->assertContains( $link, $html );
    }
    
    /**
     * Test the loop_add_to_cart_link function for a customizable product.
     */    
    public function test_loop_add_to_cart_link_for_customizable_product() {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
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
        $html = $mystyle_frontend->loop_add_to_cart_link( $link, $product );
        
        //var_dump($html);
        
        $cust_pid = MyStyle_Customize_Page::get_id();
        
        $expected = '<a href="http://example.org/?page_id=' . $cust_pid . '&#038;product_id=1" rel="nofollow" class="button  product_type_simple" >Customize</a>';
        
        $this->assertContains( $expected, $html );
    }
    
    /**
     * Disable the wp_redirect function so that it returns false and doesn't
     * perform the redirect.
     * @param type $location
     * @param type $status
     * @return string
     */
    function filter_wp_redirect( $location, $status ){
        return false;
    }
    
    /**
     * Test the mystyle_add_to_cart_handler function.
     * @todo This test fails because the function calls exit.  We need to mock
     * and stub out the exit function.
     */    
    /*
    public function test_mystyle_add_to_cart_handler() {
        global $product;
        
        $mystyle_frontend = new MyStyle_Frontend();
        
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
        
        $return = $mystyle_frontend->mystyle_add_to_cart_handler( '' );
        
        //Assert that the function returns false as expected (we disabled the redirect)
        $this->assertFalse( $return );
    }
    */
}
