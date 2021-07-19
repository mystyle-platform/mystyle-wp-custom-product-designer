<?php
/**
 * The MyStyleDesignManagerTest class includes tests for testing the
 * MyStyle_DesignManager
 * class.
 *
 * @package MyStyle
 * @since 1.0.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleDesignManagerTest class.
 */
class MyStyleDesignManagerTest extends WP_UnitTestCase {

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
	 * Test that the can_user_edit function returns true when the design is the
	 * user's.
	 */
	public function test_can_user_edit_when_own_design() {
		$design_id = 1;
		$user_id   = 1;

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a Design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$can_edit = MyStyle_DesignManager::can_user_edit( $design, $user );

		$this->assertTrue( $can_edit );
	}

	/**
	 * Test that the can_user_edit function returns false when the design
	 * belongs to a different user.
	 */
	public function test_can_user_edit_when_not_own_design() {
		$design_id      = 1;
		$design_user_id = 1;
		$user_id        = 2;

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a Design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $design_user_id );
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$can_edit = MyStyle_DesignManager::can_user_edit( $design, $user );

		$this->assertFalse( $can_edit );
	}

	/**
	 * Test that the can_user_edit function returns true when the passed user
	 * has the administrator role/capability.
	 */
	public function test_can_user_edit_when_admin() {
		$design_id      = 1;
		$design_user_id = 1;
		$user_id        = 2;

		// Mock a WP_User.
		$user       = new WP_User();
		$user->caps = array( 'administrator' );
		$user->ID   = $user_id;

		// Create a Design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $design_user_id );
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$can_edit = MyStyle_DesignManager::can_user_edit( $design, $user );

		$this->assertFalse( $can_edit );
	}

	/**
	 * Test the get function with a valid design id.
	 */
	public function test_get_with_a_valid_design_id() {

		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id );

		// Assert that the design_id is set.
		$this->assertEquals( $design_id, $design_from_db->get_design_id() );
	}

	/**
	 * Test the get function with an invalid design id.
	 */
	public function test_get_with_an_invalid_design_id() {

		$design_id = 999;

		// Call the function.
		$design = MyStyle_DesignManager::get( $design_id );

		// Assert that the function returned null.
		$this->assertNull( $design );
	}

	/**
	 * Test that the get function throws a MyStyle_Unauthorized_Exception when
	 * accessing a private design and unidentified ( no user passed ).
	 */
	public function test_get_private_design_when_unauthorized() {
		$this->setExpectedException( 'MyStyle_Unauthorized_Exception' );

		$design_id = 1;
		$user_id   = 1;

		// Create a private design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );
		$design->set_user_id( $user_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id );
	}

	/**
	 * Test that the get function throws a MyStyle_Forbidden_Exception when
	 * accessing a private design that isn't the user's.
	 */
	public function test_get_private_design_when_forbidden() {
		$this->setExpectedException( 'MyStyle_Forbidden_Exception' );

		$design_id = 1;
		$user_id   = 1;

		// Create a private design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );
		$design->set_user_id( $user_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock a WP_User.
		$user = new WP_User();
		// Set the user id to one greater than the designer's.
		$user->ID = $design->get_user_id() + 1;

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id, $user );
	}

	/**
	 * Test that the get function returns a design to the user that created the
	 * design.
	 */
	public function test_get_private_with_user_match() {

		$design_id = 1;
		$user_id   = 1;

		// Create a private design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );
		$design->set_user_id( $user_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id, $user );

		// Assert that the design is returned.
		$this->assertEquals( $design_id, $design_from_db->get_design_id() );
	}

	/**
	 * Test that the get function returns a design to the session that created
	 * the design.
	 */
	public function test_get_private_with_session_match() {

		$design_id  = 1;
		$session_id = 'testsession';

		// Create the session.
		$session = MyStyle_Session::create( $session_id );

		// Create a private design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );
		$design->set_session_id( $session->get_session_id() );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id, null, $session );

		// Assert that the design is returned.
		$this->assertEquals( $design_id, $design_from_db->get_design_id() );
	}

	/**
	 * Test that the get function returns a design when accessed by an admin.
	 */
	public function test_get_private_when_admin() {

		$design_id = 1;

		// Create a private design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock a WP_User with admin privileges.
		$user     = new WP_User();
		$user->ID = 100;
		$user->add_cap( 'read_private_posts' );

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id, $user );

		// Assert that the design is returned.
		$this->assertEquals( $design_id, $design_from_db->get_design_id() );
	}

	/**
	 * Test that the get function returns a design when accessed by a user with
	 * the "shop_manager" role.
	 */
	public function test_get_private_when_shop_manager() {

		$design_id = 1;

		// Create a private design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Mock a WP_User with admin privileges.
		$user     = new WP_User();
		$user->ID = 101;
		$user->add_cap( 'shop_manager' );

		// Call the function.
		$design_from_db = MyStyle_DesignManager::get( $design_id, $user );

		// Assert that the design is returned.
		$this->assertEquals( $design_id, $design_from_db->get_design_id() );
	}

	/**
	 * Test the delete function.
	 */
	public function test_delete() {

		$design_id = 1;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Assert that the design is found.
		$design_from_db = MyStyle_DesignManager::get( $design_id );
		$this->assertEquals( $design_id, $design_from_db->get_design_id() );

		// Call the function.
		$deleted = MyStyle_DesignManager::delete( $design );

		// Assert that the function returned true.
		$this->assertTrue( $deleted );

		// Assert that the design is no longer found.
		$design_from_db = MyStyle_DesignManager::get( $design_id );
		$this->assertNull( $design_from_db );
	}

	/**
	 * Test the get_previous_design function.
	 */
	public function test_get_previous_design() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$previous_design = MyStyle_DesignManager::get_previous_design( 2 );

		// Assert that the previous design was found and has the expected id.
		$this->assertEquals( 1, $previous_design->get_design_id() );
	}

	/**
	 * Test the get_next_design function.
	 */
	public function test_get_next_design() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$next_design = MyStyle_DesignManager::get_next_design( 1 );

		// Assert that the design was found and has the expected id.
		$this->assertEquals( 2, $next_design->get_design_id() );
	}

	/**
	 * Test the set_user_id function sets the user_id on a design that matches
	 * both the session and the user's email.
	 *
	 * @global wpdb $wpdb
	 */
	public function test_set_user_id_for_email_and_session_match() {
		global $wpdb;

		$design_id  = 1;
		$session_id = 'testsessionid';
		$email      = 'someone@example.com';

		// Create the session.
		MyStyle_SessionHandler::get_instance();

		remove_action( 'user_register', array( MyStyle_User_Interface::get_instance(), 'on_user_register' ) );

		// Mock the user ( note this will call the function since it is hooked into the register function ).
		$user_id = MyStyle_Test_util::create_user( 'testuser', 'testpassword', $email );
		$user    = get_user_by( 'id', $user_id );

		// Mock the session.
		$session = MyStyle_Session::create( $session_id );
		MyStyle_SessionManager::persist( $session );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Add a session id and email to the design.
		$design->set_session_id( $session_id );
		$design->set_email( $email );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$result = MyStyle_DesignManager::set_user_id( $user, $session );

		// Assert that one row was modified.
		$this->assertEquals( 1, $result );

		// Get the design.
		$design_from_db = MyStyle_DesignManager::get( $design_id );

		// Assert that the user_id is set.
		$this->assertEquals( $user_id, $design_from_db->get_user_id() );
	}

	/**
	 * Test the set_user_id function sets the user_id on a design with a session
	 * id match but no email.
	 *
	 * @global wpdb $wpdb
	 */
	public function test_set_user_id_for_session_match_with_no_email() {
		global $wpdb;

		$design_id  = 1;
		$session_id = 'testsessionid';
		$email      = null;

		// Mock the user (remove our hooked functions first).
		$user_id = MyStyle_Test_util::create_user( 'testuser', 'testpassword', $email );
		$user    = get_user_by( 'id', $user_id );

		// Mock the session.
		$session = MyStyle_Session::create( $session_id );
		MyStyle_SessionManager::persist( $session );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Add a session id to the design.
		$design->set_session_id( $session_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$result = MyStyle_DesignManager::set_user_id( $user, $session );

		// Assert that one row was modified.
		$this->assertEquals( 1, $result );

		// Get the design.
		$design_from_db = MyStyle_DesignManager::get( $design_id );

		// Assert that the user_id is set.
		$this->assertEquals( $user_id, $design_from_db->get_user_id() );
	}

	/**
	 * Test the set_user_id function sets the user_id on a design with a session
	 * id match but no design email.
	 *
	 * @global wpdb $wpdb
	 */
	public function test_set_user_id_for_session_match_with_no_design_email() {
		global $wpdb;

		$design_id  = 1;
		$session_id = 'testsessionid';
		$email      = 'someone@example.com';

		// Mock the user.
		$user_id = MyStyle_Test_util::create_user( 'testuser', 'testpassword', $email );
		$user    = get_user_by( 'id', $user_id );

		// Mock the session.
		$session = MyStyle_Session::create( $session_id );
		MyStyle_SessionManager::persist( $session );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );

		// Add a session id to the design.
		$design->set_session_id( $session_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$result = MyStyle_DesignManager::set_user_id( $user, $session );

		// Assert that one row was modified.
		$this->assertEquals( 1, $result );

		// Get the updated design from the db.
		$design_from_db = MyStyle_DesignManager::get( $design_id );

		// Assert that the user_id is set on the design.
		$this->assertEquals( $user_id, $design_from_db->get_user_id() );
	}

	/**
	 * Test the set_user_id function sets the user_id on a design with a session
	 * id match but no design email.
	 *
	 * @global wpdb $wpdb
	 */
	public function test_set_user_id_for_failed_email_match() {
		global $wpdb;

		$design_id  = 1;
		$session_id = 'testsessionid';
		$email1     = 'someone@example.com';
		$email2     = 'someoneelse@example.com';

		// Mock the user ( note this will call the function since it is hooked into the register function ).
		$user_id = MyStyle_Test_util::create_user( 'testuser', 'testpassword', $email1 );
		$user    = get_user_by( 'id', $user_id );

		// Mock the session.
		$session = MyStyle_Session::create( $session_id );
		MyStyle_SessionManager::persist( $session );

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_email( $email2 );

		// Add a session id to the design.
		$design->set_session_id( $session_id );

		// Persist the design.
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$result = MyStyle_DesignManager::set_user_id( $user, $session );

		// Assert that no rows were modified.
		$this->assertEquals( 0, $result );

		// Get the design.
		$design_from_db = MyStyle_DesignManager::get( $design_id );

		// Assert that the user_id is NOT set.
		$this->assertEquals( 0, $design_from_db->get_user_id() );
	}

	/**
	 * Test the get_designs function with a simple scenario (all designs are
	 * public, no user is passed).
	 */
	public function test_get_designs_simple() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$designs = MyStyle_DesignManager::get_designs();

		// Assert that the design was found and has the expected id.
		$this->assertEquals( 2, count( $designs ) );
	}

	/**
	 * Test that the get_designs function hides the private designs of 1
	 * authenticated user from another authenticated user.
	 */
	public function test_get_designs_hides_other_users_private_design() {

		// Create user_1.
		$user_1     = new WP_User();
		$user_1->ID = 100;

		// Create user_2.
		$user_2     = new WP_User();
		$user_2->ID = 200;

		// Create a public design for user 1.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		$design_1->set_user_id( $user_1->ID );
		MyStyle_DesignManager::persist( $design_1 );

		// Create a private design for user 2.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		$design_2->set_user_id( $user_2->ID );
		$design_2->set_access( MyStyle_Access::ACCESS_PRIVATE );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$designs = MyStyle_DesignManager::get_designs( 250, 1, $user_1 );

		// Assert that the only 1 design was returned.
		$this->assertEquals( 1, count( $designs ) );

		// Assert that the private design wasn't returned
		/* @var $design \MyStyle_Design The Design. */
		$design = $designs[0];
		$this->assertEquals( $user_1->ID, $design->get_user_id() );
	}

	/**
	 * Test that the get_designs function hides private designs from anonymous
	 * users.
	 */
	public function test_get_designs_hides_private_design_from_anonymous_users() {

		// Create a user.
		$user     = new WP_User();
		$user->ID = 100;

		// Create a private design for the user.
		/* @var $design \MyStyle_Design The Design. */
		$design = MyStyle_MockDesign::get_mock_design( 1 );
		$design->set_user_id( $user->ID );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );
		MyStyle_DesignManager::persist( $design );

		// Call the function anonymously (with no user).
		$designs = MyStyle_DesignManager::get_designs();

		// Assert that no designs were returned.
		$this->assertEquals( 0, count( $designs ) );
	}

	/**
	 * Test that the get_designs function shows private designs to admin users.
	 */
	public function test_get_designs_shows_private_design_to_admin_user() {

		// Create a user.
		$user_1     = new WP_User();
		$user_1->ID = 100;

		// Create a private design for the user.
		/* @var $design_1 \MyStyle_Design Create the design. */
		$design = MyStyle_MockDesign::get_mock_design( 1 );
		$design->set_user_id( $user_1->ID );
		$design->set_access( MyStyle_Access::ACCESS_PRIVATE );
		MyStyle_DesignManager::persist( $design );

		// Create an admin user.
		$admin_user     = new WP_User();
		$admin_user->ID = 200;
		$admin_user->add_cap( 'read_private_posts' );

		// Call the function as the admin user.
		$designs = MyStyle_DesignManager::get_designs( 250, 1, $admin_user );

		// Assert that the design was returned.
		$this->assertEquals( 1, count( $designs ) );

		// Assert that the returned design was indeed marked private.
		/* @var $returned_design \MyStyle_Design The returned_design. */
		$returned_design = $designs[0];
		$this->assertEquals( MyStyle_Access::ACCESS_PRIVATE, $returned_design->get_access() );
	}

	/**
	 * Test the get_random_designs function.
	 */
	public function test_get_random_designs() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$designs = MyStyle_DesignManager::get_random_designs();

		// Assert that the designs were returned.
		$this->assertEquals( 2, count( $designs ) );
	}

	/**
	 * Test the get_designs_by_term_id function.
	 */
	public function test_get_designs_by_term_id() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Give the design the term.
		$tag_name      = 'test_tag';
		$taxonomy_name = MYSTYLE_TAXONOMY_NAME;
		$term_ids      = wp_add_object_terms(
			$design_1->get_design_id(),
			$tag_name,
			$taxonomy_name
		);
		$term_id       = $term_ids[0];

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$designs = MyStyle_DesignManager::get_designs_by_term_id( $term_id );

		// Assert that only the design with the term was returned.
		$this->assertEquals( 1, count( $designs ) );
		$returned_design = $designs[0];
		$this->assertEquals( 'MyStyle_Design', get_class( $returned_design ) );
	}

	/**
	 * Test the get_total_design_count function.
	 */
	public function test_get_total_design_count() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$count = MyStyle_DesignManager::get_total_design_count();

		// Assert that the expected count is returned.
		$this->assertEquals( 2, $count );
	}

	/**
	 * Test the get_total_user_design_count function.
	 */
	public function test_get_total_user_design_count() {

		// Create a user.
		$user_1     = new WP_User();
		$user_1->ID = 1;

		// Create another user.
		$user_2     = new WP_User();
		$user_2->ID = 2;

		// Create a design for user_1.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		$design_1->set_user_id( $user_1->ID );
		MyStyle_DesignManager::persist( $design_1 );

		// Create a design for user_2.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		$design_2->set_user_id( $user_2->ID );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$count = MyStyle_DesignManager::get_total_user_design_count( $user_1 );

		// Assert that the expected count is returned.
		$this->assertEquals( 1, $count );
	}

	/**
	 * Test the get_total_term_design_count function.
	 */
	public function test_get_total_term_design_count() {

		// Create a design.
		$design_1 = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design_1 );

		// Give the design the term.
		$tag_name      = 'test_tag';
		$taxonomy_name = MYSTYLE_TAXONOMY_NAME;
		$term_ids      = wp_add_object_terms(
			$design_1->get_design_id(),
			$tag_name,
			$taxonomy_name
		);
		$term_id       = $term_ids[0];

		// Create another design.
		$design_2 = MyStyle_MockDesign::get_mock_design( 2 );
		MyStyle_DesignManager::persist( $design_2 );

		// Call the function.
		$count = MyStyle_DesignManager::get_total_term_design_count( $term_id );

		// Assert that the expected count is returned.
		$this->assertEquals( 1, $count );
	}

	/**
	 * Test the is_user_design_owner function for a design that the user owns.
	 */
	public function test_is_user_design_owner_when_user_is_owner() {

		// Mock a WP_User.
		$user_id  = 100;
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design_id = 1;
		$design    = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Call the function.
		$ret = MyStyle_DesignManager::is_user_design_owner(
			$user_id,
			$design_id
		);

		// Assert that true is returned as expected.
		$this->assertTrue( $ret );
	}

	/**
	 * Test the is_user_design_owner function for a design that the user DOES
	 * NOT own.
	 */
	public function test_is_user_design_owner_when_user_is_not_owner() {

		// Mock a WP_User.
		$user_id_1  = 100;
		$user_1     = new WP_User();
		$user_1->ID = $user_id_1;

		// Another user.
		$user_id_2 = 200;

		// Create a design.
		$design_id = 1;
		$design    = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id_1 );
		MyStyle_DesignManager::persist( $design );

		// Call the function (with the second user's id).
		$ret = MyStyle_DesignManager::is_user_design_owner(
			$user_id_2,
			$design_id
		);

		// Assert that false is returned as expected.
		$this->assertFalse( $ret );
	}

	/**
	 * Test the get_design_tags function with the with_slugs param turned off.
	 */
	public function test_get_design_tags() {

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design );

		// Give the design the term.
		$tag_name      = 'test_tag';
		$taxonomy_name = MYSTYLE_TAXONOMY_NAME;
		$term_ids      = wp_add_object_terms(
			$design->get_design_id(),
			$tag_name,
			$taxonomy_name
		);
		$term_id       = $term_ids[0];

		// Call the function.
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the expected count is returned.
		$this->assertEquals( 1, count( $tags ) );
		$this->assertEquals( $tag_name, $tags[0] );
	}

	/**
	 * Test the get_design_tags function with the with_slugs param turned ON.
	 */
	public function test_get_design_tags_with_slugs() {

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( 1 );
		MyStyle_DesignManager::persist( $design );

		// Give the design the term.
		$tag_name      = 'Test Tag';
		$taxonomy_name = MYSTYLE_TAXONOMY_NAME;
		$term_ids      = wp_add_object_terms(
			$design->get_design_id(),
			$tag_name,
			$taxonomy_name
		);
		$term_id       = $term_ids[0];

		// Call the function.
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id(),
			true // with_slugs.
		);

		// Assert that the expected count is returned.
		$this->assertEquals( 1, count( $tags ) );
		$this->assertEquals( $tag_name, $tags[0]['name'] );
		$this->assertEquals( 'test-tag', $tags[0]['slug'] );
	}

	/**
	 * Test the add_tag_to_design function.
	 */
	public function test_add_tag_to_design() {
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

		// Call the function.
		$tag_id = MyStyle_DesignManager::add_tag_to_design(
			$design_id,
			$tag_name,
			$user
		);

		// Assert that a tag id is returned as expected.
		$this->assertGreaterThan( 0, intval( $tag_id ) );
	}

	/**
	 * Test the remove_tag_from_design function.
	 */
	public function test_remove_tag_from_design() {
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

		// Add the Tag to the design.
		$tag_id = MyStyle_DesignManager::add_tag_to_design(
			$design_id,
			$tag_name,
			$user
		);

		// Get the tags for the design.
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the design has 1 tag.
		$this->assertEquals( 1, count( $tags ) );

		// Call the method.
		$tags = MyStyle_DesignManager::remove_tag_from_design(
			$design->get_design_id(),
			$tag_name,
			$user
		);

		// Get the tags for the design (again).
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the design now has 0 tags.
		$this->assertEquals( 0, count( $tags ) );
	}

	/**
	 * Test the update_design_tags function.
	 */
	public function test_update_design_tags() {
		$orig_tag_name = 'Orig Tag';
		$design_id     = 1;
		$user_id       = 1;
		$new_tags      = array( $orig_tag_name, 'New Tag 1', 'New Tag 2' );

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Add the Tag to the design.
		$tag_id = MyStyle_DesignManager::add_tag_to_design(
			$design_id,
			$orig_tag_name,
			$user
		);

		// Get the tags for the design.
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the design has 1 tag.
		$this->assertEquals( 1, count( $tags ) );

		// Call the method.
		$tags = MyStyle_DesignManager::update_design_tags(
			$design->get_design_id(),
			$new_tags,
			$user
		);

		// Get the tags for the design (again).
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the design now has 3 tags.
		$this->assertEquals( 3, count( $tags ) );
	}

	/**
	 * Test that the update_design_tags function removes all tags when passed
	 * an empty array.
	 */
	public function test_update_design_tags_removes_all_tags_when_passed_empty_array() {
		$orig_tag_name = 'Orig Tag';
		$design_id     = 1;
		$user_id       = 1;
		$new_tags      = array();

		// Mock a WP_User.
		$user     = new WP_User();
		$user->ID = $user_id;

		// Create a design.
		$design = MyStyle_MockDesign::get_mock_design( $design_id );
		$design->set_user_id( $user_id );
		MyStyle_DesignManager::persist( $design );

		// Add the Tag to the design.
		$tag_id = MyStyle_DesignManager::add_tag_to_design(
			$design_id,
			$orig_tag_name,
			$user
		);

		// Get the tags for the design.
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the design has 1 tag.
		$this->assertEquals( 1, count( $tags ) );

		// Call the method.
		$tags = MyStyle_DesignManager::update_design_tags(
			$design->get_design_id(),
			$new_tags,
			$user
		);

		// Get the tags for the design (again).
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id()
		);

		// Assert that the design now has 0 tags.
		$this->assertEquals( 0, count( $tags ) );
	}

}
