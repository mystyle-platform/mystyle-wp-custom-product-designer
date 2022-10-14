<?php
/**
 * The MyStyle_Design_Collection_Page Singleton class has hooks for working with the
 * MyStyle Design Collection page.
 *
 * @package MyStyle
 * @since 3.14.0
 */

/**
 * MyStyle_Design_Collection_Page class.
 */
class MyStyle_Design_Collection_Page {

	/**
	 * The default title for the page.
	 *
	 * @var string
	 */
	private static $default_post_title = 'Design Collections';

	/**
	 * The default name for the page.
	 *
	 * @var string
	 */
	private static $default_post_name = 'design-collections';

	/**
	 * The default content for the page.
	 *
	 * @var string
	 */
	private static $default_post_content = '[mystyle_design_collections]';

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_Design_Collection_Page
	 */
	private static $instance;

	/**
	 * Pager for the page.
	 *
	 * @var \MyStyle_Pager
	 */
	private $pager;

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

		add_action( 'init', array( &$this, 'rewrite_rules' ) );
		add_action( 'query_vars', array( &$this, 'query_vars' ) );

		add_filter( 'document_title_parts', array( &$this, 'filter_document_title_parts' ), 10, 1 );
		add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
		add_filter( 'woocommerce_get_breadcrumb', array( &$this, 'filter_breadcrumbs' ), 20, 2 );
	}

	/**
	 * Add rewrite rule.
	 */
	public function rewrite_rules() {

		// Flush rewrite rules for newly created rewrites.
		flush_rewrite_rules();

		add_rewrite_rule(
			'design-collections/([a-zA-Z0-9_-].+)?$',
			'index.php?pagename=design-collections&collection_term=$matches[1]',
			'top'
		);
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array of query vars.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'collection_term';

		return $query_vars;
	}

	/**
	 * Function to determine if the post exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function exists() {
		$exists = false;

		// Get the page id of the Design Collection page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}

	/**
	 * Function to get the id of the page.
	 *
	 * @return int Returns the page id of the page.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if the page is
	 * missing.
	 * @todo Add unit testing
	 */
	public static function get_id() {
		// Get the page id of the Design Tag page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( ! isset( $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] ) ) {
			throw new MyStyle_Exception(
				__( 'Design Collection Page is Missing!', 'mystyle' ),
				404
			);
		}
		$page_id = $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ];

		return $page_id;
	}

	/**
	 * Function that tests to see if the passed id is the id of the Design
	 * Collection page OR the id of a translation of the Design Collection page.
	 *
	 * @param int $id The post id.
	 * @return bool Returns true if the passed id is the id of the Design
	 * Collection page OR the id of a translation of the Design Collection page.
	 * Otherwise, returns false.
	 * @todo Add unit testing.
	 */
	public static function is_design_collection_page( $id ) {
		$is_design_collection_page = false;

		if (
			( self::get_id() === $id ) ||
			( MyStyle_Wpml::get_instance()->is_translation_of_page( self::get_id(), $id ) )
		) {
			$is_design_collection_page = true;
		}

		return $is_design_collection_page;
	}

	/**
	 * Function to create the page.
	 *
	 * @return number Returns the page id of the Design Collection page.
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create() {
		// Create the Design Collections page.
		$design_tag_page = array(
			'post_title'   => self::$default_post_title,
			'post_name'    => self::$default_post_name,
			'post_content' => self::$default_post_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'guid'         => 'design-collections',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );

		// Store the design tag page's id in the database.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] = $post_id;
		$updated = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $post_id );
			throw new MyStyle_Exception( __( 'Could not store page id.', 'mystyle' ), 500 );
		}

		return $post_id;
	}

	/**
	 * Function that upgrades the page.
	 *
	 * @param string $old_version The version that you are upgrading from.
	 * @param string $new_version The version that you are upgrading to.
	 * @todo Add unit testing
	 */
	public static function upgrade( $old_version, $new_version ) {

		// Updated the option name for the page id (if necessary).
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if (
			( isset( $options['mystyle_design_collection_index_page_id'] ) )
			&& ( ! isset( $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] ) )
		) {
			$post_id = $options['mystyle_design_collection_index_page_id'];
			$options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] = $post_id;
			$updated = update_option( MYSTYLE_OPTIONS_NAME, $options );
		}
	}

	/**
	 * Filter the post title.
	 *
	 * @param string $title The title of the post.
	 * @param int    $id    The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
		if (
				( get_the_ID() === $id ) && // Make sure we're in the loop.
				( in_the_loop() ) // Make sure we're in the loop.
		) {
			$term = self::get_current_term( $id );

			if ( null !== $term ) {
				$title = ucfirst( $term->name ) . ' - ' . __( 'Design Collection', 'mystyle' );
			}
		}

		return $title;
	}

	/**
	 * Filters the parts of the document title.
	 *
	 * This sets the title tag in the HEAD of the HTML document to the title of
	 * the design (if a design title is set).
	 *
	 * @param array $title {
	 *     The document title parts.
	 *
	 *     @type string $title   Title of the viewed page.
	 *     @type string $page    Optional. Page number if paginated.
	 *     @type string $tagline Optional. Site description when on home page.
	 *     @type string $site    Optional. Site title when not on home page.
	 * }
	 * @todo Unit test this method.
	 */
	public function filter_document_title_parts( $title ) {

		$post = get_post();
		$id   = $post->ID;

		if ( self::get_id() !== $id ) {
			return $title;
		}

		$term = self::get_current_term( $id );

		if ( null !== $term ) {
			$title['title'] = $term->name . ' - ' . __( 'Design Collection', 'mystyle' );
		}

		return $title;
	}

	/**
	 * Filter the breadcrumbs.
	 *
	 * @param array $crumbs     The array of breadcrumbs.
	 * @param array $breadcrumb The current (top most) breadcrumb.
	 * @return array Returns the filtered breadcrumbs.
	 */
	public function filter_breadcrumbs( $crumbs, $breadcrumb ) {
		if ( empty( $crumbs ) ) {
			return $crumbs;
		}

		$post = get_post();
		$id   = $post->ID;

		if ( self::get_id() !== $id ) {
			return $crumbs;
		}

		$term = self::get_current_term( $id );

		if ( null !== $term ) {
			$new_crumb = array( $term->name, '#' );
			$crumbs[]  = $new_crumb;
		}

		return $crumbs;
	}

	/**
	 * Static function that builds a URL to the page for the collection.
	 *
	 * @param string $slug The collection slug.
	 * @return string Returns a URL that can be used to access the page for the
	 * collection.
	 */
	public static function get_collection_url( $slug ) {
		$url = site_url( 'design-collections' ) . '/' . $slug;

		return $url;
	}

	/**
	 * Attempt to fix the Design Collection page. This may involve creating,
	 * re-creating or repairing it.
	 *
	 * @return Returns a message describing the outcome of fix operation.
	 * @todo Add unit testing
	 */
	public static function fix() {
		$message = '<br/>';
		$status  = 'Design Collection page looks good, no action necessary.';
		// Get the page id of the Design Collection page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ] ) ) {
			$post_id  = $options[ MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME ];
			$message .= 'Found the stored ID of the Design Collection page...<br/>';

			/* @var $post \WP_Post phpcs:ignore */
			$post = get_post( $post_id );
			if ( null !== $post ) {
				$message .= 'Design Collection page exists...<br/>';

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
						$status   = 'Design Collection page fixed!<br/>';
					}
				} else {
					$message .= 'Design Collection page is published...<br/>';
				}

				// Check for the shortcode.
				if ( false === strpos( $post->post_content, '[mystyle_design_collections' ) ) {
					$message            .= 'The mystyle_design_collections shortcode not found in the page content, adding...<br/>';
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
						$status   = 'Design Collection page fixed!<br/>';
					}
				} else {
					$message .= 'Design Collection page has mystyle_design_collections shortcode...<br/>';
				}
			} else { // Post not found, recreate.
				$message .= 'Design Collection page appears to have been deleted, recreating...<br/>';
				try {
					$post_id = self::create();
					$status  = 'Design Collection page fixed!<br/>';
				} catch ( \Exception $e ) {
					$status = 'Error: ' . $e->getMessage();
				}
			}
		} else { // ID not available, create.
			$message .= 'Design Collection page missing, creating...<br/>';
			self::create();
			$status = 'Design Collection page fixed!<br/>';
		}

		$message .= $status;

		return $message;
	}

	/**
	 * Sets the current HTTP response code.
	 *
	 * @param int $http_response_code The HTTP response code to set as the
	 * currently set response code. This is used by the shortcode and view
	 * layer.  We set it as a variable since it is difficult to retrieve in
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
	 * Gets the current WP_Term.
	 *
	 * @param int $id The id of the current post/page.
	 * @return \WP_Term|null Returns the current WP_Term (or null if there isn't
	 * one).
	 * @gloab \WP_Query $wp_query
	 */
	private static function get_current_term( $id ) {
		global $wp_query;

		$current_term = null;
		if ( isset( $wp_query->query['collection_term'] ) ) {
			$term_slug = $wp_query->query['collection_term'];
			if ( preg_match( '/\//', $term_slug ) ) {
				$url_array = explode( '/', $term_slug );
				if ( 'page' === $url_array[0] ) {
					$term_slug = null;
				} else {
					$term_slug = $url_array[0];
				}
			}

			if ( null !== $term_slug ) {
				$current_term = get_term_by( 'slug', $term_slug, MYSTYLE_COLLECTION_NAME );
			}
		}

		return $current_term;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Collection_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
