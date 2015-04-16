<?php
/**
 * Functions for adding the API to the WordPress front end.
 * @package MyStyle
 * @since 1.0
 */

/**
 * Function that adds the MyStyle api to the DOM.
 */
function mystyle_add_api() {
    $api_key = mystyle_get_active_api_key();
    
    //Load the api (if there is an api_key)
    if(!empty($api_key)) {
 ?>
<!-- MyStyle Start -->
<!-- MyStyle End -->
<?php
        //Optionally load the QUnit tests.
        if( (defined('MYSTYLE_LOAD_QUNIT')) && (MYSTYLE_LOAD_QUNIT == true) ) {
            require_once(MYSTYLE_PATH . 'tests/qunit.php');
            mystyle_load_qunit('frontend-api');
        }
        
    } //end if api_key
    
} //end mystyle_add_api
