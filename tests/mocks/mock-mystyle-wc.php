<?php
/**
 * Mocks our interface with WooCommerce.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * MyStyle_MockWC class.
 */
class MyStyle_MockWC extends MyStyle_AbstractWC implements MyStyle_WC_Interface {

	/**
	 * Fake for the get_matching_variation method.
	 *
	 * @param integer $product_id The product id of the product whose variation
	 * you are looking for.
	 * @param array   $variation The variation that you are looking for.
	 * @return integer Returns the variation id of the matching product
	 * variation.
	 */
	public function get_matching_variation( $product_id, $variation ) {
		return 1;
	}

}
