<?php
/**
 * The MyStyle Frontend Order class has hooks for working with order displays.
 *
 * @package MyStyle
 * @since 3.19.0
 */

/**
 * MyStyle_Frontend_Order class.
 */
class MyStyle_Frontend_Order {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Frontend_Order
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_order_item_name', array( &$this, 'modify_order_item_name' ), 10, 3 );
	}

	/**
	 * Modify the order item name to include the design thumbnail.
	 *
	 * @param string $name The current order item name.
	 * @param object $item The order item object.
	 * @param bool   $is_visible Whether the product is visible.
	 * @return string Returns the updated order item name with design thumbnail prepended.
	 */
	public function modify_order_item_name( $name, $item, $is_visible ) {
		$modified_name = $name;

		// Try to get the design data from the order item.
		$mystyle_data = $item->get_meta( 'mystyle_data' );

		if ( null !== $mystyle_data && is_array( $mystyle_data ) && isset( $mystyle_data['design_id'] ) ) {
			$design_id = $mystyle_data['design_id'];

			/* @var $current_user \WP_User phpcs:ignore */
			$current_user = wp_get_current_user();

			/* @var $session \MyStyle_Session phpcs:ignore */
			$session = MyStyle()->get_session();

			/* @var $design \MyStyle_Design phpcs:ignore */
			$design = MyStyle_DesignManager::get( $design_id, $current_user, $session, true ); // skip the security check because the design is already in the order.

			// Only proceed if we have a design to work with.
			if ( null !== $design ) {
				// Get the design thumbnail URL.
				$thumb_url = $design->get_thumb_url();

				// Create the image HTML.
				$design_image = sprintf(
					'<img src="%1$s" alt="%2$s" style="width: 50px; height: 50px; margin-right: 10px; vertical-align: middle; display: inline-block;" />',
					esc_url( $thumb_url ),
					esc_attr( 'Design ' . $design_id )
				);

				// Prepend the design image to the product name.
				$modified_name = $design_image . $name;
			}
		}

		return $modified_name;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Frontend_Order Returns the singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Frontend_Order Returns the singleton instance of this class.
	 */
	public static function reset_instance() {
		self::$instance = new self();

		return self::$instance;
	}

}
