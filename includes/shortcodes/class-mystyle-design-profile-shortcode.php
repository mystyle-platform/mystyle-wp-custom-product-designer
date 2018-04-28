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
        
        //-------------------- handle exceptions ----------------------//
        $ex = $design_profile_page->get_exception();
        if( $ex != null ) { 
            if( $design_profile_page->get_pager() != null ) {
                // index
                $template_name = 'design-profile/index_error-general.php';
            } else {
                // design profile page
                switch( get_class( $ex ) ) {
                    case 'MyStyle_Unauthorized_Exception':
                        $template_name = 'design-profile/profile_error-unauthorized.php';
                        break;
                    case 'MyStyle_Forbidden_Exception':
                        $template_name = 'design-profile/profile_error-forbidden.php';
                        break;
                    default:
                        $template_name = 'design-profile/profile_error-general.php';
                }
            }
            
            ob_start();
            require( MYSTYLE_TEMPLATES . $template_name );
            $out = ob_get_contents();
            ob_end_clean();
        } else {
            // --------------- Valid Requests ------------------------- //
            if( $design_profile_page->get_design() != null ) {
                $out = self::output_design_profile();
            } else {
                $out = self::output_design_index();
            }
        }
        
        return $out;
    }
    
    /**
     * Returns the output for a design profile.
     * @return string
     */
    public static function output_design_profile() {
        $design_profile_page = MyStyle_Design_Profile_Page::get_instance();
        
        // ------------- set the template variables -------------------//
        $design = $design_profile_page->get_design();
        
        $previous_design = $design_profile_page->get_previous_design();
        if( $previous_design != null ) {
            $previous_design_url = MyStyle_Design_Profile_Page::get_design_url( $previous_design );
        }
        
        $next_design = $design_profile_page->get_next_design();
        if( $next_design != null ) {
            $next_design_url = MyStyle_Design_Profile_Page::get_design_url( $next_design );
        }
        
        // ---------- Call the view layer ------------------------ //
        ob_start();
        require( MYSTYLE_TEMPLATES . 'design-profile/profile.php' );
        $out = ob_get_contents();
        ob_end_clean();
        // ------------------------------------------------------ //

        return $out;       
    }
    
    /**
     * Returns the output for the design index.
     * @return string Returns the output for the design index.
     */
    public static function output_design_index() {
        //get the design profile page
        $design_profile_page = MyStyle_Design_Profile_Page::get_instance();
        
        /* @var $pager \Mystyle_Pager */
        $pager = $design_profile_page->get_pager();
        
        // ---------- Call the view layer ------------------------ //
        ob_start();
        require( MYSTYLE_TEMPLATES . 'design-profile/index.php' );
        $out = ob_get_contents();
        ob_end_clean();
        // ------------------------------------------------------ //

        return $out; 
    }

}