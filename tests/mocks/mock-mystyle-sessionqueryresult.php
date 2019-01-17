<?php
/**
 * Mocks the result of a mystyle session query.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyle_MockSessionQueryResult class.
 */
class MyStyle_MockSessionQueryResult {

	/**
	 * The primary key. An alphanumeric string. Also the cookie.
	 *
	 * @var string
	 */
	public $session_id;

	/**
	 * The date the session was created.
	 *
	 * @var number
	 */
	public $session_created;

	/**
	 * The date the session was created (adjusted to the GMT timezone).
	 *
	 * @var number
	 */
	public $session_created_gmt;

	/**
	 * The date the session was last modified/used.
	 *
	 * @var number
	 */
	public $session_modified;

	/**
	 * The date the session was last modified/used (adjusted to the GMT timezone).
	 *
	 * @var number
	 */
	public $session_modified_gmt;

	/**
	 * Constructor.
	 *
	 * @param string $session_id An id for the session.
	 */
	public function __construct( $session_id ) {
		$this->session_id           = $session_id;
		$this->session_created      = '2015-08-06 22:35:52';
		$this->session_created_gmt  = '2015-08-06 22:35:52';
		$this->session_modified     = '2015-08-06 22:35:52';
		$this->session_modified_gmt = '2015-08-06 22:35:52';
	}

}
