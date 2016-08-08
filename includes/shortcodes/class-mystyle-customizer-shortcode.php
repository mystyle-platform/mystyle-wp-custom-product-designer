<?php

/**
 * Class for the MyStyle Customizer Shortcode.
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_Customizer_Shortcode {


    /**
     * Modify the WooCommerce products shortcode query to only include MyStyle
     * enabled products.
     * @param array $args An array of query arguments
     * @return array Returns the array of query arguments
     */
    public static function modify_woocommerce_shortcode_products_query( $args ) {
        $mystyle_filter = array();
        $mystyle_filter['key'] = '_mystyle_enabled';
        $mystyle_filter['value'] = 'yes';
        $mystyle_filter['compare'] = 'IN';

        $args['meta_query'][] = $mystyle_filter;

        return $args;
    }

    /**
     * Output the customizer shortcode.
     */
    public static function output() {

        $mystyle_app_id = MyStyle_Options::get_api_key();

        if( ! isset( $_GET['product_id'] ) ) {
            $out = '';
            add_filter( 'woocommerce_shortcode_products_query', array( 'MyStyle_Customizer_Shortcode', 'modify_woocommerce_shortcode_products_query' ), 10, 1 );
            $out = do_shortcode('[products per_page="12"]');

            if( strlen( $out ) < 50 ) {
                $out = '<p>Sorry, no products are currently available for customization.</p>';
                $out .= '<h2><a class="button" href="' . get_permalink( woocommerce_get_page_id( 'shop' ) ) . '">Shop</a></h2>';
                if( is_super_admin() ) {
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

        // get data
        $product_id          = htmlspecialchars( $_GET['product_id'] ) ;
        $design_id           = (isset($_GET['design_id'])) ? htmlspecialchars( $_GET['design_id'] ) : null; // reload design ID from URL
        $default_design_id   = get_post_meta( $product_id, '_mystyle_design_id', true );
        $mystyle_template_id = get_post_meta( $product_id, '_mystyle_template_id', true );
        $customizer_ux       = get_post_meta( $product_id, '_mystyle_customizer_ux', true );
        $print_type          = get_post_meta( $product_id, '_mystyle_print_type', true );
        $passthru            = ( isset( $_GET['h'] ) ) ? $_GET['h'] : '';

        // Product Settings - Default Design ID
        // if no reload design id from url, use default design ID if there is one
        if( ( $design_id == null ) && ( $default_design_id != null && $default_design_id > 0 ) ){
            $design_id = $default_design_id;
        }

        // package up data in settings
        $settings = array();
        $settings['redirect_url'] = MyStyle_Handoff::get_url();
        $settings['email_skip'] = 0;
        $settings['print_type'] = $print_type;

        //TODO: skip enter email step if logged in and email can be pulled from user acct
        //if ($CURRENT_USER) {
            //$settings['email_skip'] = 1;
        //}

        // base64 encode settings
        $encoded_settings = base64_encode( json_encode( $settings ) );

        // add all vars to URL
        $customizer_query_string =
                        "?app_id=$mystyle_app_id" .
                        "&amp;product_id=$mystyle_template_id" .
                        ( ( ! empty( $customizer_ux ) ) ? "&amp;ux=$customizer_ux" : '' ) .
                        ( ( $design_id != null ) ? "&amp;design_id=$design_id" : '' ) .
                        "&amp;settings=$encoded_settings" .
                        "&amp;passthru=h,$passthru";

        //---------- variables for use by the view layer ---------
        $customizer_url = 'http://customizer.ogmystyle.com/' . $customizer_query_string;
        $mobile_customizer_url = 'http://customizer-js.ogmystyle.com/' . $customizer_query_string;

        // force mobile from plugin admin settings?
        $force_mobile = MyStyle_Options::get_force_mobile();

        // force mobile from GET var override?
        if ( isset( $_GET['force_mobile'] ) ) {
            $force_mobile = 1;
        }

        // ---------- Call the view layer ------- //
        ob_start();
        require( MYSTYLE_TEMPLATES . 'customizer.php' );
        $out = ob_get_contents();
        ob_end_clean();
        // -------------------------------------- //

        if( ( defined( 'MYSTYLE_ENABLE_MOCK_SUBMIT_BUTTON' ) ) && ( MYSTYLE_ENABLE_MOCK_SUBMIT_BUTTON == true ) ) {
            //Add the mock form (for testing)
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