<?php

require_once(MYSTYLE_INCLUDES . 'entities/class-mystyle-design.php');
require_once(MYSTYLE_INCLUDES . 'admin/class-mystyle-install.php');

/**
 * The MystyleAdminTest class includes tests for testing the MyStyle_Admin 
 * class.
 *
 * @package MyStyle
 * @since 0.5.3
 */
class MyStyleInstallTest extends WP_UnitTestCase {
    
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
     * Test the create_tables function successfully creates the tables.
     */    
    public function test_create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->get_var( "SHOW TABLES LIKE '" . MyStyle_Design::get_table_name() . "'" );
        
        //Assert that the table doesn't yet exist.
        $this->assertNotEquals( MyStyle_Design::get_table_name(), $table_name );
        
        //Create the tables
        MyStyle_Install::create_tables();
        
        $table_name = $wpdb->get_var( "SHOW TABLES LIKE '" . MyStyle_Design::get_table_name() . "'" );
        
        //Assert that the table now exists.
        $this->assertEquals( MyStyle_Design::get_table_name(), $table_name );
    }
    
    /**
     * Test the get_schema function
     */    
    public function test_get_schema() {
        
        $expected_schema = '
            CREATE TABLE wptests_mystyle_designs (
                ms_design_id bigint(32) NOT NULL,
                ms_product_id bigint(20) NOT NULL,
                ms_user_id bigint(20) NULL,
                ms_description text NULL,
                ms_price numeric(15,2) NULL,
                ms_print_url varchar(255) NULL,
                ms_web_url varchar(255) NULL,
                ms_thumb_url varchar(255) NULL,
                ms_design_url varchar(255) NULL,
                product_id bigint(20) NULL,
                PRIMARY KEY  (ms_design_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
        
        $schema = MyStyle_Install::get_schema();
        
        $this->assertEquals( $expected_schema, $schema );
    }
    
}
