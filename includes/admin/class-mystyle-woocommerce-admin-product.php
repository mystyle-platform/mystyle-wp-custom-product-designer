<?php

/**
 * MyStyle WooCommerce Admin Product class.
 * 
 * The MyStyle WooCommerce Admin Product class hooks MyStyle into the 
 * WooCommerce Product admin interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_WooCommerce_Admin_Product {
    
    /**
     * Constructor, constructs the class and registers hooks.
     */
    public function __construct() {
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
    }
    
    /**
     * Init the mystyle woocommerce admin
     */
    function admin_init() {
        add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'add_product_data_tab' ) );
        add_action( 'woocommerce_product_write_panels', array( &$this, 'add_mystyle_data_panel' ) );
        add_action( 'woocommerce_process_product_meta', array( &$this, 'process_mystyle_data_panel' ) );
    }
    
    /**
     * Add a MyStyle tab to the product options tab set.
     */
    public static function add_product_data_tab() {
        echo '<li class="mystyle_product_tab mystyle_product_options"><a href="#mystyle_product_data">MyStyle</a></li>';
    }
    
    /**
     * Create the content of the MyStyle product options tab.
     * @global WP_Post $post The post that is currently being edited
     */
    public static function add_mystyle_data_panel() {
        global $post;
        
        $mystyle_enabled = get_post_meta( $post->ID, '_mystyle_enabled', true );
        $template_id = get_post_meta( $post->ID, '_mystyle_template_id', true );
        
        ?>
            <div id="mystyle_product_data" class="panel woocommerce_options_panel">
                <div class="options_group">
                    <?php 
                        woocommerce_wp_checkbox( 
                            array( 
                                'id' => '_mystyle_enabled',
                                'label' => __( 'Make Customizable?', 'mystyle' ),
                                'desc_tip'    => 'true',
                                'description' => __( 'Enable this option to make the product customizable.', 'mystyle' ),
                                'value'       => $mystyle_enabled,
                            )
                        );
                        woocommerce_wp_text_input( 
                            array( 
                                'id'          => '_mystyle_template_id', 
                                'label'       => __( 'Template Id', 'mystyle' ), 
                                'placeholder' => '',
                                'desc_tip'    => 'true',
                                'description' => __( 'Enter the MyStyle Template Id for the product. For an example template, you can use Template Id 70.', 'woocommerce' ),
                                'value'       => $template_id,
                            )
			);
                    ?>
                    <p class="description" style="margin-left: 2em;">
                        Need a template? Check out our <a href="http://www.mystyleplatform.com/mystyle-product-catalog/" title="MyStyle Product Catalog" target="_blank">Product Catalog</a>.
                    </p>
                </div>
            </div>
        <?php
    }
    
    /**
     * Process the mystyle tab options when a post is saved
     * @param integer $post_id The id of the post that is being saved.
     */
    public static function process_mystyle_data_panel( $post_id ) {
        update_post_meta( $post_id, '_mystyle_enabled', ( isset( $_POST['_mystyle_enabled'] ) && $_POST['_mystyle_enabled'] ) ? 'yes' : 'no' );
        update_post_meta( $post_id, '_mystyle_template_id', $_POST['_mystyle_template_id'] );
    }
 
}


