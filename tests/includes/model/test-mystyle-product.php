<?php
/**
 * The MyStyleProductTest class includes tests for testing the MyStyle_Product
 * class.
 *
 * @package MyStyle
 * @since 2.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleProductTest class.
 */
class MyStyleProductTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();

		// Create the tables.
		MyStyle_Install::create_tables();
	}

	/**
	 * Overwrite the tearDown function to remove our custom tables.
	 */
	public function tearDown() {
		global $wpdb;
		// Perform the actual task according to parent class.
		parent::tearDown();

		// Drop the tables that we created.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Design::get_table_name() ); // phpcs:ignore WordPress.DB
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Session::get_table_name() ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Test the constructor.
	 */
	public function test_constructor() {
		// Get a WC_Product.
		$wc_product = WC_Helper_Product::create_simple_product();

		// Instantitate the MyStyle_Product.
		$mystyle_product = new MyStyle_Product( $wc_product );

		// Assert that a product id is returned.
		$this->assertEquals( 'MyStyle_Product', get_class( $mystyle_product ) );
	}

	/**
	 * Test the get_by_id function.
	 */
	public function test_get_by_id() {
		// Get a WC_Product.
		$wc_product = WC_Helper_Product::create_simple_product();

		// Instantitate the MyStyle_Product.
		$mystyle_product = MyStyle_Product::get_by_id( $wc_product->get_id() );

		// Assert that a product id is returned.
		$this->assertEquals( 'MyStyle_Product', get_class( $mystyle_product ) );
	}

	/**
	 * Test the get_id function.
	 */
	public function test_get_id() {

		// Set up the test data.
		$product = new MyStyle_Product( WC_Helper_Product::create_simple_product() );

		// Call the function.
		$id = $product->get_id();

		// Assert that a product id is returned.
		$this->assertTrue( $id > 0 );
	}

	/**
	 * Test the get_type function.
	 */
	public function test_get_type() {

		// Set up the test data.
		$product       = new MyStyle_Product( WC_Helper_Product::create_simple_product() );
		$expected_type = 'simple';

		// Call the function.
		$type = $product->get_type();

		// Assert that the expected product type is returned.
		$this->assertEquals( $expected_type, $type );
	}

	/**
	 * Test the get_children function.
	 */
	public function test_get_children() {

		// Set up the test data.
		$product = new MyStyle_Product( WC_Helper_Product::create_variation_product() );

		// Call the function.
		$children = $product->get_children();

		// Assert that the expected number of children are returned.
		$this->assertEquals( 6, count( $children ) );
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

	/**
	 * Test the product_is_customizable function when product isn't mystyle
	 * enabled.
	 */
	public function test_product_is_customizable_returns_false_when_product_not_mystyle_enabled() {

		// Create a product.
		$product_id      = create_wc_test_product();
		$product         = new \WC_Product_Simple( $product_id );
		$mystyle_product = new \MyStyle_Product( $product );

		// Call the function.
		$is_customizable = $mystyle_product->is_customizable();

		// Assert that is_customizable is false.
		$this->assertFalse( $is_customizable );
	}

	/**
	 * Test the is_customizable function when product is mystyle enabled.
	 */
	public function test_is_customizable_returns_true_when_product_is_mystyle_enabled() {

		// Create a product.
		$product_id      = create_wc_test_product();
		$product         = new \WC_Product_Simple( $product_id );
		$mystyle_product = new \MyStyle_Product( $product );

		add_post_meta( $product_id, '_mystyle_enabled', 'yes' );

		// Call the function.
		$is_customizable = $mystyle_product->is_customizable();

		// Assert that is_customizable is true.
		$this->assertTrue( $is_customizable );
	}

	/**
	 * Test the configur8_enabled function when the product doesn't have
	 * configur8 enabled.
	 */
	public function test_product_configur8_enabled_returns_false_when_configur8_not_enabled() {

		// Create a product.
		$product_id      = create_wc_test_product();
		$product         = new \WC_Product_Simple( $product_id );
		$mystyle_product = new \MyStyle_Product( $product );

		// Call the function.
		$configur8_enabled = $mystyle_product->configur8_enabled();

		// Assert that configur8_enabled is false.
		$this->assertFalse( $configur8_enabled );
	}

	/**
	 * Test the configur8_enabled function when product has configur8 enabled.
	 */
	public function test_configur8_enabled_returns_true_when_configur8_is_enabled() {

		// Create a product.
		$product_id      = create_wc_test_product();
		$product         = new \WC_Product_Simple( $product_id );
		$mystyle_product = new \MyStyle_Product( $product );

		add_post_meta( $product_id, '_mystyle_configur8_enabled', 'yes' );

		// Call the function.
		$configur8_enabled = $mystyle_product->configur8_enabled();

		// Assert that configur8_enabled is true.
		$this->assertTrue( $configur8_enabled );
	}

	/**
	 * Test the get_parent_design method.
	 */
	public function test_get_parent_design() {

		// Create and persist a design.
		$design_id = 1;
		$design    = MyStyle_MockDesign::get_mock_design( $design_id );
		MyStyle_DesignManager::persist( $design );

		// Create a test product and set the design in the post_meta.
		$wc_product = WC_Helper_Product::create_variation_product();
		add_post_meta( $wc_product->get_id(), '_mystyle_design_id', $design->get_design_id() );
		$mystyle_product = new MyStyle_Product( $wc_product );

		// Call the function.
		/* @var $parent_design \MyStyle_Design The parent design. */
		$parent_design = $mystyle_product->get_parent_design();

		// Assert that the parent design is returned as expected.
		$this->assertEquals( 'MyStyle_Design', get_class( $parent_design ) );
		$this->assertEquals( $design_id, $parent_design->get_design_id() );
	}

}
