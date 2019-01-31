<?php
/**
 * The MyStylePassthruCodec class includes tests for testing the
 * MyStyle_Passthru_Codec class.
 *
 * @package MyStyle
 * @since 3.8.2
 */

/**
 * MyStyleCartTest class.
 */
class MyStylePassthruCodec extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();

		// Create the tables.
		MyStyle_Install::create_tables();
	}

	/**
	 * Test the build_passthru function.
	 */
	public function test_build_passthru() {
		// Set up the product.
		$product_id = create_wc_test_product();
		/* @var $mystyle_product \MyStyle_Product The product. */
		$mystyle_product = MyStyle_Product::get_by_id( $product_id );

		// Set up the post.
		$post                = array();
		$post['add-to-cart'] = $product_id;
		$post['quantity']    = 1;

		// This is what we expect.
		$expected_passthru = array(
			'post' => array(
				'add-to-cart' => $product_id,
				'quantity'    => 1,
			),
		);

		// Call the function.
		$passthru_codec = MyStyle_Passthru_Codec::get_instance();
		$passthru       = $passthru_codec->build_passthru( $post, $mystyle_product );

		// Assert that the function returned the expected passthru.
		$this->assertEquals( $expected_passthru, $passthru );
	}

}
