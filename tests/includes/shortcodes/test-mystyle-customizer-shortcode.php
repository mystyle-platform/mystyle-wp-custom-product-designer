<?php

/**
 * The MyStyleCustomizerShortcodeTest class includes tests for testing the 
 * MyStyle_Customizer_Shortcode class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleCustomizerShortcodeTest extends WP_UnitTestCase {
    
    /**
     * Test the output function
     */    
    public function test_output() {
        
        $_GET['product_id'] = 1;
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( '<iframe', $output );
    }
    
}
