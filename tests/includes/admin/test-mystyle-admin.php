<?php
/**
 * The MystyleAdminTest class includes tests for testing the MyStyle_Admin
 * class.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/class-mystyle-admin.php';
require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-exception.php';

/**
 * MyStyleAdminTest class.
 */
class MyStyleAdminTest extends WP_UnitTestCase {

	/**
	 * Test the constructor
	 */
	public function test_constructor() {
		$mystyle_admin = new MyStyle_Admin();

		global $wp_filter;

		// Assert that the settings link is registered.
		$function_names = get_function_names(
			$wp_filter[ 'plugin_action_links_' . MYSTYLE_BASENAME ]
		);
		$this->assertContains( 'add_settings_link', $function_names );

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['admin_init'] );
		$this->assertContains( 'admin_init', $function_names );
	}

	/**
	 * Test the add_settings_link function.
	 */
	public function test_mystyle_settings_link() {
		$links         = array();
		$mystyle_admin = new MyStyle_Admin();
		$links         = $mystyle_admin->add_settings_link( $links );

		$this->assertEquals( count( $links ), 1 );
	}

	/**
	 * Test the admin_init function.
	 */
	public function admin_init() {
		$mystyle_admin = new MyStyle_Admin();

		// Set the version to something old/incorrect.
		$options            = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options['version'] = 'old_version';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Run the function.
		$mystyle_admin->admin_init();

		// Assert that the version was updated.
		$new_options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$this->assertEquals( $new_options['version'], MYSTYLE_VERSION );

		// Assert that a notice of the upgrade was registered.
		ob_start();
		$mystyle_admin->admin_notices();
		$outbound = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'Upgraded version from', $outbound );
	}

}
