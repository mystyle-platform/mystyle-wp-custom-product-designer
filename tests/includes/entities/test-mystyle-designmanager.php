<?php

require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php');

/**
 * The MyStyleDesignManagerTest class includes tests for testing the 
 * MyStyle_DesignManager
 * class.
 *
 * @package MyStyle
 * @since 1.0.0
 */
class MyStyleDesignManagerTest extends WP_UnitTestCase {
    
    /**
     * Overrwrite the setUp function so that our custom tables will be persisted
     * to the test database.
     */
    function setUp() {
        // Perform the actual task according to parent class.
        parent::setUp();
        // Remove filters that will create temporary tables. So that permanent tables will be created.
        remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
        remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
        
        //Create the tables
        MyStyle_Install::create_tables();
    }
    
    /**
     * Overrwrite the tearDown function to remove our custom tables.
     */
    function tearDown() {
        global $wpdb;
        // Perform the actual task according to parent class.
        parent::tearDown();
        
        //Drop the tables that we created
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Design::get_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Session::get_table_name());
    }
    
    /**
     * Test the get function with a .
     */    
    function test_get_with_a_valid_design_id() {
        
        $design_id = 1;
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the design_id is set
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
    }
    
    /**
     * Test the get function with an invalid design id.
     */    
    function test_get_with_an_invalid_design_id() {
        
        $design_id = 999;
        
        //Call the function
        $design = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the function returned null.
        $this->assertNull( $design );
    }
    
    /**
     * Test that the get function throws a MyStyle_Unauthorized_Exception when
     * accessing a private design and unidentified (no user passed).
     */    
    function test_get_private_design_when_unauthorized() {
        $this->setExpectedException( 'MyStyle_Unauthorized_Exception' );
        
        $design_id = 1;
        $user_id = 1;
        
        //Create a private design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_access( MyStyle_Access::$PRIVATE );
        $design->set_user_id( $user_id ); 
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id );
    }
    
    /**
     * Test that the get function throws a MyStyle_Forbidden_Exception when
     * accessing a private design that isn't the user's.
     */    
    function test_get_private_design_when_forbidden() {
        $this->setExpectedException( 'MyStyle_Forbidden_Exception' );
        
        $design_id = 1;
        $user_id = 1;
        
        //Create a private design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_access( MyStyle_Access::$PRIVATE );
        $design->set_user_id( $user_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Mock a WP_User
        $user = new WP_User();
        //set the user id to one greater than the designer's
        $user->ID = $design->get_user_id()+1; 
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id, $user );
    }
    
    /**
     * Test that the get function returns a design to the user that created the
     * design.
     */    
    function test_get_private_with_user_match() {
        
        $design_id = 1;
        $user_id = 1;
        
        //Create a private design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_access( MyStyle_Access::$PRIVATE );
        $design->set_user_id( $user_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Mock a WP_User
        $user = new WP_User();
        $user->ID = $user_id;
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id, $user );
        
        //Assert that the design is returned
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
    }
    
    /**
     * Test that the get function returns a design to the session that created
     * the design.
     */    
    function test_get_private_with_session_match() {
        
        $design_id = 1;
        $session_id = 'testsession';
        
        //Create the session
        $session = MyStyle_Session::create( $session_id );
        
        //Create a private design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_access( MyStyle_Access::$PRIVATE );
        $design->set_session_id( $session->get_session_id() );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id, null, $session );
        
        //Assert that the design is returned
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
    }
    
    /**
     * Test that the get function returns a design when accessed by an admin.
     */    
    function test_get_private_when_admin() {
        
        $design_id = 1;
        
        //Create a private design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_access( MyStyle_Access::$PRIVATE );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Mock a WP_User with admin privileges
        $user = new WP_User();
        $user->ID = 100;
        $user->add_cap( 'read_private_posts' );
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id, $user );
        
        //Assert that the design is returned
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
    }
    
    /**
     * Test the delete function.
     */    
    function test_delete() {
        
        $design_id = 1;
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Assert that the design is found
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
        
        //call the function
        $deleted = MyStyle_DesignManager::delete( $design );
        
        //Assert that the function returned true
        $this->assertTrue( $deleted );
        
        //Assert that the design is no longer found
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        $this->assertNull( $design_from_db );
    }
    
    /**
     * Test the get_previous_design function.
     */    
    function test_get_previous_design() {
        
        //Create a design
        $design_1 = MyStyle_MockDesign::getMockDesign( 1 );
        MyStyle_DesignManager::persist( $design_1 );
        
        //Create another design
        $design_2 = MyStyle_MockDesign::getMockDesign( 2 );
        MyStyle_DesignManager::persist( $design_2 );
        
        //call the function
        $previous_design = MyStyle_DesignManager::get_previous_design( 2 );
        
        //Assert that the previous design was found and has the expected id
        $this->assertEquals( 1, $previous_design->get_design_id() );
    }
    
    /**
     * Test the get_next_design function.
     */    
    function test_get_next_design() {
        
        //Create a design
        $design_1 = MyStyle_MockDesign::getMockDesign( 1 );
        MyStyle_DesignManager::persist( $design_1 );
        
        //Create another design
        $design_2 = MyStyle_MockDesign::getMockDesign( 2 );
        MyStyle_DesignManager::persist( $design_2 );
        
        //call the function
        $next_design = MyStyle_DesignManager::get_next_design( 1 );
        
        //Assert that the design design was found and has the expected id
        $this->assertEquals( 2, $next_design->get_design_id() );
    }
    
    /**
     * Test the set_user_id function sets the user_id on a design that matches
     * both the session and the user's email.
     * @global wpdb $wpdb
     */
    function test_set_user_id_for_email_and_session_match() {
        global $wpdb;
        
        $design_id = 1;
        $session_id = 'testsessionid';
        $email = 'someone@example.com';
        
        //Mock the user (note this will call the function since it is hooked into the register function)
        $user_id = wp_create_user( 'testuser', 'testpassword', $email );
        $user = get_user_by( 'id', $user_id );
        
        //Mock the session
        $session = MyStyle_Session::create( $session_id );
        MyStyle_SessionManager::persist( $session );
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Add a session id and email to the design
        $design->set_session_id( $session_id );
        $design->set_email( $email );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $result = MyStyle_DesignManager::set_user_id( $user, $session );
        
        //Assert that one row was modified
        $this->assertEquals( 1, $result );
        
        //Get the design
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the user_id is set
        $this->assertEquals( $user_id, $design_from_db->get_user_id() );
    }
    
    /**
     * Test the set_user_id function sets the user_id on a design with a session
     * id match but no email.
     * @global wpdb $wpdb
     */
    function test_set_user_id_for_session_match_with_no_email() {
        global $wpdb;
        
        $design_id = 1;
        $session_id = 'testsessionid';
        $email = null;
        
        //Mock the user (note this will call the function since it is hooked into the register function)
        $user_id = wp_create_user( 'testuser', 'testpassword', $email );
        $user = get_user_by( 'id', $user_id );
        
        //Mock the session
        $session = MyStyle_Session::create( $session_id );
        MyStyle_SessionManager::persist( $session );
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Add a session id to the design
        $design->set_session_id( $session_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $result = MyStyle_DesignManager::set_user_id( $user, $session );
        
        //Assert that one row was modified
        $this->assertEquals( 1, $result );
        
        //Get the design
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the user_id is set
        $this->assertEquals( $user_id, $design_from_db->get_user_id() );
    }
    
    /**
     * Test the set_user_id function sets the user_id on a design with a session
     * id match but no design email.
     * @global wpdb $wpdb
     */
    function test_set_user_id_for_session_match_with_no_design_email() {
        global $wpdb;
        
        $design_id = 1;
        $session_id = 'testsessionid';
        $email = 'someone@example.com';
        
        //Mock the user
        $user_id = wp_create_user( 'testuser', 'testpassword', $email );
        $user = get_user_by( 'id', $user_id );
        
        //Mock the session
        $session = MyStyle_Session::create( $session_id );
        MyStyle_SessionManager::persist( $session );
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Add a session id to the design
        $design->set_session_id( $session_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $result = MyStyle_DesignManager::set_user_id( $user, $session );
        
        //Assert that one row was modified
        $this->assertEquals( 1, $result );
        
        //Get the updated design from the db
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the user_id is set on the design
        $this->assertEquals( $user_id, $design_from_db->get_user_id() );
    }
    
    /**
     * Test the set_user_id function sets the user_id on a design with a session
     * id match but no design email.
     * @global wpdb $wpdb
     */
    function test_set_user_id_for_failed_email_match() {
        global $wpdb;
        
        $design_id = 1;
        $session_id = 'testsessionid';
        $email1 = 'someone@example.com';
        $email2 = 'someoneelse@example.com';
        
        //Mock the user (note this will call the function since it is hooked into the register function)
        $user_id = wp_create_user( 'testuser', 'testpassword', $email1 );
        $user = get_user_by( 'id', $user_id );
        
        //Mock the session
        $session = MyStyle_Session::create( $session_id );
        MyStyle_SessionManager::persist( $session );
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_email($email2);
        
        //Add a session id to the design
        $design->set_session_id( $session_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $result = MyStyle_DesignManager::set_user_id( $user, $session );
        
        //Assert that no rows were modified
        $this->assertEquals( 0, $result );
        
        //Get the design
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the user_id is NOT set
        $this->assertEquals( 0, $design_from_db->get_user_id() );
    }
    
    /**
     * Test the get_designs function.
     */    
    function test_get_designs() {
        
        //Create a design
        $design_1 = MyStyle_MockDesign::getMockDesign( 1 );
        MyStyle_DesignManager::persist( $design_1 );
        
        //Create another design
        $design_2 = MyStyle_MockDesign::getMockDesign( 2 );
        MyStyle_DesignManager::persist( $design_2 );
        
        //call the function
        $designs = MyStyle_DesignManager::get_designs();
        
        //Assert that the design design was found and has the expected id
        $this->assertEquals( 2, sizeof( $designs ) );
    }
    
    /**
     * Test the get_total_design_count function.
     */    
    function test_get_total_design_count() {
        
        //Create a design
        $design_1 = MyStyle_MockDesign::getMockDesign( 1 );
        MyStyle_DesignManager::persist( $design_1 );
        
        //Create another design
        $design_2 = MyStyle_MockDesign::getMockDesign( 2 );
        MyStyle_DesignManager::persist( $design_2 );
        
        //call the function
        $count = MyStyle_DesignManager::get_total_design_count();
        
        //Assert that the expected count is returned
        $this->assertEquals( 2, $count );
    }

}
