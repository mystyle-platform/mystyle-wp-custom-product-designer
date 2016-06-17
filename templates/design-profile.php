<?php
/**
 * The template for displaying the MyStyle Design Profile page content.
 * @package MyStyle
 * @since 1.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div id="mystyle-design-profile-wrapper" class="woocommerce">
    <img id="mystyle-design-profile-img" src="<?php echo $design->get_web_url(); ?>"/>
    
    <button onclick="location.href = '<?php echo $design->get_reload_url( ); ?>';" class="button">Customize</a>
</div>

