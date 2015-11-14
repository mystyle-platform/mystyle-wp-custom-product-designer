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
     * Sets the user_id on any designs created by the session (only if
     * the design isn't already assigned to a user).
     * @global wpdb $wpdb
     * @param MyStyle_Session $session
     * @param WP_User $user
     * @return integer Returns the number or designs that were updated or false
     * if no rows were updated.
     */
    public static function set_wp_user_id_by_mystyle_session( $session, $user ) {
        global $wpdb;
        
        $result = $wpdb->update( 
                MyStyle_Design::get_table_name(), //table 
                array( 'user_id' => $user->ID ), //data
                array( 'session_id' => $session->get_session_id() ), //where
                array( '%s' ), //format
                array( '%s' ) //where format
        );
        
        return $result;
    }

}


