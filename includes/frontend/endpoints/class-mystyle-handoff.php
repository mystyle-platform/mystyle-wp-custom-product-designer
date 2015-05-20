<?php

/**
 * Class for creating an endpoint the mystyle handoff.
 * @todo Create unit tests
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_Handoff {
    
    private static $SLUG = "mystyle-handoff";
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_action( 'wp_loaded', array( &$this, 'override' ) );
    }
    
    /**
     * Scan the url and catch any requests that match the handoff slug.
     * 
     * Needs to be public and static because it is registered as an a WP action.
     */
    public static function override() {
        //$url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $url = $_SERVER['REQUEST_URI'];
        //echo $url;
        if( strpos( $url, self::$SLUG ) !== FALSE ) {
            ob_start( array( 'MyStyle_Handoff', 'handle' ) );
        }
    }
    
    /**
     * Called by the override function above. Handles requests for the handoff
     * page. Only supports POST requests, GET requests are given an Access
     * DENIED message.
     * 
     * Needs to be public and static because it is registered as an a WP action.
     * 
     * @return string Returns the html to output to the browser.
     */
    public static function handle() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            
            /*
            //------ Output the POST variables to the screen (for debugging) --------//
            $html = "<!DOCTYPE html><html><head></head><body>";
            foreach($_POST as $key => $value) {
                $html .= "<strong>" . $key . ":</strong>" . $value. "<br/>";
            }
            $html .= "<hr/>";
            $html .= "<string>design id:</strong>" . $cart_item_data['mystyle_data']['design_id'];
            $html .= "</body></head>";
            */
            
            //- Add the product to the cart along with the mystyle variables -//
            global $woocommerce;
            
            //Create a Design from the post
            $design = MyStyle_Design::create_from_post( $_POST );
            
            //Add data from api call
            $design = MyStyle_Api::add_api_data_to_design( $design );
            
            //Get the woocommerce cart
            $cart = $woocommerce->cart;
            
            //Add the mystyle meta data to the cart item
            $cart_item_data = array();
            $cart_item_data['mystyle_data'] = $design->get_meta();
            
            //Add the product and meta data to the cart
            $cart_item_key = $cart->add_to_cart(
                                        $design->get_product_id(),
                                        1,
                                        '',
                                        array(),
                                        $cart_item_data
                                );
            
            //Redirect the user to the cart
            $html = '<!DOCTYPE html><html>' .
                    '<head>' .
                      '<style>h1 {color: rgba(51, 51, 51, 0.7); font-family: "Noto Sans",sans-serif;}</style>' .
                      '<title>Adding Product to Cart...</title>' . 
                      '<META http-equiv="refresh" content="1;URL=' . $cart->get_cart_url() . '">' .
                    '</head>' . 
                    '<body><h1>Adding product to cart...</h1></body>'.
                    '</html>';
        }
        else { // GET Request
            $html = '<!DOCTYPE html><html><head></head><body><h1>MyStyle</h1><h2>Access Denied</h2></body></head>';
        }
        
        return $html;
    }
    
}