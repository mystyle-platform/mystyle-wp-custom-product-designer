<?php

/**
 * Class for working with the MyStyle Customize page.
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_Customize_Page {
    
    /**
     * Singleton class instance
     * @var MyStyle_Customize_Page
     */
    private static $instance;
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
        add_filter( 'body_class', array( &$this, 'filter_body_class' ), 10, 1 );
    }
    
    /**
     * Function to create the page.
     * @return number Returns the page id of the Customize page.
     */
    public static function create() {
        //Create the Customize page
        $customize_page = array(
            'post_title'   => 'Customize',
            'post_content' => '[mystyle_customizer]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $page_id = wp_insert_post( $customize_page );
        
        //Store the customize page's id in the database
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] = $page_id;
        $updated = update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        if( ! $updated ) {
            wp_delete_post($page_id);
            throw new MyStyle_Exception( __( 'Could not store page id.', 'mystyle' ), 500 );
        }
        
        return $page_id;
    }
    
    /**
     * Function to get the id of the customize page.
     * @return number Returns the page id of the Customize page.
     * @throws MyStyle_Exception
     */
    public static function get_id() {
        //Get the page id of the Customize page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( ! isset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] ) ) {
            throw new MyStyle_Exception( __( 'Customize Page is Missing! Please use the "Fix Customize Page Tool" on the MyStyle Settings page to fix.', 'mystyle' ), 404 );
        }
        $page_id = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];
        
        return $page_id;
    }
    
    /**
     * Function to determine if the page exists.
     * @return boolean Returns true if the page exists, otherwise false.
     * @throws MyStyle_Exception
     */
    public static function exists() {
        $exists = false;
        
        //Get the page id of the Customize page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( isset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] ) ) {
            $exists = true;
        }
        
        return $exists;
    }
    
    /**
     * Function to delete the Customize page.
     */
    public static function delete() {
        //Get the page id of the Customize page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $page_id = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];
        
        //Remove the page id from the datababase.
        unset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] );
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        //Delete the page from WordPress
        wp_delete_post( $page_id );
    }
    
    /**
     * Builds a url to the customize page including url paramaters to load
     * the passed design.
     * @param MyStyle_Design $design
     * @param integer $cart_item_key
     * @param array $passthru Any passthru data to include in the url. If non is
     * passed, defaults are used.
     * @return string Returns a link that can be used to reload a design.
     */
    public static function get_design_url( MyStyle_Design $design, $cart_item_key = null, $passthru = null ) {
        
        if($passthru == null) {
            $passthru = array(
                'post' => array (
                    'quantity' => 1,
                    'add-to-cart' => $design->get_product_id()
                )
            );
        }
        
        if( $cart_item_key != null ) {
            $passthru['cart_item_key'] = $cart_item_key;
        }
        
        $passthru_encoded = base64_encode( json_encode( $passthru ) );
        $customize_args = array(
            'product_id' => $design->get_product_id(),
            'design_id' => $design->get_design_id(),
            'h' => $passthru_encoded,
        );
        
        $customizer_url = add_query_arg( $customize_args , get_permalink( self::get_id() ) );
        
        return $customizer_url;
    }
    
    /**
     * Gets the url for the customizer for the passed product.
     * @param string $product The product whose customizer URL you want to get.
     * This product should be marked customizable.
     * @return string Returns the customizer url for the product.
     */
    public static function get_product_url( $product ) {
        //var_dump($product);
        
        $mystyle_product = new \MyStyle_Product( $product );
        $product_id = $mystyle_product->get_id();
        
        $customize_page_id = self::get_id();
            
        //build the url to the customizer including the poduct_id
        $customizer_url = add_query_arg( 'product_id', $product_id, get_permalink( $customize_page_id ) );
            
        //Add the passthru data to the url
        $passthru = array();
        $passthru['post'] = array();
        $passthru['post']['quantity'] = 1;
        $passthru['post']['add-to-cart'] = $product_id;
        $passthru_encoded = base64_encode( json_encode( $passthru ) );
        $customizer_url = add_query_arg( 'h', $passthru_encoded, $customizer_url );
        
        return $customizer_url;
    }
    
    /**
     * Filter the post title. Hide the title if on the Customize page and the
     * customize_page_title_hide setting is set to true. 
     * @param string $title The title of the post.
     * @param type $id The id of the post.
     * @return string Returns the filtered title.
     */
    public function filter_title( $title, $id = null ) {
        try {
            if( 
                ( ! empty( $id ) ) &&
                ( $id == MyStyle_Customize_Page::get_id() ) &&
                ( self::hide_title() ) &&
                ( $id == get_the_ID() ) &&
                ( in_the_loop() )
              )
            {
                $title = '';
            }
        } catch( MyStyle_Exception $e ) {
            //this exception may be thrown if the Customize Page is missing.
            //For this function, that is okay, just continue.
        }

        return $title;
    }
    
    /**
     * Filter the body class output.  Adds a "mystyle-customize" class if the
     * page is the Customize page.
     * @param array $classes An array of classes that are going to be outputed
     * to the body tag.
     * @return array Returns the filtered classes array.
     */
    public function filter_body_class( $classes ) {
        global $post;
        
        try {
            if( $post != null ) {
                if( 
                    ( $post->ID == MyStyle_Customize_Page::get_id() ) &&
                    ( isset( $_GET['product_id'] ) )
                  )
                {
                    $classes[] = 'mystyle-customize';
                }
            }
        } catch( MyStyle_Exception $e ) {
            //this exception may be thrown if the Customize Page is missing.
            //For this function, that is okay, just continue.
        }

	return $classes;
    }
    
    /**
     * Function that gets the value of the customize_page_title_hide setting.
     * @return boolean Returns true if the customize_page_title_hide setting is
     * enabled, otherwise returns false.
     */
    static function hide_title() {
        return MyStyle_Options::is_option_enabled(
                        MYSTYLE_OPTIONS_NAME, 
                        'customize_page_title_hide'
                    );
    }
    
    /**
     * Function that gets the value of the 
     * customize_page_disable_viewport_rewrite setting.
     * @return boolean Returns true if the customize_page_title_hide setting is 
     * enabled, otherwise returns false.
     */
    static function disable_viewport_rewrite() {
        return MyStyle_Options::is_option_enabled(
                        MYSTYLE_OPTIONS_NAME, 
                        'customize_page_disable_viewport_rewrite'
                    );
    }
    
    /**
     * Attempt to fix the Customize page. This may involve creating, re-creating
     * or repairing it.
     * @return Returns a message describing the outcome of fix operation.
     * @todo: Add unit testing
     */
    public static function fix() {
        $message = '<br/>';
        $status = 'Customize page looks good, no action necessary.';
        //Get the page id of the Customize page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( isset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] ) ) {
            $post_id = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];
            $message .= 'Found the stored ID of the Customize page...<br/>';
            
            /* @var $post \WP_Post */
            $post = get_post( $post_id );
            if( $post != null ) {
                $message .= 'Customize page exists...<br/>';
                
                //Check the status
                if( $post->post_status != 'publish') {
                    $message .= 'Status was "' . $post->post_status . '", changing to "publish"...<br/>';
                    $post->post_status = 'publish';
                    
                    /* @var $error \WP_Error */ 
                    $errors = wp_update_post( $post, true );
                    						  
                    if( is_wp_error( $errors ) ) {
                        foreach( $errors as $error ) {
                            $messages .= $error . '<br/>';
                            $status .= 'Fix errored out :(<br/>';
                        }
                    } else {
                        $message .= 'Status updated.<br/>';
                        $status = 'Customize page fixed!<br/>';
                    }
                } else {
                    $message .= 'Customize page is published...<br/>';
                }
                
                //Check for the shortcode
                if( strpos( $post->post_content, '[mystyle_customizer]' ) === false ) {
                    $message .= 'The mystyle_customizer shortcode not found in the page content, adding...<br/>';
                    $post->post_content .= '[mystyle_customizer]';
                    
                    /* @var $error \WP_Error */ 
                    $errors = wp_update_post( $post, true );
                    						  
                    if( is_wp_error( $errors ) ) {
                        foreach( $errors as $error ) {
                            $messages .= $error . '<br/>';
                            $status .= 'Fix errored out :(<br/>';
                        }
                    } else {
                        $message .= 'Shortcode added.<br/>';
                        $status = 'Customize page fixed!<br/>';
                    }
                } else {
                    $message .= 'Customize page has mystyle_customizer shortcode...<br/>';
                }
                
            } else { //Post not found, recreate
                $message .= 'Customize page appears to have been deleted, recreating...<br/>';
                try {
                    $post_id = self::create();
                    $status = 'Customize page fixed!<br/>';
                } catch(\Exception $e) {
                    $status = 'Error: ' . $e->getMessage();
                }
                
            }
        } else { //ID not available, create
            $message .= 'Customize page missing, creating...<br/>';
            self::create();
            $status = 'Customize page fixed!<br/>';
        }
        
        $message .= $status;

        return $message;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Customize_Page Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle_Customize_Page Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}