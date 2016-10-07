<?php

/**
 * Mocks our interface with WooCommerce.
 *
 * @package MyStyle
 * @since 1.4.10
 */
class MyStyle_MockWC extends MyStyle_AbstractWC implements MyStyle_WC_Interface {
    
    /**
     * Fake for the global wc_get_page_id function.
     * @param string $page
     * @return int
     */
    public function wc_get_page_id( $page ) {
        return 1;
    }

}
