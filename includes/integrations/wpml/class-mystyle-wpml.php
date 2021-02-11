<?php
/**
 *
 * Class for integrating with the WPML (The WordPress Multilingual) plugin.
 *
 * @package MyStyle
 * @since 3.13.2
 */

/**
 * MyStyle_Wpml class.
 */
class MyStyle_Wpml {

	/**
	 * The name of the db table where this WPML translations are stored.
	 *
	 * Note: this is without the db prefix.
	 *
	 * @var string
	 */
	const TRANSLATIONS_TABLE_NAME = 'icl_translations';

	/**
	 * The name of the cookie that holds the current language.
	 *
	 * @var string
	 */
	const CURRENT_LANGUAGE_COOKIE_NAME = '_icl_current_language';

	/**
	 * The key of the WPML options.
	 *
	 * @var string
	 */
	const WPML_OPTIONS_KEY = 'icl_sitepress_settings';

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Wpml
	 */
	private static $instance;

	/**
	 * Returns the name of the WPML translations table.
	 *
	 * @global type $wpdb
	 * @return string Returns the name of the WPML translations table.
	 */
	public function get_translations_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TRANSLATIONS_TABLE_NAME;
	}

	/**
	 * Function that determines if the the WPML plugin is installed.
	 *
	 * Note: we test against database tables so that this function will work
	 * regardless of plugin load order.
	 *
	 * @returns boolean Returns true if the WPML plugin is installed. Otherwise,
	 * returns false.
	 * @global type $wpdb
	 */
	public function is_installed() {
		global $wpdb;

		$table_name = $this->get_translations_table_name();

		$found_table_name = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( $found_table_name === $table_name ) {
			$is_installed = true;
		} else {
			$is_installed = false;
		}

		return $is_installed;
	}

	/**
	 * Function that determines if the page with an id of $translation_id is a
	 * translation of the page with an id of $parent_id.
	 *
	 * @param int $parent_id The post id of the parent page.
	 * @param int $translation_id The post id of the page that might be a
	 * translation.
	 * @returns boolean Returns true if the page is a translation of the
	 * parent page. Returns false if the page is not a translation of the parent
	 * page. Also returns false if WPML isn't found.
	 * @global type $wpdb
	 */
	public function is_translation_of_page( $parent_id, $translation_id ) {
		global $wpdb;

		// If WPML isn't installed, just return false.
		if ( ! $this->is_installed() ) {
			return false;
		}

		$ret = $wpdb->query(
			$wpdb->prepare(
				'SELECT 1 '
				. "FROM {$wpdb->prefix}icl_translations "
				. 'WHERE element_type = \'post_page\'
				 AND element_id = %d
				 AND trid = (SELECT trid '
					. "FROM {$wpdb->prefix}icl_translations "
					. 'WHERE element_type = \'post_page\'
					AND element_id = %d
					)
				',
				array( $translation_id, $parent_id )
			)
		);

		$is_translation_of_page = boolval( $ret );

		return $is_translation_of_page;
	}

	/**
	 * Gets the default WPML language.
	 *
	 * @return string|null Returns the default WPML language (or null) if not
	 * set.
	 */
	public function get_default_language() {
		$default_language = null;

		$wpml_options = get_option( self::WPML_OPTIONS_KEY, array() );
		if ( array_key_exists( 'default_language', $wpml_options ) ) {
			$default_language = $wpml_options['default_language'];
		}

		return $default_language;
	}

	/**
	 * Returns the current language (ex: "fr"). If the language isn't set, this
	 * method returns null. The current language is retrieved from the cookies.
	 *
	 * @return string|null Returns the current language (ex: "fr") or null if no
	 * language is set.
	 */
	public function get_current_language() {
		$language = null;

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		if ( isset( $_COOKIE[ self::CURRENT_LANGUAGE_COOKIE_NAME ] ) ) {
			$language = wp_unslash( sanitize_key( $_COOKIE[ self::CURRENT_LANGUAGE_COOKIE_NAME ] ) );
		}
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		return $language;
	}

	/**
	 * Returns the current translation language (ex: "fr"). This works the same
	 * as the get_current_language method except that it returns null if the
	 * current language is also the default language.
	 *
	 * @return string|null Returns the current translation language (ex: "fr").
	 * Returns null if no language is set or if the current language is the
	 * default language.
	 */
	public function get_current_translation_language() {
		$ret = null;

		$current_lang = $this->get_current_language();

		if ( null !== $current_lang ) {
			$default_lang = $this->get_default_language();

			if ( null !== $default_lang ) {
				if ( $current_lang !== $default_lang ) {
					$ret = $current_lang;
				}
			}
		}

		return $ret;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Wpml Returns the singleton instance
	 * of this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Wpml Returns the singleton instance
	 * of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
