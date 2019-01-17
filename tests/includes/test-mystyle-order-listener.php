<?php
/**
 * The MyStyleOrderListenerTest class includes tests for testing the
 * MyStyle_Order_Listener class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . '../woocommerce/woocommerce.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php';

/**
 * MyStyleOrderListenerTest class.
 */
class MyStyleOrderListenerTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();
		// Remove filters that will create temporary tables. So that permanent
		// tables will be created.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		// Create the tables.
		MyStyle_Install::create_tables();

		// Instantiate the MyStyle and MyStyle_WC object.
		MyStyle::get_instance()->set_WC( new MyStyle_WC() );
	}

	/**
	 * Overwrite the tearDown function to remove our custom tables.
	 */
	public function tearDown() {
		global $wpdb;
		// Perform the actual task according to parent class.
		parent::tearDown();

		// Drop the tables that we created.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Design::get_table_name() );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Session::get_table_name() );
	}

	/**
	 * Test the constructor.
	 *
	 * @global $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_order_listener = new MyStyle_Order_Listener();

		if ( WC()->version < 3.0 ) {
			$function_names = get_function_names( $wp_filter['woocommerce_add_order_item_meta'] );

			// Assert that the add_mystyle_order_item_meta function is registered.
			$this->assertContains( 'add_mystyle_order_item_meta_legacy', $function_names );
		} else {
			$function_names = get_function_names( $wp_filter['woocommerce_checkout_create_order_line_item'] );

			// Assert that the add_mystyle_order_item_meta function is registered.
			$this->assertContains( 'add_mystyle_order_item_meta', $function_names );
		}
	}

	/**
	 * Mock the mystyle_metadata.
	 *
	 * @param null|array|string $metadata The value get_metadata() should return a single metadata value, or an
	 *                                    array of values.
	 * @param int               $object_id  Post ID.
	 * @param string            $meta_key Meta key.
	 * @param string|array      $single   Meta value, or an array of values.
	 * @return string Returns "yes".
	 */
	public function mock_mystyle_metadata( $metadata, $object_id, $meta_key, $single ) {
		return 'yes';
	}
}
