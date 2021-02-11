<?php
/**
 * The MyStyleHelpTest class includes tests for testing the functions in the
 * MyStyle_Help class.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/help/class-mystyle-help.php';

/**
 * MyStyleHelpTest class.
 */
class MyStyleHelpTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global array $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		// Call the constructor.
		$mystyle_help = new MyStyle_Help();

		$function_names = get_function_names( $wp_filter['current_screen'] );

		// Assert that the add_help function is registered.
		$this->assertContains( 'add_help', $function_names );
	}

	/**
	 * Test the add_help function.
	 *
	 * @global string $mystyle_hook
	 * @global string $current_screen
	 */
	public function test_add_help() {
		global $mystyle_hook;
		global $current_screen;

		// Set up the variables.
		$mystyle_hook = 'mock-hook';
		$screen_id    = 'toplevel_page_' . $mystyle_hook;
		$screen       = WP_Screen::get( $screen_id );

		// Set the current screen (via $current_screen global).
		$current_screen = $screen;

		// Instanitate the SUT (System Under Test) class.
		$mystyle_help = new MyStyle_Help();

		// Assert that the MyStyle settings help is not in the screen.
		// phpcs:disable
		$this->assertNotContains(
			'MyStyle Custom Product Designer Help',
			json_encode( $screen->get_help_tabs() )
		);
		// phpcs:enable

		// Run the function.
		$mystyle_help->add_help();

		// Asset that the MyStyle plugin help is now in the screen.
		// phpcs:disable
		$this->assertContains(
			'MyStyle Custom Product Designer Help',
			json_encode( $screen->get_help_tabs() )
		);
		// phpcs:enable
	}

	/**
	 * Test that the add_help function doesn't add help to a page that is not
	 * the target page.
	 *
	 * @global string $mystyle_hook
	 */
	public function test_add_help_for_different_page() {
		global $mystyle_hook;

		// Set up the variables.
		$mystyle_hook = 'mock-hook';
		$screen_id    = 'widgets';
		$screen       = WP_Screen::get( $screen_id );

		// Set the current screen (via $current_screen global).
		$current_screen = $screen;

		// Instanitate the SUT (System Under Test) class.
		$mystyle_help = new MyStyle_Help();

		// Run the function.
		$mystyle_help->add_help();

		// Assert that the MyStyle plugin help is not in the screen.
		// phpcs:disable
		$this->assertNotContains(
			'MyStyle Custom Product Designer Help',
			json_encode( $screen->get_help_tabs() )
		);
		// phpcs:enable
	}

}
