<?php
/**
 * The MyStyle_API_Interface class is an abstract class that can be used by the
 * MyStyle_DBManager to perisit objects to the database.
 *
 * @package MyStyle
 * @since 1.6.0
 */

/**
 * MyStyle_API_Interface interface.
 */
interface MyStyle_API_Interface {

	/**
	 * Retrieves design data from the API and adds it to the passed design
	 * object.
	 *
	 * @param MyStyle_Design $design The design that you are working with.
	 * @return \MyStyle_Design
	 */
	public function add_api_data_to_design( MyStyle_Design $design );

	/**
	 * Creates and returns a MyStyle_User object using the passed user_id and
	 * data retrieved from the API.
	 *
	 * @param integer $user_id The MyStyle user id.
	 * @return \MyStyle_User
	 */
	public function get_user( $user_id );
}
