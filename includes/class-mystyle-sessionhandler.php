<?php
/**
 * The MyStyle Session Handler class handles MyStyle Sessions.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyle_SessionHandler class.
 */
class MyStyle_SessionHandler {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_SessionHandler
	 */
	private static $instance;

	/**
	 * The current MyStyle_Session.
	 *
	 * @var MyStyle_Session
	 */
	private $session;

	/**
	 * Switch to enable/disable cookies. This is just used for testing purposes
	 * at this point.
	 *
	 * @var boolean
	 */
	private $use_cookies;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		$this->use_cookies = true; // Use cookie by default.

		add_action( 'wp_logout', array( &$this, 'end_session' ), 10, 0 );
		add_action( 'wp_login', array( &$this, 'start_session' ), 10, 0 );
	}

	/**
	 * Starts the session.
	 */
	public function start_session() {
		// phpcs:disable WordPress.VIP.SessionFunctionsUsage
		if ( ( ! headers_sent() ) && ( ! session_id() ) ) {
			session_start();
		}
		// phpcs:enable WordPress.VIP.SessionFunctionsUsage

		$this->get();
	}

	/**
	 * Ends/destroys the session.
	 */
	public function end_session() {
		// phpcs:ignore WordPress.VIP
		session_destroy();
	}

	/**
	 * Disables cookies. This is just used for testing at this point.
	 */
	public function disable_cookies() {
		$this->use_cookies = false;
	}

	/**
	 * Static function to get the current MyStyle Session. This function does
	 * the following:
	 *
	 *  * Looks for the session in the session variables.
	 *  * Looks for the session in the cookies.
	 *  * If no session is found, it creates one.
	 *  * Updates the modified date of the session and persists it to the
	 *    database.
	 *
	 * @return \MyStyle_Session Returns the current mystyle session.
	 */
	public function get() {
		if ( null !== $this->session ) {
			return $this->session;
		}

		$session = null;

		try {
			// First look in the session variables.
			// phpcs:disable WordPress.VIP
			if ( isset( $_SESSION[ MyStyle_Session::SESSION_KEY ] ) ) {
				$session = $_SESSION[ MyStyle_Session::SESSION_KEY ];
			}
			// phpcs:enable WordPress.VIP

			// Next look in their cookies and the db.
			// Note: If deserialization failed, get_class may return "__PHP_Incomplete_Class".
			// If that happens, go to the db to get the session. Deserialization
			// issues can happen if another plugin starts the php session before our
			// plugin files have been included.
			// TODO: Manual serialization/deserialization might be a better solution to this
			// problem.
			if ( ( null === $session ) || ( 'MyStyle_Session' !== get_class( $session ) ) ) {
				// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected
				if ( isset( $_COOKIE[ MyStyle_Session::COOKIE_NAME ] ) ) {
					$session_id                               = sanitize_key( wp_unslash( $_COOKIE[ MyStyle_Session::COOKIE_NAME ] ) );
					$session                                  = MyStyle_SessionManager::get( $session_id );
					$_SESSION[ MyStyle_Session::SESSION_KEY ] = $session; // phpcs:ignore WordPress.VIP.SessionVariableUsage.SessionVarsProhibited
				}
				// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected
			}
		} catch ( \Exception $ex ) {
			// If an unexpected exception occurs when trying to retrieve the
			// session, fail silently, write to the log, and null out the
			// (possibly corrupted) session. In this scenario, a new session
			// will be created below as if there never was one.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Exception caught while trying to retrieve the user\'s session ' . $ex );
		}

		// Version 1.3.0 - 1.3.4 had an issue where it was creating binary
		// session id's. Here we check to see if the session id is binary and if
		// so, set the $session to null so that a new one is created.
		if ( ( null !== $session ) && ( ! ctype_print( $session->get_session_id() ) ) ) {
			$session = null;
		}

		// If no session is found, create a new one and set the cookie.
		if ( null === $session ) {
			$session                                  = MyStyle_Session::create();
			$_SESSION[ MyStyle_Session::SESSION_KEY ] = $session; // phpcs:ignore WordPress.VIP.SessionVariableUsage.SessionVarsProhibited
			if ( ( $this->use_cookies ) && ( ! headers_sent() ) ) {
				setcookie(
					MyStyle_Session::COOKIE_NAME,
					$session->get_session_id(),
					time() + ( 60 * 60 * 24 * 365 * 10 ),
					'/'
				);
			}
		}

		// If the session is already persistent, update it.
		if ( $session->is_persistent() ) {
			MyStyle_SessionManager::update( $session );
		}

		$this->session = $session;

		return $this->session;
	}

	/**
	 * Persist the current MyStyle_Session to the database. Upgrades a session
	 * to a persistent session (one that is stored in the db) or if it is
	 * already persistent, updates it (updates the modified date).
	 *
	 * @param \MyStyle_Session $session (optional) The session to persist. If
	 * not passed, it will pull the current session using the get function of
	 * this class.
	 * @return \MyStyle_Session Returns the session.
	 * @todo Add unit tests.
	 */
	public function persist( $session = null ) {
		if ( null === $session ) {
			$session = self::get();
		}

		MyStyle_SessionManager::update( $session );

		return $session;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_SessionHandler Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_SessionHandler Returns the singleton instance of this
	 * class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
