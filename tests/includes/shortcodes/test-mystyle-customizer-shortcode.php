<?php
/**
 * The MyStyleCustomizerShortcodeTest class includes tests for testing the
 * MyStyle_Customizer_Shortcode class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-wc.php';
require_once MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php';

/**
 * MyStyleCustomizerShortcodeTest class.
 */
class MyStyleCustomizerShortcodeTest extends WP_UnitTestCase {

	/**
	 * Test the modify_woocommerce_shortcode_products_query function with valid
	 * parameters.
	 */
	public function test_modify_woocommerce_shortcode_products_query() {

		// Mock the args.
		$args               = array();
		$args['meta_query'] = array(); // phpcs:ignore WordPress.VIP.SlowDBQuery.slow_db_query_meta_query

		$modified_args = MyStyle_Customizer_Shortcode::modify_woocommerce_shortcode_products_query( $args );

		// Assert that the modified args include the mystyle_enabled meta key.
		$this->assertContains( '_mystyle_enabled', $modified_args['meta_query'][0]['key'] );
	}

	/**
	 * Test the output function with valid parameters.
	 */
	public function test_output_with_valid_params() {

		// Mock the GET params.
		$_GET['product_id'] = 1;
		$passthru           = base64_encode(
			wp_json_encode(
				array(
					'post' => array(
						'quantity'    => 2,
						'add-to-cart' => 1,
					),
				)
			)
		);
		$_GET['h']          = $passthru;

		// Call the function.
		$output = MyStyle_Customizer_Shortcode::output();

		// Assert that the output includes the customizer-wrapper.
		$this->assertContains( '<div id="customizer-wrapper">', $output );

		// Assert that the expected passthru is included.
		$expected_passthru = '"passthru": [{"fieldName": "h", "fieldValue": "' . $passthru . '"';
		$this->assertContains( $expected_passthru, $output );
	}

	/**
	 * Test the output function with invalid/no params.
	 */
	public function test_output_with_no_params() {

		// Call the function.
		$output = MyStyle_Customizer_Shortcode::output();

		// Assert that the output includes an iframe tag.
		$this->assertContains( 'Sorry, no products are currently available for customization.', $output );
	}

	/**
	 * Test the output function with no h param.  The function should stuff in
	 * some defaults.
	 */
	public function test_output_with_no_h_param() {

		// Mock the GET params.
		$_GET['product_id'] = 1;

		// Call the function.
		$output = MyStyle_Customizer_Shortcode::output();

		// Assert that the output includes the customizer-wrapper.
		$this->assertContains( '<div id="customizer-wrapper">', $output );

		// Build the expected passthru.
		$passthru          = base64_encode(
			wp_json_encode(
				array(
					'post' => array(
						'quantity'    => 1,
						'add-to-cart' => 1,
					),
				)
			)
		);
		$expected_passthru = '"passthru": [{"fieldName": "h", "fieldValue": "' . $passthru . '"}]';

		// Assert that the expected passthru is included.
		$this->assertContains( $expected_passthru, $output );
	}

	/**
	 * Test the output function with h and settings parameters but with a
	 * redirect url that isn't permitted. This should throw a
	 * MyStyle_Bad_Request_Exception.
	 */
	public function test_output_with_settings_param_with_non_permitted_redirect_url() {
		$this->setExpectedException( 'MyStyle_Bad_Request_Exception' );

		// Mock the GET params.
		$_GET['product_id'] = 1;
		$passthru           = base64_encode(
			wp_json_encode(
				array(
					'post' => array(
						'quantity'    => 2,
						'add-to-cart' => 1,
					),
				)
			)
		);
		$_GET['h']          = $passthru;

		$settings         = base64_encode(
			wp_json_encode(
				array(
					'redirect_url' => 'https://www.example.com',
					'email_skip'   => '1',
					'print_type'   => 'fake',
				)
			)
		);
		$_GET['settings'] = $settings;

		// Call the function.
		$output = MyStyle_Customizer_Shortcode::output();

		// Assert that the output includes the customizer-wrapper.
		$this->assertContains( '<div id="customizer-wrapper">', $output );

		// Assert that the expected passthru is included.
		$expected_passthru = '"passthru": "' . $passthru . '"';
		$this->assertContains( $expected_passthru, $output );
	}

	/**
	 * Test the output function with h and settings parameters and a permitted
	 * redirect url ( domain is on the whitelist ).
	 */
	public function test_output_with_settings_param_with_permitted_redirect_url() {

		// Install the redirect_url_whitelist.
		$options = array();
		update_option( MYSTYLE_OPTIONS_NAME, $options );
		$options['redirect_url_whitelist'] = "www.example.com\r\nwww.example.net";
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Mock the GET params.
		$_GET['product_id'] = 1;
		$passthru           = base64_encode(
			wp_json_encode(
				array(
					'post' => array(
						'quantity'    => 2,
						'add-to-cart' => 1,
					),
				)
			)
		);
		$_GET['h']          = $passthru;

		$settings         = base64_encode(
			wp_json_encode(
				array(
					'redirect_url' => 'https://www.example.com',
					'email_skip'   => '1',
					'print_type'   => 'fake',
				)
			)
		);
		$_GET['settings'] = $settings;

		// Call the function.
		$output = MyStyle_Customizer_Shortcode::output();

		// Assert that the output includes the customizer-wrapper.
		$this->assertContains( '<div id="customizer-wrapper">', $output );

		// Assert that the expected passthru is included.
		$expected_passthru = '"passthru": [{"fieldName": "h", "fieldValue": "' . $passthru . '"}]';
		$this->assertContains( $expected_passthru, $output );
	}

}
