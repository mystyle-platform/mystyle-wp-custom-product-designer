<?php

/**
 * MyStyle Order class. 
 * 
 * The MyStyle Order class is used to wrap the WC_Order class to allow us to
 * support multiple versions of WooCommerce.
 *
 * @package MyStyle
 * @since 1.7.0
 */
class MyStyle_Customer extends WC_Customer {
    
    /**
     * Constructor
     * @param \WC_Customer $order The WC_Customer that we are extending.
     */
    public function __construct( WC_Customer $customer ) {
        parent::__construct( $customer );
    }
    
    /**
     * Gets the customer id.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return number Returns the customer id.
     */
    public function get_id() {
        return $this->id;
    }

}