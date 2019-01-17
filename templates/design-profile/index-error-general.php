<?php
/**
 * The template for displaying the MyStyle Design Profile index page when a
 * general error occurs.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<h1>Error</h1>
<p><?php echo esc_html( $ex->getMessage() ); ?></p>
<p>
	<?php
	// Output a link to the main design profile page index.
	$post = get_post( MyStyle_Design_Profile_Page::get_id() );
	echo '<a href="' . esc_attr( get_permalink( $post->ID ) ) . '" >' . esc_html( $post->post_title ) . '</a>';
	?>
</p>
