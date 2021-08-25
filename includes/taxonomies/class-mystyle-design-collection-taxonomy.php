<?php
/**
 * Class for working with the MyStyle Design Collection taxonomy.
 *
 * This class has both static functions and hooks as well as the ability to be
 * instantiated as a singleton instance with various methods.
 *
 * @package MyStyle
 * @since 3.18.5
 */

/**
 * MyStyle_Design_Collection_Taxonomy class.
 */
class MyStyle_Design_Collection_Taxonomy {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Design_Collection_Taxonomy
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'register' ), 10, 0 );
	}

	/**
	 * Returns whether or not the taxonomy exists (is registered with
	 * WordPress).
	 *
	 * @todo Add unit testing for this function.
	 */
	public function exists() {
		return taxonomy_exists( MYSTYLE_COLLECTION_NAME );
	}

	/**
	 * Register the taxonomy.
	 */
	public function register() {
		// If the taxonomy already exists, return.
		if ( $this->exists() ) {
			return;
		}

		register_taxonomy(
			MYSTYLE_COLLECTION_NAME, 'design', array(
				'labels'       => array(
					'name'              => _x( 'Design Collections', 'taxonomy general name', 'mystyle' ),
					'singular_name'     => _x( 'Design Collection', 'taxonomy singular name', 'mystyle' ),
					'search_items'      => __( 'Search Design Collections', 'mystyle' ),
					'all_items'         => __( 'All Design Collections', 'mystyle' ),
					'parent_item'       => __( 'Parent Design Collection', 'mystyle' ),
					'parent_item_colon' => __( 'Parent Design Collection:', 'mystyle' ),
					'edit_item'         => __( 'Edit Design Collection', 'mystyle' ),
					'update_item'       => __( 'Update Design Collection', 'mystyle' ),
					'add_new_item'      => __( 'Add New Design Collection', 'mystyle' ),
					'new_item_name'     => __( 'New Design Collection Name', 'mystyle' ),
					'menu_name'         => __( 'Design Collections', 'mystyle' ),
				),
				// Control the slugs used for this taxonomy.
				'rewrite'      => array(
					'slug' => 'design-collections',
				),
				'public'       => true,
				'show_in_rest' => true,
			)
		);

	}

	/**
	 * Static function that searches for and returns existing design tags using
	 * the passed search phrase.
	 *
	 * This is mostly copy/paste from the wp_ajax_ajax_tag_search function.
	 *
	 * @param string $s The search phase to use.
	 * @return array Returns an array of results.
	 */
	public static function search( $s ) {
		$taxonomy = MYSTYLE_COLLECTION_NAME;

		$comma = _x( ',', 'tag delimiter', 'mystyle' );
		if ( ',' !== $comma ) {
			$s = str_replace( $comma, ',', $s );
		}

		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[ count( $s ) - 1 ];
		}

		$s = trim( $s );

		$results = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $s,
				'fields'     => 'names',
				'hide_empty' => false,
			)
		);

		return $results;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Design_Collection_Taxonomy Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Collection_Taxonomy Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
