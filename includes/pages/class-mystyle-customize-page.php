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
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
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
            throw new MyStyle_Exception( __( 'Customize Page is Missing', 'mystyle' ), 404 );
        }
        $page_id = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];
        
        return $page_id;
    }
    
    /**
     * Function to determine if the page exists.
     * @return boolean Returns true if the page exists, otherwise false.
     * @throws MyStyle_Exception
     * @todo Add unit testing for this function
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

}