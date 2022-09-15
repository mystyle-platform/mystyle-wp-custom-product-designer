<?php
/**
 * The MyStyleDesignTagIndexPageTest class includes tests for testing the
 * MyStyle_Design_Tag_Index_Page class.
 *
 * @package MyStyle
 * @since 4.0.0
 */

/**
 * MyStyleDesignTagIndexPageTest class.
 */
class MyStyleDesignTagIndexPageTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();
		// Remove filters that will create temporary tables. So that permanent
		// tables will be created.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		// Create the tables.
		MyStyle_Install::create_tables();
	}

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$page = new MyStyle_Design_Tag_Index_Page();

		// Assert that the object is instantiated as expected.
		$this->assertEquals(
			'MyStyle_Design_Tag_Index_Page',
			get_class( $page )
		);

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );
	}

	/**
	 * Test the exists function.
	 */
	public function test_exists() {

		// Assert that the exists function returns false before the page is
		// created.
		$this->assertFalse( MyStyle_Design_Tag_Index_Page::exists() );

		// Create the MyStyle Design Tag page.
		MyStyle_Design_Tag_Index_Page::create();

		// Assert that the exists function returns true after the page is
		// created.
		$this->assertTrue( MyStyle_Design_Tag_Index_Page::exists() );
	}

	/**
	 * Test the get_id function.
	 */
	public function test_get_id() {
		// Create the MyStyle Design Tag page.
		$page_id1 = MyStyle_Design_Tag_Index_Page::create();

		$page_id2 = MyStyle_Design_Tag_Index_Page::get_id();

		// Assert that the page id was successfully retrieved.
		$this->assertEquals( $page_id2, $page_id1 );
	}

	/**
	 * Test the create function.
	 */
	public function test_create() {
		// Create the MyStyle_Design_Tag_Index_Page page.
		$page_id = MyStyle_Design_Tag_Index_Page::create();

		$page = get_post( $page_id );

		// Assert that the page was created and has the expected title.
		$this->assertEquals( 'Design Tags Index', $page->post_title );
	}

	/**
	 * Test the upgrade function.
	 */
	public function test_upgrade() {
		// Set up the test data.
		$old_version    = '3.19.1';
		$new_version    = '3.19.2';
		$current_title  = 'Old Title';
		$upgraded_title = 'Design Tags Index';

		// Create the MyStyle_Design_Tag_Index_Page page.
		$page_id = MyStyle_Design_Tag_Index_Page::create();

		$page = get_post( $page_id );

		$page->post_title = $current_title;

		// Assert that the page doesn't have the correct title..
		$this->assertNotEquals( $upgraded_title, $page->post_title );

		// Call the upgrade function.
		MyStyle_Design_Tag_Index_Page::upgrade( $old_version, $new_version );

		// Get the page again.
		$upgraded_page = get_post( $page_id );

		// Assert that the page now has the correct title.
		$this->assertEquals( $upgraded_title, $upgraded_page->post_title );
	}

}
