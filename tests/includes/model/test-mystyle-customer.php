<?php

/**
 * The MyStyleCustomerTest class includes tests for testing the MyStyle_Customer
 * class.
 *
 * @package MyStyle
 * @since 2.0
 */
class MyStyleCustomerTest extends WP_UnitTestCase {
    
    /**
     * Test the get_id function.
     */    
    public function test_get_id() {
        
        //set up the test data
        $customer = new MyStyle_Customer( WC_Helper_Customer::create_mock_customer() );
    
        //call the function
        $id = $customer->get_id();
        
        //assert that a customer id is returned.
        $this->assertTrue( $id > 0 );
    }
    
}
