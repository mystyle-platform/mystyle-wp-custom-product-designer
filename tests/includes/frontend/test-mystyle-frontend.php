<?php
/**
 * The FrontEndTest class includes tests for testing the MyStyle_FrontEnd class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php';

/**
 * MyStyleFrontEndTest class.
 */
class MyStyleFrontEndTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();
		// Remove filters that will create temporary tables. So that permanent tables will be created.
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
	 * Test the constructor.
	 *
	 * @global $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_frontend = new MyStyle_FrontEnd();

		// Assert that the add_query_vars_filter function is registered.
		$function_names = get_function_names( $wp_filter['query_vars'] );
		$this->assertContains( 'add_query_vars_filter', $function_names );

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );

		// Assert that the add_query_vars_filter function is registered.
		$function_names = get_function_names( $wp_filter['template_redirect'] );
		$this->assertContains( 'init_vars', $function_names );
	}

	/**
	 * Test the init function.
	 *
	 * @global $wp_scripts
	 * @global $wp_styles
	 */
	public function test_init() {
		global $wp_scripts;
		global $wp_styles;

		// Call the function.
		MyStyle_Frontend::get_instance()->init();

		// Assert that our scripts are registered.
		$this->assertContains( 'swfobject', serialize( $wp_scripts ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

		// Assert that our stylesheets are registered.
		$this->assertContains( 'myStyleFrontendStylesheet', serialize( $wp_styles ) );  // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
	}

	/**
	 * Test the init_vars when design_id in query string.
	 */
	public function test_init_vars_with_design_id_in_query_string() {
		$design_id = 1;

		// Mock the query var.
		set_query_var( 'design_id', $design_id );

		// Get the SUT ( System Under Test ) class.
		$frontend = MyStyle_Frontend::get_instance();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$frontend->init_vars();

		// Get the current design from the singleton instance.
		$current_design = $frontend->get_design();

		// Assert that the page was created and has the expected title.
		$this->assertEquals( $design_id, $current_design->get_design_id() );
	}

	/**
	 * Test the init_vars when design_id NOT in query string. It should just
	 * quit and not return an error code.
	 */
	public function test_init_vars_with_design_id_not_in_query_string() {
		// Get the SUT (System Under Test) class.
		$frontend = MyStyle_Frontend::get_instance();

		// Call the function.
		$frontend->init_vars();

		// Assert that the class doesn't result in an error response.
		$this->assertEquals( 200, $frontend->get_http_response_code() );
	}

	/**
	 * Test the add_query_vars_filter function.
	 */
	public function test_add_query_vars_filter() {
		$mystyle_frontend = new MyStyle_FrontEnd();

		$vars[] = array();

		// Call the function.
		$ret_vars = MyStyle_FrontEnd::get_instance()->add_query_vars_filter( $vars );

		$this->assertTrue( in_array( 'design_id', $ret_vars, true ) );
	}

}
