<?php
/**
 * Class for the MyStyle Design Shortcode.
 *
 * How to use:
 *  * Add [mystyle_design] to the page.
 *  * Make sure that your url includes a design_id variable
 *    ( example: "http://www.example.com/somepage?design_id=123" ).
 *
 * Adds the mystyle design to the page. It pulls the design id from the
 * design_id variable that needs to be in the url for the shortcode to work.
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
	 * @throws Exception Throws the exception if one is set in the controller.
	 */
	public static function output( $atts = [], $content = null, $tag = '' ) {
		$out = '';

		$mystyle_frontend = MyStyle_FrontEnd::get_instance();

		// -------------------- Handle Exceptions ---------------------- //
		$ex = $mystyle_frontend->get_exception();
		if ( null !== $ex ) {
			throw $ex;
		} else {
			// --------------- Valid Requests ------------------------- //
			if ( null !== $mystyle_frontend->get_design() ) {
				$out = self::output_design();
			} else {
                if( isset($atts['count']) ) { //return random public designs
                    
                    $count = $atts['count'] ;
                    $out = self::output_random_designs( $count ) ;
                }
				// Fail silently. This can happen in the admin or if the
				// design_id isn't set in the url.
				// throw new MyStyle_Bad_Request_Exception('Design not found');
			}
		}

		return $out;
	}

	/**
	 * Returns the output for a design.
	 *
	 * @return string
	 */
	public static function output_design() {
		$mystyle_frontend = MyStyle_FrontEnd::get_instance();

		// ------------- set the template variables -------------------//
		$design = $mystyle_frontend->get_design();

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
     * Return the output for random designs
     *
     * @return string
     */
    public static function output_random_designs( $count ) {
        
        // Create a new pager.
		$pager = new MyStyle_Pager();

		// Designs per page.
		$pager->set_items_per_page( $count );
        
		$pager->set_current_page_number(
			1
		);

		// Pager items.
		$designs = MyStyle_DesignManager::get_random_designs(
            $count,
            1
        );
		$pager->set_items( $designs );
        
        
        // ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'design-profile/index.php';
		$out = ob_get_contents();
		ob_end_clean();
        
        return $out ;
    }
    
}
