<?php

/**
 * Simple entity class.
 * @package MyStyle
 * @since 0.5
 */
class MyStyle_Handoff {
    
    private static $SLUG = 'mystyle-handoff';
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_action( 'wp_loaded', array( &$this, 'override' ) );
    }
    
    /**
     * Gets the url for the handoff endpoint.
     * @return string Returns the url of the handoff endpoint
     */
    public static function get_url() {
        return site_url( '?' . self::$SLUG );
    }
    
    /**
     * Scan the url and catch any requests that match the handoff slug.
     * 
     * Needs to be public and static because it is registered as an a WP action.
     */
    public static function override() {
        
        $url = $_SERVER['REQUEST_URI'];
        //echo $url;
        if( strpos( $url, self::$SLUG ) !== FALSE ) {
            if(isset($GLOBALS['skip_ob_start'])) { //Used by our PHPUnit tests
                return true;
            } else {
                ob_start( array( 'MyStyle_Handoff', 'handle' ) );
            }
        } else {
            if(isset($GLOBALS['skip_ob_start'])) { //Used by our PHPUnit tests
                return false;
            }
        }
        
    }
    
    /**
     * Called by the override function above. Handles requests for the handoff
     * page. Only supports POST requests, GET requests are given an Access
     * DENIED message.
     * 
     * Needs to be public and static because it is registered as a WP action.
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
            
            //Persist the design to the database
            $design = MyStyle_DesignManager::persist( $design );
            
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
            
            if(MyStyle_Options::is_demo_mode()) {
                //Send to Demo Mode Message
                $html = self::buildView('MyStyle Demo', $cart->get_cart_url(), false);
            } else {
                //Redirect the user to the cart
                $html = self::buildView('Adding Product to Cart...', $cart->get_cart_url(), true);
            }
            
        }
        else { // GET Request
            $html = '<!DOCTYPE html><html><head></head><body><h1>MyStyle</h1><h2>Access Denied</h2></body></head>';
        }
        
        return $html;
    }
    
    /**
     * Builds a view to display to the user after the handoff.
     * @param string $title The title to be used in the view.
     * @param string $link The link to be used in the view.
     * @param string $enable_redirect Whether or not to redirect.
     * @return string Returns a string of html.
     */
    public static function buildView( $title, $link, $enable_redirect ) {
        
        $redirect = ( $enable_redirect ) 
                        ? '<META http-equiv="refresh" content="0;URL=' . $link . '">' 
                        : '';
        
        $format = '
            <!DOCTYPE html><html>
                <head>
                    <style>
                        h1, h2, p {color: #515151; font-family: "Noto Sans",sans-serif;}
                        h1 {font-size: 3em}
                        body {background-color: #E6E6E6;} 
                        div.container {width: 600px; background: white; box-shadow: 0 2px 6px rgba(100, 100, 100, 0.3); margin: 30px auto 0px auto;} 
                        section {padding: 10px 30px 30px 30px; text-align: center;}
                    </style>
                    %s
                    <title>%s</title>
                </head>
                <body>
                    <div class="container">
                        <section>
                            <h1>%s</h1>
                            <h2>Product added to cart</h2>
                            <p>The customized product has been added to your cart.</p>
                            <p><a href="%s">Go to cart</a></p>
                        </section>
                    </div>
                </body>
            </html>';
        $html = sprintf($format, $redirect, $title, $title, $link);
        
        return $html;
    }
    
}