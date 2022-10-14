<?php
/**
 * The MyStyle_Design_Tag_Page Singleton class has hooks for working with the
 * MyStyle Design Tag page.
 *
 * The Design_Tag_Page shows design tags with their designs. This is in
 * contrast to the Design_Tag_Index_Page which shows just the design tags as
 * simple links.
 *
 * @package MyStyle
 * @since 3.14.0
 */

/**
 * MyStyle_Design_Tag_Page class.
 */
class MyStyle_Design_Tag_Page {

	/**
	 * The default title for the page.
	 *
	 * @var string
	 */
	private static $default_post_title = 'Design Tags';

	/**
	 * The default name for the page.
	 *
	 * @var string
	 */
	private static $default_post_name = 'design-tags';

	/**
	 * The default content for the page.
	 *
	 * @var string
	 */
	private static $default_post_content = '[mystyle_design_tags per_tag="5" tags_per_page="12"]';

	/**
	 * Singleton class instance.
	 *
	 * @var \MyStyle_Design_Tag_Page
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
		add_action( 'posts_pre_query', array( &$this, 'alter_query' ), 25, 2 );
		add_action( 'template_redirect', array( &$this, 'set_pager' ) );

		add_filter( 'document_title_parts', array( &$this, 'filter_document_title_parts' ), 10, 1 );
		add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
		add_filter( 'woocommerce_get_breadcrumb', array( &$this, 'filter_breadcrumbs' ), 20, 2 );
		add_filter( 'has_post_thumbnail', array( &$this, 'has_post_thumbnail' ), 10, 3 );
		add_filter( 'wp_get_attachment_image_src', array( &$this, 'wp_get_attachment_image_src' ), 10, 4 );
		add_filter( 'post_link', array( &$this, 'post_link' ), 10, 3 );
	}

	/**
	 * Add rewrite rule.
	 */
	public function rewrite_rules() {

		// Flush rewrite rules for newly created rewrites.
		flush_rewrite_rules();

		add_rewrite_rule(
			'design-tags/([a-zA-Z0-9_-].+)?$',
			'index.php?pagename=design-tags&tag_term=$matches[1]',
			'top'
		);
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array of query vars.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'tag_term';

		return $query_vars;
	}

	/**
	 * Function to determine if the post exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function exists() {
		$exists = false;

		// Get the page id of the Design Tag page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] ) ) {
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
		if ( ! isset( $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] ) ) {
			throw new MyStyle_Exception(
				__( 'Design Tag Page is Missing!', 'mystyle' ),
				404
			);
		}
		$page_id = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ];

		return $page_id;
	}

	/**
	 * Function that tests to see if the passed id is the id of the Design Tag
	 * page OR the id of a translation of the Design Tag page.
	 *
	 * @param int $id The post id.
	 * @return bool Returns true if the passed id is the id of the Design Tag
	 * page OR the id of a translation of the Design Tag page. Otherwise,
	 * returns false.
	 * @todo Add unit testing.
	 */
	public static function is_design_tag_page( $id ) {
		$is_design_tag_page = false;

		if (
			( self::get_id() === $id ) ||
			( MyStyle_Wpml::get_instance()->is_translation_of_page( self::get_id(), $id ) )
		) {
			$is_design_tag_page = true;
		}

		return $is_design_tag_page;
	}

	/**
	 * Function to create the page.
	 *
	 * @return number Returns the page id of the Design Tag page.
	 * @throws \MyStyle_Exception Throws a MyStyle_Exception if unable to store
	 * the id of the created page in the db.
	 */
	public static function create() {
		// Create the Design Tag page.
		$design_tag_page = array(
			'post_title'   => self::$default_post_title,
			'post_name'    => self::$default_post_name,
			'post_content' => self::$default_post_content,
			'post_status'  => 'draft',
			'post_type'    => 'page',
		);
		$post_id         = wp_insert_post( $design_tag_page );
		update_post_meta( $post_id, '_thumbnail_id', 1 );

		// Store the design tag page's id in the database.
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] = $post_id;
		$updated                                   = update_option( MYSTYLE_OPTIONS_NAME, $options );

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
		if ( ! self::exists() ) {
			return;
		}

		// If upgrading from a version less than 3.14.6, update the post status
		// from 'private' to 'draft' (if necessary).
		if ( version_compare( $old_version, '3.14.6', '<' ) ) {
			$post = get_post( self::get_id() );
			if ( 'private' === $post->post_status ) {
				$post_data = array(
					'ID'          => self::get_id(),
					'post_status' => 'draft',
				);
				wp_update_post( $post_data );
			}
		}

