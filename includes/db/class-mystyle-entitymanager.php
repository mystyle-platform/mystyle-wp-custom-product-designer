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
     * @global \wpdb $wpdb
     * @param MyStyle_Entity $entity
     * @return \MyStyle_Entity Returns the persisted entity.
     */
    public static function persist( MyStyle_Entity $entity ) {
        global $wpdb;
        
        $ret = $wpdb->replace( 
                    $entity->get_table_name(),
                    $entity->get_data_array(),
                    $entity->get_insert_format() 
                );
        
        if($ret == false) {
            $msg = "Could not persist data to database.\n" .
                    $wpdb->last_error . "\n" .
                    $wpdb->last_query . "\n" .
                    $entity->get_table_name() . "\n\n" .
                    var_export( $entity->get_data_array() ) . "\n\n";
            throw new MyStyle_Exception( $msg , 500 );
        }
        
        return $entity;
    }

}


