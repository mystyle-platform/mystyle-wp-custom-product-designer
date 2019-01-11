<?php
/**
 * The MyStyleExceptionTest class includes tests for testing the
 * MyStyle_Exception class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-exception.php';

/**
 * MyStyleExceptionTest class.
 */
class MyStyleExceptionTest extends WP_UnitTestCase {

	/**
	 * Test throwing an exception.
	 *
	 * @throws MyStyle_Exception Throws a MyStyle_Exception in order to test it.
	 */
	public function test_throw_exception() {
		$this->setExpectedException( 'MyStyle_Exception' );

		throw new MyStyle_Exception( 'MyStyle Exception Message', 500 );
	}

	/**
	 * Test exception content.
	 *
	 * @throws MyStyle_Exception Throws a MyStyle_Exception (but catches it).
	 */
	public function test_exception_content() {
		$message = 'MyStyle Exception Message';
		$code    = 500;

		try {
			throw new MyStyle_Exception( $message, $code );
		} catch ( MyStyle_Exception $e ) {
			// Assert that the expected code is returned.
			$this->assertEquals( $code, $e->getCode() );

			// Assert that the expected message is returned.
			$this->assertEquals( $message, $e->getMessage() );
		}
	}

}
