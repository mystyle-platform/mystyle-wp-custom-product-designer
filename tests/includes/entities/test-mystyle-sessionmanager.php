<?php

/**
 * The MyStyleSessionManagerTest class includes tests for testing the 
 * MyStyle_SessionManager
 * class.
 *
 * @package MyStyle
 * @since 1.3.0
 */
class MyStyleSessionManagerTest extends WP_UnitTestCase {
    
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
     * Test the get function.
     * @global wpdb $wpdb
     */    
    function test_get() {
        global $wpdb;
        
        $session_id = 'testsession';
        
        //Create the session
        $session = MyStyle_Session::create( $session_id );
        
        //Persist the session
        MyStyle_SessionManager::persist( $session );
        
        //Call the function
        $session_from_db = MyStyle_SessionManager::get( $session_id );
        
        //Assert that the session_id is set
        $this->assertEquals( $session_id, $session_from_db->get_session_id() );
    }
    
    /**
     * Test the update function.
     * @global wpdb $wpdb
     */    
    function test_update() {
        global $wpdb;
        
        $session_id = 'testsession';
        
        //Create the session
        $session = MyStyle_Session::create( $session_id );
        
        //Persist the session
        MyStyle_SessionManager::persist( $session );
        
        //Get the persisted session.
        $session_from_db = MyStyle_SessionManager::get( $session_id );
        
        //Get the modified date
        $modified_orig = $session_from_db->get_modified();
        
        //Wait 1.5 seconds
        sleep(1.5);
        
        //Call the update function
        $session_from_db = MyStyle_SessionManager::update( $session );
        
        //Get the new modified date
        $modified_updated = $session_from_db->get_modified();
        
        $this->assertNotEquals( $modified_updated, $modified_orig );
    }

}
