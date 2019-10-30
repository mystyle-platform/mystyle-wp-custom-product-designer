<?php
/**
 * The MyStyleWpmlTest class includes tests for testing the
 * MyStyle_Wpml class.
 *
 * @package MyStyle
 * @since 3.13.1
 */

/**
 * MyStyleWpmlTest class.
 */
class MyStyleWpmlTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 */
	public function test_constructor() {

		$mystyle_wpml = new MyStyle_Wpml();

		// Assert that the object was instantiated as expected.
		$this->assertEquals(
			'MyStyle_Wpml',
			get_class( $mystyle_wpml )
		);
	}
}
