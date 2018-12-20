<?php

/**
 * MyStyle_Design_Complete class.
 * The MyStyle_Design_Complete class has hooks for working with what happens
 * after a design is completed.
 *
 * @package MyStyle
 * @since 3.4.0
 */
class MyStyle_Design_Complete {

	/**
	 * Singleton class instance
	 * @var MyStyle_Design_Complete
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_filter('query_vars', array(&$this, 'add_query_vars_filter'), 10, 1);

		// Set the priority to 11 (instead of the default 10) so that our scripts load after jQuery
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'), 11, 0);
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$design_complete = ( intval(get_query_var('design_complete', '0')) == 1 ) ? true : false;

		if ($design_complete) {
			wp_register_script('mystyle-design-complete', MYSTYLE_ASSETS_URL . 'js/design-complete.js');
			wp_enqueue_script('mystyle-design-complete');
		}
	}

	/**
	 * Add design_complete as a custom query var.
	 * @param array $vars
	 * @return string
	 */
	public function add_query_vars_filter($vars) {
		$vars[] = 'design_complete';

		return $vars;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 * @return MyStyle_Cart Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 * @return MyStyle_Design_Complete Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Function that builds the Get Redirect Url.
	 * @return string|null Returns the built Get Redirect Url
	 * URL if one is set, otherwise returns null.
	 */
	static function get_redirect_url( MyStyle_Design $design ) {

		$url = '';
		$product_id = $design->get_product_id();
		$override = get_post_meta($product_id, '_mystyle_customizer_redirect', true);
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());
		if (!empty($options['alternate_design_complete_redirect_url'])) {
			$url = $options['alternate_design_complete_redirect_url'];
		}

		if ($override != '') {
			$url = $override;
		}

		if (!empty($url)) {
			if (strpos($url, '?') == false) {
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
