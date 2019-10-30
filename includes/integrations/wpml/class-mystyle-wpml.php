<?php
/**
 *
 * Class for integrating with the WPML (The WordPress Multilingual) plugin.
 *
 * @package MyStyle
 * @since 3.13.2
 */

/**
 * MyStyle_Wpml class.
 */
class MyStyle_Wpml {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Wpml
	 */
	private static $instance;

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Wpml Returns the singleton instance
	 * of this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Wpml Returns the singleton instance
	 * of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
