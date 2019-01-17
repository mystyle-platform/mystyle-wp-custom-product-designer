<?php
/**
 * The MyStyleWCTest class includes tests for testing the MyStyle_WC class.
 *
 * @package MyStyle
 * @since 2.0.0
 */

/**
 * MyStyleWCTest class.
 */
class MyStyleWCTest extends WP_UnitTestCase {

	/**
	 * Test the get_matching_variation function.
	 */
	public function test_get_matching_variation() {
		// Set up the test data.
		$wc_product = WC_Helper_Product::create_variation_product();

		// Fix the test data (WC < 3.0 is broken).
		fix_variation_product( $wc_product );

		// Wrap the product to get the id.
		$product    = new MyStyle_Product( $wc_product );
		$product_id = $product->get_id();

		// Create the MyStyle_WC instance.
		$mystyle_wc = new MyStyle_WC();

		// Get all children of the product.
		$children = $product->get_children();

		// ------------------- TEST THE FIRST VARIATION ------------------//
		// Get the first variation.
		$expected_variation_id = $children[0];
		$variation             = wc_get_product_variation_attributes( $expected_variation_id );

		// Call the function.
		$returned_variation_id = $mystyle_wc->get_matching_variation( $product_id, $variation );

		// Assert that the modified args include the mystyle_enabled meta key.
		$this->assertEquals( $expected_variation_id, $returned_variation_id );

		// ------------------- TEST THE SECOND VARIATION ------------------//
		// Get the first variation.
		$expected_variation_id = $children[1];
		$variation             = wc_get_product_variation_attributes( $expected_variation_id );

		// Call the function.
		$returned_variation_id = $mystyle_wc->get_matching_variation( $product_id, $variation );

		// Assert that the modified args include the mystyle_enabled meta key.
		$this->assertEquals( $expected_variation_id, $returned_variation_id );
	}

}
