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
	<?php foreach ( $terms as $term ) : ?>
	<h3 class="mystyle-design-id"><a href="<?php echo esc_url( get_term_link( $term ) ); ?>" title="<?php echo esc_attr( $term->name ); ?> Gallery"><?php echo esc_html( $term->name ); ?></a></h3>
	<ul>
		<?php foreach ( $term->designs as $design ) : ?>
			<?php
			$design_url = MyStyle_Design_Profile_page::get_design_url( $design );
			$user       = get_user_by( 'id', $design->get_user_id() );
			?>
		<li>
			<a href="<?php echo esc_url( $design_url ); ?>" title="<?php echo esc_attr( ( null !== $design->get_title() ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>">
				<img alt="<?php echo esc_html( ( null !== $design->get_title() ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?> Image" src="<?php echo esc_url( $design->get_thumb_url() ); ?>" />
				<?php echo esc_html( ( null !== $design->get_title() ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>
			</a>
			<div>Designed by: <?php echo esc_html( $user->user_nicename ); ?></div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endforeach; ?>
	<div class="pager">
		<?php if ( ! is_null( $prev ) ) : ?>
		<a href="<?php echo esc_url( '?pager=' . $prev ); ?>" title="Previous page">Previous</a>
		<?php endif; ?>
		<?php if ( ! is_null( $next ) ) : ?>
		<a href="<?php echo esc_url( '?pager=' . $next ); ?>" title="Next page">Next</a>
		<?php endif; ?>
	</div>
</div>
