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
        //update_post_meta( $wc_product->get_id(), '_product_attributes', array('color' => array('red', 'blue'), 'is_taxonomy' => false) );
        var_dump($wc_product->get_attributes());
        $product = new MyStyle_Product( $wc_product );
        
        $product->product_attributes = array( 'attribute_pa_size' );
        
        $product_id = $product->get_id();
        $children = $product->get_children();
        $expected_variation_id = $children[1];
        
        var_dump($product->get_product()->get_variation_attributes());
        //echo 'product_id:' . $product_id;
        
        $variation = wc_get_product_variation_attributes( $expected_variation_id );
        //var_dump($variation);
        
        //$variation = array( 'attribute_pa_size' => 'large' );
        
        $mystyle_wc = new MyStyle_WC();
        
        //call the function
        $returned_variation_id = $mystyle_wc->get_matching_variation( $product_id, $variation );
        
        //echo $expected_variation_id . ':' . $returned_variation_id;
        
        //assert that the modified args include the mystyle_enabled meta key
        $this->assertEquals( $expected_variation_id, $returned_variation_id );
    }
    
}
