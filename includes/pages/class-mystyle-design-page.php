<?php

/**
 * The MyStyle Design Paage Singleton class for displaying random designs shortcode.
 *
 * @package MyStyle
 * @since 3.14.3
 */

/**
 * MyStyle_DesignPage class.
 */
class MyStyle_DesignPage {
    
    /**
	 * Singleton class instance.
	 *
	 * @var MyStyle_DesignPage
	 */
	private static $instance;
    
    /**
	 * Stores the current user (when the class is instantiated as a singleton).
	 *
	 * @var WP_User
	 */
	private $user;
    
	/**
	 * Stores the current session (when the class is instantiated as a
	 * singleton).
	 *
	 * @var MyStyle_Session
	 */
	private $session;
    
	/**
	 * Pager for the design profile index.
	 *
	 * @var MyStyle_Pager
	 */
	private $pager;
    
	/**
	 * Stores the currently thrown exception (if any) (when the class is
	 * instantiated as a singleton).
	 *
	 * @var MyStyle_Exception
	 */
	private $exception;

	/**
	 * Stores the current ( when the class is instantiated as a singleton ) status
	 * code.  We store it here since php's http_response_code() function wasn't
	 * added until php 5.4.
	 *
	 * See: http://php.net/manual/en/function.http-response-code.php
	 *
	 * @var int
	 */
	private $http_response_code;
    
    public function __construct() {
        add_filter( 'body_class', array( &$this, 'filter_body_class' ) ); 
	}
    
    /**
	 * Filter the body class output. Adds a "mystyle-design-profile" class if
	 * the page is the Design_Profile page.
	 *
	 * @param array $classes An array of classes that are going to be outputed
	 * to the body tag.
	 * @return array Returns the filtered classes array.
	 */
	public function filter_body_class( $classes ) {
        global $post ;
        
        if( has_shortcode( $post->post_content, 'mystyle_design')) {
            $classes[] = 'mystyle-design-profile';
        }
		
		return $classes;
	}
    
    /**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_DesignPage Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}