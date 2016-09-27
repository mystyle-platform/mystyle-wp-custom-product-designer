<?php
/**
 * Shared functions for testing the plugin.
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Given a base registry array entry, this private function will return all
 * function names within that array
 * @param type $regArray1 The base registry array that you want to search
 * through
 * @return array An array of the function names that were found.
 */
function get_function_names( $regArray1 ) {
   $function_names = array();
   foreach( $regArray1 as $regArray2 ) {
       foreach( $regArray2 as $regArray3 ) {
           $function_names[] = $regArray3['function'][1];
       }
   }
   return $function_names;
}

/**
 * Function to create a WooCommerce product for testing purposes.
 * @return Returns the product/post id of the new product.
 */
function create_wc_test_product() {
    $name = 'Test Product';
    $sku = strtoupper($name);
    $description = 'This is a test product';
    $email = 'test@example.com';
    $user_id = wp_create_user( 'testuser', 'testpassword', $email );
    
    $post_id = wp_insert_post( array(
        'post_author' => $user_id,
        'post_title' => $name,
        'post_content' => $description,
        'post_status' => 'publish',
        'post_type' => "product",
    ) );
    wp_set_object_terms( $post_id, 'simple', 'product_type' );
    update_post_meta( $post_id, '_visibility', 'visible' );
    update_post_meta( $post_id, '_stock_status', 'instock');
    update_post_meta( $post_id, 'total_sales', '0' );
    update_post_meta( $post_id, '_downloadable', 'no' );
    update_post_meta( $post_id, '_virtual', 'yes' );
    update_post_meta( $post_id, '_regular_price', '' );
    update_post_meta( $post_id, '_sale_price', '' );
    update_post_meta( $post_id, '_purchase_note', '' );
    update_post_meta( $post_id, '_featured', 'no' );
    update_post_meta( $post_id, '_weight', '' );
    update_post_meta( $post_id, '_length', '' );
    update_post_meta( $post_id, '_width', '' );
    update_post_meta( $post_id, '_height', '' );
    update_post_meta( $post_id, '_sku', $sku );
    update_post_meta( $post_id, '_product_attributes', array() );
    update_post_meta( $post_id, '_sale_price_dates_from', '' );
    update_post_meta( $post_id, '_sale_price_dates_to', '' );
    update_post_meta( $post_id, '_price', '' );
    update_post_meta( $post_id, '_sold_individually', '' );
    update_post_meta( $post_id, '_manage_stock', 'no' );
    update_post_meta( $post_id, '_backorders', 'no' );
    update_post_meta( $post_id, '_stock', '' );
    
    return $post_id;
}
