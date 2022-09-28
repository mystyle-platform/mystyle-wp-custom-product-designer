<?php
/**
 * The MyStyle_Design_Tag_Page Singleton class has hooks for working with the
 * MyStyle Design Tag page.
 *
 * @package MyStyle
 * @since 3.14.0
 */

/**
 * MyStyle_Design_Tag_Page class.
 */
class MyStyle_Design_Tag_Page {

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_Design_Tag_Page
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
		add_action( 'posts_pre_query', array( &$this, 'alter_query' ), 25, 2 );
		add_action( 'template_redirect', array( &$this, 'set_pager' ) );

		add_filter( 'has_post_thumbnail', array( &$this, 'has_post_thumbnail' ), 10, 3 );
		add_filter( 'wp_get_attachment_image_src', array( &$this, 'wp_get_attachment_image_src' ), 10, 4 );
		add_filter( 'post_link', array( &$this, 'post_link' ), 10, 3 );
        add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 ) ;
		add_filter( 'document_title_parts', array( &$this, 'document_title_parts' ) ) ;
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
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] ) ) {
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
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}
    
    /**
	 * Function to determine if the post exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function seo_index_exists() {
		$exists = false;

		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_INDEX_SEO_PAGEID_NAME ] ) ) {
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
			'post_title'   => 'Design Tags Default Page',
			'post_name'    => 'designtags',
			'post_content' => 'Design Tags Default Page. DO NOT DELETE!',
			'post_status'  => 'draft',
			'post_type'    => 'post',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );

		// Store the design tag page's id in the database.
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] = $post_id;
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
			'post_title'   => 'Design Tags',
			'post_name'    => 'design-tags',
			'post_content' => '[mystyle_design_tags per_tag="4" tags_per_page="12"]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );
        
        // Store the design tag page's id in the database.
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ] = $post_id;
		$updated                                   = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $post_id );
			throw new MyStyle_Exception( __( 'Could not store index page id.', 'mystyle' ), 500 );
		}
        
		return $post_id;
	}
    
    /**
	 * Function to create the SEO index page.
	 *
	 * @return number Returns the page id of the Design Tag page.
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create_seo_index() {
		// Create the Design Profile page.
		$design_tag_page = array(
			'post_title'   => 'Design Tags Index',
			'post_name'    => 'design-tags-index',
			'post_content' => '[mystyle_design_tags per_tag="5" tags_per_page="1000" show_designs="false"]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );
        
        // Store the design tag page's id in the database.
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_TAG_INDEX_SEO_PAGEID_NAME ] = $post_id;
		$updated                                   = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $post_id );
			throw new MyStyle_Exception( __( 'Could not store index page id.', 'mystyle' ), 500 );
		}
        
		return $post_id;
	}
    
    /**
     * Function fix the design tags index slug
     *
     * @return nothing
     */ 
    public function fix_index() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );

		$post_id = $options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ];
        
        if( $post_id ) {
            $post_data = array(
                'ID' => $post_id,
                'post_title'   => 'Design Tags',
                'post_name' => 'design-tags',
                'post_content' => '[mystyle_design_tags per_tag="5" tags_per_page="12"]'
            ) ;
            
            wp_update_post( $post_data ) ;
        }
        
        
    }
    
    /**
     * Function fix the design tags index slug
     *
     * @return nothing
     */ 
    public function fix_seo_index() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );

		$post_id = $options[ MYSTYLE_DESIGN_TAG_INDEX_SEO_PAGEID_NAME ];
        
        if( $post_id ) {
            $post_data = array(
                'ID' => $post_id,
                'post_title'   => 'Design Tags Index',
                'post_name' => 'design-tags-index',
                'post_content' => '[mystyle_design_tags per_tag="5" tags_per_page="1000" show_designs="false"]'
            ) ;
            
            wp_update_post( $post_data ) ;
        }
        
        
    }

	/**
	 * Function to fix the post_status.
	 *
	 * @return number Returns the page id of the Design Profile page.
	 */
	public static function fix() {

		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );

		$post_id = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ];

		$draft_post = array(
			'ID'          => $post_id,
			'post_status' => 'draft',
		);

		$update_post_id = wp_update_post( $draft_post );

		return $update_post_id;
	}

	/**
	 * Alter WP_QUERY pager information based in the MyStyle_Pager class
	 *
	 * @global $wp_query
	 */
	public function set_pager() {
		global $wp_query;

		if ( isset( $wp_query->query['design_tag'] ) ) {

			if ( ! $wp_query->is_main_query() ) {
				return;
			}

			$wp_query->max_num_pages = $this->pager->get_page_count();
		}
	}

	/**
	 * Alter WP_QUERY to return designs based on URL query.
	 *
	 * @param array     $posts Current array of posts (still pre-query).
	 * @param \WP_Query $query The WP_Query being filtered.
	 * @global \wpdb $wpdb
	 * @since 3.14.0
	 */
	public function alter_query( $posts, $query ) {
		global $wpdb;
        
		// Just return if this isn't the query that we are looking for.
		if (
			( ! $query->is_main_query() )
			|| ( ! isset( $query->query['design_tag'] ) )
		) {
			return $posts;
		}
        
		$wp_user = wp_get_current_user();

		$session = MyStyle()->get_session();

		$term_taxonomy_id = $query->queried_object->term_taxonomy_id;

		// Create a new pager.
		$this->pager = new MyStyle_Pager();

		// Designs per page.
		$this->pager->set_items_per_page( MYSTYLE_DESIGNS_PER_PAGE );

		// Current page number.
		$this->pager->set_current_page_number(
			max( 1, ( isset( $query->query['paged'] ) ? $query->query['paged'] : 0 ) )
		);

		$page_limit = $this->pager->get_items_per_page();
		$page_num   = 1;

		if ( ( isset( $query->query['paged'] ) ) && ( null !== $query->query['paged'] ) ) {
			$page_num = ( $this->pager->get_current_page_number() - 1 ) * $page_limit;
		}

		$design_objs = MyStyle_DesignManager::get_designs_by_term_id(
			$term_taxonomy_id,
			$wp_user,
			$session,
			$page_limit,
			$page_num
		);

		$designs = array();

		foreach ( $design_objs as $design ) {
			if ( $design ) {
				try {
					$title = ( '' === $design->get_title() )
						? 'Design ' . $design->get_design_id()
						: $design->get_title();

					$product_id = $design->get_product_id();

					$product = wc_get_product( $product_id );
					
					$product_name = ( ! $product ? 'product' : $product->get_name() ) ;

					$options = get_option( MYSTYLE_OPTIONS_NAME, array() );

					$design_post               = new stdClass() ;
					$design_post->ID           = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] ;
					$design_post->design_id    = $design->get_design_id() ;
					$design_post->post_author  = $design->get_user_id() ;
					$design_post->post_name    = $title ;
					$design_post->post_type    = 'Design' ;
					$design_post->post_title   = $title ;
					$design_post->post_content = $title . ' custom ' . $product_name ;
                    $design_post->guid = get_site_url() . '/designs/' . $design->get_design_id() ;
                    
					$designs[] = $design_post;
				} catch ( MyStyle_Unauthorized_Exception $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// If unauthorized, skip and go onto the next one.
				}
			}
		}

		$this->pager->set_items( $designs );

		// Total items.
		$term_count = MyStyle_DesignManager::get_total_term_design_count( $term_taxonomy_id, $wp_user, $session );

		$this->pager->set_total_item_count( $term_count );
        
		return $designs;
	}

	/**
	 * Force showing post thumbnail on design archive pages.
	 *
	 * @param bool              $has_thumbnail True if the post has a post thumbnail, otherwise false.
	 * @param int|\WP_Post|null $post          Post ID or WP_Post object. Default is global `$post`.
	 * @param int|false         $thumbnail_id  Post thumbnail ID or false if the post does not exist.
	 * @global \wp_query $wp_query
	 */
	public function has_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {
		global $wp_query;

		if ( isset( $wp_query->query['design_tag'] ) ) {
			return true;
		}

		return $has_thumbnail;
	}

	/**
	 * Load the current design's thumbnail image in The_Loop.
	 *
	 * @param array|false  $image         {
	 *     Array of image data, or boolean false if no image is available.
	 *
	 *     @type string $0 Image source URL.
	 *     @type int    $1 Image width in pixels.
	 *     @type int    $2 Image height in pixels.
	 *     @type bool   $3 Whether the image is a resized image.
	 * }
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size          Requested image size. Can be any registered image size name, or
	 *                                    an array of width and height values in pixels (in that order).
	 * @param bool         $icon          Whether the image should be treated as an icon.
	 * @global \wp_query $wp_query
	 */
	public function wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		global $wp_query;
        
		if ( isset( $wp_query->query['design_tag'] ) ) {
			global $post;
            
			$wp_user = wp_get_current_user();

			$session = MyStyle()->get_session();

			$design = MyStyle_DesignManager::get( $post->design_id, $wp_user, $session );
            
            if ( null !== $design ) {
				$image[0] = $design->get_web_url();
				$image[1] = 200;
				$image[2] = 200;
			}

			return $image;
		}

		return $image;
	}

	/**
	 * Load the current design's permalink in The_Loop.
	 *
	 * @param string  $permalink The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @param bool    $leavename Whether to keep the post name.
	 * @global \WP_Query $wp_query
	 */
	public function post_link( $permalink, $post, $leavename ) {
		global $wp_query;
		if ( isset( $wp_query->query['design_tag'] ) && isset($post->design_id ) ) {
			return get_site_url() . '/designs/' . $post->design_id;
		}

		return $permalink;
	}

	/**
	 * Static function that builds a URL to the page for the tag.
	 *
	 * @param string $slug The tag slug.
	 * @return string Returns a URL that can be used to access the page for the
	 * tag.
	 */
	public static function get_tag_url( $slug ) {
		$url = site_url( 'design-tags' ) . '/' . $slug;

		return $url;
	}

    /**
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array of query vars.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'design_tag_term';

		return $query_vars;
	}

	/**
	 * Added rewrite rule since WordPress 5.5.
	 */
	public function rewrite_rules() {
        
		// Flush rewrite rules for newly created rewrites.
		flush_rewrite_rules();

		add_rewrite_rule(
			'design-tags/([a-zA-Z0-9_-].+)?$',
			'index.php?pagename=design-tags&design_tag_term=$matches[1]',
			'top'
		);
	}

    /**
	 * Filter the post title.
	 *
	 * @param string $title The title of the post.
	 * @param type   $id The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
		
        global $wp_query ;
        
        if (
					( get_the_ID() === $id ) && // Make sure we're in the loop.
					( in_the_loop() ) // Make sure we're in the loop.
			)
        {
            if ( isset( $wp_query->query['design_tag_term'] ) ) {
                
                $term_slug = $wp_query->query['design_tag_term'] ;
                
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

					$title = ucfirst( $term->name ) . ( is_null( $site_wide_title ) ? ' Design Tag' : ' ' . $site_wide_title . ' Community Designs' ) ;
                }
                
            }
        }
        
		return $title;
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
        
        
		if ( isset( $wp_query->query['design_tag_term'] ) ) {
			
			$term_slug = $wp_query->query['design_tag_term'] ;
			
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

				$title['title'] = ucfirst( $term->name ) . ( is_null( $site_wide_title ) ? ' Design Tag' : ' ' . $site_wide_title . ' Community Designs' ) ;
			}
			
		}
        
		return $title;
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
	 * @return MyStyle_Design_Tag_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
