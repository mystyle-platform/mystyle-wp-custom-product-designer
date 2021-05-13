<?php
/**
 * Class for the MyStyle Design Tag Shortcode.
 *
 * @package MyStyle
 * @since 3.17.5
 */

/**
 * MyStyle_Design_Tag_Shortcode class.
 */
abstract class MyStyle_Design_Tag_Shortcode {

	/**
	 * Output the design tag shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 */
	public static function output( $atts ) {

		$wp_user = wp_get_current_user();

		$session = MyStyle()->get_session();

		$pager  = 0;
		$limit  = 4;
		$offset = 0;

		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		if ( isset( $_GET['pager'] ) ) {
			$pager  = intval( $_GET['pager'] );
			$offset = ( $pager * $limit );
		}
		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		$terms = get_terms(
			array(
				'taxonomy'   => MYSTYLE_TAXONOMY_NAME,
				'hide_empty' => false,
				'number'     => $limit,
				'offset'     => $offset,
			)
		);

		$terms_count = count( $terms );

		for ( $i = 0; $i < $terms_count; $i++ ) {
			$designs = MyStyle_DesignManager::get_designs_by_term_id(
				$terms[ $i ]->term_id,
				$wp_user,
				$session,
				6,
				1
			);

			if ( 0 === count( $designs ) ) {
				unset( $terms[ $i ] );
			} else {
				$terms[ $i ]->designs = $designs;
			}
		}

		$pager_array = self::pager( $pager, $limit, $terms_count );
		$next        = $pager_array['next'];
		$prev        = $pager_array['prev'];

		ob_start();
		require MYSTYLE_TEMPLATES . 'design-tag-index.php';
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

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

}
