<?php
/**
 * The MyStyle Configur8 class has functions for adding the Configur8 feature to
 * the WordPress front end.
 *
 * @package MyStyle
 * @since 3.6.0
 */

/**
 * MyStyle_Configur8 class.
 */
class MyStyle_Configur8 {

	/**
	 * Singleton instance of the class.
	 *
	 * @var MyStyle_Configur8
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and registers filters and actions.
	 */
	public function __construct() {
		add_filter( 'woocommerce_before_single_product', array( $this, 'drop_configur8_script' ), 0 );
	}

	/**
	 * Function that adds the MyStyle Configur8 script to the page.
	 *
	 * @global \WC_Product $product
	 */
	public function drop_configur8_script() {
		global $product;

		$mystyle_product = new \MyStyle_Product( $product );
        
		// Drop the configur8 script (if enabled).
		if (
			MyStyle_Options::are_keys_installed() &&
			MyStyle_Options::enable_configur8() &&
			$mystyle_product->configur8_enabled()
		) {
			?>

			<!-- MyStyle Configur8 - MyStyle Custom Product Designer v<?php echo esc_html( MYSTYLE_VERSION ); ?> - https://www.mystyleplatform.com -->
			<script>
				( function ( ) {
					var d = document, s = 'script', id = 'configur8';
					var js, fjs = d.getElementsByTagName( s )[0];
					if ( d.getElementById( id ) )
						return;
					js = d.createElement(s);
					js.id = id;
					js.async = 1;
					js.src = '//static2.ogmystyle.com/configur8/js/mystyle-configur8.min.js';
					fjs.parentNode.insertBefore( js, fjs );
				}());
			</script>
			<!-- / MyStyle Configur8 -->

			<?php
		} // End if configur8 enabled.
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return \MyStyle_Configur8 Returns the singleton instance of this
	 * class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
