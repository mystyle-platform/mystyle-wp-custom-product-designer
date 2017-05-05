<?php

/**
 * The MyStyleSessionHandlerTest class includes tests for testing the 
 * MyStyle_SessionHandler
 * class.
 *
 * @package MyStyle
 * @since 1.3.0
 */
class MyStyleSessionHandlerTest extends WP_UnitTestCase {
    
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
     * Test the constructor
     */    
    public function test_constructor() {
        global $wp_filter;
        
        $mystyle_sessionhandler = new MyStyle_SessionHandler();
        
        //Assert that the expected functions are registered.
        $function_names = get_function_names( $wp_filter['mystyle_session_garbage_collection'] );
        $this->assertContains( 'garbage_collection', $function_names );
    }
    
    /**
     * Test that the get function generates a new session if one doesn't exist.
     * @global \wpdb $wpdb
     */
    function test_get_generates_new_session_if_one_doesnt_exist() {
        
        //Set the session variable
        if(session_id() == '') {
            session_start();
        }
        
        //Assert that the $_SESSION variable isn't set
        unset( $_SESSION[MyStyle_Session::$SESSION_KEY] );
        $this->assertFalse( isset( $_SESSION[MyStyle_Session::$SESSION_KEY] ) );
        
        //Call the function
        $returned_session = MyStyle_SessionHandler::get();
        
        //Assert that the session_id is set
        $this->assertNotNull( $returned_session->get_session_id() );
        
        //Assert taht the $_SESSION variable is now set
        $this->assertTrue( isset( $_SESSION[MyStyle_Session::$SESSION_KEY] ) );
    }
    
    
    /**
     * Test that the get function returns an existing persisted session.
     */
    function test_get_returns_existing_persisted_session() {
        
        $session_id = 'testsession';
        
        //Create and persist the session
        $session = MyStyle_Session::create( $session_id );
        MyStyle_SessionManager::persist( $session );
        
        //Set the session variable
        if(session_id() == '') {
            session_start();
        }
        $_SESSION[MyStyle_Session::$SESSION_KEY] = $session;
        
        //Call the function
        $returned_session = MyStyle_SessionHandler::get();
        
        //Assert that the session_id is set
        $this->assertEquals( $session_id, $returned_session->get_session_id() );
    }

    /**
     * Test that the garbage_collection function leaves a new session.
     * @global \wpdb $wpdb
     */
    function test_garbage_collection_leaves_new_session() {
        
        $session_id = 'testsession';
        
        //Create and persist the session
        $session = MyStyle_Session::create( $session_id );
        MyStyle_SessionManager::persist( $session );
        
        //Call the garbage collector
        MyStyle_SessionHandler::garbage_collection();
        
        //attempt to get the session from the db.
        $session_from_db = MyStyle_SessionManager::get( $session_id );
        
        //Assert that the session is still in the db.
        $this->assertEquals( $session_id, $session_from_db->get_session_id() );
    }
    
    /**
     * Test that the garbage_collection function removes an old session.
     * @global \wpdb $wpdb
     */
    function test_garbage_collection_removes_an_old_session() {
        
        $session_id = 'testsession';
        
        //Create and persist an old session
        $session = MyStyle_Session::create( $session_id );
        $one_week_ago = gmdate( 'Y-m-d H:m:s', strtotime( '-7 days' ) );
        $session->set_modified_gmt( $one_week_ago );
        MyStyle_SessionManager::persist( $session );
        
        //Call the garbage collector
        MyStyle_SessionHandler::garbage_collection();
        
        //attempt to get the session from the db.
        $session_from_db = MyStyle_SessionManager::get( $session_id );
        
        //Assert that the session is no longer in the db
        $this->assertNull( $session_from_db );
    }
    
    /**
     * Test that the garbage_collection function leaves an old session with a
     * design.
     * @global wpdb $wpdb
     */
    function test_garbage_collection_leaves_old_session_with_design() {
        
        $session_id = 'testsession';
        
        //Create and persist an old session
        $session = MyStyle_Session::create( $session_id );
        $one_week_ago = gmdate( 'Y-m-d H:m:s', strtotime( '-7 days' ) );
        $session->set_modified_gmt( $one_week_ago );
        MyStyle_SessionManager::persist( $session );
        
        //Create and persist a design with the same session_id.
        $design_id = 1;
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        $design->set_session_id( $session_id );
        MyStyle_DesignManager::persist( $design );
        
        //Call the garbage collector
        MyStyle_SessionHandler::garbage_collection();
        
        //attempt to get the session from the db.
        $session_from_db = MyStyle_SessionManager::get( $session_id );
        
        //Assert that the session is still in the db.
        $this->assertEquals( $session_id, $session_from_db->get_session_id() );
    }
    
}
