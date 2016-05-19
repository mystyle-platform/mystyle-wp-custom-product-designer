<?php

/**
 * The MyStyleDesignProfilePageTest class includes tests for testing the 
 * MyStyle_Design_Profile_Page class.
 *
 * @package MyStyle
 * @since 1.3.2
 */
class MyStyleDesignProfilePageTest extends WP_UnitTestCase {
    
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
     * Test the create function
     */    
    public function test_create() {
        //Create the MyStyle Design Profile page
        $page_id = MyStyle_Design_Profile_Page::create();
        
        $page = get_post($page_id); 
        
        //assert that the page was created and has the expected title
        $this->assertEquals( 'Design Profile', $page->post_title );
    }
    
    /**
     * Test the get_id function
     */    
    public function test_get_id() {
        //Create the MyStyle Design Profile page
        $page_id1 = MyStyle_Design_Profile_Page::create();
        
        $page_id2 = MyStyle_Design_Profile_Page::get_id();
        
        //assert that the page id was successfully retrieved
        $this->assertEquals( $page_id2, $page_id1 );
    }
    
    /**
     * Test the exists function
     */    
    public function test_exists() {
        
        //assert that the exists function returns false before the page is created
        $this->assertFalse( MyStyle_Design_Profile_Page::exists() );
        
        //Create the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::create();
        
        //assert that the exists function returns true after the page is created
        $this->assertTrue( MyStyle_Design_Profile_Page::exists() );
    }
    
    /**
     * Test the delete function
     */    
    public function test_delete() {
        //Create the MyStyle Design Profile page
        $page_id = MyStyle_Design_Profile_Page::create();
        
        //Delete the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::delete();
        
        //attempt to get the page
        $page = get_post($page_id);
        
        //assert that the page was deleted
        $this->assertEquals( $page->post_status, 'trash' );
    }
    
    /**
     * Test the get_design_url function without permalinks
     * @global WP_Rewrite $wp_rewrite
     */    
    public function test_get_design_url_without_permalinks() {
        global $wp_rewrite;
        
        //disable page permalinks
        //unset( $wp_rewrite->page_structure );
        $wp_rewrite->page_structure = null;
        
        $design_id = 1;
        
        //Create the MyStyle Design Profile page
        $page_id = MyStyle_Design_Profile_Page::create();
        
        //Build the expected url
        $expected_url = 'http://example.org/?page_id=' . $page_id . '&design_id=1';
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $url = MyStyle_Design_Profile_Page::get_design_url( $design );
        
        //assert that the exepected $url was returned
        $this->assertEquals( $expected_url, $url );
    }
    
    /**
     * Test the get_design_url function with permalinks
     * @global WP_Rewrite $wp_rewrite
     */    
    public function test_get_design_url_with_permalinks() {
        global $wp_rewrite;
        
        //enable page permalinks
        $wp_rewrite->page_structure = '%pagename%';
        
        $design_id = 1;
        $expected_url = 'http://example.org/designs/1';
        
        //Create the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::create();
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Call the function
        $url = MyStyle_Design_Profile_Page::get_design_url( $design );
        
        //assert that the exepected $url was returned
        $this->assertEquals( $expected_url, $url );
    }
    
}
