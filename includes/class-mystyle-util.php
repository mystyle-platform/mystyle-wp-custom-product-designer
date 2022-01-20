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

	/**
	 * Preps a value for being returned by the REST API.
	 *
	 * This function does the following:
	 *
	 *  * Replaces empty strings ("") with nulls.
	 *
	 * @param mixed $val The value that you want to prep.
	 * @return int|null Returns value prepped and ready to returned by the
	 * REST API.
	 */
	public static function prep_rest_val( $val ) {
		$ret = null;

		if ( ! empty( $val ) ) {
			$ret = $val;
		}

		return $ret;
	}
    
    /**
	 * Helper method that encrypts or decrypts the passed string. This is used
	 * for hashing the user email for the URL.
	 *
	 * @param string $action The action to perform. Valid values are "encrypt"
	 * and "decrypt".
	 * @param string $string The string to encrypt or decrypt.
	 */
	public static function encrypt_decrypt( $action, $string ) {
		$output = false;

		$encrypt_method = 'AES-256-CBC';
		$secret_key     = wp_salt( 'auth' );
		$secret_iv      = wp_salt( 'secure_auth' );

		// hash.
		$key = hash( 'sha256', $secret_key );

		// iv - encrypt method AES-256-CBC expects 16 bytes.
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( 'encrypt' === $action ) {
			$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
			$output = base64_encode( $output );
		} elseif ( 'decrypt' === $action ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		return $output;
	}

}
