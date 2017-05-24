<?php

require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );

/**
 * The MyStyleOrderListenerTest class includes tests for testing the 
 * MyStyle_Order_Listener class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleOrderListenerTest extends WP_UnitTestCase {
    
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
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Session::get_table_name());
    }
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        global $wp_filter;
        
        $mystyle_order_listener = new MyStyle_Order_Listener();
        
        if( WC()->version < 3.0 ) {
            $function_names = get_function_names( $wp_filter['woocommerce_add_order_item_meta'] );
        } else {
            $function_names = get_function_names( $wp_filter['woocommerce_checkout_create_order_line_item'] );
        }
        
    //Assert that the add_mystyle_order_item_meta function is registered.
        $this->assertContains( 'add_mystyle_order_item_meta', $function_names );
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
     * Test the add_mystyle_order_item_meta function.
     * @todo Get this test to work.  See the notes in Google Docs.
     */
    public function test_add_mystyle_order_item_meta() {
         //TODO
    }
    
}
