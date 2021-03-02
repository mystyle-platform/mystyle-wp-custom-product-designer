<?php
/**
 * The MyStyleDesignTagTaxonomyTest class includes tests for testing the
 * MyStyle_Design_Tag_Taxonomy class.
 *
 * @package MyStyle
 * @since 3.16.7
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
	 * Test the register_taxonomy function.
	 */
	public function test_register_taxonomy() {

		$taxonomy = MyStyle_Design_Tag_Taxonomy::get_instance();

		// Call the function.
		$taxonomy->register_taxonomy();

		// Assert that the taxonomy now exists.
		$this->assertTrue( taxonomy_exists( MYSTYLE_TAXONOMY_NAME ) );
	}

}
