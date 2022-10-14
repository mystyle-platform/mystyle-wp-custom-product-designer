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

	<?php if ( null === $term ) : ?>
	<div class="mystyle-sort">
		<form name="mystyle-sort-form" method="get" class="mystyle-sort-form" action="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>">
			<label for="mystyle-sort-select">Sort tags by:</label>
			<select name="sort_by" class="mystyle-sort-select">
				<option value="qty"<?php echo ( 'qty' === $sort_by['name'] ) ? ' selected' : ''; ?>>Quantity</option>
				<option value="alpha"<?php echo ( 'alpha' === $sort_by['name'] ) ? ' selected' : ''; ?>>Alphabetical</option>
			</select>
		</form>
	</div>
	<?php endif ?>

	<?php // Output the tags content. ?>
	<div>
		<?php foreach ( $terms as $term ) : ?>

		<div class="mystyle-design-tag show-designs">
			<h3 class="mystyle-tag-id">
				<a
					href="<?php echo esc_url( get_term_link( $term ) ); ?>"
					title="<?php echo esc_attr( $term->name ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			</h3>
			<?php $count = count( $term->designs ); ?>
			<ul>
				<?php foreach ( $term->designs as $design ) : ?>
					<?php
					$design_url = MyStyle_Design_Profile_page::get_design_url( $design );
					$user       = get_user_by( 'id', $design->get_user_id() );
					?>
				<li>
					<div class="design-img">
						<a href="<?php echo esc_url( $design_url ); ?>" title="<?php echo esc_attr( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>">
							<img alt="<?php echo esc_html( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?> Image" src="<?php echo esc_url( $design->get_thumb_url() ); ?>" />
							<?php echo esc_html( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>
						</a>
						<div>Designed by: <?php echo esc_html( $user->user_nicename ); ?></div>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php // Conditionally show a view more link to view additional designs. ?>
			<?php if ( ( 1 < count( $terms ) ) && ( $design_limit < $term->total_design_count ) ) : ?>
			<div class="design-tile view-more">
				<a href="<?php echo esc_url( get_term_link( $term->slug, MYSTYLE_TAXONOMY_NAME ) ); ?>" title="<?php echo esc_attr( $term->name ); ?>">View More</a>
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<nav class="woocommerce-pagination">
		<?php
		echo paginate_links( // WPCS: XSS ok.
			array(
				'base'      => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
				'format'    => '',
				'add_args'  => false,
				'current'   => $pager->get_current_page_number(),
				'total'     => $pager->get_page_count(),
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type'      => 'list',
				'end_size'  => 3,
				'mid_size'  => 3,
			)
		);
		?>
	</nav>

</div>
