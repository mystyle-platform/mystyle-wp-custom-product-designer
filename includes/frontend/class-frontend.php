<?php

/**
 * MyStyle FrontEnd class.
 * The MyStyle FrontEnd class sets up and controls the MyStyle front end
 * interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_FrontEnd {
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    function __construct() {
        add_action('init', array(&$this, 'mystyle_frontend_init'));
        add_action('woocommerce_before_add_to_cart_button', array(&$this, 'mystyle_woocommerce_before_add_to_cart_button'), 10, 0);
        add_action('woocommerce_after_add_to_cart_button', array(&$this, 'mystyle_woocommerce_after_add_to_cart_button'), 10, 0);
    }
    
    /**
     * Init the MyStyle front end.
     */
    function mystyle_frontend_init() {
        //Add the MyStyle frontend stylesheet to the WP frontend head
        wp_register_style('myStyleFrontEndStylesheet', plugins_url('../../css/frontend.css', __FILE__) );
        wp_enqueue_style('myStyleFrontEndStylesheet');
    }
    
    /**
     * Wrap the section with a mystyle-customizable-product class
     */
    function mystyle_woocommerce_before_add_to_cart_button() {
        $current_product_id = get_the_ID();
        $mystyle_enabled = get_post_meta($current_product_id, "_mystyle_enabled", true);
        
        if($mystyle_enabled == "yes") {
            echo '<div class="mystyle-customizable-product">';
        }
    }
    
    /**
     * Add Customize button after the add to cart button.
     */
    function mystyle_woocommerce_after_add_to_cart_button() {
        $customize_page_id = MyStyle_Customize_Page::get_id();
        $current_product_id = get_the_ID();
        $mystyle_enabled = get_post_meta($current_product_id, "_mystyle_enabled", true);
        
        if($mystyle_enabled == "yes") {
            $customizer_url = add_query_arg('product_id', $current_product_id, get_permalink($customize_page_id));
            
            $out  = '</div><button class="mystyle_customize_button button alt" type="button" onclick="location.href = \'' . $customizer_url . '\'; return false;">Customize</button>';
            $out .= '</div>'; //close the mystyle_customizable wrapper div
            echo $out;
        }
    }

}


