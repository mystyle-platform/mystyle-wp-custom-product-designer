<?php

/**
 * Class for rendering the MyStyle Options/Settings page within the WordPress
 * Administrator.
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyle_Options_Page {

    /**
     * Singleton instance
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
    }

    /**
     * Function to initialize the MyStyle options page.
     */
    public function admin_init() {
        //$sanitize_callback = array( &$this, 'validate' ); //A callback function that sanitizes the option's value.
        register_setting( 'mystyle_options', MYSTYLE_OPTIONS_NAME, array( &$this, 'validate' ) );
        // ************** ACCOUNT SETTINGS SECTION ******************//
        add_settings_section(
                'mystyle_options_access_section',
                'Account Settings',
                array( &$this, 'render_access_section_text' ),
                'mystyle_account_settings'
        );
        add_settings_field(
                'api_key',
                'API Key',
                array( &$this, 'render_api_key' ),
                'mystyle_account_settings',
                'mystyle_options_access_section'
        );
        add_settings_field(
                'secret',
                'Secret',
                array( &$this, 'render_secret' ),
                'mystyle_account_settings',
                'mystyle_options_access_section'
        );

        // ************** ADVANCED SETTINGS SECTION ******************//
        add_settings_section(
                'mystyle_options_advanced_section',
                'Advanced Settings',
                array( &$this, 'render_advanced_section_text' ),
                'mystyle_advanced_settings'
        );

        /* ENABLE FLASH SETTING */
        add_settings_field(
                'enable_flash',
                'Enable Flash (Not Recommended)',
                array( &$this, 'render_enable_flash' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );

        /* HIDE PAGE TITLE ON CUSTOMIZE PAGE */
        add_settings_field(
                'customize_page_title_hide',
                'Hide Customize Page Title',
                array( &$this, 'render_hide_customize_title' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );
        
        /* SHOW ADD TO CART BUTTON ON DESIGN PROFILE PAGES */
        add_settings_field(
                'design_profile_page_show_add_to_cart',
                'Show Add to Cart Button on Design Profile Pages',
                array( &$this, 'render_design_profile_page_show_add_to_cart' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );
        
        /* DISABLE_VIEWPORT_REWRITE */
        add_settings_field(
                'customize_page_disable_viewport_rewrite',
                'Disable Viewport Rewrite',
                array( &$this, 'render_customize_page_disable_viewport_rewrite' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );

        /* FORM INTEGRATION CONFIG */
        add_settings_field(
                'form_integration_config',
                'Form Integration Config',
                array( &$this, 'render_form_integration_config' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );
        
        /* ENABLE_ALTERNATE_DESIGN_COMPLETE_REDIRECT */
        add_settings_field(
                'enable_alternate_design_complete_redirect',
                'Enable Alternate Design Complete Redirect',
                array( &$this, 'render_enable_alternate_design_complete_redirect' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );
        
        /* ALTERNATE_DESIGN_COMPLETE_REDIRECT_URL */
        add_settings_field(
                'alternate_design_complete_redirect_url',
                'Alternate Design Complete Redirect URL',
                array( &$this, 'render_alternate_design_complete_redirect_url' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );
        
        /* REDIRECT URL WHITELIST */
        add_settings_field(
                'redirect_url_whitelist',
                'Redirect URL Whitelist',
                array( &$this, 'render_redirect_url_whitelist' ),
                'mystyle_advanced_settings',
                'mystyle_options_advanced_section'
        );

        // ************** TOOLS SECTION ******************//
        add_settings_section(
                'mystyle_options_tools_section',
                'MyStyle Tools',
                array( &$this, 'render_tools_section_text' ),
                'mystyle_tools'
        );
        if ( ( ! empty( $_GET['action'] ) ) && ( $_SERVER['REQUEST_METHOD'] == 'POST' ) ) {
            switch ( $_GET['action'] ) {
                case 'fix_customize_page' :

                    //Attempt the fix
                    $message = MyStyle_Customize_Page::fix();

                    //Post Fix Notice
                    $fix_notice = MyStyle_Notice::create( 'notify_fix', $message );
                    mystyle_notice_add_to_queue( $fix_notice );

                    break;
                case 'fix_design_profile_page' :

                    //Attempt the fix
                    $message = MyStyle_Design_Profile_Page::fix();

                    //Post Fix Notice
                    $fix_notice = MyStyle_Notice::create( 'notify_fix', $message );
                    mystyle_notice_add_to_queue( $fix_notice );

                    break;
            }
        }
    }

    /**
     * Function to add the options page to the settings menu.
     */
    public function add_page_to_menu() {
        global $mystyle_hook;
        $mystyle_hook = 'mystyle';

        add_menu_page('MyStyle', 'MyStyle', 'manage_options', $mystyle_hook, array( &$this, 'render_page' ), MYSTYLE_ASSETS_URL . '/images/mystyle-icon.png', '56' );
        add_submenu_page( $mystyle_hook, 'Settings', 'Settings', 'manage_options', $mystyle_hook );
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
                <div class="mystyle-admin-box">
                    <?php do_settings_sections( 'mystyle_account_settings' ); ?>
                </div>
                <br/>
                <div class="mystyle-admin-box">
                    <?php do_settings_sections( 'mystyle_advanced_settings' ); ?>
                </div>
                <p class="submit">
                    <input type="submit" name="Submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
                </p>
            </form>
            <br/>
            <div class="mystyle-admin-box">
                <?php do_settings_sections( 'mystyle_tools' ); ?>
                <form action="admin.php?page=mystyle&action=fix_customize_page" method="post">
                    <p class="submit">
                        <input type="submit" name="Submit" id="submit_fix_customize_page" class="button button-primary" value="<?php esc_attr_e('Fix Customize Page'); ?>" /><br/>
                        <small>This tool will attempt to fix the Customize page. This may involve creating, recreating, or restoring the page.</small>
                    </p>
                </form>
                <form action="admin.php?page=mystyle&action=fix_design_profile_page" method="post">
                    <p class="submit">
                        <input type="submit" name="Submit" id="submit_fix_design_profile_page" class="button button-primary" value="<?php esc_attr_e('Fix Design Profile Page'); ?>" /><br/>
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
            To use the <a href="http://www.mystyleplatform.com">MyStyle</a> customizer,
            <a href="http://www.mystyleplatform.com/?ref=wpcpd_settings" target="_blank" title="mystyleplatform.com">sign up for MyStyle</a> and then get your own MyStyle License
            <br/>Once you have a license, enter your API Key and Secret below.
        </p>
    <?php
    }

    /**
     * Function to render the API Key field and description
     */
    public function render_api_key() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $api_key = ( array_key_exists('api_key', $options) ) ? $options['api_key'] : '';
     ?>
        <input id="mystyle_api_key" name="mystyle_options[api_key]" size="5" type="text" value="<?php echo $api_key ?>" />
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
        $secret = ( array_key_exists( 'secret', $options ) ) ? $options['secret'] : '';
     ?>
        <input id="mystyle_secret" name="mystyle_options[secret]" size="27" type="text" value="<?php echo $secret ?>" />
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
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $enable_flash = ( array_key_exists( 'enable_flash', $options ) ) ? $options['enable_flash'] : 0;
     ?>

        <label class="description">
            <input type="checkbox" id="mystyle_enable_flash" name="mystyle_options[enable_flash]" value="1" <?php echo checked( 1, $enable_flash, false ) ?> />
            &nbsp; Use the Flash version of the MyStyle customizer (when Flash is available).
        </label>
    <?php

    }


    /**
     * Function to render the Hide Customize Page Title option and checkbox.
     */
    public function render_hide_customize_title() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $customize_page_title_hide = ( array_key_exists( 'customize_page_title_hide', $options ) ) ? $options['customize_page_title_hide'] : 0;
     ?>

        <label class="description">
            <input type="checkbox" id="customize_page_title_hide" name="mystyle_options[customize_page_title_hide]" value="1" <?php echo checked( 1, $customize_page_title_hide, false ) ?> />
            &nbsp; Hide the page title on the Customize page.
        </label>
    <?php

    }
    
    /**
     * Function to render the Show Add to Cart Button on Design Profile Pages
     * option and checkbox.
     */
    public function render_design_profile_page_show_add_to_cart() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $design_profile_page_show_add_to_cart = ( array_key_exists( 'design_profile_page_show_add_to_cart', $options ) ) ? $options['design_profile_page_show_add_to_cart'] : 1;
        //echo $design_profile_page_show_add_to_cart;
        //exit();
     ?>

        <label class="description">
            <input type="checkbox" id="design_profile_page_show_add_to_cart" name="mystyle_options[design_profile_page_show_add_to_cart]" value="1" <?php echo checked( 1, $design_profile_page_show_add_to_cart, false ) ?> />
            &nbsp; Show the Add to Cart button on Design Profile pages.
        </label>
    <?php

    }
    
    /**
     * Function to render the Disable Viewport Rewrite option and checkbox.
     */
    public function render_customize_page_disable_viewport_rewrite() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $customize_page_disable_viewport_rewrite = ( array_key_exists( 'customize_page_disable_viewport_rewrite', $options ) ) ? $options['customize_page_disable_viewport_rewrite'] : 0;
     ?>

        <label class="description">
            <input 
                type="checkbox" 
                id="customize_page_disable_viewport_rewrite" 
                name="mystyle_options[customize_page_disable_viewport_rewrite]" 
                value="1" 
                <?php echo checked( 1, $customize_page_disable_viewport_rewrite, false ) ?> 
            />
            &nbsp; The MyStyle plugin will rewrite the viewport tag on the
            Customize page (only) for optimal display of the MyStyle Customizer.
            Check this box if you would prefer to control the viewport yourself.
        </label>
    <?php

    }

    /**
     * Function to render the form integration config field
     */
    public function render_form_integration_config() {

        $options = get_option( MYSTYLE_OPTIONS_NAME, array() ); // get WP Options table Key of this option
        $current_val = ( array_key_exists( 'mystyle_form_integration_config', $options ) ) ? $options['mystyle_form_integration_config'] : '';
     ?>
        <textarea id="mystyle_form_integration_config" name="mystyle_options[mystyle_form_integration_config]" ><?php echo $current_val; ?></textarea>
        <p class="description">Configure advanced form integrations here (not recommended)</p>
    <?php
    }
    
    /**
     * Function to render the Enable Alternate Design Complete Redirect option
     * and checkbox.
     */
    public function render_enable_alternate_design_complete_redirect() {
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $enable_alternate_design_complete_redirect = ( array_key_exists( 'enable_alternate_design_complete_redirect', $options ) ) ? $options['enable_alternate_design_complete_redirect'] : 0;
     ?>

        <label class="description">
            <input type="checkbox" id="enable_alternate_design_complete_redirect" name="mystyle_options[enable_alternate_design_complete_redirect]" value="1" <?php echo checked( 1, $enable_alternate_design_complete_redirect, false ) ?> />
            &nbsp; Enable the alternate design complete redirect.
        </label>
    <?php

    }

    /**
     * Function to render the alternate design complete redirect url field.
     */
    public function render_alternate_design_complete_redirect_url() {

        $options = get_option( MYSTYLE_OPTIONS_NAME, array() ); // get WP Options table Key of this option
        $current_val = ( array_key_exists( 'alternate_design_complete_redirect_url', $options ) ) ? $options['alternate_design_complete_redirect_url'] : '';
     ?>
        <input id="mystyle_alternate_design_complete_redirect_url" name="mystyle_options[alternate_design_complete_redirect_url]" size="60" type="text" value="<?php echo $current_val ?>" />
        <p class="description">Specify an alternate URL to redirect to after the user completes their design. By default, the user will be redirected to the cart.</p>
    <?php
    }
    
    /**
     * Function to render the redirect url whitelist field
     */
    public function render_redirect_url_whitelist() {

        $options = get_option( MYSTYLE_OPTIONS_NAME, array() ); // get WP Options table Key of this option
        $current_val = ( array_key_exists( 'redirect_url_whitelist', $options ) ) ? $options['redirect_url_whitelist'] : '';
     ?>
        <textarea id="mystyle_redirect_url_whitelist" name="mystyle_options[redirect_url_whitelist]" ><?php echo $current_val; ?></textarea>
        <p class="description">White list domains that can be redirected to (one per line, ex: "www.example.com"). Contact MyStyle for details.</p>
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
     * @param array $input The submitted values
     * @return array Returns the new options to be stored in the database.
     */
    public function validate( $input ) {
        
        //Return without doing any validation if a tools/action button pressed
        if ( ( ! empty( $_GET['action'] ) ) && ( $_SERVER['REQUEST_METHOD'] == 'POST' ) ) {
            return $input;
        }
        
        $old_options = get_option( MYSTYLE_OPTIONS_NAME );
        $new_options = $old_options;  //start with the old options.

        $has_errors = false;
        $msg_message = null;

        //------------ process the new values ------------

        //API Key
        $new_options['api_key'] = trim( $input['api_key'] );
        if( ! preg_match( '/^[a-z0-9]*$/i', $new_options['api_key'] ) ) {
            $has_errors = true;
            $msg_message = 'Please enter a valid API Key.';
            $new_options['api_key'] = '';
        }

        //Secret
        $new_options['secret'] = trim( $input['secret'] );
        if( ! preg_match( '/^[a-z0-9]*$/i', $new_options['secret'] ) ) {
            $has_errors = true;
            $msg_message = 'Please enter a valid Secret.';
            $new_options['secret'] = '';
        }

        //Enable Flash
        $new_options['enable_flash'] = ( isset( $input['enable_flash'] ) ) ? intval( $input['enable_flash'] ) : 0;
        if( ! preg_match( '/^[01]$/', $new_options['enable_flash'] ) ) {
            $has_errors = true;
            $msg_message = 'Invalid HTML5 Customizer option';
            $new_options['enable_flash'] = 0;
        }
        
        //Hide Customize Page Title
        $new_options['customize_page_title_hide'] = ( isset( $input['customize_page_title_hide'] ) ) ? intval( $input['customize_page_title_hide'] ) : 0;
        if( ! preg_match( '/^[01]$/', $new_options['customize_page_title_hide'] ) ) {
            $has_errors = true;
            $msg_message = 'Invalid Hide Customize Page Title option';
            $new_options['customize_page_title_hide'] = 0;
        }
        
        //Show Add to Cart Button on Design Profile Pages
        $new_options['design_profile_page_show_add_to_cart'] = ( isset( $input['design_profile_page_show_add_to_cart'] ) ) ? intval( $input['design_profile_page_show_add_to_cart'] ) : 0;
        if( ! preg_match( '/^[01]$/', $new_options['design_profile_page_show_add_to_cart'] ) ) {
            $has_errors = true;
            $msg_message = 'Show Add to Cart Button on Design Profile Pages option';
            $new_options['design_profile_page_show_add_to_cart'] = 1;
        }
        
        //Disable Viewport Rewrite
        $new_options['customize_page_disable_viewport_rewrite'] = ( isset( $input['customize_page_disable_viewport_rewrite'] ) ) ? intval( $input['customize_page_disable_viewport_rewrite'] ) : 0;
        if( ! preg_match( '/^[01]$/', $new_options['customize_page_disable_viewport_rewrite'] ) ) {
            $has_errors = true;
            $msg_message = 'Disable Viewport Rewrite';
            $new_options['customize_page_disable_viewport_rewrite'] = 0;
        }

        // Form Integration Config
        $new_options['mystyle_form_integration_config'] = trim( $input['mystyle_form_integration_config'] );
        // example valdation (not needed)
        /*if( !preg_match( '/^[a-z0-9]*$/i', $new_options['mystyle_form_integration_config'] ) ) {
            $has_errors = true;
            $msg_type = 'error';
            $msg_message = 'Please enter a valid API Key.';
            $new_options['mystyle_form_integration_config'] = '';
        }*/
        
        //Enable Alternate Design Complete Redirect.
        $new_options['enable_alternate_design_complete_redirect'] = ( isset( $input['enable_alternate_design_complete_redirect'] ) ) ? intval( $input['enable_alternate_design_complete_redirect'] ) : 0;
        if( ! preg_match( '/^[01]$/', $new_options['enable_alternate_design_complete_redirect'] ) ) {
            $has_errors = true;
            $msg_message = 'Invalid Enable Alternate Design Complete Redirect option';
            $new_options['enable_alternate_design_complete_redirect'] = 0;
        }
        
        //Alternate Design Complete Redirect URL
        $new_options['alternate_design_complete_redirect_url'] = trim( $input['alternate_design_complete_redirect_url'] );
        if( 
            ( ! empty( $new_options['alternate_design_complete_redirect_url'] ) ) &&
            (filter_var( $new_options['alternate_design_complete_redirect_url'], FILTER_VALIDATE_URL ) == false )
          ) {
            $has_errors = true;
            $msg_message = 'Please enter a valid Alternate Design Complete Redirect URL.';
            $new_options['alternate_design_complete_redirect_url'] = '';
        }
        
        // Redirect URL Whitelist
        $new_options['redirect_url_whitelist'] = trim( $input['redirect_url_whitelist'] );

        if( $has_errors ) {
            add_settings_error(
                'MyStyleOptionsSaveMessage',
                esc_attr( 'settings_updated' ),
                $msg_message,
                'error'
            );
        }

        return $new_options;
    }
    
    /**
     * Get the singleton instance
     * @return MyStyle_Addons_Page
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}