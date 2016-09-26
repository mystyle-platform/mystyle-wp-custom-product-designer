<?php

/**
 * MyStyle_Product class. 
 * 
 * The MyStyle_Product class extends a WooCommerce product with changes for
 * customizable products.
 *
 * @package MyStyle
 * @since 1.4.6
 */
class MyStyle_Product extends WC_Product {
    
    /**
     * The design id of the product.
     * @var MyStyle_Design
     */
    private $design;
    
    /**
     * If this product is in the cart, store the cart_item_key here.
     * @var string
     */
    private $cart_item_key;
    
    /**
     * Constructor.
     *
     * @access public
     * @param mixed $product
     * @param MyStyle_Design $design
     * @param string $cart_item_key
     */
    public function __construct( $product, $design = null, $cart_item_key = null ) {
        $this->product_type = 'customizable';
        $this->design = $design;
        $this->cart_item_key = $cart_item_key;
        
        parent::__construct( $product );
    }
    
    /**
     * Get's a permalink to the product.  If $this->design is set, return a
     * link to the design profile page instead.
     *
     * @param array $item_object (optional) If the cart/order item is passed, we
     * can get a link containing the exact attributes selected for the 
     * variation, rather than the default attributes.
     * @return string
     */
    public function get_permalink( $item_object = null ) {
        
        //if there is a design, return the url to the design profile page
        if( $this->design != null ) {
            $url = MyStyle_Design_Profile_Page::get_design_url( $this->design, $this->cart_item_key );
        } else { //no design id, return a link to the product page
            $url = get_permalink( $this->id );
        }
        
        return $url;
    }

    

}
