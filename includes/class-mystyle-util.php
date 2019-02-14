<?php
/**
 *
 * The MyStyle_Util class has general utility functions.
 *
 * @package MyStyle
 * @since 3.9.1
 */

/**
 * MyStyle_Util class.
 */
class MyStyle_Util {

	/**
	 * Gets an int value from the query string. If the query var isn't set or
	 * isn't numeric, this function returns null.
	 *
	 * @param string $var_name The name of the query var (ex: "design_id").
	 * @return int|null Returns the int found in the query string or null if the
	 * var isn't found or isn't an int.
	 */
	public static function get_query_var_int( $var_name ) {
		$ret = null;

		$val_string = get_query_var( $var_name, null );

		if ( ( null !== $val_string ) && ( 0 !== intval( $val_string ) ) ) {
			$ret = intval( $val_string );
		}

		return $ret;
	}

}
