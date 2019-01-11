<?php
/**
 * The MyStyleDesignShortcodeTest class includes tests for testing the
 * MyStyle_Design_Shortcode class.
 *
 * @package MyStyle
 * @since 1.4.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleDesignShortcodeTest class.
 */
class MyStyleDesignShortcodeTest extends WP_UnitTestCase {

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
	 *
	 * @global $wpdb
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
	 */
	public function test_output_with_valid_design_id() {
		// Set up the data.
		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Init the MyStyle_FrontEnd.
		MyStyle_FrontEnd::reset_instance();
		MyStyle_FrontEnd::get_instance()->set_design( $design );

		// Call the function.
		$output = MyStyle_Design_Shortcode::output();

		// Assert that the output includes an img tag.
		$this->assertContains( '<img', $output );
	}

	/**
	 * Test the output function with no design loaded. Should exit without
	 * throwing an exception.
	 */
	public function test_output_with_no_design() {

		// Reset the MyStyle_FrontEnd.
		MyStyle_FrontEnd::reset_instance();

		// Call the function (should throw a MyStyle_Bad_Request_Exception).
		$output = MyStyle_Design_Shortcode::output();

		$this->assertEquals( '', $output );
	}

	/**
	 * Test the output_design function.
	 */
	public function test_output_design() {
		// Set up the data.
		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Init the MyStyle_FrontEnd.
		MyStyle_FrontEnd::reset_instance();
		MyStyle_FrontEnd::get_instance()->set_design( $design );

		// Call the function.
		$output = MyStyle_Design_Shortcode::output_design();

		// Assert that the output includes an img tag.
		$this->assertContains( '<img', $output );
	}

}
