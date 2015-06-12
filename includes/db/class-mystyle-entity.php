<?php

/**
 * MyStyle_Entity class. 
 * 
 * The MyStyle_Entity class is an abstract class that can be used by the
 * MyStyle_DBManager to perisit objects to the database.
 *
 * @package MyStyle
 * @since 0.5
 * @todo Add unit testing
 */
interface MyStyle_Entity {
    
    /**
     * Gets the name of the table for the entity.
     * @return string Returns the name of the table for the entity.
     */
    public function getTableName();
    
    /**
     * Gets the schema for the entities table.
     * @return string Returns the schema for the entities table.
     */
    public static function getSchema();
    
    /**
     * Gets the entity data to insert into the table.
     * @return array Data to insert (in column => value pairs)
     */
    public function getDataArray();
    
    /**
     * Gets the insert format for the entity.
     * See https://codex.wordpress.org/Class_Reference/wpdb#INSERT_rows
     * @return (array|string)
     */
    public function getInsertFormat();

}


