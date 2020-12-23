<?php
/**
 * The MyStyle Session class represents a user session for users of MyStyle
 * Customizer.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyle_Session class.
 */
class MyStyle_Session implements MyStyle_Entity {

	/**
	 * The key to use for the session.
	 *
	 * @var string
	 */
	const SESSION_KEY = 'mystyle';

	/**
	 * The name of the cookie that tracks the session.
	 *
	 * @var string
	 */
	const COOKIE_NAME = 'mystyle_session_id';

	/**
	 * The name of the database table where sessions are stored.
	 *
	 * Note: this is without the db prefix.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'mystyle_sessions';

	/**
	 * The name of the primary key column in the database.
	 *
	 * @var string
	 */
	const PRIMARY_KEY = 'session_id';

	/**
	 * The primary key. An alphanumeric string. Also the cookie.
	 *
	 * @var string
	 */
	private $session_id;

	/**
	 * The date the session was created.
	 *
	 * @var number
	 */
	private $created;

	/**
	 * The date the session was created (adjusted to the GMT timezone).
	 *
	 * @var number
	 */
	private $created_gmt;

	/**
	 * The date the session was last modified/used.
	 *
	 * @var number
	 */
	private $modified;

	/**
	 * The date the session was last modified/used (adjusted to the GMT timezone).
	 *
	 * @var number
	 */
	private $modified_gmt;

	/**
	 * Whether or not the session has been persisted to the database.
	 *
	 * @var boolean
	 */
	private $persistent;

	/**
	 * Constructor. Note: see the functions below for additional ways to create
	 * a Design.
	 */
	public function __construct() {
		$this->created      = date( MyStyle::STANDARD_DATE_FORMAT );
		$this->created_gmt  = gmdate( MyStyle::STANDARD_DATE_FORMAT );
		$this->modified     = date( MyStyle::STANDARD_DATE_FORMAT );
		$this->modified_gmt = gmdate( MyStyle::STANDARD_DATE_FORMAT );
		$this->persistent   = false;
	}

	/**
	 * Static function to create a new Session. Call using
	 * MyStyle_Session::create();
	 *
	 * @param string $session_id The id (alphanumeric string of the session that
	 * you are creating.
	 * @return \self Works like a constructor.
	 */
	public static function create( $session_id = null ) {
		$instance = new self();

		if ( null !== $session_id ) {
			$instance->session_id = $session_id;
		} else {
			$instance->session_id = self::generate_session_id();
		}

		return $instance;
	}

	/**
	 * Static function to create a Session from a WP result object. Call
	 * using MyStyle_Session::create_from_result_object($result_object);  This
	 * function should correspond with the get_data_array() function below.
	 *
	 * @param array $result_object A WP row result object to be used to
	 * construct the Session. This is an object with public fields that
	 * correspond to the column names from the database.
	 * @return \self Works like a constructor.
	 */
	public static function create_from_result_object( $result_object ) {
		$instance = new self();

		$instance->session_id   = htmlspecialchars( $result_object->session_id );
		$instance->created      = htmlspecialchars( $result_object->session_created );
		$instance->created_gmt  = htmlspecialchars( $result_object->session_created_gmt );
		$instance->modified     = htmlspecialchars( $result_object->session_modified );
		$instance->modified_gmt = htmlspecialchars( $result_object->session_modified );

		$instance->persistent = true;

		return $instance;
	}

	/**
	 * Gets the value of session_id.
	 *
	 * @return string Returns the value of session_id.
	 */
	public function get_session_id() {
		return $this->session_id;
	}

	/**
	 * Gets the value of created.
	 *
	 * @return number Returns the value of created.
	 */
	public function get_created() {
		return $this->created;
	}

	/**
	 * Gets the value of created_gmt.
	 *
	 * @return number Returns the value of created_gmt.
	 */
	public function get_created_gmt() {
		return $this->created_gmt;
	}

	/**
	 * Sets the value of modified.
	 *
	 * @param number $modified The new value for modified.
	 */
	public function set_modified( $modified ) {
		$this->modified = $modified;
	}

