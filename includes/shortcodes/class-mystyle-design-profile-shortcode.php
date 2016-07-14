<?php

/**
 * Class for the MyStyle Design Profile Shortcode.
 * @package MyStyle
 * @since 1.4.0
 */
abstract class MyStyle_Design_Profile_Shortcode {

    /**
     * Output the design profile shortcode.
     */
    public static function output() {
        
        $design_profile_page = MyStyle_Design_Profile_Page::get_instance();
        
        $design = $design_profile_page->get_design();
        $ex = $design_profile_page->get_exception();
        
        if( $ex != null ) { //handle exceptions
            $out = '<p>' . $ex->getMessage() . '</p>';
        } else {
            // ---------- Call the view layer ------- //
            ob_start();
            require( MYSTYLE_TEMPLATES . 'design-profile.php' );
            $out = ob_get_contents();
            ob_end_clean();
            // -------------------------------------- //
        }

        return $out;       
    }

}