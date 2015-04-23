<?php

/**
 * Class for rendering the MyStyle Options/Settings page within the WordPress
 * Administrator.
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyle_Options_Page {
    
    /**
     * Constructor, constructs the options page and adds it to the Settings
     * menu.
     */
    function __construct() {
        add_action('admin_menu', array( &$this, 'mystyle_add_options_page_to_menu' ));
        add_action('admin_init', array( &$this, 'mystyle_options_init' ));
    }
    
    
    /**
     * Function to render the MyStyle options page.
     */
    function mystyle_options_render_page() {
    ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div><h2>MyStyle Settings</h2>
            <form action="options.php" method="post">
                <?php settings_fields('mystyle_options'); ?>
                <?php do_settings_sections('mystyle'); ?>

                <p class="submit">
                    <input type="submit" name="Submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
                </p>
            </form>
            <?php if(mystyle_are_keys_installed()) { ?>
                <br/>
                <ul>
                    <li>Go to <a href="https://www.mystyleplatform.com" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a>.</li>
                </ul>
            <?php } //end if ?>
        </div>
    <?php
    }

    /**
     * Function to add the options page to the settings menu.
     */
    function mystyle_add_options_page_to_menu() {
        global $mystyle_hook;
        $mystyle_hook = add_options_page('MyStyle Settings', 'MyStyle', 'manage_options', 'mystyle', array( &$this, 'mystyle_options_render_page') );
    }

    /**
     * Function to render the text for the access section.
     */
    function mystyle_options_render_access_section_text() {
    ?>
        <p>
            To use MyStyle, you will need a MyStyle API Key and Secret.  To get
            your MyStyle API Key and Secret, log in or register at 
            <a href="http://www.mystyleplatform.com" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a>.
        </p>
    <?php
    }

    /**
     * Function to render the API Key field and description
     */
    function mystyle_options_render_api_key() {
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $api_key = (array_key_exists('api_key', $options)) ? $options['api_key'] : "";
     ?>
        <input id="mystyle_api_key" name="mystyle_options[api_key]" size="5" type="text" value="<?php echo $api_key ?>" />
        <p class="description">
            You must enter a valid MyStyle API Key here. If you need an
            API Key, you can create one
            <a href="https://www.mystyleplatform.com" target="_blank" title="MyStyle Signup">here</a>.
        </p>
    <?php
    }

    /**
     * Function to render the Secret field and description
     */
    function mystyle_options_render_secret() {
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $secret = (array_key_exists('secret', $options)) ? $options['secret'] : "";
     ?>
        <input id="mystyle_secret" name="mystyle_options[secret]" size="5" type="text" value="<?php echo $secret ?>" />
        <p class="description">
            You must enter a valid MyStyle Secret here. If you need a MyStyle 
            Secret, you can create one
            <a href="https://www.mystyleplatform.com" target="_blank" title="MyStyle Signup">here</a>.
        </p>
    <?php
    }
    
    /**
     * Function to initialize the MyStyle options page.
     */
    function mystyle_options_init() {
        register_setting('mystyle_options', MYSTYLE_OPTIONS_NAME, array( &$this, 'mystyle_options_validate' ) );
        add_settings_section(
                'mystyle_options_access_section',
                'Access Settings',
                array( &$this,'mystyle_options_render_access_section_text'),
                'mystyle'
        );
        add_settings_field(
                'api_key', 
                'API Key', 
                array(&$this, 'mystyle_options_render_api_key'),
                'mystyle', 
                'mystyle_options_access_section'
        );
        add_settings_field(
                'secret', 
                'Secret', 
                array(&$this, 'mystyle_options_render_secret'),
                'mystyle', 
                'mystyle_options_access_section'
        );
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
    function mystyle_options_validate($input) {
        $old_options = get_option(MYSTYLE_OPTIONS_NAME);
        $new_options = $old_options;  //start with the old options.
        
        $has_errors = false;
        $msg_type = null;
        $msg_message = null;
        
        //------------ process the new values ------------
        
        //API Key
        $new_options['api_key'] = trim($input['api_key']);
        if(!preg_match('/^[a-z0-9]*$/i', $new_options['api_key'])) {
            $has_errors = true;
            $msg_type = 'error';
            $msg_message = 'Please enter a valid API Key.';
            $new_options['api_key'] = '';
        }
        
        //Secret
        $new_options['secret'] = trim($input['secret']);
        if(!preg_match('/^[a-z0-9]*$/i', $new_options['secret'])) {
            $has_errors = true;
            $msg_type = 'error';
            $msg_message = 'Please enter a valid Secret.';
            $new_options['secret'] = '';
        }
        
        if(!$has_errors) {
            $msg_type = 'updated';
            $msg_message = 'Settings saved.';
        }
        
        add_settings_error(
            'MyStyleOptionsSaveMessage',
            esc_attr('settings_updated'),
            $msg_message,
            $msg_type
        );
        
        return $new_options;
    }
}