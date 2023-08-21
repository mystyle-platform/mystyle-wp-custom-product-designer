<?php
/**
 * Class for the MyStyle Design Shortcode.
 *
 * To use add [mystyle_design] to the page with attributes or query params set
 * as described below.
 *
 * Available attributes:
 *
 * * gallery: Set gallery equal to 1 (ex: [mystyle_design gallery=1]) to have a
 *   gallery of designs displayed. The gallery is also displayed if no design id
 *   is passed.
 * * design_id: Set the design_id attribute to the id of the design that you
 *   want to display. This attribute is ignored if gallery is turned on (see
 *   above). Note that the shortcode can also retrieve the design_id from the
 *   URL (as described below).
 * * count: Used with gallery mode. Use count to specify how many designs to
 *   show (ex: [mystyle-design gallery=1 count=6]). Default is 10.
 * * total: Synonym for count.
 * * tag: Used with gallery mode. Pass to only show designs with the provided
 *   tag (ex: [mystyle-design gallery=1 tag="anime"]).
 *
 * Available Query Params:
 *
 *  * design_id: The design_id can be passed to the shortcode either through
 *    a shortcode attribute (as described above) or via a query param
 *   (example: "http://www.example.com/somepage?design_id=123").
 *
 * @package MyStyle
 * @since 3.4.0
 */

/**
 * MyStyle_Design_Shortcode class.
 */
abstract class MyStyle_Design_Shortcode {

	/**
	 * Output the design shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @throws Exception Throws the exception if one is set in the controller.
	 */
	public static function output( $atts ) {
		$out = '';

		$mystyle_frontend = MyStyle_FrontEnd::get_instance();

		// -------------------- Handle Exceptions ---------------------- //
		$ex = $mystyle_frontend->get_exception();
		if ( null !== $ex ) {
			throw $ex;
		}

		// --------------------- Valid Requests ------------------------ //
		if ( ( isset( $atts['gallery'] ) ) && ( $atts['gallery'] ) ) {
			$out = self::output_gallery( $atts );
		} else {
			if (
				( null !== $mystyle_frontend->get_design() )
				|| ( isset( $atts['design_id'] ) )
			) {
				$out = self::output_design( $atts );
			} else {
				$out = self::output_gallery( $atts );
			}
		}

		return $out;
	}

	/**
	 * Private helper method that returns the output for a specific design.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @return string Returns the output for a design (as a string).
	 * @throws MyStyle_Not_Found_Exception Throws a MyStyle_Not_Found_Exception
	 * if the design isn't found.
	 */
	private static function output_design( $atts ) {
		$mystyle_frontend = MyStyle_FrontEnd::get_instance();

		// ------------- set the template variables -------------------//
		// Get the design from the URL.
		$design = $mystyle_frontend->get_design();

		// If not in the URL, get the design from the shortcode attributes.
		if ( null === $design ) {
			$design_id = $atts['design_id'];
			$design    = MyStyle_DesignManager::get(
				$design_id,
				wp_get_current_user(),
				MyStyle()->get_session()
			);
		}

		// If still no design, throw an exception.
		if ( null === $design ) {
			throw new MyStyle_Not_Found_Exception( 'Design not found.' );
		}

		$renderer_url = 'https://www.mystyleplatform.com/' .
				'tools/render/?mode=customerpreview' .
				'&design_url=' . $design->get_design_url();

		// ---------- Call the view layer ------------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'design.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	/**
	 * Private helper method that returns the output for the gallery.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @return string Returns the output for the gallery (as a string).
	 */
	private static function output_gallery( $atts ) {

		// Determine the count (can be passed as `count` or `total`).
		$count = 10;
		if ( isset( $atts['count'] ) ) {
			$count = $atts['count'];
		}
		if ( isset( $atts['total'] ) ) {
			$count = $atts['total'];
		}

		// If tag is passed, serve designs with the specified tag.
		if ( isset( $atts['tag'] ) ) {
			$tag = $atts['tag'];

			$out = self::output_tagged_designs( $tag, $count );
		} elseif (isset($atts['collection'])) { // If collection is passed, serve designs with the specified collection.
			$collection = $atts['collection'];
			$out = self::output_collection_designs($collection, $count);
		} else {
			// Serve random designs.
			$out = self::output_random_designs($count);
		}

		return $out;
	}

	/**
	 * Private helper method that return the output for the random designs
	 * gallery.
	 *
	 * @param integer $count The number of designs to display.
	 * @return string Returns the output for the random designs gallery (as a
	 * (string).
	 */
	private static function output_random_designs( $count ) {
		$user = wp_get_current_user();

		// Create a new pager.
		$pager = new MyStyle_Pager();

		// Designs per page.
		$pager->set_items_per_page( $count );

		$pager->set_current_page_number( 1 );

		// Pager items.
		$designs = MyStyle_DesignManager::get_random_designs( $count, $user );

		$pager->set_items( $designs );

		// ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'design-profile/index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	/**
	 * Private helper method that returns the output for the gallery of designs
	 * for a specific tag.
	 *
	 * @param string  $tag   The tag to serve.
	 * @param integer $count The number of designs to display.
	 * @return string Returns the output for the tagged design gallery (as a
	 * string).
	 */
	private static function output_tagged_designs( $tag, $count ) {

		$term = get_term_by( 'name', $tag, 'design_tag' );

		$term_taxonomy_id = $term->term_taxonomy_id;

		$user = wp_get_current_user();

		$session = MyStyle()->get_session();

		// Create a new pager.
		$pager = new MyStyle_Pager();

		// Designs per page.
		$pager->set_items_per_page( $count );

		$pager->set_current_page_number( 1 );

		// Pager items.
		$designs = MyStyle_DesignManager::get_designs_by_term_id( $term_taxonomy_id, $user, $session, $count, 1 );

		$pager->set_items( $designs );

		// ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'design-profile/index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}



	/**
	 * Private helper method that returns the output for the gallery of designs
	 * for a specific collections.
	 */


	private static function output_collection_designs($collection, $count)
	{

		$term = get_term_by('name', $collection, 'design_collection');

		$term_taxonomy_id = $term->term_taxonomy_id;

		$user = wp_get_current_user();

		$session = MyStyle()->get_session();

		// Create a new pager.
		$pager = new MyStyle_Pager();

		// Designs per page.
		$pager->set_items_per_page($count);

		$pager->set_current_page_number(1);

		// Pager items.
		$designs = MyStyle_DesignManager::get_designs_by_term_id($term_taxonomy_id, $user, $session, $count, 1);

		$pager->set_items($designs);

		// ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'design-profile/index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}
}