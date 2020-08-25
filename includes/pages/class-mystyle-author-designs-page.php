<?php



/**
 * The MyStyle Author Designs class has hooks for working with the WooCommerce My Account page.
 *
 * @package MyStyle
 * @since 3.13.6
 */

/**
 * MyStyle_Author_Designs class.
 */
class MyStyle_Author_Designs {
    
    /**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Design_Profile_Page
	 */
	private static $instance;
    
	/**
	 * Pager for the design profile index.
	 *
	 * @var MyStyle_Pager
	 */
	private $pager;
    
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
		
        add_action('init', array( &$this, 'rewrite_rules') ) ;
        add_action('query_vars', array( &$this, 'query_vars') ) ;
        add_action( 'template_redirect', array( &$this, 'init' ) );
        add_action( 'loop_start', array( &$this, 'loop_start' ) ) ;
        
	}
    
    public function init() {
        
        $this->set_http_response_code(200) ;
        
        if( get_query_var( 'designpage' ) != false || get_query_var( 'designpage' ) != '' ) {
            
            $username = get_query_var( 'username' ) ;
            $decrypted = $this->encrypt_decrypt('decrypt', $username) ;

            if($decrypted) {
                $user = $decrypted ;
            }
            else {
                $user = get_user_by( 'slug', $username ) ;
            }
            
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
                $user
            );
            $this->pager->set_items( $designs );

            // Total items.
            $this->pager->set_total_item_count(
                MyStyle_DesignManager::get_total_design_count(),
                $user
            );

        }
        
	}
    
    /**
    * Add user designs to loop
    **/
    public function loop_start( $array ) {
        
        if( get_query_var( 'designpage' ) != false || get_query_var( 'designpage' ) != '' ) {
            
            /* @var $pager \Mystyle_Pager phpcs:ignore */
            $pager = $this->get_pager();

            // ---------- Call the view layer ------------------ //
            ob_start();
            require MYSTYLE_TEMPLATES . 'design-profile/index.php';
            $out = ob_get_contents();
            ob_end_clean();
            print $out;
            
            //clear any post data
            $array->posts = array() ;
            return $array ;
        }
            
    }
    
    /**
    * Add rewrite rules for custom author pages
    **/
    public function rewrite_rules() {
        add_rewrite_rule('author/([a-zA-Z0-9_-].+)/([a-z]+)/?$', 'index.php?designpage=$matches[2]&username=$matches[1]', 'top') ;
    }
    
    /**
    * Add custom query vars
    **/
    public function query_vars( $query_vars ) {
        $query_vars[] = 'username';
        $query_vars[] = 'designpage';
        return $query_vars;
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
    * Encrypt and Decrypt user email for url
    **/
    public function encrypt_decrypt($action, $string)
    {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = wp_salt('auth') ;
        $secret_iv = wp_salt('secure_auth') ;

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
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
	 * Gets the pager for the designs index.
	 *
	 * @return MyStyle_Pager Returns the pager for the designs index.
	 */
	public function get_pager() {
		return $this->pager;
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