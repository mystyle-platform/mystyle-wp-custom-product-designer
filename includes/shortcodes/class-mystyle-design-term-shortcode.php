<?php
/**
 * Abstract base class for the MyStyle_Design_Tag_Shortcode and
 * MyStyle_Design_Collection_Shortcode
 *
 * @package MyStyle
 * @since 4.0.0
 */

/**
 * MyStyle_Design_Term_Shortcode class.
 */
abstract class MyStyle_Design_Term_Shortcode {

	/**
	 * The file path for the short code's template.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * The taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * The URL query parameter that may contain the term
	 * (ex: "collection_term").
	 *
	 * @var string
	 */
	protected $term_query_param;

	/**
	 * The number of designs to show at a time when viewing a single
	 * term.
	 *
	 * @var int
	 */
	protected $term_design_limit;

	/**
	 * The number of designs to show at a time when viewing the term
	 * index.
	 *
	 * @var int
	 */
	protected $index_design_limit;

	/**
	 * The number of terms to show at a time when viewing the term
	 * index.
	 *
	 * @var int
	 */
	protected $index_term_limit;

	/**
	 * The term manager.
	 *
	 * @var \MyStyle_Design_Term_Manager_Interface
	 */
	protected $term_manager;

	/**
	 * Helper method that returns the pager array.
	 *
	 * @param int $page           The current page.
	 * @param int $terms_per_page The number of terms shown on each page.
	 * @param int $total_terms    The total number of terms.
	 * @return array Returns the pager array.
	 */
	protected static function get_pager( $page, $terms_per_page, $total_terms ) {
		$prev = ( 1 === $page ) ? null : $page - 1;

		$next = $page + 1;
		if ( $next > ceil( $total_terms / $terms_per_page ) ) {
			$next = null;
		}

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

	/**
	 * Protected helper method that builds the output for the design term
	 * shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @returns string Returns the output for the shortcode as a string of HTML.
	 */
	protected function build_output( $atts ) {
		// Get the session variables.
		$wp_user = wp_get_current_user();
		$session = MyStyle()->get_session();

		// Get the attributes and URL params.
		$atts_clean = $this->get_attributes( $atts );
		$url_params = $this->get_url_params();
		$term       = $url_params['term'];

		if ( null !== $term ) {
			// ----------- SINGLE TERM -------------- //
			$out = $this->get_single_term_output(
				$atts_clean,
				$url_params,
				$wp_user,
				$session,
				$term
			);
		} else {
			// ------------ TERM INDEX -------------- //
			$out = $this->get_term_index_output(
				$atts_clean,
				$url_params,
				$wp_user,
				$session
			);
		}

		return $out;
	}

	/**
	 * Helper method that returns the sort_by array.
	 *
	 * Example: ['name' => 'alpha', 'slug' => 'name', 'direction' => 'ASC']
	 *
	 * @return array Returns the sort_by array.
	 */
	protected static function get_sort_by() {

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$name = null;
		if ( isset( $_GET['sort_by'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_GET['sort_by'] ) );
		}
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

		// Default to sorted by count/qty (from biggest to smallest).
		$slug      = 'count';
		$direction = 'DESC';

		if ( 'alpha' === $name ) {
			$slug      = 'name';
			$direction = 'ASC';
		}

		return array(
			'name'      => $name,
			'slug'      => $slug,
			'direction' => $direction,
		);
	}

	/**
	 * Protected helper method that builds the shortcode for outputting the term
	 * index.
	 *
	 * @param array           $atts_clean An array of attributes passed into the shortcode.
	 * @param array           $url_params An array of the current relevant URL parameters.
	 * @param WP_User         $wp_user    The current WP_User.
	 * @param MyStyle_Session $session    The current MyStyle Session.
	 */
	protected function get_term_index_output(
		array $atts_clean,
		array $url_params,
		WP_User $wp_user,
		MyStyle_Session $session
	) {
		// Set up the main variables.
		$page_num     = $url_params['page_num'];
		$design_limit = $this->index_design_limit; // Number of designs to show per term.
		$term_limit   = $this->index_term_limit; // Number of terms to show per page.

		// Start setting up the Pager (more variables set further down).
		$pager = new MyStyle_Pager();
		$pager->set_current_page_number( $page_num );
		$pager->set_items_per_page( $term_limit );

		$sort_by         = self::get_sort_by();
		$order_by        = ( 'alpha' === $sort_by['name'] ) ? 'name' : 'count';
		$order_direction = ( 'alpha' === $sort_by['name'] ) ? 'ASC' : 'DESC';

		// Get all_terms.
		$all_terms = $this->term_manager::get_terms(
			$this->taxonomy,
			0, // Limit (0 = unlimited).
			1, // Page number.
			$order_by,
			$order_direction
		);
		$pager->set_total_item_count( count( $all_terms ) );

		// Create an array of terms (restricted by the term_limit and offset).
		$terms = $this->term_manager::get_terms(
			$this->taxonomy,
			$pager->get_items_per_page(),
			$pager->get_current_page_number(),
			$order_by,
			$order_direction
		);

		/**
		 * Loop the terms and set the designs on each.
		 *
		 * @var $slug string The current slug in the loop.
		 * @var $term \WP_Term The current WP_Term in the loop.
		 */
		foreach ( $terms as $slug => $term ) {
			$this->hydrate_designs( $term, $wp_user, $session, $design_limit, 1 );

			if ( 1 > count( $term->designs ) ) {
				unset( $terms[ $slug ] );
			}
		}
		$pager->set_items( $terms );

		// Render.
		$out = $this->render(
			$atts_clean['show_designs'],
			$pager,
			$terms,
			$all_terms,
			$design_limit,
			null, // term.
			$sort_by
		);

		return $out;
	}

	/**
	 * Protected helper method that builds the shortcode for outputing an
	 * individual term.
	 *
	 * @param array           $atts_clean An array of attributes passed into the shortcode.
	 * @param array           $url_params An array of the current relevant URL parameters.
	 * @param WP_User         $wp_user    The current WP_User.
	 * @param MyStyle_Session $session    The current MyStyle Session.
	 * @param string          $term_str   The current term (passed in via the URL params).
	 */
	protected function get_single_term_output(
		array $atts_clean,
		array $url_params,
		WP_User $wp_user,
		MyStyle_Session $session,
		$term_str
	) {
		// Set up the main variables.
		$page_num     = $url_params['page_num'];
		$design_limit = $this->term_design_limit;

		// Start setting up the Pager (more variables set further down).
		$pager = new MyStyle_Pager();
		$pager->set_current_page_number( $page_num );
		$pager->set_items_per_page( $design_limit );

		// Get all_terms (for the left nav).
		$all_terms = $this->get_all_terms();

		// Get the WP_Term.
		$term = get_term_by( 'slug', $term_str, $this->taxonomy );

		// Hydrate the designs on the term.
		$this->hydrate_designs( $term, $wp_user, $session, $design_limit, $page_num );

		// Set the items on the pager to the term's designs.
		$pager->set_items( $term->designs );
		$pager->set_total_item_count( $term->total_design_count );

		// Render.
		$out = $this->render(
			$atts_clean['show_designs'],
			$pager,
			array( $term ),
			$all_terms,
			$design_limit,
			$term
		);

		return $out;
	}

	/**
	 * Protected helper method that gets the attributes as a clean array.
	 *
	 * @param array $atts The shortcode attributes array as passed in from
	 *                    WordPress.
	 * @return array Returns the shortcode attributes as a clean array.
	 */
	protected function get_attributes( $atts ) {
		$atts_clean = array(
			'show_designs' => true,
		);

		if (
			( isset( $atts['show_designs'] ) )
			&& ( 'false' === $atts['show_designs'] )
		) {
			$atts_clean['show_designs'] = false;
		}

		return $atts_clean;
	}

	/**
	 * Protected helper method that gets the relevant parameters from the URL.
	 *
	 * @global \WP_Query $wp_query
	 * @return array Returns the URL parameters as an array.
	 */
	protected function get_url_params() {
		global $wp_query;

		$url_params = array(
			'term'     => null,
			'page_num' => 1,
		);

		if ( isset( $wp_query->query[ $this->term_query_param ] ) ) {
			$term = $wp_query->query[ $this->term_query_param ];

			$url_parts       = explode( '/', $term );
			$url_parts_count = count( $url_parts );
			$i               = 0;
			while ( ( $i < $url_parts_count ) && ( 1 === $url_params['page_num'] ) ) {
				if ( 'page' === $url_parts[ $i ] ) {
					$url_params['page_num'] = $url_parts[ $i + 1 ];
				} else {
					$url_params['term'] = $url_parts[ $i ];
				}
				$i++;
			}
		}

		return $url_params;
	}

	/**
	 * Protected helper method that hydrates the designs property on the passed
	 * term with the designs that have that term.
	 *
	 * @param \WP_Term         $term     The term that you are working with.
	 * @param \WP_User         $wp_user  The current WP_User.
	 * @param \MyStyle_Session $session  The current MyStyle_Session.
	 * @param int              $limit    The number of designs to hydrate.
	 * @param int              $page_num The page number that we are on.
	 */
	protected function hydrate_designs(
		\WP_Term $term,
		\WP_User $wp_user,
		\MyStyle_Session $session,
		$limit,
		$page_num
	) {
		$designs       = $this->term_manager::get_designs_by_term_taxonomy_id(
			$term->term_taxonomy_id,
			$wp_user,
			$session,
			$limit,
			$page_num
		);
		$term->designs = $designs;

		$total_design_count       = $this->term_manager::get_total_term_design_count(
			$term->term_taxonomy_id,
			$wp_user,
			$session
		);
		$term->total_design_count = $total_design_count;
	}

	/**
	 * Protected helper method that gets all terms. This is used for the left
	 * nav and for the total count on the term index.
	 *
	 * @return array Returns all of the terms for the taxonomy.
	 */
	protected function get_all_terms() {
		$all_terms = get_terms(
			array(
				'taxonomy'   => $this->taxonomy,
				'hide_empty' => true,
			)
		);

		return $all_terms;
	}

	/**
	 * Protected helper function that renders the view layer and returns the
	 * output.
	 *
	 * This code is in its own method to identify and restrict the variables
	 * available to the view layer.
	 *
	 * @param bool           $show_designs Whether or not to show designs (in addition to terms).
	 * @param \MyStyle_Pager $pager        The pager object.
	 * @param array          $terms        The array of terms (may just be one).
	 * @param array          $all_terms    The array of all terms (used for the left nav).
	 * @param int|null       $design_limit The maximum number of designs to show per term.
	 * @param \WP_Term|null  $term         The current term (or null if viewing the index of terms).
	 * @param array          $sort_by      The sort_by array (see the get_sort_by method for details).
	 * @return string Returns the output (as a string).
	 */
	protected function render(
		$show_designs,
		\MyStyle_Pager $pager,
		array $terms,
		array $all_terms,
		$design_limit,
		\WP_Term $term = null,
		$sort_by = null
	) {

		ob_start();
		require $this->template;
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

}
