<?php

/**
 * MyStyle_DesignManager class. 
 * 
 * The MyStyle_DesignManager class has functions for managing MyStyle_Design
 * entities.
 *
 * @package MyStyle
 * @since 0.5
 */
abstract class MyStyle_DesignManager extends \MyStyle_EntityManager {
    
    /**
     * Get the design from the database.
     * @global wpdb $wpdb
     * @param integer $design_id The design id.
     * @return \MyStyle_Design Returns the MyStyle_Design entity.
     */
    public static function get( $design_id ) {
        global $wpdb;
        
        $query = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ' . 
                 'WHERE ' . MyStyle_Design::get_primary_key() . ' = ' . $design_id;
        
        $result_object = $wpdb->get_row($query);

        $design = MyStyle_Design::create_from_result_object( $result_object );
        
        return $design;
    }
    
    /**
     * Sets the user_id on designs where it is null (or 0) using the email and
     * the session.
     * @global wpdb $wpdb
     * @param WP_User $user
     * @param MyStyle_Session (optional) $session
     * @return integer Returns the number or designs that were updated or false
     * if no rows were updated.
     */
    public static function set_user_id( $user, $session = null) {
        global $wpdb;

        $query = 'UPDATE ' . MyStyle_Design::get_table_name() . ' ' . 
                 'SET user_id = "' . $user->ID . '" ' .
                 'WHERE ( ( user_id IS NULL ) OR ( user_id = 0 ) ) ';
        $query .='AND ( ';
        
        if( ! empty( $user->user_email ) ) {
            // Where email matches and the session is empty or matches the passed session id.
            $query .=
                 ' ( ms_email = "' . $user->user_email . '" )';
            $query .= 
                 'AND ( ';
            if( $session != null ) {
                $query .=
                       ' ( session_id = "' . $session->get_session_id() . '" ) OR ';
            }
            $query .= 
                       ' ( session_id IS NULL ) OR ( session_id = "" ) ';
            $query .= 
                    ' ) ';
        } else {
            //If the user doesn't have an email address, try to match based on the session id
            if( $session != null ) {
                $query .=
                      ' ( session_id = "' . $session->get_session_id() . '" ) ';
            }
        }
        
        //if the design doesn't have an email set, try to macth just based on the session id.
        $query .= ') OR (ms_email IS NULL AND session_id = "' . $session->get_session_id() . '" ) ';
        
        //echo $query;
        
        $result = $wpdb->query($query);
        
        return $result;
    }

}


