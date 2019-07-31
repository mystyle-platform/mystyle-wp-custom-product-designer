<?php
/**
 * The MyStyle_DesignManager class has functions for managing MyStyle_Design
 * entities.
 *
 * @package MyStyle
 * @since 0.5
 */

/**
 * MyStyle_DesignManager class.
 */
abstract class MyStyle_DesignManager extends \MyStyle_EntityManager {

	/**
	 * Get the design from the database.
	 *
	 * @global wpdb $wpdb
	 * @param integer         $design_id The design id.
	 * @param WP_User         $user (optional) The current user.
	 * @param MyStyle_Session $session The user's MyStyle_Session.
	 * @return \MyStyle_Design|null Returns the MyStyle_Design entity or null
	 * if the design can't be found.
	 * @throws MyStyle_Forbidden_Exception Throws a MyStyle_Forbidden_Exception
	 * if the requested design is marked as private and the user isn't logged
	 * in.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the design is marked as private and the
	 * the passed user is not the owner of the design and the user doesn't have
	 * 'read_private_posts' capability.
	 */
	public static function get(
		$design_id,
		WP_User $user = null,
		MyStyle_Session $session = null
	) {
		global $wpdb;

		$design = null;

		$query = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ' .
				'WHERE ' . MyStyle_Design::get_primary_key() . ' = ' . $design_id;

		$result_object = $wpdb->get_row( $query );

		if ( null !== $result_object ) {
			$design = MyStyle_Design::create_from_result_object( $result_object );
		}

		if ( null !== $design ) {
			// Security Check (throws exception if access not permitted).
			self::security_check( $design, $user, $session );
		}

		return $design;
	}

	/**
	 * Get the design from the database using the legacy_design_id.
	 *
	 * @global wpdb $wpdb
	 * @param integer         $legacy_design_id The legacy design id.
	 * @param WP_User         $user (optional) The current user.
	 * @param MyStyle_Session $session The user's MyStyle_Session.
	 * @return \MyStyle_Design|null Returns the MyStyle_Design entity or null
	 * if the design can't be found.
	 * @throws MyStyle_Forbidden_Exception Throws a MyStyle_Forbidden_Exception
	 * if the requested design is marked as private and the user isn't logged
	 * in.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the design is marked as private and the
	 * the passed user is not the owner of the design and the user doesn't have
	 * 'read_private_posts' capability.
	 */
	public static function get_by_legacy_design_id(
		$legacy_design_id,
		WP_User $user = null,
		MyStyle_Session $session = null
	) {
		global $wpdb;

		$design = null;

		$query = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ' .
				'WHERE legacy_design_id ' . ' = ' . $legacy_design_id;

		$result_object = $wpdb->get_row( $query );

		if ( null !== $result_object ) {
			$design = MyStyle_Design::create_from_result_object( $result_object );
		}

		// Security Check (throws exception if access not permitted).
		if ( null !== $design ) {
			self::security_check( $design, $user, $session );
		}

		return $design;
	}

	/**
	 * Deletes the passed design from the database.
	 *
	 * @global \wpdb $wpdb
	 * @param MyStyle_Design $design The design that you want to delete.
	 * @return boolean Returns true is the Design was successfully deleted,
	 * otherwise, returns false.
	 */
	public static function delete( MyStyle_Design $design ) {
		global $wpdb;

		$ret = $wpdb->delete(
			MyStyle_Design::get_table_name(),
			array( MyStyle_Design::get_primary_key() => $design->get_design_id() ),
			array( '%d' )
		);

		$deleted = ( false !== $ret );

		return $deleted;
	}

