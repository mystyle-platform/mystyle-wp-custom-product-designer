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

		if ( ! isset( $_GET['product_id'] ) ) {
			$out = '';
			add_filter( 'woocommerce_shortcode_products_query', array( 'MyStyle_Customizer_Shortcode', 'modify_woocommerce_shortcode_products_query' ), 10, 1 );
			$out = do_shortcode( '[products per_page="12" limit="12" paginate="true"]' );
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
		$product_id          = htmlspecialchars( $_GET['product_id'] );
		$design_id           = ( isset( $_GET['design_id'] ) ) ? htmlspecialchars( $_GET['design_id'] ) : null; // Reload design ID from URL.
		$default_design_id   = get_post_meta( $product_id, '_mystyle_design_id', true );
		$mystyle_template_id = get_post_meta( $product_id, '_mystyle_template_id', true );
		$customizer_ux       = get_post_meta( $product_id, '_mystyle_customizer_ux', true );
		$print_type          = get_post_meta( $product_id, '_mystyle_print_type', true );
		$passthru            = ( isset( $_GET['h'] ) ) ? $_GET['h'] : null;

		// If no passthru (h) data was received in the GET vars, build some
		// defaults to keep things working.
		if ( null === $passthru ) {
			$passthru_arr                        = array();
			$passthru_arr['post']                = array();
			$passthru_arr['post']['quantity']    = 1;
			$passthru_arr['post']['add-to-cart'] = (int) $product_id;
			$passthru                            = base64_encode( wp_json_encode( $passthru_arr ) );
		}

		// Product Settings - Default Design ID.
		// If no reload design id from url, use default design ID if there is one.
		if ( ( null === $design_id ) && ( ( null !== $default_design_id ) && ( $default_design_id > 0 ) ) ) {
			$design_id = $default_design_id;
		}

		// Get any settings that were passed in via the url.
		$settings_param = ( isset( $_GET['settings'] ) ) ? $_GET['settings'] : null;

		if ( ! empty( $settings_param ) ) {
			$settings = json_decode( base64_decode( $settings_param ), true );
		} else {
			$settings = array();
		}

		// Set the redirect_url (if it wasn't passed in).
		if ( ! array_key_exists( 'redirect_url', $settings ) ) {
			$settings['redirect_url'] = MyStyle_Handoff::get_url();
		} else {
			// An array key was passed in, validate it.
			if ( ! MyStyle_Options::is_redirect_url_permitted( $settings['redirect_url'] ) ) {
				throw new MyStyle_Bad_Request_Exception( 'The passed redirect url is not allowed. If you are the site admin, please add the domain to your MyStyle Redirect URL Whitelist.' );
			}
		}

		// Set the email_skip ( if it wasn't passed in ).
		if ( ! array_key_exists( 'email_skip', $settings ) ) {
			$settings['email_skip'] = 0;
		}

		// Set the print_type (if it wasn't passed in).
		if ( ! array_key_exists( 'print_type', $settings ) ) {
			$settings['print_type'] = $print_type;
		}

		// Skip enter email step if logged in and email can be pulled from user acct.
		if ( is_user_logged_in() ) {
			$settings['email_skip'] = 1;
		}

		// Base64 encode settings.
		$encoded_settings = base64_encode( wp_json_encode( $settings ) );

		// echo '<pre>' ; var_dump(json_decode(base64_decode($passthru))) ; echo '</pre>' ;
		// Add all vars to URL.
		$customizer_query_string = "?app_id=$mystyle_app_id" .
				"&amp;product_id=$mystyle_template_id" .
				( ( ! empty( $customizer_ux ) ) ? "&amp;ux=$customizer_ux" : '' ) .
				( ( null !== $design_id ) ? "&amp;design_id=$design_id" : '' ) .
				"&amp;settings=$encoded_settings" .
				"&amp;passthru=h,$passthru";

		// ---------- Variables for use by the view layer ---------
		$flash_customizer_url = 'http://customizer.ogmystyle.com/' . $customizer_query_string;

		// set the customizer to dev if parameter isset
		if ( isset( $_GET['customizerdev'] ) ) {
			$html5_customizer_url = 'http://sean.base.customizer-js.api.dev.ogmystyle.com/' . $customizer_query_string;
		} else {
			$html5_customizer_url = '//customizer-js.ogmystyle.com/' . $customizer_query_string;
		}

		// Force mobile from plugin admin settings?
		$enable_flash = MyStyle_Options::enable_flash();

		// Force mobile from GET var override?
		if ( isset( $_GET['enable_flash'] ) ) {
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
