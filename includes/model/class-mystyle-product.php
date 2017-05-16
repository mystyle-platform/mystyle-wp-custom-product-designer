<?php

/**
 * MyStyle Product class. 
 * 
 * The MyStyle Product class is used to wrap the WC_Product class to allow us to
 * support multiple versions of WooCommerce.
 *
 * @package MyStyle
 * @since 1.7.0
 * @todo Add unit testing
 */
class MyStyle_Product extends WC_Product {
    
    public static $MYSTYLE_DATA_KEY = 'mystyle_data';
    
    /**
     * Constructor
     * @param \WC_Product $product The WC_Product that we are extending.
     */
    public function __construct( WC_Product $product ) {
        parent::__construct( $product );
    }
    
    /**
     * Gets the product id.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return number Returns the product id.
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Gets the product type.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return number Returns the product type.
     */
    public function get_type() {
        if( is_callable( 'parent::get_type' ) ) {
            $product_type = parent::get_type();
        } else {
            $product_type = $this->product_type;
        }
        
        return $product_type;
    }

}