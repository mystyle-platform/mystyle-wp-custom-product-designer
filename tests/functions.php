<?php
/**
 * Shared functions for testing the plugin.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Given a base registry array entry, this function will return all function
 * names within that array.
 *
 * @param array $reg_array_1 The base registry array that you want to search
 * through.
 * @return array An array of the function names that were found.
 */
function get_function_names( $reg_array_1 ) {
	$function_names = array();
	foreach ( $reg_array_1 as $reg_array_2 ) {
		foreach ( $reg_array_2 as $reg_array_3 ) {
			$function_names[] = $reg_array_3['function'][1];
		}
	}
	return $function_names;
}

/**
 * Function that creates a simple WC_Product to run our tests against.
 *
 * @param string $type ( optional ) The product type to create (default:
 * WC_Product_Simple).
 * @return \WC_Product
 */
function create_test_product( $type = 'WC_Product_Simple' ) {
	$product = null;
	if ( MyStyle()->get_WC()->version_compare( '3.0', '<' ) ) {
		// Mock the global $post variable.
		$post_vars     = new stdClass();
		$post_vars->ID = 1;

		// Create a mock product using the mock Post.
		$product = new $type( new WP_Post( $post_vars ) );
	} else {
		$product = new $type();
	}

	return $product;
}

/**
 * Function to create a WooCommerce product for testing purposes.
 *
 * @param string $name ( optional ) A name/title for the product.
 * @param string $type ( optional ) The type of product to create
 * ( 'simple'|'variable' ).
 * @param array  $attributes ( optional ) An array of all possible product
 * attributes as a two dimensional array.
 * ( ex: array("color" => array("red","blue" ) ) ).
 * @return Returns the product/post id of the new product.
 */
function create_wc_test_product(
	$name = 'Test Product',
	$type = 'simple',
	$attributes = array() ) {
	$name        = $name;
	$sku         = strtoupper( $name );
	$description = 'This is a test product';
	$email       = 'test@example.com';
	$user_id     = wp_create_user( 'testuser', 'testpassword', $email );

	$post_id = wp_insert_post(
		array(
			'post_author'  => $user_id,
			'post_title'   => $name,
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => 'product',
		)
	);
	wp_set_object_terms( $post_id, $type, 'product_type' );
	update_post_meta( $post_id, '_visibility', 'visible' );
	update_post_meta( $post_id, '_stock_status', 'instock' );
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
 *
 * @param WP_Post $parent The parent product of the variation.
 * @param integer $variation_number A unique sequential number for the
 * variation. Variation numbers should start at 1.
 * @param array   $attributes An array of attributes for the variation.
 * @return Returns the product/post id of the new product.
 */
function create_wc_test_product_variation(
	WP_Post $parent,
	$variation_number,
	$attributes ) {

	// The product name (post title).
	$title = 'Variation of ' . $parent->post_title;

	// The slug.
	$name = 'product-' . $parent->ID . '-variation';
	if ( $variation_number > 1 ) {
		$name .= ( '-' . $variation_number );
	}

	$sku         = strtoupper( $name );
	$description = 'This is a test product variation';

	$post_id = wp_insert_post(
		array(
			'post_author'  => $parent->post_author,
			'post_title'   => $title,
			'post_name'    => $name,
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => 'product_variation',
			'post_parent'  => $parent->ID,
		)
	);
	wp_set_object_terms( $post_id, 'variable', 'product_type' );
	$parent_post_meta = get_post_meta( $parent->ID );

	// Copy the post_meta from the parent.
	foreach ( $parent_post_meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	// Set the variation only post_meta.
	update_post_meta( $post_id, '_variation_description', '' );

	// Add the variation's atttributes as post_meta.
	foreach ( $attributes as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	return $post_id;
}

/**
 * This function fixes the data generated by the WC_Helper_Product::create_variation_product
 * function. That function doesn't properly create a variable product in WC < 3.0.
 * This function fixes it.
 *
 * @param WC_Product $product The product that you want to use.
 */
function fix_variation_product( WC_Product $product ) {

	if ( MyStyle()->get_WC()->version_compare( '3.0', '<' ) ) {

		// Properly create the product attributes on the post.
		update_post_meta(
			$product->id,
			'_product_attributes',
			array(
				'size' => array(
					'name'         => 'size',
					'value'        => 'small | large',
					'position'     => '0',
					'is_visible'   => 1,
					'is_variation' => 1,
					'is_taxonomy'  => 0,
				),
			)
		);

		// Copy the 'attribute_pa_size' meta to 'attribute_size' for all
		// children.
		foreach ( $product->get_children() as $child_id ) {
			// Get the size from the 'attribute_pa_size'.
			$size_arr = get_post_meta( $child_id, 'attribute_pa_size' );
			$size     = $size_arr[0];

			// Add the size to a new 'attribute_size' meta.
			update_post_meta( $child_id, 'attribute_size', $size );
		}
	}
}
