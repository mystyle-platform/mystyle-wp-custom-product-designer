<?php
/**
 * The NoticeControllerTest class includes tests for testing the various methods
 * and functions of the MyStyle_Notice_Controller class.
 *
 * @package MyStyle
 * @since 1.2
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/notices/class-mystyle-notice-controller.php';

/**
 * MyStyleNoticeControllerTest class.
 */
class MyStyleNoticeControllerTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_notice_controller = new MyStyle_Notice_Controller();

		// Assert that the admin_notices function is registered.
		$function_names = get_function_names( $wp_filter['admin_notices'] );
		$this->assertContains( 'admin_notices', $function_names );

		// Assert that the wp_ajax_mystyle_set_notice_pref function is registered.
		$function_names = get_function_names( $wp_filter['wp_ajax_mystyle_set_notice_pref'] );
		$this->assertContains( 'set_notice_pref_callback', $function_names );
	}

	/**
	 * Test the admin_notices function.
	 * TODO: add more tests for this function.
	 */
	public function test_admin_notices() {
		// Assert that a notice was registered.
		ob_start();
		MyStyle_Notice_Controller::get_instance()->admin_notices();
		$outbound = ob_get_contents();
		ob_end_clean();

		$this->assertContains( 'MyStyle', $outbound );
	}

	/**
	 * Test the set_notice_pref_callback function.
	 */
	public function test_set_notice_pref_callback() {
		$notice_key  = 'test_notice';
		$remind_when = '+30 days';
		$expected    = '{"notice_key":"' . $notice_key . '","status":"success"}';

		$_POST['notice_key']  = $notice_key;
		$_POST['remind_when'] = $remind_when;

		// Assert that the expected output string is returned.
		$this->expectOutputString( $expected );
		try {
			MyStyle_Notice_Controller::get_instance()->set_notice_pref_callback();
		} catch ( WPDieException $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing.
		}
	}

}
