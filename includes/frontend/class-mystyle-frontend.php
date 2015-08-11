<?php

/**
 * MyStyle FrontEnd class.
 * The MyStyle FrontEnd class sets up and controls the MyStyle front end
 * interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_FrontEnd {
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_filter( 'woocommerce_add_to_cart_handler', array( &$this, 'add_to_cart_handler_filter' ), 10, 2 );
        
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'woocommerce_before_add_to_cart_button', array( &$this, 'before_add_to_cart_button' ), 10, 0 );
        add_action( 'woocommerce_after_add_to_cart_button', array( &$this, 'after_add_to_cart_button' ), 10, 0 );
        add_action( 'woocommerce_loop_add_to_cart_link', array( &$this, 'loop_add_to_cart_link' ), 10, 2 );
        add_action( 'woocommerce_add_to_cart_handler_mystyle_customizer', array( &$this, 'mystyle_add_to_cart_handler' ), 10, 1 );
    }
    
    /**
     * Init the MyStyle front end.
     */
    public static function init() {
        //Add the MyStyle frontend stylesheet to the WP frontend head
        wp_register_style( 'myStyleFrontEndStylesheet', MYSTYLE_ASSETS_URL . 'css/frontend.css' );
        wp_enqueue_style( 'myStyleFrontEndStylesheet' );
        
        //Add the swfobject.js file to the WP head
        wp_register_script( 'swfobject', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js' );
        wp_enqueue_script( 'swfobject' );
    }
    
    /**
     * Wrap the section with a mystyle-customizable-product class
     */
    public static function before_add_to_cart_button() {
        global $post;
        
        $current_product_id = $post->ID;
        $mystyle_enabled = get_post_meta( $current_product_id, '_mystyle_enabled', true );
        
        if( $mystyle_enabled == 'yes' ) {
            echo '<div class="mystyle-customizable-product">'; //open the mystyle_customizable wrapper div
        }
    }
    
    /**
     * Add Customize button after the add to cart button.
     */
    public static function after_add_to_cart_button() {
        global $post;
        
        $customize_page_id = MyStyle_Customize_Page::get_id();
        $current_product_id = $post->ID;
        $mystyle_enabled = get_post_meta( $current_product_id, '_mystyle_enabled', true );
        
        if( $mystyle_enabled == 'yes' ) {
            $customizer_url = add_query_arg( 'product_id', $current_product_id, get_permalink( $customize_page_id ) );
            
            //TODO: Add multilingual support
            $out  =     '<button class="mystyle_customize_button button alt" type="submit">Customize</button>';
            $out .= '</div>'; //close the mystyle_customizable wrapper div
            echo $out;
        }
    }
    
    /**
     * Modify the add to cart link for product listings
     * @param type $link The "Add to Cart" link (html)
     * @param type $product The current product.
     * @return type Returns the html to be outputted.
     */
    public static function loop_add_to_cart_link( $link, $product ) {
        
        $mystyle_enabled = get_post_meta( $product->id, '_mystyle_enabled', true );
        
        if( $mystyle_enabled == 'yes' ) {
            $customize_page_id = MyStyle_Customize_Page::get_id();
            $customizer_url = add_query_arg( 'product_id', $product->id, get_permalink( $customize_page_id ) );
            
            $customize_link = sprintf( 
                '<a ' .
                    'href="%s" ' . 
                    'rel="nofollow" ' .
                    'class="button %s product_type_%s" ' .
                '>%s</a>',
		esc_url( $customizer_url ),
		$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
		esc_attr( $product->product_type ),
		esc_html( "Customize" ) ); //TODO: Add multilingual support
	
            
            $ret = $customize_link;
        } else {
            $ret = $link;
        }
        
        return $ret;
    }
    
    /**
     * Filter to add our add_to_cart handler for customizable products.
     * @param string $handler The current add_to_cart handler.
     * @param type $product The current product.
     * @return string Returns the name of the handler to use for the add_to_cart
     * action.
     * @todo Add unit testing
     */
    function add_to_cart_handler_filter( $handler, $product ) {
        var_dump($product);
        if($product != null) {
            $product_id = $product->id;
        } else {
            $product_id = absint( $_REQUEST['add-to-cart'] );
        }
        $mystyle_enabled = get_post_meta( $product_id, '_mystyle_enabled', true );

        if($mystyle_enabled) {
            $handler = 'mystyle_customizer';
        }
    
        return $handler;
    }
    
    /**
     * The MyStyle add_to_cart handler.  Handles the add_to_cart action for
     * Customizable products.
     * @param string $url The current url.
     * @todo Add unit testing
     */
    function mystyle_add_to_cart_handler( $url ) {
        $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );

        $customize_page_id = MyStyle_Customize_Page::get_id();
        $customizer_url = add_query_arg( 'product_id', $product_id, get_permalink( $customize_page_id ) );
        wp_safe_redirect( $customizer_url );
        exit;
    }

}


