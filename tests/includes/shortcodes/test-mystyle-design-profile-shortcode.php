<?php

/**
 * The MyStyleDesignProfileShortcodeTest class includes tests for testing the 
 * MyStyle_Design_Profile_Shortcode class.
 *
 * @package MyStyle
 * @since 1.4.0
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
     * @global stdClass $post
     */
    public function test_output_with_valid_design_id() {
        global $post;
        
        //reset the singleton instance of the design profile page (to clear out
        // any previously set values)
        MyStyle_Design_Profile_Page::reset_instance();
        
        //set up the data
        $design_id = 1;
        
        //create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Create the MyStyle Customize page
        MyStyle_Customize_Page::create();
        
        //Create the MyStyle_Design_Profile page
        MyStyle_Design_Profile_Page::create();
        
        //set the current post to the Design_Profile_Page.
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/1';
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        //Init the MyStyle_Design_Profile_Page
        MyStyle_Design_Profile_Page::init();

        //call the function
        $output = MyStyle_Design_Profile_Shortcode::output();
        
        //assert that the output includes an img tag
        $this->assertContains( '<img', $output );
    }
    
    /**
     * Test the output function with no design id.  Should load the design
     * index.
     * @global stdClass $post
     */    
    public function test_output_with_no_design_id() {
        global $post;
        
        $design_id = 1;
        
        //create the MyStyle_Design_Profile page
        MyStyle_Design_Profile_Page::create();
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Reset the singleton instance (to clear out any previously set values)
        MyStyle_Design_Profile_Page::reset_instance();
        
        //mock the request uri
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/';
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        //init the MyStyle_Design_Profile_Page
        MyStyle_Design_Profile_Page::init();
        
        //call the function
        $output = MyStyle_Design_Profile_Shortcode::output();
        
        //assert that the output includes 'mystyle-design-profile-index-wrapper'
        $this->assertContains( 'mystyle-design-profile-index-wrapper', $output );
    }
    
    /**
     * Test the output function with an invalid design id.
     * @global stdClass $post
     */    
    public function test_output_with_an_invalid_design_id() {
        global $post;
        
        //create the MyStyle_Design_Profile page
        MyStyle_Design_Profile_Page::create();
        
        //mock the request uri
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/999';
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        //Reset the singleton instance (clear out any previously set values)
        MyStyle_Design_Profile_Page::reset_instance();
        
        //init the MyStyle_Design_Profile_Page
        MyStyle_Design_Profile_Page::init();
        
        //call the function
        $output = MyStyle_Design_Profile_Shortcode::output();
        
        //assert that the output includes includes 'Design Not Found'
        $this->assertContains( 'Design not found', $output );
    }
    
}
