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

/**
 * MyStyle_Design_Profile_Page class.
 */
class MyStyle_Design_Profile_Page {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Design_Profile_Page
	 */
	private static $instance;

	/**
	 * Stores the currently loaded design (when the class is instantiated as a
	 * singleton).
	 *
	 * @var MyStyle_Design
	 */
	private $design;

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
	 * The design that comes immediately before this one in the collection.
	 *
	 * @var MyStyle_Design
	 */
	private $previous_design;

	/**
	 * The design that comes immediately before this one in the collection.
	 *
	 * @var MyStyle_Design
	 */
	private $next_design;

	/**
	 * Array of designs for the index.
	 *
	 * @var array
	 */
	private $designs;

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

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->http_response_code = 200;
        
        add_action( 'init', array( &$this, 'rewrite_rules') ) ;
        add_action( 'query_vars', array( &$this, 'query_vars') ) ;
		add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
		add_filter( 'body_class', array( &$this, 'filter_body_class' ), 10, 1 );
		add_action( 'template_redirect', array( &$this, 'init' ) );
        add_action( 'wp_head', array( &$this, 'wp_head' ), 2);
        
        add_action( 'wp_ajax_design_tag_search', array( &$this, 'design_tag_search' ) ) ;
        add_action( 'wp_ajax_design_tag_add', array( &$this, 'design_tag_add' ) ) ;
        add_action( 'wp_ajax_design_tag_remove', array( &$this, 'design_tag_remove' ) ) ;
        
