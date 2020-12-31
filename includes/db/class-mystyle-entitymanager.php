<?php
/**
 * The MyStyle_EntityManager class has functions to work with the MyStyle and
 * WordPress database tables.
 *
 * @package MyStyle
 * @since 0.5
 */

/**
 * MyStyle_EntityManager abstract class.
 */
abstract class MyStyle_EntityManager {

	/**
	 * Persists the passed entity to the database.
	 *
	 * @global \wpdb $wpdb
	 * @param MyStyle_Entity $entity The MyStyle_Entity that you want to
	 * persist.
	 * @return \MyStyle_Entity Returns the persisted entity.
	 * @throws MyStyle_Exception Throws a MyStyleException if the entity
	 * couldn't be persisted to the database.
	 */
	public static function persist( MyStyle_Entity $entity ) {
		global $wpdb;

		$ret = $wpdb->replace(
			$entity->get_table_name(),
			$entity->get_data_array(),
			$entity->get_insert_format()
		);

		if ( false === $ret ) {
			$msg = "Could not persist data to database.\n" .
					$wpdb->last_error . "\n" .
					$wpdb->last_query . "\n" .
					$entity->get_table_name() . "\n\n" .
					var_export( $entity->get_data_array() ) . "\n\n"; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			throw new MyStyle_Exception( $msg, 500 );
		}

		return $entity;
	}

}
