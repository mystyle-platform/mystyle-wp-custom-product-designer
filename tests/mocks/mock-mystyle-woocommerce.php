<?php

/**
 * Mocks woocommerce
 *
 * @package MyStyle
 * @since 0.5.4
 */
class MyStyle_MockWooCommerce {
    
    public $cart;
    
    public function __construct() {
        $this->cart = new MyStyle_MockWooCommerceCart();
    }

}
