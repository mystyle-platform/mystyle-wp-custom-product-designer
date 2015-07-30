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
        add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_cart_item_from_session' ), 10, 3 );
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
     * @return Returns false on failure. On success, returns the ID of the inserted row.
     */
    public static function add_mystyle_order_item_meta( $item_id, $values ) {
        if( isset( $values['mystyle_data'] ) ) {
            return wc_add_order_item_meta( $item_id, 'mystyle_data', $values['mystyle_data'] );
        }
    }
    
    /**
     * Filter the woocommerce_get_cart_item_from_session and add our session 
     * data.
     * @param array $session_data The current session_data.
     * @param array $values The values that are to be stored in the session.
     * @param string $key The key of the cart item.
     * @return string Returns the updated cart image tag.
     */
    public static function get_cart_item_from_session( $session_data, $values, $key ) {
        
        // Fix for WC 2.2 (if our data is missing from the cart item, get it from the session variable 
        if( ! isset($session_data['mystyle_data'] ) ) {
            $cart_item_data = WC()->session->get( 'mystyle_' . $key );
            $session_data['mystyle_data'] = $cart_item_data['mystyle_data'];
        }
	
        return $session_data;
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
    
    /**
     * Function that looks to see if any products are mystyle enabled.
     * @return boolean Returns true if at least one product is customizable.
     * @todo: write unit tests
     */
    public static function site_has_customizable_products() {
        $args = array(
                    'post_type'      => 'product',
                    'numberposts' => 1,
                    'meta_key'       => '_mystyle_enabled',
                    'meta_value'     => 'yes',
                );

        $customizable_products = get_posts( $args );
        
        if( ! empty( $customizable_products ) ) {
            return true;
        } else {
            return false;
        }
    }
    
}
