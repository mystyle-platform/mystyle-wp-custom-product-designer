<?php
/**
 * The MyStyleNoticeFunctionsTest class includes tests for testing the notice
 * functions.
 *
 * @package MyStyle
 * @since 1.2
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/notices/class-mystyle-notice.php';
require_once MYSTYLE_INCLUDES . 'admin/notices/mystyle-notice-functions.php';

/**
 * MyStyleNoticeFunctionsTest class.
 */
class MyStyleNoticeFunctionsTest extends WP_UnitTestCase {

	/**
	 * Test the mystyle_notice_add_to_queue function.
	 */
	public function test_mystyle_notice_add_to_queue() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		mystyle_notice_add_to_queue( $notice );

		$notices = get_option( MYSTYLE_NOTICES_NAME );

		// Assert that the queued notice was found in the database.
		$this->assertNotEmpty( $notices[ $notice_key ] );
	}

	/**
	 * Test the mystyle_notice_pull_all_queued function.
	 */
	public function test_mystyle_notice_pull_all_queued() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Add the notice to the database.
		$stored_notices                              = get_option( MYSTYLE_NOTICES_NAME );
		$stored_notices[ $notice->get_notice_key() ] = $notice->to_array();
		update_option( MYSTYLE_NOTICES_NAME, $stored_notices );

		$notices = mystyle_notice_pull_all_queued();

		/* @var $notice1 MyStyle_Notice The notice. */
		$notice1 = $notices[0];

		// Assert that the queued notice was returned.
		$this->assertEquals( $notice_key, $notice1->get_notice_key() );

		$stored_notices = get_option( MYSTYLE_NOTICES_NAME );

		// Assert that the queue is now empty.
		$this->assertEmpty( $stored_notices );
	}

}
