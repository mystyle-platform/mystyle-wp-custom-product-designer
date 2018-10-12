<?php

require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php' );

/**
 * The MyStyleUserInterfaceTest class includes tests for testing the
 * MyStyle_User_Interface class.
 *
 * @package MyStyle
 * @since 1.5.0
 */
class MyStyleUserInterfaceTest extends WP_UnitTestCase {

	/**
	 * Overrwrite the setUp function so that our custom tables will be persisted
	 * to the test database.
	 */
	function setUp() {
		// Perform the actual task according to parent class.
		parent::setUp();
		// Remove filters that will create temporary tables. So that permanent tables will be created.
		remove_filter('query', array($this, '_create_temporary_tables'));
		remove_filter('query', array($this, '_drop_temporary_tables'));

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
	}

	/**
	 * Mock the mystyle_metadata
	 * @param type $metadata
	 * @param type $object_id
	 * @param type $meta_key
	 * @param type $single
	 * @return string
	 */
	function mock_mystyle_metadata($metadata, $object_id, $meta_key, $single) {
		return 'yes';
	}

	/**
	 * Test the constructor
	 */
	public function test_constructor() {
		$mystyle_ui = new MyStyle_User_Interface();

		global $wp_filter;

		//Assert that the expected functions are registered.
		$function_names = get_function_names($wp_filter['wp_login']);
		$this->assertContains('on_wp_login', $function_names);

		$function_names = get_function_names($wp_filter['user_register']);
		$this->assertContains('on_user_register', $function_names);

		$function_names = get_function_names($wp_filter['woocommerce_created_customer']);
		$this->assertContains('on_woocommerce_created_customer', $function_names);
	}

	/**
	 * Test the on_wp_login function.
	 *
	 * This test does the following:
	 * * Creates an anonymous design.
	 * * Asserts that the design doesn't have a user id set.
	 * * Calls the function.
	 * * Asserts that the design now has a user id set.
	 */
	public function test_on_wp_login() {
		$design_id = 1;
		$email = 'someone@example.com';
		$session_id = 'testsession';

		//Create and persist the session
		$session = MyStyle_Session::create($session_id);
		MyStyle_SessionManager::persist($session);

		//Set the session to be for the current request
		$_SESSION[MyStyle_Session::$SESSION_KEY] = $session;

		//Create a design
		$design = MyStyle_MockDesign::getMockDesign($design_id);
		$design->set_session_id($session_id);

		//Persist the design
		MyStyle_DesignManager::persist($design);

		//assert that there is no user id set on the design
		$this->assertNull($design->get_user_id());

		//Mock a WP_User
		$user_id = MyStyle_Test_util::create_user('testuser', 'testpassword', $email);
		/** \WP_User */
		$user = get_user_by('id', $user_id);

		//call the function
		$mystyle_ui = new MyStyle_User_Interface();
		$mystyle_ui->on_wp_login(null, $user);

		//Get the design
		$design_from_db = MyStyle_DesignManager::get($design_id);

		//assert that the user id is now set on the design.
		$this->assertEquals($user_id, $design_from_db->get_user_id());
	}

	/**
	 * Test the on_user_register function.
	 *
	 * This test does the following:
	 * * Creates an anonymous design.
	 * * Asserts that the design doesn't have a user id set.
	 * * Calls the function.
	 * * Asserts that the design now has a user id set.
	 */
	public function test_on_user_register() {
		$design_id = 1;
		$email = 'someone@example.com';
		$session_id = 'testsession';

		//Create and persist the session
		$session = MyStyle_Session::create($session_id);
		MyStyle_SessionManager::persist($session);

		//Set the session to be for the current request
		$_SESSION[MyStyle_Session::$SESSION_KEY] = $session;

		//Create a design
		$design = MyStyle_MockDesign::getMockDesign($design_id);
		$design->set_session_id($session_id);

		//Persist the design
		MyStyle_DesignManager::persist($design);

		//assert that there is no user id set on the design
		$this->assertNull($design->get_user_id());

		//Mock a WP_User
		$user_id = MyStyle_Test_util::create_user('testuser', 'testpassword', $email);
		/** \WP_User */
		$user = get_user_by('id', $user_id);

		//call the function
		$mystyle_ui = new MyStyle_User_Interface();
		$mystyle_ui->on_user_register($user_id);

		//Get the design
		$design_from_db = MyStyle_DesignManager::get($design_id);

		//assert that the user id is now set on the design.
		$this->assertEquals($user_id, $design_from_db->get_user_id());
	}

	/**
	 * Test the on_woocommerce_created_customer function.
	 *
	 * This test does the following:
	 * * Creates an anonymous design.
	 * * Asserts that the design doesn't have a user id set.
	 * * Calls the function.
	 * * Asserts that the design now has a user id set.
	 */
	public function test_on_woocommerce_created_customer() {
		$design_id = 1;
		$email = 'someone@example.com';
		$session_id = 'testsession';

		//Create and persist the session
		$session = MyStyle_Session::create($session_id);
		MyStyle_SessionManager::persist($session);

		//Set the session to be for the current request
		$_SESSION[MyStyle_Session::$SESSION_KEY] = $session;

		//Create a design
		$design = MyStyle_MockDesign::getMockDesign($design_id);
		$design->set_session_id($session_id);

		//Persist the design
		MyStyle_DesignManager::persist($design);

		//assert that there is no user id set on the design
		$this->assertNull($design->get_user_id());

		//Mock a WP_User
		$user_id = MyStyle_Test_util::create_user('testuser', 'testpassword', $email);
		/** \WP_User */
		$user = get_user_by('id', $user_id);

		//call the function
		$mystyle_ui = new MyStyle_User_Interface();
		$mystyle_ui->on_woocommerce_created_customer($user_id, null, null);

		//Get the design
		$design_from_db = MyStyle_DesignManager::get($design_id);

		//assert that the user id is now set on the design.
		$this->assertEquals($user_id, $design_from_db->get_user_id());
	}

}
