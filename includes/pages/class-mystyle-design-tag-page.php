<?php

/**
 * The MyStyle My Designs Singleton class has hooks for working with the WooCommerce Design Tag page.
 *
 * @package MyStyle
 * @since 3.14.10
 */

/**
 * MyStyle_MyDesigns class.
 */
class MyStyle_DesignTag_Page {
    
    /**
	 * Singleton class instance.
	 *
	 * @var MyStyle_DesignTag_Page
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
        $this->http_response_code = 200 ;
        
        add_action( 'template_redirect', array( &$this, 'alter_query'), 20 );
        //add_action( 'loop_start', array( &$this, 'loop_start' )) ;

	}
    
    public function alter_query() {
        
        global $wp_query ;
        
        
        //$wp_query->request = "SELECT * FROM wp_mystyle_designs" ;
        
        echo '<pre>' ; var_dump($wp_query) ; echo '</pre>' ;
        
        
        $wp_query = new WP_Query( array(
            'post_type'         => 'post',
            'post_status'       => 'publish'
        ) );
        
    }
    
    
    public function loop_start( $array ) {
        
        
        global $wp_query;
        //checks the context before altering the query
        
        
        $design = new stdClass() ;
                
        $design->ID = 213123 ;
        $design->post_author = '123' ;
        $design->post_name = 'Test' ;
        $design->post_type = 'Design' ;
        $design->post_title = 'Test Design' ;
        $design->post_content = 'Test' ;
        
        $array->posts = array($design) ;
        
        //$query->set('posts', array($design) ) ;
        //$query->set('found_posts', true) ;
        //$query->set('is_post_page', true) ;
        
        //echo '<pre>' ; var_dump($wp_query) ; echo '</pre>' ;// die() ;
        //remove_all_actions ( '__after_loop');
        
        
    }
    
    public function init() {
        
        global $wp_query ;
        
        if(isset($wp_query->query_vars['my-designs'])) {
            $design_profile_page = self::get_instance();

            // Set the user.
            /* @var $user \WP_User phpcs:ignore */
            $user = wp_get_current_user();
            $design_profile_page->set_user( $user );

            // Set the session.
            /* @var $session \MyStyle_Session phpcs:ignore */
            $session = MyStyle()->get_session();
            $design_profile_page->set_session( $session );

