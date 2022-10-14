<?php
/**
 * The template for displaying the MyStyle design tag index.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 3.17.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div id="mystyle-design-tag-index-wrapper" class="woocommerce design-tags">
	<div class="mystyle-design-tag-index">
		<?php foreach ( $terms as $term ) : ?>
			<a
				href="<?php echo esc_url( get_term_link( $term ) ); ?>"
				title="<?php echo esc_attr( $term->name ); ?> Gallery"
			><?php echo esc_html( $term->name ); ?></a>&nbsp;
		<?php endforeach; ?>
	</div>
</div>
