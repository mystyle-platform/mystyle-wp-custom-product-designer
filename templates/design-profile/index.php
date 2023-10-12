<?php

/**
 * The template for displaying the MyStyle Design Profile index page.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 1.4.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
?>
<div id="mystyle-design-profile-index-wrapper" class="woocommerce">
	<?php
	if ((isset($pager)) && (null !== $pager->get_items())) {
	?>
		<ul class="mystyle-designs">
			<?php
			/* @var $design \MyStyle_Design The current MyStyle_Design. */
			foreach ($pager->get_items() as $design) {
				$design_url = MyStyle_Design_Profile_page::get_design_url($design);
				$product    = $design->get_product();
				if ($product) {
					$product_title = $product->get_title();
				} else {
					$product       = '';
					$product_title = '';
				}
			?>
				<li>
					<?php
					$design_url = MyStyle_Design_Profile_page::get_design_url($design);
					$user       = get_user_by('id', $design->get_user_id());
					$options = get_option(MYSTYLE_OPTIONS_NAME, array()); // Get WP Options table Key of this option.
					$product_phrase = (array_key_exists('alternate_design_tag_collection_title', $options)) ? $options['alternate_design_tag_collection_title'] : '';

					if (empty($design->get_title())) {
						$title = 'Design' . ' ' . $design->get_design_id() . ' ' . $product_phrase;
					} else {
						$title = $design->get_title() . ' ' . $product_phrase;
					}
					?>
					<a href="<?php echo esc_url($design_url); ?>" title="<?php echo $title; ?>">
						<img alt="<?php echo $title; ?> Image" src="<?php echo esc_url($design->mystyle_design_Url()); ?>" />
						<h3 class="mystyle-design-id">
							<?php
							if (!empty($design->get_title())) {
								echo esc_html($design->get_title());
							} elseif (preg_match('/custom/i', $product_title)) {
								echo esc_html($product_title) . ' <span>' . esc_html($design->get_design_id()) . '</span>';
							} else {
								echo 'Custom ' . esc_html($product_title) . ' <span>' . esc_html($design->get_design_id()) . '</span>';
							}
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
					'base'      => esc_url_raw(str_replace(999999999, '%#%', get_pagenum_link(999999999, false))),
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