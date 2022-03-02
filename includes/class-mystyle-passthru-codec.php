<?php
/**
 * MyStyle Passthru Codec.
 *
 * The MyStyle Passthru Codec works with MyStyle passthru data. It has functions
 * for encoding, decoding, building, modifying, etc.
 *
 * Passthru data is an array of data to pass to/through the customizer.
 *
 * @package MyStyle
 * @since 3.8.2
 */

/**
 * MyStyle_Passthru_Codec class.
 */
class MyStyle_Passthru_Codec {

	/**
	 * Singleton class instance.
	 *
	 * @var MyStyle_Passthru_Codec
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Do nothing.
	}

	/**
	 * Builds passthru data from the passed post and product.
	 *
	 * @param array            $post The post to use for the passthru. This is
	 * typically the $_REQUEST object.
	 * @param \MyStyle_Product $mystyle_product The product to use for building
	 * the passthru.
	 * @todo Add unit testing.
	 */
	public function build_passthru(
						array $post,
						\MyStyle_Product $mystyle_product ) {
		// Set up an array of data to pass to/through the customizer.
		$passthru = array();

		// Start the passthru post data from the parent design (if available).
		/* @var $parent_design \MyStyle_Design The Design that the product was spawned from. */
		$parent_design = $mystyle_product->get_parent_design();
		if ( null !== $parent_design ) {
			if ( null !== $parent_design->get_cart_data() ) {
				$post_data        = json_decode( $parent_design->get_cart_data(), true );
				$passthru['post'] = $post_data;
			}
		}

		// Now add any data from the actual post (overwriting existing values if
		// necessary).
		if ( isset( $passthru['post'] ) ) {
			$passthru['post'] = array_merge( $passthru['post'], $post );
		} else {
			$passthru['post'] = $post;
		}
        
        if( is_user_logged_in() ) {
            $passthru['user']['user_id'] = get_current_user_id() ;
        }

		// Add all available product attributes (if there are any) to the pass
		// through data.
		$product    = new WC_Product_Variable( $mystyle_product->get_id() );
		$attributes = $product->get_variation_attributes();
		if ( ! empty( $attributes ) ) {
			$passthru['attributes'] = $attributes;
		}

		// Add custom template data if enabled.
		$mystyle_custom_template_enabled = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template', true );

		if ( 'yes' === $mystyle_custom_template_enabled ) {
			$passthru['width']  = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_width', true );
			$passthru['height'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_height', true );
			$passthru['shape']  = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_shape', true );

			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_color', true ) ) {
				$passthru['color'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_color', true );
			}

			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_default_text_color', true ) ) {
				$passthru['textColorDefault'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_default_text_color', true );
			}

			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_bgimg', true ) ) {
				$passthru['tbgimg'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_bgimg', true );
			}

			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_fgimg', true ) ) {
				$passthru['tfgimg'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_fgimg', true );
			}

			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_bleed', true ) ) {
				$passthru['bleed'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_bleed', true );
			}

			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_custom_template_boxshadow', true ) === 'yes' ) {
				$passthru['boxshadow'] = 1;
			}
            
			if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_3d_view_enabled', true ) === 'yes' ) {
				$passthru['view_3d'] = 1;
			}
            
            if ( get_post_meta( $mystyle_product->get_id(), '_mystyle_3d_depth', true ) ) {
                $passthru['view_3d'] = get_post_meta( $mystyle_product->get_id(), '_mystyle_3d_depth', true ) ;
            }
		}

		return $passthru;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Passthru_Codec Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
