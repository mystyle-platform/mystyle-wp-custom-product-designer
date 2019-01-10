<?php
/**
 * The template for displaying the MyStyle Design Profile page content.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 1.4.0
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>
<div id="mystyle-design-profile-wrapper" class="woocommerce">
    <ul class="mystyle-button-group mystyle-design-nav">
		<?php if (!empty($previous_design_url)) { ?>
			<li><a href="<?php echo $previous_design_url; ?>">&larr;</a></li>
		<?php } else { ?>
			<li>&nbsp;</li>
		<?php } ?>
		<li><a href="<?php echo get_permalink(MyStyle_Design_Profile_Page::get_id()); ?>">&uarr;</a></li>
		<?php if (!empty($next_design_url)) { ?>
			<li><a href="<?php echo $next_design_url; ?>">&rarr;</a></li>
		<?php } else { ?>
			<li>&nbsp;</li>
		<?php } ?>
    </ul>
    <img id="mystyle-design-profile-img" src="<?php echo $design->get_web_url(); ?>"/>
    <ul class="mystyle-button-group">
        <li>
            <form enctype="multipart/form-data" method="post" action="<?php echo get_permalink($design->get_product_id()); ?>">
				<?php
				//if we have the cart_data (older versions of the plugin don't) through it all into hidden fields
				if ($design->get_cart_data_array() != null) {
					foreach ($design->get_cart_data_array() AS $key => $value) {
						echo '<input type="hidden" name="' . $key . '" value="' . sanitize_title($value) . '" />';
					}
				} else {
					//if we don't have the cart data just use the product_id
					echo '<input type="hidden" name="add-to-cart" value="' . $design->get_product_id() . '" />';
				}
				?>
                <input type="hidden" name="design_id" value="<?php echo $design->get_design_id(); ?>" />
				<?php if (MyStyle_Design_Profile_Page::show_add_to_cart_button()) { ?>
					<button type="submit" class="button">Add to Cart</a>
					<?php } ?>
            </form>
        </li>
        <li><a href="<?php echo $design->get_reload_url(); ?>" class="button">Customize</a></li>
        <li><a href="<?php echo $design->get_scratch_url(); ?>" class="button">Design from scratch</a></li>

    </ul>

    <div class="product_description">
    	<?php 
			$product_id = $design->get_product_id();
			$product_link = get_permalink( $product_id );
			$product = wc_get_product( $product_id ); 
			$layout_view = MyStyle_Options::get_layout_view(); ?>
			<h2 class='linked_title'><a href="<?php echo $product_link; ?>"><?php echo "Custom ".$product->get_title(); ?></a></h2>
			<div class='linked_desc'><?php echo ( $product->get_description() ) ?: 'No description.'; ?></div>
    </div>
    <div class="customize_products <?php echo $layout_view; ?>">
    	<h2>Load design on another product:</h2>
    	<?php 
			$mystyle_app_id = MyStyle_Options::get_api_key();
			$out = '';
			add_filter('woocommerce_shortcode_products_query', array('MyStyle_Customizer_Shortcode', 'modify_woocommerce_shortcode_products_query'), 10, 1);
	      	remove_action( 'woocommerce_after_shop_loop',  'woocommerce_catalog_ordering', 10 );
	      	remove_action( 'woocommerce_after_shop_loop',  'woocommerce_result_count', 20 );
	      	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

			$out = do_shortcode('[products per_page="12" limit="12" pagination="true"]');

			if (strlen($out) < 50) {
				$out = '<p>Sorry, no products are currently available for customization.</p>';
			}
			echo $out;
    	?>
    </div>
</div>