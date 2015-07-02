<?php

/**
 * Class for the MyStyle Customizer Shortcode.
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_Customizer_Shortcode {
    
    /**
     * Output the customizer shortcode.
     */
    public static function output() {
        
        $mystyle_app_id = MyStyle_Options::get_api_key();
        
        if( ! isset( $_GET['product_id'] ) ) {
            $out = '<h2>You\'ll need to select a product to customize first!</h2>';
            $out .= '<p><a href="' . get_home_url() . '">Home</a>';
            return $out;
        }
        
        $product_id = htmlspecialchars( $_GET['product_id'] ) ;
        $mystyle_template_id = get_post_meta( $product_id, '_mystyle_template_id', true );
        
        $settings = array();
        $settings['redirect_url'] = MyStyle_Handoff::get_url();
        $settings['email_skip'] = 0;
        //if ($CURRENT_USER) {
            //$settings['email_skip'] = 1;
        //}
        $encoded_settings = base64_encode( json_encode( $settings ) );
        
        $customizer_query_string =
                        "?app_id=$mystyle_app_id" . 
                        "&amp;product_id=$mystyle_template_id" . 
                        "&amp;settings=$encoded_settings" . 
                        "&amp;passthru=local_product_id,$product_id";
        
        //---------- variables for use by the view layer ---------
        $customizer_url = 'http://customizer.ogmystyle.com/' . $customizer_query_string;
        $mobile_customizer_url = 'http://customizer-js.ogmystyle.com/' . $customizer_query_string;
        
        $force_mobile = 0;
        if ( isset( $_GET['force_mobile'] ) ) {
            $force_mobile = 1;
        }
        
        // ---------- Call the view layer ------- //
        ob_start();
        require( MYSTYLE_TEMPLATES . 'customizer.php' );
        $out = ob_get_contents();
        ob_end_clean();
        // -------------------------------------- //
                    
        if( ( defined( 'MYSTYLE_ENABLE_MOCK_SUBMIT_BUTTON' ) ) && ( MYSTYLE_ENABLE_MOCK_SUBMIT_BUTTON == true ) ) {
            //Add the mock form (for testing)
            $out .= '<form action="/wordpress/?mystyle-handoff" method="POST">' .
                        '<input type="hidden" name="description" value="Fulfillment Instructions...">' .
                        '<input type="hidden" name="design_id" value="78580">' .
                        '<input type="hidden" name="product_id" value="' . $mystyle_app_id . '">' .
                        '<input type="hidden" name="local_product_id" value="' . $product_id . '">' .
                        '<input type="hidden" name="user_id" value="29057">' .
                        '<input type="hidden" name="price" value="$0.01">' .
                        '<input type="submit" value="Submit Mock">' .
                    '</form>';
        }
        
        return $out;
    }

}