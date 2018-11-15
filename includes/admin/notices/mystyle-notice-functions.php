<?php
/**
 * Functions for working with notices.
 *
 * @package MyStyle
 * @since 1.2
 */

/**
 * Adds a notice to the database for display on the next refresh.
 *
 * @param MyStyle_Notice $notice The notice that you want to queue.
 */
function mystyle_notice_add_to_queue( $notice ) {
	$notices                              = get_option( MYSTYLE_NOTICES_NAME );
	$notices[ $notice->get_notice_key() ] = $notice->to_array();
	update_option( MYSTYLE_NOTICES_NAME, $notices );
}

/**
 * Returns an array containing any queued notices. If there are no queued notices
 * the function returns an empty array. After pulling the queued notices, they
 * are deleted.
 *
 * @return array An array of queued MyStyle_Notices or else an empty array.
 */
function mystyle_notice_pull_all_queued() {
	$notices        = array();
	$queued_notices = get_option( MYSTYLE_NOTICES_NAME );

	if ( $queued_notices ) {
		foreach ( $queued_notices as $notice ) {
			$notices[] = MyStyle_Notice::recreate( $notice );
		}
		delete_option( MYSTYLE_NOTICES_NAME );
	}

	return $notices;
}
