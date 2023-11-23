<?php
/**
 * The MyStyle_Design_Collection_Page Singleton class has hooks for working with the
 * MyStyle Design Collection page.
 *
 * @package MyStyle
 * @since 3.14.0
 */

/**
 * MyStyle_Design_Collection_Page class.
 */
class MyStyle_Design_Collection_Page {

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_Design_Collection_Page
	 */
	private static $instance;

	/**
	 * Pager for the design profile index.
	 *
	 * @var \MyStyle_Pager
	 */
	private $pager;

	/**
	 * Stores the current ( when the class is instantiated as a singleton )
	 * status code. We store it here since PHP's http_response_code() function
	 * wasn't added until PHP 5.4.
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
        
        add_action( 'init', array( &$this, 'rewrite_rules' ) );
        add_action( 'query_vars', array( &$this, 'query_vars' ) );
        add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
		add_filter( 'document_title_parts', array( &$this, 'document_title_parts' ) ) ;
		add_filter( 'wpseo_title', array(&$this, 'mystyle_design_collection_page_title'), 10);
		add_filter( 'wp_title', array(&$this, 'custom_design_collection_page_title'), 10);
		add_filter( 'the_title', array(&$this, 'wpse46249_filter_wp_title'), 10, 2);
		add_filter( 'body_class', array( &$this, 'body_class' ), 10, 2 ) ;
		add_filter( 'wpseo_metadesc', array(&$this, 'mystyle_design_collection_yoast_description'),10, 2);
		add_filter( 'rank_math/frontend/title', array(&$this, 'custom_design_collection_rank_math_title'), 10,1);
		add_filter( 'rank_math/frontend/description', array(&$this, 'custom_design_collection_rank_math_description'), 10, 2);
		add_filter( 'get_canonical_url', array( &$this, 'canonical_url' ), 10, 2 ) ;
		add_filter( 'get_shortlink', array( &$this, 'shortlink' ), 10, 4 ) ;
		add_filter( 'wpseo_canonical', array( &$this, 'wpseo_canonical' ), 10, 1 ) ;
		add_filter( 'rank_math/frontend/canonical', array( &$this, 'rank_math_canonical' ), 10, 1 ) ;
	}
    
    /**
	 * Added rewrite rule since WordPress 5.5.
	 */
	public function rewrite_rules() {
        
		// Flush rewrite rules for newly created rewrites.
		flush_rewrite_rules();

		add_rewrite_rule(
			'design-collections/([a-zA-Z0-9_-].+)?$',
			'index.php?pagename=design-collections&collection_term=$matches[1]',
			'top'
		);
	}
    /**
 * Filter the Rank Math title for the Design Collections page.
 *
 * @param string $title The current title.
 * @return string The modified title.
 */

	/**
	 * Filter the Rank Math meta description for the Design Collections page.
	 *
	 * @param string $description The current meta description.
	 * @return string The modified meta description.
	 */
 function mystyle_design_collection_yoast_description($description)
	{
		global $wp_query;
		// Check if you are on the Design Collections page.
		if (is_page('design-collections')) {
			$term_slug = get_query_var('collection_term');

			if ($term_slug != "") {
				$term = get_term_by('slug', $term_slug, MYSTYLE_COLLECTION_NAME);
				$site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();

				// Modify the main description based on your condition.
				$main_description = ucfirst($term->name) . (is_null($site_wide_title) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection') . ' customize or purchase at ' . get_bloginfo('name');

				// Set the main description.
				$description = $main_description;
			}
		}

		return $description;
	}
/**
 * Filter the document title for the Design Collections page.
 *
 * @param string $title The current title.
 * @return string The modified title.
 */
/**
 * Filter the document title for the Design Collections page.
 *
 * @param string $title The current title.
 * @return string The modified title.
 */
function custom_design_collection_page_title($title) {
    global $wp_query;

    // Check if you are on the Design Collections page.
    if (is_page('design-collections')) {
        $term_slug = get_query_var('collection_term');

        if ($term_slug != "") {
            $term = get_term_by('slug', $term_slug, MYSTYLE_COLLECTION_NAME);
            $site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();

            // Modify the main title based on your condition.
            $main_title = ucfirst($term->name) . (is_null($site_wide_title) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection');

            // Append the main title to the existing title.
            $title = $main_title . ' - ' . get_bloginfo('name');
        }
    }

    return $title;
}


function wpse46249_filter_wp_title( $title, $id ) {

	if (( get_the_ID() === $id ) && // Make sure we're in the loop.
		( in_the_loop() ) // Make sure we're in the loop.
		) {
			if (is_page('design-collections')) {
				$term_slug = get_query_var('collection_term');
				if ($term_slug != "") {
					$term = get_term_by('slug', $term_slug, MYSTYLE_COLLECTION_NAME);
					$site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();
					$main_title = ucfirst($term->name) . (is_null($site_wide_title) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection');
					$title = $main_title ;
				}
			}
	}
		
	return $title;
}





    /**
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array of query vars.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'collection_term';

		return $query_vars;
	}

	/**
	 * Function to get the page id.
	 */
	public static function get_id() {
		$page_id = null;

		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_COLLECTION_INDEX_PAGEID_NAME ] ) ) {
			$page_id = $options[ MYSTYLE_DESIGN_COLLECTION_INDEX_PAGEID_NAME ];
		}

		return $page_id;
	}

