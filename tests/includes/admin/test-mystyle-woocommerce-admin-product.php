<?php
/**
 * The MyStyleWooCommerceAdminProductTest class includes tests for testing the
 * MyStyle_WooCommerce_Admin_Product class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . '../woocommerce/woocommerce.php';
require_once MYSTYLE_PATH . '../woocommerce/includes/admin/wc-admin-functions.php';
require_once MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-product.php';

/**
 * MyStyleWooCommerceAdminProductTest class.
 */
class MyStyleWooCommerceAdminProductTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 *
	 * @global $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();

		// Assert that the init function is registered.
		$function_names = get_function_names( $wp_filter['admin_init'] );
		$this->assertContains( 'admin_init', $function_names );
	}

	/**
	 * Test the admin_init() function.
	 *
	 * @global $wp_filter
	 */
	public function test_admin_init() {
		global $wp_filter;

		$mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();
		$mystyle_wc_admin_product->admin_init();

		// Assert that the add_product_data_tab function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_product_write_panel_tabs'] );
		$this->assertContains( 'add_product_data_tab', $function_names );

		// Assert that the add_mystyle_data_panel function is registered.
		if ( MyStyle()->get_WC()->version_compare( '2.6', '<' ) ) {
			$function_names = get_function_names( $wp_filter['woocommerce_product_write_panels'] );
		} else {
			$function_names = get_function_names( $wp_filter['woocommerce_product_data_panels'] );
		}
		$this->assertContains( 'add_mystyle_data_panel', $function_names );

		// Assert that the process_mystyle_data_panel function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_process_product_meta'] );
		$this->assertContains( 'process_mystyle_data_panel', $function_names );
	}

	/**
	 * Test the add_product_data_tab function
	 */
	public function test_add_product_data_tab() {
		$mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();

		// Assert that the data tab was rendered.
		ob_start();
		$mystyle_wc_admin_product->add_product_data_tab();
		$outbound = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<li class="mystyle_product_tab mystyle_product_options">', $outbound );
	}

	/**
	 * Test the add_mystyle_data_panel function.
	 *
	 * @global $post
	 */
	public function test_add_mystyle_data_panel() {
		global $post;
		require_once MYSTYLE_PATH . '../woocommerce/includes/admin/wc-meta-box-functions.php';

		$post     = new stdClass();
		$post->ID = 1;

		$mystyle_wc_admin_product = new MyStyle_WooCommerce_Admin_Product();

		// Assert that the data panel was rendered.
		ob_start();
		$mystyle_wc_admin_product->add_mystyle_data_panel();
		$outbound = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<div id="mystyle_product_data" class="panel woocommerce_options_panel">', $outbound );
	}

	/**
	 * Test the process_mystyle_data_panel function.
	 *
	 * @global $post
	 */
	public function test_process_mystyle_data_panel() {
		global $post;

		$post_id = 1;

		// Mock the global $post var and $_POST.
		$post     = new stdClass();
		$post->ID = $post_id;

		$_POST                                    = array();
		$_POST['_mystyle_enabled']                = true;
		$_POST['_mystyle_template_id']            = 1;
		$_POST['_mystyle_custom_template']        = false;
		$_POST['_mystyle_custom_template_width']  = '';
		$_POST['_mystyle_custom_template_height'] = '';
		$_POST['_mystyle_custom_template_shape']  = '';
		$_POST['_mystyle_custom_template_color']  = '';
		$_POST['_mystyle_custom_template_bgimg']  = '';
		$_POST['_mystyle_custom_template_fgimg']  = '';
		$_POST['_mystyle_custom_template_bleed']  = '';
		$_POST['_mystyle_customizer_ux']          = 'uxtest';
		$_POST['_mystyle_customizer_redirect']    = 'http://www.example.com';
		$_POST['_mystyle_design_id']              = '2';
		$_POST['_mystyle_print_type']             = 'FULL-COLOR';
		$_POST['_mystyle_configur8_enabled']      = true;

		// Create post object.
		$test_post = array(
			'ID'            => $post_id,
			'post_title'    => 'Test post',
			'post_content'  => 'This is a test post.',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_category' => array( 8, 39 ),
		);

		// Insert the post into the database.
		wp_insert_post( $test_post );

		// Call the function.
		MyStyle_WooCommerce_Admin_Product::get_instance()->process_mystyle_data_panel( $post_id );

		// Get the post meta.
		$mystyle_enabled           = get_post_meta( $post->ID, '_mystyle_enabled', true );
		$template_id               = get_post_meta( $post->ID, '_mystyle_template_id', true );
		$customizer_ux             = get_post_meta( $post->ID, '_mystyle_customizer_ux', true );
		$design_id                 = get_post_meta( $post->ID, '_mystyle_design_id', true );
		$print_type                = get_post_meta( $post->ID, '_mystyle_print_type', true );
		$mystyle_configur8_enabled = get_post_meta( $post->ID, '_mystyle_configur8_enabled', true );

		// Assert that the post meta was set.
		$this->assertEquals( 'yes', $mystyle_enabled );
		$this->assertEquals( 1, $template_id );
		$this->assertEquals( 'uxtest', $customizer_ux );
		$this->assertEquals( 2, $design_id );
		$this->assertEquals( 'FULL-COLOR', $print_type );
		$this->assertEquals( 'yes', $mystyle_configur8_enabled );
	}

	/**
	 * Test the process_mystyle_data_panel function when customization not
	 * enabled for the product. In this case, we just store the data (assuming
	 * that they'll enable it later).
	 *
	 * @global $post
	 */
	public function test_process_mystyle_data_panel_when_not_enabled() {
		global $post;

		$post_id = 1;

		// Mock the global $post var and $_POST.
		$post     = new stdClass();
		$post->ID = $post_id;

		$_POST                                    = array();
		$_POST['_mystyle_enabled']                = false;
		$_POST['_mystyle_template_id']            = 1;
		$_POST['_mystyle_custom_template']        = false;
		$_POST['_mystyle_custom_template_width']  = '';
		$_POST['_mystyle_custom_template_height'] = '';
		$_POST['_mystyle_custom_template_shape']  = '';
		$_POST['_mystyle_custom_template_color']  = '';
		$_POST['_mystyle_custom_template_bgimg']  = '';
		$_POST['_mystyle_custom_template_fgimg']  = '';
		$_POST['_mystyle_custom_template_bleed']  = '';
		$_POST['_mystyle_customizer_ux']          = 'uxtest';
		$_POST['_mystyle_customizer_redirect']    = 'http://www.example.com';
		$_POST['_mystyle_design_id']              = '2';
		$_POST['_mystyle_print_type']             = 'FULL-COLOR';

		// Create post object.
		$test_post = array(
			'ID'            => $post_id,
			'post_title'    => 'Test post',
			'post_content'  => 'This is a test post.',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_category' => array( 8, 39 ),
		);

		// Insert the post into the database.
		wp_insert_post( $test_post );

		// Call the function.
		MyStyle_WooCommerce_Admin_Product::get_instance()->process_mystyle_data_panel( $post_id );

		// Get the post meta.
		$mystyle_enabled = get_post_meta( $post->ID, '_mystyle_enabled', true );
		$template_id     = get_post_meta( $post->ID, '_mystyle_template_id', true );
		$customizer_ux   = get_post_meta( $post->ID, '_mystyle_customizer_ux', true );
		$design_id       = get_post_meta( $post->ID, '_mystyle_design_id', true );
		$print_type      = get_post_meta( $post->ID, '_mystyle_print_type', true );

		// Assert that the post meta was set.
		$this->assertEquals( 'no', $mystyle_enabled );
		$this->assertEquals( 1, $template_id );
		$this->assertEquals( 'uxtest', $customizer_ux );
		$this->assertEquals( 2, $design_id );
		$this->assertEquals( 'FULL-COLOR', $print_type );
	}

	/**
	 * Test the process_mystyle_data_panel function when customization is
	 * enabled but no template id has been entered. In this case, we:
	 *
	 * * Store the submitted data
	 * * Set customizable to false/no
	 * * Notify the user that the template id is required.
	 *
	 * @global $post
	 */
	public function test_process_mystyle_data_panel_when_missing_template_id() {
		global $post;

		$post_id = 1;

		// Mock the global $post var and $_POST.
		$post     = new stdClass();
		$post->ID = $post_id;

		$_POST                                    = array();
		$_POST['_mystyle_enabled']                = 'yes';
		$_POST['_mystyle_template_id']            = '';
		$_POST['_mystyle_custom_template']        = false;
		$_POST['_mystyle_custom_template_width']  = '';
		$_POST['_mystyle_custom_template_height'] = '';
		$_POST['_mystyle_custom_template_shape']  = '';
		$_POST['_mystyle_custom_template_color']  = '';
		$_POST['_mystyle_custom_template_bgimg']  = '';
		$_POST['_mystyle_custom_template_fgimg']  = '';
		$_POST['_mystyle_custom_template_bleed']  = '';
		$_POST['_mystyle_customizer_ux']          = 'uxtest';
		$_POST['_mystyle_customizer_redirect']    = 'http://www.example.com';
		$_POST['_mystyle_design_id']              = '2';
		$_POST['_mystyle_print_type']             = 'FULL-COLOR';

		// Create post object.
		$test_post = array(
			'ID'            => $post_id,
			'post_title'    => 'Test post',
			'post_content'  => 'This is a test post.',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_category' => array( 8, 39 ),
		);

		// Insert the post into the database.
		wp_insert_post( $test_post );

		// Call the function.
		MyStyle_WooCommerce_Admin_Product::get_instance()->process_mystyle_data_panel( $post_id );

		// Get the post meta.
		$mystyle_enabled = get_post_meta( $post->ID, '_mystyle_enabled', true );
		$template_id     = get_post_meta( $post->ID, '_mystyle_template_id', true );
		$customizer_ux   = get_post_meta( $post->ID, '_mystyle_customizer_ux', true );
		$design_id       = get_post_meta( $post->ID, '_mystyle_design_id', true );
		$print_type      = get_post_meta( $post->ID, '_mystyle_print_type', true );

		// Assert that the post meta was set as expected (customization was disabled).
		$this->assertEquals( 'no', $mystyle_enabled );
		$this->assertEquals( '', $template_id );
		$this->assertEquals( 'uxtest', $customizer_ux );
		$this->assertEquals( 2, $design_id );
		$this->assertEquals( 'FULL-COLOR', $print_type );

		// Assert that a notice was generated.
		$notices = mystyle_notice_pull_all_queued();

		/* @var $notice1 MyStyle_Notice The notice. */
		$notice1 = $notices[0];

		// Assert that the queued notice was returned.
		$this->assertEquals( 'invalid_product_options', $notice1->get_notice_key() );
	}

}
