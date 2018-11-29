<?php
/**
 *
 * Interface for facilitating interactions with woocommerce.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * MyStyle_WC_Interface class.
 */
interface MyStyle_WC_Interface {

	/**
	 * Checks to see if WooCommerce is installed and activated.
	 *
	 * @return boolean Returns true if WooCommerce is installed and activated,
	 * otherwise, returns false.
	 */
	public function is_installed();

	/**
	 * Returns the version of WooCommerce that is installed. Returns null if
	 * WooCommerce isn't installed.
	 *
	 * @return string|null Returns the WooCommerce version that is currently
	 * running.
	 */
	public function get_version();

	/**
	 * Compares the past version with the currently installed version of
	 * WooCommerce using the passed operator.
	 *
	 * @param string $version The version to compare with ( ex: "3.0" ).
	 * @param string $operator A comparison operator ( ex: ">" ).
	 * @return boolean Returns the result of the comparison.
	 */
	public function version_compare( $version, $operator );

	/**
	 * Wrapper for the global wc_get_page_id function.
	 *
	 * @param string $page The page that you want to get the id of.
	 * @return int Returns the id of the passed page.
	 */
	public function wc_get_page_id( $page );

	/**
	 * Wraps the now depcrecated get_matching_variation method of the
	 * WC_Product_Variable to allow us to call it independent of WC version.
	 *
	 * @param integer $product_id The product id of the product whose variation
	 * you are looking for.
	 * @param array   $variation The variation that you are looking for.
	 * @return integer Returns the variation id of the matching product
	 * variation.
	 * @todo Add unit testing
	 */
	public function get_matching_variation( $product_id, $variation );
}
