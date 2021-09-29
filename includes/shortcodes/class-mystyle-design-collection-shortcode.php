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
abstract class MyStyle_Design_Collection_Shortcode {

	/**
	 * Output the design collection shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 */
	public static function output( $atts ) {
		global $wp_query;

		$term = false;

		if ( isset( $wp_query->query['collection_term'] ) ) {
			$term = $wp_query->query['collection_term'];
		}

		$wp_user = wp_get_current_user();

		$session = MyStyle()->get_session();

		$show_designs = true;

		if (
			( isset( $atts['show_designs'] ) )
			&& ( 'false' === $atts['show_designs'] )
		) {
			$show_designs = false;
		}

		$pager      = 0;
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
				$pager  = intval( $_GET['pager'] );
				$offset = ( $pager * $term_limit );
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

		if ( $show_designs ) {

			$page_num = 1;

			if ( 1 === $terms_count ) {
				$limit = 20;

				// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
				if ( ( isset( $_GET['pager'] ) ) && ( null !== $_GET['pager'] ) ) {
					$pager    = intval( $_GET['pager'] );
					$page_num = $pager + 1;
				}
				// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected

				$total_design_count = MyStyle_DesignManager::get_total_term_design_count( $terms[0]->term_id, $wp_user, $session );
				$pager_array        = self::pager( $pager, $limit, $total_design_count );
			} elseif ( count( $all_terms ) > $term_limit ) {
				$pager_array = self::pager( $pager, $term_limit, count( $all_terms ) );

			}

			for ( $i = 0; $i < $terms_count; $i++ ) {
				$designs = MyStyle_DesignManager::get_designs_by_term_id(
					$terms[ $i ]->term_id,
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
		}

		$next = $pager_array['next'];
		$prev = $pager_array['prev'];

		ob_start();
		require MYSTYLE_TEMPLATES . 'design-collection-index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	/**
	 * Helper method that returns the pager array.
	 *
	 * @param int $pager The current page.
	 * @param int $limit The limit.
	 * @param int $term_count The total number of terms.
	 * @return array Returns the pager array.
	 */
	private static function pager( $pager, $limit, $term_count ) {
		$next = $pager + 1;
		$prev = $pager - 1;

		if ( $term_count < $limit ) {
			$next = null;
		}

		if ( 0 === $pager ) {
			$prev = null;
		}

		$total_shown = ( $pager * $limit );

		if ( ( $term_count - $total_shown ) <= $limit ) {
			$next = null;
		}

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

}
