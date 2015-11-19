<?php

require_once(MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-addons-page.php');

/**
 * The MyStyleAddonsPageTest class includes tests for testing the 
 * MyStyle_Addons_Page class.
 *
 * @package MyStyle
 * @since 0.1.16
 */
class MyStyleAddonsPageTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_addons_page = new MyStyle_Addons_Page();
        
        global $wp_filter;
        
        //Assert that the page is registered.
        $function_names = get_function_names( $wp_filter['admin_menu'] );
        $this->assertContains( 'add_page_to_menu', $function_names );
    }
    
    /**
     * Test the render_page function.
     */    
    public function test_render_page() {
        $mystyle_addons_page = new MyStyle_Addons_Page();
        
        //Assert that the options page was rendered
        ob_start();
        $mystyle_addons_page->render_page();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'MyStyle Add-ons', $outbound );
    }
    
}
