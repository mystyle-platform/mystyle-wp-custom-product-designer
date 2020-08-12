<?php
/**
 * The template for displaying the MyStyle Design Profile index page.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 1.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="mystyle-design-profile-index-wrapper" class="woocommerce">
	<?php
	if ( ( isset( $pager ) ) && ( null !== $pager->get_items() ) ) {
		?>
		<ul class="mystyle-designs">
			<?php
			/* @var $design \MyStyle_Design The current MyStyleDesign. */
			foreach ( $pager->get_items() as $design ) {
				$design_url    = MyStyle_Design_Profile_page::get_design_url( $design );
				$product_id    = $design->get_product_id();
				$product_title = $design->get_product()->get_title();
				?>
				<li>
					<a href="<?php echo esc_attr( $design_url ); ?>">
						<img src="<?php echo esc_attr( $design->get_thumb_url() ); ?>" />
						<h3 class="mystyle-design-id">
							<?php
                            $title = 'Custom ' . $product_title . ' <span>' . $design->get_design_id() . '</span>' ;
                            if("" !== $design->get_title() ) {
                                $title = $design->get_title() ;
                            }
							echo $title ;
							?>
						</h3>
					</a>
				</li>
				<?php
			} //end foreach
			?>
		</ul>

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
		<?php
	} // End if designs.
	?>
</div>
