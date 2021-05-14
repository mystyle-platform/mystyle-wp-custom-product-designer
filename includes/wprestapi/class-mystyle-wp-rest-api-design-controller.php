<?php
/**
 * The MyStyle_Wp_Rest_Api_Design_Controller class hooks the WP REST API
 * to add an endpoint and functionality for working with designs.
 *
 * @package MyStyle
 * @since 3.12.0
 */

/**
 * MyStyle_Wp_Rest_Api_Design_Controller class.
 */
class MyStyle_Wp_Rest_Api_Design_Controller extends WP_REST_Controller {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_MyStyle_Wp_Rest_Api_Design_Controller
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( &$this, 'register_routes' ), 10, 1 );
	}

	/**
	 * Filters the WC is_request_to_rest_api to include our endpoints for
	 * authentication.
	 *
	 * @param bool $is_request_to_rest_api Whether or not the request is to the
	 * WC REST API.
	 * @return bool
	 */
	protected function filter_is_request_to_rest_api( $is_request_to_rest_api ) {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) { // @codingStandardsIgnoreLine
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		// Check if our endpoint.
		$is_mystyle = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix . 'mystyle/' ) ); // @codingStandardsIgnoreLine

		return ( $is_mystyle || $is_request_to_rest_api );
	}


	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '2';
		$vendor    = 'wc-mystyle';
		$namespace = $vendor . '/v' . $version;
		$base      = 'designs';
		register_rest_route(
			$namespace, '/' . $base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
			)
		);
		register_rest_route(
			$namespace, '/' . $base . '/(?P<id>[\d]+)', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => array(
							'default' => 'view',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( false ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'default' => false,
						),
					),
				),
			)
		);
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		// TODO: Retrieve these values from the request params.
		$items_per_page      = 250;
		$current_page_number = 1;
		$current_user        = wp_get_current_user();
		$designs             = MyStyle_DesignManager::get_designs(
			$items_per_page,
			$current_page_number,
			$current_user
		);

		$data = array();
		foreach ( $designs as $design ) {
			$itemdata = $this->prepare_item_for_response( $design, $request );
			$data[]   = $this->prepare_response_for_collection( $itemdata );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if the design isn't
	 * found.
	 */
	public function get_item( $request ) {
		// Get parameters from request.
		$params       = $request->get_params();
		$current_user = wp_get_current_user();
		try {
			$design_id = $params['id'];
			/* @var $design \MyStyle_Design The requested design. */
			$design = MyStyle_DesignManager::get( $design_id, $current_user );

			if ( null === $design ) {
				throw new \MyStyle_Exception( 'Design not found.', 404 );
			}

			$data = $this->prepare_item_for_response( $design, $request );

			return new WP_REST_Response( $data, 200 );
		} catch ( \Exception $ex ) {
			return new WP_Error( $ex->getCode(), $ex->getMessage() );
		}
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {
		try {
			$current_user = wp_get_current_user();

			// Get parameters from request.
			$json_body_str = $request->get_body();
			$json_arr      = json_decode( $json_body_str, true );

			/* @var $design \MyStyle_Design The design. */
			$design = MyStyle_Design::create_from_json( $json_body_str );

			$design = MyStyle_DesignManager::persist( $design );

			// Tags.
			if ( isset( $json_arr['tags'] ) ) {
				foreach ( $json_arr['tags'] as $tag_elem ) {
					$tag = ( is_array( $tag_elem ) )
						? $tag_elem['slug']
						: $tag_elem;
					MyStyle_DesignManager::add_tag_to_design(
						$design->get_design_id(),
						$tag,
						$current_user
					);
				}
			}

			$data = $this->prepare_item_for_response( $design, $request );

			return new WP_REST_Response( $data, 200 );

		} catch ( \Exception $ex ) {
			return new WP_Error( $ex->getCode(), $ex->getMessage() );
		}
	}

	/**
	 * Update one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if the design isn't
	 * found.
	 */
	public function update_item( $request ) {
		try {
			// Get parameters from request.
			$params        = $request->get_params();
			$design_id     = $params['id'];
			$json_body_str = $request->get_body();
			$json_arr      = json_decode( $json_body_str, true );
			$current_user  = wp_get_current_user();

			/* @var $design_orig \MyStyle_Design The design that is being updated. */
			$design_orig = MyStyle_DesignManager::get( $design_id, $current_user );

			if ( null === $design_orig ) {
				throw new \MyStyle_Exception( 'Design not found.', 404 );
			}

			/* @var $design \MyStyle_Design The design. */
			$design = MyStyle_Design::create_from_json( $json_body_str );

			$design = MyStyle_DesignManager::persist( $design );

			// Tags.
			$tags = array();
			if ( isset( $json_arr['tags'] ) ) {
				foreach ( $json_arr['tags'] as $tag_elem ) {
					$tag    = ( is_array( $tag_elem ) )
						? $tag_elem['slug']
						: $tag_elem;
					$tags[] = $tag;
				}
			}
			MyStyle_DesignManager::update_design_tags(
				$design->get_design_id(),
				$tags,
				$current_user
			);

			$data = $this->prepare_item_for_response( $design, $request );

			return new WP_REST_Response( $data, 200 );

		} catch ( \Exception $ex ) {
			return new WP_Error( $ex->getCode(), $ex->getMessage() );
		}
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if the design isn't
	 * found.
	 */
	public function delete_item( $request ) {
		try {
			// Get parameters from request.
			$params       = $request->get_params();
			$current_user = wp_get_current_user();
			$design_id    = $params['id'];
			/* @var $design \MyStyle_Design The requested design. */
			$design = MyStyle_DesignManager::get( $design_id, $current_user );

			if ( null === $design ) {
				throw new \MyStyle_Exception( 'Design not found.', 404 );
			}

			$deleted = MyStyle_DesignManager::delete( $design );

			if ( ! $deleted ) {
				throw new \MyStyle_Exception( 'Can\'t delete design', 500 );
			}

			return new WP_REST_Response( true, 200 );

		} catch ( \Exception $ex ) {
			return new WP_Error( $ex->getCode(), $ex->getMessage() );
		}
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return wc_rest_check_manager_permissions( 'settings', 'read' );
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		return array();
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item WordPress Representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		/* @var $item \MyStyle_Design phpcs:ignore */
		$design   = $item;
		$itemdata = $design->json_encode();

		// Add any design tags.
		$tags = MyStyle_DesignManager::get_design_tags(
			$design->get_design_id(),
			true, // with_slug.
			true // with_id.
		);
		if ( 0 < count( $tags ) ) {
			$itemdata['tags'] = array();
			foreach ( $tags as $tag ) {
				$itemdata['tags'][] = $tag;
			}
		}

		return $itemdata;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'description'       => 'Limit results to those matching a string.',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}


	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Design_Profile_Page Returns the singleton instance of
	 * this class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Profile_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
