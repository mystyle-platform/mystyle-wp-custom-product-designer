<?php
/**
 * The NoticeTest class includes tests for testing the MyStyle_Notice
 * class
 *
 * @package MyStyle
 * @since 1.2
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/notices/class-mystyle-notice.php';

/**
 * NoticeTest class.
 */
class NoticeTest extends WP_UnitTestCase {

	/**
	 * Test the constructor
	 */
	public function test_constructor() {
		$notice = new MyStyle_Notice();

		$this->assertNotNull( $notice );
	}

	/**
	 * Test the create function.
	 */
	public function test_create() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Assert that the notice_key is as expected.
		$this->assertEquals( $notice_key, $notice->get_notice_key() );
	}

	/**
	 * Test the recreate function.
	 */
	public function test_recreate() {
		$notice_key = 'test_notice';

		$array                = array();
		$array['notice_key']  = $notice_key;
		$array['message']     = 'This is a test notice';
		$array['type']        = 'updated';
		$array['dismissible'] = false;

		$notice = MyStyle_Notice::recreate( $array );

		// Assert that the notice_key is as expected.
		$this->assertEquals( $notice_key, $notice->get_notice_key() );
	}

	/**
	 * Test the get_notice_key function.
	 */
	public function test_get_notice_key() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Assert that the notice_key is returned as expected.
		$this->assertEquals( $notice_key, $notice->get_notice_key() );
	}

	/**
	 * Test the get_message function.
	 */
	public function test_get_message() {
		$message = 'This is a test notice';
		$notice  = MyStyle_Notice::create( 'test_notice', $message );

		// Assert that the message is returned as expected.
		$this->assertEquals( $message, $notice->get_message() );
	}

	/**
	 * Test the get_type function.
	 */
	public function test_get_type() {
		$type   = 'error';
		$notice = MyStyle_Notice::create( 'test_notice', 'This is a test notice', $type );

		// Assert that the type is returned as expected.
		$this->assertEquals( $type, $notice->get_type() );
	}

	/**
	 * Test the is_dismissible function.
	 */
	public function test_is_dismissible() {
		$dismissible = true;
		$notice      = MyStyle_Notice::create( 'test_notice', 'This is a test notice', 'updated', $dismissible );

		// Assert that dismissible is returned as expected.
		$this->assertEquals( $dismissible, $notice->is_dismissible() );
	}

	/**
	 * Test the to_array function.
	 */
	public function test_to_array() {

		$notice_key  = 'test_notice';
		$message     = 'This is a test notice';
		$type        = 'updated';
		$dismissible = true;
		$remind_when = '+30 days';

		$expected_array = array(
			'notice_key'  => $notice_key,
			'message'     => $message,
			'type'        => $type,
			'dismissible' => $dismissible,
			'remind_when' => $remind_when,
		);

		$notice = MyStyle_Notice::create(
			$notice_key,
			$message,
			$type,
			$dismissible,
			$remind_when
		);

		// Assert that the array is returned as expected.
		$this->assertEquals( $expected_array, $notice->to_array() );
	}

	/**
	 * Test that the is_dismissed function returns false when the notice has
	 * not been dismissed.
	 */
	public function test_is_dismissed_returns_false_when_not_dismissed() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Assert that dismissible is returned as expected.
		$this->assertFalse( $notice->is_dismissed() );
	}

	/**
	 * Test that the is_dismissed function returns true when dismissed
	 * permanently.
	 */
	public function test_is_dismissed_returns_true_when_dismissed_permanently() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Add the dismissal to the database.
		$dismissals                = get_option( MYSTYLE_NOTICES_DISMISSED_NAME, array() );
		$dismissals[ $notice_key ] = null;
		update_option( MYSTYLE_NOTICES_DISMISSED_NAME, $dismissals );

		// Assert that dismissible is returned as expected.
		$this->assertTrue( $notice->is_dismissed() );
	}

	/**
	 * Test that the is_dismissed function returns true when the remind_when
	 * date is in the future.
	 */
	public function test_is_dismissed_returns_true_when_remind_on_in_future() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Add the dismissal to the database.
		$dismissals                = get_option( MYSTYLE_NOTICES_DISMISSED_NAME, array() );
		$dismissals[ $notice_key ] = strtotime( 'tomorrow' );
		update_option( MYSTYLE_NOTICES_DISMISSED_NAME, $dismissals );

		// Assert that is_dismissed returns true as expected.
		$this->assertTrue( $notice->is_dismissed() );
	}

	/**
	 * Test that the is_dismissed function returns false when the remind_when
	 * date is in the past.
	 */
	public function test_is_dismissed_returns_false_when_remind_on_in_past() {
		$notice_key = 'test_notice';
		$notice     = MyStyle_Notice::create( $notice_key, 'This is a test notice' );

		// Add the dismissal to the database.
		$dismissals                = get_option( MYSTYLE_NOTICES_DISMISSED_NAME, array() );
		$dismissals[ $notice_key ] = time( strtotime( 'yesterday' ) );
		update_option( MYSTYLE_NOTICES_DISMISSED_NAME, $dismissals );

		// Assert that is_dismissed is returned as expected.
		$this->assertFalse( $notice->is_dismissed() );
	}

}
