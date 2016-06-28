<?php

/**
 * Class for working with the MyStyle Design Profile page.
 * @package MyStyle
 * @since 1.3.2
 */
abstract class MyStyle_Design_Profile_Page {
    
    /**
     * Function to create the page.
     * @return number Returns the page id of the Design Profile page.
     */
    public static function create() {
        //Create the Design Profile page
        $design_profile_page = array(
            'post_title'   => 'Design Profile',
            'post_name'    => 'designs',
            'post_content' => '[mystyle_design_profile]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $page_id = wp_insert_post( $design_profile_page );
        
        //Store the design profile page's id in the database
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] = $page_id;
        $updated = update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        if( ! $updated ) {
            wp_delete_post($page_id);
            throw new MyStyle_Exception( __( 'Could not store page id.', 'mystyle' ), 500 );
        }
        
        return $page_id;
    }
    
    /**
     * Function to get the id of the Design Profile page.
     * @return number Returns the page id of the Design Profile page.
     * @throws MyStyle_Exception
     */
    public static function get_id() {
        //Get the page id of the Design Profile page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( ! isset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] ) ) {
            throw new MyStyle_Exception( __( 'Design Profile Page is Missing! Please use the "Fix Design Profile Page Tool" on the MyStyle Settings page to fix.', 'mystyle' ), 404 );
        }
        $page_id = $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ];
        
        return $page_id;
    }
    
    /**
     * Function to determine if the page exists.
     * @return boolean Returns true if the page exists, otherwise false.
     * @throws MyStyle_Exception
     */
    public static function exists() {
        $exists = false;
        
        //Get the page id of the Design Profile page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( isset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] ) ) {
            $exists = true;
        }
        
        return $exists;
    }
    
    /**
     * Function to delete the Design Profile page.
     */
    public static function delete() {
        //Get the page id of the Design Profile page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $page_id = $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ];
        
        //Remove the page id from the database.
        unset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] );
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        //Delete the page from WordPress
        wp_delete_post( $page_id );
    }
    
    /**
     * Builds a url to the Design Profile page including url paramaters to load
     * the passed design.
     * @param integer $design
     * @return string Returns a link that can be used to view the design.
     * @global WP_Rewrite $wp_rewrite
     */
    public static function get_design_url( MyStyle_Design $design ) {
        global $wp_rewrite;
        
        if ( isset( $wp_rewrite->page_structure ) && ( $wp_rewrite->page_structure != '' ) ) {
            $url = get_permalink( self::get_id() ) . '/' . $design->get_design_id();    
        } else {
            $args = array(
                'design_id' => $design->get_design_id(),
            );
            $url = add_query_arg( $args , get_permalink( self::get_id() ) );
        }
        
        return $url;
    }
    
    /**
     * Attempt to fix the Design Profile page. This may involve creating, 
     * re-creating or repairing it.
     * @return Returns a message describing the outcome of fix operation.
     * @todo: Add unit testing
     */
    public static function fix() {
        $message = '<br/>';
        $status = 'Design Profile page looks good, no action necessary.';
        //Get the page id of the Design Profile page
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        if( isset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] ) ) {
            $post_id = $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ];
            $message .= 'Found the stored ID of the Design Profile page...<br/>';
            
            /* @var $post \WP_Post */
            $post = get_post( $post_id );
            if( $post != null ) {
                $message .= 'Design Profile page exists...<br/>';
                
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
                        $status = 'Design Profile page fixed!<br/>';
                    }
                } else {
                    $message .= 'Design Profile page is published...<br/>';
                }
                
                //Check for the shortcode
                if( strpos( $post->post_content, '[mystyle_design_profile]' ) === false ) {
                    $message .= 'The mystyle_designs shortcode not found in the page content, adding...<br/>';
                    $post->post_content .= '[mystyle_designs]';
                    
                    /* @var $error \WP_Error */ 
                    $errors = wp_update_post( $post, true );
                    						  
                    if( is_wp_error( $errors ) ) {
                        foreach( $errors as $error ) {
                            $messages .= $error . '<br/>';
                            $status .= 'Fix errored out :(<br/>';
                        }
                    } else {
                        $message .= 'Shortcode added.<br/>';
                        $status = 'Design Profile page fixed!<br/>';
                    }
                } else {
                    $message .= 'Design Profile page has mystyle_designs shortcode...<br/>';
                }
                
            } else { //Post not found, recreate
                $message .= 'Design Profile page appears to have been deleted, recreating...<br/>';
                try {
                    $post_id = self::create();
                    $status = 'Design Profile page fixed!<br/>';
                } catch(\Exception $e) {
                    $status = 'Error: ' . $e->getMessage();
                }
                
            }
        } else { //ID not available, create
            $message .= 'Design Profile page missing, creating...<br/>';
            self::create();
            $status = 'Design Profile page fixed!<br/>';
        }
        
        $message .= $status;

        return $message;
    }

}