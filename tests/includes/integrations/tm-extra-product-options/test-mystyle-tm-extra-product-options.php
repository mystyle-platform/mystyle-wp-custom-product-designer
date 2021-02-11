<?php
/**
 * The MyStyleTmExtraProductOptionsTest class includes tests for testing the
 * MyStyle_Tm_Extra_Product_Options class.
 *
 * @package MyStyle
 * @since 3.13.1
 */

/**
 * Mock the THEMECOMPLETE_EPO function.
 */
function THEMECOMPLETE_EPO() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

	$mock                = new stdClass();
	$mock->cart_edit_key = 'fake_old_cart_item_key';

	return $mock;
}

/**
 * MyStyleTmExtraProductOptionsTest class.
 */
class MyStyleTmExtraProductOptionsTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_tm_extra_product_options = new MyStyle_Tm_Extra_Product_Options();

		// Assert that the object was instantiated as expected.
		$this->assertEquals(
			'MyStyle_Tm_Extra_Product_Options',
			get_class( $mystyle_tm_extra_product_options )
		);

		// Assert that the stash_mystyle_data function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_add_to_cart_validation'] );
		$this->assertContains( 'stash_mystyle_data', $function_names );

		// Assert that the copy_mystyle_data function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_add_to_cart'] );
		$this->assertContains( 'copy_mystyle_data', $function_names );
	}

	/**
	 * Test the stash_mystyle_data function.
	 *
	 * @global $woocommerce
	 * @global $_REQUEST
	 */
	public function test_stash_mystyle_data() {
		global $woocommerce;
		global $_REQUEST;

		// Set up the test data.
		$old_cart_item_key = 'fake_old_cart_item_key';
		$new_cart_item_key = 'fake_new_cart_item_key';
		$mystyle_data      = 'fake_mystyle_data';
		$passed            = true;
		$product_id        = 1;
		$qty               = 1;
		$variation_id      = 1;
		$variations        = array();
		$cart_item_data    = array();

		// Mock woocommerce.
		$woocommerce                      = new MyStyle_MockWooCommerce();
		$woocommerce->cart->cart_contents = array(
			$old_cart_item_key => array(
				'mystyle_data' => $mystyle_data,
			),
			$new_cart_item_key => array(),
		);

		// Mock the $_REQUEST.
		$_REQUEST = array( 'tm_cart_item_key' => $old_cart_item_key );

		// Instantiate the SUT (System Under Test) class.
		$mystyle_tm_extra_product_options = new MyStyle_Tm_Extra_Product_Options();

		// Call the function.
		$ret = $mystyle_tm_extra_product_options->stash_mystyle_data(
			$passed,
			$product_id,
			$qty,
			$variation_id,
			$variations,
			$cart_item_data
		);

		// Assert that the function returned true (validation passed).
		$this->assertTrue( $ret );
	}

	/**
	 * Test the copy_mystyle_data function.
	 *
	 * @global $woocommerce
	 * @global $_REQUEST
	 */
	public function test_copy_mystyle_data() {
		global $woocommerce;
		global $_REQUEST;

		// Set up the test data.
		$old_cart_item_key = 'fake_old_cart_item_key';
		$new_cart_item_key = 'fake_new_cart_item_key';
		$mystyle_data      = 'fake_mystyle_data';
		$passed            = true;
		$product_id        = 1;
		$qty               = 1;
		$variation_id      = 1;
		$variations        = array();
		$cart_item_data    = array();

		// Mock woocommerce.
		$woocommerce                      = new MyStyle_MockWooCommerce();
		$woocommerce->cart->cart_contents = array(
			$old_cart_item_key => array(
				'mystyle_data' => $mystyle_data,
			),
			$new_cart_item_key => array(),
		);

		// Mock the $_REQUEST.
		$_REQUEST = array( 'tm_cart_item_key' => $new_cart_item_key );

		// Instantiate the SUT (System Under Test) class.
		$mystyle_tm_extra_product_options = new MyStyle_Tm_Extra_Product_Options();

		// Call the stash_mystyle_data function to stash the mystyle data.
		$ret = $mystyle_tm_extra_product_options->stash_mystyle_data(
			$passed,
			$product_id,
			$qty,
			$variation_id,
			$variations,
			$cart_item_data
		);

		// Call the function.
		$mystyle_tm_extra_product_options->copy_mystyle_data(
			$new_cart_item_key,
			$product_id,
			$qty,
			$variation_id,
			$variations,
			$cart_item_data
		);

		// Get the woocommerce cart.
		/* @var $cart \WC_Cart The WooCommerce Cart. */
		$cart = $woocommerce->cart;
		$cart->get_cart();

		// Get the mystyle_data.
		$new_mystyle_data = $cart->cart_contents[ $new_cart_item_key ]['mystyle_data'];

		// Assert that the mystyle data was set on the new cart item.
		$this->assertEquals( $mystyle_data, $new_mystyle_data );
	}

	/**
	 * Test that the is_tm_extra_product_options_edit_request function returns
	 * true when it is a TM Extra Product Options edit request.
	 */
	public function test_is_tm_extra_product_options_edit_request_returns_true() {
		// Set up the test data.
		$request = array( 'tm_cart_item_key' => 'fake_cart_item_key' );

		// Instantiate the SUT (System Under Test) class.
		$mystyle_tm_extra_product_options = new MyStyle_Tm_Extra_Product_Options();

		// Call the function.
		$ret = $mystyle_tm_extra_product_options->is_tm_extra_product_options_edit_request( $request );

		// Assert that the function returned true.
		$this->assertTrue( $ret );
	}

	/**
	 * Test that the is_tm_extra_product_options_edit_request function returns
	 * false when it is NOT a TM Extra Product Options edit request.
	 */
	public function test_is_tm_extra_product_options_edit_request_returns_false() {
		// Set up the test data.
		$request = array();

		// Instantiate the SUT (System Under Test) class.
		$mystyle_tm_extra_product_options = new MyStyle_Tm_Extra_Product_Options();

		// Call the function.
		$ret = $mystyle_tm_extra_product_options->is_tm_extra_product_options_edit_request( $request );

		// Assert that the function returned true.
		$this->assertFalse( $ret );
	}
}
