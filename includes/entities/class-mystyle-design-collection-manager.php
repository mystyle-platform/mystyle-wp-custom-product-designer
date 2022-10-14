<?php
/**
 * The MyStyle_Design_Collection_Manager class has functions for managing
 * MyStyle_Design Collections.
 *
 * @package MyStyle
 * @since 4.0.0
 */

/**
 * MyStyle_Design_Collection_Manager class.
 */
class MyStyle_Design_Collection_Manager
	extends \MyStyle_Design_Term_Manager
	implements \MyStyle_Design_Term_Manager_Interface {

	/**
	 * Retrieve a list of Design Collections. Only includes collections that are
	 * used by at least one public Design.
	 *
	 * @param int|null    $per_page        The number of collections to show
	 *                                     per page (default: 250).
	 * @param int|null    $page_number     The page number of the set of
	 *                                     collections that you want to get
	 *                                     (default: 1).
	 * @param string|null $order_by        What to order the results by (can be
	 *                                     either "name" or "count"). Default is
	 *                                     "name".
	 * @param string|null $order_direction The direction to order the results
	 *                                     in. Can be either "ASC" (Ascending)
	 *                                     or "DESC" (Descending). Default is
	 *                                     "ASC".
	 * @return WP_Term[] Returns an array of WP_Terms.
	 * @throws InvalidArgumentException Throws an InvalidArgumentException if
	 * passed invalid order params.
	 * @todo Add unit testing.
	 */
	public static function get_collections(
		$per_page = 250,
		$page_number = 1,
		$order_by = 'name',
		$order_direction = 'ASC'
	) {
		return parent::get_terms(
			MYSTYLE_COLLECTION_NAME,
			$per_page,
			$page_number,
			$order_by,
			$order_direction
		);
	}

	/**
	 * Gets the total number of Design collections. Only includes collections
	 * that are used sby at least one public Design. This is needed for paging
	 * through the collections returned by the get_collections method above.
	 *
	 * @return int Returns the total number of terms.
	 * @todo Add unit testing.
	 */
	public static function get_collections_count() {
		return parent::get_terms_count( MYSTYLE_COLLECTION_NAME );
	}

	/**
	 * Retrieve Designs grouped by collection. Only includes public Designs and
	 * collections that are used by at least one public Design.
	 *
	 * @param int|null    $terms_per_page   The number of collections to show
	 *                                      per page (default: 250).
	 * @param int|null    $page_number      The page number of the set of terms
	 *                                      that you want to get (default: 1).
	 * @param int|null    $designs_per_term The number of designs to show per
	 *                                      term.
	 * @param string|null $order_by         What to order the results by (can be
	 *                                      either "name" or "count"). Default is
	 *                                      "name".
	 * @param string|null $order_direction  The direction to order the results
	 *                                      in. Can be either "ASC" (Ascending)
	 *                                      or "DESC" (Descending). Default is
	 *                                      "ASC".
	 * @return array Returns a two dimensional array structured as:
	 * `["term_slug" => ["term" => WP_Term, "designs" => MyStyle_Design[]]]`.
	 */
	public static function get_designs_by_collection(
		$terms_per_page = 250,
		$page_number = 1,
		$designs_per_term = 5,
		$order_by = 'name',
		$order_direction = 'ASC'
	) {
		return parent::get_designs_by_term(
			MYSTYLE_COLLECTION_NAME,
			$terms_per_page,
			$page_number,
			$designs_per_term,
			$order_by,
			$order_direction
		);
	}

	/**
	 * Retrieve designs by collection term taxonomy id.
	 *
	 * @param int                   $term_taxonomy_id The term taxonomy id of
	 *                                                the collection.
	 * @param \WP_User|null         $user             The current user.
	 * @param \MyStyle_Session|null $session          The current MyStyle
	 *                                                session.
	 * @param int                   $per_page         The number of designs to
	 *                                                show per page
	 *                                                (default: 250).
	 * @param int                   $page_number      The page number of the set
	 *                                                of designs that you want
	 *                                                to get (default: 1).
	 * @return MyStyle_Design[]|null Returns an array of MyStyle_Design objects
	 * or null if none are found.
	 */
	public static function get_designs_by_collection_term_taxonomy_id(
		$term_taxonomy_id,
		WP_User $user = null,
		MyStyle_Session $session = null,
		$per_page = 250,
		$page_number = 1
	) {
		return parent::get_designs_by_term_taxonomy_id(
			$term_taxonomy_id,
			$user,
			$session,
			$per_page,
			$page_number
		);
	}

	/**
	 * Retrieve the total number of public designs having the passed collection.
	 *
	 * @param int                   $term_taxonomy_id The collection's term
	 *                                                taxonomy id.
	 * @param \WP_User|null         $user             The current user.
	 * @param \MyStyle_Session|null $session          The current MyStyle
	 *                                                session.
	 * @return int Returns the total number of designs having the passed
	 * collection.
	 */
	public static function get_total_collection_design_count(
		$term_taxonomy_id,
		WP_User $user = null,
		MyStyle_Session $session = null
	) {
		return parent::get_total_term_design_count(
			$term_taxonomy_id,
			$user,
			$session
		);
	}

	/**
	 * Get the collections for the design with the passed design id. See below
	 * for more info about the return value.
	 *
	 * @param int  $design_id The id of the design collections that you want to
	 *                        get.
	 * @param bool $with_slug Set to true (default is false) to include the
	 *                        term slug in the returned array. If true, the
	 *                        returned array becomes two dimensional with each
	 *                        entry having a 'name' and a 'slug' (and possibly
	 *                        an id.
	 * @param bool $with_id   Set to true (default is false) to include the
	 *                        term id in the returned array. If true, the
	 *                        returned array becomes two dimensional with each
	 *                        entry having a 'name' and an 'id' (and possibly a
	 *                        'slug').
	 * @return array Returns an array of terms. If the slug param is false, it
	 * will return a one dimensional array like ["Foo", "Bar"]. If the slug
	 * param is true, it will return a two dimensional array like
	 * [["name" => "Foo", "slug" => "foo"], ["name" => "Bar", "slug" => "bar"]].
	 */
	public static function get_design_collections(
		$design_id,
		$with_slug = false,
		$with_id = false
	) {
		return parent::get_design_terms(
			MYSTYLE_COLLECTION_NAME,
			$design_id,
			$with_slug,
			$with_id
		);
	}

	/**
	 * Add a design collection. Called to add a collection to a design.
	 *
	 * @param int     $design_id  The id of the design to add the collection to.
	 * @param string  $collection The collection to add.
	 * @param WP_User $user       The current user.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the current user doesn't own the design
	 * and isn't an administrator.
	 * @return int Returns the Term Taxonomy Id of the collection.
	 */
	public static function add_collection_to_design(
		$design_id,
		$collection,
		WP_User $user
	) {
		return parent::add_term_to_design(
			MYSTYLE_COLLECTION_NAME,
			$design_id,
			$collection,
			$user
		);
	}

	/**
	 * Removes a collection from a design.
	 *
	 * @param int     $design_id  The id of the design to remove the collection
	 *                            from.
	 * @param string  $collection The collection to remove.
	 * @param WP_User $user       The current user.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the current user doesn't own the design
	 * and isn't an administrator.
	 * @return int Returns true on success, false on failure.
	 */
	public static function remove_collection_from_design(
		$design_id,
		$collection,
		WP_User $user
	) {
		return parent::remove_term_from_design(
			MYSTYLE_COLLECTION_NAME,
			$design_id,
			$collection,
			$user
		);
	}

	/**
	 * Updates a design's collections. Called to update all collections on a
	 * design to match the passed array of collections.
	 *
	 * @param int     $design_id The id of the design to update.
	 * @param array   $collections      The array of collections. Should be an array of
	 *                           strings (ex: ["collection1", "collection2"]).
	 * @param WP_User $user      The current user.
	 * @throws MyStyle_Unauthorized_Exception Throws a
	 * MyStyle_Unauthorized_Exception if the current user doesn't own the design
	 * and isn't an administrator.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if the function
	 * fails.
	 */
	public static function update_design_collections(
		$design_id,
		$collections,
		WP_User $user
	) {
		return parent::update_design_terms(
			MYSTYLE_COLLECTION_NAME,
			$design_id,
			$collections,
			$user
		);
	}

}
