<?php
/**
 * Class for the MyStyle Design Collection Shortcode.
 *
 * @package MyStyle
 * @since 3.18.5
 */

/**
 * MyStyle_Design_Collection_Shortcode class.
 */
abstract class MyStyle_Design_Collection_Shortcode extends MyStyle_Design_Term_Shortcode {

	/**
	 * Output the design collection shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @global \WP_Query $wp_query
	 */
	public static function output( $atts ) {
		global $wp_query;

		// Get the term from the URL (if any).
		$term = false;
		if ( isset( $wp_query->query['collection_term'] ) ) {
			$term = $wp_query->query['collection_term'];
		}

		$wp_user = wp_get_current_user();
		$session = MyStyle()->get_session();

		$page       = 1;
		$limit      = 4;
		$term_limit = 10;
		$offset     = 0;

		$all_terms = get_terms(
			array(
				'taxonomy'   => MYSTYLE_COLLECTION_NAME,
				'hide_empty' => true,
			)
		);

		if ( $term ) {
			$terms   = array();
			$terms[] = get_term_by( 'slug', $term, MYSTYLE_COLLECTION_NAME );
		} else {
			// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
			if ( ( isset( $_GET['pager'] ) ) && ( 0 !== $_GET['pager'] ) ) {
				$page   = intval( $_GET['pager'] );
				$offset = ( $page * $term_limit );
			}
			// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected

			$terms = get_terms(
				array(
					'taxonomy'   => MYSTYLE_COLLECTION_NAME,
					'hide_empty' => true,
					'number'     => $term_limit,
					'offset'     => $offset,
				)
			);
		}

		$terms_count = count( $terms );

		$page_num = 1;

		$pager_array = array(
			'next' => null,
			'prev' => null,
		);

		if ( 1 === $terms_count ) {
			$limit = 20;

			// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
			if ( ( isset( $_GET['pager'] ) ) && ( null !== $_GET['pager'] ) ) {
				$page     = intval( $_GET['pager'] );
				$page_num = $page + 1;
			}
			// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected

			$total_design_count = MyStyle_Design_Collection_Manager::get_total_collection_design_count(
				$terms[0]->term_taxonomy_id,
				$wp_user,
				$session
			);
			$pager_array        = self::get_pager( $page, $limit, $total_design_count );
		} elseif ( count( $all_terms ) > $term_limit ) {
			$pager_array = self::get_pager( $page, $term_limit, count( $all_terms ) );
		}

		for ( $i = 0; $i < $terms_count; $i++ ) {
			$designs = MyStyle_Design_Collection_Manager::get_designs_by_collection_term_taxonomy_id(
				$terms[ $i ]->term_taxonomy_id,
				$wp_user,
				$session,
				$limit,
				$page_num
			);

			if ( 0 === count( $designs ) ) {
				unset( $terms[ $i ] );
			} else {
				$terms[ $i ]->designs = $designs;
			}
		}

		$next = $pager_array['next'];
		$prev = $pager_array['prev'];

		ob_start();
		require MYSTYLE_TEMPLATES . 'design-collection-index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

}
