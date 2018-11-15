<?php
/**
 * MyStyle_Entity class.
 *
 * The MyStyle_Entity class is an interface that can be implemented to allow the
 * MyStyle_DBManager to perisit objects to the database.
 *
 * @package MyStyle
 * @since 0.5
 */

/**
 * MyStyle_Entity interface.
 */
interface MyStyle_Entity {

	/**
	 * Gets the name of the table for the entity.
	 *
	 * @return string Returns the name of the table for the entity.
	 */
	public static function get_table_name();

	/**
	 * Gets the name of the primary key column.
	 *
	 * @return string Returns the name of the primary key column for the table.
	 */
	public static function get_primary_key();

	/**
	 * Gets the schema for the entities table.
	 *
	 * @return string Returns the schema for the entities table.
	 */
	public static function get_schema();

	/**
	 * Gets the entity data to insert into the table.
	 *
	 * @return array Data to insert (in column => value pairs)
	 */
	public function get_data_array();

	/**
	 * Gets the insert format for the entity.
	 *
	 * See https://codex.wordpress.org/Class_Reference/wpdb#INSERT_rows
	 *
	 * @return (array|string)
	 */
	public function get_insert_format();
}
