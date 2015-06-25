<?php

/**
 * Mocks the woocommerce cart
 *
 * @package MyStyle
 * @since 0.5.4
 */
class MyStyle_MockWooCommerceCart {
    
    public function __construct() {
        //
    }
    
    public function add_to_cart( $product_id, $some_int, $some_string, $some_array, $cart_item_data ) {
        // do nothing
    }
    
    public function get_cart_url() {
        return 'mock_cart_url';
    }

}
