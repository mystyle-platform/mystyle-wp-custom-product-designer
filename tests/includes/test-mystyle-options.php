<?php
/**
 * The MyStyleOptionsTest class includes tests for testing the MyStyle_Options
 * class.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php';

/**
 * MyStyleOptionsTest class.
 */
class MyStyleOptionsTest extends WP_UnitTestCase {

	/**
	 * Assert that are_keys_installed() correctly returns whether or not a
	 * MyStyle API Key and Secret have been installed.
	 */
	public function test_are_keys_installed() {
		// Clear out any options.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Assert function correctly determines api_key and secret are not
		// installed.
		$this->assertFalse( MyStyle_Options::are_keys_installed() );

		// Install the api_key and secret.
		$options['api_key'] = 'test_key';
		$options['secret']  = 'test_secret';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Assert function correctly determines that keys are installed.
		$this->assertTrue( MyStyle_Options::are_keys_installed() );
	}

	/**
	 * Assert that get_api_key() returns the expected API Key.
	 */
	public function test_get_api_key() {
		// Install the api_key.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['api_key'] = 'test';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$api_key = MyStyle_Options::get_api_key();

		if ( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
			$this->assertContains( MYSTYLE_OVERRIDE_API_KEY, $api_key );
		} else {
			$this->assertEquals( 'test', $api_key );
		}
	}

	/**
	 * Assert that get_secret() returns the expected secret.
	 */
	public function test_get_secret() {
		// Install the secret.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['secret'] = 'test';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$secret = MyStyle_Options::get_secret();

		if ( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
			$this->assertContains( MYSTYLE_OVERRIDE_SECRET, $secret );
		} else {
			$this->assertEquals( 'test', $secret );
		}
	}

	/**
	 * Assert that get_enable_flash() returns the expected enable_flash value.
	 */
	public function test_get_enable_flash() {
		// Set enable_flash.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['enable_flash'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$enable_flash = MyStyle_Options::enable_flash();

		$this->assertTrue( $enable_flash );
	}

