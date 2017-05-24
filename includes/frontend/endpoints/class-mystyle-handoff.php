<?php

/**
 * Class to receive and process the handoff from the customizer.
 * @package MyStyle
 * @since 0.5
 */
class MyStyle_Handoff {
    
    private static $SLUG = 'mystyle-handoff';
    
    /**
     * @var MyStyle_Api_Interface 
     */
    private $mystyle_api;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct( MyStyle_API_Interface $mystyle_api ) {
        //construct object
        $this->mystyle_api = $mystyle_api;
        
        //hooks
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
     * Needs to be public because it is registered as a WP action.
     */
    public function override() {
        
        $url = $_SERVER['REQUEST_URI'];
        //echo $url;
        if( strpos( $url, self::$SLUG ) !== FALSE ) {
            if( isset( $GLOBALS['skip_ob_start'] ) ) { //Used by our PHPUnit tests
                return true;
            } else {
                $this->handle();
            }
        } else {
            if( isset( $GLOBALS['skip_ob_start'] ) ) { //Used by our PHPUnit tests
                return false;
            }
        }
        
    }
    
    /**
     * Called by the override function above. Handles requests for the handoff
     * page. Only supports POST requests, GET requests are given an Access
     * DENIED message.
     * 
     * Public for now to make testing easier.
     *
     * @todo Make private. 
     * @todo Unit test the variation support
     * @todo Break this long function up.
     */
    public function handle() {
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
            /* @var $design \MyStyle_Design */
            $design = MyStyle_Design::create_from_post( $_POST );
            
            //Get and persist the Design
            $session = MyStyle_SessionHandler::get();
            MyStyle_SessionHandler::persist( $session );
            
            //Add the session id to the design
            $design->set_session_id( $session->get_session_id() );
            
            //Add data from api call
            $design = $this->mystyle_api->add_api_data_to_design( $design );
            
            //Get the mystyle user from the API
            /* @var $user \MyStyle_User */
            $mystyle_user = $this->mystyle_api->get_user( $design->get_designer_id() );
            
            //Add data from the user to the design
            $design->set_email( $mystyle_user->get_email() );
            
            //If the user is logged in to WordPress, store their user id with their design
            $wp_user_id = get_current_user_id();
            if( $wp_user_id !== 0 ) {
                $design->set_user_id( $wp_user_id );
            } else {
                //if the user isn't logged in, see if their email matches an existing user and store that id with the design
                $user = get_user_by( 'email', $mystyle_user->get_email() );
                if( $user !== false ) {
                    $design->set_user_id( $user->ID );
                }
            }
            
            // ------------------- Send email to user ---------------
            //$design_complete_email = new MyStyle_Email_Design_Complete( $design );
            //$design_complete_email->send();
            
            if ( has_action( 'mystyle_send_design_complete_email' ) ) {
                //custom email
                do_action( 'mystyle_send_design_complete_email', $design );
            } else {
                //basic email
                $site_title = get_bloginfo( 'name' );
                $site_url = network_site_url( '/' );
                $site_description = get_bloginfo( 'description' );
                $message = 
                        "Design Created!\n\n" .
                        "This email is to confirm that your design was successfully " .
                        "saved. Thanks for using our site!\n\n" .
                        "Your design id is " . $design->get_design_id() . ".\n\n" .
                        "You can access your design at any time from the following " .
                        "url:\n\n" . 
                        MyStyle_Design_Profile_Page::get_design_url( $design ) . "\n\n".
                        "Reload and edit your design at any time here:\n\n".
                        MyStyle_Customize_Page::get_design_url( $design ) . "\n".
                $admin_email = get_option( 'admin_email' );
                $blogname = get_option( 'blogname' );
                $headers = '';
                if ( $admin_email && $blogname ) {
                    $headers = array( 'From: ' . $blogname . ' <' . $admin_email . '>' );
                }

                wp_mail( 
                    $mystyle_user->get_email(), 
                    'Design Created!', 
                    $message,
                    $headers
                );
            }
            // -------------------------------------------------------
            
            //Persist the design to the database
            $design = MyStyle_DesignManager::persist( $design );
            
            //Get the passthru data
            $passthru = json_decode( base64_decode( $_POST['h'] ), true );
            $passthru_post = $passthru['post'];
            $quantity = $passthru_post['quantity'];
            $product_id = $passthru_post['add-to-cart'];
            $cart_item_key = ( array_key_exists( 'cart_item_key', $passthru ) ) ? $passthru['cart_item_key'] : null;
            
            //Set the $_POST to the post data that passed through.
            $_POST = $passthru_post;
            
            $variation_id = ( isset( $passthru_post['variation_id'] ) ) ? $passthru_post['variation_id'] : '';
            
            //get the variations (they should all be in the passthru post and start with "attribute_")
            $variation = array();
            foreach( $passthru_post as $key => $value ) {
                if( substr( $key, 0, 10 ) === "attribute_" ) {
                    $variation[$key] = $value;
                }
            }
            
            //The customizer may change the attributes but doesn't ever change
            //the variation_id.  Here we update the variation_id to match the
            //passed attributes.
            echo 'before:' . $variation_id;
            var_dump($variation);
            if( ! empty( $variation_id ) ) {
                $variation_id = MyStyle_WC()->get_matching_variation( $product_id, $variation );
            }
            echo 'after:' . $variation_id;
            
            //echo $quantity . ':' . $variation_id;
            //exit;
            
            //Get the woocommerce cart
            /* @var $cart \WC_Cart */
            $cart = $woocommerce->cart;
            //init the cart contents (pull from memory, etc)
            $cart->get_cart();
            
            if( $cart_item_key != null ) { //existing cart item
                //update the mystyle data
                $cart->cart_contents[$cart_item_key]['mystyle_data'] = $design->get_meta();
                
                //commit our change to the session
                $cart->set_session();
            } else { //new cart item
                //Add the mystyle meta data to the cart item
                $cart_item_data = array();
                $cart_item_data['mystyle_data'] = $design->get_meta();
                
                //Add the product and meta data to the cart
                $cart_item_key = $cart->add_to_cart(
                                            $design->get_product_id(), //WooCommerce product id
                                            $quantity, //quantity
                                            $variation_id, //variation id
                                            $variation, //variation attribute values
                                            $cart_item_data //extra cart item data we want to pass into the item
                                    );
                
                // ---------------------- Fix for WC 2.2----------------------- 
                // Set a session variable with our data that can later be retrieved if necessary
                if( isset( $woocommerce->session ) ) {
                    $woocommerce->session->set( 'mystyle_' . $cart_item_key, $cart_item_data );
                }
                // ------------------------------------------------------------
            }
        }
        
        if( ! isset( $GLOBALS['skip_ob_start'] ) ) { //Used by our PHPUnit tests to skip the ob_start line
            ob_start( array( &$this, 'get_output' ) );
        }
    }
    
