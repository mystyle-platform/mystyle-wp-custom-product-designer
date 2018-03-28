<?php

require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-wc.php' );
require_once( MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php' );

/**
 * The MyStyleCustomizerShortcodeTest class includes tests for testing the 
 * MyStyle_Customizer_Shortcode class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleCustomizerShortcodeTest extends WP_UnitTestCase {
    
    /**
     * Override the setUp function.
     */
    function setUp() {
        parent::setUp();
    }
    
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
        
        //mock the GET params
        $_GET['product_id'] = 1;
        $passthru = base64_encode( json_encode( array( 'post' => array( 'quantity' => 2, 'add-to-cart' => 1 ) ) ) );
        $_GET['h'] = $passthru;
        
        //call the function
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( '<iframe', $output );
        
        //assert that the expected passthru is included
        $expectedPassthru = 'passthru=h,' . $passthru;
        $this->assertContains( $expectedPassthru, $output );
    }
    
    /**
     * Test the output function with invalid/no params.
     */    
    public function test_output_with_no_params() {
        
        //call the function
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( 'Sorry, no products are currently available for customization.', $output );
    }
    
    /**
     * Test the output function with no h param.  The function should stuff in
     * some defaults.
     */    
    public function test_output_with_no_h_param() {
        
        //mock the GET params
        $_GET['product_id'] = 1;
        
        //call the function
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( '<iframe', $output );
        
        //build the expected passthru
        $passthru = base64_encode( json_encode( array( 'post' => array( 'quantity' => 1, 'add-to-cart' => 1 ) ) ) );
        $expectedPassthru = 'passthru=h,' . $passthru;
        
        //assert that the expected passthru is included
        $this->assertContains( $expectedPassthru, $output );
    }
    
    /**
     * Test the output function with h and settings parameters.
     */    
    public function test_output_with_settings_param() {
        
        //mock the GET params
        $_GET['product_id'] = 1;
        $passthru = base64_encode( json_encode( array( 'post' => array( 'quantity' => 2, 'add-to-cart' => 1 ) ) ) );
        $_GET['h'] = $passthru;
        
        $settings = base64_encode( json_encode( array( 'redirect_url' => 'https://www.example.com', 'email_skip' => '1', 'print_type' => 'fake' ) ) );
        $_GET['settings'] = $settings;
        
        //call the function
        $output = MyStyle_Customizer_Shortcode::output();
        
        //assert that the output includes an iframe tag
        $this->assertContains( '<iframe', $output );
        
        //assert that the expected passthru is included
        $expectedPassthru = 'passthru=h,' . $passthru;
        $this->assertContains( $expectedPassthru, $output );
        
        //assert that the expected settings are included
        $expectedSettings = 'settings=' . $settings;
        $this->assertContains( $expectedSettings, $output );
    }
    
}
