<?php

/**
 * MyStyle Order class. 
 * 
 * The MyStyle Order class is used to wrap the WC_Order class to allow us to
 * support multiple versions of WooCommerce.
 * 
 * We don't extend the WC_Order because we can't guarantee that our plugin will
 * be included after WooCommerce.
 *
 * @package MyStyle
 * @since 1.7.0
 * @todo Add unit testing
 */
class MyStyle_Order {
    
    public static $MYSTYLE_DATA_KEY = 'mystyle_data';
    
    /** @var \WC_Order */
    private $order;
    
    /**
     * Constructor
     * @param \WC_Order $order The WC_Order that we are extending.
     */
    public function __construct( WC_Order $order ) {
        $this->order = $order;
    }
    
    /**
     * Gets the order id.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return number Returns the order id.
     */
    public function get_id() {
        if( method_exists( $this->order, 'get_id' ) ) {
            $id = $this->order->get_id();
        } else {
            $id = $this->order->ID; 
        }
        
        return $id;
    }
    
    /**
     * Gets the order date (date created).
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return string Returns the order date as a string.
     */
    public function get_order_date() {
        if( method_exists( $this->order, 'get_date_created' ) ) {
            $order_date = $this->order->get_date_created();
        } else { //WC  < 3.0
            $order_date = $this->order->order_date;
        }
        
        return $order_date;
    }
    
    /**
     * Gets the order shipping first name.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return string Returns the shipping first name.
     */
    public function get_shipping_first_name() {
        if( method_exists( $this->order, 'get_shipping_first_name' ) ) {
            $first_name = $this->order->get_shipping_first_name();
        } else { //WC  < 3.0
            $first_name = $this->order->shipping_first_name;
        }
        
        return $first_name;
    }
    
    /**
     * Gets the order shipping last name.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return string Returns the shipping last name.
     */
    public function get_shipping_last_name() {
        if( method_exists( $this->order, 'get_shipping_last_name' ) ) {
            $last_name = $this->order->get_shipping_last_name();
        } else { //WC  < 3.0
            $last_name = $this->order->shipping_last_name;
        }
        
        return $last_name;
    }
    
    /**
     * Gets the order billing email.
     * 
     * Works with WC 2.x and WC 3.x.
     * 
     * @return string Returns the billing email.
     */
    public function get_billing_email( $context = 'view' ) {
        if( method_exists( $this->order, 'get_billing_email' ) ) {
            $email = $this->order->get_billing_email();
        } else { //WC  < 3.0
            $email = $this->order->billing_email;
        }
        
        return $email;
    }
    
    /**
     * Adds the is of the passed design to the item with the passed id.
     * @param integer $item_id
     * @param \MyStyle_Design $design
     */
    public function add_design_to_item( $item_id, \MyStyle_Design $design ) {
        
        $mystyle_data = array( 'design_id' => $design->get_design_id() );
        
        
        /** @var $items array */
        $items = $this->order->get_items();
        
        /* @var $item \WC_Order_Item */
        $item = $items[ $item_id ];
        if( is_object( $item ) ) {
            $item->add_meta_data( self::$MYSTYLE_DATA_KEY, $mystyle_data );
            $item->save_meta_data();
        } else { // WC < 3.0
            wc_add_order_item_meta( 
                            $item_id, 
                            self::$MYSTYLE_DATA_KEY, 
                            $mystyle_data
                        );
        }
    }
    
    /**
     * Gets the design (if any) attached to the item corresponding to the passed
     * item id.
     * @param integer $item_id The id of the item that you want the design of.
     * @return \MyStyle_Design|null Returns the design attached to the item.
     * Returns null if there is none.
     */
    public function get_design_from_item( $item_id ) {
        /** @var $design \MyStyle_Design */
        $design = null;
        
        /** @var $items array */
        $items = $this->order->get_items();
        
        /* @var $item \WC_Order_Item */
        $item = $items[ $item_id ];
        if( is_object( $item ) ) {
            $mystyle_data = $item->get_meta( self::$MYSTYLE_DATA_KEY );
        } else { // WC < 3.0
            $mystyle_data = wc_get_order_item_meta( 
                            $item_id, 
                            'mystyle_data'
                        );
        }
        
        if( $mystyle_data != null ) {
            $design_id = $mystyle_data['design_id'];
            $design = MyStyle_DesignManager::get( $design_id );
        }
        
        return $design;
    }
            
    
    /**
     * Gets the product represented by the passed item id.
     * @param integer $item_id The id of the item that you want the product of.
     * @return \MyStyle_Product|null Returns the product associated with the
     * item. Returns null if there is none.
     */
    public function get_order_item_product( $item_id ) {
        /** @var $design \MyStyle_Product */
        $product = null;
        
        /** @var $items array */
        $items = $this->order->get_items();
        
        /* @var $item \WC_Order_Item_Product */
        $item = $items[ $item_id ];
        if( is_object( $item ) ) {
            //var_dump($item);
            $product = new \MyStyle_Product( $item->get_product() );
        } else { //WC < 3.0
            $product_id = $item['product_id'];
            $product = new \MyStyle_Product( wc_get_product( $product_id ) );
        }
        
        return $product;
    }

}