<?php

/**
 * The MyStyleDesignProfileShortcodeTest class includes tests for testing the 
 * MyStyle_Design_Profile_Shortcode class.
 *
 * @package MyStyle
 * @since 1.3.2
 */
class MyStyleDesignProfileShortcodeTest extends WP_UnitTestCase {
    
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
     * Test the output function with valid design id.
     */    
    public function test_output_with_valid_design_id() {
        
        //set up the data
        $design_id = 1;
        
        //create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //persist the design
        MyStyle_DesignManager::persist( $design );
        
        //mock the request uri
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/1';
        
        //Create the MyStyle Customize page
        $page_id = MyStyle_Customize_Page::create();

        //call the function
        $output = MyStyle_Design_Profile_Shortcode::output();
        
        //assert that the output includes an img tag
        $this->assertContains( '<img', $output );
    }
    
    /**
     * Test the output function with no design id.
     */    
    public function test_output_with_no_design_id() {
        
        //mock the request uri
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/';
        
        //call the function
        $output = MyStyle_Design_Profile_Shortcode::output();
        
        //assert that the output includes includes 'Design Not Found'
        $this->assertContains( 'Design not found', $output );
    }
    
    /**
     * Test the output function with an invalid design id.
     */    
    public function test_output_with_an_invalid_design_id() {
        
        //mock the request uri with an invalid design id
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/999';
        
        //call the function
        $output = MyStyle_Design_Profile_Shortcode::output();
        
        //assert that the output includes includes 'Design Not Found'
        $this->assertContains( 'Design not found', $output );
    }
    
}
