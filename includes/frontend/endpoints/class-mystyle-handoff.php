<?php
/**
 * Class to receive and process the handoff from the customizer.
 *
 * @package MyStyle
 * @since 0.5
 */

/**
 * MyStyle_Handoff class.
 */
class MyStyle_Handoff {

	/**
	 * The URL slug for the page/endpoint.
	 *
	 * @var string
	 */
	const SLUG = 'mystyle-handoff';

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Handoff
	 */
	private static $instance;

	/**
	 * Injected reference to the MyStyle_API_Interface.
	 *
	 * @var MyStyle_API_Interface
	 */
	private $mystyle_api;

	/**
	 * The current MyStyle_Design.
	 *
	 * @var MyStyle_Design
	 */
	private $design;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 *
	 * Note: Call set_mystyle_api in addition to this constructor.
	 */
	public function __construct() {
		// Hooks.
		add_action( 'wp_loaded', array( &$this, 'override' ) );
	}

	/**
	 * Static function that gets the url for the handoff endpoint.
	 *
	 * @return string Returns the url of the handoff endpoint
	 */
	public static function get_url() {
		$lang = MyStyle_Wpml::get_instance()->get_current_translation_language();

		if ( null !== $lang ) {
			$url = site_url( $lang . '/?' . self::SLUG );
		} else {
			//$url = site_url( self::SLUG );
			$url = site_url( '?' . self::SLUG );
		}

		return $url;
	}

