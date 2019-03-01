<?php
/**
 * The MyStyleCustomizePageTest class includes tests for testing the
 * MyStyle_Customize_Page class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php';

/**
 * MyStyleCustomizePageTest class.
 */
class MyStyleCustomizePageTest extends WP_UnitTestCase {

	/**
	 * Test the create function
	 */
	public function test_create() {
		// Create the MyStyle Customize page.
		$page_id = MyStyle_Customize_Page::create();

		$page = get_post( $page_id );

		// Assert that the page was created and has the expected title.
		$this->assertEquals( 'Customize', $page->post_title );
	}

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		MyStyle_Customize_Page::get_instance();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );

		// Assert that the filter_title function is registered.
		$function_names = get_function_names( $wp_filter['the_title'] );
		$this->assertContains( 'filter_title', $function_names );

		// Assert that the filter_body_class function is registered.
		$function_names = get_function_names( $wp_filter['body_class'] );
		$this->assertContains( 'filter_body_class', $function_names );
	}

	/**
	 * Test the get_id function.
	 */
	public function test_get_id() {
		// Create the MyStyle Customize page.
		$page_id1 = MyStyle_Customize_Page::create();

		$page_id2 = MyStyle_Customize_Page::get_id();

		// Assert that the page id was successfully retrieved.
		$this->assertEquals( $page_id2, $page_id1 );
	}

	/**
	 * Test the exists function.
	 */
	public function test_exists() {

		// Assert that the exists function returns false before the page is created.
		$this->assertFalse( MyStyle_Customize_Page::exists() );

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Assert that the exists function returns true after the page is created.
		$this->assertTrue( MyStyle_Customize_Page::exists() );
	}

	/**
	 * Test the delete function.
	 */
	public function test_delete() {
		// Create the MyStyle Customize page.
		$page_id = MyStyle_Customize_Page::create();

		// Delete the MyStyle Customize page.
		MyStyle_Customize_Page::delete();

		// Attempt to get the page.
		$page = get_post( $page_id );

		// Assert that the page was deleted.
		$this->assertEquals( $page->post_status, 'trash' );
	}

	/**
	 * Test the get_design_url function.
	 */
	public function test_get_design_url() {

		// Create the MyStyle Customize page.
		$page_id = MyStyle_Customize_Page::create();

		// Build the expected url.
		$expected_url = 'http://example.org/?page_id=' . $page_id . '&product_id=0&design_id=1&h=eyJwb3N0Ijp7InF1YW50aXR5IjoxLCJhZGQtdG8tY2FydCI6MH19';

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( 1 );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Call the function.
		$url = MyStyle_Customize_Page::get_design_url( $design );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_design_url function when passed passthru data.
	 */
	public function test_get_design_url_with_passthru() {

		// Create the MyStyle Customize page.
		$page_id = MyStyle_Customize_Page::create();

		// Build the expected url.
		$expected_url = 'http://example.org/?page_id=' . $page_id . '&product_id=0&design_id=1&h=eyJwb3N0Ijp7InF1YW50aXR5Ijo3LCJhZGQtdG8tY2FydCI6MH19';

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( 1 );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Create the passthru.
		$passthru = array(
			'post' => array(
				'quantity'    => 7,
				'add-to-cart' => $design->get_product_id(),
			),
		);

		// Call the function.
		$url = MyStyle_Customize_Page::get_design_url( $design, null, $passthru );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_reload_url function for a design with cart_data.
	 */
	public function test_get_reload_url_for_design_with_cart_data() {

		// Create the MyStyle Customize page (needed for the url).
		MyStyle_Customize_Page::create();

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( 1 );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Add the cart data to the design.
		$obj                  = new stdClass();
		$obj->quantity        = 1;
		$obj->{'add-to-cart'} = 2;
		$cart_data            = wp_json_encode( $obj );
		$design->set_cart_data( $cart_data );

		// Call the function.
		$url = MyStyle_Customize_Page::get_design_url( $design );

		// Assert that the expected page_id parameter is included in the url.
		$this->assertContains( 'page_id=' . MyStyle_Customize_Page::get_id(), $url );

		// Assert that the expected design_id parameter is included in the url.
		$this->assertContains( 'design_id=' . $design->get_design_id(), $url );
	}

	/**
	 * Test the filter_title function.
	 */
	public function test_filter_title() {
		global $post;
		global $wp_query;

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Mock the post, etc.
		$post                  = new stdClass();
		$post->ID              = MyStyle_Customize_Page::get_id();
		$wp_query->in_the_loop = true;

		// Enable the hide title option.
		$options                              = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options['customize_page_title_hide'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Call the function.
		$new_title = MyStyle_Customize_Page::get_instance()->filter_title( 'foo', MyStyle_Customize_Page::get_id() );

		// Assert that the title has been set to the empty string.
		$this->assertEquals( '', $new_title );
	}

	/**
	 * Test the filter_body_class function.
	 */
	public function test_filter_body_class_adds_class_to_customize_page() {
		global $post;

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Mock the post and get vars.
		$post               = new stdClass();
		$post->ID           = MyStyle_Customize_Page::get_id();
		$_GET['product_id'] = 1;

		// Mock the $classes var.
		$classes = array();

		// Call the function.
		$returned_classes = MyStyle_Customize_Page::get_instance()->filter_body_class( $classes );

		// Assert that the mystyle-customize class is added to the classes array.
		$this->assertEquals( 'mystyle-customize', $returned_classes[0] );
	}

	/**
	 * Test the enqueue_scripts function.
	 *
	 * @global $post
	 * @global $wp_scripts
	 */
	public function test_enqueue_scripts() {
		global $post;
		global $wp_scripts;

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Mock the post and get vars.
		$post               = new stdClass();
		$post->ID           = MyStyle_Customize_Page::get_id();
		$_GET['product_id'] = 1;

		// Call the function.
		$returned_classes = MyStyle_Customize_Page::get_instance()->enqueue_scripts();

		// Assert that the customize.js script is registered.
		$this->assertContains(
			'mystyle-customize',
			serialize( $wp_scripts ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		);
	}

	/**
	 * Assert the hide_title() function.
	 */
	public function test_hide_title() {
		// Set customize_page_title_hide setting.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['customize_page_title_hide'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$hide_title = MyStyle_Customize_Page::hide_title();

		$this->assertTrue( $hide_title );
	}

	/**
	 * Assert the disable_viewport_rewrite() function.
	 */
	public function test_disable_viewport_rewrite() {
		// Set customize_page_disable_viewport_rewrite.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['customize_page_disable_viewport_rewrite'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$disable_viewport_rewrite = MyStyle_Customize_Page::disable_viewport_rewrite();

		$this->assertTrue( $disable_viewport_rewrite );
	}

}