	/**
	 * Get the previous design from the database.
	 *
	 * @global wpdb $wpdb
	 * @param int     $current_design_id The design_id that you want to use as
	 * the base for retrieving the previous design.
	 * @param WP_User $user (optional) The current user.
	 * @return \MyStyle_Design|null Returns the previous MyStyle_Design or null if
	 * there isn't one.
	 */
	public static function get_previous_design(
		$current_design_id,
		WP_User $user = null
	) {
		global $wpdb;

		$design = null;

		$select = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ';
		$where  = 'WHERE ' . MyStyle_Design::get_primary_key() . ' < ' . $current_design_id . ' ';

		// Add security WHERE (AND) clause.
		$where .= self::getSecurityWhereClause( 'AND', $user );

		$order = 'ORDER BY ' . MyStyle_Design::get_primary_key() . ' DESC ';

		$limit = 'LIMIT 1 ';

		$query = $select . $where . $order . $limit;

		$result_object = $wpdb->get_row( $query );

		if ( null !== $result_object ) {
			$design = MyStyle_Design::create_from_result_object( $result_object );
		}

		return $design;
	}

	/**
	 * Get the next design from the database.
	 *
	 * @global wpdb $wpdb
	 * @param int     $current_design_id The design_id that you want to use as
	 * the base for retrieving the next design.
	 * @param WP_User $user (optional) The current user.
	 * @return \MyStyle_Design|null Returns the next MyStyle_Design or null if
	 * there isn't one.
	 */
	public static function get_next_design(
		$current_design_id,
		WP_User $user = null
	) {
		global $wpdb;

		$design = null;

		$select = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ';
		$where  = 'WHERE ' . MyStyle_Design::get_primary_key() . ' > ' . $current_design_id . ' ';

		// Add security WHERE (AND) clause.
		$where .= self::getSecurityWhereClause( 'AND', $user );

		$limit = 'LIMIT 1 ';

		$query = $select . $where . $limit;

		$result_object = $wpdb->get_row( $query );

		if ( null !== $result_object ) {
			$design = MyStyle_Design::create_from_result_object( $result_object );
		}

		return $design;
	}

	/**
	 * Sets the user_id on designs where it is null (or 0) using the email and
	 * the session.
	 *
	 * @global wpdb $wpdb
	 * @param WP_User                    $user The user.
	 * @param MyStyle_Session (optional) $session The current user session.
	 * @return integer Returns the number or designs that were updated or false
	 * if no rows were updated.
	 */
	public static function set_user_id( $user, $session = null ) {
		global $wpdb;

		$query  = 'UPDATE ' . MyStyle_Design::get_table_name() . ' ' .
				'SET user_id = "' . $user->ID . '" ' .
				'WHERE ( ( user_id IS NULL ) OR ( user_id = 0 ) ) ';
		$query .= 'AND ( ';

		if ( ! empty( $user->user_email ) ) {
			// Where email matches and the session is empty or matches the passed session id.
			$query .= ' ( ms_email = "' . $user->user_email . '" )';
			$query .= 'AND ( ';
			if ( null !== $session ) {
				$query .= ' ( session_id = "' . $session->get_session_id() . '" ) OR ';
			}
			$query .= ' ( session_id IS NULL ) OR ( session_id = "" ) ';
			$query .= ' ) ';
		} else {
			// If the user doesn't have an email address, try to match based on the session id.
			if ( null !== $session ) {
				$query .= ' ( session_id = "' . $session->get_session_id() . '" ) ';
			}
		}

		// If the design doesn't have an email set, try to macth just based on the session id.
		$query .= ') OR (ms_email IS NULL AND session_id = "' . $session->get_session_id() . '" ) ';

		$result = $wpdb->query( $query );

		return $result;
	}

	/**
	 * Retrieve designs from the database.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *  * If no user is specified, only public designs are returned.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capablility, all designs are returned).
	 *  * If the passed user is a regular user, all public designs are returned
	 *    allong with any private designs that the user owns.
	 *
	 * @param int     $per_page The number of designs to show per page (default:
	 * 250).
	 * @param int     $page_number The page number of the set of designs that you
	 * want to get (default: 1).
	 * @param WP_User $user (optional) The current user.
	 * @global $wpdb;
	 * @return mixed Returns an array of MyStyle_Design objects or null if none
	 * are found.
	 */
	public static function get_designs(
		$per_page = 250,
		$page_number = 1,
		WP_User $user = null
	) {
		global $wpdb;

		$sql = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ';

		// Add security WHERE clause.
		$sql .= self::getSecurityWhereClause( 'WHERE', $user );

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY ms_design_id DESC';
		}

