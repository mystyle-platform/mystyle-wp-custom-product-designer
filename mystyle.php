<?php

/*
Plugin Name: MyStyle
Plugin URI: http://www.mystyleplatform.com
Description: The MyStyle WordPress Plugin is a simple plugin that allows your customers to customize products in WooCommerce.
Version: 0.2.1
Author: MyStyle
Author URI: www.mystyleplatform.com
License: GPL v3

MyStyle WordPress Plugin
Copyright (c) 2015 MyStyle <contact@mystyleplatform.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @package MyStyle
 * @since 0.1.0
 */

//define constants
define('MYSTYLE_PATH', plugin_dir_path( __FILE__ ));
define('MYSTYLE_BASENAME', plugin_basename(__FILE__));

// Include the optional config.php file
if(file_exists(MYSTYLE_PATH . 'config.php')) {
    include_once(MYSTYLE_PATH . 'config.php');
}

if(!defined('MYSTYLE_ADSERVER')) { define('MYSTYLE_SERVER', 'www.mystyleplatform.com/api'); }
if(!defined('MYSTYLE_VERSION')) { define('MYSTYLE_VERSION', '0.2.1'); }

define('MYSTYLE_OPTIONS_NAME', 'mystyle_options');
define('MYSTYLE_NOTICES_NAME', 'mystyle_notices');
define('MYSTYLE_CUSTOMIZE_PAGEID_NAME', 'mystyle_customize_page_id');

//includes
require_once(MYSTYLE_PATH . 'functions.php');
require_once(MYSTYLE_PATH . 'tests/qunit.php');
require_once(MYSTYLE_PATH . 'pages/class-customize-page.php');

if(is_admin()) {
    //---- ADMIN ----//
    //includes
    require_once(MYSTYLE_PATH . 'admin/admin-class.php');
    require_once(MYSTYLE_PATH . 'admin/pages/class-options-page.php');
    require_once(MYSTYLE_PATH . 'admin/help/help-dispatch.php');
    
    //Plugin setup and registrations
    $mystyle_admin = new MyStyle_Admin();
    register_activation_hook(__FILE__, array('MyStyle_Admin', 'mystyle_activation'));
    register_deactivation_hook(__FILE__, array('MyStyle_Admin', 'mystyle_deactivation'));
    register_uninstall_hook(__FILE__, array('MyStyle_Admin', 'mystyle_uninstall'));
    
    //set up the options page 
    $mystyle_options_page = new MyStyle_Options_Page();
    add_filter('contextual_help', 'mystyle_help_dispatch', 10, 3);
    
    //load qunit
    if( (defined('MYSTYLE_LOAD_QUNIT')) && (MYSTYLE_LOAD_QUNIT == true) ) {
        add_action('admin_footer', 'mystyle_load_qunit');
    }

} else {
    //---- FRONT END ----//
    require_once(MYSTYLE_PATH . 'frontend/frontend-class.php');
    $mystyle_frontend = new MyStyle_FrontEnd();
}