        add_filter( 'document_title_parts', array( &$this, 'filter_document_title_parts' ), 10, 1 ); 
	}
    
    /**
     * Added rewrite rule since WordPress 5.5
     */
    public function rewrite_rules() {
        //flush rewrite rules for newly created rewrites
        flush_rewrite_rules() ;
        
        add_rewrite_rule('designs/([a-zA-Z0-9_-].+)?$', 'index.php?pagename=designs&design_id=$matches[1]', 'top') ;
    }
    
    
    /**
    * Add custom query vars
    **/
    public function query_vars( $query_vars ) {
        $query_vars[] = 'design_id';
        return $query_vars;
    }

	/**
	 * Function to create the page.
	 *
	 * @return number Returns the page id of the Design Profile page.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create() {
		// Create the Design Profile page.
		$design_profile_page = array(
			'post_title'   => 'Community Design Gallery',
			'post_name'    => 'designs',
			'post_content' => '[mystyle_design_profile]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		$page_id             = wp_insert_post( $design_profile_page );

		// Store the design profile page's id in the database.
		$options                                       = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] = $page_id;
		$updated                                       = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $page_id );
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
	 */
	public function init() {
		// Only run if we are currently serving the design profile page.
		if ( self::is_current_post() ) {
            
			$design_profile_page = self::get_instance();

			// Set the user.
			/* @var $user \WP_User phpcs:ignore */
			$user = wp_get_current_user();
			$design_profile_page->set_user( $user );

			// Set the session.
			/* @var $session \MyStyle_Session phpcs:ignore */
			$session = MyStyle()->get_session();
			$design_profile_page->set_session( $session );

			// Get the design from the url, if it's not found, this function
			// returns false.
            $design_id = self::get_design_id_from_url();
            
			if ( false === $design_id || preg_match('/page/', $design_id ) ) {
				$design_profile_page->init_index_request();
			} else {
                
                if(isset($_POST['ms-title'])) {
                    MyStyle_DesignManager::set_title($design_id, $_POST['ms-title']) ;
                }
                
				$design_profile_page->init_design_request( $design_id );
			}
		}
	}

	/**
	 * Init the singleton for a design request.
	 *
	 * @param type $design_id The design_id to use when initializing.
	 * @throws MyStyle_Not_Found_Exception Throws a MyStyle_Not_Found_Exception
	 * if the Design can't be found.
	 */
	private function init_design_request( $design_id ) {
		try {
			// Get the previous design.
			$this->set_previous_design(
				MyStyle_DesignManager::get_previous_design( $design_id )
			);

			// Get the next design (note: we do this first in case getting
			// the design throws an exception).
			$this->set_next_design(
				MyStyle_DesignManager::get_next_design( $design_id )
			);

			// Get the design. If the user doesn't have access, an exception is
			// thrown.
			$design = MyStyle_DesignManager::get(
				$design_id,
				$this->user,
				$this->session
			);
            
            
			// Throw exception if design isn't found (it's caught at the bottom
			// of this function.
			if ( null === $design ) {
				throw new MyStyle_Not_Found_Exception( 'Design not found.' );
			}
            
            
            if(get_current_user_id() == $design->get_user_id() || current_user_can('administrator')) {
                
                
                wp_register_style( 'jquery-ui-styles','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
                wp_enqueue_style( 'jquery-ui-styles' );
                
                wp_register_style( 'tokenfield-styles','https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/css/bootstrap-tokenfield.min.css' );
                wp_enqueue_style( 'tokenfield-styles' );
                
                wp_register_script( 'tokenfield', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/bootstrap-tokenfield.js', null, null, true );
                wp_enqueue_script('tokenfield');
                
                wp_register_script( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', null, null, true );
                wp_enqueue_script( 'jquery-ui' );
                
                wp_enqueue_script( 'frontend_js', MYSTYLE_ASSETS_URL . 'js/frontend.js', array(), // deps.
				'1.0.0', // version.
				true);
                
                wp_register_style( 'tokenfield-custom-styles', MYSTYLE_ASSETS_URL . 'css/tokenfield.css' );
                wp_enqueue_style( 'tokenfield-custom-styles' );
                
            }

			// Set the current design in the singleton instance.
			$this->set_design( $design );

			// When an exception is thrown, set the status code and set the
			// exception in the singleton instance, it will later be used by
			// the shortcode and view layer.
		} catch ( MyStyle_Not_Found_Exception $ex ) {
			$response_code = 404;
			status_header( $response_code );

			$this->set_exception( $ex );
			$this->set_http_response_code( $response_code );
		} catch ( MyStyle_Unauthorized_Exception $ex ) { // Unauthenticated.
			// Note: we would ideally return a 401 but WordPress seems to work
			// best with 200.
			$response_code = 200;
			status_header( $response_code );

			$this->set_exception( $ex );
			$this->set_http_response_code( $response_code );
		} catch ( MyStyle_Forbidden_Exception $ex ) {
			// Note: we would ideally return a 403 but WordPress seems to work
			// best with 200.
			$response_code = 200;
			status_header( $response_code );

			$this->set_exception( $ex );
			$this->set_http_response_code( $response_code );
		}
	}

	/**
	 * Init the singleton for an index request.
	 */
	private function init_index_request() {
		// ------- SET UP THE PAGER ------------//
		// Create a new pager.
		$this->pager = new MyStyle_Pager();

		// Designs per page.
		$this->pager->set_items_per_page( MYSTYLE_DESIGNS_PER_PAGE );
        
		// Current page number.
        $page_array = explode('/', get_query_var( 'design_id' )) ; //not the best solution @TODO cleaner fix for this
        
		$this->pager->set_current_page_number(
			max( 1, $page_array[1] )
		);

		// Pager items.
		$designs = MyStyle_DesignManager::get_designs(
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
	 * Function to get the id of the Design Profile page.
	 *
	 * @return number Returns the page id of the Design Profile page.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if the Design
	 * Profile page is missing.
	 */
	public static function get_id() {
		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( ! isset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] ) ) {
			throw new MyStyle_Exception( __( 'Design Profile Page is Missing! Please use the "Fix Design Profile Page Tool" on the MyStyle Settings page to fix.', 'mystyle' ), 404 );
		}
		$page_id = $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ];

		return $page_id;
	}

	/**
	 * Determines whether or not this page is the current page/post.
	 *
	 * @return boolean Returns true if this page is the current page/post,
	 * otherwise returns false.
	 */
	public static function is_current_post() {
		$ret = false;

		try {
			$current_post = get_post();
			if ( ( ! empty( $current_post ) ) &&
					( self::get_id() === $current_post->ID ) ) {
				$ret = true;
			}
		} catch ( Exception $ex ) {
			// Do nothing ( return false ).
		}

		return $ret;
	}

	/**
	 * Function to determine if the page exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function exists() {
		$exists = false;

		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}

	/**
	 * Function to delete the Design Profile page.
	 */
	public static function delete() {
		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$page_id = $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ];

		// Remove the page id from the database.
		unset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] );
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Delete the page from WordPress.
		wp_delete_post( $page_id );
	}

	/**
	 * Static function that builds a url to the Design Profile page including
	 * url paramaters to load the passed design.
	 *
	 * @param \MyStyle_Design $design The design that you want a URL for.
	 * @return string Returns a link that can be used to view the design.
	 * @global WP_Rewrite $wp_rewrite
	 */
	public static function get_design_url( \MyStyle_Design $design ) {
		global $wp_rewrite;

		if ( isset( $wp_rewrite->page_structure ) && ( '' !== $wp_rewrite->page_structure ) ) {
			$url = get_permalink( self::get_id() );
			if ( '/' !== substr( $url, -1 ) ) {
				$url .= '/';
			}
			$url .= $design->get_design_id();
		} else {
			$args = array(
				'design_id' => $design->get_design_id(),
			);
			$url  = add_query_arg( $args, get_permalink( self::get_id() ) );
		}

		return $url;
	}
    
    /**
     * Get design tags 
     *
     *
     */
    public static function get_design_tags( $design_id = null, $slug = false ) {
        if( null == $design_id ) {
            $design_id = self::get_design_id_from_url() ;
        }
        
        $tag_names = array() ;
        $terms = wp_get_object_terms( $design_id, MYSTYLE_TAXONOMY_NAME ) ;
        
        foreach( $terms as $term ) {
            if($slug) {
                $tag_names[] = array( 'name' => $term->name, 'slug' => $term->slug ) ;
            }
            else {
                $tag_names[] = $term->name ;
            }
        }
        
        return $tag_names ;
    }
    
    /**
     * Search design tags for ajax
     *
     */
    public static function design_tag_search() {
        $taxonomy = MYSTYLE_TAXONOMY_NAME ;
        $s = wp_unslash( $_GET['q'] );
 
        $comma = _x( ',', 'tag delimiter' );
        if ( ',' !== $comma ) {
            $s = str_replace( $comma, ',', $s );
        }

        if ( false !== strpos( $s, ',' ) ) {
            $s = explode( ',', $s );
            $s = $s[ count( $s ) - 1 ];
        }

        $s = trim( $s );

        /**
         * Filters the minimum number of characters required to fire a tag search via Ajax.
         *
         * @since 4.0.0
         *
         * @param int         $characters The minimum number of characters required. Default 2.
         * @param WP_Taxonomy $tax        The taxonomy object.
         * @param string      $s          The search term.
         */
        $term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $s );

        /*
         * Require $term_search_min_chars chars for matching (default: 2)
         * ensure it's a non-negative, non-zero integer.
         */
        if ( ( 0 == $term_search_min_chars ) || ( strlen( $s ) < $term_search_min_chars ) ) {
            wp_die();
        }

        $results = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'name__like' => $s,
                'fields'     => 'names',
                'hide_empty' => false,
            )
        );

        header('Content-Type: application/json');
        echo json_encode( $results ) ;
        wp_die();
    }
    
    /**
     * Save design tag
     *
     */
    public static function design_tag_add() {
        $taxonomy = MYSTYLE_TAXONOMY_NAME ;
        $tag = $_POST['tag'] ;
        $design_id = intval($_POST['design_id']) ;
        
        $user_id = get_current_user_id() ;
        
        global $wpdb ;
        
        $table_name = $wpdb->prefix . MyStyle_Design::TABLE_NAME;
        
        $sql = "SELECT user_id FROM " . $table_name . " WHERE ms_design_id = " . $design_id ;
        
        $design_user_id = $wpdb->get_var($sql) ;
        
        if( ( $design_user_id == $user_id ) || current_user_can('administrator') ) {
            wp_add_object_terms($design_id, $tag, $taxonomy) ;  
        }
        
        header('Content-Type: application/json');
        print json_encode(array('tag' => $tag)) ;
        die() ;
    }
    
    /**
     * Remove design tag
     *
     */
    public static function design_tag_remove() {
        $taxonomy = MYSTYLE_TAXONOMY_NAME ;
        $tag = $_POST['tag'] ;
        $design_id = intval($_POST['design_id']) ;
        
        $user_id = get_current_user_id() ;
        
        global $wpdb ;
        
        $table_name = $wpdb->prefix . MyStyle_Design::TABLE_NAME;
        
        $sql = "SELECT user_id FROM " . $table_name . " WHERE ms_design_id = " . $design_id ;
        
        $design_user_id = $wpdb->get_var($sql) ;
        
        if( ( $design_user_id == $user_id ) || current_user_can('administrator') ) {
            wp_remove_object_terms($design_id, $tag, $taxonomy) ;
        }
        
        header('Content-Type: application/json');
        print json_encode(array('tag' => $tag)) ;
        die() ;
    }
    

	/**
	 * Gets the design id from the url. If it can't find the design id in the
	 * url, this function returns false.
	 *
	 * @return int|false Returns the design id from the url or false if none
	 * found.
	 */
	public static function get_design_id_from_url() {
		// Try the query vars ( ex: &design_id=10 ).
		$design_id = get_query_var( 'design_id' );
        if(preg_match('/page/', $design_id)) {
            $design_id = false ;
        }
        elseif ( empty( $design_id ) ) {
			// ---------- try at /designs/10. --------
			$path = $_SERVER['REQUEST_URI'];

			// Get the design profile page's WP_Post slug.
			/* @var $post \WP_Post phpcs:ignore */
			$post = get_post( self::get_id() );
			$slug = $post->post_name;

			$pattern = '/^.*\/' . $slug . '\/([\d]+)/';
			if ( preg_match( $pattern, $path, $matches ) ) {
				$design_id = $matches[1];
			} else {
				$design_id = false;
			}
			// -------------------------------------
		}
        
		return $design_id;
	}

	/**
	 * Sets the current design.
	 *
	 * @param MyStyle_Design $design The design to set as the current design.
	 */
	public function set_design( MyStyle_Design $design ) {
		$this->design = $design;
	}

	/**
	 * Gets the current design.
	 *
	 * @return MyStyle_Design Returns the currently loaded MyStyle_Design.
	 */
	public function get_design() {
		return $this->design;
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
	 * Sets the previous design.
	 *
	 * @param MyStyle_Design $design The design to set as the previous design.
	 */
	public function set_previous_design( MyStyle_Design $design = null ) {
		$this->previous_design = $design;
	}

	/**
	 * Gets the previous design.
	 *
	 * @return MyStyle_Design Returns the previous MyStyle_Design.
	 */
	public function get_previous_design() {
		return $this->previous_design;
	}

	/**
	 * Sets the next design.
	 *
	 * @param MyStyle_Design $design The design to set as the next design.
	 */
	public function set_next_design( MyStyle_Design $design = null ) {
		$this->next_design = $design;
	}

	/**
	 * Gets the next design.
	 *
	 * @return MyStyle_Design Returns the next MyStyle_Design.
	 */
	public function get_next_design() {
		return $this->next_design;
	}

	/**
	 * Sets the current designs.
	 *
	 * @param array $designs The designs to set as the current designs.
	 */
	public function set_designs( $designs ) {
		$this->designs = $designs;
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
	 * Sets the current exception.
	 *
	 * @param MyStyle_Exception $exception The exception to set as the currently
	 * thrown exception. This is used by the shortcode and view layer.
	 */
	public function set_exception( MyStyle_Exception $exception ) {
		$this->exception = $exception;
	}

	/**
	 * Gets the current exception.
	 *
	 * @return MyStyle_Exception Returns the currently thrown MyStyle_Exception
	 * if any. This is used by the shortcode and view layer.
	 */
	public function get_exception() {
		return $this->exception;
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
	 * Function that gets the value of the design_profile_page_show_add_to_cart
	 * setting.
	 *
	 * @return boolean Returns true if the design_profile_page_show_add_to_cart
	 * is enabled, otherwise returns false. Defaults to enabled (1).
	 */
	public static function show_add_to_cart_button() {
		return MyStyle_Options::is_option_enabled(
			MYSTYLE_OPTIONS_NAME,
			'design_profile_page_show_add_to_cart',
			true // Default to true.
		);
	}

	/**
	 * Attempt to fix the Design Profile page. This may involve creating,
	 * re-creating or repairing it.
	 *
	 * @return Returns a message describing the outcome of fix operation.
	 * @todo: Add unit testing
	 */
	public static function fix() {
		$message = '<br/>';
		$status  = 'Design Profile page looks good, no action necessary.';
		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ] ) ) {
			$post_id  = $options[ MYSTYLE_DESIGN_PROFILE_PAGEID_NAME ];
			$message .= 'Found the stored ID of the Design Profile page...<br/>';

			/* @var $post \WP_Post phpcs:ignore */
			$post = get_post( $post_id );
			if ( null !== $post ) {
				$message .= 'Design Profile page exists...<br/>';

				// Check the status.
				if ( 'publish' !== $post->post_status ) {
					$message          .= 'Status was "' . $post->post_status . '", changing to "publish"...<br/>';
					$post->post_status = 'publish';

					/* @var $error \WP_Error phpcs:ignore */
					$errors = wp_update_post( $post, true );

					if ( is_wp_error( $errors ) ) {
						foreach ( $errors as $error ) {
							$messages .= $error . '<br/>';
							$status   .= 'Fix errored out :(<br/>';
						}
					} else {
						$message .= 'Status updated.<br/>';
						$status   = 'Design Profile page fixed!<br/>';
					}
				} else {
					$message .= 'Design Profile page is published...<br/>';
				}

				// Check for the shortcode.
				if ( false === strpos( $post->post_content, '[mystyle_design_profile]' ) ) {
					$message            .= 'The mystyle_designs shortcode not found in the page content, adding...<br/>';
					$post->post_content .= '[mystyle_designs]';

					/* @var $error \WP_Error phpcs:ignore */
					$errors = wp_update_post( $post, true );

					if ( is_wp_error( $errors ) ) {
						foreach ( $errors as $error ) {
							$messages .= $error . '<br/>';
							$status   .= 'Fix errored out :(<br/>';
						}
					} else {
						$message .= 'Shortcode added.<br/>';
						$status   = 'Design Profile page fixed!<br/>';
					}
				} else {
					$message .= 'Design Profile page has mystyle_designs shortcode...<br/>';
				}
			} else { // Post not found, recreate.
				$message .= 'Design Profile page appears to have been deleted, recreating...<br/>';
				try {
					$post_id = self::create();
					$status  = 'Design Profile page fixed!<br/>';
				} catch ( \Exception $e ) {
					$status = 'Error: ' . $e->getMessage();
				}
			}
		} else { // ID not available, create.
			$message .= 'Design Profile page missing, creating...<br/>';
			self::create();
			$status = 'Design Profile page fixed!<br/>';
		}

		$message .= $status;

		return $message;
	}

	/**
	 * Filter the post title.
	 *
	 * @param string $title The title of the post.
	 * @param type   $id The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
		try {
			if (
					( ! empty( $id ) ) &&
					( self::get_id() === $id ) &&
					( get_the_ID() === $id ) && // Make sure we're in the loop.
					( in_the_loop() ) // Make sure we're in the loop.
			) {
				$design = $this->get_design();
				if ( null !== $design ) {
                    if("" !== $design->get_title()) {
                        $title = $design->get_title() ;
                    }
                    else {
                        $title = 'Design ' . $design->get_design_id();
                    }
				}
			}
		} catch ( MyStyle_Exception $e ) {
			// This exception may be thrown if the Design Profile Page is
			// missing. For this function, that is okay, just continue.
		}

		return $title;
	}
    
    /**
     * Filter Document Title Parts
     *
     */
    public function filter_document_title_parts( $title ) {
        $design_id = self::get_design_id_from_url();
        
        if ( $design_id && !isset($_GET['design_id'])) {
            $design = $this->get_design() ;
            if("" !== $design->get_title() ) {
                $title['page'] = $design->get_title() ;
            }
        }
        
        return $title ;
        
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
		global $post;

		try {
			if ( null !== $post ) {
				if ( self::get_id() === $post->ID ) {
					$classes[] = 'mystyle-design-profile';
				}
			}
		} catch ( MyStyle_Exception $e ) {
			// This exception may be thrown if the Customize Page or Design
			// Profile Page is missing. For this function, that is okay, just
			// continue.
		}

		return $classes;
	}
    
    /**
    * Generate Author metatag for individual design pages
    **/
    public function wp_head() {
        $design_id = self::get_design_id_from_url();

        if ( $design_id && !isset($_GET['design_id'])) {
            $design = $this->get_design() ;
            if(null !== $design) {
                $user_id = $design->get_user_id() ;
                $product_id = $design->get_product_id() ;
                $product = wc_get_product($product_id) ;
                $user = get_user_by('id', $user_id) ;
                $design_title = ' Design ' . $design_id ;

                if("" !== $design->get_title()) {
                    $design_title = $design->get_title() ;
                }

                ?>
                <meta name="author" content="<?php print $user->user_nicename ; ?>">
                <meta name="description" content="<?php print $product->name . ' ' . $design_title ; ?>">
                <meta name="keywords" content="<?php print $product->name . ', ' . $design_title ; ?>">
                <?php
                
                if(get_current_user_id() == $design->get_user_id() || current_user_can('administrator')) {
                    $tags = $this->get_design_tags() ;
                ?>
                <script>
                    var design_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
                    var designId = <?php echo $design_id ; ?> ;
                    var designTags = '<?php echo ( (count($tags) > 0) ? implode(",", $tags) : '') ; ?>' ;
                </script>
                <?php
                }
            }
            
        }
    }

	/**
	 * Gets the list of products for the design profile page as an HTML string.
	 *
	 * @return string Returns the product list as an HTML string.
	 * @todo Unit test this function.
	 */
	public function get_product_list_html() {
		// Hook the WooCommerce [products] shortcode to modify the output.
		add_filter( 'woocommerce_loop_product_link', array( &$this, 'modify_woocommerce_loop_product_link' ), 10, 1 );
        add_action( 'woocommerce_loop_add_to_cart_link', array( &$this, 'loop_add_to_cart_link' ), 10, 2 );
		add_filter( 'woocommerce_shortcode_products_query', array( &$this, 'modify_woocommerce_shortcode_products_query' ), 10, 1 );

		// Get the shortcode output.
		$out = do_shortcode( '[products paginate="false"]' );

		// Undo our hooks.
		remove_filter( 'woocommerce_loop_product_link', array( &$this, 'modify_woocommerce_loop_product_link' ) );
        remove_action( 'woocommerce_loop_add_to_cart_link', array( &$this, 'loop_add_to_cart_link' ) );
		remove_filter( 'woocommerce_shortcode_products_query', array( &$this, 'modify_woocommerce_shortcode_products_query' ) );

		return $out;
	}

	/**
	 * Modify the WooCommerce products link to go to the customizer instead
	 * of the product info page.
	 *
	 * Used by the get_product_list_html method above.
	 *
	 * @return string Returns the html for the link as a string (i.e.
	 * "<a href...").
	 * @global $product
	 * @todo Unit test this function.
	 */
	public function modify_woocommerce_loop_product_link() {
		global $product ;
        $product_id = $product->get_id() ;
		$mystyle_design = $this->get_design() ;
		$customizer_url  = MyStyle_Customize_Page::get_design_url( $mystyle_design, null, null, $product_id );
        
		return $customizer_url;
	}
    
    /**
	 * Modify the add to cart link for product listings.
	 *
	 * @param type $link The "Add to Cart" link (html).
	 * @param type $product The current product.
	 * @return type Returns the html to be outputted.
	 */
	public function loop_add_to_cart_link( $link, $product ) {
		$mystyle_product = new \MyStyle_Product( $product );
		$product_id      = $mystyle_product->get_id();
		$product_type    = $mystyle_product->get_type();

		if ( ( $mystyle_product->is_customizable() ) && ( 'variable' !== $product_type ) ) {
			$customize_page_id = MyStyle_Customize_Page::get_id();

			// Build the url to the customizer including the poduct_id 
            
			$mystyle_design = $this->get_design() ;
		    $customizer_url  = MyStyle_Customize_Page::get_design_url( $mystyle_design, null, null, $product_id );

			// Add the passthru data to the url.
			$passthru                        = array();
			$passthru['post']                = array();
			$passthru['post']['quantity']    = 1;
			$passthru['post']['add-to-cart'] = $product_id;
			$passthru_encoded                = base64_encode( wp_json_encode( $passthru ) );
			$customizer_url                  = add_query_arg( 'h', $passthru_encoded, $customizer_url );

			// Build the link ( a tag ) to the customizer.
			$customize_link = sprintf(
				'<a ' .
				'href="%s" ' .
				'rel="nofollow" ' .
				'class="button %s product_type_%s" ' .
				'>%s</a>',
				esc_url( $customizer_url ),
				$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
				esc_attr( $product_type ),
				esc_html( 'Customize' )
			);

			$ret = $customize_link;
		} else {
			$ret = $link;
		}

		return $ret;
	}

	/**
	 * Modify the WooCommerce products shortcode query to only include MyStyle
	 * enabled products.
	 *
	 * Used by the get_product_list_html method above.
	 *
	 * @param array $args An array of query arguments.
	 * @return array Returns the array of query arguments.
	 * @todo Unit test this function.
	 */
	public function modify_woocommerce_shortcode_products_query( $args ) {
		$mystyle_filter            = array();
		$mystyle_filter['key']     = '_mystyle_enabled';
		$mystyle_filter['value']   = 'yes';
		$mystyle_filter['compare'] = 'IN';

		$args['meta_query'][] = $mystyle_filter;

		return $args;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Design_Profile_Page Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
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
