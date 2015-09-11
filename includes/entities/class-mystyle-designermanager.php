<?php

/**
 * MyStyle_DesignerManager class. 
 * 
 * The MyStyle_DesignerManager class has functions for managing MyStyle_Designer
 * entities.
 *
 * @package MyStyle
 * @since 1.2.0
 */
abstract class MyStyle_DesignerManager extends \MyStyle_EntityManager {
    
    /**
     * Get the designer from the database.
     * @global wpdb $wpdb
     * @param integer $designer_id The designer id.
     * @return \MyStyle_Designer Returns the MyStyle_Designer entity.
     */
    public static function get( $designer_id ) {
        global $wpdb;
        
        $query = 'SELECT * ' . 
                 'FROM ' . MyStyle_Designer::get_table_name() . ' ' . 
                 'WHERE ' . MyStyle_Designer::get_primary_key() . ' = ' . $designer_id;
        
        $result_object = $wpdb->get_row($query);

        $designer = MyStyle_Designer::create_from_result_object( $result_object );
        
        return $designer;
    }

}


