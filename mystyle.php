<?php

/*
Plugin Name: MyStyle
Plugin URI: http://www.mystyleplatform.com
Description: The MyStyle Custom Product Designer is a simple plugin that allows your customers to customize products in WooCommerce.
Version: 1.0.1
Author: MyStyle
Author URI: www.mystyleplatform.com
License: GPL v3

MyStyle Custom Product Designer
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
define( 'MYSTYLE_PATH', plugin_dir_path( __FILE__ ) );
define( 'MYSTYLE_INCLUDES', MYSTYLE_PATH . 'includes/' );
define( 'MYSTYLE_BASENAME', plugin_basename(__FILE__) );
define( 'MYSTYLE_URL', plugins_url( '/', __FILE__ ) );
define( 'MYSTYLE_ASSETS_URL', MYSTYLE_URL . 'assets/' );

// Include the optional config.php file
if( file_exists( MYSTYLE_PATH . 'config.php' ) ) {
    include_once( MYSTYLE_PATH . 'config.php');
}

if( ! defined('MYSTYLE_SERVER') ) { define( 'MYSTYLE_SERVER', 'http://api.ogmystyle.com/' ); }
if( ! defined('MYSTYLE_VERSION') ) { define( 'MYSTYLE_VERSION', '1.0.1' ); }

define( 'MYSTYLE_OPTIONS_NAME', 'mystyle_options' );
define( 'MYSTYLE_NOTICES_NAME', 'mystyle_notices' );
define( 'MYSTYLE_CUSTOMIZE_PAGEID_NAME', 'mystyle_customize_page_id' );

//includes
require_once( MYSTYLE_PATH . 'tests/qunit.php' );
require_once( MYSTYLE_INCLUDES . 'exceptions/class-mystyle-exception.php' );
require_once( MYSTYLE_INCLUDES . 'class-mystyle.php' );
require_once( MYSTYLE_INCLUDES . 'class-mystyle-options.php' );
require_once( MYSTYLE_INCLUDES . 'db/class-mystyle-entity.php' );
require_once( MYSTYLE_INCLUDES . 'db/class-mystyle-entitymanager.php' );
require_once( MYSTYLE_INCLUDES . 'entities/class-mystyle-design.php' );
require_once( MYSTYLE_INCLUDES . 'entities/class-mystyle-designmanager.php' );
require_once( MYSTYLE_INCLUDES . 'class-mystyle-api.php' );
require_once( MYSTYLE_INCLUDES . 'pages/class-mystyle-customize-page.php' );
require_once( MYSTYLE_INCLUDES . 'shortcodes/class-mystyle-customizer-shortcode.php' );

$mystyle = new MyStyle();

if( is_admin() ) {
    //---- ADMIN ----//
    //includes
    require_once( MYSTYLE_INCLUDES . 'admin/class-mystyle-install.php' );
    require_once( MYSTYLE_INCLUDES . 'admin/class-mystyle-admin.php' );
    require_once( MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-options-page.php' );
    require_once( MYSTYLE_INCLUDES . 'admin/help/help-dispatch.php' );
    require_once( MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-product.php' );
    require_once( MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-order.php' );

    //Plugin setup and registrations
    $mystyle_admin = new MyStyle_Admin();
    register_activation_hook( __FILE__, array( 'MyStyle_Admin', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'MyStyle_Admin', 'deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'MyStyle_Admin', 'uninstall' ) );

    //set up the options page
    $mystyle_options_page = new MyStyle_Options_Page();
    add_filter( 'contextual_help', 'mystyle_help_dispatch', 10, 3 );

    //hook into the WooCommerce admin
    $mystyle_woocommerce_admin_product = new MyStyle_WooCommerce_Admin_Product();
    $mystyle_woocommerce_admin_order = new MyStyle_WooCommerce_Admin_Order();

    //load qunit
    if( ( defined('MYSTYLE_LOAD_QUNIT') ) && ( MYSTYLE_LOAD_QUNIT == true ) ) {
        add_action( 'admin_footer', 'mystyle_load_qunit' );
    }

} else {
    //---- FRONT END ----//
    require_once( MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php' );
    require_once( MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php' );

    $mystyle_frontend = new MyStyle_FrontEnd();
    $mystyle_handoff = new MyStyle_Handoff();
}

//Register shortcodes
add_shortcode( 'mystyle_customizer', array( 'MyStyle_Customizer_Shortcode', 'output' ) );
