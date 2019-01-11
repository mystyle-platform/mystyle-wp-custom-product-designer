<?php
/**
 * The MyStyleSessionManagerTest class includes tests for testing the
 * MyStyle_SessionManager
 * class.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleSessionManagerTest class.
 */
class MyStyleSessionManagerTest extends WP_UnitTestCase {

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
	 * Test the persist function.
	 *
	 * @global \wpdb $wpdb
	 */
	public function test_persist() {
		global $wpdb;

		$session_id = 'testsession';

		// Create a session.
		$session = MyStyle_Session::create( $session_id );

		// Call the function.
		MyStyle_SessionManager::persist( $session );

		// Assert that the session is marked as persisted.
		$this->assertTrue( $session->is_persistent() );
	}

	/**
	 * Test the get function.
	 *
	 * @global wpdb $wpdb
	 */
	public function test_get() {
		global $wpdb;

		$session_id = 'testsession';

		// Create the session.
		$session = MyStyle_Session::create( $session_id );

		// Persist the session.
		MyStyle_SessionManager::persist( $session );

		// Call the function.
		$session_from_db = MyStyle_SessionManager::get( $session_id );

		// Assert that the session_id is set.
		$this->assertEquals( $session_id, $session_from_db->get_session_id() );
	}

	/**
	 * Test the update function.
	 *
	 * @global wpdb $wpdb
	 */
	public function test_update() {
		global $wpdb;

		$session_id = 'testsession';

		// Create the session.
		$session = MyStyle_Session::create( $session_id );

		// Persist the session.
		MyStyle_SessionManager::persist( $session );

		// Get the persisted session.
		$session_from_db = MyStyle_SessionManager::get( $session_id );

		// Get the modified date.
		$modified_orig = $session_from_db->get_modified();

		// Wait 1.5 seconds.
		sleep( 1.5 );

		// Call the update function.
		$session_from_db = MyStyle_SessionManager::update( $session );

		// Assert that the session is marked as persistent.
		$this->assertTrue( $session_from_db->is_persistent() );

		// Get the new modified date.
		$modified_updated = $session_from_db->get_modified();

		$this->assertNotEquals( $modified_updated, $modified_orig );
	}

	/**
	 * Test the purge_abandoned_sessions() function.
	 *
	 * @global \wpdb $wpdb
	 */
	public function test_purge_abandoned_sessions() {
		global $wpdb;

		$session_id_1           = 'testsession1';
		$design_id_1            = 0;
		$abandoned_session_id_1 = 'abandonedsession1';
		$abandoned_session_id_2 = 'abandonedsession2';

		// Assert that there are 0 sessions currently in the db.
		$this->assertEquals( 0, $this->get_session_count() );

		// Create and persist a session with a design.
		$session_1 = MyStyle_Session::create( $session_id_1 );
		MyStyle_SessionManager::persist( $session_1 );
		$design = MyStyle_MockDesign::get_mock_design( $design_id_1 );
		$design->set_session_id( $session_id_1 );
		MyStyle_DesignManager::persist( $design );

		// Create and persist 2 abandoned sessions.
		$abandoned_session_1 = MyStyle_Session::create( $abandoned_session_id_1 );
		MyStyle_SessionManager::persist( $abandoned_session_1 );
		$abandoned_session_2 = MyStyle_Session::create( $abandoned_session_id_2 );
		MyStyle_SessionManager::persist( $abandoned_session_2 );

		// Assert that there are now 3 sessions in the db.
		$this->assertEquals( 3, $this->get_session_count() );

		// Run the function.
		MyStyle_SessionManager::purge_abandoned_sessions();

		// Assert that there is now 1 session in the db.
		$this->assertEquals( 1, $this->get_session_count() );

		// Get the session with a design from the db.
		$session_from_db = MyStyle_SessionManager::get( $session_id_1 );

		// Assert that the session with the design is still in the db.
		$this->assertEquals( $session_id_1, $session_from_db->get_session_id() );

		// Attempt to get the abandoned session from the db.
		$session_from_db = MyStyle_SessionManager::get( $abandoned_session_id_1 );

		// Assert that the abandoned session is no longer in the db.
		$this->assertNull( $session_from_db );
	}

	/**
	 * Private helper function that returns the total number of sessions
	 * currently in the database.
	 *
	 * @global \wpdb $wpdb
	 * @return integer Returns the number of sessions currently in the database.
	 */
	private function get_session_count() {
		global $wpdb;

		$sql = 'SELECT COUNT(*) FROM ' . MyStyle_Session::get_table_name();

		return intval( $wpdb->get_var( $sql ) );
	}

}
