<?php
/**
 * The MyStyleAddonsPageTest class includes tests for testing the
 * MyStyle_Addons_Page class.
 *
 * @package MyStyle
 * @since 0.1.16
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-addons-page.php';

/**
 * MyStyleAddonsPageTest class.
 */
class MyStyleAddonsPageTest extends WP_UnitTestCase {

	/**
	 * Test the constructor
	 *
	 * @global $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_addons_page = new MyStyle_Addons_Page();

		// Assert that the page is registered.
		$function_names = get_function_names( $wp_filter['admin_menu'] );
		$this->assertContains( 'add_page_to_menu', $function_names );
	}

	/**
	 * Test the render_page function.
	 */
	public function test_render_page() {
		// Assert that the options page was rendered.
		ob_start();
		MyStyle_Addons_Page::get_instance()->render_page();
		$outbound = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'MyStyle Add-ons', $outbound );
	}

}