		$sql .= " LIMIT $per_page";

		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$results = $wpdb->get_results( $sql, 'OBJECT' );

		// Transform the result objects (stdClass) into MyStyle_Designs.
		$designs = null;
		if ( null !== $results ) {
			$designs = array();
			foreach ( $results as $result ) {
				$design = MyStyle_Design::create_from_result_object( $result );
				array_push( $designs, $design );
			}
		}

		return $designs;
	}

	/**
	 * Retrieve the total number of designs (filtered by security rules) from
	 * the db.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *  * If no user is specified, only public designs are counted.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capablility, all designs are counted).
	 *  * If the passed user is a regular user, all public designs are counted
	 *    allong with any private designs that the user owns.
	 *
	 * @param WP_User $user (optional) The current user.
	 * @global $wpdb
	 * @return integer
	 */
	public static function get_total_design_count( WP_User $user = null ) {
		global $wpdb;

		$sql = 'SELECT COUNT(' . MyStyle_Design::get_primary_key() . ') ' .
				'FROM ' . MyStyle_Design::get_table_name();

		// Add security WHERE clause.
		$sql .= self::getSecurityWhereClause( 'WHERE', $user );

		$count = $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * Helper method that returns the security WHERE clause ( EX: ' WHERE
	 * ms_access = 0 ').
	 *
	 * @param string  $exp The expression to use ('WHERE' or 'AND').
	 * @param WP_User $user The current user.
	 * @return Returns a WHERE clause for adding security to the design lookups.
	 * @todo This should really be private but this is an abstract class. We
	 * should probably make it into a singleton instead.
	 */
	public static function getSecurityWhereClause( $exp, WP_User $user = null ) {
		$sql = '';

		// Note: admin (and users with the read_private_posts capability) see all designs.
		if ( ( null === $user ) || ( 0 === $user->ID ) ) {
			// No user, get public designs only.
			$sql = ' ' . $exp . ' ms_access = 0 ';
		} else {
			// User was passed.
			if ( ! $user->has_cap( 'read_private_posts' ) ) {
				// User isn't admin, show public and their own private designs.
				$sql .= ' ' . $exp . ' ( ms_access = 0 OR ( ( ms_access = 1 ) AND ( user_id = ' . $user->ID . ' ) ) ) ';
			}
		}

		return $sql;
	}

	/**
	 * Helper method that does a security (authorization) check on the passed
	 * design.
	 *
	 * @global wpdb $wpdb
	 * @param MyStyle_Design  $design The Design that we want to check.
	 * @param WP_User         $user (optional) The current user.
	 * @param MyStyle_Session $session The user's MyStyle_Session.
	 * @return \MyStyle_Design|null Returns the MyStyle_Design entity or null
	 * if the design can't be found.
	 * @throws MyStyle_Forbidden_Exception Throws a MyStyle_Forbidden_Exception
	 * if the requested design is marked as private and the user isn't logged
	 * in.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the design is marked as private and the
	 * the passed user is not the owner of the design and the user doesn't have
	 * 'read_private_posts' capability.
	 */
	public static function security_check(
		MyStyle_Design $design,
		WP_User $user = null,
		MyStyle_Session $session = null
	) {

		// -------------- SECURITY CHECK ------------ //
		if ( null !== $design ) {
			if ( $design->get_access() === MyStyle_Access::ACCESS_PRIVATE ) {
				// Check if created by current/passed session.
				if (
						( null !== $session ) &&
						( null !== $design->get_session_id() ) &&
						( $session->get_session_id() === $design->get_session_id() )
				) {
					// Design was created by the passed session, continue.
				} else {
					// Check for wp user match.
					if ( null !== $design->get_user_id() ) {
						if ( ( null === $user ) || ( 0 === $user->ID ) ) {
							throw new MyStyle_Unauthorized_Exception( 'This design is private, you must log in to view it.' );
						}
						if ( $design->get_user_id() !== $user->ID ) {
							if ( ! $user->has_cap( 'read_private_posts' ) ) {
								throw new MyStyle_Forbidden_Exception( 'You are not authorized to access this design.' );
							}
						}
					}
				}
			}
		}

	}

}
