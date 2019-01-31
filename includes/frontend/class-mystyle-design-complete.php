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
			wp_register_script(
				'mystyle-design-complete', // handle.
				MYSTYLE_ASSETS_URL . 'js/design-complete.js', // source.
				array(), // deps.
				'1.0.0', // version.
				true // load in footer.
			);
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

	/**
	 * Function that builds the redirect URL.
	 *
	 * @param MyStyle_Design $design The MyStyle_Design to use for building the
	 * URL.
	 * @return string|null Returns the built redirect URL if one is set,
	 * otherwise, returns null.
	 */
	public static function get_redirect_url( MyStyle_Design $design ) {

		$product_id            = $design->get_product_id();
		$override_redirect_url = get_post_meta( $product_id, '_mystyle_customizer_redirect', true );
		$url                   = MyStyle_Options::get_alternate_design_complete_redirect_url( $design );

		if ( ! empty( $override_redirect_url ) ) {
			$url = $override_redirect_url;
		}

		if ( ! empty( $url ) ) {
			if ( false === strpos( $url, '?' ) ) {
				$url .= '?';
			} else {
				$url .= '&';
			}
			$url .= 'design_id=' . $design->get_design_id();
			$url .= '&design_complete=1';
		}

		return $url;
	}

}
