<?php
/**
 * MyStyle Design Collection Page. Renders the MyStyle Design Collections page within the WordPress
 * Administrator.
 *
 * @package MyStyle
 * @since 3.18.5
 */

/**
 * MyStyle_Design_Collections_Page class.
 */
class MyStyle_Design_Collections_Page {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Design_Collections_Page
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
			'Design Collections',
			'Design Collections',
			'manage_options',
			'edit-tags.php?taxonomy=design_collection',
			'',
			62
		);
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Design_Collections_Page
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
