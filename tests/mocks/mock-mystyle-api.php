<?php
/**
 * Mocks the MyStyle API
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_MockAPI class.
 */
class MyStyle_MockAPI implements MyStyle_API_Interface {

	/**
	 * Stubs the response from the add_api_data_to_design function.
	 *
	 * @param MyStyle_Design $design The design that you want to use.
	 * @return \MyStyle_Design
	 */
	public function add_api_data_to_design( MyStyle_Design $design ) {

		$design_data               = array();
		$design_data['thumb_url']  = 'http://testhost/test_thumb_url.jpg';
		$design_data['web_url']    = 'http://testhost/test_web_url.jpg';
		$design_data['print_url']  = 'http://testhost/test_print_url.jpg';
		$design_data['design_url'] = 'http://testhost/test_design_url.jpg';
		$design_data['access']     = 0;
		$design_data['mobile']     = 0;
		$design_data['design_id']  = $design->get_design_id();
		$design_data['user_id']    = 1;
		$design_data['app_id']     = 0;
		$design_data['product_id'] = 0;
		$design_data['created']    = 0;

		$design->add_api_data( $design_data );

		return $design;
	}

	/**
	 * Stubs the get_user function. Creates and returns a MyStyle_User object
	 * using the passed user_id and a pre set email.
	 *
	 * @param integer $user_id The MyStyle user id.
	 * @return \MyStyle_User
	 */
	public function get_user( $user_id ) {

		$email = 'someone@example.com';

		$user = new \MyStyle_User( $user_id, $email );

		return $user;
	}

	/**
	 * Returns a mocked response from the MyStyle API. To use, hook the
	 * pre_http_request action.
	 *
	 * NOTE: We are returning both the design and the user in a single mocked
	 * response.  Our code will work in this scenario but this wouldn't ever
	 * actually be returned from the API.
	 *
	 * @wp-hook pre_http_request
	 * @param bool   $preempt Whether to preempt an HTTP request return. Default
	 * false.
	 * @param array  $args HTTP request arguments.
	 * @param string $url The request URL.
	 * @return Returns a mocked response from the MyStyle API.
	 */
	public static function mock_api_call( $preempt, $args, $url ) {
		// Get an instance of WP_Http.
		$http = _wp_http_get_object();

		// ------------- HEADER -------------- //
		$result                            = array();
		$result['headers']                 = array();
		$result['headers']['server']       = 'Apache';
		$result['headers']['vary']         = 'Accept-Encoding';
		$result['headers']['content-type'] = 'text/html';
		$result['headers']['date']         = 'Wed, 27 May 2015 20:59:04 GMT';
		$result['headers']['keep-alive']   = 'timeout=5, max=100';
		$result['headers']['access-control-allow-origin']  = '*';
		$result['headers']['connection"']                  = 'close';
		$result['headers']['x-powered-by']                 = 'PHP/5.5.9-1ubuntu4.9';
		$result['headers']['access-control-allow-headers'] = 'Origin, X-Requested-With, Content-Type, Accept';

		// ------------ DESIGN -------------- //
		$design_id = 1;

		$design               = array();
		$design['thumb_url']  = 'http://testhost/test_thumb_url.jpg';
		$design['web_url']    = 'http://testhost/test_web_url.jpg';
		$design['print_url']  = 'http://testhost/test_print_url.jpg';
		$design['design_url'] = 'http://testhost/test_design_url.jpg';
		$design['access']     = 0;
		$design['mobile']     = 0;
		$design['design_id']  = $design_id;
		$design['user_id']    = 1;
		$design['app_id']     = 0;
		$design['product_id'] = 0;
		$design['created']    = 0;

		$json                       = array();
		$json['data']               = array();
		$json['data'][ $design_id ] = $design;

		// ---------- DESIGNER/USER -----------
		$designer_id = 2;

		$designer            = array();
		$designer['user_id'] = $designer_id;
		$designer['email']   = 'someone@example.com';

		$json['data'][ $designer_id ] = $designer;

		// ---------- PUT IT ALL TOGETHER -------
		$result['body'] = wp_json_encode( $json );

		$result['headers']['content-length'] = strlen( $result['body'] );

		$result['response']             = array();
		$result['response']['code']     = 200;
		$result['response']['message']  = 'OK';
		$result['response']['cookies']  = array();
		$result['response']['filename'] = null;

		return $result;
	}

}
