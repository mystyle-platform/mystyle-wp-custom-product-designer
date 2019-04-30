<?php
/**
 * The MyStyleWpRestApiDesignControllerTest class includes tests for testing the
 *  MyStyle_Wp_Rest_Api_Design_Controller class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'wprestapi/class-mystyle-wp-rest-api-design-controller.php';

/**
 * MyStyleWpRestApiDesignControllerTest class.
 */
class MyStyleWpRestApiDesignControllerTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();
		// Remove filters that will create temporary tables. So that permanent tables will be created.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		// Create the tables.
		MyStyle_Install::create_tables();
	}

	/**
	 * Overwrite the tearDown function to remove our custom tables.
	 *
	 * @global $wpdb
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
	 */
	public function test_constructor() {
		$mystyle_frontend = new MyStyle_Wp_Rest_Api_Design_Controller();

		// Assert that the class was instantiated as expected.
		$this->assertEquals(
			'MyStyle_Wp_Rest_Api_Design_Controller',
			get_class( $mystyle_frontend )
		);
	}

}
