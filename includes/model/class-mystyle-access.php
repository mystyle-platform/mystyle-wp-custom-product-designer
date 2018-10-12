<?php

/**
 * MyStyle Access class.
 *
 * The MyStyle_Access class stores the various different possible values for the
 * MyStyle access variable.
 *
 * @package MyStyle
 * @since 1.4.2
 */
abstract class MyStyle_Access {

	/**
	 * Anyone can access.
	 * @var int
	 */
	public static $PUBLIC = 0;

	/**
	 * Only the author can access.
	 * @var int
	 */
	public static $PRIVATE = 1;

	/**
	 * Only the admin can access (not yet implemented).
	 * @var int
	 */
	public static $RESTRICTED = 2;

}
