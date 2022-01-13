<?php
/**
 * The MyStyle API class works with the MyStyle API.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle API class.
 */
class MyStyle_API implements MyStyle_API_Interface {

	/**
	 * The URL of the API endpoint.
	 *
	 * @var string
	 */
	private $api_endpoint_url;

	/**
	 * Constructor.
	 *
	 * @param string $api_endpoint_url The URL of the API endpoint.
	 */
	public function __construct( $api_endpoint_url ) {
		$this->api_endpoint_url = $api_endpoint_url;
	}

	/**
	 * Determines if the API credentials are valid.
	 *
	 * @return boolean Returns true if the credentials are valid, otherwise,
	 * returns false. If either the api_key or secret aren't set, the function
	 * returns false.
	 */
	public function has_valid_credentials() {
		$has_valid_credentials = false;
		$design_id             = 1; // An arbitrary design id.

		// Set up the api call variables.
		$api_key = MyStyle_Options::get_api_key();
		$secret  = MyStyle_Options::get_secret();
		$action  = 'design';
		$method  = 'get';
		$data    = '{\'design_id\':[' . $design_id . ']}';
		$ts      = time();

		$to_hash = $action . $method . $api_key . $data . $ts;
		$sig     = base64_encode( hash_hmac( 'sha256', $to_hash, $secret, true ) );

		$post_data           = array();
		$post_data['action'] = $action;
		$post_data['method'] = $method;
		$post_data['app_id'] = $api_key;
		$post_data['data']   = $data;
		$post_data['sig']    = $sig;
		$post_data['ts']     = $ts;
		$response            = wp_remote_post(
			$this->api_endpoint_url,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => $post_data,
				'cookies'     => array(),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$response_data = json_decode( $response['body'], true );

			if ( ! isset( $response_data['error'] ) ) {
				$has_valid_credentials = true;
			}
		}

		return $has_valid_credentials;
	}

	/**
	 * Retrieves design data from the API and adds it to the passed design
	 * object.
	 *
	 * @param MyStyle_Design $design The design that you are working with.
	 * @return \MyStyle_Design
	 */
	public function add_api_data_to_design( MyStyle_Design $design ) {

		// Set up the api call variables.
		$api_key = MyStyle_Options::get_api_key();
		$secret  = MyStyle_Options::get_secret();
		$action  = 'design';
		$method  = 'get';
		$data    = '{"design_id":[' . $design->get_design_id() . ']}';
		$ts      = time();

		$to_hash = $action . $method . $api_key . $data . $ts;
		$sig     = base64_encode( hash_hmac( 'sha256', $to_hash, $secret, true ) );

		$post_data           = array();
		$post_data['action'] = $action;
		$post_data['method'] = $method;
		$post_data['app_id'] = $api_key;
		$post_data['data']   = $data;
		$post_data['sig']    = $sig;
		$post_data['ts']     = $ts;

		$response = wp_remote_post(
			$this->api_endpoint_url,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => $post_data,
				'cookies'     => array(),
			)
		);
        
		if ( is_wp_error( $response ) ) {
			// We fail silently and write to the log.
			$error_message = $response->get_error_message();
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $error_message );
		} else {
			$response_data = json_decode( $response['body'], true );
			$design_data   = $response_data['data'][ $design->get_design_id() ];

			$design->add_api_data( $design_data );
		}

		return $design;
	}

	/**
	 * Creates and returns a MyStyle_User object using the passed user_id and
	 * data retrieved from the API.
	 *
	 * @param integer $user_id The MyStyle user id.
	 * @return \MyStyle_User
	 */
	public function get_user( $user_id ) {
		/* @var $user \MyStyle_User The MyStyle User. */
		$user = null;

		// Set up the api call variables.
		$api_key = MyStyle_Options::get_api_key();
		$secret  = MyStyle_Options::get_secret();
		$action  = 'user';
		$method  = 'get';
		$data    = '{"user_id":[' . $user_id . ']}';
		$ts      = time();

		$to_hash = $action . $method . $api_key . $data . $ts;
		$sig     = base64_encode( hash_hmac( 'sha256', $to_hash, $secret, true ) );

		$post_data           = array();
		$post_data['action'] = $action;
		$post_data['method'] = $method;
		$post_data['app_id'] = $api_key;
		$post_data['data']   = $data;
		$post_data['sig']    = $sig;
		$post_data['ts']     = $ts;

		$response = wp_remote_post(
			$this->api_endpoint_url,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => $post_data,
				'cookies'     => array(),
			)
		);

		if ( is_wp_error( $response ) ) {
			// We fail silently and write to the log.
			$error_message = $response->get_error_message();
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $error_message );
		} else {
			$response_data = json_decode( $response['body'], true );

			$user_data = $response_data['data'][ $user_id ];
			$user      = new \MyStyle_User( $user_id, $user_data['email'] );
		}

		return $user;
	}

}
