<?php
/**
 * Class for working with the MyStyle Customize page.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_Customize_Page class.
 */
class MyStyle_Customize_Page {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Customize_Page
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'the_title', array( &$this, 'filter_title' ), 10, 2 );
		add_filter( 'body_class', array( &$this, 'filter_body_class' ), 10, 1 );

		// Set the priority to 11 ( instead of the default 10 ) so that our scripts load after jQuery.
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 11, 0 );
	}

	/**
	 * Function to create the page.
	 *
	 * @return number Returns the page id of the Customize page.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if the function is
	 * unable to store the id of the page.
	 */
	public static function create() {
		// Create the Customize page.
		$customize_page = array(
			'post_title'   => 'Customize',
			'post_content' => '[mystyle_customizer]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		$page_id        = wp_insert_post( $customize_page );

		// Store the customize page's id in the database.
		$options                                  = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] = $page_id;
		$updated                                  = update_option( MYSTYLE_OPTIONS_NAME, $options );

		if ( ! $updated ) {
			wp_delete_post( $page_id );
			throw new MyStyle_Exception( __( 'Could not store page id.', 'mystyle' ), 500 );
		}

		return $page_id;
	}

	/**
	 * Function to get the id of the customize page.
	 *
	 * @return number Returns the page id of the Customize page.
	 * @throws MyStyle_Exception Throws a MyStyle_Exception if the customize
	 * page is missing.
	 */
	public static function get_id() {
		// Get the page id of the Customize page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( ! isset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] ) ) {
			throw new MyStyle_Exception( __( 'Customize Page is Missing! Please use the "Fix Customize Page Tool" on the MyStyle Settings page to fix.', 'mystyle' ), 404 );
		}
		$page_id = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];

		return $page_id;
	}

	/**
	 * Function to determine if the page exists.
	 *
	 * @return boolean Returns true if the page exists, otherwise false.
	 */
	public static function exists() {
		$exists = false;

		// Get the page id of the Customize page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] ) ) {
			$exists = true;
		}

		return $exists;
	}

	/**
	 * Function to delete the Customize page.
	 */
	public static function delete() {
		// Get the page id of the Customize page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$page_id = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];

		// Remove the page id from the datababase.
		unset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] );
		update_option( MYSTYLE_OPTIONS_NAME, $options );

		// Delete the page from WordPress.
		wp_delete_post( $page_id );
	}

	/**
	 * Builds a URL to the customize page including url paramaters to load
	 * the passed design.
	 *
	 * @param MyStyle_Design $design The design that you want a url for.
	 * @param integer|null   $cart_item_key An optional cart_item_key.
	 * @param array|null     $passthru Any passthru data to include in the url.
	 * If none is passed, defaults are used.
	 * @param integer|null   $product_id An optional product id.
	 * @return string Returns a link that can be used to reload a design.
	 * @todo Combine this code with the get_scratch_url and get_product_url
	 * functions.
	 */
	public static function get_design_url(
		MyStyle_Design $design,
		$cart_item_key = null,
		$passthru = null,
		$product_id = null
		) {

		if ( null === $passthru ) {
			if ( null !== $design->get_cart_data() ) {
				$post_data = json_decode( $design->get_cart_data(), true );
                $post_data['add-to-cart'] = ( ( null === $product_id ) ? $design->get_product_id() : $product_id ) ;
			} else {
				// Set some default post/cart data.
				$post_data = array(
					'quantity'    => 1,
					'add-to-cart' => ( ( null === $product_id ) ? $design->get_product_id() : $product_id ),
				);
			}

			$passthru = array(
				'post' => $post_data,
			);

			// Add custom template data if enabled.
			$mystyle_custom_template_enabled = get_post_meta( $design->get_product_id(), '_mystyle_custom_template', true );

			if ( 'yes' === $mystyle_custom_template_enabled ) {
                $custom_product_id = ( ( null === $product_id ) ? $design->get_product_id() : $product_id ) ;
                
				$passthru['width']  = get_post_meta( $custom_product_id, '_mystyle_custom_template_width', true );
				$passthru['height'] = get_post_meta( $custom_product_id, '_mystyle_custom_template_height', true );
				$passthru['shape']  = get_post_meta( $custom_product_id, '_mystyle_custom_template_shape', true );

				if ( get_post_meta( $custom_product_id, '_mystyle_custom_template_color', true ) ) {
					$passthru['color'] = get_post_meta( $custom_product_id, '_mystyle_custom_template_color', true );
				}

				if ( get_post_meta( $custom_product_id, '_mystyle_custom_template_default_text_color', true ) ) {
					$passthru['textColorDefault'] = get_post_meta( $custom_product_id, '_mystyle_custom_template_default_text_color', true );
				}

				if ( get_post_meta( $custom_product_id, '_mystyle_custom_template_bgimg', true ) ) {
					$passthru['tbgimg'] = get_post_meta( $custom_product_id, '_mystyle_custom_template_bgimg', true );
				}

				if ( get_post_meta( $custom_product_id, '_mystyle_custom_template_fgimg', true ) ) {
					$passthru['tfgimg'] = get_post_meta( $custom_product_id, '_mystyle_custom_template_fgimg', true );
				}

				if ( get_post_meta( $custom_product_id, '_mystyle_custom_template_bleed', true ) ) {
					$passthru['bleed'] = get_post_meta( $custom_product_id, '_mystyle_custom_template_bleed', true );
				}

				if ( get_post_meta( $custom_product_id, '_mystyle_custom_template_boxshadow', true ) === 'yes' ) {
					$passthru['boxshadow'] = 1;
				}
                
                if ( get_post_meta( $custom_product_id, '_mystyle_3d_view_enabled', true ) === 'yes' ) {
                    $passthru['view_3d'] = 1;
                }
                
                if ( get_post_meta( $custom_product_id, '_mystyle_3d_depth', true ) ) {
                    $passthru['view_3d'] = get_post_meta( $custom_product_id, '_mystyle_3d_depth', true ) ;
                }
			}
		}

		if ( null !== $cart_item_key ) {
			$passthru['cart_item_key'] = $cart_item_key;
		}

		$passthru_encoded = base64_encode( wp_json_encode( $passthru ) );
		$customize_args   = array(
			'product_id' => ( ( null === $product_id ) ? $design->get_product_id() : $product_id ),
			'design_id'  => $design->get_design_id(),
			'h'          => $passthru_encoded,
		);
		$customizer_url = add_query_arg( $customize_args, get_permalink( self::get_id() ) );

		return $customizer_url;
	}

	/**
	 * Build the scratch url to the customizer for the design.
	 *
	 * The "scratch" url is a url that loads the customizer with the product
	 * used in the design but without the design (allowing you to create a new
	 * design for the product "from scratch").
	 *
	 * This works the same as the get_reload_url function above except that it
	 * leaves off the 'design_id' URL query arg.
	 *
	 * @param MyStyle_Design $design The design that you want a url for.
	 * @param integer        $cart_item_key An optional cart_item_key.
	 * @param array          $passthru Any passthru data to include in the url.
	 * If none is passed, defaults are used.
	 * @returns string Returns the "scratch" url to the customizer for the
	 * design.
	 * @todo Combine this code with the get_design_url and get_product_url
	 * functions.
	 */
	public static function get_scratch_url(
		MyStyle_Design $design,
		$cart_item_key = null,
		$passthru = null ) {

		if ( null === $passthru ) {

			if ( null !== $design->get_cart_data() ) {
				$post_data = json_decode( $design->get_cart_data(), true );
			} else {
				// Set some default post/cart data.
				$post_data = array(
					'quantity'    => 1,
					'add-to-cart' => $design->get_product_id(),
				);
			}

			$passthru = array(
				'post' => $post_data,
			);

			// Add custom template data if enabled.
			$mystyle_custom_template_enabled = get_post_meta( $design->get_product_id(), '_mystyle_custom_template', true );

			if ( 'yes' === $mystyle_custom_template_enabled ) {
				$passthru['width']  = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_width', true );
				$passthru['height'] = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_height', true );
				$passthru['shape']  = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_shape', true );

				if ( get_post_meta( $design->get_product_id(), '_mystyle_custom_template_color', true ) ) {
					$passthru['color'] = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_color', true );
				}

				if ( get_post_meta( $design->get_product_id(), '_mystyle_custom_template_default_text_color', true ) ) {
					$passthru['textColorDefault'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_default_text_color', true );
				}

				if ( get_post_meta( $design->get_product_id(), '_mystyle_custom_template_bgimg', true ) ) {
					$passthru['tbgimg'] = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_bgimg', true );
				}

				if ( get_post_meta( $design->get_product_id(), '_mystyle_custom_template_fgimg', true ) ) {
					$passthru['tfgimg'] = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_fgimg', true );
				}

				if ( get_post_meta( $design->get_product_id(), '_mystyle_custom_template_bleed', true ) ) {
					$passthru['bleed'] = get_post_meta( $design->get_product_id(), '_mystyle_custom_template_bleed', true );
				}

				if ( get_post_meta( $design->get_product_id(), '_mystyle_custom_template_boxshadow', true ) === 'yes' ) {
					$passthru['boxshadow'] = 1;
				}
                
                if ( get_post_meta( $design->get_product_id(), '_mystyle_3d_view_enabled', true ) === 'yes' ) {
                    $passthru['view_3d'] = 1;
                }
                
                if ( get_post_meta( $design->get_product_id(), '_mystyle_3d_depth', true ) ) {
                    $passthru['view_3d'] = get_post_meta( $design->get_product_id(), '_mystyle_3d_depth', true ) ;
                }
			}
		}

		if ( null !== $cart_item_key ) {
			$passthru['cart_item_key'] = $cart_item_key;
		}

		$passthru_encoded = base64_encode( wp_json_encode( $passthru ) );
		$customize_args   = array(
			'product_id' => $design->get_product_id(),
			'h'          => $passthru_encoded,
		);

		$customizer_url = add_query_arg( $customize_args, get_permalink( self::get_id() ) );

		return $customizer_url;
	}

	/**
	 * Builds a url to the customize page including url paramaters to load
	 * the passed product.
	 *
	 * @param MyStyle_Product $product The design that you want a url for.
	 * @param integer         $cart_item_key An optional cart_item_key.
	 * @param array           $passthru Any passthru data to include in the url.
	 * If none is passed, defaults are used.
	 * @return string Returns a link that can be used to reload a design.
	 * @todo Unit test this function.
	 * @todo Combine this code with the get_design_url and get_scratch_url
	 * functions.
	 */
	public static function get_product_url(
		MyStyle_Product $product,
		$cart_item_key = null,
		$passthru = null ) {

		if ( null === $passthru ) {
			$passthru = array(
				'post' => array(
					'quantity'    => 1,
					'add-to-cart' => $product->get_id(),
				),
			);
		}

		if ( null !== $cart_item_key ) {
			$passthru['cart_item_key'] = $cart_item_key;
		}

		$passthru_encoded = base64_encode( wp_json_encode( $passthru ) );
		$customize_args   = array(
			'product_id' => $product->get_id(),
			'h'          => $passthru_encoded,
		);

		$customizer_url = add_query_arg( $customize_args, get_permalink( self::get_id() ) );

		return $customizer_url;
	}

	/**
	 * Filter the post title. Hide the title if on the Customize page and the
	 * customize_page_title_hide setting is set to true.
	 *
	 * @param string $title The title of the post.
	 * @param type   $id The id of the post.
	 * @return string Returns the filtered title.
	 */
	public function filter_title( $title, $id = null ) {
		try {
			if (
					( ! empty( $id ) ) &&
					( $this->is_customize_page( $id ) ) &&
					( self::hide_title() ) &&
					( get_the_ID() === $id ) &&
					( in_the_loop() )
			) {
				$title = '';
			}
		} catch ( MyStyle_Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// This exception may be thrown if the Customize Page is missing.
			// For this function, that is okay, just continue.
		}

		return $title;
	}

	/**
	 * Filter the body class output. Adds a "mystyle-customize" class if the
	 * page is the Customize page.
	 *
	 * @param array $classes An array of classes that are going to be outputed
	 * to the body tag.
	 * @return array Returns the filtered classes array.
	 */
	public function filter_body_class( $classes ) {
		global $post;

		try {
			if ( null !== $post ) {
				if (
						( $this->is_customize_page( $post->ID ) )
						&& ( isset( $_GET['product_id'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
				) {
					$classes[] = 'mystyle-customize';
				}
			}
		} catch ( MyStyle_Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// This exception may be thrown if the Customize Page is missing.
			// For this function, that is okay, just continue.
		}

		return $classes;
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		global $post;

		try {
			if ( null !== $post ) {
				if (
						( $this->is_customize_page( $post->ID ) )
						&& ( isset( $_GET['product_id'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
				) {
					wp_register_script( 'mystyle-customize', 'https://static2.ogmystyle.com/mystyle-customize/1.0.1/customize.min.js' );
					wp_enqueue_script( 'mystyle-customize' );
				}
			}
		} catch ( MyStyle_Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// This exception may be thrown if the Customize Page is missing.
			// For this function, that is okay, just continue.
		}
	}

	/**
	 * Function that gets the value of the customize_page_title_hide setting.
	 *
	 * @return boolean Returns true if the customize_page_title_hide setting is
	 * enabled, otherwise returns false.
	 */
	public static function hide_title() {
		return MyStyle_Options::is_option_enabled(
			MYSTYLE_OPTIONS_NAME,
			'customize_page_title_hide'
		);
	}

	/**
	 * Function that gets the value of the
	 * customize_page_disable_viewport_rewrite setting.
	 *
	 * @return boolean Returns true if the customize_page_title_hide setting is
	 * enabled, otherwise returns false.
	 */
	public static function disable_viewport_rewrite() {
		return MyStyle_Options::is_option_enabled(
			MYSTYLE_OPTIONS_NAME,
			'customize_page_disable_viewport_rewrite'
		);
	}

	/**
	 * Attempt to fix the Customize page. This may involve creating, re-creating
	 * or repairing it.
	 *
	 * @return Returns a message describing the outcome of fix operation.
	 * @todo: Add unit testing
	 */
	public static function fix() {
		$message = '<br/>';
		$status  = 'Customize page looks good, no action necessary.';
		// Get the page id of the Customize page.
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		if ( isset( $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ] ) ) {
			$post_id  = $options[ MYSTYLE_CUSTOMIZE_PAGEID_NAME ];
			$message .= 'Found the stored ID of the Customize page...<br/>';

			/* @var $post \WP_Post phpcs:ignore */
			$post = get_post( $post_id );
			if ( null !== $post ) {
				$message .= 'Customize page exists...<br/>';

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
						$status   = 'Customize page fixed!<br/>';
					}
				} else {
					$message .= 'Customize page is published...<br/>';
				}

				// Check for the shortcode.
				if ( false === strpos( $post->post_content, '[mystyle_customizer]' ) ) {
					$message            .= 'The mystyle_customizer shortcode not found in the page content, adding...<br/>';
					$post->post_content .= '[mystyle_customizer]';

					/* @var $error \WP_Error phpcs:ignore */
					$errors = wp_update_post( $post, true );

					if ( is_wp_error( $errors ) ) {
						foreach ( $errors as $error ) {
							$messages .= $error . '<br/>';
							$status   .= 'Fix errored out :(<br/>';
						}
					} else {
						$message .= 'Shortcode added.<br/>';
						$status   = 'Customize page fixed!<br/>';
					}
				} else {
					$message .= 'Customize page has mystyle_customizer shortcode...<br/>';
				}
			} else { // Post not found, recreate.
				$message .= 'Customize page appears to have been deleted, recreating...<br/>';
				try {
					$post_id = self::create();
					$status  = 'Customize page fixed!<br/>';
				} catch ( \Exception $e ) {
					$status = 'Error: ' . $e->getMessage();
				}
			}
		} else { // ID not available, create.
			$message .= 'Customize page missing, creating...<br/>';
			self::create();
			$status = 'Customize page fixed!<br/>';
		}

		$message .= $status;

		return $message;
	}

	/**
	 * Function that tests to see if the passed id is the id of the Customize
	 * page OR the id of a translation of the Customize page.
	 *
	 * @param int $id The post id.
	 * @return boolean Returns the filtered title.
	 * @todo Add unit testing.
	 */
	public function is_customize_page( $id ) {
		$is_customize_page = false;

		if (
			( self::get_id() === $id ) ||
			( MyStyle_Wpml::get_instance()->is_translation_of_page( self::get_id(), $id ) )
		) {
			$is_customize_page = true;
		}

		return $is_customize_page;
	}

	/**
	 * Resets the singleton instance. This is used during testing if we want to
	 * clear out the existing singleton instance.
	 *
	 * @return MyStyle_Customize_Page Returns the singleton instance of this
	 * class.
	 */
	public static function reset_instance() {

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Customize_Page Returns the singleton instance of this
	 * class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
