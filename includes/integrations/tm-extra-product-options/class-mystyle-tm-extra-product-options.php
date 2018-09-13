<?php

/**
 * Class for integrating with the TM Extra Product Options plugin.
 * @package MyStyle
 * @since 3.6.0
 */
class MyStyle_Tm_Extra_Product_Options {

    /**
     * Singleton class instance
     * @var MyStyle_Tm_Extra_Product_Options
     */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'mystyle_customizer_passthru_array', array( &$this, 'filter_mystyle_customizer_passthru_array' ), 10, 3 );
    }

    /**
     * Filter the mystyle passthru data to add options from the TM Extra Product
     * Options plugin
     * @param array $passthru_arr The title of the post.
     * @param integer $product_id The id of the product/post.
     * @param integer|null $design_id The id of the design.
     * @return string Returns the filtered title.
     */
    public function filter_mystyle_customizer_passthru_array( $passthru_arr, $product_id, $design_id = null) {

        // See if we can find the background image. searches the passthru data
        // for a field called 'tmcp_radio_0' (this will be the name of the
        // first TM Extra Product Options radio button on the form). It then
        // attempts to pull the image for the selected option from the TM Extra
        // Product Options product meta data.
        try {
            $selectedOption = $passthru_arr['post']['tmcp_radio_0'];
            $selectedOptionIdx = substr($selectedOption, strrpos($selectedOption, '_') + 1);

            $tm_meta = get_post_meta( $product_id, 'tm_meta', true );

            $background_image_url = $tm_meta['tmfbuilder']['multiple_radiobuttons_options_image'][0][$selectedOptionIdx];
            $passthru_arr['background_image_url'] = $background_image_url;
        } catch (\Exception $ex) {
            //
        }

        return $passthru_arr;
    }

    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Tm_Extra_Product_Options Returns the singleton instance
     * of this class.
     */
    public static function reset_instance() {

        self::$instance = new self();

        return self::$instance;
    }


    /**
     * Gets the singleton instance.
     * @return MyStyle_Tm_Extra_Product_Options Returns the singleton instance
     * of this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}