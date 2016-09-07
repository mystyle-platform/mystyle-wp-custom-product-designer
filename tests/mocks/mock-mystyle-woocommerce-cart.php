<?php

/**
 * Mocks the woocommerce cart
 *
 * @package MyStyle
 * @since 1.0.0
 */
class MyStyle_MockWooCommerceCart {
    
    public function __construct() {
        //
    }
    
    /**
     * Mock the add_to_cart method.
     * @param type $product_id
     * @param type $some_int
     * @param type $some_string
     * @param type $some_array
     * @param type $cart_item_data
     */
    public function add_to_cart( $product_id, $some_int, $some_string, $some_array, $cart_item_data ) {
        // do nothing
    }
    
    /**
     * Mock the get_cart_url method.
     * @return string
     */
    public function get_cart_url() {
        return 'mock_cart_url';
    }
    
    /**
     * Mock the get_cart method.
     * @return type
     */
    public function get_cart() {
        return array();
    }

}
