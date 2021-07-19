<?php
/**
 * The MyStyleDesignProfilePageTest class includes tests for testing the
 * MyStyle_Design_Profile_Page class.
 *
 * @package MyStyle
 * @since 1.4.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php';

/**
 * MyStyleDesignProfilePageTest class.
 */
class MyStyleDesignProfilePageTest extends WP_UnitTestCase {

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

		// Reset the server globals.
		$_SERVER['REQUEST_METHOD'] = 'GET';

		MyStyle_Design_Profile_Page::reset_instance();
	}

	/**
	 * Test the constructor.
	 *
	 * @global wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_design_profile_page = new MyStyle_Design_Profile_Page();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['init'] );
		$this->assertContains( 'init', $function_names );
	}

	/**
	 * Test the init function with.
	 *
	 * @global \stdClass $post
	 */
	public function test_init_with_valid_design_id() {
		global $post;

		// Default the response code to 200.
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( 200 );
		}

		$design_id = 1;

		// Create the Design Profile Page.
		$design_profile_page = MyStyle_Design_Profile_Page::create();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock the request uri  and post as though we were loading the design
		// profile page for design 1.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/' . $design_id;
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Get the Mystyle_Design_Profile page singleton.
		$mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();

		// Call the function.
		$mystyle_design_profile_page->init();

		// Get the current design from the singleton instance.
		$current_design = $mystyle_design_profile_page->get_design();

		// Assert that the page was created and has the expected title.
		$this->assertEquals( $design_id, $current_design->get_design_id() );

		// Assert that the http response code is set to 200.
		$this->assertEquals( 200, $mystyle_design_profile_page->get_http_response_code() );

		// Assert that the exception is null.
		$this->assertEquals( null, $mystyle_design_profile_page->get_exception() );
	}

	/**
	 * Test the init function with no design id.
	 *
	 * @global stdClass $post
	 */
	public function test_init_with_no_design_id() {
		global $post;

		if ( ! defined( 'MYSTYLE_DESIGNS_PER_PAGE' ) ) {
			define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
		}

		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Reset the singleton instance (to clear out any previously set values).
		MyStyle_Design_Profile_Page::reset_instance();

		// Create the Design Profile Page.
		$design_profile_page = MyStyle_Design_Profile_Page::create();

		// NOTE: we would normally create a design here but for this test,
		// the design doesn't exist.
		// mock the request uri and post as though we were loading the design
		// index.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/';
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Call the function.
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Get the Mystyle_Design_Profile page singleton.
		$mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();

		// Assert that no design is loaded.
		$this->assertNull( null, $mystyle_design_profile_page->get_design() );

		// Assert that the http response code is set to 200.
		$this->assertEquals( 200, $mystyle_design_profile_page->get_http_response_code() );

		$pager = $mystyle_design_profile_page->get_pager();

		$this->assertTrue( ! empty( $pager ) );
	}

	/**
	 * Test the init function.
	 *
	 * @global stdClass $post
	 */
	public function test_init_with_a_non_existant_design_id() {
		global $post;

		$design_id = 999;

		// Reset the singleton instance (to clear out any previously set values).
		MyStyle_Design_Profile_Page::reset_instance();

		// Create the Design Profile Page.
		$design_profile_page = MyStyle_Design_Profile_Page::create();

		// NOTE: we would normally create a design here but for this test,
		// the design doesn't exist.
		// mock the request uri  and post as though we were loading the design
		// profile page for design 1 (which doesn't exist).
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/' . $design_id;
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		// Call the function.
		MyStyle_Design_Profile_Page::get_instance()->init();

		// Get the Mystyle_Design_Profile page singleton.
		$mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();

		// Assert that no design is loaded.
		$this->assertNull( null, $mystyle_design_profile_page->get_design() );

		// Assert that the http response code is set to 404.
		$this->assertEquals( 404, $mystyle_design_profile_page->get_http_response_code() );

		// Assert that the exception is set.
		$this->assertEquals(
			'MyStyle_Not_Found_Exception',
			get_class( $mystyle_design_profile_page->get_exception() )
		);
	}

	/**
	 * Test the init function with.
	 *
	 * @global stdClass $post
	 */
	public function test_init_with_post_request() {
		global $post;

		// Default the response code to 200.
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( 200 );
		}

		$design_id        = 1;
		$new_design_title = 'New Design Title';

		// Create the Design Profile Page.
		$design_profile_page = MyStyle_Design_Profile_Page::create();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock the request uri and post as though we were loading the design
		// profile page for design 1.
		$_SERVER['REQUEST_URI']    = 'http://localhost/designs/' . $design_id;
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['ms_title']         = $new_design_title;
		$_REQUEST['_wpnonce']      = wp_create_nonce( 'mystyle_design_edit_nonce' );
		$post                      = new stdClass();
		$post->ID                  = MyStyle_Design_Profile_Page::get_id();

		// Get the Mystyle_Design_Profile page singleton.
		$mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();

		// Call the function.
		$mystyle_design_profile_page->init();

		// Get the current design from the singleton instance.
		$current_design = $mystyle_design_profile_page->get_design();

		// Assert that the http response code is set to 200.
		$this->assertEquals( 200, $mystyle_design_profile_page->get_http_response_code() );

		// Assert that the Design title was updated as expected.
		$this->assertEquals( $new_design_title, $current_design->get_title() );

		// Assert that the exception is null.
		$this->assertEquals( null, $mystyle_design_profile_page->get_exception() );
	}

	/**
	 * Test the create function.
	 */
	public function test_create() {
		// Create the MyStyle Design Profile page.
		$page_id = MyStyle_Design_Profile_Page::create();

		$page = get_post( $page_id );

		// Assert that the page was created and has the expected title.
		$this->assertEquals( 'Community Design Gallery', $page->post_title );
	}

	/**
	 * Test the get_id function.
	 */
	public function test_get_id() {
		// Create the MyStyle Design Profile page.
		$page_id1 = MyStyle_Design_Profile_Page::create();

		$page_id2 = MyStyle_Design_Profile_Page::get_id();

		// Assert that the page id was successfully retrieved.
		$this->assertEquals( $page_id2, $page_id1 );
	}

	/**
	 * Test the is_current_post function returns true when the current post is
	 * the Design Profile Page.
	 *
	 * @global stdClass $post
	 */
	public function test_is_current_post_returns_true_when_current_post() {
		global $post;

		// Create the Design Profile Page.
		$design_profile_page = MyStyle_Design_Profile_Page::create();

		// Mock the request uri.
		$_SERVER['REQUEST_URI'] = 'http://localhost/designs/';
		$post                   = new stdClass();
		$post->ID               = MyStyle_Design_Profile_Page::get_id();

		$this->assertTrue( MyStyle_Design_Profile_Page::is_current_post() );
	}

	/**
	 * Test the is_current_post function returns true when the current post is
	 * the Design Profile Page.
	 */
	public function test_is_current_post_returns_false_when_not_current_post() {

		// Create the Design Profile Page.
		$design_profile_page = MyStyle_Design_Profile_Page::create();

		$this->assertFalse( MyStyle_Design_Profile_Page::is_current_post() );
	}

	/**
	 * Test the exists function.
	 */
	public function test_exists() {

		// Assert that the exists function returns false before the page is created.
		$this->assertFalse( MyStyle_Design_Profile_Page::exists() );

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Assert that the exists function returns true after the page is created.
		$this->assertTrue( MyStyle_Design_Profile_Page::exists() );
	}

	/**
	 * Test the delete function.
	 */
	public function test_delete() {
		// Create the MyStyle Design Profile page.
		$page_id = MyStyle_Design_Profile_Page::create();

		// Delete the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::delete();

		// Attempt to get the page.
		$page = get_post( $page_id );

		// Assert that the page was deleted.
		$this->assertEquals( $page->post_status, 'trash' );
	}

	/**
	 * Test the get_index_url function without permalinks.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function test_get_index_url_without_permalinks() {
		global $wp_rewrite;

		// disable page permalinks.
		$wp_rewrite->page_structure = null;

		// Create the MyStyle Design Profile page.
		$page_id = MyStyle_Design_Profile_Page::create();

		// Build the expected url.
		$expected_url = 'http://example.org/?page_id=' . $page_id;

		// Call the function.
		$url = MyStyle_Design_Profile_Page::get_index_url();

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_index_url function with permalinks.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function test_get_index_url_with_permalinks() {
		global $wp_rewrite;

		// Enable page permalinks.
		$wp_rewrite->page_structure = '%pagename%';

		$design_id    = 1;
		$expected_url = 'http://example.org/designs';

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Call the function.
		$url = MyStyle_Design_Profile_Page::get_index_url();

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_design_url function without permalinks.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function test_get_design_url_without_permalinks() {
		global $wp_rewrite;

		// disable page permalinks.
		$wp_rewrite->page_structure = null;

		$design_id = 1;

		// Create the MyStyle Design Profile page.
		$page_id = MyStyle_Design_Profile_Page::create();

		// Build the expected url.
		$expected_url = 'http://example.org/?page_id=' . $page_id . '&design_id=1';

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$url = MyStyle_Design_Profile_Page::get_design_url( $design );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_design_url function with permalinks.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function test_get_design_url_with_permalinks() {
		global $wp_rewrite;

		// Enable page permalinks.
		$wp_rewrite->page_structure = '%pagename%';

		$design_id    = 1;
		$expected_url = 'http://example.org/designs/1';

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$url = MyStyle_Design_Profile_Page::get_design_url( $design );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_design_url function when the post has a custom slug.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function test_get_design_url_with_custom_slug() {
		global $wp_rewrite;

		$slug = 'widgets';

		// Enable page permalinks.
		$wp_rewrite->page_structure = '%pagename%';

		$design_id    = 1;
		$expected_url = 'http://example.org/' . $slug . '/1';

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Change to a custom slug.
		wp_update_post(
			array(
				'ID'        => MyStyle_Design_Profile_Page::get_id(),
				'post_name' => $slug,
			)
		);

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$url = MyStyle_Design_Profile_Page::get_design_url( $design );

		// Assert that the exepected $url was returned.
		$this->assertEquals( $expected_url, $url );
	}

	/**
	 * Test the get_design_id_url function without permalinks.
	 *
	 * @global WP_Query $wp_query
	 */
	public function test_get_design_id_from_url_without_permalinks() {
		global $wp_query;

		$design_id = 1;
		$query     = 'http://localhost/index.php?page_id=1&design_id=' . $design_id;

		// Init the mystyle frontend to register the design_id query var.
		if ( ! defined( 'MYSTYLE_DESIGNS_PER_PAGE' ) ) {
			define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
		}
		MyStyle_FrontEnd::get_instance();

		// Mock the current query.
		$wp_query = new WP_Query( $query );

		// Call the function.
		$returned_design_id = MyStyle_Design_Profile_Page::get_design_id_from_url();

		// Assert that the exepected design_id is returned.
		$this->assertEquals( $design_id, $returned_design_id );
	}

	/**
	 * Test the get_design_id_url function with permalinks.
	 */
	public function test_get_design_id_from_url_with_permalinks() {
		$design_id = 1;
		$query     = 'http://www.example.com/designs/' . $design_id;

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Mock the current query.
		$_SERVER['REQUEST_URI'] = $query;

		// Call the function.
		$returned_design_id = MyStyle_Design_Profile_Page::get_design_id_from_url();

		// Assert that the exepected design_id is returned.
		$this->assertEquals( $design_id, $returned_design_id );
	}

	/**
	 * Test the get_design_id_url function with a custom slug.
	 */
	public function test_get_design_id_from_url_with_a_custom_slug() {
		$design_id = 1;
		$slug      = 'widgets';
		$query     = 'http://www.example.com/' . $slug . '/' . $design_id;

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Change to a custom slug.
		wp_update_post(
			array(
				'ID'        => MyStyle_Design_Profile_Page::get_id(),
				'post_name' => $slug,
			)
		);

		// Mock the current query.
		$_SERVER['REQUEST_URI'] = $query;

		// Call the function.
		$returned_design_id = MyStyle_Design_Profile_Page::get_design_id_from_url();

		// Assert that the exepected design_id is returned.
		$this->assertEquals( $design_id, $returned_design_id );
	}

	/**
	 * Test the filter_title function.
	 *
	 * @global $post
	 * @global $wp_query
	 */
	public function test_filter_title() {
		global $post;
		global $wp_query;

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( 1 );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Instantiate the MyStyle Design Profile page.
		$mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();
		$mystyle_design_profile_page->set_design( $design );

		// Mock the post, etc.
		$post                  = new stdClass();
		$post->ID              = MyStyle_Design_Profile_Page::get_id();
		$wp_query->in_the_loop = true;

		// Call the function.
		$new_title = $mystyle_design_profile_page->filter_title( 'foo', MyStyle_Design_Profile_Page::get_id() );

		// Expected.
		$expected = 'Design ' . $design->get_design_id();

		// Assert that the title has been set as expected.
		$this->assertEquals( $expected, $new_title );
	}

	/**
	 * Test the filter_body_class function.
	 *
	 * @global $post
	 */
	public function test_filter_body_class_adds_class_to_design_profile_page() {
		global $post;

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create the MyStyle Design Profile page.
		MyStyle_Design_Profile_Page::create();

		// Mock the post and get vars.
		$post     = new stdClass();
		$post->ID = MyStyle_Design_Profile_Page::get_id();

		// Mock the $classes var.
		$classes = array();

		// Call the function.
		$returned_classes = MyStyle_Design_Profile_Page::get_instance()->filter_body_class( $classes );

		// Assert that the mystyle-design-profile class is added to the classes array.
		$this->assertEquals( 'mystyle-design-profile', $returned_classes[0] );
	}

	/**
	 * Assert that show_add_to_cart_button() function.
	 */
	public function test_show_add_to_cart() {
		// Set customize_page_title_hide.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['design_profile_page_show_add_to_cart'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$show_add_to_cart = MyStyle_Design_Profile_Page::show_add_to_cart_button();

		$this->assertTrue( $show_add_to_cart );
	}

}
