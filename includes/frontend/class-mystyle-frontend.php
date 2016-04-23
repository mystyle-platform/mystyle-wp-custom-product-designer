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
        add_filter( 'body_class', array( &$this, 'filter_body_class' ), 10, 1 );
        add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
        add_filter( 'woocommerce_product_single_add_to_cart_text', array( &$this, 'filter_cart_button_text' ), 10, 1 ); 
        add_filter( 'woocommerce_add_to_cart_handler', array( &$this, 'filter_add_to_cart_handler' ), 10, 2 );
        
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'woocommerce_loop_add_to_cart_link', array( &$this, 'loop_add_to_cart_link' ), 10, 2 );
        add_action( 'woocommerce_add_to_cart_handler_mystyle_customizer', array( &$this, 'mystyle_add_to_cart_handler' ), 10, 1 );
    }
    
    /**
     * Init the MyStyle front end.
     * @todo: Add unit testing for the frontend stylesheet inclusion
     */
    public static function init() {
        //Add the MyStyle frontend stylesheet to the WP frontend head
        wp_register_style( 'myStyleFrontendStylesheet', MYSTYLE_ASSETS_URL . 'css/frontend.css' );
        wp_enqueue_style( 'myStyleFrontendStylesheet' );
        
        //Add the swfobject.js file to the WP head
        wp_register_script( 'swfobject', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js' );
        wp_enqueue_script( 'swfobject' );
    }
    
    /**
     * Filter the body class output.  Adds a "mystyle-customize" class if the
     * page is the Customize page.
     * @param array $classes An array of classes that are going to be outputed
     * to the body tag.
     * @return array Returns the filtered classes array.
     * @todo Add unit testing
     */
    function filter_body_class( $classes ) {
        global $post;
        
        try {
            if( $post != null ) {
                if( 
                    ( $post->ID == MyStyle_Customize_Page::get_id() ) &&
                    ( isset( $_GET['product_id'] ) )
                  )
                {
                    $classes[] = 'mystyle-customize';
                }
            }
        } catch( MyStyle_Exception $e ) {
            //this exception may be thrown if the Customize Page is missing.
            //For this function, that is okay, just continue.
        }
        
	return $classes;
    }
    
    /**
     * Filter the post title. Hide the title if on the Customize page and the
     * customizer_page_title_hide setting is set to true. 
     * @param string $title The title of the post.
     * @param type $id The id of the post.
     * @return string Returns the filtered title.
     * @todo Add unit testing
     */
    function filter_title( $title, $id = null ) {
        
        try {
            if( 
                ( ! empty( $id ) ) &&
                ( $id == MyStyle_Customize_Page::get_id() ) &&
                ( MyStyle_Options::get_customizer_page_title_hide() )
              )
            {
                $title = '';
            }
        } catch( MyStyle_Exception $e ) {
            //this exception may be thrown if the Customize Page is missing.
            //For this function, that is okay, just continue.
        }

        return $title;
    }
    
    /**
     * Filter the "Add to Cart" button text.
     * @param string $text The current cart button text.
     */
    function filter_cart_button_text( $text ) {
        global $product;
        
        if( $product != null ) {
            
            if( MyStyle::product_is_customizable( $product->id ) ) {
                $text = "Customize";
            }
        }
        
        return $text;
    }
    
    /**
     * Filter to add our add_to_cart handler for customizable products.
     * @param string $handler The current add_to_cart handler.
     * @param type $product The current product.
     * @return string Returns the name of the handler to use for the add_to_cart
     * action.
     */
    function filter_add_to_cart_handler( $handler, $product ) {

        if($product != null) {
            $product_id = $product->id;
        } else {
            $product_id = absint( $_REQUEST['add-to-cart'] );
        }
        
        if( MyStyle::product_is_customizable( $product_id ) ) {
            $handler = 'mystyle_customizer';
            if(WC_VERSION < 2.3) {
                //old versions of woo commerce don't support custom add_to_cart handlers so just go there now.
                self::mystyle_add_to_cart_handler(false);
            }
        }
    
        return $handler;
    }
    
    /**
     * Modify the add to cart link for product listings
     * @param type $link The "Add to Cart" link (html)
     * @param type $product The current product.
     * @return type Returns the html to be outputted.
     */
    public static function loop_add_to_cart_link( $link, $product ) {
        //var_dump($product);
        
        if( (MyStyle::product_is_customizable( $product->id )) && ( $product->product_type != 'variable') ) {
            $customize_page_id = MyStyle_Customize_Page::get_id();
            
            //build the url to the customizer including the poduct_id
            $customizer_url = add_query_arg( 'product_id', $product->id, get_permalink( $customize_page_id ) );
            
            //Add the passthru data to the url
            $passthru = array();
            $passthru['post'] = array();
            $passthru['post']['quantity'] = 1;
            $passthru['post']['add-to-cart'] = $product->id;
            $passthru_encoded = base64_encode( json_encode( $passthru ) );
            $customizer_url = add_query_arg( 'h', $passthru_encoded, $customizer_url );
            
            //Build the link (a tag) to the customizer
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
     * The MyStyle add_to_cart handler.  Handles the add_to_cart action for
     * Customizable products.
     * @param string $url The current url.
     * @todo Add unit testing
     */
    function mystyle_add_to_cart_handler( $url ) {
        $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
        
        //set up an array of data to pass to/through the customizer.
        $passthru = array(
            'post' => $_REQUEST,
        );

        //add all available product attributes (if there are any) to the pass through data
        $product = new WC_Product_Variable( $product_id );
        $attributes = $product->get_variation_attributes();
        if( !empty( $attributes ) ) {
            $passthru['attributes'] = $attributes;
        }

        $customize_page_id = MyStyle_Customize_Page::get_id();
        
        $args = array(
                    'product_id' => $product_id,
                    'h' => base64_encode(json_encode($passthru)),
                );
        
        $customizer_url = add_query_arg( $args, get_permalink( $customize_page_id ) );
        wp_safe_redirect( $customizer_url );
        exit;
    }

}


