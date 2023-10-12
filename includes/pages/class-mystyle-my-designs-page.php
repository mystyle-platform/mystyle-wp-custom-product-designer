<?php
/**
 * The MyStyle_My_Designs_Page singleton class has hooks for working with the
 * MyStyle My Designs page.
 *
 * @package MyStyle
 * @since 3.13.6
 */

/**
 * MyStyle_My_Designs_Page class.
 */
class MyStyle_My_Designs_Page {

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_My_Designs_Page
	 */
	private static $instance;

	/**
	 * Stores the current user (when the class is instantiated as a singleton).
	 *
	 * @var \WP_User
	 */
	private $user;

	/**
	 * Stores the current session (when the class is instantiated as a
	 * singleton).
	 *
	 * @var \MyStyle_Session
	 */
	private $session;

	/**
	 * Stores the current exception (when the class is instantiated as a
	 * singleton).
	 *
	 * @var \MyStyle_Exception
	 */
	private $exception;

	/**
	 * Pager for the design profile index.
	 *
	 * @var \MyStyle_Pager
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

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->http_response_code = 200;

		add_action( 'init', array( &$this, 'design_endpoints' ) );
		add_action( 'init', array( &$this, 'flush_rewrite_rules' )) ; 

		add_filter( 'query_vars', array( &$this, 'design_query_vars' ), 0 );
		add_action( 'woocommerce_account_my-designs_endpoint', array( &$this, 'designs_list' ) );

		// Add My Account Menu Item.
		add_filter( 'woocommerce_account_menu_items', array( &$this, 'my_account_menu_items' ) );

		add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
		add_filter( 'body_class', array( &$this, 'body_classes' ) );
		add_action( 'template_redirect', array( &$this, 'init' ) );
		add_filter( 'woocommerce_breadcrumb_defaults', array( &$this, 'breadcrumbs' ) );

		
	}

	/**
	 * Init the class.
	 *
	 * @global \WP_Query $wp_query
	 */
	public function init() {

		global $wp_query;

		//UsersWP support
		add_filter( 'uwp_get_profile_tabs', array( &$this, 'uwp_add_profile_tabs' ), 10, 1 ) ;
		add_action( 'uwp_profile_mystyle_designs_tab_content', array( &$this, 'uwp_add_profile_mystyle_designs_tab_content'), 10, 2 ) ;

		if ( isset( $wp_query->query_vars['my-designs'] ) ) {
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
	 * Register a new endpoint for the My Designs page.
	 */
	public function design_endpoints() {
		add_rewrite_endpoint( 'my-designs', EP_ROOT | EP_PAGES );
	}

	/**
	 * Register query variables.
	 *
	 * @param array $vars The query variables.
	 */
	public function design_query_vars( $vars ) {
		$vars[] = 'my-designs';
		$vars[] = 'page';

		return $vars;
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
	 * UsersWP support
	 * 
	 * @param array $tabs
	 */
	public function uwp_add_profile_tabs($tabs) {
		$new_tab = (object) array(
            "id" => "4",
            "form_type" => "profile-tabs",
            "sort_order" => "4",
            "tab_layout" => "profile",
            "tab_type" => "standard",
            "tab_level" => "0",
            "tab_parent" => "0",
            "tab_privacy" => "0",
            "user_decided"=> "1",
            "tab_name" => "Designs",
            "tab_icon" => "fas fa-brush",
            "tab_key" => "mystyle_designs"
        ) ;
        
        $userswp_profile = new UsersWP_Profile() ;
        
        $content = $userswp_profile->tab_content($new_tab) ;
        
        $new_tab->tab_content = $content ;
        $new_tab->tab_content_rendered = $content ;
        
        $tabs[] = (array) $new_tab ;
		
		return $tabs;
	}

	public function uwp_add_profile_mystyle_designs_tab_content($user, $tab) {
		// Set the user.
		/* @var $user \WP_User phpcs:ignore */
		$this->set_user( $user ) ;
		
		$this->designs_list() ;
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

	/**
	 * Flush rewrite rules on Plugin activation and deactivation.
	 */
	public function flush_rewrite_rules() {
		
		flush_rewrite_rules();
	}

	/**
	 * Display the user designs list.
	 */
	public function designs_list() {
		$count = MyStyle_DesignManager::get_total_user_design_count( $this->user ) ;

		/* @var $pager \Mystyle_Pager phpcs:ignore */
		$pager = new MyStyle_Pager();

		$pager->set_items_per_page( $count ) ; // @TODO add pager for more then 100 designs
		
		$page_num = ( isset( $_GET['paged'] ) ? absint($_GET['paged']) : 1 ) ;

		$pager->set_current_page_number( $page_num );

		//get user designs
		$designs = MyStyle_DesignManager::get_user_designs(
			$pager->get_items_per_page(),
			$pager->get_current_page_number(),
			$this->user
		);

		$pager->set_items( $designs );

		$pager->set_total_item_count(
			$count
		);
        
        if( ! $pager->get_items() || count( $pager->get_items() ) == 0 ) {
            $out = '<h3>No designs yet. <a href="/">Create one now!</a></h3>' ;
        }
        else {
            // ---------- Call the view layer ------------------ //
            ob_start();
            require MYSTYLE_TEMPLATES . 'design-profile/index.php';
            $out = ob_get_contents();
            ob_end_clean();
        }

		echo $out; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
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
			$title = __( 'My Designs', 'mystyle' );

			remove_filter( 'the_title', array( &$this, 'filter_title' ) );
		}

		return $title;
	}

	/**
	 * Add design profile body class name.
	 *
	 * @param string $classes Current assigned body classes.
	 */
	public function body_classes( $classes ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars['my-designs'] ) ) {
			$classes[] = 'mystyle-my-designs';
		}

		return $classes;
	}

	/**
	 * Init the singleton for an user designindex request.
	 */
	private function init_user_index_request() {
		// ------- SET UP THE PAGER ------------//
		// Create a new pager.
		$this->pager = new MyStyle_Pager();

		// Designs per page.
		$this->pager->set_items_per_page( MYSTYLE_DESIGNS_PER_PAGE ); // @TODO add pager for more then 100 designs

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
		$this->pager->set_total_item_count( MyStyle_DesignManager::get_total_user_design_count( $this->user ) );

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

	public function set_exception( MyStyle_Exception $exception ) {
		$this->exception = $exception;
	}


	/**
	 * Sets the current http response code.
	 *
	 * @param int $http_response_code The http response code to set as the
	 * currently set response code. This is used by the shortcode and view
	 * layer. We set it as a variable since it is difficult to retrieve in
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
	 * @return MyStyle_My_Designs_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
