<?php
/**
 * The MyStyle_Author_Designs_Endpoint class has hooks for working with the
 * MyStyle Author Designs page.
 *
 * It registers endpoints at /author using WordPress' add_rewrite_rule function.
 *
 * Formerly the MyStyle_Author_Endpoint class.
 *
 * @package MyStyle
 * @since 3.13.6
 */

/**
 * MyStyle_Author_Designs_Endpoint class.
 */
class MyStyle_Author_Designs_Endpoint {

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_Author_Designs_Endpoint
	 */
	private static $instance;

	/**
	 * Stores the current user.
	 *
	 * @var \WP_User
	 */
	private $user;

	/**
	 * Stores the current session.
	 *
	 * @var \MyStyle_Session
	 */
	private $session;

	/**
	 * Stores the author whose designs are being retrieved.
	 *
	 * @var \WP_User
	 */
	private $author;

	/**
	 * Pager for the author designs index.
	 *
	 * @var \MyStyle_Pager
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
	 * Stores the currently thrown exception (if any).
	 *
	 * @var \MyStyle_Exception
	 */
	private $exception;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'query_vars', array( &$this, 'add_query_vars' ), 1, 1 );
		add_action( 'init', array( &$this, 'register_endpoints' ), 1, 0 );
		add_action( 'template_redirect', array( &$this, 'init' ), 10, 0 );
		add_action( 'the_content', array( &$this, 'output' ), 10, 1 );

		add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
	}

	/**
	 * Determines if the current page/endpoint is the Author Designs Endpoint
	 * (/author/<username>/designs)
	 *
	 * @return bool Returns true if we are currently on the Author Designs
	 * Endpoint, otherwise, returns false.
	 */
	public function is_current_page() {
		if (
				( is_admin() )
				|| ( ! is_main_query() )
		) {
			return false;
		}

		if (
			( false !== get_query_var( 'designpage' ) )
			&& ( '' !== get_query_var( 'designpage' ) )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Init the class.
	 */
	public function init() {
		if ( ! $this->is_current_page() ) {
			return;
		}

		// Set the user and author.
		$this->user   = wp_get_current_user();
		$this->author = $this->determine_author();

		// ------- SET UP THE PAGER ------------//
		// Create a new pager.
		$this->pager = new MyStyle_Pager();

		// Designs per page.
		$this->pager->set_items_per_page( MYSTYLE_DESIGNS_PER_PAGE );

		// Current page number.
		$this->pager->set_current_page_number(
			$this->get_current_page_num()
		);

		// Pager items.
		$designs = MyStyle_DesignManager::get_user_designs(
			$this->pager->get_items_per_page(),
			$this->pager->get_current_page_number(),
			$this->author, // Design author.
			$this->user // Current user.
		);
		$this->pager->set_items( $designs );

		// Total items.
		$this->pager->set_total_item_count(
			MyStyle_DesignManager::get_total_user_design_count(
				$this->author,
				$this->user
			)
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
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array of query vars.
	 * @return array Returns the updated query_vars array.
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'username';
		$query_vars[] = 'designpage';
		$query_vars[] = 'pagex'; // Note: we use pagex to keep WP from interfering.

		return $query_vars;
	}

	/**
	 * Add rewrite rules for custom author design pages.
	 */
	public function register_endpoints() {
		add_rewrite_rule(
			'author/([a-zA-Z0-9_-].+)/([a-z]+)/?$',
			'index.php?designpage=$matches[2]&username=$matches[1]',
			'top'
		);

		// Note: we use pagex to keep WP from interfering.
		add_rewrite_rule(
			'author/([a-zA-Z0-9_-].+)/([a-z]+)/([0-9]+)?$',
			'index.php?designpage=$matches[2]&username=$matches[1]&pagex=$matches[3]',
			'top'
		);
	}

	/**
	 * Flush rewrite rules. Called on Plugin activation and deactivation.
	 */
	public function flush_rewrite_rules() {
		$this->register_endpoints();
		flush_rewrite_rules();
	}

	/**
	 * Output the content.
	 *
	 * @param string $content The content.
	 */
	public function output( $content ) {
		if ( ! $this->is_current_page() ) {
			return $content;
		}

		// Set up variables for view layer.
		$pager                   = $this->get_pager();
		$current_author_base_url
			= self::get_author_url( $this->author );

		// ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'author-designs.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Filter the post title.
	 *
	 * @param string $title The title of the post.
	 * @param type   $id The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
		// Return unaltered if not the current page.
		if ( ! $this->is_current_page() ) {
			return $title;
		}

		// Return unaltered if not in the loop.
		if (
				( empty( $id ) )
				|| ( get_the_ID() !== $id ) // Make sure we're in the loop.
				|| ( ! in_the_loop() ) // Make sure we're in the loop.
		) {
			return $title;
		}

		$title = 'Designs by ' . $this->author->display_name;

		return $title;
	}

	/**
	 * Static function that builds a URL to the Author Designs Endpoint
	 * including URL parameters to load the page for the passed author.
	 *
	 * @param mixed $author The author.
	 * @return string Returns a URL that can be used to view the page.
	 */
	public static function get_author_url( $author ) {
		$url = site_url( 'author' )
			. '/' . ( ( is_string( $author ) )
				? $author
				: $author->user_nicename )
			. '/designs/';

		return $url;
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
	 * Sets the current HTTP response code.
	 *
	 * @param int $http_response_code The HTTP response code to set as the
	 * currently set response code. This is used by the shortcode and view
	 * layer. We set it as a variable since it is difficult to retrieve in
	 * PHP < 5.4.
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
	 * @return int Returns the current HTTP response code. This is used by
	 * the shortcode and view layer.
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
	 * @param \MyStyle_Exception $exception The exception to set as the currently
	 * thrown exception. This is used by the shortcode and view layer.
	 */
	public function set_exception( MyStyle_Exception $exception ) {
		$this->exception = $exception;
	}

	/**
	 * Gets the current exception.
	 *
	 * @return MyStyle_Exception Returns the currently thrown MyStyle_Exception
	 * if any. This is used by the view layer.
	 */
	public function get_exception() {
		return $this->exception;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Author_Designs_Endpoint Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private helper method that gets the current page number.
	 *
	 * @return int Returns the current page number.
	 */
	private function get_current_page_num() {
		$num = 1;

		$pagex = get_query_var( 'pagex' );

		if ( $pagex ) {
			$num = intval( $pagex );
		}

		return $num;
	}

	/**
	 * Private helper method that determines the author whose designs are being
	 * retrieved.
	 *
	 * @return WP_User Gets the author whose designs are being retrieved.
	 */
	private function determine_author() {
		$username  = get_query_var( 'username' );
		$decrypted = $this->encrypt_decrypt( 'decrypt', $username );

		if ( $decrypted ) {
			$author = $decrypted;
		} else {
			$author = get_user_by( 'slug', $username );
		}

		return $author;
	}

}
