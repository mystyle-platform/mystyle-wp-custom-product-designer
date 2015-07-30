<?php

require_once(MYSTYLE_PATH . '../woocommerce/woocommerce.php');
require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php');

/**
 * The MyStyleClassTest class includes tests for testing the MyStyle
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleClassTest extends WP_UnitTestCase {

    /**
     * Overrwrite the setUp function so that our custom tables will be persisted
     * to the test database.
     */
    function setUp() {
        // Perform the actual task according to parent class.
        parent::setUp();
        // Remove filters that will create temporary tables. So that permanent tables will be created.
        remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
        remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
        
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
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle = new MyStyle();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );  
        
        //Assert that the add_mystyle_order_item_meta function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_order_item_meta'] );
        $this->assertContains( 'add_mystyle_order_item_meta', $function_names );
    }
    
    /**
     * Test the init function.
     */    
    public function test_init() {
        $mystyle = new MyStyle();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_cart_item_thumbnail'] );
        $this->assertContains( 'modify_cart_item_thumbnail', $function_names );
    }
    
    /**
     * Test the add_mystyle_order_item_meta function.
     * @todo Get this test to work.  See the notes in Google Docs.
     */
    public function test_add_mystyle_order_item_meta() {
         //TODO
    }
    
    /**
     * Test the modify_cart_item_thumbnail function.
     */    
    public function test_modify_cart_item_thumbnail() {
        $design_id = 1;
        $get_image = '<img src="someimage.jpg"/>';
        $cart_item = array();
        $cart_item['mystyle_data'] = array();
        $cart_item['mystyle_data']['design_id'] = $design_id;
        $cart_item_key = null;
        
        //Create the design (note: we should maybe mock this in the tested class)
        $result_object = new MyStyle_MockDesignQueryResult( $design_id );
        $design = MyStyle_Design::create_from_result_object( $result_object );
        MyStyle_DesignManager::persist( $design );
        
        $new_image = MyStyle::modify_cart_item_thumbnail( $get_image, $cart_item, $cart_item_key );
        
        $this->assertEquals( '<img src="http://www.example.com/example.jpg"/>' , $new_image );
    }
    
    /**
     * Test the site_has_customizable_products function returns true when
     * customizable products exist.
    public function test_site_has_customizable_products_returns_true_when_customizable_products_exist() {
        //TODO: Will need to mock get_posts or WP_Query
    }
     */
    
    /**
     * Test the site_has_customizable_products function returns true when
     * customizable products exist.
    public function test_site_has_customizable_products_returns_false_when_customizable_products_dont_exist() {
        //TODO: Will need to mock get_posts or WP_Query
    }
     */
    
}
