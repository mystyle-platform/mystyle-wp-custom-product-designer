<?php
/**
 * The MyStyleDesignComplete class includes tests for testing the
 * MyStyle_Design_Complete class.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-design-complete.php';

/**
 * MyStyleDesignCompleteTest class.
 */
class MyStyleDesignCompleteTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();

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
	 * Test the constructor.
	 *
	 * @global $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		// Call the constructor.
		$mystyle_design_complete = new MyStyle_Design_Complete();

		// Assert that the filter_cart_button_text function is registered.
		$function_names = get_function_names( $wp_filter['query_vars'] );
		$this->assertContains( 'add_query_vars_filter', $function_names );

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['wp_enqueue_scripts'] );
		$this->assertContains( 'enqueue_scripts', $function_names );
	}

	/**
	 * Test the enqueue_scripts function.
	 *
	 * @global $wp_scripts
	 */
	public function test_enqueue_scripts() {
		global $wp_scripts;

		// Instantiate the SUT ( System Under Test ) class.
		$mystyle_design_complete = new MyStyle_Design_Complete();

		// Mock the query var.
		set_query_var( 'design_complete', 1 );

		// Call the method.
		$mystyle_design_complete->enqueue_scripts();

		// Assert that the design-complete.js script is registered.
		$this->assertContains(
			'mystyle-design-complete',
			serialize( $wp_scripts ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		);
	}

	/**
	 * Test the add_query_vars_filter function.
	 */
	public function test_add_query_vars_filter() {

		$vars[] = array();

		// Call the function.
		$ret_vars = MyStyle_Design_Complete::get_instance()->add_query_vars_filter( $vars );

		$this->assertTrue( in_array( 'design_complete', $ret_vars, true ) );
	}

	/**
	 * Assert that get_redirect_url() returns the expected URL when using a
	 * simple URL.
	 */
	public function test_get_redirect_url_with_simple_url() {
		// Install the alternate_design_complete_redirect_url.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['alternate_design_complete_redirect_url'] = 'http://www.example.com';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( 1 );

		$url = MyStyle_Design_Complete::get_redirect_url( $design );

		$this->assertEquals(
			'http://www.example.com?design_id=1&design_complete=1',
			$url
		);
	}

	/**
	 * Assert that get_redirect_url() returns the expected URL when using a URL
	 * that already includes a query string.
	 */
	public function test_get_redirect_url_with_url_that_includes_a_query_string() {
		// Install a global redirect url.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['alternate_design_complete_redirect_url'] = 'http://www.example.com?foo=bar';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( 1 );

		// Create a product and set the product specific redirect url.
		$product_id = create_wc_test_product();
		$product    = new \WC_Product_Simple( $product_id );
		add_post_meta(
			$product_id,
			'_mystyle_customizer_redirect',
			'http://www.example.net?foo=bar'
		);
		$design->set_product_id( $product_id );

		$url = MyStyle_Design_Complete::get_redirect_url( $design );

		$this->assertEquals(
			'http://www.example.net?foo=bar&design_id=1&design_complete=1',
			$url
		);
	}

	/**
	 * Assert that get_redirect_url() returns the expected URL when a product
	 * specific URL is set.
	 */
	public function test_get_redirect_url_with_product_specific_url() {
		// Install the alternate_design_complete_redirect_url.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['alternate_design_complete_redirect_url'] = 'http://www.example.com';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( 1 );

		// Set a redirect url on the design's product.

		$url = MyStyle_Design_Complete::get_redirect_url( $design );

		$this->assertEquals(
			'http://www.example.com?design_id=1&design_complete=1',
			$url
		);
	}

}
