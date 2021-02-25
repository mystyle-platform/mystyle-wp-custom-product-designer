<?php
/**
 * The MyStyleDesignProfileShortcodeTest class includes tests for testing the
 * MyStyle_Design_Profile_Shortcode class.
 *
 * @package MyStyle
 * @since 1.4.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleDesignProfileShortcodeTest class.
 */
class MyStyleDesignProfileShortcodeTest extends WP_UnitTestCase {

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
	 * Overwrite the tearDown function to remove our custom tables.
	 */
	public function tearDown() {
		global $wpdb;
		// Perform the actual task according to parent class.
		parent::tearDown();

		// Drop the tables that we created.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Design::get_table_name() );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Session::get_table_name() );
	}

	/**
	 * Test the output function with valid design id.
	 *
	 * @global stdClass $post
	 */
	public function test_output_with_valid_design_id() {
		global $post;

		// Reset the singleton instance of the design profile page (to clear out
		// any previously set values).
		MyStyle_Design_Profile_Page::reset_instance();

		// Set up the data.
		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Create a real product for the design.
		$product_id = create_wc_test_product();
		$design->set_product_id( $product_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create the MyStyle_Design_Profile page.
		MyStyle_Design_Profile_Page::create();

		// Set the current post to the Design_Profile_Page.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/1';
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Init the MyStyle_Design_Profile_Page.
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Call the function.
		$output = MyStyle_Design_Profile_Shortcode::output();

		// Assert that the output includes an img tag.
		$this->assertContains( '<img', $output );
	}

	/**
	 * Test the output function with no design id. Should load the design
	 * index.
	 *
	 * @global stdClass $post
	 */
	public function test_output_with_no_design_id() {
		global $post;

		if ( ! defined( 'MYSTYLE_DESIGNS_PER_PAGE' ) ) {
			define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
		}

		$design_id = 1;

		// Create the MyStyle_Design_Profile page.
		MyStyle_Design_Profile_Page::create();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Create a real product for the design.
		$product_id = create_wc_test_product();
		$design->set_product_id( $product_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Reset the singleton instance (to clear out any previously set
		// values).
		MyStyle_Design_Profile_Page::reset_instance();

		// Mock the request uri.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/';
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Init the MyStyle_Design_Profile_Page.
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Call the function.
		$output = MyStyle_Design_Profile_Shortcode::output();

		// Assert that the output is as expected.
		$this->assertContains( 'mystyle-design-profile-index-wrapper', $output );
		$this->assertContains( 'Custom Test Product <span>1</span>', $output );
	}

	/**
	 * Test the output function with an invalid design id.
	 *
	 * @global stdClass $post
	 */
	public function test_output_with_an_invalid_design_id() {
		global $post;

		// Create the MyStyle_Design_Profile page.
		MyStyle_Design_Profile_Page::create();

		// Mock the request uri.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/999';
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Reset the singleton instance (clear out any previously set values).
		MyStyle_Design_Profile_Page::reset_instance();

		// Init the MyStyle_Design_Profile_Page.
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Call the function.
		$output = MyStyle_Design_Profile_Shortcode::output();

		// Assert that the output includes includes 'Design Not Found'.
		$this->assertContains( 'Design not found', $output );
	}

	/**
	 * Test the output function displays a custom design title (if set).
	 *
	 * @global stdClass $post
	 */
	public function test_output_index_displays_design_title() {
		global $post;

		if ( ! defined( 'MYSTYLE_DESIGNS_PER_PAGE' ) ) {
			define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
		}

		$design_id = 1;
		$title     = 'Test Title';

		// Create the MyStyle_Design_Profile page.
		MyStyle_Design_Profile_Page::create();

		// Create a design (with a custom title).
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_title( $title );

		// Create a real product for the design.
		$product_id = create_wc_test_product();
		$design->set_product_id( $product_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Reset the singleton instance (to clear out any previously set
		// values).
		MyStyle_Design_Profile_Page::reset_instance();

		// Mock the request uri.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/';
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Init the MyStyle_Design_Profile_Page.
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Call the function.
		$output = MyStyle_Design_Profile_Shortcode::output();

		// Assert that the output includes the custom title as expected.
		$this->assertContains( 'Test Title', $output );
	}

}
