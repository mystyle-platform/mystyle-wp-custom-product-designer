<?php

/**
 * MyStyle WooCommerce Admin Order class.
 * 
 * The MyStyle WooCommerce Admin Order class hooks MyStyle into the WooCommerce
 * Order admin interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_WooCommerce_Admin_Order {
    
    /**
     * Constructor, constructs the class and registers hooks.
     */
    public function __construct() {
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
    }
    
    /**
     * Init the mystyle woocommerce admin
     */
    function admin_init() {
        add_action( 'woocommerce_admin_order_item_headers', array( &$this, 'add_order_item_header' ) );
        add_action( 'woocommerce_admin_order_item_values', array( &$this, 'admin_order_item_values' ), 10, 3 );        
    }
    
    /**
     * Add the mystyle column header to the order items table.
     */
    public static function add_order_item_header() {
        ?>
            <th class="item-mystyle"><?php _e( 'MyStyle', 'woocommerce' ); ?></th>
        <?php
    }
    
    /**
     * Add the mystyle column body to the order items table.
     */
    public static function admin_order_item_values( $_product, $item, $item_id ) {

        $mystyle_data = null;
        if( isset( $item['mystyle_data'] ) ) {            
            /**
             * NOTE: We aught to be able to get the data by unserializing
             * $item['mystyle_data'], this however fails because the data comes
             * through without the tabs and carriage returns which throws the
             * string counts off.  To work around this, we just get the data
             * directly using a database call.
             */
            $mystyle_data = wc_get_order_item_meta( $item_id, 'mystyle_data' );
        }
    
        ?>
        <td class="item-mystyle">
            <?php if( $mystyle_data != null ) : ?>
                <div class="mystyle-item-toggle">
                    <a class="mystyle-item-link" title="Click to toggle" onclick="mystyleOrderItemDataToggleVis(<?php echo $item_id; ?>)">MyStyle Data</a>
                    <a id="mystyle-item-handle-<?php echo $item_id; ?>" class="mystyle-item-handle" title="Click to toggle" onclick="mystyleOrderItemDataToggleVis(<?php echo $item_id; ?>)"></a>
                </div>
                <div class="mystyle-item-data" id="mystyle-item-data-<?php echo $item_id; ?>" style="display:none;">
                    <div>
                        <a href="<?php echo $mystyle_data['print_url']; ?>" target="_blank">Print Image</a>&nbsp;&nbsp;
                        <a href="<?php echo $mystyle_data['web_url']; ?>" target="_blank">Web Preview</a>
                    </div>
                    <img src="<?php echo $mystyle_data['thumb_url']; ?>"/>
 
                </div>
            <?php endif; ?>
        </td>
        <?php

    }

}