	/**
	 * Function to determine if the post exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function exists() {
		$exists = false;

		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}
    
    /**
	 * Function to determine if the post exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function index_exists() {
		$exists = false;

		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_COLLECTION_INDEX_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}

	/**
	 * Function to create the page.
	 *
	 * @return number Returns the page id of the Design Tag page.
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create() {
		// Create the Design Profile page.
		$design_tag_page = array(
			'post_title'   => 'Design Collections Default Page',
			'post_name'    => 'designtags',
			'post_content' => 'Design Collections Default Page. DO NOT DELETE!',
			'post_status'  => 'draft',
			'post_type'    => 'post',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );

		// Store the design tag page's id in the database.
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] = $post_id;
		$updated                                   = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $post_id );
			throw new MyStyle_Exception( __( 'Could not store page id.', 'mystyle' ), 500 );
		}

		return $post_id;
	}
    
    /**
	 * Function to create the index page.
	 *
	 * @return number Returns the page id of the Design Tag page.
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create_index() {
		// Create the Design Profile page.
		$design_tag_page = array(
			'post_title'   => 'Design Collections',
			'post_name'    => 'design-collections',
			'post_content' => '[mystyle_design_collections]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
            'guid'         => 'design-collections'
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );
        
        // Store the design tag page's id in the database.
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_COLLECTION_INDEX_PAGEID_NAME ] = $post_id;
		$updated                                   = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $post_id );
			throw new MyStyle_Exception( __( 'Could not store index page id.', 'mystyle' ), 500 );
		}
        
		return $post_id;
	}

	/**
	 * WPSEO canonical url.
	 */
	public function wpseo_canonical( $canonical ) {
		$slug = get_query_var( 'collection_term' ) ;
		
		if ( $slug != "" ) {
			$canonical = $this->get_collection_url( $slug ) ;
		}

		return $canonical;
	}

	/**
	 * Get Rank Math Canonical URL.
	 */
	public function rank_math_canonical( $canonical_url ) {
		$slug = get_query_var( 'collection_term' ) ;
		
		if ( $slug != "" ) {
			$canonical_url = $this->get_collection_url( $slug ) ;
		}

		return $canonical_url;
	}

	/**
	 * Get canonical url.
	 */
	public function canonical_url( $url, $post ) {
		$slug = get_query_var( 'collection_term' ) ;
		
		if ( $slug != "" ) {
			$url = $this->get_collection_url( $slug ) ;
		}

		return $url;

	}

