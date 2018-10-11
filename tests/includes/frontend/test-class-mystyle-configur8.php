<?php

require_once( MYSTYLE_INCLUDES . 'frontend/class-mystyle-configur8.php' );

/**
 * The Test_MyStyle_Configur8 class includes tests for testing the
 * MyStyle_Configur8 class.
 *
 * @package MyStyle
 * @since 3.6.0
 */
class Test_MyStyle_Configur8 extends WP_UnitTestCase {

	/**
     * Overrwrite the setUp function so that our custom tables will be persisted
     * to the test database.
     */
    function setUp() {
        // Perform the actual task according to parent class.
        parent::setUp();

        //Create the tables
        MyStyle_Install::create_tables();
    }

    /**
     * Overrwrite the tearDown function to remove our custom tables.
     */
    function tearDown() {
        global $wpdb;
        // Perform the actual task according to parent class.
        parent::tearDown();

        //Drop the tables that we created
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Design::get_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Session::get_table_name());
    }

	/**
	 * Test the constructor.
	 * @global array $wp_filter
	 */
	public function test_constructor() {
		global $wp_filter;

		$configur8 = new MyStyle_Configur8();

		//Assert that the drop_configur8_script function is registered.
		$function_names = get_function_names( $wp_filter['woocommerce_before_single_product'] );
		$this->assertContains( 'drop_configur8_script', $function_names );
	}

	/**
	 * Test that the drop_configur8_script function doesn't output anything if
	 * the global enable_configur8 setting is turned off.
	 *
	 * @global \WC_Product $product
	 */
	public function test_drop_configur8_script_doesnt_serve_when_not_enabled() {
		global $product;

		//Mock the global $product variable
        $product_id = create_wc_test_product();
        $product = new \WC_Product_Simple( $product_id );
        $GLOBALS['post'] = $product;

		$configur8 = MyStyle_Configur8::get_instance();

		// Assert that nothing was output.
		ob_start();
		$configur8->drop_configur8_script();
		$outbound = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '', $outbound );
	}

	/**
     * Mock the mystyle_metadata
     * @param type $metadata
     * @param type $object_id
     * @param type $meta_key
     * @param type $single
     * @return string
     */
    function mock_mystyle_metadata( $metadata, $object_id, $meta_key, $single ){
        return 'yes';
    }

	/**
	 * Test that the drop_configur8_script function outputs the script when
	 * properly enabled.
	 *
	 * @global \WC_Product $product
	 */
	public function test_drop_configur8_script_outputs_script_when_enabled() {
		global $product;

		//Mock the global $product variable
        $product_id = create_wc_test_product();
        $product = new \WC_Product_Simple( $product_id );

		$configur8 = MyStyle_Configur8::get_instance();

		// Set the global configur8_enabled setting.
        $options = array();
        $options['api_key'] = '0';
		$options['secret'] = 'fake-secret';
		$options['enable_configur8'] = true;
        update_option( MYSTYLE_OPTIONS_NAME, $options );

		//Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);

		// Call the function.
		ob_start();
		$configur8->drop_configur8_script();
		$outbound = ob_get_contents();
		ob_end_clean();

		// Assert that the SDK is output.
		$this->assertContains( '<script', $outbound );

	}

	/**
	 * Test the get_instance function.
	 */
	public function test_get_instance() {
		// Get the singleton instance.
		$configur8 = MyStyle_Configur8::get_instance();

		// Assert that the instance is returned and is the expected class.
		$this->assertEquals( 'MyStyle_Configur8', get_class( $configur8 ) );
	}

}
