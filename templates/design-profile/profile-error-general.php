<?php
/**
 * The template for displaying the MyStyle Design Profile page when a general
 * error occurs.
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
<h1>Error</h1>
<p><?php echo esc_html( $ex->getMessage() ); ?></p>
<ul class="mystyle-button-group">
	<?php if ( ! empty( $previous_design_url ) ) { ?>
		<li><a href="<?php echo esc_attr( $previous_design_url ); ?>">Previous</a></li>
	<?php } ?>
	<?php if ( ! empty( $next_design_url ) ) { ?>
		<li><a href="<?php echo esc_attr( $next_design_url ); ?>">Next</a></li>
	<?php } ?>
</ul>
