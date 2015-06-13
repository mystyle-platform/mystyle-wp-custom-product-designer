<?php

require_once(MYSTYLE_INCLUDES . 'admin/class-mystyle-admin.php');
require_once(MYSTYLE_INCLUDES . 'exceptions/class-mystyle-exception.php');

/**
 * The MystyleAdminTest class includes tests for testing the MyStyle_Admin 
 * class.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyleAdminTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_admin = new MyStyle_Admin();
        
        global $wp_filter;

        //Assert that the settings link is registered.
        $function_names = get_function_names(
                            $wp_filter[ 'plugin_action_links_' . MYSTYLE_BASENAME ]
                          );
        $this->assertContains( 'add_settings_link', $function_names );
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['admin_init'] );
        $this->assertContains( 'admin_init', $function_names );
        
        //Assert that the admin notices function is registered.
        $function_names = get_function_names( $wp_filter['admin_notices'] );
        $this->assertContains( 'admin_notices', $function_names );        
    }
    
    /**
     * Test the add_settings_link function.
     */    
    public function test_mystyle_settings_link() {
        $links = array();
        $mystyle_admin = new MyStyle_Admin();
        $links = $mystyle_admin->add_settings_link( $links );
        
        $this->assertEquals( count( $links ), 1 );
    }
    
    /**
     * Test the admin_init function.
     */    
    public function admin_init() {
        $mystyle_admin = new MyStyle_Admin();
        
        //Set the version to something old/incorrect
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $options['version'] = 'old_version';
        update_option( MYSTYLE_OPTIONS_NAME, $options );
        
        //Run the function.
        $mystyle_admin->admin_init();
        
        //Assert that the version was updated
        $new_options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $this->assertEquals( $new_options['version'], MYSTYLE_VERSION );
        
        //Assert that a notice of the upgrade was registered.
        ob_start();
        $mystyle_admin->admin_notices();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'Upgraded version from', $outbound );
    }
    
    /**
     * Test the admin_notices function.
     */    
    public function test_admin_notices() {
        $mystyle_admin = new MyStyle_Admin();
        
        //assert that a notice was registered
        ob_start();
        $mystyle_admin->admin_notices();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'MyStyle', $outbound );
    }
    
     /**
     * Test the activate function.
     */    
    public function test_activate() {
        $mystyle_admin = new MyStyle_Admin();
        
        $mystyle_admin->activate();
        
        $customize_page_id = MyStyle_Customize_Page::get_id();
        
        //assert that the customize page was created
        $this->assertNotNull($customize_page_id);
    }
    
    /**
     * Test the deactivate function.
     */    
    public function test_deactivate() {
        $mystyle_admin = new MyStyle_Admin();
        
        //activate the plugin so that we can then deactivate it
        $mystyle_admin->activate();
        
        $mystyle_admin->deactivate();
        
        //Assert that Customize page remains.
        $this->assertTrue(MyStyle_Customize_Page::exists());
    }
        
     /**
     * Test the uninstall function.
     */    
    public function test_uninstall() {
        $mystyle_admin = new MyStyle_Admin();
        
        //init the plugin so that we can then uninstall it
        $mystyle_admin->admin_init();
        
        //assert that there are options
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $this->assertNotEmpty( $options );
        
        //uninstall the plugin
        $mystyle_admin->uninstall();
        
        //assert that the options are still there
        $options_new = get_option( MYSTYLE_OPTIONS_NAME, array() );
        $this->assertNotEmpty( $options_new );
    }
    
}
