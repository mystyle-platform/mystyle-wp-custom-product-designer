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
	 * Gets whether or not the passed user has authority to edit the passed
	 * design.
	 *
	 * @param MyStyle_Design $design The design to check.
	 * @param WP_User        $user (optional) The current user.
	 * @return boolean Returns true if the user has the authority to edit the
	 * passed design, otherwise, returns false.
	 */
	public static function can_user_edit( MyStyle_Design $design, WP_User $user ) {
		$authorized_caps = array(
			'administrator',
			'edit_posts',
			'manage_woocommerce',
		);

		if ( $design->get_user_id() === $user->ID ) {
			return true;
		} else {
			foreach ( $authorized_caps as $cap ) {
				if ( $user->has_cap( $cap ) ) {
					return true;
				}
			}
		}

		return false;
	}

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
		if ( null !== $design ) {
			if ( ! self::security_check( $design, $user, $session, $skip_security ) ) {
				$private_img_url = MYSTYLE_ASSETS_URL . 'images/private-design.jpg';
				$design->set_web_url( $private_img_url );
				$design->set_thumb_url( $private_img_url );
				$design->set_print_url( $private_img_url );
			}
		}

		// ------------ END SECURITY CHECK ------------ //
		return $design;
	}

	/**
	 * Checks the security rules to determine if access to the passed Design
	 * should be allowed.
	 *
	 * @param \MyStyle_Design       $design        The design being accessed.
	 * @param \WP_User              $user          The user that is trying to access
	 *                                             the design.
	 * @param \MyStyle_Session|null $session       The current session.
	 * @param boolean               $skip_security Whether or not to skip the
	 *                                             security check.
	 * @return boolean Returns true if access is allowed. Returns false if
	 * access is denied.
	 */
	public static function security_check(
		MyStyle_Design $design,
		WP_User $user = null,
		MyStyle_Session $session = null,
		bool $skip_security = false
	) {
		if ( ( null !== $design ) && ( ! $skip_security ) ) {
			if ( MyStyle_Access::ACCESS_PRIVATE === $design->get_access() ) {
				// Check if created by current/passed session.
				if ( // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
						( null !== $session ) &&
						( null !== $design->get_session_id() ) &&
						( $session->get_session_id() === 'design->get_session_id()' )
				) {
					// Design was created by the passed session, continue.
				} else {
					// Check for wp user match.
					if ( null !== $design->get_user_id() ) {
						if ( ( null === $user ) || ( 0 === $user->ID ) ) {
							return false;
						}
						if ( $design->get_user_id() !== $user->ID ) {
							if ( ! $user->has_cap( 'read_private_posts' ) ) {
								return false;
							}
						}
					}
				}
			}
		}

		return true;
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

		// Delete any Design Tags (terms).
		$terms = wp_get_object_terms( $design->get_design_id(), MYSTYLE_TAXONOMY_NAME );
		if ( ! empty( $terms ) ) {
			$term_ids = array();
			foreach ( $terms as $term ) {
				$term_ids[] = $term->term_id;
			}
			wp_remove_object_terms( $design->get_design_id(), $term_ids, MYSTYLE_TAXONOMY_NAME );
		}

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
	 * Sets the Design access, used by the design manager and design profile pages
	 *
	 * @deprecated Deprecated since 3.18.3. Use get and persist instead.
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
	 * @deprecated Deprecated since 3.18.3. Use get and persist instead.
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
	 * Retrieve designs from the database.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *
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
	 * Retrieves designs that were created by the passed user/author from the
	 * database.
	 *
	 * The designs are filtered for the passed user based on these rules:
	 *
	 *  * If the current_user is the author whose designs are being retrieved,
	 *    all designs are returned (regardless of whether they are
	 *    public/private/etc).
	 *  * If the passed user is different from the author whose designs are
	 *    being retrieved, only the author's public designs are returned.
	 *
	 * @param int     $per_page     The number of designs to show per
	 *                              page (default: 250).
	 * @param int     $page_number  The page number of the set of designs
	 *                              that you want to get (default: 1).
	 * @param WP_User $author       The design author/designer.
	 * @param WP_User $current_user The current user.
	 * @return array|null Returns an array of MyStyle_Design objects or null if
	 * none are found.
	 * @global \wpdb $wpdb;
	 */
	public static function get_user_designs(
		$per_page = 250,
		$page_number = 1,
		WP_User $author,
		WP_User $current_user
	) {
		global $wpdb;

		$access_clause = '';
		if ( $current_user->ID !== $author->ID ) {
			// Retrieving designs for a different user (return public designs
			// only).
			$access_clause = ' AND ms_access = ' . MyStyle_Access::ACCESS_PUBLIC;
		}

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * '
				. "FROM {$wpdb->prefix}mystyle_designs
				WHERE user_id = %s"
				. $access_clause
				. ' ORDER BY ms_design_id DESC
				LIMIT %d
				OFFSET %d',
				array(
					$author->ID,
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
	 *
	 *  * If no user is specified, only public designs are returned.
	 *  * If the passed user is an admin (or has the 'read_private_posts'
	 *    capability, all designs are returned).
	 *  * If the passed user is a regular user, all public designs are returned
	 *    along with any private designs that the user owns.
	 *
	 * @param int     $count The number of designs to return (default: 250).
	 * @param WP_User $user (optional) The current user.
	 * @global \wpdb $wpdb
	 * @return mixed Returns an array of MyStyle_Design objects or null if none
	 * are found.
	 */
	public static function get_random_designs(
		$count = 250,
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
				LIMIT %d',
				array( $count )
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
	 * Retrieve Designs grouped by term. Only includes public Designs and terms
	 * that are used by at least one public Design.
	 *
	 * @param string      $taxonomy         The term taxonomy to use.
	 * @param int|null    $terms_per_page   The number of terms to show per page
	 *                                      (default: 250).
	 * @param int|null    $page_number      The page number of the set of designs
	 *                                 that you want to get (default: 1).
	 * @param int|null    $designs_per_term The number of designs to show per term.
	 * @param string|null $order_by         What to order the results by (can be
	 *                                      either "name" or "count"). Default is
	 *                                      "name".
	 * @param string|null $order_direction  The direction to order the results in.
	 *                                      Can be either "ASC" (Ascending) or "DESC"
	 *                                      (Descending). Default is "ASC".
	 * @return array Returns a two dimensional array structured as:
	 * `["term_slug" => ["term" => WP_Term, "designs" => MyStyle_Design[]]]`.
	 * and an array of Designs.
	 * @throws \InvalidArgumentException Throws an InvalidArgumentException if
	 * the passed order_direction isn't one of "ASC", "DESC".
	 * @global \wpdb $wpdb
	 */
	public static function get_designs_by_term(
		string $taxonomy,
		$terms_per_page = 250,
		$page_number = 1,
		$designs_per_term = 5,
		$order_by = 'name',
		$order_direction = 'ASC'
	) {
		global $wpdb;

		// Validate the order_direction argument.
		if ( ! in_array( $order_direction, array( 'ASC', 'DESC' ), true ) ) {
			throw new \InvalidArgumentException(
				'Invalid order_direction param "' . $order_direction . '".'
			);
		}

		// Get the terms.
		$terms = MyStyle_Design_Term_Manager::get_terms(
			$taxonomy,
			$terms_per_page,
			$page_number,
			$order_by,
			$order_direction
		);

		$offset = ( $page_number - 1 ) * $terms_per_page;

		// Build the 'IN' statement.
		$in_arr = array();
		foreach ( $terms as $term ) {
			$in_arr[] = $term->term_taxonomy_id;
		}
		if ( 0 === count( $in_arr ) ) {
			return array();
		}
		$in_str = implode( ',', $in_arr );

		// Query the db for the designs.
		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			'SELECT d.*, tt.term_taxonomy_id, tt.taxonomy, t.term_id, t.name, t.slug '
			. "FROM {$wpdb->prefix}term_relationships r "
			. "LEFT JOIN {$wpdb->prefix}mystyle_designs d ON (r.object_id = d.ms_design_id) "
			. "LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON (r.term_taxonomy_id = tt.term_taxonomy_id) "
			. "LEFT JOIN {$wpdb->prefix}terms t ON (t.term_id = tt.term_id) "
			. "WHERE tt.term_taxonomy_id IN ({$in_str}) "
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

		// Add the Terms to the return array.
		$term_designs = array();
		foreach ( $terms as $term ) {
			$slug                  = $term->slug;
			$term_designs[ $slug ] = array(
				'term'    => $term,
				'designs' => array(),
			);
		}

		// Add the Designs to the return array.
		foreach ( $results as $result ) {
			$slug = $result->slug;
			if ( count( $term_designs[ $slug ]['designs'] ) < $designs_per_term ) {
				$term_designs[ $slug ]['designs'][]
					= MyStyle_Design::create_from_result_object( $result );
			}
		}

		return $term_designs;
	}

	/**
	 * Retrieve designs by term id.
	 *
	 * @param int                   $term_taxonomy_id The term taxonomy id.
	 * @param \WP_User|null         $user             The current user.
	 * @param \MyStyle_Session|null $session          The current MyStyle session.
	 * @param int                   $per_page         The number of designs to show
	 *                                                per page (default: 250).
	 * @param int                   $page_number      The page number of the set of
	 *                                                designs that you want to get
	 *                                                (default: 1).
	 * @global \wpdb $wpdb
	 * @return mixed Returns an array of MyStyle_Design objects or null if none
	 * are found.
	 */
	public static function get_designs_by_term_taxonomy_id(
		$term_taxonomy_id,
		WP_User $user = null,
		MyStyle_Session $session = null,
		$per_page = 250,
		$page_number = 1
	) {
		global $wpdb;

		$term_relationships = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT object_id '
				. "FROM {$wpdb->prefix}term_relationships "
				. 'WHERE term_taxonomy_id = %d
				LIMIT %d
				OFFSET %d',
				array(
					$term_taxonomy_id,
					$per_page,
					( $page_number - 1 ) * $per_page,
				)
			)
		);

		$designs = array();

		foreach ( $term_relationships as $term_relationship ) {
			try {
				$design = self::get( $term_relationship->object_id, $user, $session );

				if (
					( null !== $design )
					&& ( self::security_check( $design, $user, $session ) )
				) {
					array_push( $designs, $design );
				}
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
		$where .= self::get_security_where_clause( 'WHERE', $user );

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$count = intval(
			$wpdb->get_var(
				'SELECT COUNT(ms_design_id) '
				. "FROM {$wpdb->prefix}mystyle_designs "
				. $where
			)
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

		return $count;
	}

	/**
	 * Retrieve the total number of designs for the passed author/designer. If
	 * the passed current user isn't the author, only the public designs are
	 * included in the count.
	 *
	 * @param WP_User $author       The design author/designer.
	 * @param WP_User $current_user The current user.
	 * @return integer Returns the number of designs.
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function get_total_user_design_count(
		WP_User $author,
		WP_User $current_user
	) {
		global $wpdb;

		$access_clause = '';
		if ( $current_user->ID !== $author->ID ) {
			// Retrieving designs for a different user (return public designs
			// only).
			$access_clause = ' AND ms_access = ' . MyStyle_Access::ACCESS_PUBLIC;
		}

		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$count = intval(
			$wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(ms_design_id) '
					. "FROM {$wpdb->prefix}mystyle_designs
					WHERE user_id = %s"
					. $access_clause,
					array(
						$author->ID,
					)
				)
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
	public static function is_user_design_owner( $user_id, $design_id ) {
		global $wpdb;

		$ret = false;

		$design_user_id = intval(
			$wpdb->get_var(
				$wpdb->prepare(
					'SELECT user_id '
					. "FROM {$wpdb->prefix}mystyle_designs "
					. 'WHERE ms_design_id = %d',
					array( $design_id )
				)
			)
		);

		if ( intval( $design_user_id ) === $user_id ) {
			$ret = true;
		}

		return $ret;
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
