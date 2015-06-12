<?php

/**
 * MyStyle_DBManager class. 
 * 
 * The MyStyle_DBManager class has functions to work with the mystyle and
 * wordpress database tables.
 *
 * @package MyStyle
 * @since 0.5
 * @todo Add unit testing
 */
abstract class MyStyle_DBManager {
    
    /**
     * Persists the passed entity to the database.
     * @global type $wpdb
     * @param MyStyle_Entity $entity
     * @return \MyStyle_Entity Returns the persisted entity.
     */
    public static function persist( MyStyle_Entity $entity ) {
        global $wpdb;
        
        $wpdb->insert( 
                $entity->getTableName(),
                $entity->getDataArray(),
                $entity->getInsertFormat() 
            );
        
        return $entity;
    }

}


