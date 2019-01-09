<?php

/**
 * MyStyle Options class.
 *
 * The MyStyle Options class includes functions for setting and getting MyStyle
 * Options.
 *
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_Options {

	/**
	 * Function that looks to see if an mystyle API Key and secret have been
	 * installed.
	 * @return boolean Returns true if an API Key and Secret are installed,
	 * otherwise returns false.
	 */
	static function are_keys_installed() {
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());

		if ((!empty($options['api_key']) ) && (!empty($options['secret']) )) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Function that gets the active api_key.
	 * @return string Returns the active api key.
	 */
	static function get_api_key() {
		$api_key = null;
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());
		if (!empty($options['api_key'])) {
			$api_key = $options['api_key'];
		}
		if (defined('MYSTYLE_OVERRIDE_API_KEY')) {
			$api_key = MYSTYLE_OVERRIDE_API_KEY;
		}

		return $api_key;
	}

	/**
	 * Function that gets the active secret.
	 * @return string Returns the active secret.
	 */
	static function get_secret() {
		$secret = null;
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());
		if (!empty($options['secret'])) {
			$secret = $options['secret'];
		}
		if (defined('MYSTYLE_OVERRIDE_SECRET')) {
			$secret = MYSTYLE_OVERRIDE_SECRET;
		}

		return $secret;
	}

	/**
	 * Function that gets the value of enable_flash setting.
	 * @return boolean Returns true if the enable_flash setting is enabled,
	 * otherwise returns false.
	 */
	static function enable_flash() {
		return self::is_option_enabled(MYSTYLE_OPTIONS_NAME, 'enable_flash');
	}

	/**
	 * Function that determines if the plugin is in demo mode.
	 * @return string Returns the active secret.
	 */
	static function is_demo_mode() {
		$api_key = self::get_api_key();
		$demo_key = 74;

		$ret = ($api_key == $demo_key);

		return $ret;
	}

	/**
	 * Function that gets the Form Integration Config.
	 * @return array|null Returns the Form Integration Config as a string (if
	 * it is set), otherwise returns null.
	 */
	static function get_form_integration_config() {
		$val = null;
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( ! empty( $options['form_integration_config'] ) ) {
			$val = $options['form_integration_config'];
		}

		return $val;
	}

	/**
	 * Function that gets the enable_alternate_design_complete_redirect option.
	 * @return boolean Returns true if the
	 * enable_alternate_design_complete_redirect setting is enabled, otherwise
	 * returns false.
	 */
	static function enable_alternate_design_complete_redirect() {
		return self::is_option_enabled(
						MYSTYLE_OPTIONS_NAME, 'enable_alternate_design_complete_redirect'
		);
	}

	/**
	 * Function that gets the Alternate Design Complete Redirect URL.
	 * @return string|null Returns the Alternate Design Complete Redirect URL if one
	 * is set, otherwise returns null.
	 */
	static function get_alternate_design_complete_redirect_url() {
		$val = null;
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());
		if (!empty($options['alternate_design_complete_redirect_url'])) {
			$val = $options['alternate_design_complete_redirect_url'];
		}

		return $val;
	}

	/**
	 * Function that builds the Alternate Design Complete Redirect URL.
	 * @return string|null Returns the built Alternate Design Complete Redirect
	 * URL if one is set, otherwise returns null.
	 */
	static function build_alternate_design_complete_redirect_url(
	MyStyle_Design $design
	) {

		$url = self::get_alternate_design_complete_redirect_url();
		if (!empty($url)) {
			if (strpos($url, '?') == false) {
				$url .= '?';
			} else {
				$url .= '&';
			}
			$url .= 'design_id=' . $design->get_design_id();
			$url .= '&design_complete=1';
		}

		return $url;
	}

	/**
	 * Function that gets the Redirect URL Whitelist.
	 * @return array|null Returns the Redirect URL Whitelist as an array (if one
	 * is set), otherwise returns null.
	 */
	static function get_redirect_url_whitelist() {
		$val = null;
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());
		if (!empty($options['redirect_url_whitelist'])) {
			$val = preg_split("/\r\n|\n|\r/", $options['redirect_url_whitelist']);
		}

		return $val;
	}

	/**
	 * Function that determines whether or not the passed redirect url is
	 * permitted by the redirect_url_whitelist.
	 * @param $redirect_url The url that you want to check.
	 * @return boolean Returns true if the redirect_url is permitted, otherwise
	 * returns false.
	 */
	static function is_redirect_url_permitted($redirect_url) {
		$allowed = false;
		$redirect_domain = parse_url($redirect_url, PHP_URL_HOST);
		$whitelist_array = self::get_redirect_url_whitelist();

		if (!empty($whitelist_array)) {
			$allowed = in_array($redirect_domain, $whitelist_array);
		}

		return $allowed;
	}

	/**
	 * Function that gets the value of the enable_configur8 option.
	 * @return boolean Returns true if the enable_configur8 option is enabled,
	 * otherwise returns false.
	 */
	static function enable_configur8() {
		return self::is_option_enabled(
						MYSTYLE_OPTIONS_NAME, 'enable_configur8'
		);
	}

	/**
	 * Function that gets the value of the layout_view option.
	 * @return grid if Grid vew selected,
	 * otherwise returns list.
	 */
	static function get_layout_view() {
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( ! empty( $options['layout_views'] ) ) {
			$val = $options['layout_views'];
		}else{
			$val = 'list_view';
		}
		return $val;
	}

	/**
	 * Determines whether or not the passed option is enabled.
	 * @param string $option_name The name of the option. This is passed to
	 * WordPress's get_option function.
	 * @param string $option_key It is assumed that get_option will return an
	 * array. This is the key of the array that you are checking.
	 * @param boolean $default If the option isn't set, this method will return
	 * false (not enabled). If you want, you can have it default to true
	 * instead.
	 * @return boolean Returns true if the option is enabled, otherwise, returns
	 * false.
	 */
	static function is_option_enabled(
	$option_name, $option_key, $default = false
	) {
		$enabled = $default;
		$options = get_option($option_name, array());
		if (isset($options[$option_key])) {
			$val = $options[$option_key];
			if ($val == 1) {
				$enabled = true;
			} else {
				$enabled = false;
			}
		}

		return $enabled;
	}

}
