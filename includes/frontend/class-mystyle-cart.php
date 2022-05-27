<?php
/**
 * The MyStyle Cart class has hooks for working with the shopping cart.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * MyStyle_Cart class.
 */
class MyStyle_Cart {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Cart
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( &$this, 'filter_cart_button_text' ), 10, 1 );
		add_filter( 'woocommerce_after_add_to_cart_quantity', array( &$this, 'after_add_to_cart_quantity'), 10 ) ;
		add_filter( 'woocommerce_add_to_cart_handler', array( &$this, 'filter_add_to_cart_handler' ), 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_cart_item_from_session' ), 10, 3 );

		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'woocommerce_loop_add_to_cart_link', array( &$this, 'loop_add_to_cart_link' ), 10, 2 );
		add_action( 'woocommerce_add_to_cart_handler_mystyle_customizer', array( &$this, 'mystyle_add_to_cart_handler_customize' ), 10, 1 );
		add_action( 'woocommerce_add_to_cart_handler_mystyle_add_to_cart', array( &$this, 'mystyle_add_to_cart_handler' ), 10, 1 );

		add_filter( 'woocommerce_order_again_cart_item_data', array( &$this, 'add_order_again_cart_item_data' ), 10, 3 ) ;
	}

	/**
	 * Init hooks.
	 */
	public function init() {
		if(isset($_REQUEST['customize'])) {
			$_REQUEST['add-to-cart'] = $_REQUEST['customize'] ;
			$this->mystyle_add_to_cart_handler_customize(null) ;
		}
		add_filter( 'woocommerce_cart_item_thumbnail', array( &$this, 'modify_cart_item_thumbnail' ), 10, 3 );
		add_filter( 'woocommerce_in_cart_product_thumbnail', array( &$this, 'modify_cart_item_thumbnail' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_name', array( &$this, 'modify_cart_item_name' ), 10, 3 );
	}

	/**
	 * Filter the "Add to Cart" button text.
	 *
	 * @param string $text The current cart button text.
	 */
	public function filter_cart_button_text( $text ) {
		global $product;

		if ( null !== $product ) {
			$mystyle_product = new \MyStyle_Product( $product );

			if ( $mystyle_product->is_customizable() && !$mystyle_product->is_add_to_cart() ) {
				$text = 'Customize';
			}
			elseif( $mystyle_product->is_customizable() && $mystyle_product->is_add_to_cart() ) {
				$text = 'Purchase as-is' ;
			}
		}

		return $text;
	}

	/**
	 * Add to cart button on products that allow purchase of "As is" product
	 * 
	 * Prints a second Add to cart button
	 */
	public function after_add_to_cart_quantity() {
		global $product;

		if ( null !== $product ) {
			$mystyle_product = new \MyStyle_Product( $product );

			if ( $mystyle_product->is_customizable() && $mystyle_product->is_add_to_cart() ) {
				
				?><div class="purchase_as_is"><button type="submit" name="customize" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt">Customize</button></div><br /><?php
			}
		}
	}

	/**
	 * Filter to add our add_to_cart handler for customizable products.
	 *
	 * @param string $handler The current add_to_cart handler.
	 * @param type   $product The current product.
	 * @return string Returns the name of the handler to use for the add_to_cart
	 * action.
	 */
	public function filter_add_to_cart_handler( $handler, $product ) {
		// If this is a request from the WooCommerce TM Extra Product Options
		// edit cart function, just return the handler unaltered.
		if ( MyStyle_Tm_Extra_Product_Options::is_tm_extra_product_options_edit_request( $_REQUEST ) ) { // phpcs:ignore
			return $handler;
		}

		if ( null !== $product ) {
			$mystyle_product = new \MyStyle_Product( $product );
			$product_id      = $mystyle_product->get_id();
		} else {
			$product_id      = ( ! empty( $_REQUEST['add-to-cart'] ) ) ? intval( $_REQUEST['add-to-cart'] ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$mystyle_product = new Mystyle_Product( new WC_Product( $product_id ) );
		}

		if ( isset( $_REQUEST['design_id'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$handler = 'mystyle_add_to_cart';
			if ( MyStyle()->get_WC()->version_compare( '2.3', '<' ) ) {
				// Old versions of woo commerce don't support custom add_to_cart handlers so just go there now.
				self::mystyle_add_to_cart_handler( false );
			}
		} else {

			if ( $mystyle_product->is_customizable() && ! $mystyle_product->is_add_to_cart() ) {
				
				$handler = 'mystyle_customizer';
				
				if ( MyStyle()->get_WC()->version_compare( '2.3', '<' ) ) {
					// Old versions of woo commerce don't support custom add_to_cart handlers so just go there now.
					self::mystyle_add_to_cart_handler_customize( false );
				}
				
			}
		}

		return $handler;
	}

	/**
	 * Modify the add to cart link for product listings.
	 *
	 * @param type $link The "Add to Cart" link (html).
	 * @param type $product The current product.
	 * @return type Returns the html to be outputted.
	 */
	public function loop_add_to_cart_link( $link, $product ) {
		$mystyle_product = new \MyStyle_Product( $product );
		$product_id      = $mystyle_product->get_id();
		$product_type    = $mystyle_product->get_type();

		if ( ( $mystyle_product->is_customizable() ) && ( 'variable' !== $product_type ) ) {
			$customize_page_id = MyStyle_Customize_Page::get_id();

			// Build the url to the customizer including the product_id.
			$customizer_url = add_query_arg( 'product_id', $product_id, get_permalink( $customize_page_id ) );

			// Add the passthru data to the url.
			$passthru                        = MyStyle_Passthru_Codec::get_instance()->build_passthru( $_REQUEST, $mystyle_product ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$passthru['post']                = array();
			$passthru['post']['quantity']    = 1;
			$passthru['post']['add-to-cart'] = $product_id;
			$passthru_encoded                = base64_encode( wp_json_encode( $passthru ) );
			$customizer_url                  = add_query_arg( 'h', $passthru_encoded, $customizer_url );

			// Build the link ( a tag ) to the customizer.
			$customize_link = sprintf(
				'<a ' .
				'href="%s" ' .
				'rel="nofollow" ' .
				'class="button %s product_type_%s" ' .
				'>%s</a>',
				esc_url( $customizer_url ),
				$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
				esc_attr( $product_type ),
				esc_html( 'Customize' )
			);

			$ret = $customize_link;
		} else {
			$ret = $link;
		}

		return $ret;
	}

	/**
	 * Handles the add_to_cart action for customizing customizable products.
	 *
	 * The handler to use is determined by the filter_add_to_cart_handler
	 * function above.
	 *
	 * @param string $url The current url.
	 */
	public function mystyle_add_to_cart_handler_customize( $url ) {
		if( isset($_REQUEST['customize']) ) {
			$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['customize'] ) ); //phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
		}
		else {
			$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) ); //phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
		}
		
		/* @var $mystyle_product \MyStyle_Product The product. */
		$mystyle_product = MyStyle_Product::get_by_id( $product_id );

		$passthru = MyStyle_Passthru_Codec::get_instance()->build_passthru( $_REQUEST, $mystyle_product ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.CSRF.NonceVerification.NoNonceVerification

		$customize_page_id = MyStyle_Customize_Page::get_id();

		$args = array(
			'product_id' => $product_id,
			'h'          => base64_encode( wp_json_encode( $passthru ) ),
		);

		$customizer_url = add_query_arg( $args, get_permalink( $customize_page_id ) );

		// Redirect and Exit ( unless called by phpunit ).
		if ( ! defined( 'DOING_PHPUNIT' ) ) {
			wp_safe_redirect( $customizer_url );
			exit;
		} else {
			return true;
		}
	}

	/**
	 * Handles the add_to_cart action for when an existing design is added to
	 * the cart.
	 *
	 * The handler to use is determined by the filter_add_to_cart_handler
	 * function above.
	 *
	 * @param string $url The current url.
	 * @global \WooCommerce $woocommerce
	 */
	public function mystyle_add_to_cart_handler( $url ) {
		global $woocommerce;

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated, WordPress.CSRF.NonceVerification.NoNonceVerification
		$product_id   = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
		$design_id    = absint( $_REQUEST['design_id'] );
		$quantity     = absint( $_REQUEST['quantity'] );
		$variation_id = ( isset( $_REQUEST['variation_id'] ) ) ? intval( $_REQUEST['variation_id'] ) : null;
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated

		// Get the woocommerce cart.
		$cart = $woocommerce->cart;

		// Get the variations (they should all be in the passthru post and start with "attribute_").
		$variation = array();
		foreach ( $_REQUEST as $key => $value ) { // phpcs:ignore
			if ( 'attribute_' === substr( $key, 0, 10 ) ) {
				$variation[ $key ] = $value;
			}
		}

		// Add the mystyle meta data to the cart item.
		$cart_item_data                 = array();
		$cart_item_data['mystyle_data'] = array( 'design_id' => $design_id );

		// Add the product and meta data to the cart.
		$cart_item_key = $cart->add_to_cart(
			$product_id, // WooCommerce product id.
			$quantity, // quantity.
			$variation_id, // variation id.
			$variation, // variation attribute values.
			$cart_item_data // extra cart item data we want to pass into the item.
		);

		if ( $cart_item_key ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );

			// Redirect and exit (unless called by phpunit).
			if ( ! defined( 'DOING_PHPUNIT' ) ) {
				wp_redirect( wc_get_page_permalink( 'cart' ) );
				exit();
			} else {
				return true;
			}
		}
	}

	/**
	 * Filter the woocommerce_get_cart_item_from_session and add our session
	 * data.
	 *
	 * @param array  $session_data The current session_data.
	 * @param array  $values The values that are to be stored in the session.
	 * @param string $key The key of the cart item.
	 * @return string Returns the updated cart image tag.
	 */
	public function get_cart_item_from_session( $session_data, $values, $key ) {

		// Fix for WC 2.2 (if our data is missing from the cart item, get it from the session variable.
		if ( ! isset( $session_data['mystyle_data'] ) ) {
			$cart_item_data               = WC()->session->get( 'mystyle_' . $key );
			$session_data['mystyle_data'] = $cart_item_data['mystyle_data'];
		}

		return $session_data;
	}

	/**
	 * Override the cart item thumbnail image.
	 *
	 * @param string $product_img_tag The current image tag ( ex. <img.../> ).
	 * @param string $cart_item The cart item that we are currently on.
	 * @param string $cart_item_key The current cart_item_key.
	 * @return string Returns the updated cart image tag.
	 */
	public function modify_cart_item_thumbnail( $product_img_tag, $cart_item, $cart_item_key ) {

		$out       = $product_img_tag;
		$design_id = null;
		
		// Try to get the design id, first from the cart_item and then from the session.
		if ( isset( $cart_item['mystyle_data'] ) ) {
			$design_id = $cart_item['mystyle_data']['design_id'];
		} else {
			$session_data = self::get_cart_item_from_session( array(), null, $cart_item_key );
			if ( isset( $session_data['mystyle_data'] ) ) {
				$design_id = $session_data['mystyle_data']['design_id'];
			}
		}

		if ( null !== $design_id ) {

			/* @var $user \WP_User phpcs:ignore */
			$user = wp_get_current_user();

			/* @var $session \MyStyle_Session phpcs:ignore */
			$session = MyStyle()->get_session();

			/* @var $design \MyStyle_Design phpcs:ignore */
			$design = MyStyle_DesignManager::get( $design_id, $user, $session, true ); // skip the security check because the design is already in the cart.

			// Only proceed if we have a design to work with.
			if ( null !== $design ) {

				// Overwrite the src attribute.
				$new_src         = 'src="' . $design->get_thumb_url() . '"';
				$product_img_tag = preg_replace( '/src\=".*?"/', $new_src, $product_img_tag );

				// Remove the srcset attribute.
				$product_img_tag = preg_replace( '/srcset\=".*?"/', '', $product_img_tag );

				// Prep the link to the design profile page for the design.
				$design_profile_url = MyStyle_Design_Profile_Page::get_design_url( $design, $cart_item_key );

				// Prep the link to reload the design in the customizer.
				$customizer_url = MyStyle_Customize_Page::get_design_url( $design, $cart_item_key );

				// Call the view/template layer.
				$out = mystyle_get_template_html(
					'cart/cart-item-thumbnail.php',
					array(
						'product_img_tag'    => $product_img_tag,
						'design'             => $design,
						'design_profile_url' => $design_profile_url,
						'customizer_url'     => $customizer_url,
					)
				);
			}
		}

		return $out;
	}

	/**
	 * Override the cart item name.
	 *
	 * @param string $name The current cart item name ( ex. 'My Product' ).
	 * @param string $cart_item The cart item that we are currently on.
	 * @param string $cart_item_key The current cart_item_key.
	 * @return string Returns the updated cart item name.
	 */
	public function modify_cart_item_name( $name, $cart_item, $cart_item_key ) {

		$new_name  = $name;
		$design_id = null;

		// Try to get the design id, first from the cart_item and then from the session.
		if ( isset( $cart_item['mystyle_data'] ) ) {
			$design_id = $cart_item['mystyle_data']['design_id'];
		} else {
			$session_data = self::get_cart_item_from_session( array(), null, $cart_item_key );
			if ( isset( $session_data['mystyle_data'] ) ) {
				$design_id = $session_data['mystyle_data']['design_id'];
			}
		}

		if ( null !== $design_id ) {
			/* @var $user \WP_User phpcs:ignore */
			$user = wp_get_current_user();

			/* @var $session \MyStyle_Session phpcs:ignore */
			$session = MyStyle()->get_session();

			/* @var $design \MyStyle_Design phpcs:ignore */
			$design = MyStyle_DesignManager::get( $design_id, $user, $session, true ); // skip the security check because the design is already in the cart.

			// Ensure that the design is found. Note: it might be missing if
			// they return to their cart after the admin deletes it for some
			// reason.
			if ( ! is_null( $design ) ) {
				$url = MyStyle_Design_Profile_Page::get_design_url( $design, $cart_item_key );

				$new_name = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $name );
			} else {
				$new_name = 'Design Not Found. Please remove from cart and re-add';
			}
		}

		return $new_name;
	}

	/**
	 * Add MyStyle data cart during order again function
	 * 
	 * @param array $item_data
	 * @param object $item
	 * @param object $order
	 * 
	 * @return array $item_data
	 */
	public function add_order_again_cart_item_data( $item_data, $item, $order ) {
		
		$meta = $item->get_meta( 'mystyle_data' ) ;

		if( ! $meta ) {
			return $item_data ;
		}
		
		// Add the mystyle meta data to the cart item.
		$item_data['mystyle_data'] = $meta ;

		return $item_data ;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Cart Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Cart Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
