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
        $out = '<iframe src="http://www.mystyleplatform.com" width="600" height="300"></iframe>';
        return $out;
    }

}