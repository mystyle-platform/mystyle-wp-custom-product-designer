<?php

/**
 * MyStyle_WC class. 
 * 
 * Wrapper class for facilitating interactions with woocommerce.
 * 
 * We primarily wrap WooCommerce for testing purposes.
 *
 * @package MyStyle
 * @since 1.4.10
 */
class MyStyle_WC extends MyStyle_AbstractWC implements MyStyle_WC_Interface {
    
    /**
     * Wrapper for the global wc_get_page_id function.
     * @param string $page
     * @return int
     */
    public function wc_get_page_id( $page ) {
        return wc_get_page_id( $page );
    }
    
}
