<?php
/**
 * The MyStyle_SessionManager class has functions for managing MyStyle_Session
 * entities.
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyle_SessionManager class.
 */
abstract class MyStyle_SessionManager extends \MyStyle_EntityManager {

	/**
	 * Persists the passed MyStyle_Session to the database.
	 *
	 * @param MyStyle_Entity $session The MyStyle_Session that you want to
	 * persist.
	 * @return \MyStyle_Session Returns the persisted MyStyle_Session.
	 */
	public static function persist( MyStyle_Entity $session ) {
		$session = parent::persist( $session );

		$session->set_persistent( true );

		return $session;
	}

	/**
	 * Get the session from the database.
	 *
	 * @global wpdb $wpdb
	 * @param string $session_id The session id.
	 * @return \MyStyle_Session Returns the MyStyle_Session entity.
	 */
	public static function get( $session_id ) {
		global $wpdb;

		$session = null;

		$result_object = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT *'
				. "FROM {$wpdb->prefix}mystyle_sessions "
				. 'WHERE session_id = %s',
				$session_id
			)
		);

		if ( null !== $result_object ) {
			$session = MyStyle_Session::create_from_result_object( $result_object );
		}

		return $session;
	}

	/**
	 * Updates the session in the database changing its modified date/time to
	 * the current date/time. Use this function for both create and update
	 * operations.
	 *
	 * @global wpdb $wpdb
	 * @param MyStyle_Session $session The MyStyle_Session that you want to
	 * update.
	 * @return \MyStyle_Session Returns the MyStyle_Session entity.
	 */
	public static function update( MyStyle_Session $session ) {
		global $wpdb;

		$session->set_modified( date( MyStyle::STANDARD_DATE_FORMAT ) );
		$session->set_modified_gmt( date( MyStyle::STANDARD_DATE_FORMAT ) );
		$session->set_persistent( true );

		$wpdb->replace(
			$session->get_table_name(),
			$session->get_data_array(),
			$session->get_insert_format()
		);

		return $session;
	}

	/**
	 * Purges any MyStyle_Sessions that have been abandoned (persisted with no
	 * designs).
	 *
	 * This uses a temp table so that we can purge potentially millions of rows
	 * very quickly. It is designed to do the DROP last only after all other
	 * steps have succeeded.
	 *
	 * This function has the following steps:
	 *
	 *  1. Creates a mystyle_sessions_tmp table using the same schema as the
	 *     mystyle_sessions table.
	 *  2. Loops through all designs using php and copies the corresponding
	 *     session from the original table over to the temp table.
	 *  3. Renames the mystyle_sessions table to mystyle_sessions_trash.
	 *  4. Renames the mystyle_sessions_tmp table to mystyle_sessions.
	 *  5. Drops the mystyle_sessions_trash table.
	 *
	 * @global \wpdb $wpdb
	 */
	public static function purge_abandoned_sessions() {
		global $wpdb;

		$sessions_table_name     = MyStyle_Session::get_table_name();
		$tmp_sessions_table_name = $sessions_table_name . '_tmp';

		// ---------- STEP 1 ( Create temp session table ) -----------//
		// Get the schema of the session table.
		$session_table_schema = MyStyle_Session::get_schema();

		// Update the schema to append "_tmp" to the table name.
		$tmp_session_table_schema = str_replace(
			$sessions_table_name, // search.
			$tmp_sessions_table_name, // replace.
			$session_table_schema // target.
		);
		// Add any collation to the temp table schema.
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= " DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}
		$tmp_session_table_schema .= $collate . ';';

		// Create the temp table.
		// phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
		$wpdb->query( $tmp_session_table_schema );

		// ------ STEP 2 ( Copy Sessions with Designs to Temp Table ) ---------//
		$results = $wpdb->get_results(
			'SELECT session_id '
			. "FROM {$wpdb->prefix}mystyle_designs ",
			'OBJECT_K'
		);

		// Loop through all designs and add their.
		if ( null !== $results ) {
			foreach ( $results as $session_id => $value ) {
				// Add the design's session to the temp table.
				$wpdb->query(
					$wpdb->prepare(
						'INSERT IGNORE '
						. " INTO {$wpdb->prefix}mystyle_sessions_tmp "
						. ' SELECT s.* '
						. " FROM {$wpdb->prefix}mystyle_sessions s "
						. 'WHERE session_id = %s',
						$session_id
					)
				);
			}
		}

		// ------ STEP 3 (Rename the mystyle_sessions table) ---------//
		$wpdb->query( "RENAME TABLE {$wpdb->prefix}mystyle_sessions TO {$wpdb->prefix}mystyle_sessions_trash" );

		// ------ STEP 4 (Rename the mystyle_sessions_tmp table) ---------//
		$wpdb->query( "RENAME TABLE {$wpdb->prefix}mystyle_sessions_tmp TO {$wpdb->prefix}mystyle_sessions" );

		// ------ STEP 5 (Drop the mystyle_sessions_trash table) ---------//
		$wpdb->query( "DROP TABLE {$wpdb->prefix}mystyle_sessions_trash" );

	}

}
