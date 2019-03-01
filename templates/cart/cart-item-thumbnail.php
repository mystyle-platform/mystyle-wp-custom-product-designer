<?php
/**
 * The template for displaying the thumbnail cell for the cart item rows for
 * customized products.
 *
 * This template can be overridden by copying it to
 * yourtheme/mystyle/cart/cart-item-thumbnail.php.
 *
 * @package MyStyle
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<figure>
	<a href="<?php echo esc_attr( $design_profile_url ); ?>">
		<?php echo $product_img_tag; // phpcs:ignore WordPress.XSS.EscapeOutput ?>
	</a>

	<figcaption style="font-size: 0.5em">
		Design Id: <a href="<?php echo esc_attr( $design_profile_url ); ?>"><?php echo esc_attr( $design->get_design_id() ); ?></a><br/>
		<a href="<?php echo esc_attr( $customizer_url ); ?>">Edit</a>
	</figcaption>
</figure>
