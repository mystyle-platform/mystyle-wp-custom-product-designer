<?php
/**
 *
 * Class for integrating with the TM Extra Product Options plugin.
 *
 * @package MyStyle
 * @since 3.13.1
 */

/**
 * MyStyle_Tm_Extra_Product_Options class.
 */
class MyStyle_Tm_Extra_Product_Options {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Tm_Extra_Product_Options
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Tm_Extra_Product_Options Returns the singleton instance
	 * of this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Tm_Extra_Product_Options Returns the singleton instance
	 * of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