    /**
     * Called by the handle function above. Returns the output for the request.
     * Only supports POST requests, GET requests are given an Access
     * DENIED message.
     * 
     * Public because it is called by the ob_start callback (see the end of the
     * 'handle' function above).
     * 
     * @return string Returns the html to output to the browser.
     */
    public function get_output() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            //- Add the product to the cart along with the mystyle variables -//
            global $woocommerce;
            
            
            //Get the woocommerce cart
            $cart = $woocommerce->cart;
            
            if(MyStyle_Options::is_demo_mode()) {
                //Send to Demo Mode Message
                $html = $this->buildView('MyStyle Demo', $cart->get_cart_url(), false);
            } else {
                //Redirect the user to the cart
                $html = $this->buildView('Adding Product to Cart...', $cart->get_cart_url(), true);
            }
            
        }
        else { // GET Request
            $html = '<!DOCTYPE html><html><head></head><body><h1>MyStyle</h1><h2>Access Denied</h2></body></head>';
        }
        
        return $html;
    }
    
    /**
     * Builds a view to display to the user after the handoff.
     * 
     * @param string $title The title to be used in the view.
     * @param string $link The link to be used in the view.
     * @param string $enable_redirect Whether or not to redirect.
     * @return string Returns a string of html.
     */
    public function buildView( $title, $link, $enable_redirect ) {
        
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