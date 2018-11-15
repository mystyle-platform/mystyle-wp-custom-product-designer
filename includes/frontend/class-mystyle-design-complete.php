<?php
/**
 * MyStyle_Design_Complete class.
 * The MyStyle_Design_Complete class has hooks for working with what happens
 * after a design is completed.
 *
 * @package MyStyle
 * @since 3.4.0
 */

/**
 * MyStyle_Design_Complete class.
 */
class MyStyle_Design_Complete {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Design_Complete
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_filter( 'query_vars', array( &$this, 'add_query_vars_filter' ), 10, 1 );

		// Set the priority to 11 ( instead of the default 10 ) so that our scripts load after jQuery.
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 11, 0 );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$design_complete = ( 1 === intval( get_query_var( 'design_complete', '0' ) ) ) ? true : false;

		if ( $design_complete ) {
			wp_register_script( 'mystyle-design-complete', MYSTYLE_ASSETS_URL . 'js/design-complete.js' );
			wp_enqueue_script( 'mystyle-design-complete' );
		}
	}

	/**
	 * Add design_complete as a custom query var.
	 *
	 * @param array $vars The current query vars.
	 * @return array Returns the modified query vars.
	 */
	public function add_query_vars_filter( $vars ) {
		$vars[] = 'design_complete';

		return $vars;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Cart Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Complete Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
