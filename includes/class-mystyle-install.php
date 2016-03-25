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

        return MyStyle_Design::get_schema() .  $collate . ';';
            
    }
    
    /**
     * Called when the plugin is activated.
     */
    static function activate() {
        if( ! MyStyle_Customize_Page::exists() ) {
            MyStyle_Customize_Page::create();
        }
        MyStyle_Install::create_tables();
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
    }

}