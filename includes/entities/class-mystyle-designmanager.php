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
	 * @param boolean         $skip_security Set to true to skip the security
	 *                                       check (default false).
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
		MyStyle_Session $session = null,
		$skip_security = false
	) {
		global $wpdb;

		$design = null;

		$result_object = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT *'
				. "FROM {$wpdb->prefix}mystyle_designs "
				. 'WHERE ms_design_id = %d',
				$design_id
			)
		);

		if ( null !== $result_object ) {
			$design = MyStyle_Design::create_from_result_object( $result_object );
		}

		// -------------- SECURITY CHECK ------------ //
		if ( ( null !== $design ) && ( ! $skip_security ) ) {
			if ( MyStyle_Access::ACCESS_PRIVATE === $design->get_access() ) {
				// Check if created by current/passed session.
				if ( // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
						( null !== $session ) &&
						( null !== $design->get_session_id() ) &&
						( $session->get_session_id() === $design->get_session_id() )
				) {
					// Design was created by the passed session, continue.
				} else {
					if ( ( ! current_user_can( 'administrator' ) ) ) {

						if ( null !== $design->get_user_id() ) {

							if ( ( null === $user ) || ( 0 === $user->ID ) ) {
								return false;
								// throw new MyStyle_Unauthorized_Exception( 'This design is private, you must log in to view it.' );
							}
							if ( $design->get_user_id() !== $user->ID ) {
								if ( ( ! $user->has_cap( 'read_private_posts' ) ) || ( ! is_admin() ) ) {
									return false;
									// throw new MyStyle_Forbidden_Exception( 'You are not authorized to access this design.' );
								}
							}
						}
					} else {
						return $design;
					}
				}
			}
		}
		// ------------ END SECURITY CHECK ------------ //
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
	 * @param int     $current_design_id The design_id that you want to use as
	 * the base for retrieving the previous design.
	 * @param WP_User $user (optional) The current user.
	 * @return \MyStyle_Design|null Returns the previous MyStyle_Design or null if
	 * there isn't one.
	 * @global wpdb $wpdb
	 */
	public static function get_previous_design(
		$current_design_id,
		WP_User $user = null
	) {
		global $wpdb;

		$design = null;

		$security_where_clause = self::get_security_where_clause( 'AND', $user );

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$result_object = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. 'WHERE ms_design_id < %d'
				. $security_where_clause .
				'ORDER BY ms_design_id DESC
				LIMIT 1',
				$current_design_id
			)
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

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

		$security_where_clause = self::get_security_where_clause( 'AND', $user );

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$result_object = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. 'WHERE ms_design_id > %d'
				. $security_where_clause .
				'LIMIT 1',
				$current_design_id
			)
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

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

		$query_vars = array();

		$query        = "UPDATE {$wpdb->prefix}mystyle_designs "
				. 'SET user_id = %d
				WHERE ( ( user_id IS NULL ) OR ( user_id = 0 ) ) ';
		$query_vars[] = $user->ID;

		$query .= 'AND ( ';

		if ( ! empty( $user->user_email ) ) {
			// Where email matches and the session is empty or matches the passed session id.
			$query       .= ' ( ms_email = %s )';
			$query_vars[] = $user->user_email;
			$query       .= 'AND ( ';
			if ( null !== $session ) {
				$query       .= ' ( session_id = %s ) OR ';
				$query_vars[] = $session->get_session_id();
			}
			$query .= ' ( session_id IS NULL ) OR ( session_id = \'\' ) ';
			$query .= ' ) ';
		} else {
			// If the user doesn't have an email address, try to match based on the session id.
			if ( null !== $session ) {
				$query       .= ' ( session_id = %s ) ';
				$query_vars[] = $session->get_session_id();
			}
		}

		// If the design doesn't have an email set, try to macth just based on the session id.
		$query       .= ') OR (ms_email IS NULL AND session_id = %s ) ';
		$query_vars[] = $session->get_session_id();

		$result_object = $wpdb->query( $wpdb->prepare( $query, $query_vars ) ); // phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared

		return $result_object;
	}

	/**
	 * Sets the Design title.
	 *
	 * @param int    $design_id The design_id of the design that you want to set
	 * the title of.
	 * @param string $title     The new title.
	 * @return integer Returns the number or designs that were updated or false
	 * if no rows were updated.
	 * @global wpdb $wpdb
	 */
	public static function set_title( $design_id, $title ) {
		global $wpdb;

		$where = array(
			MyStyle_Design::get_primary_key() => $design_id,
			'user_id'                         => get_current_user_id(),
		);

		if ( current_user_can( 'administrator' ) ) {
			$where = array( MyStyle_Design::get_primary_key() => $design_id );
		}

		$result = $wpdb->update(
			MyStyle_Design::get_table_name(),
			array( 'ms_title' => $title ),
			$where
		);

		return $result;
	}

	/**
	 * Sets the Design access.
	 *
	 * @param int $design_id The design_id of the design that you want to set
	 * the access of.
	 * @param int $access    The new access visibility (1,2,3, etc). See the
	 * MyStyle_Design class for valid values and what they do.
	 * @return int Returns the number or designs that were updated or false
	 * if no rows were updated.
	 * @global wpdb $wpdb
	 */
	public static function set_access( $design_id, $access ) {
		global $wpdb;

		$where = array(
			MyStyle_Design::get_primary_key() => $design_id,
			'user_id'                         => get_current_user_id(),
		);

		if ( current_user_can( 'administrator' ) ) {
			$where = array( MyStyle_Design::get_primary_key() => $design_id );
		}

		$result = $wpdb->update(
			MyStyle_Design::get_table_name(),
			array( 'ms_access' => $access ),
			$where
		);

		return $result;
	}

	/**
	 * Retrieve designs from the database.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *  * If no user is specified, only public designs are returned.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capability, all designs are returned).
	 *  * If the passed user is a regular user, all public designs are returned
	 *    along with any private designs that the user owns.
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

		// Add security WHERE clause.
		$where = self::get_security_where_clause( 'WHERE', $user );

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( ! empty( $_GET['orderby'] ) ) {
			$order  = ' ORDER BY ' . sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			$order .= ! empty( $_GET['order'] ) ? ' ' . sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ' ASC';
		} else {
			$order = ' ORDER BY ms_design_id DESC';
		}
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. $where
				. $order
				. ' LIMIT %d
				OFFSET %d',
				array(
					$per_page,
					( $page_number - 1 ) * $per_page,
				)
			),
			'OBJECT'
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

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
	 * Retrieve user designs from the database.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *  * If no user is specified, only public designs are returned.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capability, all designs are returned).
	 *  * If the passed user is a regular user, all public designs are returned
	 *    along with any private designs that the user owns.
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
	public static function get_user_designs(
		$per_page = 250,
		$page_number = 1,
		$user
	) {
		global $wpdb;

		$sql = '';

		if ( is_string( $user ) ) {
			$sql .= ' WHERE (ms_email = "' . $user . '") AND ms_access = ' . MyStyle_Access::ACCESS_PUBLIC;
		} else {
			$current_user_id = get_current_user_id();
			if ( $current_user_id === $user->ID ) {
				$sql .= ' WHERE (user_id = ' . $user->ID . ') ';
			} else {
				$sql .= ' WHERE (user_id = ' . $user->ID . ') AND ms_access = ' . MyStyle_Access::ACCESS_PUBLIC;
			}
		}

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( ! empty( $_GET['orderby'] ) ) {
			$sql .= ' ORDER BY ' . sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			$sql .= ! empty( $_GET['order'] ) ? ' ' . sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ' ASC';
		} else {
			$sql .= ' ORDER BY ms_design_id DESC';
		}
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. $sql
				. ' LIMIT %d
				OFFSET %d',
				array(
					$per_page,
					( $page_number - 1 ) * $per_page,
				)
			),
			'OBJECT'
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

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
	 * Retrieve random designs from the database.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *  * If no user is specified, only public designs are returned.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capability, all designs are returned).
	 *  * If the passed user is a regular user, all public designs are returned
	 *    along with any private designs that the user owns.
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
	public static function get_random_designs(
		$per_page = 250,
		$page_number = 1,
		WP_User $user = null
	) {
		global $wpdb;

		$where = '';

		// Add security WHERE clause.
		if ( null !== $user ) {
			$where .= self::get_security_where_clause( 'WHERE', $user );
		}

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. $where
				. ' ORDER BY RAND()
				LIMIT %d
				OFFSET %d',
				array(
					$per_page,
					( $page_number - 1 ) * $per_page,
				)
			),
			'OBJECT'
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

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
	 * Retrieve designs by term id.
	 *
	 * @param int                   $term_id     The id of the term.
	 * @param \WP_User|null         $user        The current user.
	 * @param \MyStyle_Session|null $session     The current MyStyle session.
	 * @param int                   $per_page    The number of designs to show
	 *                                           per page (default: 250).
	 * @param int                   $page_number The page number of the set of
	 *                                           designs that you want to get
	 *                                           (default: 1).
	 * @global $wpdb;
	 * @return mixed Returns an array of MyStyle_Design objects or null if none
	 * are found.
	 */
	public static function get_designs_by_term_id(
		$term_id,
		WP_User $user = null,
		MyStyle_Session $session = null,
		$per_page = 250,
		$page_number = 1
	) {
		global $wpdb;

		$terms = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT object_id '
				. "FROM {$wpdb->prefix}term_relationships "
				. 'WHERE term_taxonomy_id = %d
				LIMIT %d
				OFFSET %d',
				array(
					$term_id,
					$per_page,
					( $page_number - 1 ) * $per_page,
				)
			)
		);

		$designs = array();

		foreach ( $terms as $term ) {
			try {

				$design = MyStyle_DesignManager::get( $term->object_id, $user, $session );

				array_push( $designs, $design );

			} catch ( MyStyle_Unauthorized_Exception $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// If unauthorized, skip and continue on to the next one.
			}
		}

		return $designs;
	}

	/**
	 * Retrieve the total number of designs (filtered by security rules) from
	 * the db.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *
	 *  * If no user is specified, only public designs are counted.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capability, all designs are counted).
	 *  * If the passed user is a regular user, all public designs are counted
	 *    along with any private designs that the user owns.
	 *
	 * @param WP_User $user (optional) The current user.
	 * @global $wpdb
	 * @return integer
	 */
	public static function get_total_design_count( WP_User $user = null ) {
		global $wpdb;

		$where = '';

		// Add security WHERE clause.
		if ( null !== $user ) {
			$where .= self::get_security_where_clause( 'WHERE', $user );
		}

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$count = $wpdb->get_var(
			'SELECT COUNT(ms_design_id) '
			. "FROM {$wpdb->prefix}mystyle_designs "
			. $where
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

		return $count;
	}

	/**
	 * Retrieve the total number of user designs (filtered by WP_user->ID or email string) from
	 * the db.
	 *
	 * @param mixed    $user   The current user. Either WP_User OR user email
	 *                         string.
	 * @param int|null $access (optional) Design Access.
	 * @return integer
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function get_total_user_design_count( $user, $access = null ) {
		global $wpdb;

		if ( null === $access ) {
			$access = MyStyle_Access::ACCESS_PUBLIC;
		}

		$where = ' WHERE ms_access = ' . $access;

		if ( is_string( $user ) ) {
			$where .= ' AND ms_email = ' . $user;
		} else {
			$where .= ' AND user_id = ' . $user->ID;
		}

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(ms_design_id) '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. $where
			)
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

		return $count;
	}

	/**
	 * Determines if the user owns the design using the user id and design id.
	 *
	 * @param int $user_id   The WordPress user id.
	 * @param int $design_id The MyStyle design id.
	 * @return bool Returns true if the user owns the design, otherwise, returns
	 * false.
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * @todo Add unit testing for this method.
	 */
	public static function does_user_own_design( $user_id, $design_id ) {
		global $wpdb;

		$ret = false;

		$design_user_id = $wpdb->get_var(
			'SELECT user_id '
			. "FROM {$wpdb->prefix}mystyle_designs "
			. 'WHERE ms_design_id = %d',
			array( $design_id )
		);

		if ( $design_user_id === $user_id ) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Retrieve the total number of terms.
	 *
	 * @param int $term_id The term id.
	 * @global $wpdb
	 * @return integer Returns the total number of terms.
	 */
	public static function get_total_term_count( $term_id ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(object_id) '
				. "FROM {$wpdb->prefix}term_relationships "
				. 'WHERE term_taxonomy_id = %d',
				array( $term_id )
			)
		);

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
	public static function get_security_where_clause( $exp, WP_User $user = null ) {
		$sql = '';

		// Note: admin (and users with the read_private_posts capability) see all designs.
		if ( ( null === $user ) || ( 0 === $user->ID ) ) {
			// No user, get public designs only.
			$sql = ' ' . $exp . ' ms_access = ' . MyStyle_Access::ACCESS_PUBLIC . ' ';
		} else {
			// User was passed.
			if ( ! $user->has_cap( 'read_private_posts' ) ) {
				// User isn't admin, show public and their own private or hidden designs.
				$sql .= ' ' . $exp
						. ' ( '
							. ' ( ms_access = ' . MyStyle_Access::ACCESS_PUBLIC . ' ) OR '
							. ' ( ( ms_access = ' . MyStyle_Access::ACCESS_PRIVATE . ' ) AND ( user_id = ' . $user->ID . ' ) ) OR '
							. ' ( ( ms_access = ' . MyStyle_Access::ACCESS_HIDDEN . ' ) AND ( user_id = ' . $user->ID . ' ) ) '
						. ' ) ';
			} else {
				// Show all designs to admin user.
				$sql .= ' ' . $exp
						. ' ( '
							. ' ( ms_access = ' . MyStyle_Access::ACCESS_PUBLIC . ' ) OR '
							. ' ( ms_access = ' . MyStyle_Access::ACCESS_PRIVATE . ' )  OR '
							. ' ( ms_access = ' . MyStyle_Access::ACCESS_HIDDEN . ' ) '
						. ' ) ';
			}
		}

		return $sql;
	}


}
