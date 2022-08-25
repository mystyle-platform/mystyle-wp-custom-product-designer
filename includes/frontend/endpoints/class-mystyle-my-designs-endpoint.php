<?php
/**
 * The MyStyle_My_Designs_Endpoint singleton class has hooks for working with
 * the /my-account/my-designs/ WooCommerce endpoint.
 *
 * @package MyStyle
 * @since 3.13.6
 */

/**
 * MyStyle_My_Designs_Endpoint class.
 */
class MyStyle_My_Designs_Endpoint {

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_My_Designs_Endpoint
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
	 * Pager for the my designs index.
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
		$this->http_response_code = 200;

		add_action( 'init', array( &$this, 'register_endpoints' ) );
		add_filter( 'query_vars', array( &$this, 'add_query_vars' ), 0 );

		add_action( 'woocommerce_account_my-designs_endpoint', array( &$this, 'output' ), 10, 1 );
		add_filter( 'woocommerce_account_my-designs_query', 'custom_my_account_orders', 10, 1 );

		// Add My Account Menu Item.
		add_filter( 'woocommerce_account_menu_items', array( &$this, 'my_account_menu_items' ) );

		add_filter( 'body_class', array( &$this, 'body_classes' ) );
		add_action( 'template_redirect', array( &$this, 'init' ) );
		add_filter( 'woocommerce_breadcrumb_defaults', array( &$this, 'breadcrumbs' ) );
	}

	/**
	 * Determines if the current page/endpoint is the My Designs Endpoint
	 * (/my-account/my-designs)
	 *
	 * @return bool Returns true if we are currently on the my-designs page,
	 * otherwise, returns false.
	 */
	public function is_current_page() {
		if (
				( is_admin() )
				|| ( ! is_main_query() )
				|| ( ! is_account_page() )
		) {
			return false;
		}

		if ( false !== get_query_var( 'my-designs' ) ) {
			return true;
		}

		if (
			( false !== get_query_var( 'pagename' ) )
			&& ( 'my-designs' === get_query_var( 'pagename' ) )
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

		// Set the user and session.
		$this->user    = wp_get_current_user();
		$this->session = MyStyle()->get_session();

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
			$this->user, // Design author.
			$this->user // Current user.
		);
		$this->pager->set_items( $designs );

		// Total items.
		$this->pager->set_total_item_count(
			MyStyle_DesignManager::get_total_user_design_count(
				$this->user, // Design author.
				$this->user // Current user (for access control).
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
		$query_vars[] = 'my-designs';

		return $query_vars;
	}

	/**
	 * Register a new endpoint for the My Designs page.
	 */
	public function register_endpoints() {
		add_rewrite_endpoint( 'my-designs', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add menu item to the WC My Account page.
	 *
	 * @param array $items The current menu items.
	 */
	public function my_account_menu_items( $items ) {
		$new_items               = array();
		$new_items['my-designs'] = __( 'My Designs', 'mystyle' );

		return $this->insert_after_helper( $items, $new_items, 'dashboard' );
	}

	/**
	 * Add My Designs breadcrumb.
	 *
	 * @param array $defaults The default breadcrumbs.
	 */
	public function breadcrumbs( $defaults ) {
		$defaults[] = 'My Designs';

		return $defaults;
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
	 */
	public function output() {
		// Set up variables for the view layer.
		$pager = $this->pager;

		// ---------- Call the view layer ------------------ //
		ob_start();
		require MYSTYLE_TEMPLATES . 'my-designs.php';
		$out = ob_get_contents();
		ob_end_clean();

		echo $out; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add design profile body class name.
	 *
	 * @param string $classes Current assigned body classes.
	 */
	public function body_classes( $classes ) {

		if ( $this->is_current_page() ) {
			$classes[] = 'mystyle-design-profile';
		}

		return $classes;
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
	 * @return int Returns the current HTTP response code. This is used by the
	 * view layer.
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
	 * thrown exception. This is used by the view layer.
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
	 * @return MyStyle_My_Designs_Endpoint Returns the singleton instance of
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
	 * @return int Gets the current page number.
	 */
	private function get_current_page_num() {
		$num = 1;

		$my_designs = get_query_var( 'my-designs' );

		if ( $my_designs ) {
			$num = intval( $my_designs );
		}

		return $num;
	}

	/**
	 * Private helper method that adds new items into an array after a selected
	 * item.
	 *
	 * @param array  $items The current menu items.
	 * @param array  $new_items The new items to insert.
	 * @param string $after The item to insert after.
	 * @return array Returns the modified items.
	 */
	private function insert_after_helper( $items, $new_items, $after ) {
		// Search for the item position and +1 since is after the selected item key.
		$position = array_search( $after, array_keys( $items ), true ) + 1;

		// Insert the new item.
		$array  = array_slice( $items, 0, $position, true );
		$array += $new_items;
		$array += array_slice( $items, $position, count( $items ) - $position, true );

		return $array;
	}
}
