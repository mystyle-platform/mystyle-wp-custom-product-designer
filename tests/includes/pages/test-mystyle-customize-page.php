<?php

/**
 * The MyStyleCustomizePageTest class includes tests for testing the 
 * MyStyle_Customize_Page class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleCustomizePageTest extends WP_UnitTestCase {
    
    /**
     * Test the create function
     */    
    public function test_create() {
        //Create the MyStyle Customize page
        $page_id = MyStyle_Customize_Page::create();
        
        $page = get_post($page_id); 
        
        //assert that the page was created and has the expected title
        $this->assertEquals( $page->post_title, 'Customize' );
    }
    
    /**
     * Test the get_id function
     */    
    public function test_get_id() {
        //Create the MyStyle Customize page
        $page_id1 = MyStyle_Customize_Page::create();
        
        $page_id2 = MyStyle_Customize_Page::get_id();
        
        //assert that the page id was successfully retrieved
        $this->assertEquals( $page_id2, $page_id1 );
    }
    
    /**
     * Test the delete function
     */    
    public function test_delete() {
        //Create the MyStyle Customize page
        $page_id = MyStyle_Customize_Page::create();
        
        //Delete the MyStyle Customize page
        MyStyle_Customize_Page::delete();
        
        //attempt to get the page
        $page = get_post($page_id);
        
        //assert that the page was deleted
        $this->assertEquals( $page->post_status, 'trash' );
    }
    
}
