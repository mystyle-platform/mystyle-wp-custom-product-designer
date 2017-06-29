<?php
/**
 * The template for displaying the thumbnail cell for the cart item rows for
 * customized products.
 * 
 * This template can be overridden by copying it to yourtheme/mystyle/cart/cart-item_thumbnail.php.
 * 
 * @package MyStyle
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<figure>
    <a href="<?php echo $design_profile_url ?>">
        <?php echo $product_img_tag; ?>
    </a>
    
    <figcaption style="font-size: 0.5em">
        Design Id: <a href="<?php echo $design_profile_url; ?>"><?php echo $design->get_design_id();?></a><br/>
        <a href="<?php echo $customizer_url; ?>">Edit</a>                                
    </figcaption>
</figure>