		// If upgrading from a version less than 3.19.2, update the post title,
		// name and content.
		if ( version_compare( $old_version, '3.19.2', '<' ) ) {
			$post_data = array(
				'ID'           => self::get_id(),
				'post_title'   => self::$default_post_title,
				'post_name'    => self::$default_post_name,
				'post_content' => self::$default_post_content,
			);
			wp_update_post( $post_data );
		}
	}

	/**
	 * Alter WP_QUERY pager information based in the MyStyle_Pager class
	 *
	 * @global $wp_query
	 */
	public function set_pager() {
		global $wp_query;

		if ( isset( $wp_query->query['design_tag'] ) ) {

			if ( ! $wp_query->is_main_query() ) {
				return;
			}

			$wp_query->max_num_pages = $this->pager->get_page_count();
		}
	}

	/**
	 * Alter WP_QUERY to return designs based on URL query.
	 *
	 * @param array     $posts Current array of posts (still pre-query).
	 * @param \WP_Query $query The WP_Query being filtered.
	 * @global \wpdb $wpdb
	 * @since 3.14.0
	 */
	public function alter_query( $posts, $query ) {
		global $wpdb;

		// Just return if this isn't the query that we are looking for.
		if (
			( ! $query->is_main_query() )
			|| ( ! isset( $query->query['design_tag'] ) )
		) {
			return $posts;
		}

		$wp_user = wp_get_current_user();

		$session = MyStyle()->get_session();

		$term_taxonomy_id = $query->queried_object->term_taxonomy_id;

		// Create a new pager.
		$this->pager = new MyStyle_Pager();

		// Designs per page.
		$this->pager->set_items_per_page( MYSTYLE_DESIGNS_PER_PAGE );

		// Current page number.
		$this->pager->set_current_page_number(
			max( 1, ( isset( $query->query['paged'] ) ? $query->query['paged'] : 0 ) )
		);

		$page_limit = $this->pager->get_items_per_page();
		$page_num   = 1;

		if ( ( isset( $query->query['paged'] ) ) && ( null !== $query->query['paged'] ) ) {
			$page_num = ( $this->pager->get_current_page_number() - 1 ) * $page_limit;
		}

		$design_objs = MyStyle_Design_Tag_Manager::get_designs_by_tag_term_taxonomy_id(
			$term_taxonomy_id,
			$wp_user,
			$session,
			$page_limit,
			$page_num
		);

		$designs = array();

		foreach ( $design_objs as $design ) {
			if ( $design ) {
				try {
					$title = ( '' === $design->get_title() )
						? 'Design ' . $design->get_design_id()
						: $design->get_title();

					$product_id = $design->get_product_id();

					$product = wc_get_product( $product_id );

					$options = get_option( MYSTYLE_OPTIONS_NAME, array() );

					$design_post               = new stdClass();
					$design_post->ID           = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ];
					$design_post->design_id    = $design->get_design_id();
					$design_post->post_author  = $design->get_user_id();
					$design_post->post_name    = $title;
					$design_post->post_type    = 'Design';
					$design_post->post_title   = $title;
					$design_post->post_content = $title . ' custom ' . $product->get_name();
					$design_post->guid         = get_site_url() . '/designs/' . $design->get_design_id();

					$designs[] = $design_post;
				} catch ( MyStyle_Unauthorized_Exception $ex ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// If unauthorized, skip and go onto the next one.
				}
			}
		}

		$this->pager->set_items( $designs );

		// Total items.
		$term_count = MyStyle_Design_Tag_Manager::get_total_tag_design_count(
			$term_taxonomy_id,
			$wp_user,
			$session
		);

		$this->pager->set_total_item_count( $term_count );

		return $designs;
	}

	/**
	 * Force showing post thumbnail on design archive pages.
	 *
	 * @param bool              $has_thumbnail True if the post has a post thumbnail, otherwise false.
	 * @param int|\WP_Post|null $post          Post ID or WP_Post object. Default is global `$post`.
	 * @param int|false         $thumbnail_id  Post thumbnail ID or false if the post does not exist.
	 * @global \wp_query $wp_query
	 */
	public function has_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {
		global $wp_query;

		if ( isset( $wp_query->query['design_tag'] ) ) {
			return true;
		}

		return $has_thumbnail;
	}

	/**
	 * Load the current design's thumbnail image in The_Loop.
	 *
	 * @param array|false  $image         {
	 *     Array of image data, or boolean false if no image is available.
	 *
	 *     @type string $0 Image source URL.
	 *     @type int    $1 Image width in pixels.
	 *     @type int    $2 Image height in pixels.
	 *     @type bool   $3 Whether the image is a resized image.
	 * }
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size          Requested image size. Can be any registered image size name, or
	 *                                    an array of width and height values in pixels (in that order).
	 * @param bool         $icon          Whether the image should be treated as an icon.
	 * @global \wp_query $wp_query
	 */
	public function wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		global $wp_query;

		if ( isset( $wp_query->query['design_tag'] ) ) {
			global $post;

			$wp_user = wp_get_current_user();

			$session = MyStyle()->get_session();

			$design = MyStyle_DesignManager::get( $post->design_id, $wp_user, $session );

			if ( null !== $design ) {
				$image[0] = $design->get_web_url();
				$image[1] = 200;
				$image[2] = 200;
			}

			return $image;
		}

		return $image;
	}

	/**
	 * Load the current design's permalink in The_Loop.
	 *
	 * @param string  $permalink The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @param bool    $leavename Whether to keep the post name.
	 * @global \WP_Query $wp_query
	 */
	public function post_link( $permalink, $post, $leavename ) {
		global $wp_query;
		if ( isset( $wp_query->query['design_tag'] ) && isset( $post->design_id ) ) {
			return get_site_url() . '/designs/' . $post->design_id;
		}

		return $permalink;
	}

	/**
	 * Filter the post title.
	 *
	 * @param string $title The title of the post.
	 * @param int    $id The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
		if (
				( get_the_ID() === $id ) && // Make sure we're in the loop.
				( in_the_loop() ) // Make sure we're in the loop.
		) {
			$term = self::get_current_term( $id );

			if ( null !== $term ) {
				$title = 'Design Tag: ' . $term->name;
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
			$title['title'] = __( 'Design Tag: ', 'mystyle' ) . $term->name;
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
	 * Static function that builds a URL to the page for the tag.
	 *
	 * @param string $slug The tag slug.
	 * @return string Returns a URL that can be used to access the page for the
	 * tag.
	 */
	public static function get_tag_url( $slug ) {
		$url = site_url( 'design-tags' ) . '/' . $slug;

		return $url;
	}

	/**
	 * Attempt to fix the Design Tag page. This may involve creating,
	 * re-creating or repairing it.
	 *
	 * @return Returns a message describing the outcome of fix operation.
	 * @todo Add unit testing
	 */
	public static function fix() {
		$message = '<br/>';
		$status  = 'Design Tag page looks good, no action necessary.';
		// Get the page id of the Design Tag page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ] ) ) {
			$post_id  = $options[ MYSTYLE_DESIGN_TAG_PAGEID_NAME ];
			$message .= 'Found the stored ID of the Design Tag page...<br/>';

			/* @var $post \WP_Post phpcs:ignore */
			$post = get_post( $post_id );
			if ( null !== $post ) {
				$message .= 'Design Tag page exists...<br/>';

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
						$status   = 'Design Tag page fixed!<br/>';
					}
				} else {
					$message .= 'Design Tag page is published...<br/>';
				}

				// Check for the shortcode.
				if ( false === strpos( $post->post_content, '[mystyle_design_tags' ) ) {
					$message            .= 'The mystyle_design_tags shortcode not found in the page content, adding...<br/>';
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
						$status   = 'Design Tag page fixed!<br/>';
					}
				} else {
					$message .= 'Design Tag page has mystyle_design_tags shortcode...<br/>';
				}
			} else { // Post not found, recreate.
				$message .= 'Design Tag page appears to have been deleted, recreating...<br/>';
				try {
					$post_id = self::create();
					$status  = 'Design Tag page fixed!<br/>';
				} catch ( \Exception $e ) {
					$status = 'Error: ' . $e->getMessage();
				}
			}
		} else { // ID not available, create.
			$message .= 'Design Tag page missing, creating...<br/>';
			self::create();
			$status = 'Design Tag page fixed!<br/>';
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
		if ( isset( $wp_query->query['tag_term'] ) ) {
			$term_slug = $wp_query->query['tag_term'];
			if ( preg_match( '/\//', $term_slug ) ) {
				$url_array = explode( '/', $term_slug );
				if ( 'page' === $url_array[0] ) {
					$term_slug = null;
				} else {
					$term_slug = $url_array[0];
				}
			}

			if ( null !== $term_slug ) {
				$current_term = get_term_by( 'slug', $term_slug, MYSTYLE_TAXONOMY_NAME );
			}
		}

		return $current_term;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Design_Tag_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
