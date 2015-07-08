<?php
/**
 * Functions for rendering the MyStyle contextual help within the WordPress
 * Administrator.
 * @package MyStyle
 * @since 0.1.0
 */

require_once(MYSTYLE_INCLUDES . 'admin/help/options-page-help.php');
    
/**
 * Add help for the MyStyle plugin to the WordPress admin help system.
 * @global type $mystyle_hook
 * @param string $contextual_help The default contextual help that our 
 * function is going to replace.
 * @param string $screen_id Used to identify the page that we are on.
 * @param string $screen Used to access the elements of the current page.
 * @return string The new contextual help.
 */
function mystyle_help_dispatch( $contextual_help, $screen_id, $screen ) {
    global $mystyle_hook;

    switch( $screen_id ) {
        case 'toplevel_page_' . $mystyle_hook:
            $contextual_help = mystyle_options_page_help( $contextual_help, $screen_id, $screen );
            break;
        //add additional hooks here as required
    }
    
    return $contextual_help;
}
