<?php
/**
 * The template for displaying the MyStyle Design Profile page when a general
 * error occurs.
 * @package MyStyle
 * @since 1.4.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<h1>Error</h1>
<p><?php echo $ex->getMessage() ?></p>

