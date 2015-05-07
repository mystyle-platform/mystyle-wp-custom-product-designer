<?php

/**
 * MyStyle Main class. 
 * 
 * The MyStyle class sets up and controls the MyStyle plugin for features used
 * by both the frontend and the admin.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle {
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    function __construct() {
        add_action('woocommerce_add_order_item_meta', array( &$this, 'mystyle_woocommerce_add_order_item_meta'), 10, 2);
    }
    
    /**
     * Add the item meta from the cart to the order.
     * @param number $item_id The item_id of the item being added.
     * @param array $values The values from the cart.
     */
    public function mystyle_woocommerce_add_order_item_meta( $item_id, $values ) {
        if(isset($values['mystyle_data']) ) {
            woocommerce_add_order_item_meta( $item_id, 'mystyle_data', $values['mystyle_data']);
        }
    }

}


