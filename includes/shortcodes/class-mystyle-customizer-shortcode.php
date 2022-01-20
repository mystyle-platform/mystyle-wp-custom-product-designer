<?php
/**
 * Class for the MyStyle Customizer Shortcode.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_Customizer_Shortcode class.
 */
abstract class MyStyle_Customizer_Shortcode {

	/**
	 * Modify the WooCommerce products shortcode query to only include MyStyle
	 * enabled products.
	 *
	 * @param array $args An array of query arguments.
	 * @return array Returns the array of query arguments.
	 */
	public static function modify_woocommerce_shortcode_products_query( $args ) {
		$mystyle_filter            = array();
		$mystyle_filter['key']     = '_mystyle_enabled';
		$mystyle_filter['value']   = 'yes';
		$mystyle_filter['compare'] = 'IN';

		$args['meta_query'][] = $mystyle_filter;

		return $args;
	}

	/**
	 * Output the customizer shortcode.
	 *
	 * @throws MyStyle_Bad_Request_Exception Throws a MyStyle_Bad_Request_Exception
	 * if the passed redirect url isn't allowed.
	 */
	public static function output() {

		$mystyle_app_id = MyStyle_Options::get_api_key();

		if ( ! isset( $_GET['product_id'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
            global $shortname;
            
            $per_page = 12 ;
            
            if( isset( $shortname ) && function_exists('et_get_option')) {
                $per_page = et_get_option( $shortname . '_woocommerce_archive_num_posts', '5' ) ;
            }
            else {
                $columns = get_option( 'woocommerce_catalog_columns', 4 ) ;
                
                if( $columns ) {
                    $rows = get_option( 'woocommerce_catalog_rows', 4 ) ;
                    $per_page = $columns * $rows ;
                }
                
            }
            
			$out = '';
            
			add_filter( 'woocommerce_shortcode_products_query', array( 'MyStyle_Customizer_Shortcode', 'modify_woocommerce_shortcode_products_query' ), 10, 1 );
			$out = do_shortcode( '[products per_page="' . $per_page . '" limit="' . $per_page . '" paginate="true"]' );
			remove_filter( 'woocommerce_shortcode_products_query', array( 'MyStyle_Customizer_Shortcode', 'modify_woocommerce_shortcode_products_query' ) );

			if ( strlen( $out ) < 50 ) {
				$out  = '<p>Sorry, no products are currently available for customization.</p>';
				$out .= '<h2><a class="button" href="' . get_permalink( MyStyle_WC()->wc_get_page_id( 'shop' ) ) . '">Shop</a></h2>';
				if ( is_super_admin() ) {
					$out .= '<div style="background-color: #fafafa; border: solid 1px #eeeeee; font-family: \'Noto Sans\', sans-serif; padding: 10px;">' .
							'<p><strong>MyStyle Admin Notice:</strong></p>' .
							'<p>You need to make at least one product customizable by enabling it in the MyStyle tab of the WooCommerce product admin.</p>' .
							'<p>Please note that this message will not show to customers.</p>' .
							'</div>';
				}
			} else {
				$out = '<h2>Select a product to customize</h2>' . $out;
			}

			return $out;
		}

		// Get data.
		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$product_id = intval( wp_unslash( $_GET['product_id'] ) );
		$design_id  = ( isset( $_GET['design_id'] ) ) ? intval( $_GET['design_id'] ) : null; // Reload design ID from URL.
		$passthru   = ( isset( $_GET['h'] ) ) ? sanitize_text_field( wp_unslash( $_GET['h'] ) ) : null;
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$default_design_id   = get_post_meta( $product_id, '_mystyle_design_id', true );
		$mystyle_template_id = get_post_meta( $product_id, '_mystyle_template_id', true );
		$customizer_ux       = get_post_meta( $product_id, '_mystyle_customizer_ux', true );
		$print_type          = get_post_meta( $product_id, '_mystyle_print_type', true );

		// If no passthru (h) data was received in the GET vars, build some
		// defaults to keep things working.
		if ( null === $passthru ) {
			$passthru_arr                        = array();
			$passthru_arr['post']                = array();
			$passthru_arr['post']['quantity']    = 1;
			$passthru_arr['post']['add-to-cart'] = (int) $product_id;
            $passthru_arr['user']['token']       = MyStyle_Util::encrypt_decrypt( 'encrypt', get_current_user_id() ) ;
			$passthru                            = base64_encode( wp_json_encode( $passthru_arr ) );
		}

		// Product Settings - Default Design ID.
		// If no reload design id from url, use default design ID if there is one.
		if ( ( null === $design_id ) && ( ( null !== $default_design_id ) && ( $default_design_id > 0 ) ) ) {
			$design_id = $default_design_id;
		}

		// Get any settings that were passed in via the url.
		// phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$settings_param = ( isset( $_GET['settings'] ) ) ? sanitize_text_field( wp_unslash( $_GET['settings'] ) ) : null;

		if ( ! empty( $settings_param ) ) {
			$settings = json_decode( base64_decode( $settings_param ), true );
		} else {
			$settings = array();
		}

		// Set the redirect_url (if it wasn't passed in).
		if ( array_key_exists( 'redirect_url', $settings ) ) {
			// An array key was passed in, validate it.
			$redirect_url = $settings['redirect_url'];
			if ( ! MyStyle_Options::is_redirect_url_permitted( $redirect_url ) ) {
				throw new MyStyle_Bad_Request_Exception( 'The passed redirect url is not allowed. If you are the site admin, please add the domain to your MyStyle Redirect URL Whitelist.' );
			}
		} else {
			$redirect_url = MyStyle_Handoff::get_url();
		}

		// Set skip_email.
		$skip_email = false;
		if ( array_key_exists( 'email_skip', $settings ) ) {
			$skip_email = true;
		}
		if ( is_user_logged_in() ) {
			$skip_email = true;
		}

		// Force mobile from plugin admin settings?
		$enable_flash = MyStyle_Options::enable_flash();

		// Force flash from GET var override?
		if ( isset( $_GET['enable_flash'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$enable_flash = true;
		}

		$disable_viewport_rewrite = MyStyle_Customize_Page::disable_viewport_rewrite();

		// ---------- Call the view layer ------- //
		ob_start();
		require MYSTYLE_TEMPLATES . 'customizer.php';
		$out = ob_get_contents();
		ob_end_clean();

		if ( ( defined( 'MYSTYLE_ENABLE_MOCK_SUBMIT_BUTTON' ) ) && ( true === MYSTYLE_ENABLE_MOCK_SUBMIT_BUTTON ) ) {
			// Add the mock form (for testing).
			$out .= '<form action="/wordpress/?mystyle-handoff" method="POST">' .
					'<input type="hidden" name="description" value="Fulfillment Instructions...">' .
					'<input type="hidden" name="design_id" value="78580">' .
					'<input type="hidden" name="product_id" value="' . $mystyle_app_id . '">' .
					'<input type="hidden" name="local_product_id" value="' . $product_id . '">' .
					'<input type="hidden" name="user_id" value="29057">' .
					'<input type="hidden" name="price" value="$0.01">' .
					'<input type="submit" value="Submit Mock">' .
					'</form>';
		}

		return $out;
	}

}
