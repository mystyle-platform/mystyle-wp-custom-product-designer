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
     * Test the get function.
     * @global wpdb $wpdb
     */    
    function test_get() {
        global $wpdb;
        
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
     * Test the set_wp_user_id_by_mystyle_session function.
     * @global wpdb $wpdb
     */
    function test_set_wp_user_id_by_mystyle_session() {
        global $wpdb;
        
        $design_id = 1;
        $session_id = 'testsessionid';
        
        //Mock the user
        $user_id = wp_create_user( 'testuser', 'testpassword', 'someone@example.com' );
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
        $result = MyStyle_DesignManager::set_wp_user_id_by_mystyle_session( $session, $user );
        
        //Assert that one row was modified
        $this->assertEquals( 1, $result );
        
        //Get the design
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the user_id is set
        $this->assertEquals( $user_id, $design_from_db->get_user_id() );
    }

}
