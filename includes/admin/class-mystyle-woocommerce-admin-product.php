<?php
/**
 * The MyStyle WooCommerce Admin Product class hooks MyStyle into the
 * WooCommerce Product admin interface.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_WooCommerce_Admin_Product class.
 */
class MyStyle_WooCommerce_Admin_Product {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_WooCommerce_Admin_Product
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
		add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'add_product_data_tab' ) );
		add_action( 'woocommerce_process_product_meta', array( &$this, 'process_mystyle_data_panel' ) );

		if ( MyStyle()->get_WC()->version_compare( '2.6', '<' ) ) {
			add_action( 'woocommerce_product_write_panels', array( &$this, 'add_mystyle_data_panel' ) );
		} else {
			add_action( 'woocommerce_product_data_panels', array( &$this, 'add_mystyle_data_panel' ) );
		}

		add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_js' ) );
	}

	/**
	 * Add a MyStyle tab to the product options tab set.
	 */
	public function add_product_data_tab() {
		echo '<li class="mystyle_product_tab mystyle_product_options"><a href="#mystyle_product_data"><span>MyStyle</span></a></li>';
	}

	/**
	 * Create the content of the MyStyle product options tab.
	 *
	 * @global WP_Post $post The post that is currently being edited.
	 */
	public function add_mystyle_data_panel() {
		global $post;

		// Pull existing values.
		$mystyle_enabled                            = get_post_meta( $post->ID, '_mystyle_enabled', true );
		$template_id                                = get_post_meta( $post->ID, '_mystyle_template_id', true );
		$mystyle_custom_template                    = get_post_meta( $post->ID, '_mystyle_custom_template', true );
		$mystyle_custom_template_width              = get_post_meta( $post->ID, '_mystyle_custom_template_width', true );
		$mystyle_custom_template_height             = get_post_meta( $post->ID, '_mystyle_custom_template_height', true );
		$mystyle_custom_template_shape              = get_post_meta( $post->ID, '_mystyle_custom_template_shape', true );
		$mystyle_custom_template_color              = get_post_meta( $post->ID, '_mystyle_custom_template_color', true );
		$mystyle_custom_template_bgimg              = get_post_meta( $post->ID, '_mystyle_custom_template_bgimg', true );
		$mystyle_custom_template_fgimg              = get_post_meta( $post->ID, '_mystyle_custom_template_fgimg', true );
		$mystyle_custom_template_bleed              = get_post_meta( $post->ID, '_mystyle_custom_template_bleed', true );
		$mystyle_custom_template_boxshadow          = get_post_meta( $post->ID, '_mystyle_custom_template_boxshadow', true );
		$mystyle_custom_template_default_text_color = get_post_meta( $post->ID, '_mystyle_custom_template_default_text_color', true );
		$customizer_ux                              = get_post_meta( $post->ID, '_mystyle_customizer_ux', true );
		$customizer_redirect                        = get_post_meta( $post->ID, '_mystyle_customizer_redirect', true );
		$mystyle_design_id                          = get_post_meta( $post->ID, '_mystyle_design_id', true );
		$mystyle_print_type                         = get_post_meta( $post->ID, '_mystyle_print_type', true );
		$mystyle_3d_view_enabled                    = get_post_meta( $post->ID, '_mystyle_3d_view_enabled', true );
		$mystyle_3d_depth                           = get_post_meta( $post->ID, '_mystyle_3d_depth', true );
		$mystyle_configur8_enabled                  = get_post_meta( $post->ID, '_mystyle_configur8_enabled', true );
		$mystyle_add_to_cart_enabled                = get_post_meta( $post->ID, '_mystyle_add_to_cart_enabled', true );

		?>
		<div id="mystyle_product_data" class="panel woocommerce_options_panel">

			<h3>MyStyle Custom Product Settings</h3>

			<div class="options_group">

				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => '_mystyle_enabled',
						'label'       => __( 'Make Customizable?', 'mystyle' ),
						'desc_tip'    => 'true',
						'description' => __( 'Enable this option to make the product customizable.', 'mystyle' ),
						'value'       => $mystyle_enabled,
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'          => '_mystyle_add_to_cart_enabled',
						'label'       => __( 'Enable Purchase as-is button', 'mystyle' ),
						'desc_tip'    => 'true',
						'description' => __( 'Enable this option to allow the purchase without customization.', 'mystyle' ),
						'value'       => $mystyle_add_to_cart_enabled,
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'          => '_mystyle_template_id',
						'label'       => __( 'MyStyle Template ID', 'mystyle' ),
						'placeholder' => '',
						'desc_tip'    => 'true',
						'description' => __( 'Enter the MyStyle Template Id for the product. For an example template, you can use Template Id 70.', 'mystyle' ),
						'value'       => $template_id,
					)
				);
				?>
				<p class="description">
					Need a template? Check out our <a href="http://www.mystyleplatform.com/mystyle-product-catalog/" title="MyStyle Product Catalog" target="_blank">Product Catalog</a>.
				</p>
				<div class="mystyle-clear mystyle-spacer"></div>
			</div>
			<br/>
			<div class="options_group">

				<div class="mystyle-toggle section-title" onclick="mystyleTogglePanelVis('advanced')">
					<a class="mystyle-toggle-link" title="Click to toggle">Advanced</a>
					<a id="mystyle-toggle-handle-advanced" class="mystyle-toggle-handle" title="Click to toggle"></a>
				</div>
				<div class="mystyle-panel" id="mystyle-panel-advanced" style="display:none;">

					<?php
					woocommerce_wp_checkbox(
						array(
							'id'          => '_mystyle_custom_template',
							'label'       => __( 'Use Custom Template', 'mystyle' ),
							'desc_tip'    => 'true',
							'description' => __( 'Enable this option to use a custom design template.', 'mystyle' ),
							'value'       => $mystyle_custom_template,
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_custom_template_width',
							'label'       => __( 'Custom Template Width (inches)', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'true',
							'description' => __( 'Enter custom template width in inches', 'mystyle' ),
							'value'       => $mystyle_custom_template_width,
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_custom_template_height',
							'label'       => __( 'Custom Template Height (inches)', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'true',
							'description' => __( 'Enter custom template height in inches', 'mystyle' ),
							'value'       => $mystyle_custom_template_height,
						)
					);

					woocommerce_wp_select(
						array(
							'id'          => '_mystyle_custom_template_shape',
							'label'       => __( 'Custom Template Shape', 'mystyle' ),
							'placeholder' => 'rectangle',
							'desc_tip'    => 'true',
							'description' => __( 'Select the custom template shape', 'mystyle' ),
							'value'       => $mystyle_custom_template_shape,
							'options'     => array(
								'rectangle' => 'RECTANGLE',
								'ellipse'   => 'ELLIPSE',
							),
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_custom_template_color',
							'label'       => __( 'Custom Template Color', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'false',
							'value'       => $mystyle_custom_template_color,
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_custom_template_default_text_color',
							'label'       => __( 'Default Text Color', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'false',
							'value'       => $mystyle_custom_template_default_text_color,
						)
					);

					woocommerce_wp_checkbox(
						array(
							'id'          => '_mystyle_custom_template_boxshadow',
							'label'       => __( 'Enable Custom Template Box Shadow', 'mystyle' ),
							'desc_tip'    => 'true',
							'description' => __( 'Enable a custom template Box Shadow.', 'mystyle' ),
							'value'       => $mystyle_custom_template_boxshadow,
						)
					);

					?>
					<p class="form-field _mystyle_custom_template_bgimg_field ">
						<label for="_mystyle_custom_template_bgimg">Custom Template Background Image (BETA)</label>
						<span class="woocommerce-help-tip" data-tip="Select a custom template background image (500px X 500px maximum size)"></span>
						<input type="button" class="button" style="float:left;margin:0;" name="_mystyle_custom_template_bgimg_button" id="_mystyle_custom_template_bgimg_button" value="SELECT" placeholder="">
						<input type="text" class="short" style="width:62.5%;float:left; margin-left:4px;" name="_mystyle_custom_template_bgimg" id="_mystyle_custom_template_bgimg" value="<?php echo ( $mystyle_custom_template_bgimg ) ? esc_attr( $mystyle_custom_template_bgimg ) : ''; ?>" placeholder="">
					</p>

					<p class="form-field _mystyle_custom_template_fgimg_field ">
						<label for="_mystyle_custom_template_fgimg">Custom Template Foreground Image (BETA)</label>
						<span class="woocommerce-help-tip" data-tip="Select a custom template foreground image (500px X 500px maximum size)"></span>
						<input type="button" class="button" style="float:left;margin:0;" name="_mystyle_custom_template_fgimg_button" id="_mystyle_custom_template_fgimg_button" value="SELECT" placeholder="">
						<input type="text" class="short" style="width:62.5%;float:left; margin-left:4px;" name="_mystyle_custom_template_fgimg" id="_mystyle_custom_template_fgimg" value="<?php echo ( $mystyle_custom_template_fgimg ) ? esc_attr( $mystyle_custom_template_fgimg ) : ''; ?>" placeholder="">
					</p>
					<?php

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_custom_template_bleed',
							'label'       => __( 'Custom Template Bleed Size Per Edge (BETA)', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'true',
							'description' => __( 'Enter a bleed size per edge in inches', 'mystyle' ),
							'value'       => $mystyle_custom_template_bleed,
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_design_id',
							'label'       => __( 'MyStyle Design ID', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'true',
							'description' => __( 'Enter a MyStyle Design ID for the product to always start with.  You can get a design ID for any design your site has made by using the Design Manager (add-on).', 'mystyle' ),
							'value'       => $mystyle_design_id,
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_customizer_ux',
							'label'       => __( 'Alternate Customizer UX', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'true',
							'description' => __( 'Alternate UX must be set up special for your site. Do not use this unless you have a custom UX variant developed.', 'mystyle' ),
							'value'       => $customizer_ux,
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_customizer_redirect',
							'label'       => __( 'Alternate Customizer Redirect URL ', 'mystyle' ),
							'placeholder' => '',
							'desc_tip'    => 'true',
							'description' => __( 'There is also a global setting that controls this for all products. This setting will override the global setting optionally if it is set here. Leave blank to disable (default).', 'mystyle' ),
							'value'       => $customizer_redirect,
						)
					);

					woocommerce_wp_select(
						array(
							'id'          => '_mystyle_print_type',
							'label'       => __( 'Print Output Override', 'mystyle' ),
							'placeholder' => 'DEFAULT',
							'desc_tip'    => 'true',
							'description' => __( 'This will override the product print output type setting.', 'mystyle' ),
							'value'       => $mystyle_print_type,
							'options'     => array(
								'DEFAULT'        => 'DEFAULT',
								'FULL-COLOR'     => 'FULL-COLOR',
								'GREYSCALE'      => 'GREYSCALE',
								'BLACK-ON-WHITE' => 'BLACK-ON-WHITE',
								'WHITE-ON-BLACK' => 'WHITE-ON-BLACK',
								'NO-PRINT-FILE'  => 'NO-PRINT-FILE',
							),
						)
					);

                    woocommerce_wp_checkbox(
						array(
							'id'          => '_mystyle_3d_view_enabled',
							'label'       => __( 'Enable 3D View?', 'mystyle' ),
							'desc_tip'    => 'true',
							'description' => __( 'Enable this option to turn the 3D Viewer feature on for this product.', 'mystyle' ),
							'value'       => $mystyle_3d_view_enabled,
						)
					);
        
                    woocommerce_wp_text_input(
						array(
							'id'          => '_mystyle_3d_depth',
							'label'       => __( '3D View Depth (inches)', 'mystyle' ),
							'placeholder' => 'Example "1"',
							'desc_tip'    => 'true',
							'description' => __( 'Enter the 3D Viewer depth (Z axis) in inches', 'mystyle' ),
							'value'       => ( $mystyle_3d_depth ? $mystyle_3d_depth : 0.1 ),
						)
					);
        
					woocommerce_wp_checkbox(
						array(
							'id'          => '_mystyle_configur8_enabled',
							'label'       => __( 'Enable Configur8? (BETA)', 'mystyle' ),
							'desc_tip'    => 'true',
							'description' => __( 'Enable this option to turn the Configur8 feature on for this product. The "Enable Configure8" setting works independently of the "Make Customizable" setting.', 'mystyle' ),
							'value'       => $mystyle_configur8_enabled,
						)
					);
        
					?>

				</div> <!-- end advanced mystyle section -->

			</div>
		</div>
		<?php
	}

	/**
	 * Process the mystyle tab options when a post is saved.
	 *
	 * @param integer $post_id The id of the post that is being saved.
	 */
	public function process_mystyle_data_panel( $post_id ) {

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$mystyle_enabled                            = ( isset( $_POST['_mystyle_enabled'] ) && ( boolval( $_POST['_mystyle_enabled'] ) ) ) ? 'yes' : 'no';
		$template_id                                = ( isset( $_POST['_mystyle_template_id'] ) ) ? intval( $_POST['_mystyle_template_id'] ) : 0;
		$mystyle_custom_template                    = ( isset( $_POST['_mystyle_custom_template'] ) ) ? sanitize_key( $_POST['_mystyle_custom_template'] ) : 'no';
		$mystyle_custom_template_width              = ( ! empty( $_POST['_mystyle_custom_template_width'] ) ) ? floatval( $_POST['_mystyle_custom_template_width'] ) : null;
		$mystyle_custom_template_height             = ( ! empty( $_POST['_mystyle_custom_template_height'] ) ) ? floatval( $_POST['_mystyle_custom_template_height'] ) : null;
		$mystyle_custom_template_shape              = ( isset( $_POST['_mystyle_custom_template_shape'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_custom_template_shape'] ) ) : null;
		$mystyle_custom_template_color              = ( isset( $_POST['_mystyle_custom_template_color'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_custom_template_color'] ) ) : '';
		$mystyle_custom_template_default_text_color = ( isset( $_POST['_mystyle_custom_template_default_text_color'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_custom_template_default_text_color'] ) ) : '';
		$mystyle_custom_template_bgimg              = ( isset( $_POST['_mystyle_custom_template_bgimg'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_custom_template_bgimg'] ) ) : '';
		$mystyle_custom_template_fgimg              = ( isset( $_POST['_mystyle_custom_template_fgimg'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_custom_template_fgimg'] ) ) : '';
		$mystyle_custom_template_bleed              = ( ! empty( $_POST['_mystyle_custom_template_bleed'] ) ? floatval( $_POST['_mystyle_custom_template_bleed'] ) : null );
		$mystyle_custom_template_boxshadow          = ( isset( $_POST['_mystyle_custom_template_boxshadow'] ) ? sanitize_key( $_POST['_mystyle_custom_template_boxshadow'] ) : 'no' );
		$mystyle_design_id                          = ( ! empty( $_POST['_mystyle_design_id'] ) ) ? intval( $_POST['_mystyle_design_id'] ) : '';
		$customizer_ux                              = ( isset( $_POST['_mystyle_customizer_ux'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_customizer_ux'] ) ) : null;
		$customizer_redirect                        = ( isset( $_POST['_mystyle_customizer_redirect'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_customizer_redirect'] ) ) : null;
		$mystyle_print_type                         = ( isset( $_POST['_mystyle_print_type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_print_type'] ) ) : null;
        
		$mystyle_3d_view_enabled                    = ( isset( $_POST['_mystyle_3d_view_enabled'] ) && boolval( $_POST['_mystyle_3d_view_enabled'] ) ) ? 'yes' : 'no';
        
        $mystyle_3d_depth                           = ( isset( $_POST['_mystyle_3d_depth'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_mystyle_3d_depth'] ) ) : null;
        
		$mystyle_configur8_enabled                  = ( isset( $_POST['_mystyle_configur8_enabled'] ) && boolval( $_POST['_mystyle_configur8_enabled'] ) ) ? 'yes' : 'no';
		$mystyle_add_to_cart_enabled                = ( isset( $_POST['_mystyle_add_to_cart_enabled'] ) && boolval( $_POST['_mystyle_add_to_cart_enabled'] ) ) ? 'yes' : 'no';
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

		if ( 'yes' === $mystyle_enabled ) {
			if ( 'yes' === $mystyle_custom_template ) { // Custom template is enabled, set template_id to 1.
				update_post_meta( $post_id, '_mystyle_enabled', 'yes' );
				update_post_meta( $post_id, '_mystyle_template_id', '1' );
				update_post_meta( $post_id, '_mystyle_custom_template', $mystyle_custom_template );
				update_post_meta( $post_id, '_mystyle_custom_template_width', $mystyle_custom_template_width );
				update_post_meta( $post_id, '_mystyle_custom_template_height', $mystyle_custom_template_height );
				update_post_meta( $post_id, '_mystyle_custom_template_shape', $mystyle_custom_template_shape );
				update_post_meta( $post_id, '_mystyle_custom_template_color', $mystyle_custom_template_color );
				update_post_meta( $post_id, '_mystyle_custom_template_default_text_color', $mystyle_custom_template_default_text_color );
				update_post_meta( $post_id, '_mystyle_custom_template_bgimg', $mystyle_custom_template_bgimg );
				update_post_meta( $post_id, '_mystyle_custom_template_fgimg', $mystyle_custom_template_fgimg );
				update_post_meta( $post_id, '_mystyle_custom_template_bleed', $mystyle_custom_template_bleed );
				update_post_meta( $post_id, '_mystyle_custom_template_boxshadow', $mystyle_custom_template_boxshadow );
				update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
				update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
				update_post_meta( $post_id, '_mystyle_customizer_redirect', $customizer_redirect );
				update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );
			} elseif ( 0 !== $template_id ) { // Both required options are set (store them).
				update_post_meta( $post_id, '_mystyle_enabled', 'yes' );
				update_post_meta( $post_id, '_mystyle_template_id', $template_id );
				update_post_meta( $post_id, '_mystyle_custom_template', $mystyle_custom_template );
				update_post_meta( $post_id, '_mystyle_custom_template_width', $mystyle_custom_template_width );
				update_post_meta( $post_id, '_mystyle_custom_template_height', $mystyle_custom_template_height );
				update_post_meta( $post_id, '_mystyle_custom_template_shape', $mystyle_custom_template_shape );
				update_post_meta( $post_id, '_mystyle_custom_template_color', $mystyle_custom_template_color );
				update_post_meta( $post_id, '_mystyle_custom_template_default_text_color', $mystyle_custom_template_default_text_color );
				update_post_meta( $post_id, '_mystyle_custom_template_bgimg', $mystyle_custom_template_bgimg );
				update_post_meta( $post_id, '_mystyle_custom_template_fgimg', $mystyle_custom_template_fgimg );
				update_post_meta( $post_id, '_mystyle_custom_template_bleed', $mystyle_custom_template_bleed );
				update_post_meta( $post_id, '_mystyle_custom_template_boxshadow', $mystyle_custom_template_boxshadow );
				update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
				update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
				update_post_meta( $post_id, '_mystyle_customizer_redirect', $customizer_redirect );
				update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );
			} else { // Enabled but no template id (store data, disable and notify).
				update_post_meta( $post_id, '_mystyle_enabled', 'no' );
				update_post_meta( $post_id, '_mystyle_template_id', '' );
				update_post_meta( $post_id, '_mystyle_custom_template', $mystyle_custom_template );
				update_post_meta( $post_id, '_mystyle_custom_template_width', $mystyle_custom_template_width );
				update_post_meta( $post_id, '_mystyle_custom_template_height', $mystyle_custom_template_height );
				update_post_meta( $post_id, '_mystyle_custom_template_shape', $mystyle_custom_template_shape );
				update_post_meta( $post_id, '_mystyle_custom_template_color', $mystyle_custom_template_color );
				update_post_meta( $post_id, '_mystyle_custom_template_default_text_color', $mystyle_custom_template_default_text_color );
				update_post_meta( $post_id, '_mystyle_custom_template_bgimg', $mystyle_custom_template_bgimg );
				update_post_meta( $post_id, '_mystyle_custom_template_fgimg', $mystyle_custom_template_fgimg );
				update_post_meta( $post_id, '_mystyle_custom_template_bleed', $mystyle_custom_template_bleed );
				update_post_meta( $post_id, '_mystyle_custom_template_boxshadow', $mystyle_custom_template_boxshadow );
				update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
				update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
				update_post_meta( $post_id, '_mystyle_customizer_redirect', $customizer_redirect );
				update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );

				$validation_notice = MyStyle_Notice::create(
					'invalid_product_options',
					'You must choose a Template Id in order to make the product customizable.',
					'error'
				);
				mystyle_notice_add_to_queue( $validation_notice );
			}
		} else { // Not enabled (store data).
			update_post_meta( $post_id, '_mystyle_enabled', 'no' );
			update_post_meta( $post_id, '_mystyle_template_id', $template_id );
			update_post_meta( $post_id, '_mystyle_custom_template', $mystyle_custom_template );
			update_post_meta( $post_id, '_mystyle_custom_template_width', $mystyle_custom_template_width );
			update_post_meta( $post_id, '_mystyle_custom_template_height', $mystyle_custom_template_height );
			update_post_meta( $post_id, '_mystyle_custom_template_shape', $mystyle_custom_template_shape );
			update_post_meta( $post_id, '_mystyle_custom_template_color', $mystyle_custom_template_color );
			update_post_meta( $post_id, '_mystyle_custom_template_default_text_color', $mystyle_custom_template_default_text_color );
			update_post_meta( $post_id, '_mystyle_custom_template_bgimg', $mystyle_custom_template_bgimg );
			update_post_meta( $post_id, '_mystyle_custom_template_fgimg', $mystyle_custom_template_fgimg );
			update_post_meta( $post_id, '_mystyle_custom_template_bleed', $mystyle_custom_template_bleed );
			update_post_meta( $post_id, '_mystyle_custom_template_boxshadow', $mystyle_custom_template_boxshadow );
			update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
			update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
			update_post_meta( $post_id, '_mystyle_customizer_redirect', $customizer_redirect );
			update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );
		}
		// Store the Enable 3D View and Configur8 settings regardless of other settings.
		update_post_meta( $post_id, '_mystyle_3d_view_enabled', $mystyle_3d_view_enabled );
		update_post_meta( $post_id, '_mystyle_3d_depth', $mystyle_3d_depth );
		update_post_meta( $post_id, '_mystyle_configur8_enabled', $mystyle_configur8_enabled );
		update_post_meta( $post_id, '_mystyle_add_to_cart_enabled', $mystyle_add_to_cart_enabled );
	}

	/**
	 * Add WP admin color picker js.
	 */
	public function add_admin_js() {
		if ( is_admin() ) {

			// Add the color picker css file.
			wp_enqueue_style( 'wp-color-picker' );

			// Include our custom jQuery file with WordPress Color Picker dependency.
			wp_enqueue_script( 'mystyle-color-picker', MYSTYLE_ASSETS_URL . 'js/color-picker.js', array( 'wp-color-picker' ), MYSTYLE_VERSION, true );
			wp_enqueue_script( 'mystyle-media-select', MYSTYLE_ASSETS_URL . 'js/media-select.js', array(), false, true );
		}
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_WooCommerce_Admin_Product
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
