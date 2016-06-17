<?php

/**
 * Class for the MyStyle Design Profile Shortcode.
 * @package MyStyle
 * @since 1.3.2
 */
abstract class MyStyle_Design_Profile_Shortcode {

    /**
     * Output the design profile shortcode.
     */
    public static function output() {
        
        try {
            //-------get the design id from the url and validate it ------- //
            
            //try the query vars (ex: &design_id=10)
            $design_id = get_query_var( 'design_id' );
            if( empty( $design_id ) ) {
                //try at /designs/10
                $path = $_SERVER["REQUEST_URI"];
                $design_id = substr( $path, strpos( $path, '/designs/' ) + 9 );
                $design_id = str_replace( '/', '', $design_id );
                if( ! preg_match( '/^[\d]+$/', $design_id ) ) {
                    throw new Exception('Design not found.');
                }
            }

            $design = MyStyle_DesignManager::get( $design_id );

            if( $design == null ) {
                throw new Exception('Design not found.');
            }

            // ---------- Call the view layer ------- //
            ob_start();
            require( MYSTYLE_TEMPLATES . 'design-profile.php' );
            $out = ob_get_contents();
            ob_end_clean();
            // -------------------------------------- //

            return $out;
            
            
        } catch (Exception $e) {
            $out = '<p>' . $e->getMessage() . '</p>';
            
            return $out;
        }

    }

}