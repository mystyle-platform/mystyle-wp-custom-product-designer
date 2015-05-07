<?php

/**
 * Class for the MyStyle Customizer Shortcode.
 * @todo Create unit tests
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_Customizer_Shortcode {
    
    /**
     * Output the customizer shortcode.
     */
    public static function output() {
        
        //TODO: remove hard coded values.
        $mystyle_app_id = 72;
        $mystyle_template_id = 970;
        $product_id = htmlspecialchars($_GET["product_id"]) ;
        
        $mystyle_url = "http://customizer.ogmystyle.com/" .
                        "?app_id=$mystyle_app_id" . 
                        "&amp;product_id=$mystyle_template_id" . 
                        "&amp;passthru=local_product_id,$product_id";
        
        $out = '<iframe ' .
                    'id="customizer-iframe" ' .
                    'frameborder="0" '.
                    'hspace="0" ' .
                    'vspace="0" ' .
                    'scrolling="no" ' .
                    'src="' . $mystyle_url . '" ' .
                    'width="950" ' .
                    'height="550" ' .
                '></iframe>';
                    
        //Add the mock form (for testing)
        $out .= '<form action="/wordpress/?mystyle-handoff" method="POST">' .
                    '<input type="hidden" name="description" value="Fulfillment Instructions...">' .
                    '<input type="hidden" name="design_id" value="78580">' .
                    '<input type="hidden" name="product_id" value="970">' .
                    '<input type="hidden" name="local_product_id" value="' . $product_id . '">' .
                    '<input type="hidden" name="user_id" value="29057">' .
                    '<input type="hidden" name="price" value="$0.01">' .
                    '<input type="submit" value="Submit Mock">' .
                '</form>';
        
        return $out;
    }

}