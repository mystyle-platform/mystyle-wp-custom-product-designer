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
        $out = '<iframe id="customizer-iframe" frameborder="0" hspace="0" vspace="0" scrolling="no" src="http://customizer.ogmystyle.com/?app_id=72&product_id=970" width="950" height="550;"></iframe>';
        $product_id = htmlspecialchars($_GET["product_id"]) ;
        //$out = '<iframe src="http://www.mystyleplatform.com" width="600" height="300"></iframe>';

        $out .= '<form action="/wordpress/?mystyle-handoff" method="POST">' .
                    '<input type="hidden" name="description" value="Fulfillment Instructions...">' .
                    '<input type="hidden" name="print_url" value="http://www.w3schools.com/html/html5.gif">' .
                    '<input type="hidden" name="web_url" value="http://www.w3schools.com/html/html5.gif">' .
                    '<input type="hidden" name="thumb_url" value="http://www.w3schools.com/html/html5.gif">' .
                    '<input type="hidden" name="design_id" value="11111">' .
                    '<input type="hidden" name="product_id" value="' . $product_id . '">' .
                    '<input type="hidden" name="user_id" value="3333">' .
                    '<input type="hidden" name="price" value="$0.01">' .
                    '<input type="submit" value="Submit Mock">' .
                '</form>';
        
        return $out;
    }

}