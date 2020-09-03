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
        
        add_action( 'template_redirect', array( &$this, 'set_pager' ) );
        add_action( 'posts_pre_query', array( &$this, 'alter_query'), 25, 2 );
        
        add_filter( 'has_post_thumbnail', array( &$this, 'has_post_thumbnail'), 10, 3 ) ;
        add_filter( 'wp_get_attachment_image_src', array( &$this, 'wp_get_attachment_image_src'), 10, 4 ) ;
        add_filter( 'post_link', array( &$this, 'post_link'), 10, 3 ) ;

	}
    
    public function set_pager() {
        global $wp_query;
        if(isset($wp_query->query['design_tag'])) {
            


            if ( !$wp_query->is_main_query() )
              return;

            $wp_query->max_num_pages = 10 ;
        }
    }
    
    public function alter_query( $posts, $q ) {
        
        if($q->is_main_query()) {
            
            if(isset($q->query['design_tag'])) {
                global $wpdb ;
                
                $term_id = $q->queried_object->term_id ;
                
                $page_limit = 1 ;
                
                $sql = "SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = " . $term_id . " LIMIT " . $page_limit ; 
                
                if(null !== $q->query['paged']) {
                    $page_num = ($q->query['paged'] - 1) * $page_limit ;
                    $sql .= " OFFSET " . $page_num ;
                }
                
                $terms = $wpdb->get_results($sql) ;
                
                $designs = array() ;
                
                foreach( $terms as $term) {
                    $design = MyStyle_DesignManager::get( $term->object_id ) ;
                    
                    $title = ( "" == $design->get_title() ? "Design " . $design->get_design_id() : $design->get_title() ) ;
                    
                    $product_id = $design->get_product_id() ;
                    
                    $product = wc_get_product( $product_id ) ;
                    
                    $design_post = new stdClass() ;
                    $design_post->ID = 1 ; //$design->get_design_id() ;
                    $design_post->design_id = $design->get_design_id() ;
                    $design_post->post_author = $design->get_user_id() ;
                    $design_post->post_name = $title ;
                    $design_post->post_type = 'Design' ;
                    $design_post->post_title = $title ;
                    $design_post->post_content = $title . ' custom ' . $product->get_name() ;
                    
                    $designs[] = $design_post ;
                }
                
                return $designs ;
            }
        }
        
        return $posts ;
         
    }
    
    public function has_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {
        
        global $wp_query ;
        
        if(isset($wp_query->query['design_tag'])) {
            return true ;
        }
        
        return $has_thumbnail ;
    }
    
    public function wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
        global $wp_query ;
        
        if(isset($wp_query->query['design_tag'])) {
            global $post ;

            $design = MyStyle_DesignManager::get( $post->design_id ) ;

            $image[0] = $design->get_web_url() ;
            $image[1] = 200 ;
            $image[2] = 200 ;

            return $image ;
        }
        
        return $image ;
    }
    
    public function post_link( $permalink, $post, $leavename ) {
        
        global $wp_query ;
        
        if(isset($wp_query->query['design_tag'])) {
            return get_site_url() . '/designs/' . $post->design_id ;
        }
        
        return $permalink ;
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