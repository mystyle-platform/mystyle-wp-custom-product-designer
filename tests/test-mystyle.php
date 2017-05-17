<?php

/**
 * The MyStyleTest class includes tests for testing the main mystyle.php file
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyleTest extends WP_UnitTestCase {

    /**
     * Assert that the expected constants are declared and accessible.
     */    
    function testConstants() {
        $this->assertNotEmpty( MYSTYLE_PATH );
        $this->assertNotEmpty( MYSTYLE_INCLUDES );
        $this->assertNotEmpty( MYSTYLE_BASENAME );
        $this->assertNotEmpty( MYSTYLE_SERVER );
        $this->assertNotEmpty( MYSTYLE_VERSION );
        $this->assertNotEmpty( MYSTYLE_OPTIONS_NAME );
        $this->assertNotEmpty( MYSTYLE_NOTICES_NAME );
        $this->assertNotEmpty( MYSTYLE_CUSTOMIZE_PAGEID_NAME );
        $this->assertNotEmpty( MYSTYLE_DESIGN_PROFILE_PAGEID_NAME );
    }

    /**
     * Assert that the mystyle_customizer shortcode is registered
     */    
    function testCustomizerShortcodeIsRegistered() {
        global $shortcode_tags;

        $this->assertArrayHasKey( 'mystyle_customizer', $shortcode_tags );
    }
    
    /**
     * Assert that the mystyle_design_profile shortcode is registered
     */    
    function testDesignProfileShortcodeIsRegistered() {
        global $shortcode_tags;

        $this->assertArrayHasKey( 'mystyle_design_profile', $shortcode_tags );
    }
    
    /**
     * Test the site_has_customizable_products function returns true when
     * customizable products exist.
     */
    public function test_site_has_customizable_products_returns_false_when_customizable_products_dont_exist() {
        //create a regular test product
        $product_id = create_wc_test_product('Test Product');
        
        //call the function
        $result = MyStyle::site_has_customizable_products();
        
        $this->assertFalse( $result );
    }
    
    
    /**
     * Test the site_has_customizable_products function returns true when
     * customizable products exist.
     */
    public function test_site_has_customizable_products_returns_true_when_customizable_products_exist() {
        //create a regular test product
        $product_id = create_wc_test_product('Test Product');
        
        //enable mystyle for the product
        update_post_meta( $product_id, '_mystyle_enabled', 'yes' );
        
        //call the function
        $result = MyStyle::site_has_customizable_products();
        
        $this->assertTrue( $result );
    }
    
}
