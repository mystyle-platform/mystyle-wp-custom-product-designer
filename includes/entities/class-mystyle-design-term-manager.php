<?php
/**
 * The MyStyle_Design_Term_Manager class has functions for managing
 * MyStyle_Design Terms.
 *
 * @package MyStyle
 * @since 4.0.0
 */

/**
 * MyStyle_Design_Term_Manager class.
 */
abstract class MyStyle_Design_Term_Manager extends \MyStyle_EntityManager {

	/**
	 * Retrieve a list of Design Terms. Only includes terms that are used by at
	 * least one public Design.
	 *
	 * @param string      $taxonomy        The term taxonomy to use.
	 * @param int|null    $per_page        The number of terms to show per
	 *                                     page (default: 250).
	 * @param int|null    $page_number     The page number of the set of terms
	 *                                     that you want to get (default: 1).
	 * @param string|null $order_by        What to order the results by (can be
	 *                                     either "name" or "count"). Default is
	 *                                     "name".
	 * @param string|null $order_direction The direction to order the results
	 *                                     in. Can be either "ASC" (Ascending)
	 *                                     or "DESC" (Descending). Default is
	 *                                     "ASC".
	 * @return WP_Term[] Returns an array of WP_Terms.
	 * @global \wpdb $wpdb
	 * @throws \InvalidArgumentException Throws an InvalidArgumentException if
	 * passed invalid order params.
	 * @todo Add unit testing.
	 */
	protected static function get_terms(
		$taxonomy,
		$per_page = 250,
		$page_number = 1,
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

		$offset          = ( $page_number - 1 ) * $per_page;
		$order_by_clause = 'ORDER BY ';
		if ( 'name' === $order_by ) {
			$order_by_clause .= 't.name';
		} elseif ( 'count' === $order_by ) {
			$order_by_clause .= '`count`';
		} else {
			throw new \InvalidArgumentException(
				'Invalid/Unsupported order_by param "' . $order_by . '".'
			);
		}
		$order_by_clause .= ' ' . $order_direction;

		// Get the terms from the database.
		// phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		$term_results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT tt.term_taxonomy_id, tt.taxonomy, t.term_id, t.name, t.slug, COUNT(t.term_id) AS `count` '
				. "FROM {$wpdb->prefix}mystyle_designs d "
				. "LEFT JOIN {$wpdb->prefix}term_relationships r ON (r.object_id = d.ms_design_id) "
				. "LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON (r.term_taxonomy_id = tt.term_taxonomy_id) "
				. "LEFT JOIN {$wpdb->prefix}terms t ON (t.term_id = tt.term_id) "
				. 'WHERE tt.taxonomy = %s
				AND d.ms_access = 0
				GROUP BY tt.term_taxonomy_id '
				. "{$order_by_clause} "
				. 'LIMIT %d
				OFFSET %d',
				array(
					$taxonomy,
					$per_page,
					$offset,
				)
			),
			'OBJECT'
		);
		// phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

		// Build the array of WP_Term objects.
		$terms = array();
		foreach ( $term_results as $term_result ) {
			$term                 = new WP_Term( $term_result );
			$terms[ $term->slug ] = $term;
		}

		return $terms;
	}

	/**
	 * Gets the total number of Design terms. Only includes terms that are used
	 * by at least one public Design. This is needed for paging through the
	 * terms returned by the get_terms method above.
	 *
	 * @param string $taxonomy The term taxonomy to use.
	 * @return int Returns the total number of terms.
	 * @global \wpdb $wpdb
	 * @todo Add unit testing.
	 */
	protected static function get_terms_count( $taxonomy ) {
		global $wpdb;

		$count = intval(
			$wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*)
				FROM (SELECT 1 as term '
					. "FROM {$wpdb->prefix}mystyle_designs d "
					. "LEFT JOIN {$wpdb->prefix}term_relationships r ON (r.object_id = d.ms_design_id) "
					. "LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON (r.term_taxonomy_id = tt.term_taxonomy_id) "
					. 'WHERE tt.taxonomy = %s
				AND d.ms_access = 0
				GROUP BY tt.term_taxonomy_id
				) t',
					array( $taxonomy )
				)
			)
		);

		return $count;
	}

	/**
	 * Retrieve Designs grouped by term. Only includes public Designs and terms
	 * that are used by at least one public Design.
	 *
	 * @param string      $taxonomy         The term taxonomy to use.
	 * @param int|null    $terms_per_page   The number of terms to show per page
	 *                                      (default: 250).
	 * @param int|null    $page_number      The page number of the set of
	 *                                      designs that you want to get
	 *                                      (default: 1).
	 * @param int|null    $designs_per_term The number of designs to show per
	 *                                      term.
	 * @param string|null $order_by         What to order the results by (can be
	 *                                      either "name" or "count"). Default
	 *                                      is "name".
	 * @param string|null $order_direction  The direction to order the results
	 *                                      in. Can be either "ASC" (Ascending)
	 *                                      or "DESC" (Descending). Default is
	 *                                      "ASC".
	 * @return array Returns a two dimensional array structured as:
	 * `["term_slug" => ["term" => WP_Term, "designs" => MyStyle_Design[]]]`.
	 * @throws \InvalidArgumentException Throws an InvalidArgumentException
	 * if the passed order_direction isn't one of "ASC" or "DESC".
	 * @global \wpdb $wpdb
	 */
	protected static function get_designs_by_term(
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
		$terms = self::get_terms(
			$taxonomy,
			$terms_per_page,
			$page_number,
			$order_by,
			$order_direction
		);

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
			. 'AND d.ms_access = 0 '
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
	 * Retrieve designs by term_taxonomy_id.
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
	protected static function get_designs_by_term_taxonomy_id(
		$term_taxonomy_id,
		WP_User $user = null,
		MyStyle_Session $session = null,
		$per_page = 250,
		$page_number = 1
	) {
		global $wpdb;

		// Query the db for the designs.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT d.* '
				. "FROM {$wpdb->prefix}term_relationships r "
				. "LEFT JOIN {$wpdb->prefix}mystyle_designs d ON (r.object_id = d.ms_design_id) "
				. 'WHERE r.term_taxonomy_id = %d
				   AND d.ms_access = %d
				   LIMIT %d
				   OFFSET %d',
				array(
					$term_taxonomy_id,
					MyStyle_Access::ACCESS_PUBLIC,
					$per_page,
					( $page_number - 1 ) * $per_page,
				)
			)
		);

		// Add the Designs to the return array.
		$designs = array();
		foreach ( $results as $result ) {
			$designs[] = MyStyle_Design::create_from_result_object( $result );
		}

		return $designs;
	}

	/**
	 * Retrieve the total number of public designs having the passed term.
	 *
	 * @param int                   $term_taxonomy_id The term taxonomy id.
	 * @param \WP_User|null         $user             The current user.
	 * @param \MyStyle_Session|null $session          The current MyStyle
	 *                                                session.
	 * @return int Returns the total number of designs having the passed term.
	 * @global $wpdb
	 */
	protected static function get_total_term_design_count(
		$term_taxonomy_id,
		WP_User $user = null,
		MyStyle_Session $session = null
	) {
		global $wpdb;

		$count = intval(
			$wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*)
				FROM (SELECT 1 as design '
					. "FROM {$wpdb->prefix}mystyle_designs d "
					. "LEFT JOIN {$wpdb->prefix}term_relationships r ON (r.object_id = d.ms_design_id) "
					. 'WHERE r.term_taxonomy_id = %d
				AND d.ms_access = %d
				) t',
					array(
						$term_taxonomy_id,
						MyStyle_Access::ACCESS_PUBLIC,
					)
				)
			)
		);

		return $count;
	}

	/**
	 * Get the terms for the design with the passed design id. See below for
	 * more info about the return value.
	 *
	 * @param string $taxonomy  The taxonomy of the terms that you want to get.
	 * @param int    $design_id The id of the design terms that you want to get.
	 * @param bool   $with_slug Set to true (default is false) to include the
	 *                          term slug in the returned terms. If true, the
	 *                          returned array becomes two dimensional with each
	 *                          entry having a 'name' and a 'slug' (and possibly
	 *                          an id.
	 * @param bool   $with_id   Set to true (default is false) to include the
	 *                          term id in the returned terms. If true, the
	 *                          returned array becomes two dimensional with each
	 *                          entry having a 'name' and an 'id' (and possibly a
	 *                          'slug').
	 * @return array Returns an array of terms. If the slug param is false, it
	 * will return a one dimensional array like ["Foo", "Bar"]. If the slug
	 * param is true, it will return a two dimensional array like
	 * [["name" => "Foo", "slug" => "foo"], ["name" => "Bar", "slug" => "bar"]].
	 */
	protected static function get_design_terms(
		string $taxonomy,
		$design_id,
		$with_slug = false,
		$with_id = false
	) {
		$ret_arr = array();
		$terms   = wp_get_object_terms( $design_id, $taxonomy );

		foreach ( $terms as $term ) {
			if ( $with_slug || $with_id ) {
				$entry = array(
					'name' => $term->name,
				);
				if ( $with_slug ) {
					$entry['slug'] = $term->slug;
				}
				if ( $with_id ) {
					$entry['id'] = $term->term_taxonomy_id;
				}
			} else {
				$entry = $term->name;
			}
			$ret_arr[] = $entry;
		}

		return $ret_arr;
	}

	/**
	 * Add a design term. Called to add a term to a design.
	 *
	 * @param string  $taxonomy  The term taxonomy to use.
	 * @param int     $design_id The id of the design to add the term to.
	 * @param string  $term      The term to add.
	 * @param WP_User $user      The current user.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the current user doesn't own the design
	 * and isn't an administrator.
	 * @return int Returns the Term Taxonomy Id of the term.
	 */
	protected static function add_term_to_design(
		string $taxonomy,
		$design_id,
		$term,
		WP_User $user
	) {
		// Security check.
		if ( ! self::is_user_authorized_to_modify_terms( $design_id, $user ) ) {
			throw new MyStyle_Unauthorized_Exception(
				'Only the design owner or an administrator can add terms to a design.'
			);
		}

		// Add the term.
		$tt_ids = wp_add_object_terms( $design_id, $term, $taxonomy );
		$tt_id  = $tt_ids[0];

		return $tt_id;
	}

	/**
	 * Removes a term from a design.
	 *
	 * @param string  $taxonomy  The term taxonomy to use.
	 * @param int     $design_id The id of the design to remove the term from.
	 * @param string  $term       The term to remove.
	 * @param WP_User $user      The current user.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the current user doesn't own the design
	 * and isn't an administrator.
	 * @return int Returns true on success, false on failure.
	 */
	protected static function remove_term_from_design(
		string $taxonomy,
		$design_id,
		$term,
		WP_User $user
	) {

		// Security check.
		if ( ! self::is_user_authorized_to_modify_terms( $design_id, $user ) ) {
			throw new MyStyle_Unauthorized_Exception(
				'Only the design owner or an administrator can add terms to a design.'
			);
		}

		// Remove the term.
		$success = wp_remove_object_terms( $design_id, $term, $taxonomy );

		return $success;
	}

	/**
	 * Updates a design's terms. Called to update all terms on a design to match
	 * the passed array of terms.
	 *
	 * @param string  $taxonomy  The term taxonomy to use.
	 * @param int     $design_id The id of the design to update.
	 * @param array   $terms      The array of terms. Should be an array of
	 *                           strings (ex: ["term1", "term2"]).
	 * @param WP_User $user      The current user.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the current user doesn't own the design
	 * and isn't an administrator.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if the function
	 * fails.
	 */
	protected static function update_design_terms(
		string $taxonomy,
		$design_id,
		$terms,
		WP_User $user
	) {
		// Security check.
		if ( ! self::is_user_authorized_to_modify_terms( $design_id, $user ) ) {
			throw new MyStyle_Unauthorized_Exception(
				'Only the design owner or an administrator can update a design\'s terms.'
			);
		}

		// Remove all current taxonomy terms from the design.
		$old_terms = wp_get_object_terms( $design_id, $taxonomy );
		if ( ! empty( $old_terms ) ) {
			$old_term_ids = array();
			foreach ( $old_terms as $old_term ) {
				$old_term_ids[] = $old_term->term_id;
			}

			$removed = wp_remove_object_terms( $design_id, $old_term_ids, $taxonomy );
			if ( ! $removed ) {
				throw new MyStyle_Exception( 'Couldn`t remove existing terms.' );
			}
		}

		// Add the passed terms to the design.
		if ( ! empty( $terms ) ) {
			$term_taxonomy_ids = wp_add_object_terms(
				$design_id,
				$terms,
				$taxonomy
			);
		}
	}

	/**
	 * Determines if the user is authorized to modify the Design's terms.
	 *
	 * @param int     $design_id The id of the design attempting to be modified.
	 * @param WP_User $user      The current user.
	 * @return bool Returns true if the user owns the design, otherwise, returns
	 * false.
	 * @todo Add unit testing for this method.
	 */
	protected static function is_user_authorized_to_modify_terms(
		$design_id,
		WP_User $user
	) {
		$is_authorized = false;

		if (
			( MyStyle_DesignManager::is_user_design_owner( $user->ID, $design_id ) )
			|| ( $user->has_cap( 'administrator' ) )
		) {
			$is_authorized = true;
		}

		return $is_authorized;
	}

}
