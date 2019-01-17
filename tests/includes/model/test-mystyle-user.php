<?php
/**
 * The MyStyleUserTest class includes tests for testing the MyStyle_User
 * class.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyleUserTest class.
 */
class MyStyleUserTest extends WP_UnitTestCase {

	/**
	 * Test the create function.
	 */
	public function test_create() {
		$user_id = 1;
		$email   = 'someone@example.com';

		$user = new MyStyle_User( $user_id, $email );

		// Assert that the user is constructed.
		$this->assertEquals( 'MyStyle_User', get_class( $user ) );
	}

}
