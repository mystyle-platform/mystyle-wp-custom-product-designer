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
    public function __construct() {
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'woocommerce_add_order_item_meta', array( &$this, 'add_mystyle_order_item_meta' ), 10, 2 );
    }
    
    /**
     * Init the MyStyle interface.
     */
    public function init() {
        add_filter( 'woocommerce_cart_item_thumbnail', array( &$this, 'modify_cart_item_thumbnail' ), 10, 3 );
    }
    
    /**
     * Add the item meta from the cart to the order.
     * @param number $item_id The item_id of the item being added.
     * @param array $values The values from the cart.
     */
    public static function add_mystyle_order_item_meta( $item_id, $values ) {
        if( isset( $values['mystyle_data'] ) ) {
            wc_add_order_item_meta( $item_id, 'mystyle_data', $values['mystyle_data'] );
        }
    }
    
    /**
     * Override the product thumbnail image.
     * @param string $get_image The current image tag (ex. <img.../>).
     * @param string $cart_item The cart item that we are currently on.
     * @param string $cart_item_key The current cart_item_key.
     * @return string Returns the updated cart image tag.
     */
    public static function modify_cart_item_thumbnail( $get_image, $cart_item, $cart_item_key ) {
        
        $new_image_tag = $get_image;
        
        if( isset( $cart_item['mystyle_data'] ) ) {
            
            $design_id = $cart_item['mystyle_data']['design_id'];
            
            $design = MyStyle_DesignManager::get( $design_id );

            $new_src = 'src="' . $design->get_thumb_url() . '"';
	
            $new_image_tag = preg_replace( '/src\=".*?"/', $new_src, $get_image );
        }
	
        return $new_image_tag;
    }

}
