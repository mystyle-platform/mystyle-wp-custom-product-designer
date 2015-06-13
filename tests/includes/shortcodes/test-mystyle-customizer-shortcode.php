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
     * Test the output function with valid parameters.
     */    
    public function test_output_with_valid_params() {
        
        $_GET['product_id'] = 1;
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( '<iframe', $output );
    }
    
    /**
     * Test the output function with invalid/no params.
     */    
    public function test_output_with_invalid_params() {
        
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( 'You\'ll need to select a product to customize first!', $output );
    }
    
}
