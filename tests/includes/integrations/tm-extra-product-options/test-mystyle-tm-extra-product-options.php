<?php
/**
 * The MyStyleTmExtraProductOptionsTest class includes tests for testing the
 * MyStyle_Tm_Extra_Product_Options class.
 *
 * @package MyStyle
 * @since 3.13.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . '../woocommerce/woocommerce.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php';

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
	}
}