	/**
	 * Gets the value of modified.
	 *
	 * @return number Returns the value of modified.
	 */
	public function get_modified() {
		return $this->modified;
	}

	/**
	 * Sets the value of modified_gmt.
	 *
	 * @param number $modified_gmt The new value for modified_gmt.
	 */
	public function set_modified_gmt( $modified_gmt ) {
		$this->modified_gmt = $modified_gmt;
	}

	/**
	 * Gets the value of modified_gmt.
	 *
	 * @return number Returns the value of modified_gmt.
	 */
	public function get_modified_gmt() {
		return $this->modified_gmt;
	}

	/**
	 * Sets the value of persistent.
	 *
	 * @param boolean $persistent Sets whether or not the Session is persistent
	 * (stored in the database).
	 */
	public function set_persistent( $persistent ) {
		$this->persistent = $persistent;
	}

	/**
	 * Gets the value of persistent.
	 *
	 * @return boolean Returns whether or not the Session is persistent (stored
	 * in the database).
	 */
	public function is_persistent() {
		return $this->persistent;
	}

	/**
	 * Gets the SQL schema for creating the datbase table.
	 *
	 * @global wpdb $wpdb
	 * @return string Returns a string containing SQL schema for creating the
	 * table.
	 */
	public static function get_schema() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		return "
            CREATE TABLE $table_name (
                session_id varchar(100) NOT NULL,
                session_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (session_id)
            )";
	}

	/**
	 * Returns the table name for storing the entity.
	 *
	 * @global wpdb $wpdb
	 * @return string Returns the table name for storing the entity.
	 */
	public static function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Gets the name of the primary key column.
	 *
	 * @return string Returns the name of the primary key column for the table.
	 */
	public static function get_primary_key() {
		return self::PRIMARY_KEY;
	}

	/**
	 * Gets the entity data to insert into the table.
	 *
	 * @return array Data to insert (in column => value pairs)
	 */
	public function get_data_array() {
		$data = array();

		$data['session_id']           = $this->session_id;
		$data['session_created']      = $this->created;
		$data['session_created_gmt']  = $this->created_gmt;
		$data['session_modified']     = $this->modified;
		$data['session_modified_gmt'] = $this->modified_gmt;

		return $data;
	}

	/**
	 * Gets the insert format for the entity. This matches up with the
	 * get_data_array() function.
	 *
	 * See https://codex.wordpress.org/Class_Reference/wpdb#INSERT_rows.
	 *
	 * @return (array|string)
	 */
	public function get_insert_format() {

		$formats_arr = array(
			'%s', // session_id.
			'%s', // session_created.
			'%s', // session_created_gmt.
			'%s', // session_modified.
			'%s', // session_modified_gmt.
		);

		return $formats_arr;
	}

	/**
	 * Generates a session id.
	 *
	 * Sources:
	 *  * https://api.drupal.org/api/drupal/includes!bootstrap.inc/function/drupal_random_bytes/7.x
	 *  * http://stackoverflow.com/questions/48124/generating-pseudorandom-alpha-numeric-strings
	 *
	 * @return string Returns the generated session id.
	 */
	public static function generate_session_id() {
		$bytes = '';
		$sid   = '';

		// The number of bytes that we want.
		$count = 43;

		// Use openssl_random_pseudo_bytes if available.
		// Note: PHP versions prior 5.3.4 experienced openssl_random_pseudo_bytes()
		// locking on Windows and rendered it unusable.
		if ( version_compare( PHP_VERSION, '5.3.4', '>=' ) && function_exists( 'openssl_random_pseudo_bytes' ) ) {
			$bytes = openssl_random_pseudo_bytes( $count );
			$sid   = bin2hex( $bytes );
		} else {
			// Range is numbers (48) through capital and lower case letters (122).
			$range_start = 48;
			$range_end   = 122;

			for ( $i = 0; $i < $count; $i++ ) {
				// Generate a number within the range.
				$ascii_no = round( wp_rand( $range_start, $range_end ) );
				// Get the char by number and add it to the session id.
				$sid .= chr( $ascii_no );
			}
		}

		// Ensure that the new sid is not longer than $count.
		$sid = substr( $sid, 0, $count );

		return $sid;
	}

}
