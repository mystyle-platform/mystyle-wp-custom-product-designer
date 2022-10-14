<?php
/**
 * The MyStyle_Design_Term_Manager_Interface is implemented by classes that
 * manage design terms (tags, collections, etc).
 *
 * @package MyStyle
 * @since 4.0.0
 */

/**
 * MyStyle_Design_Term_Manager_Interface interface.
 */
interface MyStyle_Design_Term_Manager_Interface {

	/**
	 * Retrieve a list of Design Terms. Only includes terms that are used by at
	 * least one public Design.
	 *
	 * @param string      $taxonomy        The term taxonomy to use.
	 * @param int|null    $per_page        The number of terms to show per
	 *                                     page (default: 250).  Pass 0 for
	 *                                     unlimited.
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
	public static function get_terms(
		$taxonomy,
		$per_page = 250,
		$page_number = 1,
		$order_by = 'name',
		$order_direction = 'ASC'
	);

	/**
	 * Retrieve designs by term_taxonomy_id.
	 *
	 * This method is made public so that it child classes can be interchanged
	 * in their calling code.
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
	);

	/**
	 * Retrieve the total number of public designs having the passed term.
	 *
	 * This method is made public so that it child classes can be interchanged
	 * in their calling code.
	 *
	 * @param int                   $term_taxonomy_id The term taxonomy id.
	 * @param \WP_User|null         $user             The current user.
	 * @param \MyStyle_Session|null $session          The current MyStyle
	 *                                                session.
	 * @return int Returns the total number of designs having the passed term.
	 * @global $wpdb
	 */
	public static function get_total_term_design_count(
		$term_taxonomy_id,
		WP_User $user = null,
		MyStyle_Session $session = null
	);
}
