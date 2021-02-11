<?php
/**
 * The MyStyleWpmlTest class includes tests for testing the
 * MyStyle_Wpml class.
 *
 * @package MyStyle
 * @since 3.13.2
 */

/**
 * MyStyleWpmlTest class.
 */
class MyStyleWpmlTest extends WP_UnitTestCase {

	/**
	 * Overwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 *
	 * Note: we need our tables because some of the functions here invoke hooks
	 * that need the tables.
	 */
	public function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();
		// Remove filters that will create temporary tables. So that permanent
		// tables will be created.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		// Create the tables.
		$this->create_tables();
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
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Wpml::get_instance()->get_translations_table_name() );
	}

	/**
	 * Test the constructor.
	 */
	public function test_constructor() {

		$mystyle_wpml = new MyStyle_Wpml();

		// Assert that the object was instantiated as expected.
		$this->assertEquals(
			'MyStyle_Wpml',
			get_class( $mystyle_wpml )
		);
	}

	/**
	 * Test the get_translations_table_name function.
	 */
	public function test_get_translations_table_name() {
		$expected_table_name = 'wptests_icl_translations';

		// Call the method.
		$table_name = MyStyle_Wpml::get_instance()->get_translations_table_name();

		// Assert that the expected table name is returned.
		$this->assertEquals( $expected_table_name, $table_name );
	}

	/**
	 * Test the is_installed function when the WPML plugin is installed.
	 */
	public function test_is_installed_returns_true_when_installed() {
		// Call the method.
		$is_installed = MyStyle_Wpml::get_instance()->is_installed();

		// Assert that the method returned true as expected.
		$this->assertTrue( $is_installed );
	}

	/**
	 * Test the is_installed function when the WPML plugin is installed.
	 *
	 * @global $wpdb
	 */
	public function test_is_installed_returns_false_when_not_installed() {
		global $wpdb;

		// Drop the WPML table (added in the setUp method above).
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Wpml::get_instance()->get_translations_table_name() );

		// Call the method.
		$is_installed = MyStyle_Wpml::get_instance()->is_installed();

		// Assert that the method returned false as expected.
		$this->assertFalse( $is_installed );
	}

	/**
	 * Test that the is_translation_of_page function returns false when the test
	 * page is not a translation of the parent page.
	 */
	public function test_is_translation_of_page_returns_false_when_not_a_translation() {
		// Set up the test data.
		$parent_id      = 1;
		$translation_id = 2;

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->is_translation_of_page( $parent_id, $translation_id );

		// Assert that the method returned false as expected.
		$this->assertFalse( $ret );
	}

	/**
	 * Test that the is_translation_of_page function returns true when the test
	 * page is a translation of the parent page.
	 *
	 * @global $wpdb
	 */
	public function test_is_translation_of_page_returns_true_when_is_a_translation() {
		global $wpdb;

		// Set up the test data.
		$parent_id      = 1;
		$translation_id = 2;
		$trid           = 12345;

		// Insert the test data into the db.
		$table_name = MyStyle_Wpml::get_instance()->get_translations_table_name();
		$format     = array( '%d', '%s', '%d', '%d', '%s', '%s' );
		$parent_row = array(
			'translation_id'       => 100,
			'element_type'         => 'post_page',
			'element_id'           => $parent_id,
			'trid'                 => $trid,
			'language_code'        => 'en',
			'source_language_code' => null,
		);
		$wpdb->insert( $table_name, $parent_row, $format );
		$translation_row = array(
			'translation_id'       => 101,
			'element_type'         => 'post_page',
			'element_id'           => $translation_id,
			'trid'                 => $trid,
			'language_code'        => 'no',
			'source_language_code' => 'en',
		);
		$wpdb->insert( $table_name, $translation_row, $format );

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->is_translation_of_page( $parent_id, $translation_id );

		// Assert that the method returns true as expected.
		$this->assertTrue( $ret );
	}

	/**
	 * Test that the is_translation_of_page function returns false when the WPML
	 * plugin is not installed.
	 *
	 * @global $wpdb;
	 */
	public function test_is_translation_of_page_returns_false_when_wpml_not_installed() {
		global $wpdb;

		// Set up the test data.
		$parent_id      = 1;
		$translation_id = 2;

		// Drop the WPML table (added in the setUp method above).
		$wpdb->query( 'DROP TABLE IF EXISTS ' . MyStyle_Wpml::get_instance()->get_translations_table_name() );

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->is_translation_of_page( $parent_id, $translation_id );

		// Assert that the method returned false as expected.
		$this->assertFalse( $ret );
	}

	/**
	 * Test that the get_default_language function returns null when not set.
	 */
	public function test_get_default_language_returns_null_when_not_set() {

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->get_default_language();

		// Assert that NULL is returned as expected.
		$this->assertNull( $ret );
	}

	/**
	 * Test that the get_default_language function returns the expected
	 * language.
	 */
	public function test_get_default_language_returns_language() {
		// Set up the test data.
		$default_language = 'fr';

		// Mock the WPML options.
		$wpml_options                     = get_option( MyStyle_Wpml::WPML_OPTIONS_KEY, array() );
		$wpml_options['default_language'] = $default_language;
		update_option( MyStyle_Wpml::WPML_OPTIONS_KEY, $wpml_options );

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->get_default_language();

		// Assert that the expected language is returned.
		$this->assertEquals( $default_language, $ret );
	}

	/**
	 * Test that the get_current_language function returns null when no language
	 * has been set.
	 */
	public function test_get_current_language_returns_null_when_not_set() {

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->get_current_language();

		// Assert that NULL is returned as expected.
		$this->assertNull( $ret );
	}

	/**
	 * Test that the get_current_language function returns the expected
	 * language.
	 */
	public function test_get_current_language_returns_language() {
		// Set up the test data.
		$current_language = 'fr';

		// Mock the cookies.
		$_COOKIE['_icl_current_language'] = $current_language;

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->get_current_language();

		// Assert that the expected language is returned.
		$this->assertEquals( $current_language, $ret );

		// Cleanup
		unset( $_COOKIE['_icl_current_language'] );
	}

	/**
	 * Test that the get_current_translation_language function returns null when
	 * no language has been set.
	 */
	public function test_get_current_translation_language_returns_null_when_not_set() {

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->get_current_translation_language();

		// Assert that NULL is returned as expected.
		$this->assertNull( $ret );
	}

	/**
	 * Test that the get_current_translation_language function returns the
	 * expected language. In this scenario, a language has been set and the
	 * set language isn't the default language.
	 */
	public function test_get_current_translation_language_returns_language() {
		// Set up the test data.
		$current_language = 'fr';
		$default_language = 'en';

		// Mock the WPML options.
		$wpml_options                     = get_option( MyStyle_Wpml::WPML_OPTIONS_KEY, array() );
		$wpml_options['default_language'] = $default_language;
		update_option( MyStyle_Wpml::WPML_OPTIONS_KEY, $wpml_options );

		// Mock the cookies.
		$_COOKIE['_icl_current_language'] = $current_language;

		// Call the method.
		$ret = MyStyle_Wpml::get_instance()->get_current_translation_language();

		// Assert that the expected language is returned.
		$this->assertEquals( $current_language, $ret );

		// Cleanup
		unset( $_COOKIE['_icl_current_language'] );
	}

	/**
	 * Private helper method that sets up the database tables which to test
	 * against.
	 *
	 * @global $wpdb;
	 */
	private function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $this->get_schema() );
	}

	/**
	 * Private helper method that gets the SQL schema for creating the test
	 * database table.
	 *
	 * @global wpdb $wpdb
	 * @return string Returns a string containing SQL schema for creating the
	 * table.
	 */
	private function get_schema() {

		$table_name = MyStyle_Wpml::get_instance()->get_translations_table_name();

		$schema = "
			CREATE TABLE {$table_name} (
			translation_id bigint(20) NOT NULL,
			element_type varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post_post',
			element_id bigint(20) DEFAULT NULL,
			trid bigint(20) NOT NULL,
			language_code varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
			source_language_code varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL
            )";

		return $schema;
	}
}
