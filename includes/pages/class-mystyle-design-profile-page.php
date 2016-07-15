<?php

/**
 * Class for working with the MyStyle Design Profile page.
 * 
 * This class has both static functions and hooks as well as the ability to be
 * instantiated as a singleton instance with various methods.
 * 
 * @package MyStyle
 * @since 1.4.0
 */
class MyStyle_Design_Profile_Page {
    
    /**
     * Singleton class instance
     * @var MyStyle_Design_Profile_Page
     */
    static $instance;
    
    /**
     * Stores the currently loaded design (when the class is instantiated as a
     * singleton).
     * @var MyStyle_Design 
     */
    private $design;
    
    /**
     * Stores the currently thrown exception (if any) (when the class is
     * instantiated as a singleton).
     * @var MyStyle_Exception 
     */
    private $exception;
    
    /**
     * Stores the current (when the class is instantiated as a singleton) status
     * code.  We store it here since php's http_response_code() function wasn't
     * added until php 5.4.
     * see: http://php.net/manual/en/function.http-response-code.php
     * @var int 
     */
    private $http_response_code;
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->http_response_code = 200;
        
        add_action( 'template_redirect', array( &$this, 'init' ) );
    }
    
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
     * Static function to initialize the page when it is requested from the
     * front end.  
     * 
     * This function is being hooked into "template_redirect" instead of "init"
     * because we want to wait until the current post has been loaded.
     * 
     * The function bails if the loaded page is not the Design Profile page.
     * 
     * If we are serving the Design Profile page, this function pulls the
     * requested design and loads it into the singleton instance of this class
     * for use by functions that occur downstream (such as the 
     * mystyle_design_profile shortcode).  
     * 
     * It loads early enough to set headers and status codes.  If an exception
     * is thrown, it catches it and attaches it to the singleton instance for
     * for further processing downstream.
     * 
     * @throws MyStyle_Not_Found_Exception
     */
    public static function init() {
        
        //only run if we are currently serving the design profile page
        if( self::is_current_post() ) { 
            try {
                //get the design from the url, if it's not found, this function
                //throws an exception.
                $design_id = self::get_design_id_from_url();

                $design = MyStyle_DesignManager::get( $design_id );

                if( $design != null ) {
                    
                    if( $design->get_access() === MyStyle_Access::$PRIVATE ) {
                        //TODO: confirm that the user has access here
                    }
                    
                    //set the current design in the singleton instance
                    MyStyle_Design_Profile_Page::get_instance()->set_design( $design );
                } else {
                    //note: this is caught at the bottom of this function
                    throw new MyStyle_Not_Found_Exception( 'Design not found.' );
                }

            // When an exception is thrown, set the status code and set the
            // exception in the singleton instance, it will later be used by
            // the shortcode and view layer
            } catch ( MyStyle_Not_Found_Exception $ex ) {
                $response_code = 404;
                status_header( $response_code );
                
                $design_profile_page = MyStyle_Design_Profile_Page::get_instance();
                $design_profile_page->set_exception( $ex );
                $design_profile_page->set_http_response_code( $response_code );
            }
        }
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
     * Determines whether or not this page is the current page/post.
     * @return boolean Returns true if this page is the current page/post,
     * otherwise returns false.
     */
    public static function is_current_post() {
        $ret = false;
        
        try {
            $current_post = get_post();
            if( ( ! empty( $current_post) ) &&
                ( $current_post->ID == self::get_id() ) )
            {
                $ret = true;
            }
        } catch( Exception $ex ) {
            //do nothing (return false)
        }
        
        return $ret;
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
     * Static function that builds a url to the Design Profile page including 
     * url paramaters to load the passed design.
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
     * Gets the design id from the url. If it can't find the design id in the
     * url, this function throws a MyStyle_Not_Found_Exception.
     * 
     * @return int Returns the design id from the url
     * @throws MyStyle_Not_Found_Exception
     */
    public static function get_design_id_from_url() {
        //try the query vars (ex: &design_id=10)
        $design_id = get_query_var( 'design_id' );
        if( empty( $design_id ) ) {
            //try at /designs/10
            $path = $_SERVER["REQUEST_URI"];
            $pattern = '/^.*\/designs\/([\d]+)/';
            if( preg_match($pattern, $path, $matches) ) {
                $design_id = $matches[1];
            } else {
                //note: this is caught at the bottom of this function
                throw new MyStyle_Not_Found_Exception( 'Design not found.' );
            }
        }
        
        return $design_id;
    }
    
    /**
     * Sets the current design.
     * @param MyStyle_Design $design The design to set as the current design.
     */
    public function set_design( MyStyle_Design $design ) {
        $this->design = $design;
    }
    
    /**
     * Gets the current design.
     * @return MyStyle_Design Returns the currently loaded MyStyle_Design.
     */
    public function get_design() {
        return $this->design;
    }
    
    /**
     * Sets the current exception.
     * @param MyStyle_Exception $exception The exception to set as the currently
     * thrown exception. This is used by the shortcode and view layer.
     */
    public function set_exception( MyStyle_Exception $exception ) {
        $this->exception = $exception;
    }
    
    /**
     * Gets the current exception.
     * @return MyStyle_Exception Returns the currently thrown MyStyle_Exception
     * if any. This is used by the shortcode and view layer.
     */
    public function get_exception() {
        return $this->exception;
    }
    
    /**
     * Sets the current http response code.
     * @param int $http_response_code The http response code to set as the 
     * currently set response code. This is used by the shortcode and view 
     * layer.  We set it as a variable since it is difficult to retrieve in 
     * php < 5.4.
     */
    public function set_http_response_code( $http_response_code ) {
        $this->http_response_code = $http_response_code;
    }
    
    /**
     * Gets the current http response code.
     * @return int Returns the current http response code. This is used by the
     * shortcode and view layer.
     */
    public function get_http_response_code() {
        if(function_exists('http_response_code')) {
            return http_response_code();
        } else {
            return $this->http_response_code;
        }
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
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Design_Profile_Page Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle_Design_Profile_Page Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}