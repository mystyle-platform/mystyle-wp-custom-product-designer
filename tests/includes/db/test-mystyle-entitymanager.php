<?php

/**
 * The MyStyleEntityManagerTest class includes tests for testing the 
 * MyStyle_EntityManager
 * class.
 *
 * @package MyStyle
 * @since 1.0.0
 */
class MyStyleEntityManagerTest extends WP_UnitTestCase {
    
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
     * Overrwrite the tearDown function to remove our custom tables
     */
    function tearDown() {
        global $wpdb;
        // Perform the actual task according to parent class.
        parent::tearDown();
        
        //Drop the tables that we created
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Design::get_table_name());
    }
    
    /**
     * Test the persist function. Uses a design entity to test the function.
     * @global wpdb $wpdb
     */    
    function test_persist() {
        global $wpdb;
        
        $design_id = 1;
        
        //Mock the POST
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = $design_id;
        $post['product_id'] = 0;
        $post['h'] = base64_encode( json_encode( array( 'local_product_id' => 0 ) ) );
        $post['user_id'] = 0;
        $post['price'] = 0;
        
        //Create the design
        $design = MyStyle_Design::create_from_post($post);
        
        //Call the function
        MyStyle_EntityManager::persist($design);
        
        $query = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ' . 
                 'WHERE ' . MyStyle_Design::get_primary_key() . ' = ' . $design_id;
        
        $result_object = $wpdb->get_row($query);

        $design_from_db = MyStyle_Design::create_from_result_object( $result_object );
        
        //Assert that the entity was persisted
        $this->assertEquals( $design_id, $design_from_db->get_design_id() );
    }

}
