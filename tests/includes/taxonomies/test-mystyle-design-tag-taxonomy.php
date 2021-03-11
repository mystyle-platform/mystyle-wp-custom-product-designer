<?php
/**
 * The MyStyleDesignTagTaxonomyTest class includes tests for testing the
 * MyStyle_Design_Tag_Taxonomy class.
 *
 * @package MyStyle
 * @since 3.17.0
 */

/**
 * MyStyleDesignTagTaxonomyTest class.
 */
class MyStyleDesignTagTaxonomyTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$taxonomy = new MyStyle_Design_Tag_Taxonomy();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );
	}

	/**
	 * Test the exists function.
	 */
	public function test_exists() {

		$taxonomy = MyStyle_Design_Tag_Taxonomy::get_instance();

		// Call the function.
		$ret = $taxonomy->exists();

		// Assert that the function returned true as expected.
		$this->assertTrue( $ret );
	}

	/**
	 * Test the register function.
	 */
	public function test_register() {

		$taxonomy = MyStyle_Design_Tag_Taxonomy::get_instance();

		// Call the function.
		$taxonomy->register();

		// Assert that the taxonomy now exists.
		$this->assertTrue( taxonomy_exists( MYSTYLE_TAXONOMY_NAME ) );
	}

}
