<?php

/**
 * The MyStyleDesignProfilePageTest class includes tests for testing the 
 * MyStyle_Design_Profile_Page class.
 *
 * @package MyStyle
 * @since 1.4.0
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
     * Test the constructor
     * @global wp_filter
     */    
    public function test_constructor() {
        global $wp_filter;
        
        $mystyle_design_profile_page = new MyStyle_Design_Profile_Page();
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );
    }
    
    /**
     * Test the init function with
     * @global stdClass $post
     */    
    public function test_init_with_valid_design_id() {
        global $post;
        
        $design_id = 1;
        
        //Create the Design Profile Page.
        $design_profile_page = MyStyle_Design_Profile_Page::create();
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //mock the request uri  and post as though we were loading the design
        //profile page for design 1
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/' . $design_id;
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        //call the function
        MyStyle_Design_Profile_Page::init();
        
        //get the Mystyle_Design_Profile page singleton
        $mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();
        
        //get the current design from the singleton instance
        $current_design = $mystyle_design_profile_page->get_design();
        
        //assert that the page was created and has the expected title
        $this->assertEquals( $design_id, $current_design->get_design_id() );
        
        //assert that the http response code is set to 200
        $this->assertEquals( 200, $mystyle_design_profile_page->get_http_response_code() );
        
        //assert that the exception is null
        $this->assertEquals( NULL, $mystyle_design_profile_page->get_exception() );
    }
    
    /**
     * Test the init function with no design id
     * @global stdClass $post
     */    
    public function test_init_with_no_design_id() {
        global $post;
        
        $design_id = 1;
        
        //Create a design
        $design = MyStyle_MockDesign::getMockDesign( $design_id );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Reset the singleton instance (to clear out any previously set values)
        MyStyle_Design_Profile_Page::reset_instance();
        
        //Create the Design Profile Page.
        $design_profile_page = MyStyle_Design_Profile_Page::create();
        
        //NOTE: we would normally create a design here but for this test,
        //the design doesn't exist.
        
        //mock the request uri  and post as though we were loading the design
        //index.
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/';
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        //call the function
        MyStyle_Design_Profile_Page::init();
        
        //get the Mystyle_Design_Profile page singleton
        $mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();
        
        //assert that no design is loaded
        $this->assertNull( null, $mystyle_design_profile_page->get_design() );
        
        //assert that the http response code is set to 200
        $this->assertEquals( 200, $mystyle_design_profile_page->get_http_response_code() );
        
        $pager = $mystyle_design_profile_page->get_pager();
        
        $this->assertTrue( ! empty( $pager ) );
    }
    
    /**
     * Test the init function
     * @global stdClass $post
     */    
    public function test_init_with_a_non_existant_design_id() {
        global $post;
        
        $design_id = 999;
        
        //Reset the singleton instance (to clear out any previously set values)
        MyStyle_Design_Profile_Page::reset_instance();
        
        //Create the Design Profile Page.
        $design_profile_page = MyStyle_Design_Profile_Page::create();
        
        //NOTE: we would normally create a design here but for this test,
        //the design doesn't exist.
        
        //mock the request uri  and post as though we were loading the design
        //profile page for design 1 (which doesn't exist
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/' . $design_id;
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        //call the function
        MyStyle_Design_Profile_Page::init();
        
        //get the Mystyle_Design_Profile page singleton
        $mystyle_design_profile_page = MyStyle_Design_Profile_Page::get_instance();
        
        //assert that no design is loaded
        $this->assertNull( null, $mystyle_design_profile_page->get_design() );
        
        //assert that the http response code is set to 404
        $this->assertEquals( 404, $mystyle_design_profile_page->get_http_response_code() );
        
        //assert that the exception is set.
        $this->assertEquals( 'MyStyle_Not_Found_Exception', get_class( $mystyle_design_profile_page->get_exception() ) );
    }
    
    /**
     * Test the create function
     */    
    public function test_create() {
        //Create the MyStyle Design Profile page
        $page_id = MyStyle_Design_Profile_Page::create();
        
        $page = get_post($page_id); 
        
        //assert that the page was created and has the expected title
        $this->assertEquals( 'Community Design Gallery', $page->post_title );
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
     * Test the is_current_post function returns true when the current post is
     * the Design Profile Page.
     * @global stdClass $post
     */    
    public function test_is_current_post_returns_true_when_current_post() {
        global $post;
        
        //Create the Design Profile Page.
        $design_profile_page = MyStyle_Design_Profile_Page::create();
        
        //mock the request uri
        $_SERVER["REQUEST_URI"] = 'http://localhost/designs/';
        $post = new stdClass();
        $post->ID = MyStyle_Design_Profile_Page::get_id();
        
        $this->assertTrue( MyStyle_Design_Profile_Page::is_current_post() );
    }
    
    /**
     * Test the is_current_post function returns true when the current post is
     * the Design Profile Page.
     */    
    public function test_is_current_post_returns_false_when_not_current_post() {
        
        //Create the Design Profile Page.
        $design_profile_page = MyStyle_Design_Profile_Page::create();
        
        $this->assertFalse( MyStyle_Design_Profile_Page::is_current_post() );
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
    
    /**
     * Test the get_design_url function when the post has a custom slug.
     * @global WP_Rewrite $wp_rewrite
     */    
    public function test_get_design_url_with_custom_slug() {
        global $wp_rewrite;
        
        $slug = 'widgets';
        
        //enable page permalinks
        $wp_rewrite->page_structure = '%pagename%';
        
        $design_id = 1;
        $expected_url = 'http://example.org/' . $slug . '/1';
        
        //Create the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::create();
        
        //Change to a custom slug
        wp_update_post( array( 
                            'ID' => MyStyle_Design_Profile_Page::get_id(),
                            'post_name' => $slug,
                        ) 
                    );
        
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
     * Test the get_design_id_url function without permalinks.
     * @global WP_Query $wp_query
     */    
    public function test_get_design_id_from_url_without_permalinks() {
        global $wp_query;
        
        $design_id = 1;
        $query = 'http://localhost/index.php?page_id=1&design_id=' . $design_id;
        
        //init the mystyle frontend to register the design_id query var.
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        //mock the current query
        $wp_query = new WP_Query( $query );
                
        //Call the function
        $returned_design_id = MyStyle_Design_Profile_Page::get_design_id_from_url();
        
        //assert that the exepected design_id is returned
        $this->assertEquals( $design_id, $returned_design_id );
    }
    
    /**
     * Test the get_design_id_url function with permalinks.
     */    
    public function test_get_design_id_from_url_with_permalinks() {
        $design_id = 1;
        $query = 'http://www.example.com/designs/' . $design_id;
        
        //Create the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::create();
        
        //mock the current query
        $_SERVER["REQUEST_URI"] = $query;
                
        //Call the function
        $returned_design_id = MyStyle_Design_Profile_Page::get_design_id_from_url();
        
        //assert that the exepected design_id is returned
        $this->assertEquals( $design_id, $returned_design_id );
    }
    
    /**
     * Test the get_design_id_url function with a custom slug.
     */    
    public function test_get_design_id_from_url_with_a_custom_slug() {
        $design_id = 1;
        $slug = 'widgets';
        $query = 'http://www.example.com/' . $slug . '/' . $design_id;
        
        //Create the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::create();
        
        //Change to a custom slug
        wp_update_post( array( 
                            'ID' => MyStyle_Design_Profile_Page::get_id(),
                            'post_name' => $slug,
                        ) 
                    );
        
        //mock the current query
        $_SERVER["REQUEST_URI"] = $query;
                
        //Call the function
        $returned_design_id = MyStyle_Design_Profile_Page::get_design_id_from_url();
        
        //assert that the exepected design_id is returned
        $this->assertEquals( $design_id, $returned_design_id );
    }
    
}
