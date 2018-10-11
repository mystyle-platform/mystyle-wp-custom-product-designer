<?php

/**
 * MyStyle Product class.
 *
 * The MyStyle Product class is used to wrap the WC_Product class to allow us to
 * support multiple versions of WooCommerce.
 *
 * We don't extend the WC_Product because other classes (WC_Product_Variation,
 * etc) already extend it and we would actually need to extend each of those.
 * Furthermore, plugins such as the WooCommerce Dynamic Pricing Plugin expect
 * the product to be from the set of standard product types and if it isn't,
 * the Dynamic Pricing Plugin breaks.
 *
 * @package MyStyle
 * @since 1.7.0
 * @todo Add unit testing
 */
class MyStyle_Product {

    public static $MYSTYLE_DATA_KEY = 'mystyle_data';

    /** @var \WC_Product */
    private $product;

    /**
     * Constructor.
     * @param \WC_Product $product The WC_Product that we are wrapping.
     */
    public function __construct( \WC_Product $product ) {
        $this->product = $product;
    }

    /**
     * Returns the underlying WC_Product.
     * @return \WC_Product The WC_Product that this class wraps.
     */
    public function get_product() {
        return $this->product;
    }

    /**
     * Gets the product id.
     *
     * Works with WC 2.x and WC 3.x.
     *
     * @return number Returns the product id.
     */
    public function get_id() {
        if( method_exists( $this->product, 'get_id' ) ) {
            $id = $this->product->get_id();
        } else {
            $id = $this->product->ID;
        }

        return $id;
    }

    /**
     * Gets the product type.
     *
     * Works with WC 2.x and WC 3.x.
     *
     * @return string Returns the product type.
     */
    public function get_type() {
        if( method_exists( $this->product, 'get_type' ) ) {
            $product_type = $this->product->get_type();
        } else {
            $product_type = $this->product->product_type;
        }

        return $product_type;
    }

    /**
     * Gets the product's children (as an array of product ids).
     *
     * Works with WC 2.x and WC 3.x.
     *
     * @return array Returns the product's children (as an array of product
     * ids).
     */
    public function get_children() {
        if( method_exists( $this->product, 'get_children' ) ) {
            $product_type = $this->product->get_children();
        } else {
            $product_type = $this->product->children;
        }

        return $product_type;
    }

    /**
     * Function that looks to see if the product is mystyle enabled.
     * @return boolean Returns true if the product is customizable, otherwise,
     * returns false.
     */
    public function is_customizable() {
        $is_customizable = false;
        $mystyle_enabled = get_post_meta( $this->get_id(), '_mystyle_enabled', true );

        if( $mystyle_enabled == 'yes' ) {
            $is_customizable = true;
        }

        return $is_customizable;
    }

	/**
     * Function that looks to see if the product has configur8 enabled.
     * @return boolean Returns true if the product has configur8 enabled,
	 * otherwise, returns false.
     */
    public function configur8_enabled() {
        $configur8_enabled = false;
        $configur8_option_value = get_post_meta( $this->get_id(), '_mystyle_configur8_enabled', true );

        if( $configur8_option_value == 'yes' ) {
            $configur8_enabled = true;
        }

        return $configur8_enabled;
    }

}