	/**
	 * Scan the url and catch any requests that match the handoff slug.
	 *
	 * Needs to be public because it is registered as a WP action.
	 */
	public function override() {
		
        // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput
		$url = $_SERVER['REQUEST_URI'];

		if ( strpos( $url, self::SLUG ) !== false ) {
			if ( isset( $GLOBALS['skip_ob_start'] ) ) { // Used by our PHPUnit tests.
				return true;
			} else {
				$this->handle();
			}
		} else {
			if ( isset( $GLOBALS['skip_ob_start'] ) ) { // Used by our PHPUnit tests.
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
	 * @global \WooCommerce $woocommerce
	 */
	public function handle() {
		global $woocommerce;
        
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
            
			// --- Add the product to the cart along with the mystyle variables. ---
			// Create a Design from the post.
			$this->design = MyStyle_Design::create_from_post( $_POST ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

			// Get and persist the Design.
			$session_handler = MyStyle_SessionHandler::get_instance();
			$session         = $session_handler->get();
			$session_handler->persist( $session );
            
			// Add the session id to the design.
			$this->design->set_session_id( $session->get_session_id() );

			// Add data from api call.
			$this->design = $this->mystyle_api->add_api_data_to_design( $this->design );
            
            //get passthru data for user id
            $passthru = json_decode( base64_decode( $_POST['h'] ), true );

			// Get the mystyle user from the API.
			// Note: the user_id isn't returned by the API if the user is already
			// logged in locally (in which case, no email is captured or passed
			// to the API).
			$mystyle_user = null;
			if ( null !== $this->design->get_designer_id() ) {
				$mystyle_user = $this->mystyle_api->get_user(
					$this->design->get_designer_id()
				);
                
                $this->design->set_email( $mystyle_user->get_email() ) ;
                
			}

			// If the user is logged in to WordPress, store their user id with their design.
			$wp_user = wp_get_current_user();
            $wp_user_id = $wp_user->ID ;
             
			if ( 0 !== $wp_user_id ) {
				$user = get_user_by( 'ID', $wp_user_id ) ;
				$this->design->set_user_id( $wp_user_id );
				$this->design->set_email( $user->user_email );
			} elseif ( null !== $mystyle_user->get_email() ) {
				// If the user isn't logged in, see if their email matches an existing user and store that id with the design.
				$user = get_user_by( 'email', $mystyle_user->get_email() );
				if ( false !== $user ) {
					$this->design->set_user_id( $user->ID ) ;
                    $this->design->set_email( $user->user_email ) ;
				}
			}
            elseif ( isset( $passthru['user']['token'] ) ) {
                $token_user_id = MyStyle_Util::encrypt_decrypt( 'decrypt', $passthru['user']['token'] ) ;
                $user = get_user_by( 'ID', $token_user_id ) ;
                if ( false !== $user ) {
					$this->design->set_user_id( $user->ID ) ;
                    $this->design->set_email( $user->user_email ) ;
				}
            }
            
			// Persist the design to the database.
			$this->design = MyStyle_DesignManager::persist( $this->design );

			// Get the passthru data.
			$passthru = json_decode( base64_decode( $_POST['h'] ), true ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput, WordPress.CSRF.NonceVerification.NoNonceVerification

			// ------------------- Send email to user. ---------------
			if ( has_action( 'mystyle_send_design_complete_email' ) ) {
				// Custom email.
				do_action( 'mystyle_send_design_complete_email', $this->design, $passthru );
			} else {
				// Basic email.
				$site_title       = get_bloginfo( 'name' );
				$site_url         = network_site_url( '/' );
				$site_description = get_bloginfo( 'description' );
				$message          = "Design Created!\n\n" .
						'This email is to confirm that your design was successfully ' .
						"saved. Thanks for using our site!\n\n" .
						'Your design id is ' . $this->design->get_design_id() . ".\n\n" .
						'You can access your design at any time from the following ' .
						"url:\n\n" .
						MyStyle_Design_Profile_Page::get_design_url( $this->design ) . "\n\n" .
						"View all of your designs on the My Designs page here. \n\n" .
						$site_url . "my-account/my-designs/\n\n" .
						"Reload and edit your design at any time here:\n\n" .
						MyStyle_Customize_Page::get_design_url( $this->design, null, $passthru ) . "\n";
				$admin_email      = get_option( 'admin_email' );
				$blogname         = get_option( 'blogname' );
				$headers          = '';
				if ( $admin_email && $blogname ) {
					$headers = array( 'From: ' . $blogname . ' <' . $admin_email . '>' );
				}

				wp_mail(
					$this->design->get_email(),
					'Design Created!',
					$message,
					$headers
				);
			}
			// -------------------------------------------------------
			$passthru_post = $passthru['post'];
			$quantity      = $passthru_post['quantity'];
			$product_id    = $passthru_post['add-to-cart'];
			$cart_item_key = ( array_key_exists( 'cart_item_key', $passthru ) ) ? $passthru['cart_item_key'] : null;

			// Set the $_POST to the post data that passed through.
			$_POST = $passthru_post;

			$variation_id = ( isset( $passthru_post['variation_id'] ) ) ? $passthru_post['variation_id'] : '';

			// Get the variations ( they should all be in the passthru post and start with "attribute_" ).
			$variation = array();
			foreach ( $passthru_post as $key => $value ) {
				if ( substr( $key, 0, 10 ) === 'attribute_' ) {
					$variation[ $key ] = $value;
				}
			}

			// The customizer may change the attributes but doesn't ever change
			// the variation_id.  Here we update the variation_id to match the
			// passed attributes.
			if ( ! empty( $variation_id ) ) {
				$variation_id = MyStyle_WC()->get_matching_variation( $product_id, $variation );
			}

			// Get the woocommerce cart.
			$cart = $woocommerce->cart;

			// Init the cart contents ( pull from memory, etc ).
			$cart->get_cart();

			if ( null !== $cart_item_key ) { // Existing cart item.
				// Update the mystyle data.
				$cart->cart_contents[ $cart_item_key ]['mystyle_data'] = $this->design->get_meta();

				// Commit our change to the session.
				$cart->set_session();
			} else { // New cart item.
				// Add the mystyle meta data to the cart item.
				$cart_item_data                 = array();
				$cart_item_data['mystyle_data'] = $this->design->get_meta();

				// Add the product and meta data to the cart.
				$cart_item_key = $cart->add_to_cart(
					$this->design->get_product_id(), // WooCommerce product id.
					$quantity, // quantity.
					$variation_id, // variation id.
					$variation, // variation attribute values.
					$cart_item_data // extra cart item data we want to pass into the item.
				);

				// ---------------------- Fix for WC 2.2-----------------------
				// Set a session variable with our data that can later be retrieved if necessary.
				if ( isset( $woocommerce->session ) ) {
					$woocommerce->session->set( 'mystyle_' . $cart_item_key, $cart_item_data );
				}
				// ------------------------------------------------------------
			}
		}

		if ( ! isset( $GLOBALS['skip_ob_start'] ) ) { // Used by our PHPUnit tests to skip the ob_start line.
			ob_start( array( &$this, 'get_output' ) );
		}
	}

	/**
	 * Called by the handle function above. Returns the output for the request.
	 * Only supports POST requests, GET requests are given an Access DENIED
	 * message.
	 *
	 * Public because it is called by the ob_start callback (see the end of the
	 * 'handle' function above).
	 *
	 * @return string Returns the html to output to the browser.
	 * @global \WooCommerce $woocommerce
	 */
	public function get_output() {
		global $woocommerce;

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
			// -- Add the product to the cart along with the mystyle variables. --
			// Get the woocommerce cart.
			$cart = $woocommerce->cart;

			if ( MyStyle_Options::is_demo_mode() ) {
				// Send to Demo Mode Message.
				$html = $this->build_view( 'MyStyle Demo', $cart->get_cart_url(), false );
			} else {
				$link = MyStyle_Design_Complete::get_redirect_url( $this->design );
				if ( ! empty( $link ) ) {
					// Redirect to the redirect url.
					$html = '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . $link . '"></head><body></body></html>';
				} else {
					// Redirect the user to the cart.
					$html = $this->build_view( 'Adding Product to Cart...', $cart->get_cart_url(), true );
				}
			}
		} else { // GET Request.
			$html = '<!DOCTYPE html><html><head></head><body><h1>MyStyle</h1><h2>Access Denied</h2></body></html>';
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
	public function build_view( $title, $link, $enable_redirect ) {

		$redirect = ( $enable_redirect ) ? '<META http-equiv="refresh" content="0;URL=' . $link . '">' : '';

		$format = '
            <!DOCTYPE html><html>
                <head>
                    <style>
                        h1, h2, p {color: #515151; font-family: "Noto Sans",sans-serif;}
                        h1 {font-size: 3em}
                        body {background-color: #E6E6E6;}
                        div.container {width: 600px; background: white; box-shadow: 0 2px 6px rgba( 100, 100, 100, 0.3 ); margin: 30px auto 0px auto;}
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
		$html   = sprintf( $format, $redirect, $title, $title, $link );

		return $html;
	}

	/**
	 * Sets the mystyle_api.
	 *
	 * @param MyStyle_Api_Interface $mystyle_api The mystyle_api that you want
	 * the class to use.
	 */
	public function set_mystyle_api( MyStyle_Api_Interface $mystyle_api ) {
		$this->mystyle_api = $mystyle_api;
	}

	/**
	 * Sets the design. This is mostly just here for testing. Normally the
	 * design would be set by the handle() method.
	 *
	 * @param MyStyle_Design $design The design that you want the class to
	 * use.
	 */
	public function set_design( MyStyle_Design $design ) {
		$this->design = $design;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Handoff Returns the singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
