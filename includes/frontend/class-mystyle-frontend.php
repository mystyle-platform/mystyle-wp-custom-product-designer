<?php
/**
 * The MyStyle FrontEnd class sets up and controls the MyStyle front end
 * interface.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_FrontEnd class.
 */
class MyStyle_FrontEnd {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Frontend
	 */
	private static $instance;

	/**
	 * Stores the current user (when the class is instantiated as a singleton).
	 *
	 * @var WP_User
	 */
	private $user;

	/**
	 * Stores the current session (when the class is instantiated as a
	 * singleton).
	 *
	 * @var MyStyle_Session
	 */
	private $session;

	/**
	 * The current MyStyle_Design. This is retrieved via the design_id query
	 * variable in the url. If there isn't a design_id in the url, it will be
	 * null.
	 *
	 * @var MyStyle_Design
	 */
	private $design;

	/**
	 * Stores the currently thrown exception ( if any ) (when the class is
	 * instantiated as a singleton).
	 *
	 * @var MyStyle_Exception
	 */
	private $exception;

	/**
	 * Stores the current ( when the class is instantiated as a singleton ) status
	 * code.  We store it here since php's http_response_code() function wasn't
	 * added until php 5.4.
	 * see: http://php.net/manual/en/function.http-response-code.php
	 *
	 * @var int
	 */
	private $http_response_code;

	/**
	 * Constructor, constructs the class and sets up the hooks.
	 */
	public function __construct() {
		$this->http_response_code = 200;

		add_filter( 'query_vars', array( &$this, 'add_query_vars_filter' ), 10, 1 );
		add_filter( 'wp_head', array( &$this, 'render_form_integration_config' ), 0 );
        add_filter( 'body_class', array( &$this, 'filter_body_class' ), 10, 1 );

		add_action( 'init', array( &$this, 'init' ), 10, 0 );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_frontend_js' ), 10, 0 );
		add_action( 'template_redirect', array( &$this, 'init_vars' ), 10, 0 );
	}

	/**
	 * Init the MyStyle front end.
	 */
	public function init() {
		// Add the MyStyle frontend stylesheet to the WP frontend head.
		wp_register_style( 'myStyleFrontendStylesheet', MYSTYLE_ASSETS_URL . 'css/frontend.min.css', array(), MYSTYLE_VERSION );
		wp_enqueue_style( 'myStyleFrontendStylesheet' );

		// Add the WordPress Dashicons icon font to the frontend.
		wp_enqueue_style( 'dashicons' );

		// Add the swfobject.js file to the WP head.
		wp_register_script( 'swfobject', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js' );
		wp_enqueue_script( 'swfobject' );
	}

	/**
	 * Enqueue our frontend.js script and the mystyle_wp global object.
	 */
	public function enqueue_frontend_js() {
		wp_enqueue_script(
			'frontend_js',
			MYSTYLE_ASSETS_URL . 'js/frontend.js',
			array(), // deps.
			MYSTYLE_VERSION, // version.
			true
		);

		wp_localize_script(
			'frontend_js',
			'mystyle_wp',
			array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
		);
	}

	/**
	 * Init the MyStyle front end variables.
	 *
	 * @throws MyStyle_Not_Found_Exception Throws a MyStyle_Not_Found_Exception
	 * if the design isn't found.
	 */
	public function init_vars() {

		// Get the design from the url.
		$design_id = MyStyle_Util::get_query_var_int( 'design_id' );

		if ( null !== $design_id ) {
			$this->user = wp_get_current_user();
			$this->session = MyStyle()->get_session();

			// Get the design. If the user doesn't have access, an exception
			// is thrown.
			try {

				$this->design = MyStyle_DesignManager::get(
					$design_id,
					$this->user,
					$this->session
				);

				if ( null === $this->design ) {
					throw new MyStyle_Not_Found_Exception( 'Design \'' . $design_id . '\' not found.' );
				}

				// When an exception is thrown, set the status code and set the
				// exception in the singleton instance, it will later be used by
				// the shortcode and view layer.
			} catch ( MyStyle_Not_Found_Exception $ex ) {
				$response_code = 404;
				status_header( $response_code );

				$this->exception          = $ex;
				$this->http_response_code = $response_code;
			} catch ( MyStyle_Unauthorized_Exception $ex ) { // unauthenticated.
				// Note: we would ideally return a 401 but WordPress seems to work best
				// with 200.
				$response_code = 200;
				status_header( $response_code );

				$this->exception          = $ex;
				$this->http_response_code = $response_code;
			} catch ( MyStyle_Forbidden_Exception $ex ) {
				// Note: we would ideally return a 403 but WordPress seems to work best
				// with 200.
				$response_code = 200;
				status_header( $response_code );

				$this->exception          = $ex;
				$this->http_response_code = $response_code;
			}
		}
	}

	/**
	 * Renders the form_integration_config to the page.
	 */
	public function render_form_integration_config() {
		$form_integration_config = MyStyle_Options::get_form_integration_config();

		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo $form_integration_config;
	}

    /**
     * Add body class for MyStyle customizable products
     */
    public function filter_body_class( $classes ) {

        if( is_product() ) {
            $product = wc_get_product() ;

            if ( !$product->is_in_stock() ) {
                $classes[] = 'mystyle-product-not-in-stock' ;
            }
        }

		return $classes;
    }

	/**
	 * Add design_id as a custom query var.
	 *
	 * @param array $vars The original query vars.
	 * @return array Returns the query vars with 'design_id' added.
	 */
	public function add_query_vars_filter( $vars ) {
		$vars[] = 'design_id';

		return $vars;
	}

	/**
	 * Gets the current WP_User.
	 *
	 * @return WP_User Returns the current WordPress user, if any.
	 */
	public function get_user() {
		return $this->user;
	}

	/**
	 * Gets the current MyStyle_Session.
	 *
	 * @return MyStyle_Session Returns the current MyStyle_Session.
	 */
	public function get_session() {
		return $this->session;
	}

	/**
	 * Sets the current design. This is just here for testing purposes.
	 *
	 * @param MyStyle_Design $design The design to set as the current design.
	 */
	public function set_design( MyStyle_Design $design ) {
		$this->design = $design;
	}

	/**
	 * Gets the current design.
	 *
	 * @return MyStyle_Design Returns the currently loaded MyStyle_Design.
	 */
	public function get_design() {
		return $this->design;
	}

	/**
	 * Gets the current exception.
	 *
	 * @return MyStyle_Exception Returns the currently thrown MyStyle_Exception
	 * if any. This is used by the shortcode and view layer.
	 */
	public function get_exception() {
		return $this->exception;
	}

	/**
	 * Gets the current http_response_code.
	 *
	 * @return integer Returns the http response code that we are returning.
	 */
	public function get_http_response_code() {
		return $this->http_response_code;
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
