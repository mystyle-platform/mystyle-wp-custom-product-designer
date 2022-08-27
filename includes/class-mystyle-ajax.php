<?php
/**
 * The MyStyle_Ajax class has hooks AJAX functionality.
 *
 * @package MyStyle
 * @since 3.18.3
 */

/**
 * MyStyle_Ajax class.
 */
class MyStyle_Ajax {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Ajax
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		// Register hooks.
		$this->add_ajax_events();
	}

	/**
	 * Register methods with WordPress ajax handler (admin-ajax).
	 */
	public function add_ajax_events() {
		$ajax_events = array(
			'design_access_change',
			'design_tag_search',
			'design_tag_add',
			'design_tag_remove',
            'design_collection_search',
			'design_collection_add',
			'design_collection_remove',
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_mystyle_' . $ajax_event, array( &$this, $ajax_event ) );
		}
	}

	/**
	 * Function to handle ajax request to change Design access.
	 */
	public function design_access_change() {

		// Check the nonce.
		check_ajax_referer( 'mystyle_design_access_change_nonce' );

		/* @var $current_user \WP_User The current user. */
		$current_user = wp_get_current_user();

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$design_id = ( isset( $_POST['design_id'] ) ) ? intval( $_POST['design_id'] ) : null;
		$access_id = ( isset( $_POST['access_id'] ) ) ? intval( $_POST['access_id'] ) : null;
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		// Validate.
		if ( ( null === $design_id ) || ( null === $access_id ) ) {
			wp_die( 'Invalid Request!', 422 );
		}

		// Get the Design. Note: this will throw an exception, if the user isn't
		// authorized.
		/* @var $design \MyStyle_Design The current design. */
		try {
			$design = MyStyle_DesignManager::get( $design_id, $current_user );
		} catch ( Exception $ex ) {
			wp_die( '-1' );
		}

		
		// If the design is public but not their's, throw an exception.
		$auth = false ;
		if ( MyStyle_DesignManager::can_user_edit( $design, $current_user ) ) {
			// Authorized.
			$auth = true ;
		}
		
		if( $current_user->has_cap( 'print_url_write' ) ) {
			// Aauthorized.
			$auth = true ;
		}

		if( ! $auth ) {
			wp_die( '-1' ) ;
		}

		// Update and persist the design.
		$design->set_access( $access_id );
		MyStyle_DesignManager::persist( $design );

		wp_die( '1' ); // Success.
	}

	/**
	 * Search design tags.
	 *
	 * This is mostly copy/paste from the wp_ajax_ajax_tag_search function.
	 */
	public function design_tag_search() {
		$taxonomy = MYSTYLE_TAXONOMY_NAME;
		// phpcs:ignore
		$s        = wp_unslash( $_GET['q'] );
		$tax	  = wp_unslash( $_GET['tax'] ) ;
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
		if ( ( 0 === $term_search_min_chars ) || ( strlen( $s ) < $term_search_min_chars ) ) {
			wp_die();
		}

		$results = MyStyle_Design_Tag_Taxonomy::search( $s );

		wp_send_json_success( $results );
	}

	/**
	 * Add a tag to the design.
	 */
	public function design_tag_add() {
		// phpcs:disable
		$tag       = sanitize_text_field( wp_unslash( $_POST['tag'] ) );
		$design_id = intval( wp_unslash( $_POST['design_id'] ) );
		// phpcs:enable

		$user = wp_get_current_user();

		// Adds the tag - throws exception is user isn't authorized.
		MyStyle_DesignManager::add_tag_to_design(
			$design_id,
			$tag,
			$user
		);

		wp_send_json_success( array( 'tag' => $tag ) );
	}

	/**
	 * Remove a tag from the design.
	 */
	public function design_tag_remove() {
		// phpcs:disable
		$tag       = sanitize_text_field( wp_unslash( $_POST['tag'] ) );
		$design_id = intval( $_POST['design_id'] );
		// phpcs:enable

		$user = wp_get_current_user();

		// Removes the tag - throws exception is user isn't authorized.
		MyStyle_DesignManager::remove_tag_from_design(
			$design_id,
			$tag,
			$user
		);

		wp_send_json_success( array( 'tag' => $tag ) );
	}
    
    /**
	 * Search design collection.
	 *
	 * This is mostly copy/paste from the wp_ajax_ajax_collection_search function.
	 */
	public function design_collection_search() {
		$taxonomy = MYSTYLE_COLLECTION_NAME;
		// phpcs:ignore
		$s        = wp_unslash( $_GET['q'] ) ;
		$tax	  = wp_unslash( $_GET['tax'] ) ;

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
		if ( ( 0 === $term_search_min_chars ) || ( strlen( $s ) < $term_search_min_chars ) ) {
			wp_die();
		}

		$results = MyStyle_Design_Collection_Taxonomy::search( $s );

		wp_send_json_success( $results );
	}

	/**
	 * Add a collection to the design.
	 */
	public function design_collection_add() {
		// phpcs:disable
		$collection       = sanitize_text_field( wp_unslash( $_POST['collection'] ) );
		$design_id = intval( wp_unslash( $_POST['design_id'] ) );
		// phpcs:enable

		$user = wp_get_current_user();

		// Adds the collection - throws exception is user isn't authorized.
		try {
			MyStyle_DesignManager::add_collection_to_design(
				$design_id,
				$collection,
				$user
			);
		}
		catch( MyStyle_Unauthorized_Exception $error ) {
			var_dump($error) ; die() ;
		}
		

		wp_send_json_success( array( 'collection' => $collection ) );
	}

	/**
	 * Remove a collection from the design.
	 */
	public function design_collection_remove() {
		// phpcs:disable
		$collection       = sanitize_text_field( wp_unslash( $_POST['collection'] ) );
		$design_id = intval( $_POST['design_id'] );
		// phpcs:enable

		$user = wp_get_current_user();

		// Removes the tag - throws exception is user isn't authorized.
		MyStyle_DesignManager::remove_collection_from_design(
			$design_id,
			$collection,
			$user
		);

		wp_send_json_success( array( 'collection' => $collection ) );
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Ajax Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Ajax Returns the singleton instance of this
	 * class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
