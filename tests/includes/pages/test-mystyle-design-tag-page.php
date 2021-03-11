<?php
/**
 * The MyStyleDesignTagPageTest class includes tests for testing the
 * MyStyle_Design_Tag_Page class.
 *
 * @package MyStyle
 * @since 3.17.0
 */

/**
 * MyStyleDesignTagPageTest class.
 */
class MyStyleDesignTagPageTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$page = new MyStyle_Design_Tag_Page();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );
	}

	/**
	 * Test the get_tag_url function.
	 */
	public function test_get_tag_url() {
		// Set up the test data.
		$tag          = 'testtag';
		$expected_url = 'http://example.org/design-tags/testtag';

		// Call the function.
		$url = MyStyle_Design_Tag_Page::get_tag_url( $tag );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

}
