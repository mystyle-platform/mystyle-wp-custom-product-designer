<?php
/**
 * The template for displaying the MyStyle design tag.
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

<div id="mystyle-design-tag-wrapper" class="woocommerce design-tags show-designs">
	<div class="mystyle-sort">
		<form name="mystyle-sort-form" method="get" class="mystyle-sort-form" action="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>">
			<label for="mystyle-sort-select">Sort tags by:</label>
			<select name="sort_by" class="mystyle-sort-select">
				<option value="qty"<?php echo ( 'qty' === $sort_by['name'] ) ? ' selected' : ''; ?>>Quantity</option>
				<option value="alpha"<?php echo ( 'alpha' === $sort_by['name'] ) ? ' selected' : ''; ?>>Alphabetical</option>
			</select>
		</form>
	</div>
	<div class="mystyle-design-tag show-designs">
		<?php foreach ( $term_designs as $term_design ) : ?>
			<?php
				$term    = $term_design['term'];
				$designs = $term_design['designs'];
			?>
			<?php $term_name = preg_replace( '/\-/', ' ', $term->name ); ?>
			<h3 class="mystyle-tag-id"><a href="<?php echo esc_url( get_term_link( $term ) ); ?>" title="<?php echo esc_attr( $term_name ); ?> Gallery"><?php echo esc_html( $term_name ); ?></a></h3>
			<ul>
				<?php foreach ( $designs as $design ) : ?>
						<?php
						$design_url = MyStyle_Design_Profile_page::get_design_url( $design );
						$user       = get_user_by( 'id', $design->get_user_id() );
						?>
					<li>
						<a href="<?php echo esc_url( $design_url ); ?>" title="<?php echo esc_attr( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>">
							<img alt="<?php echo esc_html( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?> Image" src="<?php echo esc_url( $design->get_thumb_url() ); ?>" />
							<?php echo esc_html( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>
						</a>
						<div class="mystyle-design-author">
							Designed by: <?php echo esc_html( $user->user_nicename ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
	<div class="pager">
		<?php $sort_by_query = ( isset( $sort_by ) ? '&sort_by=' . $sort_by['name'] : '' ); ?>
		<?php if ( ! is_null( $prev ) ) : ?>
			<a href="<?php echo esc_url( '?pager=' . $prev . $sort_by_query ); ?>" title="Previous page">Previous</a>
		<?php endif; ?>
		<?php if ( ! is_null( $next ) ) : ?>
			<a href="<?php echo esc_url( '?pager=' . $next . $sort_by_query ); ?>" title="Next page">Next</a>
		<?php endif; ?>
	</div>
</div>
