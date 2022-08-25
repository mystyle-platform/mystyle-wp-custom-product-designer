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
	 * Helper method that returns the sort_by array.
	 *
	 * Example: ['name' => 'alpha', 'slug' => 'name', 'direction' => 'ASC']
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @return array Returns the sort_by array.
	 */
	protected static function get_sort_by( $atts ) {

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

}
