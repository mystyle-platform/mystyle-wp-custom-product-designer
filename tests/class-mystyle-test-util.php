<?php
/**
 * The MyStyle_Test_Util class has functions for testing the plugin.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * MyStyle_Test_Util class.
 */
abstract class MyStyle_Test_Util {

	/**
	 * Remove all actions that we've hooked to the 'user_register' event.
	 *
	 * This keeps code that isn't part of the SUT ( System Under Test ) from
	 * executing.
	 */
	public static function remove_user_register_actions() {
		remove_action( 'user_register', array( MyStyle_User_Interface::get_instance(), 'on_user_register' ) );
	}

	/**
	 * Creates a test user while removing our hooks.
	 *
	 * @param type $username The username that you want for the test user.
	 * @param type $password The password that you want for the test user.
	 * @param type $email The email that you want for the test user.
	 * @return integer Returns the user_id of the created test_user.
	 */
	public static function create_user( $username, $password, $email ) {
		self::remove_user_register_actions();

		// Mock the user (note this will call the function since it is hooked
		// into the register function).
		$user_id = wp_create_user( 'testuser', 'testpassword', $email );

		return $user_id;
	}

}
