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
        $wc_product = WC_Helper_Product::create_variation_product();
        
        //fix the test data (WC < 3.0 is broken)
        fix_variation_product( $wc_product );
        
        //wrap the product to get the id
        $product = new MyStyle_Product( $wc_product );
        $product_id = $product->get_id();
        
        //create the MyStyle_WC instance.
        $mystyle_wc = new MyStyle_WC();
        
        //get all children of the product.
        $children = $product->get_children();
        
        // ------------------- TEST THE FIRST VARIATION ------------------//
        //get the first variation
        $expected_variation_id = $children[0];
        $variation = wc_get_product_variation_attributes( $expected_variation_id );
        
        //call the function
        $returned_variation_id = $mystyle_wc->get_matching_variation( $product_id, $variation );
        
        //assert that the modified args include the mystyle_enabled meta key
        $this->assertEquals( $expected_variation_id, $returned_variation_id );
        
        // ------------------- TEST THE SECOND VARIATION ------------------//
        //get the first variation
        $expected_variation_id = $children[1];
        $variation = wc_get_product_variation_attributes( $expected_variation_id );
        
        //call the function
        $returned_variation_id = $mystyle_wc->get_matching_variation( $product_id, $variation );
        
        //assert that the modified args include the mystyle_enabled meta key
        $this->assertEquals( $expected_variation_id, $returned_variation_id );
         
    }
    
}
