<?php
/**
 * The MyStyleWooCommerceAdminOrderTest class includes tests for testing the
 * MyStyle_WooCommerce_Admin_Order class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-order.php';

/**
 * MyStyleWooCommerceAdminOrderTest class.
 */
class MyStyleWooCommerceAdminOrderTest extends WP_UnitTestCase {

	/**
	 * Test the constructor
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_wc_admin_order = new MyStyle_WooCommerce_Admin_Order();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['admin_init'] );
		$this->assertContains( 'admin_init', $function_names );
	}

	/**
	 * Test the admin_init() function.
	 *
	 * @global $wp_filter
	 */
	public function test_admin_init() {
		global $wp_filter;

		$mystyle_wc_admin_order = new MyStyle_WooCommerce_Admin_Order();
		$mystyle_wc_admin_order->admin_init();

		// Assert that the add_order_item_header function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_admin_order_item_headers'] );
		$this->assertContains( 'add_order_item_header', $function_names );

		// Assert that the admin_order_item_values function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_admin_order_item_values'] );
		$this->assertContains( 'admin_order_item_values', $function_names );
	}

	/**
	 * Test the add_order_item_header function.
	 */
	public function test_add_order_item_header() {
		$mystyle_wc_admin_order = new MyStyle_WooCommerce_Admin_Order();

		// Assert that the header was rendered.
		ob_start();
		$mystyle_wc_admin_order->add_order_item_header();
		$outbound = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<th class="item-mystyle">', $outbound );
	}

	/**
	 * Test the admin_order_item_values function.
	 */
	public function test_admin_order_item_values() {
		$item = new WC_Order_Item_Product();

		$mystyle_wc_admin_order = new MyStyle_WooCommerce_Admin_Order();

		// Assert that the header was rendered.
		ob_start();
		$mystyle_wc_admin_order->admin_order_item_values( null, $item, 0 );
		$outbound = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<td class="item-mystyle">', $outbound );
	}

}
