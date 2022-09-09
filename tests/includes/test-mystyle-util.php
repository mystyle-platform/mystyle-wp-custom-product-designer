<?php
/**
 * The MystyleUtilTest class includes tests for testing the MyStyle_Util
 * class.
 *
 * @package MyStyle
 * @since 3.9.1
 */

/**
 * MyStyleUtilTest class.
 */
class MyStyleUtilTest extends WP_UnitTestCase {

	/**
	 * Test the get_query_var_int function when query var is set as a valid int.
	 */
	public function test_get_query_var_int_when_valid() {
		// Mock the query var.
		$design_id = 12;
		set_query_var( 'design_id', $design_id );

		// Call the function.
		$ret = MyStyle_Util::get_query_var_int( 'design_id' );

		// Assert that the expected value is returned.
		$this->assertEquals( 12, $ret );
	}

	/**
	 * Test the get_query_var_int function when query var is not set.
	 */
	public function test_get_query_var_int_when_not_set() {
		// Call the function.
		$ret = MyStyle_Util::get_query_var_int( 'design_id' );

		// Assert that the expected value is returned.
		$this->assertEquals( null, $ret );
	}

	/**
	 * Test the get_query_var_int function when query var is not set.
	 *
	 * @global $wpdb
	 */
	public function test_get_query_var_int_when_not_int() {
		// Mock the query var.
		$design_id = 'FAKE SQL INJECTION';
		set_query_var( 'design_id', $design_id );

		// Call the function.
		$ret = MyStyle_Util::get_query_var_int( 'design_id' );

		// Assert that the expected value is returned.
		$this->assertEquals( null, $ret );
	}

	/**
	 * Test the encrypt_decrypt function.
	 */
	public function test_encrypt_decrypt() {
		// Set up the test data.
		$str = 'foo';

		// Call the function to encrypt the string.
		$encrypted = MyStyle_Util::encrypt_decrypt( 'encrypt', $str );

		$this->assertNotEquals( $str, $encrypted );

		// Call the function to decrypt.
		$decrypted = MyStyle_Util::encrypt_decrypt( 'decrypt', $encrypted );

		// Assert that the expected value is returned.
		$this->assertEquals( $str, $decrypted );
	}

}
