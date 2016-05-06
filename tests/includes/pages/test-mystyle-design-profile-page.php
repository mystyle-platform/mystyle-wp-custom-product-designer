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
    
}
