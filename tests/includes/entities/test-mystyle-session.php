<?php
/**
 * The MyStyleSessionTest class includes tests for testing the MyStyle_Session
 * class.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-sessionqueryresult.php';

/**
 * MyStyleSessionTest class.
 */
class MyStyleSessionTest extends WP_UnitTestCase {

	/**
	 * Test the create function.
	 */
	public function test_create() {

		$session = new MyStyle_Session();

		// Assert that the session is constructed.
		$this->assertEquals( 'MyStyle_Session', get_class( $session ) );
	}

	/**
	 * Test the create function.
	 */
	public function test_create_generates_unique_session_ids() {

		$session1 = \MyStyle_Session::create();
		$session2 = \MyStyle_Session::create();

		// Assert that the two sessions have different ids.
		$this->assertNotEquals(
			$session1->get_session_id(),
			$session2->get_session_id()
		);
	}

	/**
	 * Test the create_from_result_object function.
	 */
	public function test_create_from_result_object() {

		$session_id = 'testsession';

		// Mock the result object.
		$result_object = new MyStyle_MockSessionQueryResult( $session_id );

		$session = MyStyle_Session::create_from_result_object( $result_object );

		// Assert that the session_id is set.
		$this->assertEquals( $session_id, $session->get_session_id() );

		// Assert that the session is marked as persistent.
		$this->assertTrue( $session->is_persistent() );
	}

	/**
	 * Test the get_schema function.
	 */
	public function test_get_schema() {

		$expected_schema = "
            CREATE TABLE wptests_mystyle_sessions (
                session_id varchar(100) NOT NULL,
                session_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (session_id)
            )";

		$schema = MyStyle_Session::get_schema();

		// Assert that the expected schema is returned.
		$this->assertEquals( $expected_schema, $schema );
	}

	/**
	 * Test the get_table_name function.
	 */
	public function test_get_table_name() {

		$expected_table_name = 'wptests_mystyle_sessions';

		$table_name = MyStyle_Session::get_table_name();

		// Assert that the expected table name is returned.
		$this->assertEquals( $expected_table_name, $table_name );
	}

	/**
	 * Test the get_primary_key function.
	 */
	public function test_get_primary_key() {

		$expected_primary_key = 'session_id';

		$primary_key = MyStyle_Session::get_primary_key();

		// Assert that the expected primary key is returned.
		$this->assertEquals( $expected_primary_key, $primary_key );
	}

	/**
	 * Test the get_data_array function.
	 */
	public function test_get_data_array() {

		$session_id = 'testsession';

		// Set up the expected data array.
		$expected_data_array = array(
			'session_id'           => $session_id,
			'session_created'      => '2015-08-06 22:35:52',
			'session_created_gmt'  => '2015-08-06 22:35:52',
			'session_modified'     => '2015-08-06 22:35:52',
			'session_modified_gmt' => '2015-08-06 22:35:52',
		);

		// Create a session.
		$result_object = new MyStyle_MockSessionQueryResult( $session_id );
		$session       = MyStyle_Session::create_from_result_object( $result_object );

		// Run the function.
		$data_array = $session->get_data_array();

		// Assert that the expected data array is returned.
		$this->assertEquals( $expected_data_array, $data_array );
	}

	/**
	 * Test the get_insert_format function.
	 */
	public function test_get_insert_format() {

		// Set up the expected formats array.
		$expected_formats_arr = array(
			'%s', // session_id.
			'%s', // session_created.
			'%s', // session_created_gmt.
			'%s', // session_modified.
			'%s', // session_modified_gmt.
		);

		// Create a session.
		$result_object = new MyStyle_MockSessionQueryResult( 'testsession' );
		$session       = MyStyle_Session::create_from_result_object( $result_object );

		// Assert that the expected data array is returned.
		$this->assertEquals( $expected_formats_arr, $session->get_insert_format() );
	}

	/**
	 * Test the generate_session_id function.
	 */
	public function test_generate_session_id() {
		// Generate a session id.
		$session_id_1 = MyStyle_Session::generate_session_id();

		// Assert that the session id is the expected length.
		$this->assertEquals( 43, strlen( $session_id_1 ) );

		// Assert that the session id is composed only of printable characters.
		$this->assertTrue( ctype_print( $session_id_1 ) );

		// Generate another session id.
		$session_id_2 = MyStyle_Session::generate_session_id();

		// Assert that the session ids are not equal.
		$this->assertNotEquals( $session_id_1, $session_id_2 );
	}

}