            $design_profile_page->init_user_index_request();
        }
	}
    
    /**
    *register new endpoint for WC My Account page
    **/
    public function design_endpoints() {
        add_rewrite_endpoint( 'my-designs', EP_ROOT | EP_PAGES );
    }
    
    /**
    * register query variables
    **/
    public function design_query_vars( $vars ) {
        $vars[] = 'my-designs';

        return $vars;
    }
    
    /**
    * Add menu item to My Account Page
    **/
    public function my_account_menu_items( $items ) {
        $new_items = array() ;
        $new_items['my-designs'] = __( 'My Designs', 'woocommerce' );

        return $this->insert_after_helper($items, $new_items, 'dashboard') ;
    }
    
    /**
    * Add My Designs breadcrumb
    **/
    public function breadcrumbs( $defaults ) {
        $defaults[] = 'My Designs' ;
        return $defaults ;
    }
    
    /**
     * Custom help to add new items into an array after a selected item.
     *
     * @param array $items
     * @param array $new_items
     * @param string $after
     * @return array
     */
    function insert_after_helper( $items, $new_items, $after ) {
        // Search for the item position and +1 since is after the selected item key.
        $position = array_search( $after, array_keys( $items ) ) + 1;

        // Insert the new item.
        $array = array_slice( $items, 0, $position, true );
        $array += $new_items;
        $array += array_slice( $items, $position, count( $items ) - $position, true );

        return $array;
    }
    
    /**
    * Flush rewrite rules on Plugin activation and deactivation
    **/
    public function flush_rewrite_rules() {
        add_rewrite_endpoint( 'my-designs', EP_ROOT | EP_PAGES );
        flush_rewrite_rules();
    }
    
    /**
    * Display user designs list
    **/
    public function designs_list() {
        $design_profile_page = MyStyle_MyDesigns::get_instance();
        
		/* @var $pager \Mystyle_Pager phpcs:ignore */
		$pager = $design_profile_page->get_pager();
        
		// ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'design-profile/index.php';
		$out = ob_get_contents();
		ob_end_clean();


		print $out;
    }
    
    
	/**
	 * Filter the post title.
	 *
	 * @param string $title The title of the post.
	 * @param type   $id The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
        global $wp_query;

        $is_endpoint = isset( $wp_query->query_vars['my-designs'] );

        if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
            // New page title.
            $title = __( 'My Designs', 'woocommerce' );

            remove_filter( 'the_title', array( &$this, 'filter_title' ) );
        }

        return $title;
	}
    
    /**
    * Add design profile body class name
    **/
    public function body_classes( $classes ) {
        $classes[] = 'mystyle-design-profile' ;
        return $classes ;
    } 
    
    /**
	 * Init the singleton for an user designindex request.
	 */
	private function init_user_index_request() {
		// ------- SET UP THE PAGER ------------//
		// Create a new pager.
		$this->pager = new MyStyle_Pager();

		// Designs per page.
		$this->pager->set_items_per_page( MYSTYLE_DESIGNS_PER_PAGE );

		// Current page number.
		$this->pager->set_current_page_number(
			max( 1, get_query_var( 'paged' ) )
		);
        
		// Pager items.
		$designs = MyStyle_DesignManager::get_user_designs(
			$this->pager->get_items_per_page(),
			$this->pager->get_current_page_number(),
			$this->user
		);
		$this->pager->set_items( $designs );

		// Total items.
		$this->pager->set_total_item_count(
			MyStyle_DesignManager::get_total_design_count(),
			$this->user
		);

		// Validate the requested page.
		try {
			$this->pager->validate();
		} catch ( MyStyle_Not_Found_Exception $ex ) {
			$response_code = 404;
			status_header( $response_code );

			$this->set_exception( $ex );
			$this->set_http_response_code( $response_code );
		}
	}
    
    /**
	 * Sets the current user.
	 *
	 * @param WP_User $user The user to set as the current user.
	 */
	public function set_user( WP_User $user ) {
		$this->user = $user;
	}

	/**
	 * Gets the current user.
	 *
	 * @return WP_User Returns the currently loaded WP_User.
	 */
	public function get_user() {
		return $this->user;
	}
    
    /**
	 * Sets the current session.
	 *
	 * @param MyStyle_Session $session The session to set as the current session.
	 */
	public function set_session( MyStyle_Session $session ) {
		$this->session = $session;
	}

	/**
	 * Gets the current session.
	 *
	 * @return MyStyle_Session Returns the currently loaded MyStyle_Session.
	 */
	public function get_session() {
		return $this->session;
	}
    
	/**
	 * Gets the pager for the designs index.
	 *
	 * @return MyStyle_Pager Returns the pager for the designs index.
	 */
	public function get_pager() {
		return $this->pager;
	}
    
    
	/**
	 * Sets the current http response code.
	 *
	 * @param int $http_response_code The http response code to set as the
	 * currently set response code. This is used by the shortcode and view
	 * layer.  We set it as a variable since it is difficult to retrieve in
	 * php < 5.4.
	 */
	public function set_http_response_code( $http_response_code ) {
		$this->http_response_code = $http_response_code;
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( $http_response_code );
		}
	}

	/**
	 * Gets the current http response code.
	 *
	 * @return int Returns the current http response code. This is used by the
	 * shortcode and view layer.
	 */
	public function get_http_response_code() {
		if ( function_exists( 'http_response_code' ) ) {
			return http_response_code();
		} else {
			return $this->http_response_code;
		}
	}
    
    /**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_MyDesigns Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}