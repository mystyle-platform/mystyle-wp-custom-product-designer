<?php

/**
 * Class for working with the MyStyle Customize page.
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_Customize_Page {
    
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
     * @param integer $design
     * @return string Returns a link that can be used to reload a design.
     * @todo Add unit testing.
     */
    public static function get_design_url( MyStyle_Design $design ) {
        $passthru = array(
            'post' => array (
                'quantity' => 1,
                'add-to-cart' => $design->get_product_id()
            )
        );
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

}