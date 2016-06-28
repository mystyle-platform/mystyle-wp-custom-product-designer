<?php
/**
 * The template for displaying the MyStyle Design Profile page content.
 * @package MyStyle
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div id="mystyle-design-profile-wrapper" class="woocommerce">
    <img id="mystyle-design-profile-img" src="<?php echo $design->get_web_url(); ?>"/>
    
    <ul class="mystyle-button-group">
        <li><a onclick="location.href = '<?php echo $design->get_reload_url(); ?>';" class="button">Customize</a></li>
        <li>
            <form enctype="multipart/form-data" method="post">
                <input type="hidden" name="add-to-cart" value="<?php echo $design->get_product_id(); ?>" />
                <input type="hidden" name="design_id" value="<?php echo $design->get_design_id(); ?>" />
                <button type="submit" class="button">Add to Cart</a>
            </form>
        </li>
    </ul>
</div>

