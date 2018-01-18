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
    exit; // Exit if accessed directly
}

?>
<div id="mystyle-design-profile-index-wrapper" class="woocommerce">
    <?php 
        if( ( isset($pager) ) && ( $pager->get_items() != null ) ) {
    ?>
            <ul class="mystyle-designs">
                <?php
                    /* @var $design MyStyle_Design */
                    foreach($pager->get_items() AS $design) { 
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
    
            <nav class="woocommerce-pagination">
                <?php
                    echo paginate_links( array(
                            'base'         => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
                            'format'       => '',
                            'add_args'     => false,
                            'current'      => $pager->get_current_page_number(),
                            'total'        => $pager->get_page_count(),
                            'prev_text'    => '&larr;',
                            'next_text'    => '&rarr;',
                            'type'         => 'list',
                            'end_size'     => 3,
                            'mid_size'     => 3
                    ) );
                ?>
            </nav>
    <?php
        } //end if designs
    ?>
</div>

