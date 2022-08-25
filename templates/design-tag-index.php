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

<div id="mystyle-design-tag-index-wrapper" class="mystyle-design-tag-index woocommerce design-tags">
	<div class="mystyle-sort">
		<form name="mystyle-sort-form" method="get" class="mystyle-sort-form" action="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>">
			<label for="mystyle-sort-select">Sort tags by:</label>
			<select name="sort_by" class="mystyle-sort-select">
				<option value="qty"<?php echo ( 'qty' === $sort_by['name'] ) ? ' selected' : ''; ?>>Quantity</option>
				<option value="alpha"<?php echo ( 'alpha' === $sort_by['name'] ) ? ' selected' : ''; ?>>Alphabetical</option>
			</select>
		</form>
	</div>
	<div class="mystyle-design-tag-index">
		<?php foreach ( $terms as $term ) : ?>
			<a
				href="<?php echo esc_url( get_term_link( $term ) ); ?>"
				title="<?php echo esc_attr( $term->name ); ?> Gallery"
			><?php echo esc_html( $term->name ); ?></a>&nbsp;
		<?php endforeach; ?>
	</div>
</div>
