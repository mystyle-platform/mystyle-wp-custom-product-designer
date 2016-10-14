<?php

/**
 * MyStyle_Order class.
 * The MyStyle_Order class sets up and controls the MyStyle order related hooks,
 * etc.
 *
 * @package MyStyle
 * @since 1.5.0
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
        
        add_action( 'woocommerce_add_order_item_meta', array( &$this, 'add_mystyle_order_item_meta' ), 10, 2 );
        add_action( 'woocommerce_order_status_completed', array( &$this, 'on_order_completed' ), 10, 1 );
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
     * Add the item meta from the cart to the order.
     * @param number $item_id The item_id of the item being added.
     * @param array $values The values from the cart.
     * @return Returns false on failure. On success, returns the ID of the inserted row.
     */
    public function add_mystyle_order_item_meta( $item_id, $values ) {
        if( isset( $values['mystyle_data'] ) ) {
            return wc_add_order_item_meta( $item_id, 'mystyle_data', $values['mystyle_data'] );
        }
    }
    
    /**
     * After the order is completed, do the following for all mystyle enabled
     * products in the order:
     *  * Increment the design purchase count.
     * @param number $order_id The order_id of the new order.
     */
    function on_order_completed( $order_id ) {

        // order object (optional but handy)
        $order = new WC_Order( $order_id );

        if ( count( $order->get_items() ) > 0 ) {
            foreach ( $order->get_items() as $item_id => $item ) {

                if ( isset( $item['mystyle_data'] ) ) {  
                    $mystyle_data = maybe_unserialize( $item['mystyle_data'] );
                    $design_id = $mystyle_data['design_id'];

                    /** @var \WP_User */
                    $current_user = wp_get_current_user();
                    
                    /** @var \MyStyle_Session */
                    $session = MyStyle_SessionHandler::get();
                    
                    /** @var \MyStyle_Design */
                    $design = MyStyle_DesignManager::get( $design_id, $current_user, $session );
                    
                    //Increment the design purchase count
                    $design->increment_purchase_count();
                    MyStyle_DesignManager::persist( $design );
                }
            }
        }
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

