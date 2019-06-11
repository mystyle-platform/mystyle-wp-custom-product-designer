<?php
/**
 * Mocks the woocommerce cart.
 *
 * @package MyStyle
 * @since 1.0.0
 */

/**
 * MyStyle_MockWooCommerceCart class.
 */
class MyStyle_MockWooCommerceCart {

	/**
	 * Spy on the number of times add_to_cart called.
	 *
	 * @var integer
	 */
	public $add_to_cart_call_count;

	/**
	 * An array of what has been added to the cart.
	 *
	 * @var array
	 */
	public $added_to_cart;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_to_cart_call_count = 0;
	}

	/**
	 * Mock the add_to_cart method.
	 *
	 * @param int   $product_id The id of the product.
	 * @param int   $quantity The quantity to add to the cart.
	 * @param int   $variation_id Any variation id.
	 * @param array $variation Attribute values.
	 * @param array $cart_item_data Extra cart item data we want to pass into
	 * the item.
	 * @return string|bool $cart_item_key
	 */
	public function add_to_cart(
		$product_id,
		$quantity,
		$variation_id,
		$variation,
		$cart_item_data ) {

		$this->add_to_cart_call_count++;
		$this->added_to_cart = array(
			'product_id'     => $product_id,
			'quantity'       => $quantity,
			'variation_id'   => $variation_id,
			'variation'      => $variation,
			'cart_item_data' => $cart_item_data,
		);

		return true;
	}

	/**
	 * Mock the get_cart_url method.
	 *
	 * @return string
	 */
	public function get_cart_url() {
		return 'mock_cart_url';
	}

	/**
	 * Mock the get_cart method.
	 *
	 * @return type
	 */
	public function get_cart() {
		return array();
	}

}
