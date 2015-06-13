<?php

require_once(MYSTYLE_PATH . '../woocommerce/woocommerce.php');

/**
 * The MyStyleClassTest class includes tests for testing the MyStyle
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleClassTest extends WP_UnitTestCase {

    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle = new MyStyle();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );  
        
        //Assert that the add_mystyle_order_item_meta function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_order_item_meta'] );
        $this->assertContains( 'add_mystyle_order_item_meta', $function_names );
    }
    
    /**
     * Test the init function.
     */    
    public function test_init() {
        $mystyle = new MyStyle();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_cart_item_thumbnail'] );
        $this->assertContains( 'modify_cart_item_thumbnail', $function_names );
    }
    
    /**
     * Test the add_mystyle_order_item_meta function.
     * TODO: Get this test to work. Needs to create a woocommerce product and
     * order in order to add the order item meta. see http://stackoverflow.com/questions/26581467/creating-woocommerce-order-with-line-item-programatically
     */    
    public function test_add_mystyle_order_item_meta() {
        /*
        $item_id = 1;
        $key = 'mystyle_data';
        $data = 'test_data';
        
        $values = array();
        $values[$key] = $data;
        
        //Add the order item meta
        MyStyle::add_mystyle_order_item_meta($item_id, $values);
        
        //Retrieve the order item meta       
        $meta = wc_get_order_item_meta($item_id, $key);
        
        //Assert that the expected meta is returned
        $this->assertEquals( $data, $meta );
         */
    }
    
    /**
     * Test the modify_cart_item_thumbnail function.
     */    
    public function test_modify_cart_item_thumbnail() {
        //TODO
    }
}
