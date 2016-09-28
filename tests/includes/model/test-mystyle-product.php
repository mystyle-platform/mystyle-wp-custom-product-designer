<?php

//require_once( MYSTYLE_PATH . 'tests/mocks/mock-wc-product.php' );
require_once( MYSTYLE_PATH . '../woocommerce/includes/abstracts/abstract-wc-product.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );
require_once( MYSTYLE_INCLUDES . 'model/class-mystyle-product.php' );



/**
 * The MyStyleProductTest class includes tests for testing the MyStyle_Product
 * class.
 *
 * @package MyStyle
 * @since 1.4.10
 */
class MyStyleProductTest extends WP_UnitTestCase {

    /**
     * Test the constructor.
     */    
    function test_constructor() {
        //mock a product
        $mock_product = new stdClass();
        
        //instantiate a MyStyle_Product
        $ms_product = new MyStyle_Product( $mock_product );
        
        //Assert that the product is constructed
        $this->assertEquals( 'MyStyle_Product', get_class( $ms_product ) );
    }
    
    /**
     * Test the get_permalink function.
     */    
    function test_get_permalink() {
        $mock_product = new stdClass();
        
        //Create a design
        $result_object = new MyStyle_MockDesignQueryResult( 1 );
        $design = MyStyle_Design::create_from_result_object( $result_object );
        
        //instantiate a MyStyle_Product
        $ms_product = new MyStyle_Product( 
                            $mock_product,
                            $design
                        );
        
        //Create the MyStyle Design Profile page
        MyStyle_Design_Profile_Page::create();
        
        //call the function
        $permalink = $ms_product->get_permalink();
        
        //Assert that the correct page_id is included in the url
        $this->assertContains( 'page_id=' . MyStyle_Design_Profile_Page::get_id() , $permalink );
        
        
        $this->assertContains( 'design_id=' . $design->get_design_id(), $permalink );
    }
    
}
