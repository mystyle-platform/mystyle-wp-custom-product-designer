<?php
/**
 * MyStyle Design Tags Page. Renders the MyStyle Design Tags page within the WordPress
 * Administrator.
 *
 * @package MyStyle
 * @since 3.14.0
 */

/**
 * MyStyle_Design_Tags_Page class.
 */
class MyStyle_Design_Tags_Page {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Design_Tags_Page
	 */
	private static $instance;

	/**
	 * Constructor, constructs the addons page and adds it to the Settings
	 * menu.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'add_page_to_menu' ) );
	}


	/**
	 * Add Design Tags link to admin menu
	 */
	public function add_page_to_menu() {
		$mystyle_hook = 'mystyle';

		add_submenu_page(
			$mystyle_hook,
			'Design Tags',
			'Design Tags',
			'manage_options',
			'edit-tags.php?taxonomy=design_tag',
			'',
			62
		);
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Design_Tags_Page
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
