<?php
/**
 * The MyStyleDesignTest class includes tests for testing the MyStyle_Design
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * Test requirements.
 */
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php';
require_once MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php';

/**
 * MyStyleDesignTest class.
 */
class MyStyleDesignTest extends WP_UnitTestCase {

	/**
	 * Test the create_from_post function.
	 */
	public function test_create_from_post() {

		$design_id = 1;

		// Mock the POST.
		$post                = array();
		$post['description'] = 'test description';
		$post['design_id']   = $design_id;
		$post['product_id']  = 0;
		$post['h']           = base64_encode( wp_json_encode( array( 'post' => array( 'add-to-cart' => 0 ) ) ) );
		$post['user_id']     = 0;
		$post['price']       = 0;

		$design = MyStyle_Design::create_from_post( $post );

		// Assert that the design_id is set.
		$this->assertEquals( $design_id, $design->get_design_id() );
	}

	/**
	 * Test the create_from_result_object function.
	 */
	public function test_create_from_result_object() {

		$design_id = 1;

		// Mock the result object.
		$result_object = new MyStyle_MockDesignQueryResult( $design_id );

		$design = MyStyle_Design::create_from_result_object( $result_object );

		// Assert that the design_id is set.
		$this->assertEquals( $design_id, $design->get_design_id() );
	}

	/**
	 * Test the create_from_result_array function.
	 */
	public function test_create_from_result_array() {

		$design_id = 1;

		// Mock the result object.
		$result_object = new MyStyle_MockDesignQueryResult( $design_id );

		$result_array = $result_object->to_array();

		$design = MyStyle_Design::create_from_result_array( $result_array );

		// Assert that the design_id is set.
		$this->assertEquals( $design_id, $design->get_design_id() );
	}

	/**
	 * Test the create_from_json function.
	 */
	public function test_create_from_json() {

		// Create the JSON to use.
		$json_str = '
		{
			"design_id": 1,
			"template_id": 0,
			"designer_id": null,
			"email": null,
			"title": null,
			"description": null,
			"price": null,
			"print_url": null,
			"web_url": "http://www.example.com/example.jpg",
			"thumb_url": "http://www.example.com/example.jpg",
			"design_url": "http://www.example.com/example.jpg",
			"product_id": 0,
			"user_id": 0,
			"created": "2015-08-06 22:35:52",
			"created_gmt": "2015-08-06 22:35:52",
			"modified": "2015-08-06 22:35:52",
			"modified_gmt": "2015-08-06 22:35:52",
			"mobile": 1,
			"access": null,
			"view_count": null,
			"purchase_count": null,
			"session_id": "testsessionid",
			"cart_data": null
		}';

		// Call the method.
		$design = MyStyle_Design::create_from_json( $json_str );

		// Assert that the design is created as expected.
		$this->assertEquals( 'MyStyle_Design', get_class( $design ) );
		$this->assertEquals( '1', $design->get_design_id() );
	}

	/**
	 * Test the add_api_data function.
	 */
	public function test_add_api_data() {

		$design = new MyStyle_Design();

		// Mock the api_data.
		$api_data               = array();
		$api_data['print_url']  = 'http://testhost/test_print_url.jpg';
		$api_data['web_url']    = 'http://testhost/test_web_url.jpg';
		$api_data['thumb_url']  = 'http://testhost/test_thumb_url.jpg';
		$api_data['design_url'] = 'http://testhost/test_design_url.jpg';
		$api_data['design_url'] = 'http://testhost/test_design_url.jpg';
		$api_data['mobile']     = '1';
		$api_data['access']     = '1';

		$design->add_api_data( $api_data );

		// Assert that the fields were set.
		$this->assertEquals( $api_data['print_url'], $design->get_print_url() );
		$this->assertEquals( $api_data['web_url'], $design->get_web_url() );
		$this->assertEquals( $api_data['thumb_url'], $design->get_thumb_url() );
		$this->assertEquals( $api_data['design_url'], $design->get_design_url() );
		$this->assertEquals( $api_data['mobile'], $design->is_mobile() );
		$this->assertEquals( $api_data['access'], $design->get_access() );
	}

