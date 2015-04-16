<?php

require_once(MYSTYLE_PATH . 'admin/admin-class.php');

/**
 * The AdminTest class includes tests for testing the MyStyle_Admin class.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class AdminTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_admin = new MyStyle_Admin();
        
        global $wp_filter;

        //Assert that the settings link is registered.
        $function_names = get_function_names(
                            $wp_filter['plugin_action_links_' . MYSTYLE_BASENAME]
                          );
        $this->assertContains('mystyle_settings_link', $function_names);
        
        //Assert that the init function is registered.
        $function_names = get_function_names($wp_filter['admin_init']);
        $this->assertContains('mystyle_admin_init', $function_names);
        
        //Assert that the admin notices function is registered.
        $function_names = get_function_names($wp_filter['admin_notices']);
        $this->assertContains('mystyle_admin_notices', $function_names);        
    }
    
    /**
     * Test the mystyle_settings_link function.
     */    
    public function test_mystyle_settings_link() {
        $links = array();
        $mystyle_admin = new MyStyle_Admin();
        $links = $mystyle_admin->mystyle_settings_link($links);
        
        $this->assertEquals(count($links), 1);
    }
    
    /**
     * Test the mystyle_admin_init function.
     */    
    public function test_mystyle_admin_init() {
        $mystyle_admin = new MyStyle_Admin();
        
        //Set the version to something old/incorrect
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $options['version'] = 'old_version';
        update_option(MYSTYLE_OPTIONS_NAME, $options);
        
        //Run the function.
        $mystyle_admin->mystyle_admin_init();
        
        //Assert that the version was updated
        $new_options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $this->assertEquals($new_options['version'], MYSTYLE_VERSION);
        
        //Assert that a notice of the upgrade was registered.
        ob_start();
        $mystyle_admin->mystyle_admin_notices();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains("Upgraded version from", $outbound);
        
        //Assert that the admin stylesheet is registered
        global $wp_styles;
        $this->assertContains('myStyleAdminStylesheet', serialize($wp_styles));
    }
    
    /**
     * Test the mystyle_admin_notices function.
     */    
    public function test_mystyle_admin_notices() {
        $mystyle_admin = new MyStyle_Admin();
        
        //assert that a notice was registered
        ob_start();
        $mystyle_admin->mystyle_admin_notices();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains("MyStyle", $outbound);
    }
    
     /**
     * Test the uninstall function.
     */    
    public function test_uninstall() {
        $mystyle_admin = new MyStyle_Admin();
        
        //init the plugin so that we can then uninstall it
        $mystyle_admin->mystyle_admin_init();
        
        //assert that there are options
        $options = get_option(MYSTYLE_OPTIONS_NAME, array());
        $this->assertNotEmpty($options);
        
        //uninstall the plugin
        $mystyle_admin->mystyle_uninstall();
        
        //assert that the options are now empty
        $options_new = get_option(MYSTYLE_OPTIONS_NAME, array());
        $this->assertEmpty($options_new);
    }
    
}

