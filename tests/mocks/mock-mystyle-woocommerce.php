<?php

/**
 * Mocks woocommerce
 *
 * @package MyStyle
 * @since 1.0.0
 */
class MyStyle_MockWooCommerce {
    
    public $cart;
    
    public function __construct() {
        $this->cart = new MyStyle_MockWooCommerceCart();
    }

}
