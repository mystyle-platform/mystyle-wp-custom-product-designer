<?php
/**
 * Creates a mock MyStyle_Design
 *
 * @package MyStyle
 * @since 1.3.0
 */

/**
 * MyStyle_MockDesign class.
 */
abstract class MyStyle_MockDesign {

	/**
	 * Returns a mock MyStyle_Design for use by the tests.
	 *
	 * @param integer $design_id An id for the design.
	 * @return MyStyle_Design Returns a MyStyle_Design for use by the tests.
	 */
	public static function get_mock_design( $design_id ) {

		// Mock the POST.
		$post                = array();
		$post['description'] = 'test description';
		$post['design_id']   = $design_id;
		$post['product_id']  = 0;
		$post['h']           = base64_encode(
			wp_json_encode(
				array(
					'post' => array(
						'add-to-cart' => 0,
					),
				)
			)
		);
		$post['price']       = 0;
		$post['user_id']     = 0; // NOTE: this is the mystyle user_id and not the WP user_id
		// Create the design.
		$design = MyStyle_Design::create_from_post( $post );

		return $design;
	}

}
