<?php

/**
 * The MyStyleDesignerManagerTest class includes tests for testing the 
 * MyStyle_DesignerManager
 * class.
 *
 * @package MyStyle
 * @since 1.2.0
 */
class MyStyleDesignerManagerTest extends WP_UnitTestCase {
    
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
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Designer::get_table_name());
    }
    
    /**
     * Test the get function.
     * @global wpdb $wpdb
     */    
    function test_get() {
        global $wpdb;
        
        $designer_id = 1;
        $email = 'someone@example.com';
        
        //Create the designer
        $designer = MyStyle_Designer::create( 1, $email );
        
        //Persist the designer
        MyStyle_DesignerManager::persist( $designer );
        
        //Call the function
        $designer_from_db = MyStyle_DesignerManager::get( $designer_id );
        
        //Assert that the designer_id is set
        $this->assertEquals( $designer_id, $designer_from_db->get_designer_id() );
    }

}
