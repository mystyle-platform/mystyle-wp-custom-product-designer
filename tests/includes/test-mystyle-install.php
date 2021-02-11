<?php
/**
 * The MystyleAdminTest class includes tests for testing the MyStyle_Admin
 * class.
 *
 * @package MyStyle
 * @since 0.5.3
 */

/**
 * MyStyleInstallTest class.
 */
class MyStyleInstallTest extends WP_UnitTestCase {

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
	 * Test the create_tables function successfully creates the tables.
	 *
	 * @global $wpdb
	 */
	public function test_create_tables() {
		global $wpdb;

		$table_name = $wpdb->get_var( "SHOW TABLES LIKE '" . MyStyle_Design::get_table_name() . "'" );

		// Assert that the table doesn't yet exist.
		$this->assertNotEquals( MyStyle_Design::get_table_name(), $table_name );

		// Create the tables.
		MyStyle_Install::create_tables();

		$table_name = $wpdb->get_var( "SHOW TABLES LIKE '" . MyStyle_Design::get_table_name() . "'" );

		// Assert that the table now exists.
		$this->assertEquals( MyStyle_Design::get_table_name(), $table_name );
	}

	/**
	 * Test the delta_tables function successfully creates the tables.
	 */
	public function test_delta_tables() {
		global $wpdb;

		$table_name = $wpdb->get_var( "SHOW TABLES LIKE '" . MyStyle_Design::get_table_name() . "'" );

		// Assert that the table doesn't yet exist.
		$this->assertNotEquals( MyStyle_Design::get_table_name(), $table_name );

		// Create the tables.
		MyStyle_Install::delta_tables();

		$table_name = $wpdb->get_var( "SHOW TABLES LIKE '" . MyStyle_Design::get_table_name() . "'" );

		// Assert that the table now exists.
		$this->assertEquals( MyStyle_Design::get_table_name(), $table_name );
	}

	/**
	 * Test the get_schema function.
	 *
	 * @global $wpdb
	 */
	public function test_get_schema() {
		global $wpdb;

		$expected_schema = "
            CREATE TABLE wptests_mystyle_designs (
                ms_design_id bigint(32) NOT NULL,
                ms_product_id bigint(20) NOT NULL,
                ms_user_id bigint(20) NULL,
                ms_email varchar(255) NULL,
                ms_title varchar(255) NULL,
                ms_description text NULL,
                ms_price numeric(15,2) NULL,
                ms_print_url varchar(255) NULL,
                ms_web_url varchar(255) NULL,
                ms_thumb_url varchar(255) NULL,
                ms_design_url varchar(255) NULL,
                product_id bigint(20) NULL,
                user_id bigint(20) NULL DEFAULT NULL,
                design_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                ms_mobile int(1) NOT NULL DEFAULT '0',
                ms_access int(1) NOT NULL DEFAULT '0',
                design_view_count bigint(20) NULL DEFAULT '0',
                design_purchase_count bigint(20) NULL DEFAULT '0',
                session_id varchar(100) NULL DEFAULT NULL,
                cart_data TEXT NULL DEFAULT NULL,
                PRIMARY KEY (ms_design_id)
            ) DEFAULT CHARACTER SET $wpdb->charset COLLATE $wpdb->collate;
            CREATE TABLE wptests_mystyle_sessions (
                session_id varchar(100) NOT NULL,
                session_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (session_id)
            ) DEFAULT CHARACTER SET $wpdb->charset COLLATE $wpdb->collate;";

		$schema = MyStyle_Install::get_schema();

		$this->assertEquals( $expected_schema, $schema );
	}

	/**
	 * Test the activate function.
	 */
	public function test_activate() {
		MyStyle_Install::activate();

		$customize_page_id = MyStyle_Customize_Page::get_id();

		// Assert that the Customize page was created.
		$this->assertNotNull( $customize_page_id );

		$design_profile_page_id = MyStyle_Design_Profile_Page::get_id();

		// Assert that the Design Profile page was created.
		$this->assertNotNull( $design_profile_page_id );
	}

	/**
	 * Test the deactivate function.
	 */
	public function test_deactivate() {
		// Activate the plugin so that we can then deactivate it.
		MyStyle_Install::activate();

		MyStyle_Install::deactivate();

		// Assert that Customize page remains.
		$this->assertTrue( MyStyle_Customize_Page::exists() );
	}

	/**
	 * Test the uninstall function.
	 */
	public function test_uninstall() {
		// Assert that there are options.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$this->assertNotEmpty( $options );

		// Uninstall the plugin.
		MyStyle_Install::uninstall();

		// Assert that the options are still there.
		$options_new = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$this->assertNotEmpty( $options_new );
	}

}
