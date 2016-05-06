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

}
