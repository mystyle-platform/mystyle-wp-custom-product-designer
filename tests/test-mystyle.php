<?php

/**
 * The MyStyleTest class includes tests for testing the main mystyle.php file
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyleTest extends WP_UnitTestCase {

    /**
     * Assert that the expected constants are declared and accessible.
     */    
    function testConstants() {
        $this->assertNotEmpty(MYSTYLE_PATH);
        $this->assertNotEmpty(MYSTYLE_INCLUDES);
        $this->assertNotEmpty(MYSTYLE_BASENAME);
        $this->assertNotEmpty(MYSTYLE_SERVER);
        $this->assertNotEmpty(MYSTYLE_VERSION);
        $this->assertNotEmpty(MYSTYLE_OPTIONS_NAME);
        $this->assertNotEmpty(MYSTYLE_NOTICES_NAME);
        $this->assertNotEmpty(MYSTYLE_CUSTOMIZE_PAGEID_NAME);
    }

    /**
     * Assert that the mystyle_customizer shortcode is registered
     */    
    function testCustomizerShortcodeIsRegistered() {
        global $shortcode_tags;

        $this->assertArrayHasKey('mystyle_customizer', $shortcode_tags);
    }
    
    /**
     * Assert that the mystyle_customizer shortcode is registered
     */    
    function testMyStyleAdminActivationHookIsRegistered() {
        //$screen_id = $mystyle_hook;
        $screen = WP_Screen::set_current_screen('widgets');
        $has_filter = has_filter('contextual_help', 'mystyle_help_dispatch');

        $this->assertTrue($has_filter);
    }
    
}

