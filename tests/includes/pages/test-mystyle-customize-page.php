<?php

require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );

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
        $this->assertEquals( 'Customize', $page->post_title );
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
     * Test the exists function
     */    
    public function test_exists() {
        
        //assert that the exists function returns false before the page is created
        $this->assertFalse( MyStyle_Customize_Page::exists() );
        
        //Create the MyStyle Customize page
        MyStyle_Customize_Page::create();
        
        //assert that the exists function returns true after the page is created
        $this->assertTrue( MyStyle_Customize_Page::exists() );
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
    
    /**
     * Test the get_design_url function
     */    
    public function test_get_design_url() {
        
        //Create the MyStyle Customize page
        $page_id = MyStyle_Customize_Page::create();
        
        //Build the expected url
        $expected_url = 'http://example.org/?page_id=' . $page_id . '&product_id=0&design_id=1&h=eyJwb3N0Ijp7InF1YW50aXR5IjoxLCJhZGQtdG8tY2FydCI6MH19';
        
        //Create a design
        $result_object = new MyStyle_MockDesignQueryResult( 1 );
        $design = MyStyle_Design::create_from_result_object( $result_object );
        
        //Call the function
        $url = MyStyle_Customize_Page::get_design_url( $design );
        
        //assert that the exepected $url was returned
        $this->assertEquals( $expected_url, $url );
    }
    
}
