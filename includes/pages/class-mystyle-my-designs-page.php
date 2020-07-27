<?php

/**
 * The MyStyle My Designs class has hooks for working with the WooCommerce My Account page.
 *
 * @package MyStyle
 * @since 3.13.6
 */

/**
 * MyStyle_Cart class.
 */
class MyStyle_MyDesigns extends MyStyle_Design_Profile_Page {
    
    /**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Design_Profile_Page
	 */
	private static $instance;

    
    public function __construct() {
		add_action( 'init', array( &$this, 'design_endpoints' ) );
        add_filter( 'query_vars', array( &$this, 'design_query_vars' ), 0 );
        add_action( 'woocommerce_account_my-designs_endpoint', array( &$this, 'designs_list' )) ;
        register_activation_hook( __FILE__, array( &$this, 'flush_rewrite_rules' ) );
        register_deactivation_hook( __FILE__, array( &$this, 'flush_rewrite_rules' ) );
        
        //add My Account Menu Item
        add_filter( 'woocommerce_account_menu_items', array( &$this, 'my_account_menu_items' ) );
        
        add_filter( 'body_class', array( &$this, 'body_classes' ) ); 
        add_action( 'template_redirect', array( &$this, 'init' ) );
	}
    
    public function init() {
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
    
    /**
    *register new endpoint for WC My Account page
    **/
    public function design_endpoints() {
        add_rewrite_endpoint( 'my-designs', EP_ROOT | EP_PAGES );
        flush_rewrite_rules();
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