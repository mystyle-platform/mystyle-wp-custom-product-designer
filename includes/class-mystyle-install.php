<?php
/**
 *
 * The MyStyle_Install class has functions for activating/installing the plugin.
 *
 * @package MyStyle
 * @since 0.5
 */

/**
 * MyStyle_Install class.
 */
class MyStyle_Install {

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * @global $wpdb;
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	/**
	 * Delta/Alter any tables.  Currently this does the same thing as
	 * create_tables.
	 */
	public static function delta_tables() {
		self::create_tables();
	}

	/**
	 * Get Table schema.
	 *
	 * @global $wpdb
	 * @return string Returns a string of SQL.
	 */
	public static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= " DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		$schema  = '';
		$schema .= MyStyle_Design::get_schema() . $collate . ';';
		$schema .= MyStyle_Session::get_schema() . $collate . ';';

		return $schema;
	}

	/**
	 * Called when the plugin is activated.
	 */
	public static function activate() {
		if ( ! MyStyle_Customize_Page::exists() ) {
			MyStyle_Customize_Page::create();
		}
		if ( ! MyStyle_Design_Profile_Page::exists() ) {
			MyStyle_Design_Profile_Page::create();
		}

		MyStyle_My_Designs_Page::get_instance()->flush_rewrite_rules();

		self::create_tables();
	}

	/**
	 * Called when the plugin is deactivated.
	 */
	public static function deactivate() {
		// Do nothing at this point.
	}

	/**
	 * Function called when MyStyle is uninstalled.
	 */
	public static function uninstall() {
		delete_option( MYSTYLE_NOTICES_NAME );
	}

	/**
	 * Function called when MyStyle is upgraded.
	 *
	 * @param string $old_version The version that you are upgrading from.
	 * @param string $new_version The version that you are upgrading to.
	 * @todo Add unit testing
	 */
	public static function upgrade( $old_version, $new_version ) {
		// Delta the database tables.
		self::delta_tables();

		// Add the Design page if upgrading from less than 1.4.0 ( versions that were before this page existed ).
		// Changed to v1.4.1 ( with exists check ) because 1.4.0 wasn't working properly.
		if ( version_compare( $old_version, '1.4.1', '<' ) ) {
			if ( ! MyStyle_Design_Profile_Page::exists() ) {
				MyStyle_Design_Profile_Page::create();
			}
		}

		// Versions prior to 1.7.0 were creating an exorbitant number of
		// sessions. Here we purge them on upgrade.
		if ( version_compare( $old_version, '1.7.0', '<' ) ) {
			MyStyle_SessionManager::purge_abandoned_sessions();
		}

		// flush rewrite rules for newly created rewrites
		flush_rewrite_rules();

		// Add the Design tage page if upgrading from less than 3.14.0 ( versions that were before this page existed ).
		if ( version_compare( $old_version, '3.14.0', '<' ) ) {
			if ( ! MyStyle_Design_Tag_Page::exists() ) {
				MyStyle_Design_Tag_Page::create();
			}
		}

		// fix Design tag post status to 'draft'
		if ( version_compare( $old_version, '3.14.6', '<' ) ) {
			$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
			$post_id = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ];

			$update_post = get_post( $post_id );

			if ( $update_post->post_status == 'private' ) {
				MyStyle_Design_Tag_Page::fix();
			}
		}

		$upgrade_notice = MyStyle_Notice::create( 'notify_upgrade', 'Upgraded version from ' . $old_version . ' to ' . $new_version . '.' );
		mystyle_notice_add_to_queue( $upgrade_notice );
	}

}
