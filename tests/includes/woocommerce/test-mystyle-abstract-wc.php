<?php

/**
 * The MyStyleWCTest class includes tests for testing the MyStyle_WC class.
 *
 * @package MyStyle
 * @since 2.0.0
 */
class MyStyleWCTest extends WP_UnitTestCase {
    
    /**
     * Test the get_matching_variation function.
     */    
    public function test_get_matching_variation() {
        //set up the test data
        $product = WC_Helper_Product::create_variation_product();
        $product_id = $product->get_id();
        $children     = $product->get_children();
        $expected_variation_id = $children[0];
        
        //$attrib = wc_get_product_variation_attributes($expected_variation_id);
        //var_dump($attrib);
        
        $variation = array('attribute_pa_size' => 'small');
        
        $mystyle_wc = new MyStyle_WC();
        
        //call the function
        $returned_variation_id = $mystyle_wc->get_matching_variation( $product_id, $variation );
        
        //assert that the modified args include the mystyle_enabled meta key
        $this->assertEquals( $expected_variation_id, $returned_variation_id );
    }
    
}
