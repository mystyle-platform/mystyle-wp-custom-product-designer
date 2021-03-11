<?php
/**
 * The MyStyleAuthorDesignsPageTest class includes tests for testing the
 * MyStyle_Author_Designs_Page class.
 *
 * @package MyStyle
 * @since 3.17.0
 */

/**
 * MyStyleAuthorDesignsPageTest class.
 */
class MyStyleAuthorDesignsPageTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$page = new MyStyle_Author_Designs_Page();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );
	}

	/**
	 * Test the get_author_url function.
	 */
	public function test_get_author_url() {
		// Set up the test data.
		$author       = 'testauthor';
		$expected_url = 'http://example.org/author/testauthor/designs/';

		// Call the function.
		$url = MyStyle_Author_Designs_Page::get_author_url( $author );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

}
