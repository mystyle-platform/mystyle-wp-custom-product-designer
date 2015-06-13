<?php
/**
 * Shared functions for testing the plugin.
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Given a base registry array entry, this private function will return all
 * function names within that array
 * @param type $regArray1 The base registry array that you want to search
 * through
 * @return array An array of the function names that were found.
 */
function get_function_names( $regArray1 ) {
   $function_names = array();
   foreach( $regArray1 as $regArray2 ) {
       foreach( $regArray2 as $regArray3 ) {
           $function_names[] = $regArray3['function'][1];
       }
   }
   return $function_names;
}
