<?php
/**
 * The template for displaying the MyStyle Design Profile page when the user is
 * trying to access a private design but isn't logged in.
 * @package MyStyle
 * @since 1.4.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<h2>Sorry, this design is private.</h2>
<h3>If this is your design, log in to view it.</h3>

<p><?php wp_loginout(); ?></p>


