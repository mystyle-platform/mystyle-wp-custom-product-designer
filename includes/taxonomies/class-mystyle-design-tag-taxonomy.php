<?php
/**
 * Class for working with the MyStyle Design Tag taxonomy.
 *
 * This class has both static functions and hooks as well as the ability to be
 * instantiated as a singleton instance with various methods.
 *
 * @package MyStyle
 * @since 3.17.0
 */

/**
 * MyStyle_Design_Tag_Taxonomy class.
 */
class MyStyle_Design_Tag_Taxonomy {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Design_Tag_Taxonomy
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
		return taxonomy_exists( MYSTYLE_TAXONOMY_NAME );
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
			MYSTYLE_TAXONOMY_NAME, 'design', array(
				'labels'       => array(
					'name'              => _x( 'Design Tags', 'taxonomy general name', 'mystyle' ),
					'singular_name'     => _x( 'Design Tag', 'taxonomy singular name', 'mystyle' ),
					'search_items'      => __( 'Search Design Tags', 'mystyle' ),
					'all_items'         => __( 'All Design Tags', 'mystyle' ),
					'parent_item'       => __( 'Parent Design Tag', 'mystyle' ),
					'parent_item_colon' => __( 'Parent Design Tag:', 'mystyle' ),
					'edit_item'         => __( 'Edit Design Tag', 'mystyle' ),
					'update_item'       => __( 'Update Design Tag', 'mystyle' ),
					'add_new_item'      => __( 'Add New Design Tag', 'mystyle' ),
					'new_item_name'     => __( 'New Design Tag Name', 'mystyle' ),
					'menu_name'         => __( 'Design Tags', 'mystyle' ),
				),
				// Control the slugs used for this taxonomy.
				'rewrite'      => array(
					'slug' => 'design-tags',
				),
				'public'       => true,
				'show_in_rest' => true,
			)
		);

	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Design_Tag_Taxonomy Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Tag_Taxonomy Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
