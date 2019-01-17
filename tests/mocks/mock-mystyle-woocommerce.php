<?php
/**
 * Mocks woocommerce.
 *
 * @package MyStyle
 * @since 1.0.0
 */

/**
 * MyStyle_MockWooCommerce class.
 */
class MyStyle_MockWooCommerce {

	/**
	 * Variable for holding the MyStyle_MockWooCommerceCart.
	 *
	 * @var \MyStyle_MockWooCommerceCart
	 */
	public $cart;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->cart = new MyStyle_MockWooCommerceCart();
	}

}
