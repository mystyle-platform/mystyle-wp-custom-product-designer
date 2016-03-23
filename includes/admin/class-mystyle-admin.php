<?php

/**
 * MyStyle Admin class.
 * The MyStyle Admin class sets up and controls the MyStyle Plugin administrator
 * interace.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyle_Admin {

    /**
     * Constructor, constructs the admin class and registers hooks.
     * menu.
     */
    public function __construct() {
        add_filter('plugin_action_links_' . MYSTYLE_BASENAME, array(&$this, 'add_settings_link'));

        add_action('admin_init', array(&$this, 'admin_init'));
    }

    /**
     * Init the mystyle admin
     */
    function admin_init() {
        error_log("admin_init");
        //Add the MyStyle admin stylesheet to the WP admin head
        wp_register_style('myStyleAdminStylesheet', MYSTYLE_ASSETS_URL . 'css/admin.css');
        wp_enqueue_style('myStyleAdminStylesheet');

        //Add the MyStyle admin js file to the WP admin head
        wp_register_script('myStyleAdminJavaScript', MYSTYLE_ASSETS_URL . 'js/admin.js');
        wp_enqueue_script('myStyleAdminJavaScript');

        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $data_version = ( array_key_exists('version', $options) ) ? $options['version'] : null;
        if ($data_version != MYSTYLE_VERSION) {
            $options['version'] = MYSTYLE_VERSION;
            update_option(MYSTYLE_OPTIONS_NAME, $options);
            if (!is_null($data_version)) {  //skip if not an upgrade
                //do any necessary version data upgrades here
                $upgrade_notice = MyStyle_Notice::create('notify_upgrade', 'Upgraded version from ' . $data_version . ' to ' . MYSTYLE_VERSION . '.');
                mystyle_notice_add_to_queue($upgrade_notice);
            }
        }

        // add bootstrap
        /*
         *  Bootstrap Styles and scripts
         */
        wp_register_style('bootstrap.min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap.min');

        //Bootstrap Scripts
        wp_register_script('bootstrap.min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
        wp_enqueue_script('bootstrap.min');
        ?>

        <!-- admin / bootstrap style overrides -->
        <style>
            /* bootstrap has some fade class conflict in the admin.. overriding it */
            #wpbody .fade {opacity: 1; transition-duration: 250ms;}

            .arrow-toggle .icon-arrow-down,
            .arrow-toggle.collapsed .icon-arrow-up {
                display: inline-block;
            }
            .arrow-toggle.collapsed .icon-arrow-down,
            .arrow-toggle .icon-arrow-up {
                display: none;
            }
        </style>

        <?php
    }

    /**
     * Add settings link on plugin page
     * @param array $links An array of existing links for the plugin
     * @return array The new array of links
     */
    public static function add_settings_link($links) {
        $settings_link = '<a href="options-general.php?page=mystyle">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

}
