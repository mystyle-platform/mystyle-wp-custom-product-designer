<?php
/**
 * The template for displaying the MyStyle Design Profile index page.
 * @package MyStyle
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div id="mystyle-design-profile-index-wrapper" class="woocommerce">
    <?php 
        if( $designs != null ) {
    ?>
            <ul>
                <?php
                    foreach($designs AS $design) { 
                        $design_url = MyStyle_Design_Profile_page::get_design_url( $design );
                ?>
                        <li>
                            <a href="<?php echo $design_url ?>">
                                <img src="<?php echo $design->get_thumb_url(); ?>" />
                                <span class="mystyle-design-id">
                                    <?php echo $design->get_design_id(); ?>
                                </span>
                            </a>
                        </li>
                <?php         
                    } //end foreach
                ?>
            </ul>
    <?php
        } //end if designs
    ?>
</div>

