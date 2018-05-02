<?php

/**
 * Class for the MyStyle Design Shortcode.
 * 
 * How to use: 
 *  * Add [mystyle_design] to the page.
 *  * Make sure that your url includes a design_id variable 
 *    (example: "http://www.example.com/somepage?design_id=123").
 * 
 * Adds the mystyle design to the page. It pulls the design id from the
 * design_id variable that needs to be in the url for the shortcode to work. 
 * 
 * @package MyStyle
 * @since 3.4.0
 */
abstract class MyStyle_Design_Shortcode {

    /**
     * Output the design shortcode.
     */
    public static function output() {
        $out = '';
        
        $mystyle_frontend = MyStyle_FrontEnd::get_instance();
        
        //-------------------- Handle Exceptions ----------------------//
        $ex = $mystyle_frontend->get_exception();
        if( $ex != null ) { 
            throw $ex;
        } else {
            // --------------- Valid Requests ------------------------- //
            if( $mystyle_frontend->get_design() != null ) {
                $out = self::output_design();
            } else {
                // Fail silently. This can happen in the admin or if the
                // design_id isn't set in the url.
                //throw new MyStyle_Bad_Request_Exception(
                //        'Design not found'
                //    );
            }
        }
        
        return $out;
    }
    
    /**
     * Returns the output for a design.
     * @return string
     */
    public static function output_design() {
        $mystyle_frontend = MyStyle_FrontEnd::get_instance();
        
        // ------------- set the template variables -------------------//
        $design = $mystyle_frontend->get_design();
        
        $renderer_url = 
            'https://www.mystyleplatform.com/' . 
            'tools/render/?mode=customerpreview' . 
            '&design_url=' . $design->get_design_url();
        
        // ---------- Call the view layer ------------------------ //
        ob_start();
        require( MYSTYLE_TEMPLATES . 'design.php' );
        $out = ob_get_contents();
        ob_end_clean();
        // ------------------------------------------------------ //

        return $out;       
    }

}