<?php
/**
 * The MyStyle WooCommerce Admin Order class hooks MyStyle into the WooCommerce
 * Order admin interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_WooCommerce_Admin_Order class.
 */
class MyStyle_WooCommerce_Admin_Order {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_WooCommerce_Admin_Order
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and registers hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Init the mystyle woocommerce admin.
	 */
	public function admin_init() {
		add_action( 'woocommerce_admin_order_item_headers', array( &$this, 'add_order_item_header' ) );
		add_action( 'woocommerce_admin_order_item_values', array( &$this, 'admin_order_item_values' ), 10, 3 );
	}

	/**
	 * Add the mystyle column header to the order items table.
	 */
	public function add_order_item_header() {
		?>
		<th class="item-mystyle"><?php esc_html_e( 'MyStyle', 'mystyle' ); ?></th>
		<?php
	}

	/**
	 * Add the mystyle column body to the order items table.
	 *
	 * @param WC_Product    $product The current product.
	 * @param WC_Order_Item $item The current item.
	 * @param integer       $item_id The current item id.
	 */
	public function admin_order_item_values( $product, $item, $item_id ) {

		$design = null;

		if (
				( 'WC_Order_Item_Product' === get_class( $item ) )
				&& ( isset( $item['mystyle_data'] ) )
		) {
			/**
			 * NOTE: We aught to be able to get the data by unserializing
			 * $item['mystyle_data'], this however fails because the data comes
			 * through without the tabs and carriage returns which throws the
			 * string counts off. To work around this, we just get the data
			 * directly using a database call.
			 */
			$mystyle_data = wc_get_order_item_meta( $item_id, 'mystyle_data' );

			$design_id = $mystyle_data['design_id'];

			/* @var $current_user \WP_User The current user. */
			$current_user = wp_get_current_user();

			/* @var $design \MyStyle_Design The current design. */
			$design = MyStyle_DesignManager::get( $design_id, $current_user );
		}
		?>
		<td class="item-mystyle">
			<?php if ( null !== $design ) : ?>
				<div class="mystyle-toggle" onclick="mystyleTogglePanelVis(<?php echo esc_js( $item_id ); ?>)">
					<a class="mystyle-toggle-link button" title="Click to toggle">MyStyle Data</a>
					<a id="mystyle-toggle-handle-<?php echo esc_js( $item_id ); ?>" class="mystyle-toggle-handle" title="Click to toggle" onclick="mystyleTogglePanelVis(<?php echo esc_js( $item_id ); ?>)"></a>
				</div>
				<div class="mystyle-panel" id="mystyle-panel-<?php echo esc_js( $item_id ); ?>" style="display:none;">
					<div>
						<?php if ( ! MyStyle_Options::is_demo_mode() ) { ?>
							Design Id: <a href="<?php echo esc_url( $design->get_reload_url() ); ?>" target="_blank"><?php echo esc_html( $design->get_design_id() ); ?></a><br/>
							<?php
							$multi_print_file = false;
							if ( ( preg_match( '/^(.+\_)(\d+)(\..+)$/', $design->get_print_url(), $matches ) ) && ( $matches[2] > 1 ) ) {
								$file_name_base      = $matches[1];
								$print_file_count    = $matches[2];
								$file_name_extension = $matches[3];
								for ( $i = 1; $i <= $print_file_count; $i++ ) {
									$curr_file_name = $file_name_base . $i . $file_name_extension;
									if ( in_array( pathinfo( $curr_file_name, PATHINFO_EXTENSION ), array( 'png', 'jpg' ), true ) ) {
										echo '<a class="button" href="' . esc_url( $curr_file_name ) . '" target="_blank">Print Image ' . esc_html( $i ) . '</a><br/>';
									}
								}
							} else {
								if ( in_array( pathinfo( $design->get_print_url(), PATHINFO_EXTENSION ), array( 'png', 'jpg' ), true ) ) {
									echo '<a class="button" href="' . esc_url( $design->get_print_url() ) . '" target="_blank">Print Image</a><br/>';
								}
							}
							?>
						<?php } ?>
						<a class="button" href="<?php echo esc_url( $design->get_web_url() ); ?>" target="_blank">Web Preview</a><br/>
						<a class="button" href="http://mystyleplatform.com/render/?design_url=<?php echo esc_url( $design->get_design_url() ); ?>" target="_blank">Render Print Image</a><br/>
					</div>
					<hr>
					<img src="<?php echo esc_url( $design->get_thumb_url() ); ?>"/>

				</div>
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_WooCommerce_Admin_Order
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
