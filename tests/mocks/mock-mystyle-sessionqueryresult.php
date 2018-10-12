<?php

/**
 * Mocks the result of a mystyle session query.
 *
 * @package MyStyle
 * @since 1.3.0
 */
class MyStyle_MockSessionQueryResult {

	public $session_id;
	public $session_created;
	public $session_created_gmt;
	public $session_modified;
	public $session_modified_gmt;

	/**
	 * Constructor
	 * @param string $session_id An id for the session.
	 */
	public function __construct($session_id) {
		$this->session_id = $session_id;
		$this->session_created = '2015-08-06 22:35:52';
		$this->session_created_gmt = '2015-08-06 22:35:52';
		$this->session_modified = '2015-08-06 22:35:52';
		$this->session_modified_gmt = '2015-08-06 22:35:52';
	}

}
