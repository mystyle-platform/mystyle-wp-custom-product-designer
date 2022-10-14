<?php
/**
 * The template for displaying MyStyle design collections and the MyStyle design
 * collection index.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 3.18.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div id="mystyle-design-collection-index-wrapper" class="mystyle-design-collection-index">

	<?php // Output the collections menu (left nav). ?>
	<div class="collections-menu">
		<ul>
			<?php foreach ( $all_terms as $term ) : ?>
			<li>
				<a
					href="<?php echo esc_url( get_term_link( $term ) ); ?>"
					title="<?php echo esc_attr( $term->name ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php // Output the collections content. ?>
	<div class="collections-content">
		<?php foreach ( $terms as $term ) : ?>

		<div class="collection-row">
			<h3>
				<a
					href="<?php echo esc_url( get_term_link( $term ) ); ?>"
					title="<?php echo esc_attr( $term->name ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			</h3>
			<?php $count = count( $term->designs ); ?>
			<?php foreach ( $term->designs as $design ) : ?>
				<?php
				$design_url = MyStyle_Design_Profile_page::get_design_url( $design );
				$user       = get_user_by( 'id', $design->get_user_id() );
				?>
			<div class="design-tile">

				<div class="design-img">

					<a href="<?php echo esc_url( $design_url ); ?>" title="<?php echo esc_attr( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>">
						<img alt="<?php echo esc_html( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?> Image" src="<?php echo esc_url( $design->get_thumb_url() ); ?>" />
						<?php echo esc_html( ( ! empty( $design->get_title() ) ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>
					</a>
					<div>Designed by: <?php echo esc_html( $user->user_nicename ); ?></div>

				</div>

			</div>
			<?php endforeach; ?>
			<?php // Conditionally show a view more link to view additional designs. ?>
			<?php if ( ( 1 < count( $terms ) ) && ( $design_limit < $term->total_design_count ) ) : ?>
			<div class="design-tile view-more">
				<a href="<?php echo esc_url( get_term_link( $term->slug, MYSTYLE_COLLECTION_NAME ) ); ?>" title="<?php echo esc_attr( $term->name ); ?>">View More</a>
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
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


</div>