	/**
	 * Assert that is_demo_mode() returns true if api key is a demo key.
	 */
	public function test_is_demo_mode_for_demo_key() {
		$demo_key = 74;

		// Install the api_key.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['api_key'] = $demo_key;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
			echo 'Error, can\'t test.';
		} else {
			$this->assertTrue( MyStyle_Options::is_demo_mode() );
		}
	}

	/**
	 * Assert that is_demo_mode() returns false if api key is a not a demo key.
	 */
	public function test_is_demo_mode_for_non_demo_key() {
		$demo_key = 72;

		// Install the api_key.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['api_key'] = $demo_key;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$api_key = MyStyle_Options::get_api_key();

		if ( defined( 'MYSTYLE_OVERRIDE_API_KEY' ) ) {
			echo 'Error, can\'t test.';
		} else {
			$this->assertFalse( MyStyle_Options::is_demo_mode() );
		}
	}

	/**
	 * Assert that get_form_integration_config() returns the expected
	 * form_integration_config value.
	 */
	public function test_get_form_integration_config() {
		$test_val = 'test';

		// Set form_integration_config.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['form_integration_config'] = $test_val;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Call the function.
		$form_integration_config = MyStyle_Options::get_form_integration_config();

		// Assert that the expected value is returned.
		$this->assertEquals( $test_val, $form_integration_config );
	}

	/**
	 * Assert that enable_alternate_design_complete_redirect() returns the
	 * expected enable_alternate_design_complete_redirect value.
	 */
	public function test_enable_alternate_design_complete_redirect() {
		// Set enable_alternate_design_complete_redirect.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['enable_alternate_design_complete_redirect'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$enable_alternate_design_complete_redirect = MyStyle_Options::enable_alternate_design_complete_redirect();

		$this->assertEquals( 1, $enable_alternate_design_complete_redirect );
	}

	/**
	 * Assert that get_alternate_design_complete_redirect_url() returns the
	 * expected URL.
	 */
	public function test_get_alternate_design_complete_redirect_url() {
		// Install the alternate_design_complete_redirect_url.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['alternate_design_complete_redirect_url'] = 'http://www.example.com';
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$url = MyStyle_Options::get_alternate_design_complete_redirect_url();

		$this->assertEquals( 'http://www.example.com', $url );
	}

	/**
	 * Assert that get_redirect_url_whitelist() returns the expected value.
	 */
	public function test_get_redirect_url_whitelist() {
		// Install the redirect_url_whitelist.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['redirect_url_whitelist'] = "www.example.com\r\nwww.example.net";
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$whitelist_array = MyStyle_Options::get_redirect_url_whitelist();

		$this->assertEquals( 'www.example.com', $whitelist_array[0] );
		$this->assertEquals( 'www.example.net', $whitelist_array[1] );
	}

	/**
	 * Assert that get_redirect_url_whitelist() returns the expected array when
	 * values get stored with alternate line endings ( \n instead of \r\n ).
	 */
	public function test_get_redirect_url_whitelist_with_alternate_line_endings() {
		// Install the redirect_url_whitelist.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['redirect_url_whitelist'] = "www.example.com\nwww.example.net";
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$whitelist_array = MyStyle_Options::get_redirect_url_whitelist();

		$this->assertEquals( 'www.example.com', $whitelist_array[0] );
		$this->assertEquals( 'www.example.net', $whitelist_array[1] );
	}

	/**
	 * Assert that is_redirect_url_permitted() returns true when the domain is
	 * on the whitelist.
	 */
	public function test_is_redirect_url_permitted_returns_true_when_domain_whitelisted() {
		$whitelist    = "www.example.com\r\nwww.example.net";
		$redirect_url = 'https://www.example.net/somepage?somevar=someval';
		$expected     = true;

		// Install the redirect_url_whitelist.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['redirect_url_whitelist'] = $whitelist;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$permitted = MyStyle_Options::is_redirect_url_permitted( $redirect_url );

		$this->assertEquals( $expected, $permitted );
	}

	/**
	 * Assert that is_redirect_url_permitted() returns false when the domain is
	 * not on the whitelist.
	 */
	public function test_is_redirect_url_permitted_returns_false_when_domain_not_whitelisted() {
		$whitelist    = "www.example.com\nwww.example.net";
		$redirect_url = 'https://www.malware.com/somethingnasty';
		$expected     = false;

		// Install the redirect_url_whitelist.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['redirect_url_whitelist'] = $whitelist;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$permitted = MyStyle_Options::is_redirect_url_permitted( $redirect_url );

		$this->assertEquals( $expected, $permitted );
	}

	/**
	 * Assert that is_redirect_url_permitted() returns false when no whitelist.
	 * exists.
	 */
	public function test_is_redirect_url_permitted_returns_false_when_no_whitelist_exists() {
		$redirect_url = 'https://www.example.com';
		$expected     = false;

		$permitted = MyStyle_Options::is_redirect_url_permitted( $redirect_url );

		$this->assertEquals( $expected, $permitted );
	}

	/**
	 * Assert that enable_configur8() returns the expected value.
	 */
	public function test_enable_configur8() {
		// Set enable_configur8.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['enable_configur8'] = 1;
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		$enable_configur8 = MyStyle_Options::enable_configur8();

		$this->assertEquals( 1, $enable_configur8 );
	}

	/**
	 * Assert that the is_option_enabled function returns true when the value
	 * equals 1.
	 */
	public function test_is_option_enabled_returns_true_when_val_equals_1() {
		// Set up the test data.
		$option_name = 'test_option';
		$option_key  = 'test_key';

		// Set the option.
		$options = array();
		update_option( $option_name, $options );
		$options[ $option_key ] = 1;
		update_option( $option_name, $options );

		// Call the function.
		$enabled = MyStyle_Options::is_option_enabled( $option_name, $option_key );

		// Assert that the method returned true as expected.
		$this->assertTrue( $enabled );
	}

	/**
	 * Assert that the is_option_enabled function returns false when the value
	 * equals 0.
	 */
	public function test_is_option_enabled_returns_false_when_val_equals_0() {
		// Set up the test data.
		$option_name = 'test_option';
		$option_key  = 'test_key';

		// Set the option.
		$options = array();
		update_option( $option_name, $options );
		$options[ $option_key ] = 0;
		update_option( $option_name, $options );

		// Call the function.
		$enabled = MyStyle_Options::is_option_enabled( $option_name, $option_key );

		// Assert that the method returned false as expected.
		$this->assertFalse( $enabled );
	}

	/**
	 * Assert that the is_option_enabled function returns false when the value
	 * isn't set.
	 */
	public function test_is_option_enabled_returns_false_when_val_not_set() {
		// Set up the test data.
		$option_name = 'test_option';
		$option_key  = 'test_key';

		// Don't set the option.
		// Call the function.
		$enabled = MyStyle_Options::is_option_enabled( $option_name, $option_key );

		// Assert that the method returned false as expected.
		$this->assertFalse( $enabled );
	}

	/**
	 * Assert that the is_option_enabled function returns true when the value
	 * isn't set but the default is set to true.
	 */
	public function test_is_option_enabled_returns_true_when_val_not_set_and_default_set_to_true() {
		// Set up the test data.
		$option_name = 'test_option';
		$option_key  = 'test_key';
		$default     = true;

		// Don't set the option.
		// Call the function.
		$enabled = MyStyle_Options::is_option_enabled(
			$option_name,
			$option_key,
			$default
		);

		// Assert that the method returned true as expected.
		$this->assertTrue( $enabled );
	}

}
