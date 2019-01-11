<?php
/**
 * The MyStyleTest class includes tests for testing the main mystyle.php file
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * MyStyleTest class.
 */
class MyStyleTest extends WP_UnitTestCase {

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
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Design::get_table_name() );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Session::get_table_name() );
	}

	/**
	 * Assert that the expected constants are declared and accessible.
	 */
	public function test_constants() {
		$this->assertNotEmpty( MYSTYLE_PATH );
		$this->assertNotEmpty( MYSTYLE_INCLUDES );
		$this->assertNotEmpty( MYSTYLE_BASENAME );
		$this->assertNotEmpty( MYSTYLE_SERVER );
		$this->assertNotEmpty( MYSTYLE_VERSION );
		$this->assertNotEmpty( MYSTYLE_OPTIONS_NAME );
		$this->assertNotEmpty( MYSTYLE_NOTICES_NAME );
		$this->assertNotEmpty( MYSTYLE_CUSTOMIZE_PAGEID_NAME );
		$this->assertNotEmpty( MYSTYLE_DESIGN_PROFILE_PAGEID_NAME );
	}

	/**
	 * Assert that the mystyle_customizer shortcode is registered.
	 */
	public function test_customizer_shortcode_is_registered() {
		global $shortcode_tags;

		$this->assertArrayHasKey( 'mystyle_customizer', $shortcode_tags );
	}

	/**
	 * Assert that the mystyle_design_profile shortcode is registered.
	 */
	public function test_design_profile_shortcode_is_registered() {
		global $shortcode_tags;

		$this->assertArrayHasKey( 'mystyle_design_profile', $shortcode_tags );
	}

	/**
	 * Test the site_has_customizable_products function returns true when
	 * customizable products exist.
	 */
	public function test_site_has_customizable_products_returns_false_when_customizable_products_dont_exist() {
		// Create a regular test product.
		$product_id = create_wc_test_product( 'Test Product' );

		// Call the function.
		$result = MyStyle::site_has_customizable_products();

		$this->assertFalse( $result );
	}

	/**
	 * Test the site_has_customizable_products function returns true when
	 * customizable products exist.
	 */
	public function test_site_has_customizable_products_returns_true_when_customizable_products_exist() {
		// Create a regular test product.
		$product_id = create_wc_test_product( 'Test Product' );

		// Enable mystyle for the product.
		update_post_meta( $product_id, '_mystyle_enabled', 'yes' );

		// Call the function.
		$result = MyStyle::site_has_customizable_products();

		$this->assertTrue( $result );
	}

}
