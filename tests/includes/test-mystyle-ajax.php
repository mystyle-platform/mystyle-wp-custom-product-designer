<?php
/**
 * The MyStyleAjaxTest class includes tests for testing the
 * MyStyle_Ajax class.
 *
 * @package MyStyle
 * @since 3.18.3
 */

/**
 * MyStyleUserInterfaceTest class.
 *
 * @group ajax
 */
class MyStyleAjaxTest extends WP_Ajax_UnitTestCase {

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
	 * Test the design_access_change function when the current user is the
	 * admin.
	 */
	public function test_design_access_change_when_admin() {

		// Create an 'administrator' user and set them as the current user.
		$this->_setRole( 'administrator' );

		// Set up the test data.
		$design_id = 1;
		$user_id   = 2;

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		$design->set_access( MyStyle_Access::ACCESS_PUBLIC );
		MyStyle_DesignManager::persist( $design );

		// Mock the POST.
		$_POST['design_id']   = $design_id;
		$_POST['access_id']   = MyStyle_Access::ACCESS_PRIVATE;
		$_POST['_ajax_nonce'] = wp_create_nonce( 'mystyle_design_access_change_nonce' );

		MyStyle_Ajax::get_instance();

		try {
			// Call the function (args passed in via the mocked POST from above).
			$this->_handleAjax( 'mystyle_design_access_change' );
		} catch ( WPAjaxDieStopException $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing.
		}

		$this->assertTrue( isset( $ex ) );
		$this->assertEquals( '1', $ex->getMessage() );
	}

	/**
	 * Test the design_access_change function when the current user is not the
	 * user that owns the design.
	 */
	public function test_design_access_change_when_different_user() {

		// Create an 'administrator' user and set them as the current user.
		//$this->_setRole( 'administrator' );
		$this->_setRole( 'customer' );

		// Set up the test data.
		$design_id = 1;
		$user_id   = 999;

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		$design->set_access( MyStyle_Access::ACCESS_PUBLIC );
		MyStyle_DesignManager::persist( $design );

		// Mock the POST.
		$_POST['design_id']   = $design_id;
		$_POST['access_id']   = MyStyle_Access::ACCESS_PRIVATE;
		$_POST['_ajax_nonce'] = wp_create_nonce( 'mystyle_design_access_change_nonce' );

		MyStyle_Ajax::get_instance();

		try {
			// Call the function (args passed in via the mocked POST from above).
			$this->_handleAjax( 'mystyle_design_access_change' );
		} catch ( WPAjaxDieStopException $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing.
		}

		$this->assertTrue( isset( $ex ) );
		$this->assertEquals( '-1', $ex->getMessage() );
	}

	/**
	 * Test the design_tag_add function.
	 */
	public function test_design_tag_add() {

		// Set up the test data.
		$tag_name  = 'Test Tag';
		$design_id = 1;
		$user_id   = get_current_user_id();

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Mock the POST.
		$_POST['tag']       = $tag_name;
		$_POST['design_id'] = $design_id;
		$_POST['user_id']   = $user_id;

		// Call the ajax action.
		try {
			// Call the function (args passed in via the mocked POST from above).
			$this->_handleAjax( 'mystyle_design_tag_add' );
		} catch ( WPAjaxDieContinueException $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertTrue( $response->success );
		$this->assertEquals( $tag_name, $response->data->tag );
	}

	/**
	 * Test the design_tag_remove function.
	 */
	public function test_design_tag_remove() {

		// Set up the test data.
		$tag_name  = 'Test Tag';
		$design_id = 1;
		$user_id   = get_current_user_id();

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Add the tag to the design.
		$tag_id = MyStyle_DesignManager::add_tag_to_design(
			$design_id,
			$tag_name,
			$user
		);

		// Mock the POST.
		$_POST['tag']       = $tag_name;
		$_POST['design_id'] = $design_id;
		$_POST['user_id']   = $user_id;

		// Call the ajax action.
		try {
			// Call the function (args passed in via the mocked POST from above).
			$this->_handleAjax( 'mystyle_design_tag_remove' );
		} catch ( WPDieException $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertTrue( $response->success );
		$this->assertEquals( $tag_name, $response->data->tag );
	}

}
