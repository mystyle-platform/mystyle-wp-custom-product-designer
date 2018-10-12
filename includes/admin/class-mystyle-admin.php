<?php

/**
 * MyStyle Admin class.
 * The MyStyle Admin class sets up and controls the MyStyle Plugin administrator
 * interace.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyle_Admin {

	/**
	 * Singleton instance
	 * @var MyStyle_Admin
	 */
	private static $instance;

	/**
	 * Constructor, constructs the admin class and registers hooks.
	 * menu.
	 */
	public function __construct() {
		add_filter('plugin_action_links_' . MYSTYLE_BASENAME, array(&$this, 'add_settings_link'));

		add_action('admin_init', array(&$this, 'admin_init'));
	}

	/**
	 * Init the mystyle admin
	 */
	public function admin_init() {
		//Add the MyStyle admin stylesheet to the WP admin head
		wp_register_style('myStyleAdminStylesheet', MYSTYLE_ASSETS_URL . 'css/admin.css');
		wp_enqueue_style('myStyleAdminStylesheet');

		//Add the MyStyle admin js file to the WP admin head
		wp_register_script('myStyleAdminJavaScript', MYSTYLE_ASSETS_URL . 'js/admin.js');
		wp_enqueue_script('myStyleAdminJavaScript');
	}

	/**
	 * Add settings link on plugin page
	 * @param array $links An array of existing links for the plugin
	 * @return array The new array of links
	 */
	public function add_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=mystyle">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Get the singleton instance.
	 * @return MyStyle_Admin
	 */
	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
