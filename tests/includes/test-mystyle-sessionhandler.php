<?php
/**
 * The MyStyleSessionHandlerTest class includes tests for testing the
 * MyStyle_SessionHandler
 * class.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyleSessionHandlerTest class.
 */
class MyStyleSessionHandlerTest extends WP_UnitTestCase {

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
	 * Test the get function.
	 *
	 * @global \wpdb $wpdb
	 */
	public function test_get_generates_new_session_if_one_doesnt_exist() {

		// Assert that the $_SESSION variable isn't set.
		unset( $_SESSION[ MyStyle_Session::SESSION_KEY ] );
		$this->assertFalse( isset( $_SESSION[ MyStyle_Session::SESSION_KEY ] ) );
		MyStyle_SessionHandler::reset_instance();
		MyStyle_SessionHandler::get_instance()->disable_cookies();

		// Call the function.
		$returned_session = MyStyle_SessionHandler::get_instance()->get();

		// Assert that the session_id is set.
		$this->assertNotNull( $returned_session->get_session_id() );

		// Assert that the $_SESSION variable is now set.
		$this->assertTrue( isset( $_SESSION[ MyStyle_Session::SESSION_KEY ] ) );
	}

	/**
	 * Test the get function.
	 *
	 * @global \wpdb $wpdb
	 */
	public function test_get_returns_existing_persisted_session() {
		global $wpdb;

		// Clear everything out.
		unset( $_SESSION[ MyStyle_Session::SESSION_KEY ] );
		MyStyle_SessionHandler::reset_instance();
		MyStyle_SessionHandler::get_instance()->disable_cookies();

		$session_id = 'testsession';

		// Create and persist the session.
		$session = MyStyle_Session::create( $session_id );
		MyStyle_SessionManager::persist( $session );

		// Set the session variable.
		$_SESSION[ MyStyle_Session::SESSION_KEY ] = $session;

		// Call the function.
		$returned_session = MyStyle_SessionHandler::get_instance()->get();

		// Assert that the session_id is set.
		$this->assertEquals( $session_id, $returned_session->get_session_id() );
	}

	/**
	 * Test the persist function.
	 *
	 * @global \wpdb $wpdb
	 */
	public function test_persist() {
		global $wpdb;

		// Clear everything out.
		unset( $_SESSION[ MyStyle_Session::SESSION_KEY ] );
		MyStyle_SessionHandler::reset_instance();
		MyStyle_SessionHandler::get_instance()->disable_cookies();

		$session_id = 'testsession';

		// Create a session.
		$session = MyStyle_Session::create( $session_id );

		// Init and get the session handler.
		$session_handler = MyStyle_SessionHandler::get_instance();

		// Set the session variable.
		$_SESSION[ MyStyle_Session::SESSION_KEY ] = $session;

		// Assert that the session is not yet persisted.
		$this->assertFalse( $session_handler->get()->is_persistent() );

		// Call the function.
		$returned_session = $session_handler->persist( $session );

		// Assert that the session was persisted.
		$this->assertTrue( $returned_session->is_persistent() );

		// Get the session from the db.
		$session_from_db = MyStyle_SessionManager::get( $session_id );

		// Assert that the session was persisted to the db.
		$this->assertEquals( $session_id, $session_from_db->get_session_id() );
	}

}
