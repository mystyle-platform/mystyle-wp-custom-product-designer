<?php

/**
 * MyStyle_WC_Interface class. 
 * 
 * Interface for facilitating interactions with woocommerce.
 *
 * @package MyStyle
 * @since 1.5.0
 */
interface MyStyle_WC_Interface {
    
    public function wc_get_page_id( $page );
    
    public function get_matching_variation( $product_id, $variation );
    
}
