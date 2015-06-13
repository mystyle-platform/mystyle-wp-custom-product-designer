<?php

require_once(MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-product.php');

/**
 * The MyStyleWooCommerceAdminProductTest class includes tests for testing the
 * MyStyle_WooCommerce_Admin_Product class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleWooCommerceAdminProductTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        global $wp_filter;
        
        $mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['admin_init'] );
        $this->assertContains( 'admin_init', $function_names );
    }

    /**
     * Test the admin_init() function
     */    
    public function test_admin_init() {
        global $wp_filter;
        
        $mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();
        $mystyle_wc_admin_product->admin_init();
        
        //Assert that the add_product_data_tab function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_product_write_panel_tabs'] );
        $this->assertContains( 'add_product_data_tab', $function_names );
        
        //Assert that the add_mystyle_data_panel function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_product_write_panels'] );
        $this->assertContains( 'add_mystyle_data_panel', $function_names );
        
        //Assert that the process_mystyle_data_panel function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_process_product_meta'] );
        $this->assertContains( 'process_mystyle_data_panel', $function_names );
    }
    
    /**
     * Test the add_product_data_tab function
     */    
    public function test_add_product_data_tab() {
        $mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();
        
        //Assert that the data tab was rendered
        ob_start();
        $mystyle_wc_admin_product->add_product_data_tab();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( '<li class="mystyle_product_tab mystyle_product_options">', $outbound );
    }
    
    /**
     * Test the add_mystyle_data_panel function
     */    
    public function test_add_mystyle_data_panel() {
        //TODO: Need to be able to set or mock the global $post variable
        /*
        $mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();
        
        //Assert that the data panel was rendered
        ob_start();
        $mystyle_wc_admin_product->add_mystyle_data_panel();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( '<div id="mystyle_product_data" class="panel woocommerce_options_panel">', $outbound );
         */
    }
    
    /**
     * Test the process_mystyle_data_panel function
     */   
    public function process_mystyle_data_panel() {
        //TODO
    }
    
}
