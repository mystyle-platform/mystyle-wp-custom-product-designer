<?php

/**
 * MyStyle_Order class.
 * The MyStyle_Order class sets up and controls the MyStyle order related hooks,
 * etc.
 *
 * @package MyStyle
 * @since 1.4.10
 */
class MyStyle_Order {
    
    /**
     * Singleton class instance
     * @var MyStyle_Frontend
     */
    private static $instance;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_filter( 'woocommerce_order_item_product', array( &$this, 'filter_order_item_product' ), 10, 2 );
    }
    
    /**
     * Filter the construction of the order item product.
     * @param array $product
     * @param array $order_item
     * @return mixed Returns a WC_Product or one of its child classes.
     */
    public function filter_order_item_product( $product, $order_item ) {
        
        //Note: we put the require_once here because we need to wait until after woocommerce is bootstrapped
        require_once( MYSTYLE_INCLUDES . 'model/class-mystyle-product.php' );
        require_once( MYSTYLE_INCLUDES . 'model/class-mystyle-product-variation.php' );
        
        //convert the product to a MyStyle_Product (if it has mystyle_data)
        if( array_key_exists('mystyle_data', $order_item ) ) {
            $mystyle_data = unserialize( $order_item['mystyle_data'] );
            $design_id = $mystyle_data['design_id'];
            $design = MyStyle_DesignManager::get( $design_id );
            if( get_class( $product ) == 'WC_Product_Variation' ) {
                $product = new MyStyle_Product_Variation( $product, $design );
            } else {
                $product = new MyStyle_Product( $product, $design );
            }
        }
        
        return $product;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Design_Profile_Page Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle_Design_Profile_Page Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}

