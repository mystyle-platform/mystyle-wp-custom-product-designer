<?php

/**
 * MyStyle_SessionManager class. 
 * 
 * The MyStyle_SessionManager class has functions for managing MyStyle_Session
 * entities.
 *
 * @package MyStyle
 * @since 1.2.0
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
        
        $query = 'SELECT * FROM ' . MyStyle_Session::get_table_name() . ' ' . 
                 'WHERE ' . MyStyle_Session::get_primary_key() . ' = "' . $session_id . '"';
        
        $result_object = $wpdb->get_row($query);

        $session = MyStyle_Session::create_from_result_object( $result_object );
        
        return $session;
    }

}


