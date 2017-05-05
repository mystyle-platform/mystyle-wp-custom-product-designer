<?php

/**
 * MyStyle_Install class. 
 * 
 * The MyStyle_Install class has functions for activating/installing the plugin.
 *
 * @package MyStyle
 * @since 0.5
 */
class MyStyle_Install {
    
    
    /**
     * Set up the database tables which the plugin needs to function.
     */
    public static function create_tables() {
        global $wpdb;

        //$wpdb->hide_errors();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

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
    * Get Table schema
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

        $schema = '';
        $schema .= MyStyle_Design::get_schema() .  $collate . ';';
        $schema .= MyStyle_Session::get_schema() .  $collate . ';';
        
        return $schema;
    }
    
    /**
     * Create cron jobs (clear them first).
     */
    public static function create_cron_jobs() {
        wp_clear_scheduled_hook( 'mystyle_session_garbage_collection' );

        wp_schedule_event( time(), 'twicedaily', 'mystyle_session_garbage_collection' );
    }
    
    /**
     * Clear cron jobs.
     */
    public static function clear_cron_jobs() {
        wp_clear_scheduled_hook( 'mystyle_session_garbage_collection' );
    }
    
    /**
     * Called when the plugin is activated.
     */
    static function activate() {
        if( ! MyStyle_Customize_Page::exists() ) {
            MyStyle_Customize_Page::create();
        }
        if( ! MyStyle_Design_Profile_Page::exists() ) {
            MyStyle_Design_Profile_Page::create();
        }
        self::create_tables();
        self::create_cron_jobs();
    }

    /**
     * Called when the plugin is deactivated.
     */
    static function deactivate() {
        //
    }
    
    /**
     * Function called when MyStyle is uninstalled
     */
    static function uninstall() {
        delete_option( MYSTYLE_NOTICES_NAME );
        self::clear_cron_jobs();
    }

}
