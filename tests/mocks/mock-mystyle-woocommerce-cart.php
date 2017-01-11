<?php

/**
 * Mocks the woocommerce cart
 *
 * @package MyStyle
 * @since 1.0.0
 */
class MyStyle_MockWooCommerceCart {
    
    public $add_to_cart_call_count;
    public $added_to_cart;
    
    public function __construct() {
        $this->add_to_cart_call_count = 0;
    }
    
    /**
     * Mock the add_to_cart method.
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @param array $variation attribute values
     * @param array $cart_item_data extra cart item data we want to pass into 
     * the item.
     * @return string|bool $cart_item_key
     */
    public function add_to_cart( 
                        $product_id, 
                        $quantity, 
                        $variation_id, 
                        $variation, 
                        $cart_item_data ) 
    {
        $this->add_to_cart_call_count++;
        $this->added_to_cart = array(
            'product_id' => $product_id,
            'quantity' => $quantity,
            'variation_id' => $variation_id, 
            'variation' => $variation,
            'cart_item_data' => $cart_item_data
        );
        
        return true;
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
