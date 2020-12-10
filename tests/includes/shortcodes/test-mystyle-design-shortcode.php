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
	 * Test the output function with valid design id in the query params and no
	 * shortcode attributes.
	 */
	public function test_output_with_valid_design_id_in_url() {
		// Set up the test data.
		$design_id = 1;
		$atts      = array();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Init the MyStyle_FrontEnd.
		MyStyle_FrontEnd::reset_instance();
		MyStyle_FrontEnd::get_instance()->set_design( $design );

		// Call the function.
		$output = MyStyle_Design_Shortcode::output( $atts );

		// Assert that the output includes an img tag.
		$this->assertContains( '<img', $output );
		$this->assertNotContains(
			'mystyle-design-profile-index-wrapper',
			$output
		);
	}

	/**
	 * Test the output function with valid design id in the shortcode
	 * attributes.
	 */
	public function test_output_with_valid_design_id_in_attr() {
		// Set up the test data.
		$design_id = 1;
		$atts      = array(
			'design_id' => $design_id,
		);

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$output = MyStyle_Design_Shortcode::output( $atts );

		// Assert that the output includes an img tag.
		$this->assertContains( '<img', $output );
		$this->assertNotContains(
			'mystyle-design-profile-index-wrapper',
			$output
		);
	}

	/**
	 * Test the output function with no design_id in the query params and no
	 * attributes. This should load the gallery.
	 */
	public function test_output_returns_gallery_when_no_design_id() {
		// Set up the test data.
		$atts = array();

		// Reset the MyStyle_FrontEnd.
		MyStyle_FrontEnd::reset_instance();

		// Call the function.
		$output = MyStyle_Design_Shortcode::output( $atts );

		$this->assertContains(
			'mystyle-design-profile-index-wrapper',
			$output
		);
	}

	/**
	 * Test the output function with a random gallery requested (via the
	 * shortcode attributes).
	 */
	public function test_output_with_random_gallery_request() {
		// Set up the test data.
		$atts = array(
			'gallery' => 1,
			'count'   => 6,
		);

		// Reset the MyStyle_FrontEnd.
		MyStyle_FrontEnd::reset_instance();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( 1 );

		// Create a real product for the design.
		$product_id = create_wc_test_product();
		$design->set_product_id( $product_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Create and init the MyStyle_Design_Profile page.
		MyStyle_Design_Profile_Page::create();
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Call the function.
		$output = MyStyle_Design_Shortcode::output( $atts );

		// Assert that the gallery is returned.
		$this->assertContains(
			'mystyle-design-profile-index-wrapper',
			$output
		);
	}

}
