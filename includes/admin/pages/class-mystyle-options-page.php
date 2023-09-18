<?php
/**
 * Class for rendering the MyStyle Options/Settings page within the WordPress
 * Administrator.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * MyStyle_Options_Page class.
 */
class MyStyle_Options_Page {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Options_Page
	 */
	private static $instance;

	/**
	 * Constructor, constructs the options page and adds it to the Settings
	 * menu.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'add_page_to_menu' ), 10, 0 );
		add_action( 'admin_init', array( &$this, 'admin_init' ), 10, 0 );
		// Note: we run our custom actions in the current_screen action so that
		// it is late enough to tell what screen we are on but early enough to
		// set/display notices.
		add_action( 'current_screen', array( &$this, 'handle_custom_actions' ), 10, 0 );
	}

	/**
	 * Function to initialize the MyStyle options page.
	 */
	public function admin_init() {
		register_setting( 'mystyle_options', MYSTYLE_OPTIONS_NAME, array( &$this, 'validate' ) );
		// ************** ACCOUNT SETTINGS SECTION ******************//
		add_settings_section(
			'mystyle_options_access_section',
			'Account Settings',
			array( &$this, 'render_access_section_text' ),
			'mystyle_options'
		);
		add_settings_field(
			'api_key',
			'API Key',
			array( &$this, 'render_api_key' ),
			'mystyle_options',
			'mystyle_options_access_section'
		);
		add_settings_field(
			'secret',
			'Secret',
			array( &$this, 'render_secret' ),
			'mystyle_options',
			'mystyle_options_access_section'
		);

		// ************** ADVANCED SETTINGS SECTION ******************//
		add_settings_section(
			'mystyle_options_advanced_section',
			'Advanced Settings',
			array( &$this, 'render_advanced_section_text' ),
			'mystyle_options'
		);

		/* ENABLE FLASH SETTING */
		add_settings_field(
			'enable_flash',
			'Enable Flash (Not Recommended)',
			array( &$this, 'render_enable_flash' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* HIDE PAGE TITLE ON CUSTOMIZE PAGE */
		add_settings_field(
			'customize_page_title_hide',
			'Hide Customize Page Title',
			array( &$this, 'render_hide_customize_title' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* ALTERNATE PAGE TITLE FOR DESIGN TAG AND COLLECTIONS PAGES */
		add_settings_field(
			'alternate_design_tag_collection_title',
			'Design Tag/Collection Archives SEO Label (plural)',
			array( &$this, 'render_design_tag_collection_title' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* SHOW ADD TO CART BUTTON ON DESIGN PROFILE PAGES */
		add_settings_field(
			'design_profile_page_show_add_to_cart',
			'Show Add to Cart Button on Design Profile Pages',
			array( &$this, 'render_design_profile_page_show_add_to_cart' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* DISABLE_VIEWPORT_REWRITE */
		add_settings_field(
			'customize_page_disable_viewport_rewrite',
			'Disable Viewport Rewrite',
			array( &$this, 'render_customize_page_disable_viewport_rewrite' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* FORM INTEGRATION CONFIG */
		add_settings_field(
			'form_integration_config',
			'Form Integration Config',
			array( &$this, 'render_form_integration_config' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* ENABLE_ALTERNATE_DESIGN_COMPLETE_REDIRECT */
		add_settings_field(
			'enable_alternate_design_complete_redirect',
			'Enable Alternate Design Complete Redirect',
			array( &$this, 'render_enable_alternate_design_complete_redirect' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* ALTERNATE_DESIGN_COMPLETE_REDIRECT_URL */
		add_settings_field(
			'alternate_design_complete_redirect_url',
			'Alternate Design Complete Redirect URL',
			array( &$this, 'render_alternate_design_complete_redirect_url' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* REDIRECT URL WHITELIST */
		add_settings_field(
			'redirect_url_whitelist',
			'Redirect URL Whitelist',
			array( &$this, 'render_redirect_url_whitelist' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* ENABLE_CONFIGUR8 */
		add_settings_field(
			'enable_configur8',
			'Enable Configur8',
			array( &$this, 'render_enable_configur8' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);

		/* DESIGN_PROFILE_PRODUCT_MENU_TYPE */
		add_settings_field(
			'design_profile_product_menu_type',
			'Reload-To-Other-Product Menu Style',
			array( &$this, 'render_design_profile_product_menu_type' ),
			'mystyle_options',
			'mystyle_options_advanced_section'
		);
		
		
		/* DESIGN IMAGE  */
	add_settings_section(
		'mystyle_options_image_section',
		'Image Settings',
		array(&$this, 'render_image_section_text'),
		'mystyle_options'
	);
	/* DESIGN IMAGE  FIELD */
	add_settings_field(
		'image_type',
		'Image Type',
		array(&$this, 'render_image_type'),
		'mystyle_options',
		'mystyle_options_image_section'
	);
	
	
	// Add CDN Settings Section
add_settings_section(
    'mystyle_options_cdn_section',
    'CDN Settings',
    array(&$this, 'render_cdn_section_text'),
    'mystyle_options'
);

// Enable CDN for Images
add_settings_field(
    'enable_cdn_images',
    'Enable CDN ',
    array(&$this, 'render_enable_cdn_images'),
    'mystyle_options',
    'mystyle_options_cdn_section'
);

// Base CDN URL
add_settings_field(
    'cdn_base_url',
    'CDN Base URL',
    array(&$this, 'render_cdn_base_url'),
    'mystyle_options',
    'mystyle_options_cdn_section'
);


	
	
	
	}

	/**
	 * Function to add the options page to the settings menu.
	 */
	public function add_page_to_menu() {
		$mystyle_hook = 'mystyle';

		add_submenu_page(
			$mystyle_hook,
			'Settings',
			'Settings',
			'manage_options',
			$mystyle_hook . '_settings',
			array( &$this, 'render_page' ),
			100
		);
	}

	/**
	 * Function to handle post requests from the MyStyle Admin Options page.
	 */
	public function handle_custom_actions() {
		/* $screen \WP_Screen The current screen. */
		$screen    = get_current_screen();
		$screen_id = ( ! empty( $screen ) ? $screen->id : null );
		$handled   = false;
		if (
			( 'toplevel_page_mystyle' === $screen_id )
			&& ( ! empty( $_GET['action'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
			&& ( 'POST' === $_SERVER['REQUEST_METHOD'] ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
			&& ( wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'mystyle-admin-action' ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
		) {

			switch ( $_GET['action'] ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
				case 'fix_customize_page':
					// Attempt the fix.
					$message = MyStyle_Customize_Page::fix();

					// Post Fix Notice.
					$fix_notice = MyStyle_Notice::create( 'notify_fix', $message );
					mystyle_notice_add_to_queue( $fix_notice );
					$handled = true;

					break;
				case 'fix_design_profile_page':
					// Attempt the fix.
					$message = MyStyle_Design_Profile_Page::fix();

					// Post Fix Notice.
					$fix_notice = MyStyle_Notice::create( 'notify_fix', $message );
					mystyle_notice_add_to_queue( $fix_notice );
					$handled = true;

					break;
			}
		}

		// For unit testing.
		if ( defined( 'DOING_PHPUNIT' ) ) {
			return $handled;
		}
	}

	/**
	 * Function to render the MyStyle options page.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h2 class="mystyle-admin-title">
				<span id="mystyle-icon-general" class="icon100"></span>
				MyStyle Settings <span class="glyphicon glyphicon-cog"></span>
			</h2>
			<?php settings_errors(); ?>

			<form action="options.php" method="post">
				<?php settings_fields( 'mystyle_options' ); ?>
				<?php MyStyle_Admin::do_settings_sections( 'mystyle_options' ); ?>
				<p class="submit">
					<input type="submit" name="Submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'mystyle' ); ?>" />
				</p>
			</form>
			<br/>
			<div class="mystyle-admin-box">
				<?php do_settings_sections( 'mystyle_tools' ); ?>
				<form action="<?php echo esc_url( 'admin.php?page=mystyle&action=fix_customize_page&_wpnonce=' . wp_create_nonce( 'mystyle-admin-action' ) ); ?>" method="post">
					<p class="submit">
						<input type="submit" name="Submit" id="submit_fix_customize_page" class="button button-primary" value="<?php esc_attr_e( 'Fix Customize Page', 'mystyle' ); ?>" /><br/>
						<small>This tool will attempt to fix the Customize page. This may involve creating, recreating, or restoring the page.</small>
					</p>
				</form>
				<form action="admin.php?page=mystyle&action=fix_design_profile_page&_wpnonce=<?php echo rawurlencode( wp_create_nonce( 'mystyle-admin-action' ) ); ?>" method="post">
					<p class="submit">
						<input type="submit" name="Submit" id="submit_fix_design_profile_page" class="button button-primary" value="<?php esc_attr_e( 'Fix Design Profile Page', 'mystyle' ); ?>" /><br/>
						<small>This tool will attempt to fix the Design page. This may involve creating, recreating, or restoring the page.</small>
					</p>
				</form>
			</div>
			<br/>
			<ul>
				<li>Go to <a href="http://www.mystyleplatform.com/mystyle-personalization-plugin-wordpress-woo-commerce/" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a>.</li>
				<!-- <li>Get <a href="#" onclick="jQuery('a#contextual-help-link').trigger('click'); return false;" title="Get help using this plugin.">help</a> using this plugin.</li> -->
				<li>Get <a href="http://www.mystyleplatform.com/forums/forum/support" title="Get support for using our plugins.">free support</a> for our plugins in our <a href="http://www.mystyleplatform.com/forums/forum/support" title="Get support for using our plugins.">support forums</a>.</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Function to render the text for the access section.
	 */
	public function render_access_section_text() {
		?>
		<p>
			To use the <a href="http://www.mystyleplatform.com" target="_blank">MyStyle</a> customizer,
			<a href="http://www.mystyleplatform.com/?ref=wpcpd_settings" target="_blank" title="mystyleplatform.com">
			sign up with MyStyle</a> and get your own MyStyle License. Once you've
			gotten a license, enter your API Key and Secret below.
		</p>
		<?php
	}

	/**
	 * Function to render the API Key field and description
	 */
	public function render_api_key() {
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$api_key = ( array_key_exists( 'api_key', $options ) ) ? $options['api_key'] : '';
		?>
		<input id="mystyle_api_key" name="mystyle_options[api_key]" size="5" type="text" value="<?php echo esc_attr( $api_key ); ?>" />
		<p class="description">
			You must enter a valid MyStyle API Key here. If you need an
			API Key, you can create one
			<a href="http://www.mystyleplatform.com/apply-mystyle-license-developer-account-api-key-secret/?ref=wp_plugin_2" target="_blank" title="MyStyle Signup">here</a>.
		</p>
		<?php
	}

	/**
	 * Function to render the Secret field and description
	 */
	public function render_secret() {
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$secret  = ( array_key_exists( 'secret', $options ) ) ? $options['secret'] : '';
		?>
		<input id="mystyle_secret" name="mystyle_options[secret]" size="27" type="text" value="<?php echo esc_attr( $secret ); ?>" />
		<p class="description">
			You must enter a valid MyStyle Secret here. If you need a MyStyle
			Secret, you can create one
			<a href="http://www.mystyleplatform.com/apply-mystyle-license-developer-account-api-key-secret/?ref=wp_plugin_3" target="_blank" title="MyStyle Signup">here</a>.
		</p>
		<?php
	}

	/**
	 * Function to render the text for the advanced section.
	 */
	public function render_advanced_section_text() {
		?>
		<p>
			For advanced users only.
		</p>
		<?php
	}

	/**
	 * Function to render the Enable Flash field and description.
	 */
	public function render_enable_flash() {
		$options      = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$enable_flash = ( array_key_exists( 'enable_flash', $options ) ) ? $options['enable_flash'] : 0;
		?>

		<label class="description">
			<input type="checkbox" id="mystyle_enable_flash" name="mystyle_options[enable_flash]" value="1" <?php echo checked( 1, $enable_flash, false ); ?> />
			&nbsp; Use the Flash version of the MyStyle customizer (when Flash is available).
		</label>
		<?php
	}

	/**
	 * Function to render custom design tag page title
	 */
	public function render_design_tag_collection_title() {
		$options     = get_option( MYSTYLE_OPTIONS_NAME, array() ); // Get WP Options table Key of this option.
		$current_val = ( array_key_exists( 'alternate_design_tag_collection_title', $options ) ) ? $options['alternate_design_tag_collection_title'] : '';
		?>
		<input id="mystyle_alternate_design_tag_collection_title" name="mystyle_options[alternate_design_tag_collection_title]" size="60" type="text" value="<?php echo esc_attr( $current_val ); ?>" />
		<p class="description">Specify Design Tag/Collection Archives SEO Label (plural).</p>
		<?php
	}

	/**
	 * Function to render the Hide Customize Page Title option and checkbox.
	 */
	public function render_hide_customize_title() {
		$options                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$customize_page_title_hide = ( array_key_exists( 'customize_page_title_hide', $options ) ) ? $options['customize_page_title_hide'] : 0;
		?>

		<label class="description">
			<input type="checkbox" id="customize_page_title_hide" name="mystyle_options[customize_page_title_hide]" value="1" <?php echo checked( 1, $customize_page_title_hide, false ); ?> />
			&nbsp; Hide the page title on the Customize page.
		</label>
		<?php
	}

	/**
	 * Function to render the Show Add to Cart Button on Design Profile Pages
	 * option and checkbox.
	 */
	public function render_design_profile_page_show_add_to_cart() {
		$options                              = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$design_profile_page_show_add_to_cart = ( array_key_exists( 'design_profile_page_show_add_to_cart', $options ) ) ? $options['design_profile_page_show_add_to_cart'] : 1;
		?>

		<label class="description">
			<input type="checkbox" id="design_profile_page_show_add_to_cart" name="mystyle_options[design_profile_page_show_add_to_cart]" value="1" <?php echo checked( 1, $design_profile_page_show_add_to_cart, false ); ?> />
			&nbsp; Show the Add to Cart button on Design Profile pages.
		</label>
		<?php
	}

	/**
	 * Function to render the Disable Viewport Rewrite option and checkbox.
	 */
	public function render_customize_page_disable_viewport_rewrite() {
		$options                                 = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$customize_page_disable_viewport_rewrite = ( array_key_exists( 'customize_page_disable_viewport_rewrite', $options ) ) ? $options['customize_page_disable_viewport_rewrite'] : 0;
		?>

		<label class="description">
			<input
				type="checkbox"
				id="customize_page_disable_viewport_rewrite"
				name="mystyle_options[customize_page_disable_viewport_rewrite]"
				value="1"
				<?php echo checked( 1, $customize_page_disable_viewport_rewrite, false ); ?>
				/>
			&nbsp; The MyStyle plugin will rewrite the viewport tag on the
			Customize page (only) for optimal display of the MyStyle Customizer.
			Check this box if you would prefer to control the viewport yourself.
		</label>
		<?php
	}

	/**
	 * Function to render the form integration config field.
	 */
	public function render_form_integration_config() {

		$options     = get_option( MYSTYLE_OPTIONS_NAME, array() ); // Get WP Options table Key of this option.
		$current_val = ( array_key_exists( 'form_integration_config', $options ) ) ? $options['form_integration_config'] : '';
		?>
		<textarea id="mystyle_form_integration_config" name="mystyle_options[form_integration_config]" ><?php echo esc_textarea( $current_val ); ?></textarea>
		<p class="description">Configure advanced form integrations here (not recommended)</p>
		<?php
	}

	/**
	 * Function to render the Enable Alternate Design Complete Redirect option
	 * and checkbox.
	 */
	public function render_enable_alternate_design_complete_redirect() {
		$options                                   = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$enable_alternate_design_complete_redirect = ( array_key_exists( 'enable_alternate_design_complete_redirect', $options ) ) ? $options['enable_alternate_design_complete_redirect'] : 0;
		?>

		<label class="description">
			<input type="checkbox" id="enable_alternate_design_complete_redirect" name="mystyle_options[enable_alternate_design_complete_redirect]" value="1" <?php echo checked( 1, $enable_alternate_design_complete_redirect, false ); ?> />
			&nbsp; Enable the alternate design complete redirect.
		</label>
		<?php
	}

	/**
	 * Function to render the alternate design complete redirect url field.
	 */
	public function render_alternate_design_complete_redirect_url() {

		$options     = get_option( MYSTYLE_OPTIONS_NAME, array() ); // Get WP Options table Key of this option.
		$current_val = ( array_key_exists( 'alternate_design_complete_redirect_url', $options ) ) ? $options['alternate_design_complete_redirect_url'] : '';
		?>
		<input id="mystyle_alternate_design_complete_redirect_url" name="mystyle_options[alternate_design_complete_redirect_url]" size="60" type="text" value="<?php echo esc_attr( $current_val ); ?>" />
		<p class="description">Specify an alternate URL to redirect to after the user completes their design. By default, the user will be redirected to the cart.</p>
		<?php
	}

	/**
	 * Function to render the redirect url whitelist field
	 */
	public function render_redirect_url_whitelist() {

		$options     = get_option( MYSTYLE_OPTIONS_NAME, array() ); // Get WP Options table Key of this option.
		$current_val = ( array_key_exists( 'redirect_url_whitelist', $options ) ) ? $options['redirect_url_whitelist'] : '';
		?>
		<textarea id="mystyle_redirect_url_whitelist" name="mystyle_options[redirect_url_whitelist]" ><?php echo esc_textarea( $current_val ); ?></textarea>
		<p class="description">White list domains that can be redirected to (one per line, ex: "www.example.com"). Contact MyStyle for details.</p>
		<?php
	}

	/**
	 * Function to render the Enable Configur8 option and checkbox.
	 */
	public function render_enable_configur8() {
		$options          = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$enable_configur8 = ( array_key_exists( 'enable_configur8', $options ) ) ? $options['enable_configur8'] : 0;
		?>

		<label class="description">
			<input type="checkbox" id="enable_configur8" name="mystyle_options[enable_configur8]" value="1" <?php echo checked( 1, $enable_configur8, false ); ?> />
			&nbsp; Enable the Configur8 feature.
			<p class="description">
				Configur8 works on the product
				info page to make the product image change based on user input.
				To use, first enable this setting and then turn Configur8 on in
				the settings for each individual product as well.
			</p>
		</label>
		<?php
	}


	/**
	 * Function to render image setting.
	 */
			public function render_image_section_text()
			{
				echo '<p>Select the size of images to display in galleries.</p>';
			}

			public function render_image_type()
			{
				$options = get_option(MYSTYLE_OPTIONS_NAME, array());
				$imageType = (array_key_exists('image_type', $options)) ? $options['image_type'] : 'web';

				echo '<label><input type="radio" name="mystyle_options[image_type]" value="thumbnail" ' . checked('thumbnail', $imageType, false) . ' /> Thumbnail Image</label><br>';
				echo '<label><input type="radio" name="mystyle_options[image_type]" value="web" ' . checked('web', $imageType, false) . ' /> Web Image</label>';
			}


public function render_cdn_section_text() {
    echo '<p>Configure CDN settings for MyStyle images and thumbs.</p>';
}

public function render_enable_cdn_images() {
    $options = get_option(MYSTYLE_OPTIONS_NAME, array());
    $enableCdnImages = (array_key_exists('enable_cdn_images', $options)) ? $options['enable_cdn_images'] : 0;
    echo '<label><input type="checkbox" name="mystyle_options[enable_cdn_images]" value="1" ' . checked(1, $enableCdnImages, false) . ' /> Enable CDN for Images And Thumbs</label>';
}

public function render_cdn_base_url() {
    $options = get_option(MYSTYLE_OPTIONS_NAME, array());
    $cdnBaseUrl = (array_key_exists('cdn_base_url', $options)) ? $options['cdn_base_url'] : '';
    echo '<input type="text" name="mystyle_options[cdn_base_url]" value="' . esc_attr($cdnBaseUrl) . '" />';
    echo '<p class="description">Enter the base URL for the CDN.</p>';
}


	/**
	 * Function to render the design_profile_product_menu_type field.
	 */
	public function render_design_profile_product_menu_type() {
		$options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$type    = ( array_key_exists( 'design_profile_product_menu_type', $options ) ) ? $options['design_profile_product_menu_type'] : '';
		?>
		<label class="description">
			<select name="mystyle_options[design_profile_product_menu_type]">
			<?php
			$select = array(
				'list'     => 'List View',
				'grid'     => 'Grid View',
				'disabled' => 'Disabled',
			);
			foreach ( $select as $key => $value ) {
				if ( $key === $type ) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				// phpcs:ignore WordPress.XSS.EscapeOutput
				echo '<option value="' . $key . '"' . $selected . ' >' . $value . '</option>';
			}
			?>
			</select>
			<p class="description">Choose how to render the menu on the design profile page listing all custom products to reload the design on.</p>
		</label>
		<?php
	}

	/**
	 * Function to render the text for the tools section.
	 */
	public function render_tools_section_text() {
		?>
		<p>
			The below tools are available to repair your MyStyle configuration.
		</p>
		<?php
	}

	/**
	 * Function to validate the submitted MyStyle options field values.
	 *
	 * This function overrites the old values instead of completely replacing them so
	 * that we don't overwrite values that weren't submitted (such as the
	 * version).
	 *
	 * @param array $input  The submitted values.
	 * @return array Returns the new options to be stored in the database.
	 */
	public function validate( $input ) {

		// Return without doing any validation if a tools/action button pressed.
		if ( ( ! empty( $_GET['action'] ) ) && ( 'POST' === $_SERVER['REQUEST_METHOD'] ) ) { // phpcs:ignore WordPress.VIP.ValidatedSanitizedInput, WordPress.CSRF.NonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
			return $input;
		}

		$old_options = get_option( MYSTYLE_OPTIONS_NAME );
		$new_options = $old_options; // Start with the old options.

		$has_errors  = false;
		$msg_message = null;

		// ------------ process the new values ------------
		// API Key.
		$new_options['api_key'] = trim( $input['api_key'] );
		if ( ! preg_match( '/^[a-z0-9]*$/i', $new_options['api_key'] ) ) {
			$has_errors             = true;
			$msg_message            = 'Please enter a valid API Key.';
			$new_options['api_key'] = '';
		}

		// Secret.
		$new_options['secret'] = trim( $input['secret'] );
		if ( ! preg_match( '/^[a-z0-9]*$/i', $new_options['secret'] ) ) {
			$has_errors            = true;
			$msg_message           = 'Please enter a valid Secret.';
			$new_options['secret'] = '';
		}

		// Enable Flash.
		$new_options['enable_flash'] = ( isset( $input['enable_flash'] ) ) ? intval( $input['enable_flash'] ) : 0;
		if ( ! preg_match( '/^[01]$/', $new_options['enable_flash'] ) ) {
			$has_errors                  = true;
			$msg_message                 = 'Invalid HTML5 Customizer option';
			$new_options['enable_flash'] = 0;
		}

		// Hide Customize Page Title.
		$new_options['customize_page_title_hide'] = ( isset( $input['customize_page_title_hide'] ) ) ? intval( $input['customize_page_title_hide'] ) : 0;
		if ( ! preg_match( '/^[01]$/', $new_options['customize_page_title_hide'] ) ) {
			$has_errors                               = true;
			$msg_message                              = 'Invalid Hide Customize Page Title option';
			$new_options['customize_page_title_hide'] = 0;
		}

		// Show Add to Cart Button on Design Profile Pages.
		$new_options['design_profile_page_show_add_to_cart'] = ( isset( $input['design_profile_page_show_add_to_cart'] ) ) ? intval( $input['design_profile_page_show_add_to_cart'] ) : 0;
		if ( ! preg_match( '/^[01]$/', $new_options['design_profile_page_show_add_to_cart'] ) ) {
			$has_errors  = true;
			$msg_message = 'Show Add to Cart Button on Design Profile Pages option';
			$new_options['design_profile_page_show_add_to_cart'] = 1;
		}

		// Disable Viewport Rewrite.
		$new_options['customize_page_disable_viewport_rewrite'] = ( isset( $input['customize_page_disable_viewport_rewrite'] ) ) ? intval( $input['customize_page_disable_viewport_rewrite'] ) : 0;
		if ( ! preg_match( '/^[01]$/', $new_options['customize_page_disable_viewport_rewrite'] ) ) {
			$has_errors  = true;
			$msg_message = 'Disable Viewport Rewrite';
			$new_options['customize_page_disable_viewport_rewrite'] = 0;
		}

		// Form Integration Config.
		$new_options['form_integration_config'] = trim( $input['form_integration_config'] );

		// Enable Alternate Design Complete Redirect.
		$new_options['enable_alternate_design_complete_redirect'] = ( isset( $input['enable_alternate_design_complete_redirect'] ) ) ? intval( $input['enable_alternate_design_complete_redirect'] ) : 0;
		if ( ! preg_match( '/^[01]$/', $new_options['enable_alternate_design_complete_redirect'] ) ) {
			$has_errors  = true;
			$msg_message = 'Invalid Enable Alternate Design Complete Redirect option';
			$new_options['enable_alternate_design_complete_redirect'] = 0;
		}
			
		// Design image type thumb/web
		$new_options['image_type'] = isset($input['image_type']) && in_array($input['image_type'], array('thumbnail', 'web')) ? $input['image_type'] : 'thumbnail';

		// Design Profile Page product menu type.
		$new_options['design_profile_product_menu_type'] = trim( $input['design_profile_product_menu_type'] );

		// Alternate Design Tag/Collection title.
		$new_options['alternate_design_tag_collection_title'] = trim( $input['alternate_design_tag_collection_title'] );
			$new_options['enable_cdn_images'] = (isset($input['enable_cdn_images'])) ? intval($input['enable_cdn_images']) : 0;
			$new_options['cdn_base_url'] = trim($input['cdn_base_url']);
		// Alternate Design Complete Redirect URL.
		$new_options['alternate_design_complete_redirect_url'] = trim( $input['alternate_design_complete_redirect_url'] );

		if (
				( ! empty( $new_options['alternate_design_complete_redirect_url'] ) ) &&
				( false === filter_var( $new_options['alternate_design_complete_redirect_url'], FILTER_VALIDATE_URL ) )
		) {
			$has_errors  = true;
			$msg_message = 'Please enter a valid Alternate Design Complete Redirect URL.';
			$new_options['alternate_design_complete_redirect_url'] = '';
		}

		// Redirect URL Whitelist.
		$new_options['redirect_url_whitelist'] = trim( $input['redirect_url_whitelist'] );

		// Enable Configur8.
		$new_options['enable_configur8'] = ( isset( $input['enable_configur8'] ) ) ? intval( $input['enable_configur8'] ) : 0;
		if ( ! preg_match( '/^[01]$/', $new_options['enable_configur8'] ) ) {
			$has_errors                      = true;
			$msg_message                     = 'Invalid Enable Configur8 option';
			$new_options['enable_configur8'] = 0;
		}

		if ( $has_errors ) {
			add_settings_error(
				'MyStyleOptionsSaveMessage',
				esc_attr( 'settings_updated' ),
				$msg_message,
				'error'
			);
		}

		$new_options = apply_filters( 'mystyle_validate_options', $new_options, $input, $old_options );

		return $new_options;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Options_Page
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
