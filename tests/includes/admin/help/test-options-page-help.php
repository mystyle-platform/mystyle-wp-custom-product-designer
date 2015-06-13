<?php

require_once(MYSTYLE_INCLUDES . 'admin/help/options-page-help.php');

/**
 * The OptionsPageHelpTest class includes tests for testing the functions in the
 * options-page-help.php file.
 *
 * @package MyStyle
 * @since 0.1.0
 */
class OptionsPageHelpTest extends WP_UnitTestCase {
    
    /**
     * Test the mystyle_options_page_help function.
     */
    public function test_mystyle_options_page_help() {
        //set up the variables
        $contextual_help = '';
        $mystyle_hook = 'mock-hook';
        $screen_id = $mystyle_hook;
        $screen = WP_Screen::get( $mystyle_hook );
        
        //run the function
        mystyle_options_page_help( $contextual_help, $screen_id, $screen );
        
        //Asset that the MyStyle help is now in the screen.
        $this->assertContains( 'MyStyle Custom Product Designer Help', serialize( $screen ) );
    }
    
}

