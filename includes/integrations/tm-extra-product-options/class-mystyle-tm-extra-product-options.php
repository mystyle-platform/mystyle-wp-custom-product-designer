<?php
/**
 *
 * Class for integrating with the TM Extra Product Options plugin.
 *
 * @package MyStyle
 * @since 3.13.1
 */

/**
 * MyStyle_Tm_Extra_Product_Options class.
 */
class MyStyle_Tm_Extra_Product_Options {

	/**
	 * Place to stash the mystyle_data for passing it between hooks.
	 *
	 * @var array|null
	 */
	private $mystyle_data = null;

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Tm_Extra_Product_Options
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_add_to_cart_validation', array( &$this, 'stash_mystyle_data' ), 99998, 6 );
		add_action( 'woocommerce_add_to_cart', array( &$this, 'copy_mystyle_data' ), 1, 6 );
	}

	/**
	 * Watch for TM Extra Product Options initiated add-to-cart actions and if
	 * one is detected, stash the mystyle_data in a class property (for use
	 * in later hooks).
	 *
	 * @param boolean $passed Whether or not the item passed validation.
	 * @param int     $product_id The id of the product being added.
	 * @param int     $qty The quantity of items being added.
	 * @param int     $variation_id ID of the variation being added to the cart.
	 * @param array   $variations Attribute values.
	 * @param array   $cart_item_data Extra cart item data that we want to pass
	 *  into the item.
	 * @return string|void
	 * @global \WooCommerce $woocommerce
	 */
	public function stash_mystyle_data(
		$passed,
		$product_id,
		$qty,
		$variation_id = '',
		$variations = array(),
		$cart_item_data = array()
	) {
		global $woocommerce;

		// Just return if this isn't the scenario that we are looking for.
		if (
				( ! self::is_tm_extra_product_options_edit_request( $_REQUEST ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
				|| ( ! function_exists( 'THEMECOMPLETE_EPO' ) )
				|| ( ! THEMECOMPLETE_EPO()->cart_edit_key )
		) {
			return $passed;
		}

		// Get the woocommerce cart.
		/* @var $cart \WC_Cart phpcs:ignore */
		$cart = $woocommerce->cart;

		// Init the cart contents ( pull from memory, etc ).
		$cart->get_cart();

		// Get the old cart item key from the TM Extra Product Options plugin.
		$old_cart_item_key = THEMECOMPLETE_EPO()->cart_edit_key;

		// Stash the mystyle_data (if it exists).
		if ( isset( $cart->cart_contents[ $old_cart_item_key ]['mystyle_data'] ) ) {
			$this->mystyle_data = $cart->cart_contents[ $old_cart_item_key ]['mystyle_data'];
		}

		return $passed;
	}

	/**
	 * Copy the mystyle_data from the stash to the new cart item.
	 *
	 * @param string $cart_item_key The cart item key of the item being added.
	 * @param int    $product_id The id of the product being added.
	 * @param int    $quantity The quantity of items being added.
	 * @param int    $variation_id ID of the variation being added to the cart.
	 * @param array  $variation attribute values.
	 * @param array  $cart_item_data Extra cart item data that we want to pass
	 * into the item.
	 * @return string|void
	 * @global \WooCommerce $woocommerce
	 */
	public function copy_mystyle_data(
		$cart_item_key,
		$product_id,
		$quantity,
		$variation_id,
		$variation,
		$cart_item_data
	) {
		global $woocommerce;

		// Return if this isn't the scenario that we are looking for.
		if (
				( ! self::is_tm_extra_product_options_edit_request( $_REQUEST ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
				|| ( null === $this->mystyle_data )
		) {
			return;
		}

		// Get the woocommerce cart.
		/* @var $cart \WC_Cart phpcs:ignore */
		$cart = $woocommerce->cart;

		// Init the cart contents ( pull from memory, etc ).
		$cart->get_cart();

		// Copy the mystyle data.
		$cart->cart_contents[ $cart_item_key ]['mystyle_data'] = $this->mystyle_data;
	}

	/**
	 * Function that determines whether or not the passed request is a TM Extra
	 * Product Options edit POST request.
	 *
	 * @param REQUEST $request The current request object.
	 * @return boolean Returns true if the passed request is a TM Extra Product
	 * Options edit POST request. Otherwise, returns false.
	 */
	public static function is_tm_extra_product_options_edit_request( $request ) {
		$ret = false;

		if (
				( isset( $request['tm_cart_item_key'] ) )
				|| ( isset( $request['tc_cart_edit_key'] ) )
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Tm_Extra_Product_Options Returns the singleton instance
	 * of this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Tm_Extra_Product_Options Returns the singleton instance
	 * of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