	/**
	 * Test the get_meta function
	 */
	public function test_get_meta() {

		$design_id = 1;

		$result_object = new MyStyle_MockDesignQueryResult( $design_id );

		$design = MyStyle_Design::create_from_result_object( $result_object );

		$export = $design->get_meta();

		$serialized_export = serialize( $export ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

		$meta              = array();
		$meta['design_id'] = $design_id;
		$serialized_meta   = serialize( $meta ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

		// Assert that the expected meta is returned.
		$this->assertEquals( $serialized_meta, $serialized_export );
	}

	/**
	 * Test the get_schema function.
	 */
	public function test_get_schema() {

		$expected_schema = "
            CREATE TABLE wptests_mystyle_designs (
                ms_design_id bigint(32) NOT NULL,
                ms_product_id bigint(20) NOT NULL,
                ms_user_id bigint(20) NULL,
                ms_email varchar(255) NULL,
                ms_title varchar(255) NULL,
                ms_description text NULL,
                ms_price numeric(15,2) NULL,
                ms_print_url varchar(255) NULL,
                ms_web_url varchar(255) NULL,
                ms_thumb_url varchar(255) NULL,
                ms_design_url varchar(255) NULL,
                product_id bigint(20) NULL,
                user_id bigint(20) NULL DEFAULT NULL,
                design_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                ms_mobile int(1) NOT NULL DEFAULT '0',
                ms_access int(1) NOT NULL DEFAULT '0',
                design_view_count bigint(20) NULL DEFAULT '0',
                design_purchase_count bigint(20) NULL DEFAULT '0',
                session_id varchar(100) NULL DEFAULT NULL,
                cart_data TEXT NULL DEFAULT NULL,
                PRIMARY KEY (ms_design_id)
            )";

		$schema = MyStyle_Design::get_schema();

		// Assert that the expected schema is returned.
		$this->assertEquals( $expected_schema, $schema );
	}

	/**
	 * Test the get_table_name function.
	 */
	public function test_get_table_name() {

		$expected_table_name = 'wptests_mystyle_designs';

		$table_name = MyStyle_Design::get_table_name();

		// Assert that the expected table name is returned.
		$this->assertEquals( $expected_table_name, $table_name );
	}

	/**
	 * Test the get_primary_key function.
	 */
	public function test_get_primary_key() {

		$expected_primary_key = 'ms_design_id';

		$primary_key = MyStyle_Design::get_primary_key();

		// Assert that the expected primary key is returned.
		$this->assertEquals( $expected_primary_key, $primary_key );
	}

	/**
	 * Test the get_data_array function.
	 */
	public function test_get_data_array() {

		$design_id = 1;

		// Set up the expected data array.
		$expected_data_array = array(
			'ms_design_id'          => 1,
			'ms_product_id'         => 0,
			'ms_user_id'            => 0,
			'ms_email'              => 'someone@example.com',
			'ms_title'              => 'Design 1',
			'ms_description'        => 'test description',
			'ms_price'              => 0,
			'ms_print_url'          => 'http://www.example.com/example.jpg',
			'ms_web_url'            => 'http://www.example.com/example.jpg',
			'ms_thumb_url'          => 'http://www.example.com/example.jpg',
			'ms_design_url'         => 'http://www.example.com/example.jpg',
			'product_id'            => 0,
			'user_id'               => 0,
			'design_created'        => '2015-08-06 22:35:52',
			'design_created_gmt'    => '2015-08-06 22:35:52',
			'design_modified'       => '2015-08-06 22:35:52',
			'design_modified_gmt'   => '2015-08-06 22:35:52',
			'ms_mobile'             => 0,
			'ms_access'             => 0,
			'design_view_count'     => 0,
			'design_purchase_count' => 0,
			'cart_data'             => null,
			'session_id'            => 'testsessionid',
		);

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( $design_id );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Run the function.
		$data_array = $design->get_data_array();

		// Assert that the expected data array is returned.
		$this->assertEquals( $expected_data_array, $data_array );
	}

	/**
	 * Test the get_insert_format function.
	 */
	public function test_get_insert_format() {

		// Set up the expected formats array.
		$expected_formats_arr = array(
			'%d', // ms_design_id.
			'%d', // ms_product_id.
			'%d', // ms_user_id.
			'%s', // ms_email.
			'%s', // ms_title.
			'%s', // ms_description.
			'%d', // ms_price.
			'%s', // ms_print_url.
			'%s', // ms_web_url.
			'%s', // ms_thumb_url.
			'%s', // ms_design_url.
			'%d', // product_id.
			'%d', // user_id.
			'%s', // design_created.
			'%s', // design_created_gmt.
			'%s', // design_modified.
			'%s', // design_modified_gmt.
			'%d', // ms_mobile.
			'%d', // ms_access.
			'%d', // design_view_count.
			'%d', // design_purchase_count.
			'%s', // session_id.
			'%s', // cart_data.
		);

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( 1 );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Assert that the expected data array is returned.
		$this->assertEquals( $expected_formats_arr, $design->get_insert_format() );
	}

	/**
	 * Test the get_reload_url function.
	 */
	public function test_get_reload_url() {

		// Create the MyStyle Customize page (needed for the url).
		MyStyle_Customize_Page::create();

		// Create a design.
		$result_object = new MyStyle_MockDesignQueryResult( 1 );
		$design        = MyStyle_Design::create_from_result_object( $result_object );

		// Call the function.
		$url = $design->get_reload_url();

		// Assert that the expected page_id parameter is included in the url.
		$this->assertContains( 'page_id=' . MyStyle_Customize_Page::get_id(), $url );

		// Assert that the expected design_id parameter is included in the url.
		$this->assertContains( 'design_id=' . $design->get_design_id(), $url );
	}

	/**
	 * Test the get_add_to_cart_url function for a design with cart_data.
	 *
	 * @global $woocommerce
	 */
	public function test_get_add_to_cart_url() {
		global $woocommerce;
		$product_id = 1;
		$design_id  = 2;

		// Mock woocommerce.
		$woocommerce = new MyStyle_MockWooCommerce();

		// Create the MyStyle Customize page.
		MyStyle_Customize_Page::create();

		// Create a design.
		$result_object             = new MyStyle_MockDesignQueryResult( $design_id );
		$result_object->product_id = $product_id;
		$design                    = MyStyle_Design::create_from_result_object( $result_object );

		// Call the function.
		$url = $design->get_add_to_cart_url();

		// Assert that the expected product_id parameter is included in the url.
		$this->assertContains( 'add-to-cart=' . $product_id, $url );

		// Assert that the expected design_id parameter is included in the url.
		$this->assertContains( 'design_id=' . $design_id, $url );
	}

	/**
	 * Test the json_encode function.
	 */
	public function test_json_encode() {

		// Create Design
		$json_str = '
		{
			"design_id": 1,
			"template_id": 0,
			"designer_id": null,
			"email": null,
			"title": null,
			"description": null,
			"price": null,
			"print_url": null,
			"web_url": "http://www.example.com/example.jpg",
			"thumb_url": "http://www.example.com/example.jpg",
			"design_url": "http://www.example.com/example.jpg",
			"product_id": 0,
			"user_id": 0,
			"created": "2015-08-06 22:35:52",
			"created_gmt": "2015-08-06 22:35:52",
			"modified": "2015-08-06 22:35:52",
			"modified_gmt": "2015-08-06 22:35:52",
			"mobile": 1,
			"access": null,
			"view_count": null,
			"purchase_count": null,
			"session_id": "testsessionid",
			"cart_data": null
		}';
		$design   = MyStyle_Design::create_from_json( $json_str );

		// Call the method.
		$json_data = $design->json_encode();

		// Assert that the json data is returned as expected.
		$this->assertTrue( is_array( $json_data ) );
		$this->assertEquals( '1', $json_data['design_id'] );
	}

}
