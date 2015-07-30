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
     * Test the modify_woocommerce_shortcode_products_query function with valid parameters.
     */    
    public function test_modify_woocommerce_shortcode_products_query() {
        
        //Mock the args
        $args = array();
        $args['meta_query'] = array();
        
        $modified_args = MyStyle_Customizer_Shortcode::modify_woocommerce_shortcode_products_query( $args );
        
        //assert that the modified args include the mystyle_enabled meta key
        $this->assertContains( '_mystyle_enabled', $modified_args['meta_query'][0]['key'] );
    }
    
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
    public function test_output_with_no_params() {
        
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( 'Sorry, no products are currently available for customization.', $output );
    }
    
}
