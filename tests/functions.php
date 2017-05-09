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
 * Function that creates a simple WC_Product to run our tests against.
 * @param string $type (optional) The product type to create (default: 
 * WC_Product_Simple).
 * @return \WC_Product
 */
function create_test_product( $type = 'WC_Product_Simple' ) {
    $product = null;
    if(WC_VERSION < 2.3) {
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;

        //Create a mock product using the mock Post
        $product = new $type( new WP_Post( $post_vars ) );
    } else {
        $product = new $type();
    }

    return $product;
}

/**
 * Function to create a WooCommerce product for testing purposes.
 * @param string $name (optional) A name/title for the product.
 * @param string $type (optional) The type of product to create 
 * ('simple'|'variable').
 * @param array $attributes (optional) An array of all possible product
 * attributes as a two dimensional array 
 * (ex: array("color" => array("red","blue")) ).
 * @return Returns the product/post id of the new product.
 */
function create_wc_test_product(
                $name = 'Test Product',
                $type = 'simple',
                $attributes = array()
        ) 
{
    $name = $name;
    $sku = strtoupper( $name );
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
    wp_set_object_terms( $post_id, $type, 'product_type' );
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

/**
 * Function to create a WooCommerce product variation for testing purposes.
 * @param WP_Post $parent The parent product of the variation.
 * @param integer $variation_number A unique sequential number for the
 * variation. Variation numbers should start at 1.
 * @param array $attributes An array of $attributes for the variation.
 * @return Returns the product/post id of the new product.
 */
function create_wc_test_product_variation(
            WP_Post $parent,
            $variation_number,
            $attributes
        ) 
{
    
    //the product name (post title)
    $title = 'Variation of ' . $parent->post_title;
    
    //the slug 
    $name = 'product-' . $parent->ID . '-variation';
    if($variation_number > 1) {
        $name .= ( '-' . $variation_number );
    }
    
    $sku = strtoupper( $name );
    $description = 'This is a test product variation';
    
    $post_id = wp_insert_post( array(
        'post_author' => $parent->post_author,
        'post_title' => $title,
        'post_name' => $name,
        'post_content' => $description,
        'post_status' => 'publish',
        'post_type' => 'product_variation',
        'post_parent' => $parent->ID
    ) );
    wp_set_object_terms( $post_id, 'variable', 'product_type' );
    $parent_post_meta = get_post_meta( $parent->ID );
    
    //copy the post_meta from the parent
    foreach( $parent_post_meta AS $key => $value ) {
        update_post_meta( $post_id, $key, $value );
    }
    
    //set the variation only post_meta
    update_post_meta( $post_id, '_variation_description', '' );
    
    //add the variation's atttributes as post_meta
    foreach( $attributes AS $key => $value ) {
        update_post_meta( $post_id, $key, $value );
    }
    
    return $post_id;
}
