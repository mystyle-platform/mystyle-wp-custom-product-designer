<?php
/**
 * The MyStyleWpRestApiDesignControllerTest class includes tests for testing the
 *  MyStyle_Wp_Rest_Api_Design_Controller class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'wprestapi/class-mystyle-wp-rest-api-design-controller.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleWpRestApiDesignControllerTest class.
 */
class MyStyleWpRestApiDesignControllerTest extends WP_UnitTestCase {

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
	 */
	public function test_constructor() {
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Assert that the class was instantiated as expected.
		$this->assertEquals(
			'MyStyle_Wp_Rest_Api_Design_Controller',
			get_class( $controller )
		);
	}

	/**
	 * Test the get_items function.
	 */
	public function test_get_items() {

		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock the request.
		$request = new WP_REST_Request( 'GET' );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->get_items( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
		$this->assertEquals( $design_id, $response->data[0]['design_id'] );
	}

	/**
	 * Test the get_item function.
	 */
	public function test_get_item() {

		$tag_name  = 'Test Tag';
		$design_id = 1;
		$user_id   = 1;

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $design->get_user_id();

		// Give the Design some tags.
		MyStyle_DesignManager::add_tag_to_design(
			$design->get_design_id(),
			$tag_name,
			$user
		);

		// Mock the request.
		$request = new WP_REST_Request( 'GET' );
		$request->set_param( 'id', $design_id );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->get_item( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
		$this->assertEquals( $design_id, $response->data['design_id'] );
		$this->assertEquals( 'test-tag', $response->data['tags'][0]['slug'] );
	}

	/**
	 * Test the create_item function.
	 */
	public function test_create_item() {

		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Mock the request.
		$request = new WP_REST_Request( 'POST' );
		$request->set_body( wp_json_encode( $design->json_encode() ) );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->create_item( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
		$this->assertEquals( $design_id, $response->data['design_id'] );
	}

	/**
	 * Test the create_item function for design input that includes design tags.
	 */
	public function test_create_item_with_tags() {

		$design_id = 1;
		$tag1      = 'tag1';
		$tag2      = 'tag2';

		// Create a design.
		$design             = MyStyle_MockDesign::get_mock_design( $design_id );
		$design_arr         = $design->json_encode();
		$design_arr['tags'] = array( 'tag1', 'tag2' );

		// Mock the request.
		$request = new WP_REST_Request( 'POST' );
		$request->set_body( wp_json_encode( $design_arr ) );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->create_item( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
		$this->assertEquals( $design_id, $response->data['design_id'] );
		$this->assertEquals( 2, count( $response->data['tags'] ) );
		$this->assertEquals( $tag1, $response->data['tags'][0]['slug'] );
		$this->assertEquals( $tag2, $response->data['tags'][1]['slug'] );
	}

	/**
	 * Test the update_item function.
	 */
	public function test_update_item() {

		$design_id       = 1;
		$new_description = 'new description';

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Assert that the design starts off as expected
		$this->assertEquals( 'test description', $design->get_description() );

		// Mock some updated data.
		$design_data                = $design->json_encode();
		$design_data['description'] = $new_description;

		// Mock the request.
		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'id', $design_id );
		$request->set_body( wp_json_encode( $design_data ) );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->update_item( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
		$this->assertEquals( $design_id, $response->data['design_id'] );
		$this->assertEquals( $new_description, $response->data['description'] );
	}

	/**
	 * Test the update_item function with a design and update that include tags.
	 */
	public function test_update_item_with_tags() {

		$design_id  = 1;
		$user_id    = get_current_user_id();
		$start_tags = array( 'tag1', 'tag2' );
		$new_tags   = array( 'tag1', 'tag3', 'tag4' );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Add the tags to the design.
		MyStyle_DesignManager::update_design_tags(
			$design_id,
			$start_tags,
			$user
		);

		// Assert that the design starts off with the expected tags.
		$tags = MyStyle_DesignManager::get_design_tags( $design_id );
		$this->assertEquals( 2, count( $tags ) );
		$this->assertEquals( 'tag1', $tags[0] );

		// Mock some updated data.
		$design_data         = $design->json_encode();
		$design_data['tags'] = $new_tags;

		// Mock the request.
		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'id', $design_id );
		$request->set_body( wp_json_encode( $design_data ) );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->update_item( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
		$this->assertEquals( $design_id, $response->data['design_id'] );
		$this->assertEquals( 3, count( $response->data['tags'] ) );
	}

	/**
	 * Test the delete_item function.
	 */
	public function test_delete_item() {

		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock the request.
		$request = new WP_REST_Request( 'DELETE' );
		$request->set_param( 'id', $design_id );

		// Instantiate the SUT (System Under Test) class.
		$controller = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Call the function.
		/* @var $response \WP_REST_Response The response. */
		$response = $controller->delete_item( $request );

		// Assert that the response is returned as expected.
		$this->assertEquals( 'WP_REST_Response', get_class( $response ) );
		$this->assertEquals( 200, $response->status );
	}

}
