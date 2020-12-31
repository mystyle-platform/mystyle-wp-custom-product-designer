<?php
/**
 * The MyStyle_Author_Designs_Page class has hooks for working with the MyStyle
 * Author Designs page.
 *
 * @package MyStyle
 * @since 3.13.6
 */

/**
 * MyStyle_Author_Designs_Page class.
 */
class MyStyle_Author_Designs_Page {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Author_Designs_Page
	 */
	private static $instance;

	/**
	 * Pager for the author designs index.
	 *
	 * @var MyStyle_Pager
	 */
	private $pager;

	/**
	 * Stores the current (when the class is instantiated as a singleton) status
	 * code. We store it here since php's http_response_code() function wasn't
	 * added until php 5.4.
	 *
	 * See: http://php.net/manual/en/function.http-response-code.php
	 *
	 * @var int
	 */
	private $http_response_code;


	/**
	 * Stores the currently thrown exception (if any) (when the class is
	 * instantiated as a singleton).
	 *
	 * @var MyStyle_Exception
	 */
	private $exception;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'rewrite_rules' ) );
		add_action( 'query_vars', array( &$this, 'query_vars' ) );
		add_action( 'template_redirect', array( &$this, 'set_pager' ) );
		add_action( 'posts_pre_query', array( &$this, 'alter_query' ), 30, 2 );

		add_filter( 'has_post_thumbnail', array( &$this, 'has_post_thumbnail' ), 10, 3 );
		add_filter( 'wp_get_attachment_image_src', array( &$this, 'wp_get_attachment_image_src' ), 10, 4 );
		add_filter( 'post_link', array( &$this, 'post_link' ), 10, 3 );
	}

	/**
	 * Add rewrite rules for custom author pages.
	 */
	public function rewrite_rules() {
		add_rewrite_rule(
			'author/([a-zA-Z0-9_-].+)/([a-z]+)/?$',
			'index.php?designpage=$matches[2]&username=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'author/([a-zA-Z0-9_-].+)/([a-z]+)/page/([0-9]+)?$',
			'index.php?designpage=$matches[2]&username=$matches[1]&paged=$matches[3]',
			'top'
		);
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array of query vars.
	 * @return array Returns the updated query_vars array.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'username';
		$query_vars[] = 'designpage';
		$query_vars[] = 'paged';

		return $query_vars;
	}

	/**
	 * Alter WP_QUERY pager information based in the MyStyle_Pager class
	 *
	 * @global \WP_Query $wp_query
	 */
	public function set_pager() {
		global $wp_query;

		if (
				( false !== get_query_var( 'designpage' ) )
				|| ( '' !== get_query_var( 'designpage' ) )
		) {

			if ( ! $wp_query->is_main_query() ) {
				return;
			}

			$wp_query->max_num_pages = $this->pager->get_page_count();
		}
	}

	/**
	 * Alter WP_QUERY to return designs based on the URL query.
	 *
	 * @param array     $posts Current array of posts (still pre-query).
	 * @param \WP_Query $query The WP_Query being filtered.
	 * @global \wpdb $wpdb
	 * @since 3.14.0
	 */
	public function alter_query( $posts, $query ) {
		// Return the query unaltered if this isn't the main query or our page.
		if (
				( ! $query->is_main_query() )
				|| ( false === get_query_var( 'designpage' ) )
				|| ( '' === get_query_var( 'designpage' ) )
		) {
			return $posts;
		}

		$username  = get_query_var( 'username' );
		$decrypted = $this->encrypt_decrypt( 'decrypt', $username );

		if ( $decrypted ) {
			$user = $decrypted;
		} else {
			$user = get_user_by( 'slug', $username );
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
			MyStyle_DesignManager::get_total_user_design_count( $user ),
			$user
		);

		$pager = $this->get_pager();

		$design_posts = array();

		foreach ( $pager->get_items() as $design ) {
			$title = ( '' === $design->get_title() ) ? 'Design ' . $design->get_design_id() : $design->get_title();

			$product_id = $design->get_product_id();

			$product = wc_get_product( $product_id );

			$options = get_option( MYSTYLE_OPTIONS_NAME, array() );

			$design_post               = new stdClass();
			$design_post->ID           = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ];
			$design_post->design_id    = $design->get_design_id();
			$design_post->post_author  = $design->get_user_id();
			$design_post->post_name    = $title;
			$design_post->post_type    = 'Design';
			$design_post->post_title   = $title;
			$design_post->post_content = $title . ' custom ' . $product->get_name();

			$design_posts[] = $design_post;
		}

		return $design_posts;
	}

	/**
	 * Force showing post thumbnail on design archive pages.
	 *
	 * @param bool             $has_thumbnail true if the post has a post thumbnail, otherwise false.
	 * @param int|WP_Post|null $post          Post ID or WP_Post object. Default is global `$post`.
	 * @param int|false        $thumbnail_id  Post thumbnail ID or false if the post does not exist.
	 */
	public function has_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {
		if (
				( false !== get_query_var( 'designpage' ) )
				|| ( '' !== get_query_var( 'designpage' ) )
		) {
			return true;
		}

		return $has_thumbnail;
	}

	/**
	 * Load the current designs thumbnail image in The_Loop.
	 *
	 * @param array        $image         The current image data array (see below).
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size          Optional. Image size. Accepts any registered image size name, or an array of
	 *                                    width and height values in pixels (in that order). Default 'thumbnail'.
	 * @param bool         $icon          Optional. Whether the image should fall back to a mime type icon. Default false.
	 * @return array|false {
	 *     Array of image data, or boolean false if no image is available.
	 *
	 *     @type string $0 Image source URL.
	 *     @type int    $1 Image width in pixels.
	 *     @type int    $2 Image height in pixels.
	 *     @type bool   $3 Whether the image is a resized image.
	 * }
	 * @global WP_Post $post The current WordPress post.
	 */
	public function wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		global $post;

		// If this isn't our page or there is no design_id, return.
		if (
				( false === get_query_var( 'designpage' ) )
				|| ( '' === get_query_var( 'designpage' ) )
				|| ( ! isset( $post->design_id ) )
		) {
			return $image;
		}

		$wp_user = wp_get_current_user();
		$session = MyStyle()->get_session();
		try {
			$design   = MyStyle_DesignManager::get( $post->design_id, $wp_user, $session );
			$image[0] = $design->get_web_url();
			$image[1] = 200;
			$image[2] = 200;
		} catch ( MyStyle_Unauthorized_Exception $ex ) {
			$response_code = 200;
			status_header( $response_code );

			$this->set_exception( $ex );
			$this->set_http_response_code( $response_code );
		}

		return $image;
	}

	/**
	 * Filter the permalink for the 'designpage' post.
	 *
	 * @param string  $permalink The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @param bool    $leavename Whether to keep the post name.
	 */
	public function post_link( $permalink, $post, $leavename ) {
		if (
				( false !== get_query_var( 'designpage' ) )
				|| ( '' !== get_query_var( 'designpage' ) )
		) {
			$permalink = get_site_url() . '/designs/' . $post->design_id;
		}

		return $permalink;
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
	 * Helper method that encrypts or decrypts the passed string. This is used
	 * for hashing the user email for the URL.
	 *
	 * @param string $action The action to perform. Valid values are "encrypt"
	 * and "decrypt".
	 * @param string $string The string to encrypt or decrypt.
	 */
	public function encrypt_decrypt( $action, $string ) {
		$output = false;

		$encrypt_method = 'AES-256-CBC';
		$secret_key     = wp_salt( 'auth' );
		$secret_iv      = wp_salt( 'secure_auth' );

		// hash.
		$key = hash( 'sha256', $secret_key );

		// iv - encrypt method AES-256-CBC expects 16 bytes.
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( 'encrypt' === $action ) {
			$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
			$output = base64_encode( $output );
		} elseif ( 'decrypt' === $action ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
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
	 * Sets the current exception.
	 *
	 * @param MyStyle_Exception $exception The exception to set as the currently
	 * thrown exception. This is used by the shortcode and view layer.
	 */
	public function set_exception( MyStyle_Exception $exception ) {
		$this->exception = $exception;
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
	 * @return MyStyle_Author_Designs_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
