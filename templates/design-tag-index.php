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

<div id="mystyle-design-tag-index-wrapper" class="woocommerce design-tags<?php print ( $show_designs ? ' show-designs' : '' ); ?>">
	<div class="mystyle-sort">
		<form name="mystyle-sort-form" method="get" class="mystyle-sort-form" action="<?php print get_permalink( get_the_ID() ); ?>">
			<label for="mystyle-sort-select">Sort tags by:</label>
			<select name="sort_by" class="mystyle-sort-select">
				<option value="alpha"<?php print ( $sort_by == 'alpha' ? ' selected' : '' ); ?>>Alphabetical</option>
				<option value="qty"<?php print ( $sort_by == 'qty' ? ' selected' : '' ); ?>>Quantity</option>
			</select>
		</form>
	</div>
	<?php foreach ( $terms as $term ) : ?>
		<?php if ( $show_designs ) : ?>
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
	<?php else : ?>
	<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" title="<?php echo esc_attr( $term->name ); ?> Gallery"><?php echo esc_html( $term->name ); ?></a>
	<?php endif; ?>&nbsp;
	<?php endforeach; ?>
	<div class="pager">
		<?php $sort_by_query = ( isset( $sort_by ) ? '&sort_by=' . $sort_by : '' ); ?>
		<?php if ( ! is_null( $prev ) ) : ?> 
		<a href="<?php echo esc_url( '?pager=' . $prev . $sort_by_query ); ?>" title="Previous page">Previous</a>
		<?php endif; ?>
		<?php if ( ! is_null( $next ) ) : ?>
		<a href="<?php echo esc_url( '?pager=' . $next . $sort_by_query ); ?>" title="Next page">Next</a>
		<?php endif; ?>
	</div>
</div>
