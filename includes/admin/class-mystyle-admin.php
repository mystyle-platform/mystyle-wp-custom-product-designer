<?php
/**
 * The MyStyle Admin class sets up and controls the MyStyle Plugin administrator
 * interface.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * MyStyle_Admin class.
 */
class MyStyle_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Admin
	 */
	private static $instance;

	/**
	 * Constructor, constructs the admin class and registers hooks.
	 * menu.
	 */
	public function __construct() {
		add_filter( 'plugin_action_links_' . MYSTYLE_BASENAME, array( &$this, 'add_settings_link' ) );

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Init the mystyle admin.
	 */
	public function admin_init() {
		// Add the MyStyle admin stylesheet to the WP admin head.
		wp_register_style( 'myStyleAdminStylesheet', MYSTYLE_ASSETS_URL . 'css/admin.min.css' );
		wp_enqueue_style( 'myStyleAdminStylesheet' );

		// Add the MyStyle admin js file to the WP admin head.
		wp_register_script( 'myStyleAdminJavaScript', MYSTYLE_ASSETS_URL . 'js/admin.js', array(), MYSTYLE_VERSION );
		wp_enqueue_script( 'myStyleAdminJavaScript' );
	}


	/**
	 * Add settings link on plugin page.
	 *
	 * @param array $links An array of existing links for the plugin.
	 * @return array The new array of links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="/wp-admin/admin.php?page=mystyle_settings">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Prints out all settings sections added to a particular settings page
	 *
	 * We use this instead of WordPress' default Settings API so that we
	 * can add custom styling. We have this function here instead of in the
	 * MyStyle_Options_Page class so that it can be used by the MyStyle add-ons,
	 * etc.
	 *
	 * This function is mostly copy pasted from wp-admin/includes/template.php
	 *
	 * @param string $page The slug name of the page whose settings sections you want to output.
	 * @global array $wp_settings_sections Storage array of all settings sections added to admin pages.
	 * @global array $wp_settings_fields Storage array of settings fields and info about their pages/sections.
	 * @since 2.7.0
	 */
	public static function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			echo '<div class="mystyle-admin-box">';
			if ( $section['title'] ) {
				echo '<h2>' . esc_html( $section['title'] ) . '</h2>' . "\n";
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
			echo '</div>';
			echo '<br/>';
		}
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Admin
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
