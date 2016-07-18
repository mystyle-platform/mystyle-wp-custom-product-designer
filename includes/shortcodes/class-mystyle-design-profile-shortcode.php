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
        
        $template_name = 'design-profile.php';
        
        if( $ex != null ) { //handle exceptions
            switch( get_class( $ex ) ) {
                case 'MyStyle_Unauthorized_Exception':
                    $template_name = 'design-profile_error-unauthorized.php';
                    break;
                case 'MyStyle_Forbidden_Exception':
                    $template_name = 'design-profile_error-forbidden.php';
                    break;
                default:
                    $template_name = 'design-profile_error-general.php';
            }
        }
        
        // ---------- Call the view layer ------- //
        ob_start();
        require( MYSTYLE_TEMPLATES . $template_name );
        $out = ob_get_contents();
        ob_end_clean();
        // -------------------------------------- //

        return $out;       
    }

}