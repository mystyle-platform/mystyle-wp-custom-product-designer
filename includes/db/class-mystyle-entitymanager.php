<?php

/**
 * MyStyle_EntityManager class. 
 * 
 * The MyStyle_EntityManager class has functions to work with the mystyle and
 * wordpress database tables.
 *
 * @package MyStyle
 * @since 0.5
 */
abstract class MyStyle_EntityManager {
    
    /**
     * Persists the passed entity to the database.
     * @global type $wpdb
     * @param MyStyle_Entity $entity
     * @return \MyStyle_Entity Returns the persisted entity.
     */
    public static function persist( MyStyle_Entity $entity ) {
        global $wpdb;
        
        $wpdb->insert( 
                $entity->get_table_name(),
                $entity->get_data_array(),
                $entity->get_insert_format() 
            );
        
        return $entity;
    }

}


