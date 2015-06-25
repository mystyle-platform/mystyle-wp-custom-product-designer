<?php

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
        
        //Mock the POST
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = $design_id;
        $post['product_id'] = 0;
        $post['local_product_id'] = 0;
        $post['user_id'] = 0;
        $post['price'] = 0;
        
        //Create the design
        $design = MyStyle_Design::create_from_post($post);
        
        //Persist the design
        MyStyle_DesignManager::persist($design);
        
        //Call the function
        $design_from_db = MyStyle_DesignManager::get( $design_id );
        
        //Assert that the design_id is set
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
    }

}
