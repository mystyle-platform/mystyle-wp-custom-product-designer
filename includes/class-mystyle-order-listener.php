<?php
/**
 * The MyStyle_Order_Listener class sets up and controls the MyStyle order related
 * hooks, etc.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * MyStyle_Order_Listener class.
 */
class MyStyle_Order_Listener {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Order_Listener
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( &$this, 'on_order_completed' ), 10, 1 );
		add_action( 'init', array( &$this, 'init' ), 10, 1 );
	}

	/**
	 * Init the class.
	 *
	 * In this function we define some additional hooks. We do this late so that
	 * we can see what version WooCommerce is on.
	 *
	 * Must be public because it is called by a hook.
	 */
	public function init() {
		// We hook these in the init event because we need to wait until WooCommerce is loaded so we know the version.
		if ( MyStyle()->get_WC()->is_installed() ) {
			if ( MyStyle()->get_WC()->version_compare( '3.0', '<' ) ) {
				add_action( 'woocommerce_add_order_item_meta', array( &$this, 'add_mystyle_order_item_meta_legacy' ), 10, 2 );
			} else {
				add_action( 'woocommerce_checkout_create_order_line_item', array( &$this, 'add_mystyle_order_item_meta' ), 10, 4 );
			}
		}
	}

	/**
	 * Add the item meta from the cart to the order.
	 *
	 * @param \WC_Order_Item $item The item being added to the order.
	 * @param string         $cart_item_key The cart_item_key of the item being added.
	 * @param array          $values The values from the cart.
	 * @param \WC_Order      $order The order that is being created.
	 */
	public function add_mystyle_order_item_meta(
		\WC_Order_Item $item,
		$cart_item_key,
		$values,
		\WC_Order $order
	) {
		if ( isset( $values['mystyle_data'] ) ) {
			$item->add_meta_data( 'mystyle_data', $values['mystyle_data'] );
		}
	}

	/**
	 * Legacy function for adding the item meta from the cart to the order.
	 *
	 * For WC < 3.0
	 *
	 * @param number $item_id The item_id of the item being added.
	 * @param array  $values The values from the cart.
	 * @return Returns false on failure. On success, returns the ID of the inserted row.
	 */
	public function add_mystyle_order_item_meta_legacy( $item_id, $values ) {
		if ( isset( $values['mystyle_data'] ) ) {
			return wc_add_order_item_meta( $item_id, 'mystyle_data', $values['mystyle_data'] );
		}
	}

	/**
	 * After the order is completed, do the following for all mystyle enabled
	 * products in the order:
	 *  * Increment the design purchase count.
	 *
	 * @param number $order_id The order_id of the new order.
	 */
	public function on_order_completed( $order_id ) {

		/* @var $current_user \WP_User The current user. */
		$current_user = wp_get_current_user();

		/* @var $session \MyStyle_Session The current session. */
		$session = MyStyle()->get_session();

		// Order object (optional but handy).
		$order = new WC_Order( $order_id );

		if ( count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item_id => $item ) {

				if ( isset( $item['mystyle_data'] ) ) {
					$mystyle_data = maybe_unserialize( $item['mystyle_data'] );
					$design_id    = $mystyle_data['design_id'];

					/* @var $design \MyStyle_Design The design. */
					$skip_security = false;
					// Shipstation bug.
					if ( isset( $_REQUEST['wc-api'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
						$skip_security = true;
					}

					$design = MyStyle_DesignManager::get( $design_id, $current_user, $session, $skip_security );

					// Increment the design purchase count.
					$design->increment_purchase_count();
					MyStyle_DesignManager::persist( $design );
				}
			}
		}
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Order_Listener Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Order_Listener Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
