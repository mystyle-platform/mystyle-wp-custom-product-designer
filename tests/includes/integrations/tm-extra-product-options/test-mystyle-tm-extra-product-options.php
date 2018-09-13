<?php

/**
 * The MyStyleTmExtraProductOptionsTest class includes tests for testing the
 * MyStyle_Tm_Extra_Product_Options class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleTmExtraProductOptionsTest extends WP_UnitTestCase {

    /**
     * Test the constructor
     * @global wp_filter
     */
    public function test_constructor() {
        global $wp_filter;

        MyStyle_Tm_Extra_Product_Options::get_instance();

        //Assert that the filter_title function is registered.
        $function_names = get_function_names( $wp_filter['mystyle_customizer_passthru_array'] );
        $this->assertContains( 'filter_mystyle_customizer_passthru_array', $function_names );
    }

    /**
     * Test the filter_mystyle_customizer_passthru_array function when the post
     * has the expected data.
     * @global $post;
     */
    public function test_filter_mystyle_customizer_passthru_array_with_expected_post_data() {
        global $post;

        // Set up some test vars.
        $img_url = 'http://www.example.com/image.jpg';

        // Mock/Stub the tm_meta data (based on v4.6.9.3).
        $tm_meta = array(
            'tmfbuilder' => array(
                'multiple_radiobuttons_options_image' => array(
                    0 => array(
                        $img_url
                    )
                )
            )
        );

        // Mock the product.
        $product = WC_Helper_Product::create_simple_product();
        add_post_meta($product->get_id(), 'tm_meta', $tm_meta);
        $GLOBALS['post'] = $product;

        // Mock some passthru data.
        $passthru_arr = array();
        $passthru_arr['post'] = array();
        $passthru_arr['post']['quantity'] = 1;
        $passthru_arr['post']['add-to-cart'] = (int) $product->get_id();

        // Add a TM Extra Produc Options field.
        $passthru_arr['post']['tmcp_radio_0'] = 'Choice_0';

        // Create the System Under Test class.
        $sut = new MyStyle_Tm_Extra_Product_Options();

        // Call the function.
        $new_passthru_arr = $sut->filter_mystyle_customizer_passthru_array(
                                        $passthru_arr,
                                        $product->get_id()
                                    );

        //var_dump($new_passthru_arr);

        // Assert that the filter added the background_image_url as expected.
        $this->assertEquals(
                    $img_url,
                    $new_passthru_arr['background_image_url']
                );
    }

    /**
     * Test the filter_mystyle_customizer_passthru_array function when the post
     * doesn't have the expected data. It should just return without altering
     * the passthru data.
     */
    public function test_filter_mystyle_customizer_passthru_array_without_expected_post_data() {

        // Mock the product.
        $product = WC_Helper_Product::create_simple_product();
        $GLOBALS['post'] = $product;

        // Mock some passthru data.
        $passthru_arr = array();
        $passthru_arr['post'] = array();
        $passthru_arr['post']['quantity'] = 1;
        $passthru_arr['post']['add-to-cart'] = (int) $product->get_id();

        // Create the System Under Test class.
        $sut = new MyStyle_Tm_Extra_Product_Options();

        // Call the function
        $new_passthru_arr = $sut->filter_mystyle_customizer_passthru_array(
                                        $passthru_arr,
                                        $product->get_id()
                                    );

        // Assert that the filter left the passthru_arr untouched.
        $this->assertEquals($passthru_arr, $new_passthru_arr);
    }
}