	/**
	 * Filter the shortlink.
	 */
	public function shortlink( $shortlink, $id, $context ) {
		$slug = get_query_var( 'collection_term' ) ;
		
		if ( $slug != "" ) {
			$shortlink = $this->get_collection_url( $slug ) ;
		}

		return $shortlink;
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

    if (
        ( get_the_ID() === $id ) && // Make sure we're in the loop.
        ( in_the_loop() ) // Make sure we're in the loop.
    ) {
        if ( isset( $wp_query->query['design_tag_term'] ) ) {

            $term_slug = $wp_query->query['design_tag_term'];

            if( preg_match( '/\//', $term_slug) ) {
                $url_array  = explode('/', $term_slug );
                if($url_array[0] == 'page' ) {
                    $term_slug = false;
                }
                else {
                    $term_slug = $url_array[0];
                }

            }

            if( $term_slug ) {
                $term = get_term_by( 'slug', $term_slug, MYSTYLE_TAXONOMY_NAME);

                $site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();

                $title = esc_attr($term->name). ( is_null( $site_wide_title ) ? ' Design Tag' : ' ' . $site_wide_title . ' Community Designs' );
            }

        }
    }

    return $title;
}

/**
 * Filter the document title for the Design Collections page.
 *
 * @param string $title The current title.
 * @return string The modified title.
 */
function mystyle_design_collection_page_title($title) {
    global $wp_query;

    // Check if you are on the Design Collections page.
    if (is_page('design-collections')) {
        $term_slug = get_query_var('collection_term');

        if ($term_slug != "") {
            $term = get_term_by('slug', $term_slug, MYSTYLE_COLLECTION_NAME);
            $site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();

            // Modify the main title based on your condition.
            $main_title = ucfirst($term->name) . (is_null($site_wide_title) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection');

            // Append the main title to the existing title.
            $title = $main_title . ' - ' . get_bloginfo('name');
        }
    }

    return $title;
}

/**
 * Filter the Rank Math title for the Design Collections page.
 *
 * @param string $title The current title.
 * @return string The modified title.
 */
function custom_design_collection_rank_math_title($title) {
    global $wp_query;

    // Check if you are on the Design Collections page.
    if (is_page('design-collections')) {
        $term_slug = get_query_var('collection_term');

        if ($term_slug != "") {
            $term = get_term_by('slug', $term_slug, MYSTYLE_COLLECTION_NAME);
            $site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();

            // Modify the main title based on your condition.
            $main_title = ucfirst($term->name) . (is_null($site_wide_title) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection');

            // Set the main title.
            $title =
				$main_title . ' - ' . get_bloginfo('name');
        }
    }

    return $title;
}

/**
 * Filter the Rank Math meta description for the Design Collections page.
 *
 * @param string $description The current meta description.
 * @return string The modified meta description.
 */
function custom_design_collection_rank_math_description($description) {
    global $wp_query;

    // Check if you are on the Design Collections page.
    if (is_page('design-collections')) {
        $term_slug = get_query_var('collection_term');

        if ($term_slug != "") {
            $term = get_term_by('slug', $term_slug, MYSTYLE_COLLECTION_NAME);
            $site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title();

            // Modify the main description based on your condition.
            $main_description = ucfirst($term->name) . (is_null($site_wide_title) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection') . ' customize or purchase at ' . get_bloginfo('name');

            // Set the main description.
            $description = $main_description;
        }
    }

    return $description;
}




	/**
	 * Filter Document title parts
	 * 
	 * @param array $title head title parts array
	 * 
	 * @return array $title 
	 */
	public function document_title_parts( $title ) {
		global $wp_query ;
        
        
		if ( isset( $wp_query->query['collection_term'] ) ) {
			
			$term_slug = $wp_query->query['collection_term'] ;
			
			if( preg_match( '/\//', $term_slug) ) {
				$url_array  = explode('/', $term_slug ) ;
				if($url_array[0] == 'page' ) {
					$term_slug = false ;
				}
				else {
					$term_slug = $url_array[0] ;
				}

			}
			
			if( $term_slug ) {
				$term = get_term_by( 'slug', $term_slug, MYSTYLE_TAXONOMY_NAME) ;
				
				$site_wide_title = MyStyle_Options::get_alternate_design_tag_collection_title() ;

				$title['title'] = ucfirst( $term->name ) . ( is_null( $site_wide_title ) ? ' Design Collection' : ' ' . $site_wide_title . ' Collection' ) ;
			}
			
		}
        
		return $title;
	}

	/**
	 * Filter the body class to add when viewing single collection page
	 * 
	 * @param array $classes The class array to filter
	 * @param array $class
	 * @return array $classes
	 */
	public function body_class( $classes, $class ) {
		global $wp_query ;

		if( isset($wp_query->query['collection_term']) ) {
			$classes[] = 'mystyle-design-collection-single-term' ;
		}

		return $classes ;
	}

	/**
	 * Static function that builds a URL to the page for the collection.
	 *
	 * @param string $slug The collection slug.
	 * @return string Returns a URL that can be used to access the page for the
	 * collection.
	 */
	public static function get_collection_url( $slug ) {
		$url = site_url( 'design-collections' ) . '/' . $slug . '/' ;

		return $url;
	}

	/**
	 * Sets the current HTTP response code.
	 *
	 * @param int $http_response_code The HTTP response code to set as the
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
	 * Gets the current HTTP response code.
	 *
	 * @return int Returns the current HTTP response code. This is used by the
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
	 * @return MyStyle_Design_Collection_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
