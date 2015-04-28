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
        
        //$out = '<iframe src="http://www.mystyleplatform.com" width="600" height="300"></iframe>';
        
        return $out;
    }

}