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
abstract class MyStyle_Design_Tag_Shortcode extends MyStyle_Design_Term_Shortcode {

	/**
	 * Output the design tag shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 */
	public static function output( $atts ) {

		$show_designs = true;
		if (
			( isset( $atts['show_designs'] ) )
			&& ( 'false' === $atts['show_designs'] )
		) {
			$show_designs = false;
		}

		if ( $show_designs ) {
			$out = self::get_design_tag_output( $atts );
		} else {
			$out = self::get_design_tag_index_output( $atts );
		}

		return $out;
	}

	/**
	 * Gets the design tag index (the output with just links).
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @return string Returns the output.
	 */
	private static function get_design_tag_index_output( $atts ) {

		$sort_by = self::get_sort_by( $atts );

		$terms = MyStyle_Design_Tag_Manager::get_tags(
			250, // Terms per page.
			1, // Page num.
			$sort_by['slug'],
			$sort_by['direction']
		);

		ob_start();
		require MYSTYLE_TEMPLATES . 'design-tag-index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	/**
	 * Gets the design tag output.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @return string Returns the output.
	 * @throws MyStyle_Not_Found_Exception Throws a MyStyle_Not_Found_Exception
	 * if the requested page number doesn't exist.
	 */
	private static function get_design_tag_output( $atts ) {

		$sort_by = self::get_sort_by( $atts );

		$designs_per_tag = 5;
		if ( isset( $atts['per_tag'] ) ) {
			$designs_per_tag = $atts['per_tag'];
		}

		$tags_per_page = 250;
		if ( isset( $atts['tags_per_page'] ) ) {
			$tags_per_page = $atts['tags_per_page'];
		}

		$page = 1;
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		if ( isset( $_GET['pager'] ) ) {
			$page = intval( $_GET['pager'] );
			if ( $page < 1 ) {
				throw new MyStyle_Not_Found_Exception( 'Page not found.' );
			}
		}
		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		$term_designs = MyStyle_Design_Tag_Manager::get_designs_by_tag(
			$tags_per_page,
			$page,
			$designs_per_tag,
			$sort_by['slug'],
			$sort_by['direction']
		);

		// More variables for the view layer.
		$total_terms = MyStyle_Design_Tag_Manager::get_tags_count();
		$pager_array = self::get_pager( $page, $tags_per_page, $total_terms );
		$next        = $pager_array['next'];
		$prev        = $pager_array['prev'];
		$wp_user     = wp_get_current_user();
		$session     = MyStyle()->get_session();

		ob_start();
		require MYSTYLE_TEMPLATES . 'design-tag.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

}
