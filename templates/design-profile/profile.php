<?php
/**
 * The template for displaying the MyStyle Design Profile page content.
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
<div id="mystyle-design-profile-wrapper" class="woocommerce">
	<?php if ( ( ( 0 !== $design->get_user_id() ) && ( get_current_user_id() === $design->get_user_id() ) ) || ( current_user_can( 'administrator' ) ) ) : ?>
	<a id="ms-edit-title-form-show" href="#">Edit Title</a>
	<div id="ms-edit-title-form">
		<form method="post" id="ms-edit-title-form">
			<input type="text" name="ms-title" value="<?php echo ( ( ! empty( $design->get_title() ) ) ? esc_attr( $design->get_title() ) : ( 'Design ' . esc_attr( $design->get_design_id() ) ) ); ?>" />
			<input type="submit" class="button" value="Save Title" />
		</form>
	</div>
	<?php endif; ?>
	<ul class="mystyle-button-group mystyle-design-nav">
		<?php if ( ! empty( $previous_design_url ) ) { ?>
			<li><a href="<?php echo esc_attr( $previous_design_url ); ?>">&larr;</a></li>
		<?php } else { ?>
			<li>&nbsp;</li>
		<?php } ?>
		<li><a href="<?php echo esc_attr( MyStyle_Design_Profile_Page::get_index_url() ); ?>">&uarr;</a></li>
		<?php if ( ! empty( $next_design_url ) ) { ?>
			<li><a href="<?php echo esc_attr( $next_design_url ); ?>">&rarr;</a></li>
		<?php } else { ?>
			<li>&nbsp;</li>
		<?php } ?>
	</ul>
	<img id="mystyle-design-profile-img" src="<?php echo esc_attr( $design->get_web_url() ); ?>"/>
	<ul class="mystyle-button-group">
		<li>
			<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr( get_permalink( $design->get_product_id() ) ); ?>">
				<?php
				// If we have the cart_data (older versions of the plugin don't)
				// throw it all into hidden fields.
				if ( null !== $design->get_cart_data_array() ) {
					foreach ( $design->get_cart_data_array() as $key => $value ) {
						echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
					}
				} else {
					// If we don't have the cart data just use the product_id.
					echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $design->get_product_id() ) . '" />';
				}
				?>
				<input type="hidden" name="design_id" value="<?php echo esc_attr( $design->get_design_id() ); ?>" />
				<?php if ( $show_add_to_cart_button ) { ?>
					<button type="submit" class="button">Add to Cart</a>
				<?php } ?>
			</form>
		</li>
		<li><a href="<?php echo esc_attr( $design->get_reload_url() ); ?>" class="button">Customize</a></li>
		<li><a href="<?php echo esc_attr( $design->get_scratch_url() ); ?>" class="button">Design from scratch</a></li>

	</ul>

	<div class="product_description">
		<h2 class='linked_title'>
			<a href="<?php echo esc_attr( $product->get_permalink() ); ?>">
				<?php echo 'Custom ' . esc_html( $product->get_title() ); ?>
			</a>
		</h2>
		<div class='linked_desc'>
			<?php echo ( $product->get_description() ) ?: 'No description.'; ?>
		</div>
		<?php if ( $author ) : ?>
		<div class="linked_user">
			Designer: <a
				href="<?php echo esc_url( MyStyle_Author_Designs_Page::get_author_url( $author ) ); ?>"
				title="<?php echo ( ( is_string( $author ) ) ? 'Anonymous User' : esc_attr( $author->user_nicename ) ); ?> Designs"
				><?php echo ( ( is_string( $author ) ) ? 'Anonymous User' : esc_html( $author->user_nicename ) ); ?></a>
		</div>
		<?php endif; ?>
		<?php if ( ( ( 0 !== $design->get_user_id() ) && ( get_current_user_id() === $design->get_user_id() ) ) || ( current_user_can( 'administrator' ) ) ) : ?>
		<div class="edit-design-tags">
			<br />
			<b>Add or Edit Design Tags</b>
			<form method="post">
				<input type="text" class="edit-design-tag-input" name="edit-design-tag" />
				<input type="submit" class="button" value="SAVE TAGS" />
			</form>
			<div class="design-tag-status"></div>
		</div>
		<?php else : ?>
		<div class="design-tags">
			Design Tags:
			<?php foreach ( $design_tags as $i => $tag ) : ?>
				<?php
				if ( $i > 0 ) {
					echo ', ';}
				?>
				<a href="<?php echo esc_url( MyStyle_Design_Tag_Page::get_tag_url( $tag['slug'] ) ); ?>" title="<?php echo esc_attr( $tag['name'] ); ?> Design Tags"><?php echo esc_html( $tag['name'] ); ?></a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
<?php if ( 'disabled' !== $product_menu_type ) { ?>
	<div class="customize_products <?php echo esc_attr( $product_menu_type ); ?>">
		<h2>Load design on another product:</h2>
		<?php
		$out = MyStyle_Design_Profile_Page::get_instance()->get_product_list_html();

		if ( strlen( $out ) < 50 ) {
			$out = '<p>Sorry, no products are currently available for customization.</p>';
		}

		echo $out; // phpcs:ignore WordPress.XSS.EscapeOutput
		?>
	</div>
	<?php
}
?>
</div>
