<?php
/**
 * The MyStyle_Design_Tag_Index_Page Singleton class has hooks for working with
 * the MyStyle Design Tag Index page.
 *
 * The Design_Tag_Index_Page shows just the design tags as simple links. This is
 * in contrast to the Design_Tag_Page which shows design tags with their
 * designs.
 *
 * @package MyStyle
 * @since 3.19.0
 */

/**
 * MyStyle_Design_Tag_Index_Page class.
 */
class MyStyle_Design_Tag_Index_Page {

	/**
	 * The default content for the page.
	 *
	 * @var string
	 */
	private static $default_post_content = '[mystyle_design_tags show_designs="false"]';

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_Design_Tag_Index_Page
	 */
	private static $instance;

	/**
	 * Stores the current (when the class is instantiated as a singleton)
	 * status code. We store it here since PHP's http_response_code() function
	 * wasn't added until PHP 5.4.
	 *
	 * See: http://php.net/manual/en/function.http-response-code.php
	 *
	 * @var int
	 */
	private $http_response_code;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->http_response_code = 200;
	}

	/**
	 * Function to determine if the post exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function exists() {
		$exists = false;

		// Get the page id of the Design Profile page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}

	/**
	 * Function to create the index page.
	 *
	 * @return number Returns the page id of the Design Tag Index page.
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create() {
		// Create the Design Profile page.
		$design_tag_page = array(
			'post_title'   => 'Design Tags Index',
			'post_name'    => 'design-tags-index',
			'post_content' => self::$default_post_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );

		// Store the design tag index page's id in the database.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ] = $post_id;
		$updated = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $post_id );
			throw new MyStyle_Exception(
				__( 'Could not store index page id.', 'mystyle' ),
				500
			);
		}

		return $post_id;
	}

	/**
	 * Attempt to fix the Design Tag Index page. This may involve creating,
	 * re-creating or repairing it.
	 *
	 * @return Returns a message describing the outcome of fix operation.
	 * @todo Add unit testing
	 */
	public static function fix() {
		$message = '<br/>';
		$status  = 'Design Tag Index page looks good, no action necessary.';
		// Get the page id of the Customize page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ] ) ) {
			$post_id  = $options[ MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME ];
			$message .= 'Found the stored ID of the Design Tag Index page...<br/>';

			/* @var $post \WP_Post phpcs:ignore */
			$post = get_post( $post_id );
			if ( null !== $post ) {
				$message .= 'Design Tag Index page exists...<br/>';

				// Check the status.
				if ( 'publish' !== $post->post_status ) {
					$message          .= 'Status was "' . $post->post_status . '", changing to "publish"...<br/>';
					$post->post_status = 'publish';

					/* @var $error \WP_Error phpcs:ignore */
					$errors = wp_update_post( $post, true );

					if ( is_wp_error( $errors ) ) {
						foreach ( $errors as $error ) {
							$messages .= $error . '<br/>';
							$status   .= 'Fix errored out :(<br/>';
						}
					} else {
						$message .= 'Status updated.<br/>';
						$status   = 'Design Tag Index page fixed!<br/>';
					}
				} else {
					$message .= 'Design Tag Index page is published...<br/>';
				}

				// Check for the shortcode.
				if ( false === strpos( $post->post_content, '[mystyle_design_tags' ) ) {
					$message            .= 'The mystyle_customizer shortcode not found in the page content, adding...<br/>';
					$post->post_content .= self::$default_post_content;

					/* @var $error \WP_Error phpcs:ignore */
					$errors = wp_update_post( $post, true );

					if ( is_wp_error( $errors ) ) {
						foreach ( $errors as $error ) {
							$messages .= $error . '<br/>';
							$status   .= 'Fix errored out :(<br/>';
						}
					} else {
						$message .= 'Shortcode added.<br/>';
						$status   = 'Design Tag Index page fixed!<br/>';
					}
				} else {
					$message .= 'Design Tag Index page has mystyle_customizer shortcode...<br/>';
				}
			} else { // Post not found, recreate.
				$message .= 'Design Tag Index page appears to have been deleted, recreating...<br/>';
				try {
					$post_id = self::create();
					$status  = 'Design Tag Index page fixed!<br/>';
				} catch ( \Exception $e ) {
					$status = 'Error: ' . $e->getMessage();
				}
			}
		} else { // ID not available, create.
			$message .= 'Design Tag Index page missing, creating...<br/>';
			self::create();
			$status = 'Design Tag Index page fixed!<br/>';
		}

		$message .= $status;

		return $message;
	}

	/**
	 * Sets the current HTTP response code.
	 *
	 * @param int $http_response_code The HTTP response code to set as the
	 * currently set response code. This is used by the shortcode and view
	 * layer. We set it as a variable since it is difficult to retrieve in
	 * php < 5.4.
	 */
	public function set_http_response_code( $http_response_code ) {
		$this->http_response_code = $http_response_code;
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( $http_response_code );
		}
	}

	/**
	 * Gets the current HTTP response code.
	 *
	 * @return int Returns the current HTTP response code. This is used by the
	 * shortcode and view layer.
	 */
	public function get_http_response_code() {
		if ( function_exists( 'http_response_code' ) ) {
			return http_response_code();
		} else {
			return $this->http_response_code;
		}
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Tag_Index_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
