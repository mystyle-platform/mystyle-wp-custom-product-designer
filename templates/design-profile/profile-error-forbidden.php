<?php
/**
 * The template for displaying the MyStyle Design Profile page when the user is
 * forbidden to view a design (user is trying to access a private design
 * that isn't theirs and they aren't an admin).
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 1.4.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<h2>Sorry, this design is private.</h2>
<ul class="mystyle-button-group">
	<?php if ( ! empty( $previous_design_url ) ) { ?>
		<li><a href="<?php echo esc_attr( $previous_design_url ); ?>">Previous</a></li>
	<?php } ?>
	<?php if ( ! empty( $next_design_url ) ) { ?>
		<li><a href="<?php echo esc_attr( $next_design_url ); ?>">Next</a></li>
	<?php } ?>
</ul>

