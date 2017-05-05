<?php

/**
 * MyStyle_SessionManager class. 
 * 
 * The MyStyle_SessionManager class has functions for managing MyStyle_Session
 * entities.
 *
 * @package MyStyle
 * @since 1.3.0
 */
abstract class MyStyle_SessionManager extends \MyStyle_EntityManager {
    
    /**
     * Get the session from the database.
     * @global wpdb $wpdb
     * @param string $session_id The session id.
     * @return \MyStyle_Session Returns the MyStyle_Session entity.
     */
    public static function get( $session_id ) {
        global $wpdb;
        
        $session = null;
        
        $query = 'SELECT * FROM ' . MyStyle_Session::get_table_name() . ' ' . 
                 'WHERE ' . MyStyle_Session::get_primary_key() . ' = "' . $session_id . '"';
        
        $result_object = $wpdb->get_row($query);

        if( $result_object != null ) {
            $session = MyStyle_Session::create_from_result_object( $result_object );
        }
        
        
        return $session;
    }
    
    /**
     * Updates the session in the database changing its modified date/time to
     * the current date/time. Use this function for both create and update
     * operations.
     * @global wpdb $wpdb
     * @param MyStyle_Session $session The MyStyle_Session that you want to
     * update.
     * @return \MyStyle_Session Returns the MyStyle_Session entity.
     */
    public static function update( MyStyle_Session $session ) {
        global $wpdb;
        
        $session->set_modified( date( MyStyle::$STANDARD_DATE_FORMAT ) );
        $session->set_modified_gmt( gmdate( MyStyle::$STANDARD_DATE_FORMAT ) );
        
        $wpdb->replace(
                $session->get_table_name(),
                $session->get_data_array(),
                $session->get_insert_format()
            );
        
        return $session;
    }

}


